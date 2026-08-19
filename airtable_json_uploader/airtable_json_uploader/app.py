import os
import sys
import json
from pathlib import Path
from typing import Dict, Any, List
from flask import Flask, render_template, request, jsonify

try:
    from .config import (
        get_api_key,
        save_api_key,
        save_config,
        get_base_id,
        get_products_table,
        get_categories_table,
        get_attributes_table,
    )
    from .airtable_client import AirtableClient
    from .parser import JSONParser, flatten_json
    from .mapper import MappingProfile
except ImportError:
    sys.path.insert(0, str(Path(__file__).parent.parent))
    from airtable_json_uploader.config import (
        get_api_key,
        save_api_key,
        save_config,
        get_base_id,
        get_products_table,
        get_categories_table,
        get_attributes_table,
    )
    from airtable_json_uploader.airtable_client import AirtableClient
    from airtable_json_uploader.parser import JSONParser, flatten_json
    from airtable_json_uploader.mapper import MappingProfile


# Package directories
PACKAGE_DIR = Path(__file__).parent
MAPPINGS_DIR = PACKAGE_DIR.parent / "mappings"
JSON_FILES_DIR = PACKAGE_DIR.parent / "json_files"

os.makedirs(MAPPINGS_DIR, exist_ok=True)
os.makedirs(JSON_FILES_DIR, exist_ok=True)

app = Flask(
    __name__,
    template_folder=str(PACKAGE_DIR / "templates"),
    static_folder=str(PACKAGE_DIR / "static")
)


def get_client() -> AirtableClient:
    key = get_api_key()
    if not key:
        raise ValueError("AIRTABLE_API_KEY is not configured in .env")
    return AirtableClient(api_key=key)


@app.route("/")
def index():
    return render_template("index.html")


@app.route("/extract")
def extract_page():
    return render_template("extract.html")


@app.route("/test-attributes")
def test_attributes_page():
    return render_template("test_attributes.html")


@app.route("/api/status", methods=["GET"])

def get_status():
    key = get_api_key()
    masked = f"{key[:7]}...{key[-4:]}" if len(key) > 12 else ("Set" if key else "Missing")
    return jsonify({
        "has_key": bool(key),
        "masked_key": masked,
        "json_dir": str(JSON_FILES_DIR),
        "mappings_dir": str(MAPPINGS_DIR),
        "base_id": get_base_id(),
        "products_table": get_products_table(),
        "categories_table": get_categories_table(),
        "attributes_table": get_attributes_table()
    })


@app.route("/api/save-token", methods=["POST"])
def save_token_route():
    data = request.get_json() or {}
    new_token = data.get("token", "").strip()
    if not new_token:
        return jsonify({"error": "Token cannot be empty"}), 400
    save_api_key(new_token)
    return jsonify({"success": True, "masked_key": f"{new_token[:7]}...{new_token[-4:]}"})


@app.route("/api/save-config", methods=["POST"])
def save_config_route():
    data = request.get_json() or {}
    save_config(
        base_id=data.get("base_id"),
        products_table=data.get("products_table"),
        categories_table=data.get("categories_table"),
        attributes_table=data.get("attributes_table")
    )
    return jsonify({"success": True})



@app.route("/api/workspaces-and-bases", methods=["GET"])
def get_workspaces_and_bases_route():
    try:
        client = get_client()
        bases, workspaces, base_to_wsp = client.get_workspaces_and_bases()
        return jsonify({
            "bases": bases,
            "workspaces": workspaces,
            "base_to_wsp": base_to_wsp
        })
    except Exception as e:
        return jsonify({"error": str(e)}), 500


@app.route("/api/tables", methods=["GET"])
def get_tables_route():
    base_id = request.args.get("base_id")
    if not base_id:
        return jsonify({"error": "base_id parameter is required"}), 400
    try:
        client = get_client()
        tables = client.list_tables(base_id)
        return jsonify({"tables": tables})
    except Exception as e:
        return jsonify({"error": str(e)}), 500


@app.route("/api/json-files", methods=["GET"])
def list_json_files_route():
    files = []
    if JSON_FILES_DIR.exists():
        for f in os.listdir(JSON_FILES_DIR):
            if f.lower().endswith((".json", ".xlsx", ".xls", ".csv")):
                full_p = JSON_FILES_DIR / f
                files.append({
                    "name": f,
                    "path": str(full_p),
                    "size": full_p.stat().st_size
                })
    return jsonify({"files": files, "drop_folder": str(JSON_FILES_DIR)})


@app.route("/api/parse-json", methods=["POST"])
def parse_json_route():
    try:
        items = []

        if "file" in request.files:
            file_obj = request.files["file"]
            filename = file_obj.filename
            if filename.lower().endswith((".xlsx", ".xls", ".csv")):
                temp_path = JSON_FILES_DIR / filename
                file_obj.save(str(temp_path))
                items = JSONParser.load_path(str(temp_path))
            else:
                raw_text = file_obj.read().decode("utf-8")
                data = json.loads(raw_text)
                if isinstance(data, list):
                    for obj in data:
                        if isinstance(obj, dict):
                            items.append({"_source_file": filename, "_raw": obj, "_flat": flatten_json(obj)})
                elif isinstance(data, dict):
                    items.append({"_source_file": filename, "_raw": data, "_flat": flatten_json(data)})

        else:
            payload = request.get_json() or {}
            target_path = payload.get("path")
            target_paths = payload.get("paths", [])
            raw_json_str = payload.get("raw_json")


            if raw_json_str:
                data = json.loads(raw_json_str)
                if isinstance(data, list):
                    for obj in data:
                        if isinstance(obj, dict):
                            items.append({"_source_file": "Pasted JSON", "_raw": obj, "_flat": flatten_json(obj)})
                elif isinstance(data, dict):
                    items.append({"_source_file": "Pasted JSON", "_raw": data, "_flat": flatten_json(data)})
            elif target_paths:
                for p in target_paths:
                    if p == "ALL":
                        items.extend(JSONParser.load_path(str(JSON_FILES_DIR)))
                        break
                    else:
                        full_p = JSON_FILES_DIR / p if not os.path.isabs(p) else Path(p)
                        if full_p.exists():
                            items.extend(JSONParser.load_path(str(full_p)))

            elif target_path:
                if target_path == "ALL":
                    items = JSONParser.load_path(str(JSON_FILES_DIR))
                else:
                    full_p = JSON_FILES_DIR / target_path if not os.path.isabs(target_path) else Path(target_path)
                    items = JSONParser.load_path(str(full_p))
            else:
                return jsonify({"error": "No file, path, or JSON data provided"}), 400


        if not items:
            return jsonify({"error": "No valid JSON product items found"}), 400

        available_keys = JSONParser.extract_available_keys(items)
        sample_item = items[0]["_raw"]

        return jsonify({
            "total_items": len(items),
            "keys": available_keys,
            "sample": sample_item,
            "items": items
        })
    except Exception as e:
        return jsonify({"error": str(e)}), 500


@app.route("/api/mappings", methods=["GET"])
def list_mappings_route():
    profiles = []
    if MAPPINGS_DIR.exists():
        for f in os.listdir(MAPPINGS_DIR):
            if f.lower().endswith(".json"):
                profiles.append(f)
    return jsonify({"profiles": sorted(profiles)})


@app.route("/api/load-mapping", methods=["GET", "POST"])
def load_mapping_route():
    name = request.args.get("name") if request.method == "GET" else (request.get_json() or {}).get("name")
    if not name:
        return jsonify({"error": "Mapping profile name is required"}), 400

    filepath = MAPPINGS_DIR / name
    if not filepath.exists():
        return jsonify({"error": f"Mapping profile '{name}' not found"}), 404

    try:
        profile = MappingProfile.load(str(filepath))
        return jsonify({"name": name, "mapping": profile.mapping})
    except Exception as e:
        return jsonify({"error": str(e)}), 500


@app.route("/api/save-mapping", methods=["POST"])
def save_mapping_route():
    payload = request.get_json() or {}
    name = payload.get("name", "").strip()
    mapping = payload.get("mapping", {})

    if not name:
        name = "product_mapping.json"
    if not name.endswith(".json"):
        name += ".json"

    filepath = MAPPINGS_DIR / name
    try:
        profile = MappingProfile(mapping)
        profile.save(str(filepath))
        return jsonify({"success": True, "name": name, "filepath": str(filepath)})
    except Exception as e:
        return jsonify({"error": str(e)}), 500


@app.route("/api/preview-matches", methods=["POST"])
def preview_matches_route():
    payload = request.get_json() or {}
    base_id = payload.get("base_id")
    table_name = payload.get("table_name")
    primary_col = payload.get("primary_key_col")
    attr_table_name = payload.get("attr_table_name")
    mapping = payload.get("mapping", {})
    items = payload.get("items", [])

    if not base_id or not table_name:
        return jsonify({"error": "base_id and table_name are required"}), 400

    try:
        client = get_client()

        # Fetch table columns schema for direct name matching
        tables = client.list_tables(base_id)
        target_cols = []
        for t in tables:
            if t.get("name", "").lower() == table_name.lower() or t.get("id") == table_name:
                target_cols = [f.get("name") for f in t.get("fields", [])]
                break

        # Auto-detect primary key column if not specified (prefer SKU, Name)
        if not primary_col:
            for candidate in ["SKU", "Name", "Product name", "Title", "ID"]:
                for tc in target_cols:
                    if candidate.lower() == tc.lower():
                        primary_col = tc
                        break
                if primary_col:
                    break
            if not primary_col and target_cols:
                primary_col = target_cols[0]

        # Auto-build 1-to-1 mapping based on exact key-to-column name matching if mapping not provided or empty
        if not mapping:
            mapping = {}
            for item in items:
                flat = item.get("_flat", {})
                for j_k in flat.keys():
                    if j_k in mapping:
                        continue
                    j_clean = j_k.strip().lower()
                    for col_name in target_cols:
                        c_clean = col_name.strip().lower()
                        if j_clean == c_clean:
                            mapping[j_k] = col_name
                            break

        existing_records = client.fetch_existing_records(base_id, table_name)

        # Build index of existing records
        existing_by_key = {}
        for rec in existing_records:
            fields = rec.get("fields", {})
            val = fields.get(primary_col)
            if val is not None:
                existing_by_key[str(val).strip().lower()] = rec

        profile = MappingProfile(mapping)
        primary_json_key = None
        for j_key, col_name in mapping.items():
            if col_name.lower() == primary_col.lower():
                primary_json_key = j_key
                break

        matches = []
        unmatched = []

        for item in items:
            raw_item = item.get("_raw", {})
            flat = item.get("_flat", {})
            mapped_fields = profile.apply(flat)
            if not mapped_fields:
                continue

            # Process linked attributes if attr_table_name provided or Attributes key present
            if attr_table_name:
                attr_input = raw_item.get("Attributes") or raw_item.get("attributes") or raw_item.get("product_features") or flat.get("Attributes") or flat.get("product_features")
                if attr_input:
                    rec_ids = client.sync_linked_attributes(base_id, attr_table_name, attr_input)
                    if rec_ids:
                        for j_k, c_name in mapping.items():
                            if j_k.lower() in ["attributes", "product_features"] or "attribute" in c_name.lower():
                                mapped_fields[c_name] = rec_ids

            match_val = str(flat.get(primary_json_key, "")).strip().lower() if primary_json_key else ""
            matched_rec = existing_by_key.get(match_val) if match_val else None

            if matched_rec:
                # Compute diff
                current_fields = matched_rec.get("fields", {})
                diffs = {}
                for col_name, new_val in mapped_fields.items():
                    curr_val = current_fields.get(col_name)
                    if curr_val != new_val:
                        diffs[col_name] = {"old": curr_val, "new": new_val}

                matches.append({
                    "record_id": matched_rec["id"],
                    "match_key": match_val,
                    "primary_value": current_fields.get(primary_col, match_val),
                    "fields_to_update": mapped_fields,
                    "diffs": diffs,
                    "selected": True
                })
            else:
                unmatched.append({
                    "match_key": match_val,
                    "source_file": item.get("_source_file", "JSON"),
                    "mapped_fields": mapped_fields
                })



        return jsonify({
            "total_json_items": len(items),
            "total_existing_records": len(existing_records),
            "matches": matches,
            "unmatched": unmatched
        })
    except Exception as e:
        return jsonify({"error": str(e)}), 500


@app.route("/api/test-attributes-sync", methods=["POST"])
def test_attributes_sync_route():
    payload = request.get_json() or {}
    base_id = payload.get("base_id")
    attr_table = payload.get("attr_table_name", "Product attributes")
    features_input = payload.get("product_features")

    if not base_id or not features_input:
        return jsonify({"error": "base_id and product_features JSON are required"}), 400

    try:
        client = get_client()
        rec_ids = client.sync_linked_attributes(base_id, attr_table, features_input)
        return jsonify({
            "success": True,
            "attr_table_name": attr_table,
            "record_ids_count": len(rec_ids),
            "record_ids": rec_ids
        })
    except Exception as e:
        return jsonify({"error": str(e)}), 500


@app.route("/api/execute-updates", methods=["POST"])
def execute_updates_route():

    payload = request.get_json() or {}
    base_id = payload.get("base_id")
    table_name = payload.get("table_name")
    updates = payload.get("updates", [])    # list of {"id": "recXXX", "fields": {...}}
    creations = payload.get("creations", []) # list of {"fields": {...}}

    if not base_id or not table_name:
        return jsonify({"error": "base_id and table_name are required"}), 400

    if not updates and not creations:
        return jsonify({"error": "No updates or creations provided to execute"}), 400

    try:
        client = get_client()
        updated_records = []
        created_records = []

        if updates:
            updated_records = client.update_records(base_id, table_name, updates)

        if creations:
            created_records = client.create_records(base_id, table_name, creations)

        return jsonify({
            "success": True,
            "updated_count": len(updated_records),
            "created_count": len(created_records),
            "updated_records": updated_records,
            "created_records": created_records
        })
    except Exception as e:
        return jsonify({"error": str(e)}), 500


@app.route("/api/extract", methods=["POST"])
def extract_route():
    try:
        data = request.get_json() or {}
        base_id = data.get("base_id") or get_base_id()
        products_table = data.get("products_table") or get_products_table()
        categories_table = data.get("categories_table") or get_categories_table()
        attributes_table = data.get("attributes_table") or get_attributes_table()

        try:
            from .extractor import run_extraction_cmd
        except ImportError:
            from airtable_json_uploader.extractor import run_extraction_cmd
        catalog = run_extraction_cmd(
            base_id=base_id,
            products_table=products_table,
            categories_table=categories_table,
            attributes_table=attributes_table,
        )
        return jsonify({
            "success": True,
            "product_count": len(catalog.get("products", [])),
            "category_count": len(catalog.get("categories", []))
        })
    except Exception as e:
        return jsonify({"error": str(e)}), 500


def main():
    import argparse
    import socket

    parser = argparse.ArgumentParser(description="Airtable JSON Uploader Web Application")
    parser.add_argument("-p", "--port", type=int, default=int(os.environ.get("PORT", 5050)), help="Port to run web server on (default: 5050)")
    args = parser.parse_args()

    port = args.port

    # Test if port is available; if busy, increment to next available port
    for attempt in range(20):
        with socket.socket(socket.AF_INET, socket.SOCK_STREAM) as s:
            if s.connect_ex(("127.0.0.1", port)) != 0:
                break
            port += 1

    print(f"\n=======================================================")
    print(f"   Airtable JSON Uploader Web Application")
    print(f"   Open in browser: http://127.0.0.1:{port}")
    print(f"=======================================================\n")
    app.run(host="0.0.0.0", port=port, debug=True)


if __name__ == "__main__":
    main()

