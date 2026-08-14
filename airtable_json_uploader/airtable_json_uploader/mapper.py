import json
import os
from typing import Dict, Any, List, Optional


class MappingProfile:
    """Manages mapping between JSON keys and Airtable column names."""

    def __init__(self, mapping: Optional[Dict[str, str]] = None):
        # mapping format: {"json_key_path": "Airtable Column Name"}
        self.mapping = mapping if mapping else {}

    def set_mapping(self, json_key: str, column_name: str) -> None:
        if column_name:
            self.mapping[json_key] = column_name
        elif json_key in self.mapping:
            del self.mapping[json_key]

    def save(self, filepath: str) -> None:
        """Saves current mapping profile to a JSON file."""
        os.makedirs(os.path.dirname(os.path.abspath(filepath)), exist_ok=True)
        with open(filepath, "w", encoding="utf-8") as f:
            json.dump(self.mapping, f, indent=2)
        print(f"Mapping profile saved to: {filepath}")

    @classmethod
    def load(cls, filepath: str) -> "MappingProfile":
        """Loads a mapping profile from a JSON file."""
        if not os.path.exists(filepath):
            raise FileNotFoundError(f"Mapping profile not found: {filepath}")
        with open(filepath, "r", encoding="utf-8") as f:
            mapping = json.load(f)
        return cls(mapping)

    def apply(self, flat_item: Dict[str, Any]) -> Dict[str, Any]:
        """Translates a flattened JSON object into an Airtable 'fields' dictionary."""
        fields = {}
        for json_key, airtable_col in self.mapping.items():
            if not airtable_col:
                continue

            # Retrieve value from flat_item
            if json_key in flat_item:
                val = flat_item[json_key]
                # Format or sanitize if needed (e.g. lists or single values)
                if isinstance(val, list) and not val:
                    continue
                fields[airtable_col] = val
        return fields
