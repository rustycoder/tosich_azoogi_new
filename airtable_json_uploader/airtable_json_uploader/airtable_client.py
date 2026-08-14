import time
import requests
from typing import List, Dict, Any, Optional, Tuple
from .config import get_auth_headers

BASE_URL = "https://api.airtable.com/v0"


class AirtableClient:
    """Client for interacting with Airtable REST API and Meta API."""

    def __init__(self, api_key: Optional[str] = None):
        self.headers = get_auth_headers() if not api_key else {
            "Authorization": f"Bearer {api_key}",
            "Content-Type": "application/json"
        }

    def _request(self, method: str, url: str, **kwargs) -> requests.Response:
        """Internal helper for API calls with automatic retry on rate limit (HTTP 429)."""
        retries = 3
        backoff = 1.5
        for attempt in range(retries):
            response = requests.request(method, url, headers=self.headers, **kwargs)
            if response.status_code == 429:
                time.sleep(backoff)
                backoff *= 2
                continue
            return response
        return response

    def list_bases(self) -> List[Dict[str, Any]]:
        """Fetch all bases accessible to the API key via Meta API."""
        url = f"{BASE_URL}/meta/bases"
        response = self._request("GET", url)
        if response.status_code == 200:
            return response.json().get("bases", [])
        else:
            raise Exception(f"Failed to fetch bases ({response.status_code}): {response.text}")

    def list_workspaces(self) -> List[Dict[str, Any]]:
        """Fetch workspaces via Meta API (/v0/meta/workspaces) if accessible."""
        url = f"{BASE_URL}/meta/workspaces"
        response = self._request("GET", url)
        if response.status_code == 200:
            return response.json().get("workspaces", [])
        return []

    def get_workspaces_and_bases(self) -> Tuple[List[Dict[str, Any]], List[Dict[str, Any]], Dict[str, str]]:
        """
        Retrieves all bases and workspaces, returning:
        (bases_list, workspaces_list, base_id_to_workspace_name_map)
        """
        bases = self.list_bases()
        workspaces = self.list_workspaces()
        base_to_wsp = {}

        for wsp in workspaces:
            wsp_name = wsp.get("name", f"Workspace {wsp.get('id', '')}")
            for b_id in wsp.get("baseIds", []):
                base_to_wsp[b_id] = wsp_name

        return bases, workspaces, base_to_wsp



    def list_tables(self, base_id: str) -> List[Dict[str, Any]]:
        """Fetch schema for all tables in a base via Meta API."""
        url = f"{BASE_URL}/meta/bases/{base_id}/tables"
        response = self._request("GET", url)
        if response.status_code == 200:
            return response.json().get("tables", [])
        else:
            raise Exception(f"Failed to fetch tables for base {base_id} ({response.status_code}): {response.text}")

    def fetch_existing_records(self, base_id: str, table_id_or_name: str) -> List[Dict[str, Any]]:
        """Fetch all existing records from a table with offset pagination."""
        records = []
        offset = None
        url = f"{BASE_URL}/{base_id}/{table_id_or_name}"

        while True:
            params = {}
            if offset:
                params["offset"] = offset

            response = self._request("GET", url, params=params)
            if response.status_code != 200:
                raise Exception(f"Failed to fetch records from table '{table_id_or_name}' ({response.status_code}): {response.text}")

            data = response.json()
            fetched = data.get("records", [])
            records.extend(fetched)

            offset = data.get("offset")
            if not offset:
                break

        return records

    def update_records(self, base_id: str, table_id_or_name: str, records: List[Dict[str, Any]]) -> List[Dict[str, Any]]:
        """
        Update existing records in Airtable in batches of up to 10.
        `records` should be a list of objects like: [{"id": "recXXX", "fields": {...}}, ...]
        """
        updated = []
        url = f"{BASE_URL}/{base_id}/{table_id_or_name}"
        batch_size = 10

        for i in range(0, len(records), batch_size):
            batch = records[i:i + batch_size]
            payload = {"records": batch, "typecast": True}
            response = self._request("PATCH", url, json=payload)
            if response.status_code in [200, 201]:
                res_data = response.json()
                updated.extend(res_data.get("records", []))
            else:
                print(f"Error updating batch ({response.status_code}): {response.text}")

        return updated

    def sync_linked_attributes(self, base_id: str, attr_table_name: str, attributes_input: Any) -> List[str]:
        """
        Processes attribute key-value pairs (e.g. from product_features or attributes list),
        deduplicates/creates records in the secondary Product Attributes table,
        and returns an array of Airtable Record IDs ["recXXX1", "recXXX2", ...].
        """
        if not attributes_input or not attr_table_name:
            return []

        # Parse attribute pairs into list of (attr_name, attr_value)
        attr_pairs = []

        if isinstance(attributes_input, dict):
            if "product_features" in attributes_input and isinstance(attributes_input["product_features"], dict):
                attributes_input = attributes_input["product_features"]
            elif "attributes" in attributes_input and isinstance(attributes_input["attributes"], dict):
                attributes_input = attributes_input["attributes"]

            for k, v in attributes_input.items():
                if isinstance(v, list):
                    for sub_v in v:
                        if isinstance(sub_v, dict) and "name" in sub_v:
                            attr_pairs.append((str(k), str(sub_v["name"])))
                        elif sub_v is not None:
                            attr_pairs.append((str(k), str(sub_v)))
                elif isinstance(v, dict) and "name" in v:
                    attr_pairs.append((str(k), str(v["name"])))
                elif v is not None:
                    attr_pairs.append((str(k), str(v)))


        elif isinstance(attributes_input, list):
            for item in attributes_input:
                if isinstance(item, dict) and "name" in item and "value" in item:
                    attr_pairs.append((str(item["name"]), str(item["value"])))
                elif isinstance(item, dict) and "name" in item:
                    attr_pairs.append(("Attribute", str(item["name"])))

        elif isinstance(attributes_input, str):
            try:
                parsed = json.loads(attributes_input)
                return self.sync_linked_attributes(base_id, attr_table_name, parsed)
            except Exception:
                pass

        # Dynamically discover column names for Product Attributes table from schema
        name_col = "Attribute Name"
        value_col = "Term Name"
        visible_col = None

        try:
            tables = self.list_tables(base_id)
            for t in tables:
                if t.get("name", "").lower() == attr_table_name.lower() or t.get("id") == attr_table_name:
                    field_names = [f.get("name") for f in t.get("fields", [])]

                    # Match Key column (prefers "Attribute Name")
                    matched_name = None
                    for fn in field_names:
                        if fn.lower() == "attribute name":
                            matched_name = fn
                            break
                    if not matched_name:
                        for candidate in ["Attribute Name", "Attribute", "Name"]:
                            for fn in field_names:
                                if candidate.lower() == fn.lower():
                                    matched_name = fn
                                    break
                    if matched_name:
                        name_col = matched_name

                    # Match Value column (prefers "Term Name", then "Attribute Value", "Term Value", "Value")
                    matched_val = None
                    for fn in field_names:
                        if fn.lower() == "term name":
                            matched_val = fn
                            break
                    if not matched_val:
                        for candidate in ["Attribute Value", "Term Value", "Value", "Term Name", "Slug"]:
                            for fn in field_names:
                                if candidate.lower() == fn.lower() and fn != name_col:
                                    matched_val = fn
                                    break
                    if matched_val:
                        value_col = matched_val

                    # Match visible column
                    for fn in field_names:
                        if "visible" in fn.lower():
                            visible_col = fn
                            break
                    break
        except Exception as e:
            print(f"Warning: Could not fetch schema for table '{attr_table_name}': {e}")

        # Fetch existing records from Product Attributes table to build in-memory lookup cache
        try:
            existing_attrs = self.fetch_existing_records(base_id, attr_table_name)
        except Exception as e:
            print(f"Warning: Could not fetch from attributes table '{attr_table_name}': {e}")
            existing_attrs = []

        cache = {}

        for rec in existing_attrs:
            fields = rec.get("fields", {})
            name_val = fields.get(name_col) or fields.get("Attribute Name") or fields.get("Name") or ""
            val_val = fields.get(value_col) or fields.get("Term Name") or fields.get("Attribute Value") or fields.get("Value") or ""
            key = (str(name_val).strip().lower(), str(val_val).strip().lower())
            cache[key] = rec["id"]


        linked_record_ids = []
        to_create = []
        creation_keys = []

        for name, val in attr_pairs:
            name_clean = name.strip()
            val_clean = val.strip()
            if not name_clean or not val_clean:
                continue

            cache_key = (name_clean.lower(), val_clean.lower())
            if cache_key in cache:
                rec_id = cache[cache_key]
                if rec_id not in linked_record_ids:
                    linked_record_ids.append(rec_id)
            else:
                fields_dict = {
                    name_col: name_clean,
                    value_col: val_clean
                }
                if visible_col:
                    fields_dict[visible_col] = True

                to_create.append({
                    "fields": fields_dict
                })
                creation_keys.append((cache_key, name_clean, val_clean))

        # Execute batch creation for new attribute records
        if to_create:
            created = self.create_records(base_id, attr_table_name, to_create)
            for idx, rec in enumerate(created):
                if idx < len(creation_keys):
                    ck, n, v = creation_keys[idx]
                    cache[ck] = rec["id"]
                    if rec["id"] not in linked_record_ids:
                        linked_record_ids.append(rec["id"])

        return linked_record_ids


    def create_records(self, base_id: str, table_id_or_name: str, records: List[Dict[str, Any]]) -> List[Dict[str, Any]]:
        """
        Create new records in Airtable in batches of up to 10.
        `records` should be a list of objects like: [{"fields": {...}}, ...]
        """
        created = []
        url = f"{BASE_URL}/{base_id}/{table_id_or_name}"
        batch_size = 10

        for i in range(0, len(records), batch_size):
            batch = records[i:i + batch_size]
            payload = {"records": batch, "typecast": True}
            response = self._request("POST", url, json=payload)
            if response.status_code in [200, 201]:
                res_data = response.json()
                created.extend(res_data.get("records", []))
            else:
                raise Exception(f"Failed to create records in Airtable table '{table_id_or_name}' ({response.status_code}): {response.text}")

        return created


