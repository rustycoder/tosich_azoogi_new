# Azoogi Website

Laravel 13 application for the Azoogi lighting website, with the existing Airtable extraction pipeline for product catalogue data.

## Requirements

- PHP 8.3+
- Composer
- Python 3 (optional, for Airtable sync)

## Setup

```bash
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate
```

Serve the app:

```bash
php artisan serve
```

Then open [http://localhost:8000](http://localhost:8000). The web root is `public/` — do not serve the project root as a static folder.

## Pages

| URL | Page |
| --- | --- |
| `/` | Home |
| `/products` | Product catalogue |
| `/product-detail` | Product detail |
| `/projects` | Projects |
| `/about` | About |
| `/solutions` | Solutions |
| `/contact` | Contact |
| `/ai-lighting` | AI Lighting |
| `/led-strip-calculator` | LED Calculator |
| `/policies` | Policies |
| `/trade-login` | Trade login (coming soon) |

Legacy `*.html` URLs redirect to the new routes (for example `/products.html` → `/products`).

## Airtable extraction

Product categories, details, and attributes still sync from Airtable via the Python package.

1. Configure `airtable_json_uploader/.env` with your Airtable PAT and base IDs (see `airtable_json_uploader/.env.example`).
2. Install and run:

```bash
cd airtable_json_uploader
pip install -e .
airtable-extract
```

Output files:

- `public/assets/data/products.json`
- `public/assets/js/products_data.js`

The extractor also writes product images under `public/assets/img/`.

Web Studio (optional):

```bash
airtable-web
```

Open `http://127.0.0.1:5050/extract`.

## Cache version (`?v=...`)

Front-end CSS/JS cache-busting uses `ASSET_VERSION` in `.env`. Bump it after catalogue extracts:

```bash
python update_version.py bump
python update_version.py status
python update_version.py 2.11
```

`airtable-extract` bumps this automatically.

## Project layout

```
app/Http/Controllers/     Contact form handling
resources/views/          Blade layouts and pages
public/assets/            CSS, JS, images, catalogue JSON
airtable_json_uploader/   Airtable extraction engine
v3/                       Source product JSON used by the extractor
```
