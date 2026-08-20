import os
import json
import logging
from pathlib import Path
from typing import Dict, List, Any, Optional, Union
from .airtable_client import AirtableClient
from .config import (
    get_api_key,
    get_base_id,
    get_products_table,
    get_categories_table,
    get_attributes_table,
)

logger = logging.getLogger(__name__)


class AirtableDataExtractor:
    """Extracts, normalizes, and resolves Product, Category, and Attribute data from Airtable."""

    def __init__(
        self,
        api_key: Optional[str] = None,
        base_id: Optional[str] = None,
        products_table: Optional[str] = None,
        categories_table: Optional[str] = None,
        attributes_table: Optional[str] = None,
    ):
        self.api_key = api_key or get_api_key()
        self.base_id = base_id or get_base_id()
        self.products_table = products_table or get_products_table()
        self.categories_table = categories_table or get_categories_table()
        self.attributes_table = attributes_table or get_attributes_table()

        if not self.api_key:
            raise ValueError("AIRTABLE_API_KEY is required. Please set it in .env or pass it to extractor.")

        self.client = AirtableClient(api_key=self.api_key)

    def extract_image_urls(self, record_fields: Dict[str, Any]) -> List[str]:
        """Extract image URLs from Airtable Attachment fields or plain string/URL fields."""
        images = []
        image_field_keys = [
            "Images", "Product_Images", "Image", "Attachments", "Photos", "Media", "product_images",
            "Product image", "Product gallery", "Gallery", "Photo"
        ]

        for key, val in record_fields.items():
            is_target_key = key.lower() in [k.lower() for k in image_field_keys] or "image" in key.lower() or "attachment" in key.lower() or "gallery" in key.lower() or "photo" in key.lower()
            if is_target_key and "dimension" not in key.lower() and val:
                if isinstance(val, list):
                    for item in val:
                        if isinstance(item, dict) and "url" in item:
                            images.append(item["url"])
                        elif isinstance(item, str) and item.startswith("http"):
                            images.append(item)
                elif isinstance(val, str) and val.startswith("http"):
                    images.append(val)

        return list(dict.fromkeys(images))  # Deduplicate while keeping order

    def extract_attributes(self, attribute_records: List[Dict[str, Any]]) -> Dict[str, Dict[str, Any]]:
        """
        Build an index of Attribute Record ID -> Attribute details.
        Handles schemas with 'Attribute Name', 'Name', 'Value', 'Product', etc.
        """
        attr_map = {}
        for rec in attribute_records:
            rec_id = rec.get("id")
            fields = rec.get("fields", {})

            name = fields.get("Attribute_Name") or fields.get("Attribute Name") or fields.get("Name") or fields.get("Attribute") or ""
            val = fields.get("Attribute_Value") or fields.get("Attribute Value") or fields.get("Value") or fields.get("Option") or ""

            # Check for product link field (if attributes store product IDs)
            product_links = fields.get("Product") or fields.get("Products") or []
            if isinstance(product_links, str):
                product_links = [product_links]

            attr_map[rec_id] = {
                "id": rec_id,
                "name": str(name).strip(),
                "value": val,
                "product_ids": product_links,
                "raw_fields": fields
            }
        return attr_map

    @staticmethod
    def parse_json_field(val: Any, default_type: Any = None) -> Any:
        """Safely parse JSON strings into dicts/lists or return default."""
        if default_type is None:
            default_type = {}
        if isinstance(val, (dict, list)):
            return val
        if isinstance(val, str) and val.strip():
            try:
                return json.loads(val.strip())
            except Exception:
                pass
        return default_type

    @staticmethod
    def parse_attribute_keys(attr_keys_str: str) -> Dict[str, Any]:
        """
        Parse pipe-delimited Key:Value strings such as:
        "CCT:3000K|CCT:4000K|IP Rating:IP20|Power:30W|Voltage:24V"
        into structured dictionary {"CCT": ["3000K", "4000K"], "IP Rating": "IP20", ...}
        """
        result: Dict[str, Any] = {}
        if not isinstance(attr_keys_str, str) or not attr_keys_str.strip():
            return result

        parts = [p.strip() for p in attr_keys_str.split("|") if p.strip()]
        for part in parts:
            if ":" in part:
                key, val = part.split(":", 1)
                key = key.strip()
                val = val.strip()
                if not key or not val:
                    continue

                if key not in result:
                    result[key] = val
                else:
                    if isinstance(result[key], list):
                        if val not in result[key]:
                            result[key].append(val)
                    else:
                        if result[key] != val:
                            result[key] = [result[key], val]
        return result




    def sanitize_field_value(self, val: Any) -> Any:
        """Convert attachment dict objects containing 'url' into clean URL strings."""
        if isinstance(val, list):
            cleaned = []
            for item in val:
                if isinstance(item, dict) and "url" in item:
                    cleaned.append(item["url"])
                else:
                    cleaned.append(item)
            return cleaned
        elif isinstance(val, dict) and "url" in val:
            return val["url"]
        return val

    def run_extraction(self) -> Dict[str, Any]:
        """Fetch all tables from Airtable and compile normalized product catalog JSON."""
        if not self.base_id:
            raise ValueError("AIRTABLE_BASE_ID is required. Please set it in .env or pass it to extractor.")

        logger.info(f"Extracting data from Base ID: {self.base_id}")

        # 1. Fetch Products
        products_raw = []
        try:
            products_raw = self.client.fetch_existing_records(self.base_id, self.products_table)
            logger.info(f"Fetched {len(products_raw)} records from '{self.products_table}'")
        except Exception as e:
            logger.error(f"Failed to fetch products from '{self.products_table}': {e}")
            raise

        # 2. Fetch Attributes (Optional/Safe fallback)
        attributes_raw = []
        try:
            attributes_raw = self.client.fetch_existing_records(self.base_id, self.attributes_table)
            logger.info(f"Fetched {len(attributes_raw)} records from '{self.attributes_table}'")
        except Exception as e:
            logger.warning(f"Could not fetch attributes from '{self.attributes_table}' (ignoring if not present): {e}")

        # 3. Fetch Categories (Optional/Safe fallback)
        categories_raw = []
        try:
            categories_raw = self.client.fetch_existing_records(self.base_id, self.categories_table)
            logger.info(f"Fetched {len(categories_raw)} records from '{self.categories_table}'")
        except Exception as e:
            logger.warning(f"Could not fetch categories from '{self.categories_table}' (ignoring if not present): {e}")

        # Process Attributes Map
        attr_index = self.extract_attributes(attributes_raw)

        # Process Categories Map (if category table exists)
        cat_index = {}
        for cat_rec in categories_raw:
            c_id = cat_rec.get("id")
            c_fields = cat_rec.get("fields", {})
            cat_name = c_fields.get("Category_Name") or c_fields.get("Category Name") or c_fields.get("Name") or ""
            cat_index[c_id] = str(cat_name).strip()

        # Build Normalized Product Items
        catalog = {
            "categories": [],
            "products": [],
            "tree": []
        }

        category_groups: Dict[str, List[Dict[str, Any]]] = {}

        for p_rec in products_raw:
            p_id = p_rec.get("id")
            fields = p_rec.get("fields", {})

            # Product Name & Descriptions
            p_name = fields.get("Product_Name") or fields.get("Product Name") or fields.get("Name") or fields.get("Title") or "Unnamed Product"
            p_short_desc = fields.get("Product short description") or fields.get("Short description") or fields.get("short_description") or fields.get("Short Description") or ""
            p_long_desc = fields.get("Product long description") or fields.get("Product description") or fields.get("Long description") or fields.get("Description") or fields.get("description") or ""

            # Category resolution (Single Select / Text / Linked Record ID)
            raw_cat = fields.get("Category") or fields.get("Product Category") or fields.get("Categories") or "General"
            if isinstance(raw_cat, list) and raw_cat:
                first_cat = raw_cat[0]
                category_name = cat_index.get(first_cat, str(first_cat))
            else:
                category_name = str(raw_cat).strip() if raw_cat else "General"

            # Extract Attached Images
            images = self.extract_image_urls(fields)

            # Extract Options & Constraints (parsed JSON)
            raw_options = fields.get("Options") or fields.get("options") or {}
            options = self.parse_json_field(raw_options, default_type={})

            raw_constraints = fields.get("Constraints") or fields.get("constraints") or []
            constraints = self.parse_json_field(raw_constraints, default_type=[])

            # Extract Pipe-Separated Attributes Keys (e.g. "CCT:3000K|CCT:4000K|IP Rating:IP20")
            attr_keys_str = fields.get("Attributes keys") or fields.get("Attribute Keys") or fields.get("Attributes Keys") or ""
            parsed_attr_keys = self.parse_attribute_keys(attr_keys_str)

            # Build Product Features
            product_features = {}

            # 1. Start with parsed Attributes Keys
            product_features.update(parsed_attr_keys)

            # 2. Add linked attributes from attributes_table
            if attr_index:
                for f_key, f_val in fields.items():
                    if isinstance(f_val, list):
                        linked_attrs = [attr_index[item] for item in f_val if isinstance(item, str) and item in attr_index]
                        if linked_attrs:
                            for lattr in linked_attrs:
                                a_name = lattr["name"] or f_key
                                a_val = lattr["value"]
                                if a_name and a_val:
                                    if a_name not in product_features:
                                        product_features[a_name] = a_val
                                    elif isinstance(product_features[a_name], list):
                                        if a_val not in product_features[a_name]:
                                            product_features[a_name].append(a_val)
                                    else:
                                        if product_features[a_name] != a_val:
                                            product_features[a_name] = [product_features[a_name], a_val]

            # Extract Non-Attribute Columns for Top-Level Product Keys
            sku = self.sanitize_field_value(fields.get("SKU") or fields.get("sku") or fields.get("Supplier Code") or "")
            dimension_drawing = self.sanitize_field_value(fields.get("Product Dimension") or fields.get("Product dimension") or "")
            stocked_item = self.sanitize_field_value(fields.get("Stocked Item") or fields.get("Stock / Quantity") or "")
            datasheet = self.sanitize_field_value(fields.get("Datasheet") or "")
            product_icons = self.sanitize_field_value(fields.get("Product Icons") or "")
            meta_keywords = self.sanitize_field_value(fields.get("Meta Keywords") or fields.get("meta_keywords") or fields.get("Meta keywords") or fields.get("meta keywords") or "")
            supplier_name = self.sanitize_field_value(fields.get("Supplier Name") or "")
            status = self.sanitize_field_value(fields.get("Status") or "")
            product_type = self.sanitize_field_value(fields.get("Product type") or "")

            # 3. Add direct column attributes to product_features, excluding non-attribute meta keys
            excluded_meta_keys = [
                "product_name", "product name", "name", "title",
                "category", "product category", "categories",
                "images", "product_images", "attachments", "photos", "media",
                "options", "constraints", "attributes keys", "attribute keys", "attributes",
                "sku", "product dimension", "product_dimension", "product image", "product gallery",
                "product short description", "product long description", "product description", "short description",
                "stocked item", "stock / quantity", "status", "product type", "datasheet", "product icons",
                "meta keywords", "meta_keywords",
                "supplier code", "supplier name", "raw_fields"
            ]

            for f_key, f_val in fields.items():
                if f_key.lower() not in excluded_meta_keys and f_val not in [None, "", []]:
                    sanitized = self.sanitize_field_value(f_val)
                    if f_key not in product_features:
                        product_features[f_key] = sanitized

            # Also check Attributes table referencing Product ID
            if attr_index:
                for a_id, a_data in attr_index.items():
                    if p_id in a_data.get("product_ids", []):
                        a_name = a_data["name"]
                        a_val = a_data["value"]
                        if a_name and a_val:
                            if a_name not in product_features:
                                product_features[a_name] = a_val
                            elif isinstance(product_features[a_name], list):
                                if a_val not in product_features[a_name]:
                                    product_features[a_name].append(a_val)

            product_entry = {
                "id": p_id,
                "product_name": p_name,
                "category": category_name,
                "sku": sku,
                "product_short_description": p_short_desc,
                "product_description": p_long_desc,
                "product_images": images,
                "product_dimension": dimension_drawing,
                "stocked_item": stocked_item,
                "datasheet": datasheet,
                "product_icons": product_icons,
                "meta_keywords": meta_keywords,
                "supplier_name": supplier_name,
                "status": status,
                "product_type": product_type,
                "product_features": product_features,
                "options": options,
                "constraints": constraints
            }

            catalog["products"].append(product_entry)

            if category_name not in category_groups:
                category_groups[category_name] = []
            category_groups[category_name].append(product_entry)

        # Build Tree format matching AZOOGI_PRODUCTS structure from Categories table and products
        all_categories, tree_categories = self.build_category_tree(categories_raw, catalog["products"])

        catalog["tree"] = tree_categories
        catalog["categories"] = all_categories

        return catalog

    def build_category_tree(self, categories_raw: List[Dict[str, Any]], products_list: List[Dict[str, Any]]) -> tuple:
        """
        Builds hierarchical categories and category tree from Airtable Categories table records.
        Handles Parent / Child linked category records, as well as string path delimiters ('/', '>', '|').
        """
        cat_by_id = {}
        cat_by_name = {}

        # 1. First pass: Index all Category records from Categories table
        for cat_rec in categories_raw:
            c_id = cat_rec.get("id")
            c_fields = cat_rec.get("fields", {})

            name = (
                c_fields.get("Name") or
                c_fields.get("Category_Name") or
                c_fields.get("Category Name") or
                c_fields.get("Title") or
                c_fields.get("Category") or
                ""
            )
            name = str(name).strip()
            if not name:
                continue

            # Extract parent category references (linked record IDs or text names)
            parent_refs = (
                c_fields.get("Parent") or
                c_fields.get("Parent Category") or
                c_fields.get("Parent_Category") or
                c_fields.get("Parent_Category_Name") or
                c_fields.get("Parent Category Name") or
                []
            )
            if not isinstance(parent_refs, list):
                parent_refs = [parent_refs]

            # Extract child category references (linked record IDs or text names)
            child_refs = (
                c_fields.get("Child Categories") or
                c_fields.get("Subcategories") or
                c_fields.get("Children") or
                c_fields.get("Sub-Categories") or
                c_fields.get("Sub Categories") or
                []
            )
            if not isinstance(child_refs, list):
                child_refs = [child_refs]

            cat_info = {
                "id": c_id,
                "name": name,
                "parent_refs": [str(r).strip() for r in parent_refs if r],
                "child_refs": [str(r).strip() for r in child_refs if r],
                "parent_ids": set(),
                "child_ids": set(),
                "products": []
            }

            cat_by_id[c_id] = cat_info
            cat_by_name[name.lower()] = cat_info

        # 2. Second pass: Establish Parent-Child links
        for c_id, cat in cat_by_id.items():
            # Resolve parent references
            for pref in cat["parent_refs"]:
                if pref in cat_by_id:
                    cat["parent_ids"].add(pref)
                    cat_by_id[pref]["child_ids"].add(c_id)
                elif pref.lower() in cat_by_name:
                    parent_node = cat_by_name[pref.lower()]
                    cat["parent_ids"].add(parent_node["id"])
                    parent_node["child_ids"].add(c_id)

            # Resolve child references
            for cref in cat["child_refs"]:
                if cref in cat_by_id:
                    cat["child_ids"].add(cref)
                    cat_by_id[cref]["parent_ids"].add(c_id)
                elif cref.lower() in cat_by_name:
                    child_node = cat_by_name[cref.lower()]
                    cat["child_ids"].add(child_node["id"])
                    child_node["parent_ids"].add(c_id)

        # 3. Associate Products with Categories & compute Category Path
        category_product_map = {}
        all_cat_names = set()

        for prod in products_list:
            cat_val = prod.get("category") or "General"
            matched_cat = None

            if isinstance(cat_val, list) and cat_val:
                for c_item in cat_val:
                    c_str = str(c_item).strip()
                    if c_str in cat_by_id:
                        matched_cat = cat_by_id[c_str]
                        break
                    elif c_str.lower() in cat_by_name:
                        matched_cat = cat_by_name[c_str.lower()]
                        break
            else:
                c_str = str(cat_val).strip()
                if c_str in cat_by_id:
                    matched_cat = cat_by_id[c_str]
                elif c_str.lower() in cat_by_name:
                    matched_cat = cat_by_name[c_str.lower()]

            if matched_cat:
                cat_name = matched_cat["name"]
                prod["category"] = cat_name

                # Build category path [Parent, Child]
                path = []
                curr = matched_cat
                visited = set()
                while curr and curr["id"] not in visited:
                    visited.add(curr["id"])
                    path.insert(0, curr["name"])
                    if curr["parent_ids"]:
                        parent_id = list(curr["parent_ids"])[0]
                        curr = cat_by_id.get(parent_id)
                    else:
                        break

                prod["category_path"] = path
                matched_cat["products"].append(prod)
                all_cat_names.add(cat_name)
                if path and len(path) > 1:
                    all_cat_names.add(path[0])
            else:
                # Handle path delimiters if category name has '/' or '>'
                raw_cat_name = str(cat_val).strip()
                delimiters = [" > ", " / ", ">", "/"]
                path_parts = [raw_cat_name]
                for delim in delimiters:
                    if delim in raw_cat_name:
                        path_parts = [p.strip() for p in raw_cat_name.split(delim) if p.strip()]
                        break

                prod["category"] = path_parts[-1]
                prod["category_path"] = path_parts
                cat_name = path_parts[-1]
                all_cat_names.add(cat_name)
                if len(path_parts) > 1:
                    all_cat_names.add(path_parts[0])

                if cat_name not in category_product_map:
                    category_product_map[cat_name] = []
                category_product_map[cat_name].append(prod)

        # 4. Build Tree data structure
        tree_nodes = []

        if cat_by_id:
            # Find top-level categories (categories with no parent_ids)
            root_cats = [c for c in cat_by_id.values() if not c["parent_ids"]]
            if not root_cats:
                root_cats = list(cat_by_id.values())

            for root_cat in root_cats:
                root_node = self._build_category_node(root_cat, cat_by_id)
                if root_node:
                    tree_nodes.append(root_node)

        if not tree_nodes:
            # Fallback tree building from category_groups if no explicit category records
            for cat_name, prod_list in category_product_map.items():
                cat_node = {
                    "type": "category",
                    "name": cat_name,
                    "children": []
                }
                for prod in prod_list:
                    prod_row = {
                        "type": "product_row",
                        "name": prod["product_name"],
                        "variants": {
                            prod["product_name"]: prod
                        }
                    }
                    cat_node["children"].append(prod_row)
                tree_nodes.append(cat_node)

        all_categories = sorted(list(all_cat_names or [c["name"] for c in cat_by_id.values()]))
        return all_categories, tree_nodes

    def _build_category_node(self, cat_info: Dict[str, Any], cat_by_id: Dict[str, Any]) -> Dict[str, Any]:
        node = {
            "type": "category",
            "name": cat_info["name"],
            "children": []
        }

        # First add child categories
        for child_id in cat_info["child_ids"]:
            child_info = cat_by_id.get(child_id)
            if child_info:
                child_node = self._build_category_node(child_info, cat_by_id)
                if child_node:
                    node["children"].append(child_node)

        # Then add direct products
        for prod in cat_info["products"]:
            prod_row = {
                "type": "product_row",
                "name": prod["product_name"],
                "variants": {
                    prod["product_name"]: prod
                }
            }
            node["children"].append(prod_row)

        return node

    def save_outputs(self, catalog: Dict[str, Any], json_path: Path, js_path: Path) -> None:
        """Save extracted catalog to static JSON and compiled JS file."""
        json_path.parent.mkdir(parents=True, exist_ok=True)
        js_path.parent.mkdir(parents=True, exist_ok=True)

        # Write products.json
        with open(json_path, "w", encoding="utf-8") as f:
            json.dump(catalog, f, indent=2, ensure_ascii=False)
        logger.info(f"Saved extracted catalog JSON to {json_path}")

        # Write products_data.js
        js_content = f"// Azoogi Auto-Generated Product Database from Airtable\nconst AZOOGI_PRODUCTS = {json.dumps(catalog, indent=2, ensure_ascii=False)};\n"
        with open(js_path, "w", encoding="utf-8") as f:
            f.write(js_content)
        logger.info(f"Saved compiled JavaScript bundle to {js_path}")


def run_extraction_cmd(
    base_id: Optional[str] = None,
    products_table: Optional[str] = None,
    categories_table: Optional[str] = None,
    attributes_table: Optional[str] = None,
    output_json: Optional[str] = None,
    output_js: Optional[str] = None
) -> Dict[str, Any]:
    """Top-level helper function for CLI / API trigger."""
    extractor = AirtableDataExtractor(
        base_id=base_id,
        products_table=products_table,
        categories_table=categories_table,
        attributes_table=attributes_table,
    )
    catalog = extractor.run_extraction()

    # Determine default paths
    project_root = Path(__file__).parent.parent.parent
    json_path = Path(output_json) if output_json else project_root / "assets" / "data" / "products.json"
    js_path = Path(output_js) if output_js else project_root / "assets" / "js" / "products_data.js"

    extractor.save_outputs(catalog, json_path, js_path)
    return catalog
