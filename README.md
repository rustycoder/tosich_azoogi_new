# Azoogi Website & Airtable Integration

This workspace contains the Azoogi Lighting website and an integrated **Airtable Extraction & Synchronization Engine** to extract Product Categories, Product Details, and Attributes directly from Airtable and display them on the website.

---

## 🚀 Quick Setup & Configuration

### 1. Configure Environment Variables (`.env`)

Navigate to `airtable_json_uploader/` (or create a `.env` file in root) and set your Airtable credentials:

```bash
# airtable_json_uploader/.env

# Airtable Personal Access Token (PAT) with read permissions
AIRTABLE_API_KEY=patXXXXXXXXXXXX.XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX

# Target Airtable Base ID
AIRTABLE_BASE_ID=appXXXXXXXXXXXXXX

# Airtable Table Names (Customizable to match your base)
AIRTABLE_PRODUCTS_TABLE=Products
AIRTABLE_CATEGORIES_TABLE=Categories
AIRTABLE_ATTRIBUTES_TABLE=Attributes
```

---

## 🔄 Extracting Data from Airtable

You can extract product categories, details, images, and linked attributes using either the **CLI tool** or the **Visual Web Studio**.

### Option 1: Command-Line Interface (CLI)

1. Install the `airtable_json_uploader` package in editable mode:
   ```bash
   cd airtable_json_uploader
   pip install -e .
   ```

2. Run the extraction command:
   ```bash
   airtable-extract
   ```

   *Or specify custom parameters on the command line:*
   ```bash
   airtable-extract --base-id appXXXXXXXXXXXXXX --products-table Products --attributes-table Attributes
   ```

3. The extractor will output:
   * **`assets/data/products.json`**: Static JSON payload containing normalized products, categories, and attributes.
   * **`assets/js/products_data.js`**: Compiled JavaScript bundle exposing `AZOOGI_PRODUCTS` for the front-end.

---

### Option 2: Visual Web Studio Page

1. Start the Web Studio server:
   ```bash
   airtable-web
   ```
   *(Or `python airtable_json_uploader/airtable_json_uploader/app.py`)*

2. Open **`http://127.0.0.1:5050/extract`** in your browser.
3. Select your **Workspace**, **Base**, and target tables (**Products**, **Categories**, **Attributes**) from visual dropdowns.
4. Click **"Save Config to .env"** to update your `.env` settings or click **"Extract & Sync to Website"** for 1-click execution.


---

## 🌐 Running & Testing the Website Locally

1. Start a local HTTP web server from the project root:
   ```bash
   python3 -m http.server 8000
   ```

2. Open **`http://localhost:8000`** in your browser to inspect the website:
   * **Product Catalog Grid**: Check `http://localhost:8000/products.html` to test dynamic product cards, attribute filter pills, and search.
   * **Product Details Page**: Check `http://localhost:8000/product-detail.html` for technical specs, CCT options, and image gallery rendering.
   * **Navigation Mega Menu**: Check `http://localhost:8000/index.html` to verify topbar category dropdowns.

---

## 🛠 Project Structure

```
azoogi/
├── assets/
│   ├── data/
│   │   └── products.json         # Static extracted catalog JSON
│   ├── js/
│   │   ├── products_data.js     # Auto-compiled JS bundle (AZOOGI_PRODUCTS)
│   │   ├── mega_menu.js         # Navigation dynamic loader
│   │   └── site_header.js       # Dynamic topbar header
├── airtable_json_uploader/
│   ├── .env                     # Airtable PAT & Table configuration
│   ├── airtable_json_uploader/
│   │   ├── extractor.py         # Main extraction and schema resolver engine
│   │   ├── airtable_client.py   # Airtable REST API client with retry logic
│   │   ├── config.py            # Environment configuration helpers
│   │   ├── cli.py               # CLI entry points (airtable-extract)
│   │   └── app.py               # Web Studio Flask application
│   ├── setup.py
│   └── test_extractor.py        # Extractor unit tests
├── index.html                   # Main landing page
├── products.html                # Product catalog grid page
├── product-detail.html          # Individual product details page
└── README.md                    # System documentation
```
