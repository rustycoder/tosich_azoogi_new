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

