import os
import json
import logging
import hashlib
import ssl
import urllib.request
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

            name = (
                fields.get("Attribute name") or
                fields.get("Attribute_Name") or
                fields.get("Attribute Name") or
                fields.get("Name") or
                fields.get("Attribute") or
                ""
            )
            val = (
                fields.get("Term Name") or
                fields.get("Attribute Value") or
                fields.get("Attribute_Value") or
                fields.get("Value") or
                fields.get("Option") or
                fields.get("Term Value") or
                ""
            )

            # Extract Attribute Icon URL
            icon_field = (
                fields.get("Attribute Icon") or
                fields.get("Attribute_Icon") or
                fields.get("Attribute icon") or
                fields.get("Icon") or
                fields.get("attribute_icon") or
                fields.get("attribute icon") or
                ""
            )
            icon_url = ""
            if isinstance(icon_field, list) and icon_field:
                first_icon = icon_field[0]
                if isinstance(first_icon, dict) and "url" in first_icon:
                    icon_url = first_icon["url"]
                elif isinstance(first_icon, str) and first_icon.startswith("http"):
                    icon_url = first_icon
            elif isinstance(icon_field, dict) and "url" in icon_field:
                icon_url = icon_field["url"]
            elif isinstance(icon_field, str) and icon_field.startswith("http"):
                icon_url = icon_field

            # Check for product link field (if attributes store product IDs)
            product_links = (
                fields.get("Simple Products") or
                fields.get("Product") or
                fields.get("Products") or
                []
            )
            if isinstance(product_links, str):
                product_links = [product_links]

            attr_map[rec_id] = {
                "id": rec_id,
                "name": str(name).strip(),
                "value": str(val).strip() if val is not None else "",
                "icon": icon_url,
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
    def parse_attribute_keys(attr_keys_str: str) -> Dict[str, List[Dict[str, str]]]:
        """
        Parse pipe-delimited Key:Value strings such as:
        "CCT:3000K|CCT:4000K|IP Rating:IP20|Power:30W|Voltage:24V"
        into structured dictionary {"CCT": [{"value": "3000K", "icon": ""}, {"value": "4000K", "icon": ""}], ...}
        """
        result: Dict[str, List[Dict[str, str]]] = {}
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
                    result[key] = []
                
                existing_vals = [item["value"] for item in result[key] if isinstance(item, dict) and "value" in item]
                if val not in existing_vals:
                    result[key].append({"value": val, "icon": ""})
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

        # Process Attributes Map & Fast Lookup Cache
        attr_index = self.extract_attributes(attributes_raw)
        attr_lookup = {}
        for a_id, a_info in attr_index.items():
            key = (a_info["name"].strip().lower(), str(a_info["value"]).strip().lower())
            if a_info.get("icon"):
                attr_lookup[key] = a_info["icon"]

        # Process Categories Map (if category table exists)
        cat_index = {}
        for cat_rec in categories_raw:
            c_id = cat_rec.get("id")
            c_fields = cat_rec.get("fields", {})
            cat_name = c_fields.get("Category_Name") or c_fields.get("Category Name") or c_fields.get("Name") or ""
            cat_index[c_id] = str(cat_name).strip()

        # Helper to safely add attribute entries into product_features dictionary as list of {"value": ..., "icon": ...}
        def add_feature_item(features_dict: Dict[str, List[Dict[str, str]]], name: str, val: Any, icon: str = ""):
            if not name or val is None or val == "":
                return
            name = str(name).strip()
            if isinstance(val, list):
                for sub in val:
                    add_feature_item(features_dict, name, sub, icon)
                return

            if isinstance(val, dict):
                v_str = str(val.get("value", "")).strip()
                i_str = val.get("icon", icon) or icon
                if v_str:
                    add_feature_item(features_dict, name, v_str, i_str)
                return

            v_str = str(val).strip()
            if not v_str:
                return

            if not icon and attr_lookup:
                icon = attr_lookup.get((name.lower(), v_str.lower()), "")

            if name not in features_dict:
                features_dict[name] = []

            existing_vals = [item["value"] for item in features_dict[name] if isinstance(item, dict) and "value" in item]
            if v_str not in existing_vals:
                features_dict[name].append({"value": v_str, "icon": icon or ""})
            else:
                if icon:
                    for item in features_dict[name]:
                        if isinstance(item, dict) and item.get("value") == v_str and not item.get("icon"):
                            item["icon"] = icon

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
            product_features: Dict[str, List[Dict[str, str]]] = {}

            # 1. Start with parsed Attributes Keys
            for k, items in parsed_attr_keys.items():
                for item in items:
                    add_feature_item(product_features, k, item["value"], item.get("icon", ""))

            # 2. Add linked attributes from attributes_table
            if attr_index:
                for f_key, f_val in fields.items():
                    if isinstance(f_val, list):
                        linked_attrs = [attr_index[item] for item in f_val if isinstance(item, str) and item in attr_index]
                        if linked_attrs:
                            for lattr in linked_attrs:
                                a_name = lattr["name"] or f_key
                                a_val = lattr["value"]
                                a_icon = lattr.get("icon", "")
                                add_feature_item(product_features, a_name, a_val, a_icon)

            # Extract Non-Attribute Columns for Top-Level Product Keys
            product_code = self.sanitize_field_value(
                fields.get("Product Code") or 
                fields.get("Product code") or 
                fields.get("product_code") or ""
            )
            raw_sku_mappings = (
                fields.get("SKU Mappings") or 
                fields.get("SKU mappings") or 
                fields.get("sku_mappings") or 
                fields.get("SKU Mapping") or 
                fields.get("sku_mapping") or {}
            )
            sku_mappings = self.parse_json_field(raw_sku_mappings, default_type={})

            dimension_drawing = self.sanitize_field_value(fields.get("Product Dimension") or fields.get("Product dimension") or "")
            stocked_item = self.sanitize_field_value(fields.get("Stocked Item") or fields.get("Stock / Quantity") or "")
            datasheet = self.sanitize_field_value(fields.get("Datasheet") or "")
            technical_icons = self.sanitize_field_value(fields.get("Technical Icons") or fields.get("Technical icons") or fields.get("Technical_Icons") or fields.get("Product Icons") or fields.get("Product icons") or "")
            meta_keywords = self.sanitize_field_value(fields.get("Meta Keywords") or fields.get("meta_keywords") or fields.get("Meta keywords") or fields.get("meta keywords") or "")
            supplier_name = self.sanitize_field_value(fields.get("Supplier Name") or "")
            status = self.sanitize_field_value(fields.get("Status") or "")
            product_type = self.sanitize_field_value(fields.get("Product type") or "")

            # Also check Attributes table referencing Product ID
            if attr_index:
                for a_id, a_data in attr_index.items():
                    if p_id in a_data.get("product_ids", []):
                        a_name = a_data["name"]
                        a_val = a_data["value"]
                        a_icon = a_data.get("icon", "")
                        add_feature_item(product_features, a_name, a_val, a_icon)

            product_entry = {
                "id": p_id,
                "product_name": p_name,
                "category": category_name,
                "product_code": product_code,
                "sku_mappings": sku_mappings,
                "product_short_description": p_short_desc,
                "product_description": p_long_desc,
                "product_images": images,
                "product_dimension": dimension_drawing,
                "stocked_item": stocked_item,
                "datasheet": datasheet,
                "technical_icons": technical_icons,
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

        # Localize expiring Airtable image URLs to permanent static assets
        self.localize_catalog_images(catalog)

        return catalog

    def localize_image_url(self, url: str, media_root: Path, prefix: str = "img", subfolder: str = "products") -> str:
        """Downloads remote Airtable image URL to local media_root / subfolder and returns relative static path with exact file format."""
        if not url or not isinstance(url, str) or not url.startswith("http"):
            return url

        # Determine extension from URL path if available
        ext = None
        clean_url = url.split("?")[0]
        filename_part = clean_url.split("/")[-1]
        if "." in filename_part:
            possible_ext = "." + filename_part.split(".")[-1].lower()
            if possible_ext in [".jpg", ".jpeg", ".png", ".webp", ".gif", ".svg"]:
                ext = possible_ext

        url_hash = hashlib.md5(clean_url.encode("utf-8")).hexdigest()[:10]
        safe_prefix = "".join(c for c in prefix if c.isalnum() or c in "_-") or "prod"

        target_dir = media_root / subfolder
        target_dir.mkdir(parents=True, exist_ok=True)

        try:
            ctx = ssl._create_unverified_context()
            req = urllib.request.Request(
                url,
                headers={"User-Agent": "Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7)"}
            )
            with urllib.request.urlopen(req, timeout=30, context=ctx) as resp:
                content_type = resp.headers.get("Content-Type", "").lower()
                payload = resp.read()

                # Detect format from Content-Type or payload byte signature
                if "svg" in content_type or payload.strip().startswith(b"<?xml") or b"<svg" in payload[:300].lower():
                    ext = ".svg"
                elif "png" in content_type or payload.startswith(b"\x89PNG"):
                    ext = ".png"
                elif "webp" in content_type or (payload.startswith(b"RIFF") and b"WEBP" in payload[:20]):
                    ext = ".webp"
                elif "gif" in content_type or payload.startswith(b"GIF8"):
                    ext = ".gif"
                elif "jpeg" in content_type or "jpg" in content_type or payload.startswith(b"\xff\xd8\xff"):
                    ext = ".jpg"
                elif not ext:
                    ext = ".jpg"

                # Remove any stale file with same prefix and hash but different extension
                for old_ext in [".jpg", ".jpeg", ".png", ".webp", ".gif", ".svg"]:
                    if old_ext != ext:
                        stale_file = target_dir / f"{safe_prefix}_{url_hash}{old_ext}"
                        if stale_file.exists():
                            try:
                                stale_file.unlink()
                                logger.info(f"Cleaned up stale icon/image file {stale_file}")
                            except Exception:
                                pass

                filename = f"{safe_prefix}_{url_hash}{ext}"
                local_file_path = target_dir / filename
                relative_path = f"assets/img/{subfolder}/{filename}"

                # Strip white background rects from SVG payloads for transparent backgrounds
                if ext == ".svg":
                    try:
                        import re
                        svg_text = payload.decode("utf-8", errors="ignore")
                        cleaned_svg = re.sub(r'<rect[^>]*fill=["\'](?:#ffffff|#fff|white|rgb\(255,\s*255,\s*255\))["\'][^>]*/>', '', svg_text, flags=re.IGNORECASE)
                        payload = cleaned_svg.encode("utf-8")
                    except Exception as ex:
                        logger.warning(f"Could not strip SVG white background rect: {ex}")

                # Always overwrite local file with fresh payload from Airtable
                with open(local_file_path, "wb") as f:
                    f.write(payload)
                logger.info(f"Downloaded and overwrote asset {relative_path} (Format: {ext})")
                return relative_path

        except Exception as e:
            fallback_ext = ext or ".jpg"
            filename = f"{safe_prefix}_{url_hash}{fallback_ext}"
            local_file_path = target_dir / filename
            relative_path = f"assets/img/{subfolder}/{filename}"
            logger.warning(f"Failed to download image from {url}: {e}")
            if local_file_path.exists():
                return relative_path

        return relative_path

    def localize_catalog_images(self, catalog: Dict[str, Any], media_root: Optional[Path] = None, clear_existing: bool = True) -> None:
        """Traverse catalog dictionary and replace expiring Airtable image URLs with local static image paths."""
        if media_root is None:
            project_root = Path(__file__).parent.parent.parent
            media_root = project_root / "public" / "assets" / "img"

        # Clear existing images and icons folders to perform a 100% fresh download
        if clear_existing:
            for subfolder in ["products", "icons", "attribute_icon"]:
                target_dir = media_root / subfolder
                if target_dir.exists():
                    for file_path in target_dir.glob("*"):
                        if file_path.is_file():
                            try:
                                file_path.unlink()
                            except Exception as e:
                                logger.warning(f"Could not delete {file_path}: {e}")
                    logger.info(f"Cleared all existing files in {target_dir}")

        url_cache: Dict[tuple, str] = {}

        def process_url(url_val: str, prefix_str: str, subfolder: str = "products") -> str:
            if not url_val or not isinstance(url_val, str) or not url_val.startswith("http"):
                return url_val
            cache_key = (url_val, subfolder)
            if cache_key not in url_cache:
                url_cache[cache_key] = self.localize_image_url(url_val, media_root, prefix=prefix_str, subfolder=subfolder)
            return url_cache[cache_key]

        def process_url_list(urls: Any, prefix_str: str, subfolder: str = "products") -> Any:
            if isinstance(urls, list):
                return [process_url(u, prefix_str, subfolder) for u in urls]
            elif isinstance(urls, str):
                return process_url(urls, prefix_str, subfolder)
            return urls

        def process_product_features(features: Dict[str, Any]) -> None:
            if not isinstance(features, dict):
                return
            for fname, fitems in features.items():
                if isinstance(fitems, list):
                    for item in fitems:
                        if isinstance(item, dict) and "icon" in item and item["icon"]:
                            raw_icon = item["icon"]
                            if isinstance(raw_icon, str) and raw_icon.startswith("http"):
                                val_clean = str(item.get("value", "")).replace(" ", "_").replace("/", "_")
                                name_clean = str(fname).replace(" ", "_").replace("/", "_")
                                prefix = f"attr_{name_clean.lower()}_{val_clean.lower()}"
                                item["icon"] = process_url(raw_icon, prefix, "attribute_icon")

        # 1. Process top-level products
        for prod in catalog.get("products", []):
            p_id = prod.get("id") or prod.get("product_code") or prod.get("sku") or "product"
            if "product_images" in prod:
                prod["product_images"] = process_url_list(prod["product_images"], str(p_id), "products")
            if "product_dimension" in prod:
                prod["product_dimension"] = process_url_list(prod["product_dimension"], f"{p_id}_dim", "products")
            if "technical_icons" in prod:
                prod["technical_icons"] = process_url_list(prod["technical_icons"], f"{p_id}_icon", "icons")
            elif "product_icons" in prod:
                prod["product_icons"] = process_url_list(prod["product_icons"], f"{p_id}_icon", "icons")
            if "product_features" in prod:
                process_product_features(prod["product_features"])

        # 2. Process tree nodes recursively
        def process_tree_node(node: Dict[str, Any]) -> None:
            if not isinstance(node, dict):
                return
            if node.get("variants"):
                for vname, vdata in node["variants"].items():
                    p_id = (vdata.get("id") if isinstance(vdata, dict) else None) or vname
                    if isinstance(vdata, dict):
                        if "product_images" in vdata:
                            vdata["product_images"] = process_url_list(vdata["product_images"], str(p_id), "products")
                        if "product_dimension" in vdata:
                            vdata["product_dimension"] = process_url_list(vdata["product_dimension"], f"{p_id}_dim", "products")
                        if "technical_icons" in vdata:
                            vdata["technical_icons"] = process_url_list(vdata["technical_icons"], f"{p_id}_icon", "icons")
                        elif "product_icons" in vdata:
                            vdata["product_icons"] = process_url_list(vdata["product_icons"], f"{p_id}_icon", "icons")
                        if "product_features" in vdata:
                            process_product_features(vdata["product_features"])
            if node.get("children"):
                for child in node["children"]:
                    process_tree_node(child)

        for tree_root in catalog.get("tree", []):
            process_tree_node(tree_root)

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
    json_path = Path(output_json) if output_json else project_root / "public" / "assets" / "data" / "products.json"
    js_path = Path(output_js) if output_js else project_root / "public" / "assets" / "js" / "products_data.js"

    extractor.save_outputs(catalog, json_path, js_path)

    # Auto-bump script cache version across HTML files
    try:
        version_script = project_root / "update_version.py"
        if version_script.exists():
            import importlib.util
            spec = importlib.util.spec_from_file_location("update_version", version_script)
            uv_mod = importlib.util.module_from_spec(spec)
            spec.loader.exec_module(uv_mod)
            old_v, new_v, updated_files = uv_mod.update_cache_version("bump")
            logger.info(f"Auto-bumped asset cache version: v{old_v} -> v{new_v}")
    except Exception as err:
        logger.warning(f"Could not auto-bump HTML cache version: {err}")

    return catalog
