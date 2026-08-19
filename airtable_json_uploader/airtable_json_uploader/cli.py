import argparse
import os
import sys
from typing import List, Dict, Any, Optional
from .config import get_api_key, save_api_key
from .airtable_client import AirtableClient
from .parser import JSONParser
from .mapper import MappingProfile


def prompt_select_item(options: List[Dict[str, Any]], display_key: str, title: str) -> Dict[str, Any]:
    """Helper to display a numbered list and prompt user for selection."""
    print(f"\n--- {title} ---")
    for idx, opt in enumerate(options, 1):
        disp_val = opt.get(display_key, f"Item {idx}")
        opt_id = opt.get("id", "")
        print(f" [{idx}] {disp_val}" + (f" ({opt_id})" if opt_id else ""))

    while True:
        choice = input("\nEnter choice number: ").strip()
        if choice.isdigit():
            val = int(choice)
            if 1 <= val <= len(options):
                return options[val - 1]
        print("Invalid selection. Please try again.")


def interactive_key_mapping(json_keys: List[str], table_columns: List[str]) -> MappingProfile:
    """Interactively map JSON keys to Airtable table columns."""
    print("\n==============================================")
    print("       INTERACTIVE COLUMN MAPPING SETUP       ")
    print("==============================================")
    print("Map your JSON fields to the corresponding Airtable base columns.")
    print("Type the number of the column, or press Enter to skip a JSON field.\n")

    print("Available Airtable Columns:")
    for idx, col in enumerate(table_columns, 1):
        print(f"  [{idx}] {col}")
    print("  [0] (Skip this key)")

    mapping = {}
    for j_key in json_keys:
        # Check auto-match suggestion (case-insensitive exact match)
        suggested_idx = 0
        for idx, col in enumerate(table_columns, 1):
            if col.lower() == j_key.lower() or col.lower().replace(" ", "_") == j_key.lower().replace(" ", "_"):
                suggested_idx = idx
                break

        prompt_str = f"\nJSON Key: '{j_key}'"
        if suggested_idx > 0:
            prompt_str += f" [Default suggestion: {suggested_idx} ({table_columns[suggested_idx - 1]})]"
        prompt_str += "\nSelect Airtable Column #: "

        choice = input(prompt_str).strip()
        if not choice and suggested_idx > 0:
            choice = str(suggested_idx)

        if choice.isdigit():
            c_idx = int(choice)
            if 1 <= c_idx <= len(table_columns):
                matched_col = table_columns[c_idx - 1]
                mapping[j_key] = matched_col
                print(f" -> Mapped '{j_key}' => '{matched_col}'")
            else:
                print(f" -> Skipped '{j_key}'")
        else:
            print(f" -> Skipped '{j_key}'")

    profile = MappingProfile(mapping)

    save_opt = input("\nWould you like to save this mapping profile for future re-use? (y/n): ").strip().lower()
    if save_opt == "y":
        prof_name = input("Enter mapping profile filename (e.g. product_mapping.json): ").strip()
        if not prof_name:
            prof_name = "mapping_profile.json"
        if not prof_name.endswith(".json"):
            prof_name += ".json"
        save_path = os.path.join(os.getcwd(), "mappings", prof_name)
        profile.save(save_path)

    return profile


def match_and_prepare_updates(
    items: List[Dict[str, Any]],
    mapping_profile: MappingProfile,
    existing_records: List[Dict[str, Any]],
    primary_key_col: str
) -> List[Dict[str, Any]]:
    """Matches parsed JSON items with existing Airtable records and prepares update payloads."""
    existing_by_key = {}
    for rec in existing_records:
        rec_fields = rec.get("fields", {})
        val = rec_fields.get(primary_key_col)
        if val:
            existing_by_key[str(val).strip().lower()] = rec["id"]

    updates = []
    skipped = 0

    # Reverse lookup to find which JSON key maps to the primary key column
    primary_json_key = None
    for j_key, col_name in mapping_profile.mapping.items():
        if col_name == primary_key_col:
            primary_json_key = j_key
            break

    for item in items:
        flat = item.get("_flat", {})
        mapped_fields = mapping_profile.apply(flat)

        if not mapped_fields:
            skipped += 1
            continue

        target_rec_id = None

        if primary_json_key and primary_json_key in flat:
            match_val = str(flat[primary_json_key]).strip().lower()
            target_rec_id = existing_by_key.get(match_val)

        if target_rec_id:
            updates.append({
                "id": target_rec_id,
                "fields": mapped_fields
            })
        else:
            skipped += 1

    return updates


def main():
    parser = argparse.ArgumentParser(description="Airtable JSON Uploader CLI")
    parser.add_argument("--api-key", help="Airtable Personal Access Token (stored in .env if omitted)")
    parser.add_argument("--workspace-id", help="Airtable Workspace ID")
    parser.add_argument("--base-id", help="Airtable Base ID")
    parser.add_argument("--table", help="Target Airtable Table Name")
    parser.add_argument("--json-path", help="Path to JSON file or directory containing JSON files")
    parser.add_argument("--mapping", help="Path to saved mapping profile (.json file)")
    parser.add_argument("--dry-run", action="store_true", help="Preview updates without sending request to Airtable")
    args = parser.parse_args()

    # Step 1: Manage Security & API Key
    api_key = args.api_key or get_api_key()
    if not api_key:
        print("\n[!] Airtable Personal Access Token not found in .env.")
        api_key = input("Please enter your Personal Access Token (pat...): ").strip()
        if not api_key:
            print("Error: API Key is required.")
            sys.exit(1)
        save_api_key(api_key)
        print(" -> Token saved to .env securely.")

    client = AirtableClient(api_key=api_key)

    # Fetch Bases & Workspaces
    try:
        all_bases, all_workspaces, base_to_wsp = client.get_workspaces_and_bases()
        if not all_bases:
            print("No bases accessible with this token.")
            sys.exit(1)
    except Exception as e:
        print(f"Error listing bases/workspaces: {e}")
        sys.exit(1)

    # Step 2: Workspace Selection
    workspace_id = args.workspace_id
    filtered_bases = all_bases

    if not args.base_id:
        if all_workspaces:
            wsp_options = []
            for wsp in all_workspaces:
                w_id = wsp.get("id")
                w_name = wsp.get("name", f"Workspace {w_id}")
                b_count = len(wsp.get("baseIds", []))
                wsp_options.append({
                    "id": w_id,
                    "name": f"{w_name} ({b_count} base{'s' if b_count != 1 else ''})",
                    "baseIds": set(wsp.get("baseIds", []))
                })
            wsp_options.append({"id": "all", "name": "All Workspaces (Show all bases)", "baseIds": set()})

            if not workspace_id:
                selected_wsp = prompt_select_item(wsp_options, "name", "Select Workspace")
                if selected_wsp["id"] != "all":
                    workspace_id = selected_wsp["id"]
                    target_base_ids = selected_wsp["baseIds"]
                    filtered_bases = [b for b in all_bases if b["id"] in target_base_ids]
                    print(f"\nFiltered to Workspace: '{selected_wsp['name']}' ({len(filtered_bases)} base(s))")
        else:
            # Fallback: check workspaceId property on bases
            workspaces_map = {}
            for b in all_bases:
                w_id = b.get("workspaceId", "default")
                workspaces_map.setdefault(w_id, []).append(b)

            if len(workspaces_map) > 1 and not workspace_id:
                wsp_options = [{"id": w_id, "name": f"Workspace {w_id} ({len(b_list)} base{'s' if len(b_list)>1 else ''})"} for w_id, b_list in workspaces_map.items()]
                wsp_options.append({"id": "all", "name": "All Workspaces (Show all bases)"})
                selected_wsp = prompt_select_item(wsp_options, "name", "Select Workspace")
                if selected_wsp["id"] != "all":
                    workspace_id = selected_wsp["id"]

            if workspace_id and workspace_id != "all":
                filtered_bases = [b for b in all_bases if b.get("workspaceId") == workspace_id]
                print(f"\nFiltered to Workspace: {workspace_id} ({len(filtered_bases)} base(s))")


    # Step 3: Base Selection
    base_id = args.base_id
    if not base_id:
        if not filtered_bases:
            print(f"No bases found in workspace '{workspace_id}'.")
            sys.exit(1)

        # Enrich base objects with workspace tag for clear display
        for b in filtered_bases:
            w_name = base_to_wsp.get(b["id"])
            if w_name:
                b["display_title"] = f"{b['name']}  (Workspace: {w_name})"
            else:
                b["display_title"] = b["name"]

        selected_base = prompt_select_item(filtered_bases, "display_title", "Select Workspace Base")
        base_id = selected_base["id"]

    print(f"\nUsing Base ID: {base_id}")



    # Step 3: Table Selection
    table_name = args.table
    table_columns = []
    if not table_name:
        try:
            tables = client.list_tables(base_id)
            if not tables:
                print(f"No tables found in base {base_id}.")
                sys.exit(1)
            selected_table = prompt_select_item(tables, "name", "Select Airtable Table")
            table_name = selected_table["name"]
            table_columns = [f["name"] for f in selected_table.get("fields", [])]
        except Exception as e:
            print(f"Error listing tables: {e}")
            sys.exit(1)
    else:
        # Fetch columns for explicit table name
        tables = client.list_tables(base_id)
        for t in tables:
            if t["name"] == table_name or t["id"] == table_name:
                table_columns = [f["name"] for f in t.get("fields", [])]
                break

    print(f"Selected Table: {table_name}")

    # Step 4: JSON Path & Folder Selection
    json_path = args.json_path
    json_dir = os.path.join(os.getcwd(), "json_files")
    os.makedirs(json_dir, exist_ok=True)

    if not json_path:
        # Discover json files in default drop folder and current working directory
        discovered_files = []
        seen_names = set()
        for root_dir in [json_dir, os.getcwd()]:
            if os.path.exists(root_dir):
                for f in os.listdir(root_dir):
                    if f.lower().endswith(".json") and f not in seen_names:
                        full_p = os.path.join(root_dir, f)
                        if os.path.isfile(full_p):
                            seen_names.add(f)
                            discovered_files.append({"name": f, "path": full_p})

        print(f"\nJSON Drop Folder: {json_dir}")

        json_options = []
        if discovered_files:
            json_options.append({
                "id": "ALL",
                "name": f"Process ALL JSON files in folder ({len(discovered_files)} file{'s' if len(discovered_files)>1 else ''} found)",
                "path": json_dir
            })
            for df in discovered_files:
                json_options.append({
                    "id": df["path"],
                    "name": f"File: {df['name']}",
                    "path": df["path"]
                })

        json_options.append({
            "id": "CUSTOM",
            "name": "Specify a custom file or folder path manually...",
            "path": ""
        })

        selected_json = prompt_select_item(json_options, "name", "Select JSON Source")

        if selected_json["id"] == "CUSTOM":
            user_input_path = input("Enter path to JSON file or folder: ").strip()
            if not os.path.exists(user_input_path):
                # Search inside json_dir or project workspace recursively for typed filename
                target_filename = os.path.basename(user_input_path)
                found_path = None
                for r, _, files in os.walk(os.getcwd()):
                    if target_filename in files:
                        found_path = os.path.join(r, target_filename)
                        break
                if found_path:
                    user_input_path = found_path
                    print(f" -> Resolved path to: {user_input_path}")
                else:
                    print(f"Error: Path does not exist: {user_input_path}")
                    sys.exit(1)
            json_path = user_input_path
        else:
            json_path = selected_json["path"]

    if not os.path.exists(json_path):
        print(f"Error: Path does not exist: {json_path}")
        sys.exit(1)

    print(f"\nParsing JSON from: {json_path}")
    parsed_items = JSONParser.load_path(json_path)
    if not parsed_items:
        print("No valid JSON product items found.")
        sys.exit(1)
    print(f"Loaded {len(parsed_items)} item(s) from JSON source.")


    json_keys = JSONParser.extract_available_keys(parsed_items)

    # Step 5: Column Mapping Profile Selection/Creation
    mapping_profile = None
    if args.mapping and os.path.exists(args.mapping):
        print(f"Loading mapping profile from: {args.mapping}")
        mapping_profile = MappingProfile.load(args.mapping)
    else:
        use_saved = input("\nDo you have a saved mapping profile to load? (y/n): ").strip().lower()
        if use_saved == "y":
            prof_file = input("Enter path to mapping profile (.json): ").strip()
            if os.path.exists(prof_file):
                mapping_profile = MappingProfile.load(prof_file)
            else:
                print(f"Mapping profile not found at {prof_file}. Proceeding to interactive mapping...")

    if not mapping_profile:
        mapping_profile = interactive_key_mapping(json_keys, table_columns)

    print("\nActive Field Mapping Profile:")
    for jk, col in mapping_profile.mapping.items():
        print(f"  JSON key '{jk}' => Airtable column '{col}'")

    # Step 6: Fetch Existing Records
    print(f"\nFetching existing records from Airtable table '{table_name}'...")
    existing_records = client.fetch_existing_records(base_id, table_name)
    print(f"Retrieved {len(existing_records)} existing record(s) from Airtable.")

    if not existing_records:
        print("No existing records found in the table to update.")
        sys.exit(0)

    # Ask user which column to match by (Primary Key)
    print("\nSelect the Airtable Column used to match existing records (e.g. SKU, Name):")
    for idx, col in enumerate(table_columns, 1):
        print(f"  [{idx}] {col}")
    pk_choice = input("Enter column # choice [default 1]: ").strip()
    pk_idx = int(pk_choice) - 1 if pk_choice.isdigit() and 1 <= int(pk_choice) <= len(table_columns) else 0
    primary_key_col = table_columns[pk_idx]

    print(f"Matching existing records by column: '{primary_key_col}'")

    # Step 7: Prepare and Review Updates
    updates = match_and_prepare_updates(parsed_items, mapping_profile, existing_records, primary_key_col)

    print(f"\nMatched {len(updates)} record(s) out of {len(parsed_items)} JSON item(s) for update.")

    if not updates:
        print("No records matched the existing Airtable table entries.")
        sys.exit(0)

    print("\nSample Update Payload (First Record):")
    print(f"  Airtable Record ID: {updates[0]['id']}")
    print(f"  Updated Fields: {updates[0]['fields']}")

    if args.dry_run:
        print("\n[DRY RUN] Preview completed. No updates were sent to Airtable.")
        sys.exit(0)

    confirm = input(f"\nProceed with updating {len(updates)} record(s) in Airtable? (y/n): ").strip().lower()
    if confirm != "y":
        print("Operation cancelled by user.")
        sys.exit(0)

    print("\nUpdating records in Airtable...")
    updated = client.update_records(base_id, table_name, updates)
    print(f"\nSuccessfully updated {len(updated)} record(s) in Airtable!")


def extract_cli():
    """CLI entrypoint for extracting data from Airtable and syncing to website assets."""
    parser = argparse.ArgumentParser(description="Extract Product Categories, Details, and Attributes from Airtable to website assets.")
    parser.add_argument("--api-key", help="Airtable Personal Access Token")
    parser.add_argument("--base-id", help="Target Airtable Base ID")
    parser.add_argument("--products-table", help="Products table name")
    parser.add_argument("--categories-table", help="Categories table name")
    parser.add_argument("--attributes-table", help="Attributes table name")
    parser.add_argument("--output-json", help="Path for output JSON file (default: assets/data/products.json)")
    parser.add_argument("--output-js", help="Path for output JS file (default: assets/js/products_data.js)")

    args = parser.parse_args()

    from .extractor import run_extraction_cmd
    print("==============================================")
    print("       AIRTABLE DATA EXTRACTION PIPELINE      ")
    print("==============================================")

    try:
        catalog = run_extraction_cmd(
            base_id=args.base_id,
            products_table=args.products_table,
            categories_table=args.categories_table,
            attributes_table=args.attributes_table,
            output_json=args.output_json,
            output_js=args.output_js
        )
        print(f"\nExtraction successfully finished!")
        print(f"Extracted {len(catalog.get('products', []))} product(s) across {len(catalog.get('categories', []))} category(ies).")
        print(f"Updated static JSON dataset and JS catalog bundle.")
    except Exception as e:
        print(f"\nExtraction failed: {e}")
        sys.exit(1)


if __name__ == "__main__":
    main()

