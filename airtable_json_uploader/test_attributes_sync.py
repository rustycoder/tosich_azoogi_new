import os
import sys
import json
from airtable_json_uploader.config import get_api_key
from airtable_json_uploader.airtable_client import AirtableClient

TEST_PRODUCT_FEATURES = {
  "product_features": {
    "Material": [
      "ADC12",
      "spraying surface"
    ],
    "Driver Installation": "Remote",
    "Lighting direction": "downward",
    "Beam angle": [
      "15°",
      "24°",
      "36°",
      "45°"
    ],
    "UGR(X=4H, Y=8H)": [
      "≤8(15°/24°/36°)",
      "≤16(45° )"
    ],
    "Opening size": "φ55mm",
    "Dimming": [
      "Non",
      "0-10V",
      "Traic",
      "DALI"
    ],
    "SDCM(Light source)": "<3 Step",
    "CRI": "90",
    "CCT": [
      "2700K",
      "3000K",
      "3500K",
      "4000K",
      "2700-6500K"
    ],
    "Input voltage": "AC220-240V",
    "Application": "Indoor",
    "Installation": "Recessed",
    "IP Rating": "IP20",
    "Certification": [
      "CE",
      "UKCA",
      "ROHS",
      "Reach"
    ],
    "Warranty": "5 years"
  }
}


def run_test(base_id: str, attr_table_name: str = "Product Attributes"):
    api_key = get_api_key()
    if not api_key:
        print("Error: AIRTABLE_API_KEY is missing in .env")
        sys.exit(1)

    print(f"\n=======================================================")
    print(f" TESTING ATTRIBUTES SYNC IN '{attr_table_name}'")
    print(f" Base ID: {base_id}")
    print(f"=======================================================\n")

    client = AirtableClient(api_key=api_key)

    print("Step 1: Extracting attribute pairs from test JSON...")
    # Preview extracted pairs
    features = TEST_PRODUCT_FEATURES["product_features"]
    attr_pairs = []
    for k, v in features.items():
        if isinstance(v, list):
            for sub_v in v:
                attr_pairs.append((k, str(sub_v)))
        else:
            attr_pairs.append((k, str(v)))

    print(f"Extracted {len(attr_pairs)} attribute entries:")
    for idx, (name, val) in enumerate(attr_pairs, 1):
        print(f"  [{idx:02d}] Attribute Name: '{name}' | Attribute Value: '{val}'")

    print(f"\nStep 2: Syncing entries to Airtable table '{attr_table_name}'...")
    rec_ids = client.sync_linked_attributes(base_id, attr_table_name, TEST_PRODUCT_FEATURES)

    print(f"\n✓ SUCCESS! Received {len(rec_ids)} Record IDs from '{attr_table_name}':")
    for r_id in rec_ids:
        print(f"  - {r_id}")

    return rec_ids


if __name__ == "__main__":
    import argparse
    parser = argparse.ArgumentParser(description="Test attribute sync in Product Attributes table")
    parser.add_argument("--base-id", required=True, help="Airtable Base ID (e.g. appLf2XiNXE2Y32FK)")
    parser.add_argument("--attr-table", default="Product attributes", help="Product attributes table name")
    args = parser.parse_args()

    run_test(args.base_id, args.attr_table)
