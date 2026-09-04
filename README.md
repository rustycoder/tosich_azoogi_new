# Azoogi Website

Laravel 13 application for the Azoogi lighting website.

## Requirements

- PHP 8.3+
- Composer

## Setup

```bash
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate
```

Seed the database (products load from `public/assets/data/products.json`):

```bash
php artisan db:seed
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
| `/trade-login` | Trade login (coming soon) |

## Product catalogue

The first seed imports the catalogue snapshot in `public/assets/data/products.json`. Later refreshes pull from Airtable (`AIRTABLE_API_KEY` and `AIRTABLE_BASE_ID` in `.env`):

```bash
php artisan products:sync
```

The dashboard Sync button queues the same job. A host cron should run `php artisan schedule:run` every minute so products refresh every two hours.

## Cache version (`?v=...`)

Front-end CSS/JS cache-busting uses `ASSET_VERSION` in `.env`, or `versioned_asset()` which appends the file's modification time.

```bash
python update_version.py bump
python update_version.py status
python update_version.py 2.11
```

## Project layout

```
app/Http/Controllers/     HTTP controllers
resources/views/          Blade layouts and pages
public/assets/            CSS, JS, images
```
