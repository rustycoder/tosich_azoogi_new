# Azoogi Internal Media & Content Best Practices Guide

This internal guide outlines technical standards, recommended specifications, and step-by-step workflows for managing videos, images, content, and deployments across the Azoogi website.

---

## 1. Video Guidelines (Hero & Background Videos)

### 1.1 Recommended Format & Specifications
* **Container Format**: `.mp4` (*Strongly Recommended over `.webm` for 100% universal browser and iOS/Safari compatibility*).
* **Video Codec**: **H.264** (`libx264` / Baseline or Main Profile).
* **Resolution**:
  * **Full Hero Banners**: `1920x1080` (1080p) or `1280x720` (720p).
  * *Note: 720p is often visually identical under overlay text and cuts load times by ~50%.*
* **Target File Size**: **2 MB – 5 MB** (*Never exceed 8 MB*).
* **Duration**: 5 to 10 seconds (seamless loop).
* **Frame Rate**: `24 fps` or `30 fps`.
* **Audio Track**: **Strip audio completely** (*Videos with no audio track guarantee seamless autoplay across mobile devices, Safari, and Brave Shields, while saving 20–30% file size*).
* **Fast Start Flag**: Index placed at the front of the file (`+faststart`) to allow instant progressive streaming before the full file finishes downloading.

### 1.2 Mandatory Poster Image
Every video upload in the CMS **must** have a corresponding `.webp` Poster Image uploaded.
* **Purpose**: Prevents black/blank boxes while the video stream buffers on slow connections.
* **Format**: `.webp` (target size: < 150 KB).

### 1.3 Quick Optimization Tools

#### Using Command Line (`ffmpeg`):
Run this single command on your raw video:
```bash
ffmpeg -i input_video.mp4 \
  -vcodec libx264 \
  -crf 26 \
  -preset slow \
  -an \
  -movflags +faststart \
  -vf "scale=1920:1080:force_original_aspect_ratio=decrease,pad=1920:1080:(ow-iw)/2:(oh-ih)/2" \
  output_optimized.mp4
```
* `-crf 26`: High visual fidelity with strong compression.
* `-an`: Strips audio track.
* `-movflags +faststart`: Enables instant video streaming.

#### Using GUI App ([HandBrake](https://handbrake.fr/)):
1. Open video in **HandBrake**.
2. Preset: **Web → Production Standard (or Creator 1080p30)**.
3. Check the box: **Web Optimized**.
4. In the **Audio** tab: Remove all audio tracks.
5. In the **Video** tab: Set Constant Quality (RF) to `24 - 26`.
6. Export as `.mp4`.

---

## 2. Image Guidelines

### 2.1 File Formats
* **Photos & Hero Banners**: `.webp` (preferred) or `.jpg`.
* **Logos & Badges**: SVG (vector) or transparent `.png`.
* **Icons**: Inline SVG.

### 2.2 Target Image Dimensions & File Sizes

| Placement | Recommended Dimensions | Max Target File Size | Format |
| :--- | :--- | :--- | :--- |
| **Hero Banners & Posters** | `1920 x 1080 px` | `< 200 KB` | `.webp` |
| **Social Share (OG Images)** | `1200 x 630 px` | `< 150 KB` | `.webp` / `.jpg` |
| **Project / Feature Photos** | `1200 x 800 px` | `< 120 KB` | `.webp` |
| **Product Thumbnails / Cards** | `600 x 600 px` | `< 60 KB` | `.webp` / `.jpg` |
| **Logos & Badges** | Vector or `400 x 120 px` | `< 30 KB` | `.svg` / `.png` |

### 2.3 Optimization Tips
* Use tools like [Squoosh.app](https://squoosh.app/) or [TinyPNG](https://tinypng.com/) before uploading.
* Always export images in the **sRGB** color space.

---

## 3. Deployment & Environment Checklist

### 3.1 Initial Server / Environment Setup
When deploying to a new server or staging environment, execute:

```bash
# 1. Generate storage symlink for uploaded media (REQUIRED for /storage/... URLs to work)
php artisan storage:link

# 2. Run migrations
php artisan migrate --force

# 3. Seed CMS Error Pages (403, 404, 419, 500, 503)
php artisan db:seed --class=ErrorPageSeeder --force

# 4. Clear and cache config/routes for production
php artisan optimize
```

### 3.2 Cloudflare Turnstile Configuration
When setting up CAPTCHA in new environments:
1. Ensure the domain/hostname is added in **Cloudflare Dashboard → Turnstile → Settings → Domains**:
   - Production: `azoogi.com`
   - Staging: `demo.tosichcapital.com`
   - Local: `localhost`, `127.0.0.1`
2. Keep credentials in `.env`:
   ```dotenv
   TURNSTILE_SITE_KEY=0x4AAAAAAE...
   TURNSTILE_SECRET_KEY=0x4AAAAAAE...
   TURNSTILE_ENABLED=true
   ```
3. To disable CAPTCHA locally during testing, toggle:
   ```dotenv
   TURNSTILE_ENABLED=false
   ```

### 3.3 Cache Busting for CSS / JS Assets
When pushing front-end stylesheet or JavaScript changes:
```bash
python update_version.py bump
```
Or set specific version:
```bash
python update_version.py 2.12
```

---

## 4. Typography & Highlight Formatting (`{...}` Syntax)

The CMS features dynamic text accenting powered by curly braces `{}`:

* **Hero Titles (`hero.title` / Slide Titles)**:
  - Text enclosed in `{...}` is rendered with the signature **outlined stroke font** (`font-family: var(--font-outline); -webkit-text-stroke: 1.35px var(--accent);`).
  - *Example*: `Engineered Lighting.\nInfinite Scale.\n{Zero Compromise.}`
* **All Other Text Fields (Section Headings, Paragraphs, Leads, Cards, Subtitles)**:
  - Text enclosed in `{...}` is rendered in the **solid brand accent color** (`color: var(--accent);`).
  - *Example*: `Why Choose {Azoogi}` or `Our Lighting Capabilities by {Sector}`.

---

## 5. XML Sitemap & Search Engine Indexing

The website automatically compiles and serves a dynamic XML sitemap at `/sitemap.xml` conforming to the `sitemaps.org 0.9` schema.

### 5.1 URL Types & Weighting
* **1.0 (Daily)**: Homepage (`/`)
* **0.9 (Weekly)**: Catalog (`/products`), Solutions (`/solutions`)
* **0.8 (Weekly)**: Tech & Protocol pages (`/casambi`, `/silvair`, `/madrix`, `/dali-centre`, `/ai-lighting`, `/data-centre`), Category views (`/products?category=...`), Individual Products (`/products/{slug}`)
* **0.7 (Monthly)**: Showcase Projects (`/projects`, `/project-detail?slug=...`), Audience landing pages (`/architect-designer`, `/electrician-builder`, `/home-owner`, `/wholesaler`), Contact (`/contact`), About (`/about`)
* **0.3 (Yearly)**: Legal and compliance pages (`/privacy`, `/terms`, `/warranty-returns`, `/modern-slavery`)

### 5.2 Real-Time Cache Invalidation
The sitemap is cached for 24 hours to ensure `< 5ms` response times for search crawlers. The cache is automatically purged and refreshed upon:
* Page/section content updates in **Content → Pages / Sections**
* Airtable product catalog synchronization in **Products**
* Project updates or reordering in **Projects**

### 5.3 CLI Commands
```bash
# Pre-warm and regenerate the sitemap cache
php artisan sitemap:generate

# Clear the sitemap cache
php artisan sitemap:generate --clear
```

---

## 6. Generative Engine Optimization (GEO) & AI Search Standards

Generative Engine Optimization optimizes the Azoogi platform for AI search systems (**ChatGPT Search, Perplexity AI, Claude, Google Gemini / AI Overviews, Microsoft Copilot, Apple Intelligence**).

### 6.1 Machine-Readable Markdown Endpoints (`/llms.txt`)
* **Curated Overview**: [`/llms.txt`](https://azoogi.com/llms.txt) — Token-efficient summary of core architectural linear ranges, wireless control protocols (Casambi, Silvair, DALI, Madrix), calculators, and trade services.
* **Full Technical Index**: [`/llms-full.txt`](https://azoogi.com/llms-full.txt) — Complete SKU-level technical specifications, product features, and case studies formatted for LLM context windows.

### 6.2 AI Crawler Permissions (`robots.txt`)
The following verified AI bots are explicitly welcomed to index public catalog, technology protocols, and `/llms.txt`:
* `GPTBot`, `OAI-SearchBot`, `ChatGPT-User` (OpenAI / ChatGPT)
* `PerplexityBot` (Perplexity AI)
* `ClaudeBot`, `Anthropic-ai` (Anthropic / Claude)
* `Google-Extended` (Google AI / Gemini)
* `Applebot-Extended` (Apple Intelligence)
* `Bingbot` (Microsoft Copilot)

### 6.3 Unified GEO & Sitemap Command
```bash
# Pre-warm /llms.txt, /llms-full.txt, and /sitemap.xml
php artisan geo:generate

# Purge all GEO and sitemap caches
php artisan geo:generate --clear
```

---

## 7. Azoogi Airtable Ordering Guide & Catalog Hierarchy

Ordering across the lighting catalog is driven by two cooperating `Order` fields:
* The **Categories table** sets the order of categories.
* The **Products table** sets the order of products within those categories.

### 7.1 Category Order & Block Ranges

Each top-level category owns a block of numbers in **steps of 100**. Its subcategories use the numbers directly after it:

| Block | Parent Category | Subcategories |
| :--- | :--- | :--- |
| **100** | Landscape Lighting | `101` Garden Light, `102` Pool Light, `103` Handrail |
| **200** | Neon Flex | `201` Mini Neon, `202` Standard Neon, `203` 3D Neon … `213` Long Run Neon |
| **300** | COB Strips / SMD Strips | `301` COB Strips, `302–304` COB types, `305` SMD Strips, `306–307` SMD types, `308` Flex Panel Sheets |
| **400** | Profiles | `401` Trimless … `414` Wall Washer *(the 403 and 407 parents hold the Surfaced and Recessed sub-groups)* |
| **500** | Drivers | `501–504` Driver types |
| **600** | Accessories | `601` Neon, `602` LED Strip, `603` Remotes (`604` RF Remotes, `605` Wall Panels), `606` Downlight Accessories |
| **700** | Controllers | `701` DALI, `702` Tuya, `703` Casambi Controllers (empty), `704` Waterproof, `705` MADRIX Pixel |
| **750** | Smart Controls | `751` Casambi Controls, `752` Smart Switches |
| **800** | 48V Track Systems | `801` Azoogi TR11, `802` Luminaires, `803` Tracks, `804` Track Accessories, `805` Audio, `806` Ventilation |
| **900** | Downlights | `901` Recessed, `902` Surface Mounted, `903` Pendant, `904` Wall Lights |

#### Category Rules:
1. **Unique numbers**: Every category has its own number. A subcategory's number is always higher than its parent's and lower than the next parent's block.
2. **Adding a subcategory**: Give it the next free number inside its parent's block (e.g., a new Neon type is `214`).
3. **Adding a top-level category**: Assign a new empty block (e.g., `1000`).
4. **Moving a category**: Changing a category number requires updating the first three digits of all products in that category.

### 7.2 Product Order Formula & Structure

Every product's number is calculated from its category number plus its position inside that category:

$$\text{Product Order} = \text{Category Order} \times 100 + \text{position } (01–99)$$

| Product | Category (Order) | Position | Product Order |
| :--- | :--- | :--- | :--- |
| First Mini Neon product | Mini Neon (`201`) | 1 | `20101` |
| Fourth Mini Neon product | Mini Neon (`201`) | 4 | `20104` |
| First Trimless profile | Trimless Profiles (`401`) | 1 | `40101` |
| 41st Recessed downlight | Recessed (`901`) | 41 | `90141` |

> **Direct Reading Example:** `90141` = Category `901` (Recessed), Product position `41`.

#### Product Rules:
* **Unique numbers**: No two products share an `Order` value.
* **Category first**: Sorting the entire table by `Order` automatically sorts products by category, matching the category hierarchy above.
* **Multi-category products**: A product associated with multiple categories is numbered under the *first category* listed in its Airtable `Categories` field (e.g., PR126 listed under Suspended Profiles (`411`) uses `411xx`).
* **Capacity**: Each category holds up to 99 products (`01`–`99`).

### 7.3 Baseline Numbering Logic
1. Categories follow the sequence of the Categories table.
2. Within each category, products that already had an order retained their relative sequence.
3. Products without an order were placed at the end of their respective category, sorted alphabetically by name.

### 7.4 Day-to-Day Maintenance SOP
* **Adding a new product**: Find the highest number in its category and add 1 (e.g., if last Mini Neon is `20104`, the next is `20105`).
* **Reordering within a category**: Swap or renumber only that category's products, preserving the 3-digit category prefix.
* **Inserting in the middle**: Positions are consecutive with no gaps. Renumber subsequent products in that category.
* **Changing a category**: Renumber the product into the destination category's 3-digit prefix range.
* **Auditing the table**: Sort by `Order`. Any fixture out of sequence or mismatched with its parent category prefix indicates a renumbering requirement.




