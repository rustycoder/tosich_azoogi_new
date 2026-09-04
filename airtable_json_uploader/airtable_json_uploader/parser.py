import json
import os
from typing import List, Dict, Any, Union, Tuple


def format_list_value(lst: List[Any]) -> Any:
    """Formats lists (strings, option dicts, attributes) into clean, serializable values."""
    if not lst:
        return ""

    # List of primitive types (strings, numbers)
    if all(isinstance(x, (str, int, float, bool)) for x in lst):
        return ", ".join(str(x) for x in lst)

    # List of dicts with 'name' (e.g. options items: [{"name": "6W", "id": "289"}, ...])
    if all(isinstance(x, dict) and "name" in x for x in lst):
        names = [str(x["name"]) for x in lst if "name" in x]
        return ", ".join(names)

    # List of dicts with 'name' and 'value' (e.g. attribute items)
    if all(isinstance(x, dict) and "name" in x and "value" in x for x in lst):
        return ", ".join(f"{x['name']}: {x['value']}" for x in lst)

    return json.dumps(lst, ensure_ascii=False)


def flatten_json(data: Any, prefix: str = "") -> Dict[str, Any]:
    """
    Extracts clean, meaningful JSON keys from a dictionary/list.
    Includes top-level containers ('product_features', 'options', 'constraints') for direct column mapping.
    """
    out = {}

    if isinstance(data, dict):
        for k, v in data.items():
            full_key = f"{prefix}.{k}" if prefix else str(k)

            if isinstance(v, (str, int, float, bool)) or v is None:
                out[full_key] = v
            elif isinstance(v, list):
                out[full_key] = format_list_value(v)
            elif isinstance(v, dict):
                sub_flat = flatten_json(v, full_key)
                out.update(sub_flat)
                # Store container dict formatted as clean JSON text for direct column mapping
                out[full_key] = json.dumps(v, ensure_ascii=False)

    elif isinstance(data, list):
        out[prefix] = format_list_value(data)

    return out




import csv


class ExcelParser:
    """Parser to load products from Excel (.xlsx, .xls) and CSV (.csv) files."""

    @staticmethod
    def _parse_cell_value(col_name: str, val: Any) -> Any:
        if val is None:
            return ""
        if isinstance(val, (int, float, bool)):
            return val

        val_str = str(val).strip()
        if not val_str:
            return ""

        # Try parsing JSON object or list if cell starts with { or [
        if (val_str.startswith("{") and val_str.endswith("}")) or (val_str.startswith("[") and val_str.endswith("]")):
            try:
                return json.loads(val_str)
            except Exception:
                pass

        # If column is Product gallery / images and contains comma-separated URLs
        if ("gallery" in col_name.lower() or "images" in col_name.lower()) and "," in val_str:
            urls = [u.strip() for u in val_str.split(",") if u.strip()]
            return urls

        return val_str

    @staticmethod
    def is_sku_empty(parsed_row: Dict[str, Any]) -> bool:
        """Returns True if the row has an empty SKU or missing SKU value."""
        if not parsed_row:
            return True

        sku_val = None
        found_sku_col = False

        # Priority 1: Check keys containing 'product code' or 'sku' (e.g., 'Product Code', 'SKU Code', 'SKU', 'Product SKU')
        for k, v in parsed_row.items():
            k_clean = str(k).strip().lower().replace("_", " ")
            if "product code" in k_clean or "sku" in k_clean:
                sku_val = v
                found_sku_col = True
                break

        # Priority 2: Item code / Code / Part number / Model
        if not found_sku_col:
            for k, v in parsed_row.items():
                k_clean = str(k).strip().lower().replace("_", " ")
                if k_clean in ["item code", "code", "part number", "part no", "model", "model number"]:
                    sku_val = v
                    found_sku_col = True
                    break

        if found_sku_col:
            if sku_val is None:
                return True
            if str(sku_val).strip() == "" or str(sku_val).strip().lower() in ["none", "null"]:
                return True
            return False

        # Priority 3: Fallback if no SKU column exists at all in the sheet
        # Check if the row has any non-empty cell value
        has_data = any(v is not None and str(v).strip() != "" and str(v).strip().lower() not in ["none", "null"] for v in parsed_row.values())
        return not has_data

    @staticmethod
    def load_csv_file(file_path: str) -> List[Dict[str, Any]]:
        """Loads products from a CSV file where column headers are keys."""
        items = []
        filename = os.path.basename(file_path)
        with open(file_path, "r", encoding="utf-8-sig") as f:
            reader = csv.DictReader(f)
            for row in reader:
                parsed_row = {}
                for k, v in row.items():
                    if k is not None:
                        clean_k = str(k).strip()
                        parsed_row[clean_k] = ExcelParser._parse_cell_value(clean_k, v)

                if parsed_row and not ExcelParser.is_sku_empty(parsed_row):
                    items.append({
                        "_source_file": filename,
                        "_raw": parsed_row,
                        "_flat": flatten_json(parsed_row)
                    })
        return items

    @staticmethod
    def load_excel_file(file_path: str) -> List[Dict[str, Any]]:
        """Loads products from an Excel (.xlsx, .xls) file using openpyxl or csv fallback."""
        if file_path.lower().endswith(".csv"):
            return ExcelParser.load_csv_file(file_path)

        filename = os.path.basename(file_path)
        items = []

        try:
            import openpyxl
            wb = openpyxl.load_workbook(file_path, data_only=True)
            ws = wb.active
            rows = list(ws.iter_rows(values_only=True))
            if not rows:
                return []

            headers = [str(cell).strip() if cell is not None else f"Column_{i+1}" for i, cell in enumerate(rows[0])]

            for row_vals in rows[1:]:
                if not any(v is not None for v in row_vals):
                    continue

                parsed_row = {}
                for i, val in enumerate(row_vals):
                    if i < len(headers):
                        clean_k = headers[i]
                        parsed_row[clean_k] = ExcelParser._parse_cell_value(clean_k, val)

                if parsed_row and not ExcelParser.is_sku_empty(parsed_row):
                    items.append({
                        "_source_file": filename,
                        "_raw": parsed_row,
                        "_flat": flatten_json(parsed_row)
                    })


        except ImportError:
            raise ImportError("openpyxl is required to parse Excel (.xlsx) files.")
        except Exception as e:
            raise Exception(f"Failed to parse Excel file '{filename}': {e}")

        return items


class JSONParser:
    """Parser to load and extract keys from JSON, Excel (.xlsx, .xls), and CSV files."""

    @staticmethod
    def load_json_file(file_path: str) -> List[Dict[str, Any]]:
        """Loads a single JSON, Excel, or CSV file, returning a list of flattened dicts."""
        if not os.path.exists(file_path):
            raise FileNotFoundError(f"File not found: {file_path}")

        if file_path.lower().endswith((".xlsx", ".xls", ".csv")):
            return ExcelParser.load_excel_file(file_path)

        with open(file_path, "r", encoding="utf-8") as f:
            data = json.load(f)

        items = []
        if isinstance(data, list):
            for obj in data:
                if isinstance(obj, dict):
                    items.append({
                        "_source_file": os.path.basename(file_path),
                        "_raw": obj,
                        "_flat": flatten_json(obj)
                    })
        elif isinstance(data, dict):
            list_key = next((k for k in ["products", "items", "data", "records", "rows"] if k in data and isinstance(data[k], list)), None)
            if list_key:
                for obj in data[list_key]:
                    if isinstance(obj, dict):
                        items.append({
                            "_source_file": os.path.basename(file_path),
                            "_raw": obj,
                            "_flat": flatten_json(obj)
                        })
            else:
                items.append({
                    "_source_file": os.path.basename(file_path),
                    "_raw": data,
                    "_flat": flatten_json(data)
                })

        return items

    @staticmethod
    def load_path(target_path: str) -> List[Dict[str, Any]]:
        """Loads a JSON, Excel, or CSV file or recursively scans a directory."""
        if os.path.isfile(target_path):
            if target_path.lower().endswith((".xlsx", ".xls", ".csv")):
                return ExcelParser.load_excel_file(target_path)
            else:
                return JSONParser.load_json_file(target_path)
        elif os.path.isdir(target_path):
            all_items = []
            for root, _, files in os.walk(target_path):
                for f in files:
                    if f.lower().endswith((".json", ".xlsx", ".xls", ".csv")):
                        full_p = os.path.join(root, f)
                        try:
                            if f.lower().endswith((".xlsx", ".xls", ".csv")):
                                items = ExcelParser.load_excel_file(full_p)
                            else:
                                items = JSONParser.load_json_file(full_p)
                            all_items.extend(items)
                        except Exception as e:
                            print(f"Skipping {f} due to parse error: {e}")
            return all_items
        else:
            raise ValueError(f"Invalid path: {target_path}")

    @staticmethod
    def extract_available_keys(items: List[Dict[str, Any]]) -> List[str]:
        """Extracts all unique flattened keys found across a set of parsed items."""
        keys = set()
        for item in items:
            flat = item.get("_flat", {})
            keys.update(flat.keys())
        return sorted(list(keys))

