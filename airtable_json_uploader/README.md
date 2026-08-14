# Airtable JSON Uploader & Web Studio

A Python package and Web Application to process JSON files, dynamically map JSON keys to Airtable base columns in a visual interface, save mapping profiles, and update existing Airtable records.

## Features

- **Visual Web Studio**: Modern dark-glassmorphic SPA to visually connect to Airtable Workspaces, Bases, and Tables.
- **Drag & Drop JSON Dropzone**: Upload JSON files directly or select from `json_files/` folder.
- **Visual Column Mapping Studio**: Side-by-side key-to-column mapper with **Auto-Match** capabilities and mapping profile saving/loading.
- **Live Match & Diff Preview**: Compares JSON items with existing Airtable records, shows exact field diffs, and executes batch `PATCH` updates with progress tracking.
- **Secure Key Management**: API Token is stored strictly in local `.env` using `python-dotenv`.

## Installation

```bash
cd airtable_json_uploader
pip install -e .
```

## Running the Web Studio

Start the web studio by running:

```bash
airtable-web
# or specify custom port:
airtable-web --port 8080
```

Then open **`http://127.0.0.1:5050`** (or the port displayed in your terminal) in your browser!


---

## Interactive CLI Mode

If you prefer command-line execution:

```bash
airtable-upload
```


**JSON Source Selection Menu**:
Place your `.json` files inside the `json_files/` directory. When prompted, you can:
- Choose **Process ALL JSON files in folder** to upload all JSON files in one run.
- Select a specific JSON file from the list.
- Type a relative or custom path (includes auto-resolution if only filename is entered).


### 2. Command-Line Arguments Mode

```bash
airtable-upload --workspace-id wspXXXXXXXX --base-id appXXXXXXXX --table Products --json-path ./v3/my_product.json --mapping ./mappings/product_mapping.json
```

### Options

- `--api-key`: Airtable Personal Access Token (stored in `.env` if omitted).
- `--workspace-id`: Airtable Workspace ID (filters bases to specified workspace).
- `--base-id`: Target Airtable Base ID.
- `--table`: Target Airtable Table Name.
- `--json-path`: File path to a JSON file or directory containing JSON files.
- `--mapping`: File path to a saved mapping profile `.json` file.
- `--dry-run`: Preview updates without sending request to Airtable.
