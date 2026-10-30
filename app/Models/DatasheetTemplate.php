<?php

namespace App\Models;

use App\Models\Concerns\Auditable;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;

#[Fillable([
    'name',
    'slug',
    'description',
    'is_active',
    'body_html',
    'custom_css',
    'available_tokens',
    'created_by',
    'updated_by',
    'deleted_by',
])]
class DatasheetTemplate extends Model
{
    use Auditable, SoftDeletes;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'available_tokens' => 'array',
        ];
    }

    /**
     * @param  Builder<self>  $query
     */
    public function scopeActive(Builder $query): void
    {
        $query->where('is_active', true);
    }

    public function makeActive(): void
    {
        DB::transaction(function (): void {
            static::query()->where('is_active', true)->update(['is_active' => false]);
            $this->update(['is_active' => true]);
        });
    }

    /**
     * Render the template with dynamic sheet data and optional auto-print.
     *
     * @param  array<string, mixed>  $sheetData
     */
    public function render(array $sheetData, bool $autoPrint = false): string
    {
        $html = (string) $this->body_html;

        // Inject custom CSS into head or replace {{custom_css}} token if present
        $customCss = trim((string) $this->custom_css);
        if ($customCss !== '') {
            if (str_contains($html, '{{custom_css}}') || str_contains($html, '{{ custom_css }}')) {
                $html = str_replace(['{{custom_css}}', '{{ custom_css }}'], $customCss, $html);
            } else {
                $styleBlock = "<style>\n".$customCss."\n</style>";
                if (str_contains($html, '</head>')) {
                    $html = str_replace('</head>', $styleBlock."\n</head>", $html);
                } else {
                    $html = $styleBlock."\n".$html;
                }
            }
        }

        // Interpolate token placeholders
        foreach ($sheetData as $key => $value) {
            if (is_scalar($value) || $value === null) {
                $str = (string) ($value ?? '');
                $html = str_replace(['{{'.$key.'}}', '{{ '.$key.' }}'], $str, $html);
            }
        }

        // Inject auto-print script if requested and not already present
        if ($autoPrint && ! str_contains($html, 'window.print()')) {
            $printScript = "<script>\nwindow.addEventListener('load', function () {\n    window.setTimeout(function () { window.print(); }, 250);\n});\n</script>";
            if (str_contains($html, '</body>')) {
                $html = str_replace('</body>', $printScript."\n</body>", $html);
            } else {
                $html .= "\n".$printScript;
            }
        }

        return $html;
    }

    /**
     * @return list<string>
     */
    public static function defaultTokens(): array
    {
        return [
            'product_name',
            'product_code',
            'category',
            'description',
            'project_name',
            'person_name',
            'specifications_table',
            'grouped_specifications_table',
            'product_image',
            'dimension_image',
            'reviewed_on',
            'logo_left',
            'logo_right',
            'review_logo',
            'email',
            'phone',
            'website',
            'address',
            'technical_icons',
            'specification_icons',
        ];
    }

    public static function activeOrDefault(): self
    {
        $active = static::query()->where('is_active', true)->first();

        if ($active !== null) {
            return $active;
        }

        return new self([
            'name' => 'Standard Azoogi A4',
            'slug' => 'standard-azoogi-a4',
            'description' => 'Original factory specification datasheet template.',
            'is_active' => true,
            'body_html' => static::defaultHtml(),
            'custom_css' => static::defaultCss(),
            'available_tokens' => static::defaultTokens(),
        ]);
    }

    public static function defaultCss(): string
    {
        $path = public_path('assets/css/datasheet.css');
        if (file_exists($path)) {
            return (string) file_get_contents($path);
        }

        return <<<'CSS'
@import url("https://fonts.googleapis.com/css2?family=Open+Sans:wght@400;700;800&display=swap");

@page {
  size: A4;
  margin: 0;
}

* {
  box-sizing: border-box;
}

html,
body {
  margin: 0;
  padding: 0;
  font-family: "Open Sans", "Helvetica Neue", Arial, sans-serif;
  color: #1a1a1a;
  background: #d8d8d8;
  -webkit-print-color-adjust: exact;
  print-color-adjust: exact;
}

.ds-print {
  position: fixed;
  right: 20px;
  bottom: 20px;
  z-index: 3;
  width: 48px;
  height: 48px;
  padding: 0;
  border: 0;
  border-radius: 50%;
  background: #73bf44;
  color: #fff;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  box-shadow: 0 4px 16px rgba(0, 0, 0, .22);
}

.ds-print svg {
  width: 22px;
  height: 22px;
  fill: currentColor;
}

.page {
  position: relative;
  width: 210mm;
  height: 297mm;
  margin: 16px auto;
  background: #ffffff;
  overflow: hidden;
  display: flex;
  flex-direction: column;
  box-shadow: 0 8px 30px rgba(0, 0, 0, .18);
}

.az-header {
  background: #0e0e0e;
  color: #ffffff;
  padding: 7mm 10mm;
  display: flex;
  align-items: center;
  justify-content: space-between;
  border-bottom: 1px solid #000;
}

.az-header .logo-left {
  height: 14mm;
  display: block;
  max-width: 45%;
  object-fit: contain;
}

.az-header .logo-right {
  height: 14mm;
  display: block;
  max-width: 45%;
  object-fit: contain;
}

.title-block {
  padding: 6mm 10mm 0;
}

.title-block h1 {
  margin: 0;
  font-size: 24pt;
  font-weight: 700;
  color: #111;
  letter-spacing: .3px;
}

.title-block h2 {
  margin: 0 0 3mm;
  font-size: 14pt;
  font-weight: 700;
  color: #73bf44;
}

.title-block h2:empty {
  display: none;
}

.gradient-line {
  height: 2px;
  background: linear-gradient(90deg, #73bf44 0%, #73bf44 35%, rgba(103, 208, 78, 0) 100%);
  border: 0;
  margin: 0 10mm;
}

.body-wrap {
  flex: 1;
  position: relative;
  padding: 6mm 10mm 4mm;
  background: #ffffff;
  min-height: 0;
}

.content {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 6mm;
  position: relative;
  z-index: 1;
}

.desc {
  font-size: 10px;
  line-height: 1.55;
  color: #2a2a2a;
  text-align: justify;
  margin: 0 0 6mm;
}

.params-title {
  font-size: 9pt;
  font-weight: 700;
  color: #111;
  margin: 0 0 2mm;
}

table.params {
  width: 100%;
  border-collapse: collapse;
  font-size: 10px;
  border: 1px solid #ffffff;
}

table.params td {
  padding: 2mm 3mm;
  border-top: 1px solid #ccc;
  vertical-align: middle;
}

table.params tr td:first-child {
  font-weight: 700;
  width: 38%;
  background: #ffffff;
  color: #111;
}

table.params tr:nth-child(odd) td {
  background: #f6f6f6;
  color: #111;
}

table.params tr:nth-child(even) td {
  background: #ffffff;
  color: #111;
}

.product-img-container,
.dim-img-container {
  height: 78mm;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
}

.dim-img-container {
  height: 55mm;
  margin-top: 34px;
}

.product-img {
  max-width: 100%;
  max-height: 100%;
  height: auto;
  width: auto;
  display: block;
}

.dim-title {
  text-align: center;
  margin: 6mm 0 3mm;
  font-size: 9pt;
  font-weight: 700;
  color: #111;
}

.dim-img {
  max-width: 100%;
  max-height: 100%;
  height: auto;
  width: auto;
  display: block;
}

.note-img-container {
  height: 55mm;
  margin-top: 34px;
  display: flex;
  justify-content: center;
  align-items: center;
}

.note-img {
  max-width: 100%;
  max-height: 100%;
  height: auto;
  width: auto;
  display: block;
}

.az-footer {
  background: #0e0e0e;
  color: #ffffff;
  padding: 5mm 10mm;
  display: flex;
  justify-content: space-between;
  align-items: center;
  flex-wrap: wrap;
  gap: 4mm;
  font-size: 8.5pt;
}

.az-footer .f-item {
  display: inline-flex;
  align-items: center;
  gap: 5px;
  color: #e6e6e6;
}

.az-footer .f-item svg {
  width: 11px;
  height: 11px;
  fill: #67d04e;
  flex-shrink: 0;
}

.sheet {
  color: #000;
  max-width: 400px;
  margin: 0 auto;
  background: #fff;
  border: 1px solid #ff2b1c;
}

.row {
  border-bottom: 1px solid #ff2b1c
}

.row:last-child {
  border-bottom: 0
}

.head {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 5px;
  padding: 5px 10px;
}

.head h1 {
  font-size: 14px;
  font-weight: 700;
  letter-spacing: -.5px;
  line-height: 1.05;
  margin: 0px;
  padding-top: 5px;
}

.head .project {
  margin: 0px;
  padding-top: 5px;
  font-size: 8px;
  font-weight: 400;
  letter-spacing: .3px;
  text-transform: uppercase;
}

.head .project b {
  font-weight: 700
}

.logo {
  flex: 0 0 auto;
  width: 80px;
}

.logo img {
  display: block;
  width: 100%;
  height: auto
}

.checks {
  padding: 5px 10px
}

.check {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 5px;
  padding: 2px 0;
}

.check-label {
  font-size: 8px;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: .2px;
}

.box {
  flex: 0 0 auto;
  width: 10px;
  height: 10px;
  border: 1px solid #ff2b1c;
  background: #fff;
}

.line {
  padding: 5px 10px;
  font-size: 8px;
  font-weight: 700;
  text-transform: uppercase
}

.line span {
  font-weight: 400;
  text-transform: none
}

.notes {
  padding: 5px 10px;
}

.notes ul {
  list-style: none;
  margin: 0px;
  padding: 0px;
}

.notes li {
  position: relative;
  padding-left: 10px;
  font-size: 6px;
  line-height: 1.5;
}

.notes li:last-child {
  margin-bottom: 0
}

.notes li::before {
  content: "";
  position: absolute;
  left: 0px;
  top: .55em;
  width: 4px;
  height: 4px;
  border-radius: 50%;
  background: #000;
}

@media print {
  html,
  body {
    background: #ffffff;
  }

  .ds-print {
    display: none;
  }

  .page {
    margin: 0;
    box-shadow: none;
  }
}
CSS;
    }

    public static function defaultHtml(): string
    {
        return <<<'HTML'
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>{{product_name}} — Azoogi datasheet</title>
<link rel="icon" href="/assets/favicon.png">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Open+Sans:wght@400;700;800&display=swap" rel="stylesheet">
</head>
<body>
<button type="button" class="ds-print" onclick="window.print()" title="Print / Save PDF" aria-label="Print / Save PDF">
    <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M19 8H5a3 3 0 0 0-3 3v6h4v4h12v-4h4v-6a3 3 0 0 0-3-3zm-3 11H8v-5h8zm3-7a1 1 0 1 1 1-1 1 1 0 0 1-1 1zM18 3H6v4h12z"/></svg>
</button>
<section class="page">
    <header class="az-header">
        <img class="logo-left" src="{{logo_left}}" alt="Azoogi Lighting Solutions">
        <img class="logo-right" src="{{logo_right}}" alt="Lighting Council Australia">
    </header>

    <div class="title-block">
        <h1>{{product_name}}</h1>
        <h2>{{product_code}}</h2>
    </div>
    <hr class="gradient-line">

    <div class="body-wrap">
        <div class="content">
            <div>
                <p class="desc">{{description}}</p>
                {{specifications_table}}
            </div>

            <div style="display: flex; flex-direction: column;">
                <div class="product-img-container">
                    <img class="product-img" src="{{product_image}}" alt="{{product_name}}">
                </div>
                <div class="dim-img-container">
                    <div class="dim-title">DIMENSIONS</div>
                    <img class="dim-img" src="{{dimension_image}}" alt="{{product_name}} technical dimensions">
                </div>
                <div class="note-img-container">
                    <main class="sheet">
                        <section class="row head">
                            <div>
                                <h1>Lighting Technical Review</h1>
                                <div class="project"><b>Project:</b> {{project_name}}</div>
                            </div>
                            <div class="logo">
                                <img src="{{review_logo}}" alt="Azoogi Lighting Solutions logo">
                            </div>
                        </section>
                        <section class="row checks">
                            <div class="check">
                                <div class="check-label">Matched to Specifcation:</div>
                                <div class="box" role="checkbox" aria-checked="false" aria-label="Matched to specification"></div>
                            </div>
                            <div class="check">
                                <div class="check-label">Minor Variations:</div>
                                <div class="box" role="checkbox" aria-checked="false" aria-label="Minor variations"></div>
                            </div>
                        </section>
                        <section class="row line">Checked By: <span></span></section>
                        <section class="row line">Date: <span></span></section>
                        <section class="row notes">
                            <ul>
                                <li>This technical review indicates general conformity with the project design intent only.</li>
                                <li>Review does not constitute approval of dimensions, quantities, installation methods, site conditions, or regulatory compliance.</li>
                                <li>The Client and Electrical Subcontractor remain responsible for verification and compliance with all project documentation and applicable standards. Refer to the latest issued Azoogi working file and Luminaire Schedule for current product selections and revisions.</li>
                            </ul>
                        </section>
                    </main>
                </div>
            </div>
        </div>
    </div>

    <footer class="az-footer">
        <span class="f-item">
            <svg viewBox="0 0 24 24"><path d="M20 4H4a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2V6a2 2 0 0 0-2-2zm0 4-8 5-8-5V6l8 5 8-5z"/></svg>
            {{email}}
        </span>
        <span class="f-item">
            <svg viewBox="0 0 24 24"><path d="M6.6 10.8a15.9 15.9 0 0 0 6.6 6.6l2.2-2.2a1 1 0 0 1 1-.25 11.4 11.4 0 0 0 3.6.58 1 1 0 0 1 1 1V20a1 1 0 0 1-1 1A17 17 0 0 1 3 4a1 1 0 0 1 1-1h3.5a1 1 0 0 1 1 1 11.4 11.4 0 0 0 .58 3.6 1 1 0 0 1-.25 1z"/></svg>
            {{phone}}
        </span>
        <span class="f-item">
            <svg viewBox="0 0 24 24"><path d="M12 2a10 10 0 1 0 10 10A10 10 0 0 0 12 2zm7.9 9h-3.95a15.7 15.7 0 0 0-1.3-5.7A8 8 0 0 1 19.9 11zM12 4c.97 1.4 1.8 3.7 1.95 7H10.05C10.2 7.7 11.03 5.4 12 4zM4.1 13h3.95a15.7 15.7 0 0 0 1.3 5.7A8 8 0 0 1 4.1 13zm0-2A8 8 0 0 1 9.35 5.3 15.7 15.7 0 0 0 8.05 11zm7.9 9c-.97-1.4-1.8-3.7-1.95-7h3.9c-.15 3.3-.98 5.6-1.95 7zm2.65-.3a15.7 15.7 0 0 0 1.3-5.7h3.95a8 8 0 0 1-5.25 5.7z"/></svg>
            {{website}}
        </span>
        <span class="f-item">
            <svg viewBox="0 0 24 24"><path d="M12 2a7 7 0 0 0-7 7c0 5.25 7 13 7 13s7-7.75 7-13a7 7 0 0 0-7-7zm0 9.5A2.5 2.5 0 1 1 14.5 9 2.5 2.5 0 0 1 12 11.5z"/></svg>
            {{address}}
        </span>
    </footer>
</section>
</body>
</html>
HTML;
    }

    public static function modernSpecifierHtml(): string
    {
        return <<<'HTML'
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>{{product_name}} — Product Datasheet</title>
<link rel="icon" href="/assets/favicon.png">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
</head>
<body>
<button type="button" class="ds-print" onclick="window.print()" title="Print / Save PDF" aria-label="Print / Save PDF">
    <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M19 8H5a3 3 0 0 0-3 3v6h4v4h12v-4h4v-6a3 3 0 0 0-3-3zm-3 11H8v-5h8zm3-7a1 1 0 1 1 1-1 1 1 0 0 1-1 1zM18 3H6v4h12z"/></svg>
</button>

<section class="page">
    <!-- Header -->
    <header class="spec-header">
        <div class="logo-wrap">
            <img src="/assets/logo_dark.png" alt="Azoogi Lighting + Controls" class="spec-logo">
        </div>
        <div class="header-doc-title">
            PRODUCT DATASHEET
        </div>
    </header>
    <div class="header-divider"></div>

    <!-- Hero Section with Specifier Use Box -->
    <div class="spec-hero-wrap">
        <div class="spec-hero-left">
            <div class="category-eyebrow">{{category}}</div>
            <h1 class="product-title">{{product_name}}</h1>
            <p class="product-summary">{{description}}</p>
        </div>
        <div class="spec-box-wrap">
            <div class="spec-box-title">FOR SPECIFIER USE</div>
            <div class="spec-box-body">
                <div class="spec-box-row">
                    <span class="sb-label">Project</span>
                    <span class="sb-val">{{project_name}}</span>
                </div>
                <div class="spec-box-row">
                    <span class="sb-label">Type / Ref</span>
                    <span class="sb-line"></span>
                </div>
                <div class="spec-box-row">
                    <span class="sb-label">Location</span>
                    <span class="sb-line"></span>
                </div>
                <div class="spec-box-row">
                    <span class="sb-label">Qty</span>
                    <span class="sb-line"></span>
                </div>
                <div class="spec-box-row">
                    <span class="sb-label">Approved / Date</span>
                    <span class="sb-val">{{reviewed_on}}</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Order Code & Badges Bar -->
    <div class="order-badge-bar">
        <div class="order-code-group">
            <span class="order-code-label">ORDER CODE</span>
            <span class="order-code-value">{{product_code}}</span>
        </div>
        <div class="badge-group">
            {{technical_icons}}
        </div>
    </div>

    <!-- Visual Showcase: Product Image & Technical Dimensions -->
    <div class="visual-showcase-row">
        <div class="showcase-card product-showcase">
            <img src="{{product_image}}" alt="{{product_name}}" class="showcase-img">
        </div>
        <div class="showcase-card dimension-showcase">
            <img src="{{dimension_image}}" alt="{{product_name}} technical dimensions" class="showcase-img dim-img">
            <div class="dim-caption">Dimensions in mm &middot; technical reference</div>
        </div>
    </div>

    <!-- Technical Specifications: Grouped 2-Column Layout -->
    <div class="dynamic-specs-block">
        {{grouped_specifications_table}}
    </div>

    <!-- Footer -->
    <div class="footer-divider"></div>
    <footer class="spec-footer">
        <div class="footer-left">
            <div class="company-web">{{website}}</div>
            <div class="company-contact">{{phone}} &middot; {{email}}</div>
        </div>
        <div class="footer-right">
            Azoogi continually refines its products. Data is nominal and subject to change. Please confirm specifications with Azoogi before ordering.
        </div>
    </footer>
</section>
</body>
</html>
HTML;
    }

    public static function modernSpecifierCss(): string
    {
        return <<<'CSS'
@import url("https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap");

@page {
  size: A4 portrait;
  margin: 0;
}

* {
  box-sizing: border-box;
}

html, body {
  margin: 0;
  padding: 0;
  font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
  color: #111827;
  background: #e5e7eb;
  -webkit-print-color-adjust: exact;
  print-color-adjust: exact;
}

.ds-print {
  position: fixed;
  right: 20px;
  bottom: 20px;
  z-index: 99;
  width: 48px;
  height: 48px;
  padding: 0;
  border: 0;
  border-radius: 50%;
  background: #111827;
  color: #ffffff;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  box-shadow: 0 4px 16px rgba(0, 0, 0, .25);
  transition: transform 0.2s;
}

.ds-print:hover {
  transform: scale(1.05);
}

.ds-print svg {
  width: 22px;
  height: 22px;
  fill: currentColor;
}

.page {
  position: relative;
  width: 210mm;
  height: 297mm;
  margin: 14px auto;
  background: #ffffff;
  overflow: hidden;
  display: flex;
  flex-direction: column;
  padding: 10mm 12mm 8mm;
  box-shadow: 0 8px 30px rgba(0, 0, 0, .15);
}

/* 1. Header */
.spec-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding-bottom: 2mm;
}

.logo-wrap {
  display: flex;
  align-items: center;
}

.spec-logo {
  height: 22px;
  max-width: 140px;
  object-fit: contain;
}

.header-doc-title {
  font-size: 8pt;
  font-weight: 600;
  letter-spacing: 1.5px;
  color: #6b7280;
  text-transform: uppercase;
}

.header-divider {
  height: 1.2px;
  background: #111827;
  width: 100%;
  margin-bottom: 3.5mm;
}

/* 2. Hero & Specifier Box */
.spec-hero-wrap {
  display: flex;
  justify-content: space-between;
  gap: 6mm;
  align-items: flex-start;
  margin-bottom: 3mm;
}

.spec-hero-left {
  flex: 1;
}

.category-eyebrow {
  font-size: 7.5pt;
  font-weight: 700;
  color: #6b7280;
  text-transform: uppercase;
  letter-spacing: 1.2px;
  margin-bottom: 1.5mm;
}

.product-title {
  font-size: 20pt;
  font-weight: 800;
  line-height: 1.12;
  color: #111827;
  margin: 0 0 2mm;
  letter-spacing: -0.3px;
}

.product-summary {
  font-size: 7.5pt;
  line-height: 1.45;
  color: #4b5563;
  margin: 0;
  max-width: 96%;
}

.spec-box-wrap {
  width: 195px;
  flex-shrink: 0;
  border: 1px solid #9ca3af;
  padding: 5px 8px;
  background: #ffffff;
}

.spec-box-title {
  font-size: 7pt;
  font-weight: 800;
  letter-spacing: 0.8px;
  color: #374151;
  text-transform: uppercase;
  padding-bottom: 3px;
  border-bottom: 1px solid #e5e7eb;
  margin-bottom: 3px;
}

.spec-box-row {
  display: flex;
  align-items: flex-end;
  justify-content: space-between;
  font-size: 7.2pt;
  padding: 2.5px 0;
  gap: 8px;
}

.spec-box-row:last-child {
  border-bottom: none;
}

.sb-label {
  color: #4b5563;
  font-weight: 500;
  white-space: nowrap;
}

.sb-val {
  flex: 1;
  border-bottom: 1px solid #d1d5db;
  min-height: 12px;
  color: #111827;
  font-weight: 600;
  text-align: right;
  padding-bottom: 1px;
}

.sb-line {
  flex: 1;
  border-bottom: 1px solid #d1d5db;
  min-height: 12px;
}

/* 3. Order Code & Badges Bar */
.order-badge-bar {
  display: flex;
  justify-content: space-between;
  align-items: center;
  border-top: 1px solid #111827;
  border-bottom: 1px solid #111827;
  padding: 2mm 0;
  margin-bottom: 3.5mm;
}

.order-code-group {
  display: flex;
  align-items: baseline;
  gap: 8px;
}

.order-code-label {
  font-size: 7.5pt;
  font-weight: 700;
  letter-spacing: 1px;
  color: #6b7280;
  text-transform: uppercase;
}

.order-code-value {
  font-size: 12pt;
  font-weight: 800;
  color: #111827;
  letter-spacing: 0.5px;
}

.badge-group {
  display: flex;
  align-items: center;
  gap: 6px;
  flex-wrap: wrap;
}

.spec-icon-img {
  height: 22px;
  width: auto;
  max-width: 44px;
  object-fit: contain;
  vertical-align: middle;
}

.feature-badge {
  border: 1.5px solid #111827;
  padding: 1.5px 6px;
  font-size: 7pt;
  font-weight: 800;
  color: #111827;
  border-radius: 2px;
  text-transform: uppercase;
  letter-spacing: 0.4px;
  background: #ffffff;
  display: inline-flex;
  align-items: center;
  justify-content: center;
}

/* 4. Visual Showcase */
.visual-showcase-row {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 6mm;
  margin-bottom: 3.5mm;
}

.showcase-card {
  border: 1px solid #e5e7eb;
  border-radius: 2px;
  height: 125px;
  padding: 6px;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  position: relative;
  background: #ffffff;
}

.showcase-card img {
  max-height: 95px;
  max-width: 90%;
  object-fit: contain;
  display: block;
}

.dim-caption {
  font-size: 6.5pt;
  color: #6b7280;
  margin-top: 3px;
  text-align: center;
}

/* 5. Technical Specifications */
.specs-grid-wrap {
  display: flex;
  flex-direction: column;
  gap: 4mm;
  margin-top: 1mm;
  margin-bottom: 2mm;
  page-break-inside: avoid;
}

.specs-row {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 7mm;
  align-items: start;
}

.specs-column {
  display: flex;
  flex-direction: column;
  gap: 3mm;
}

.spec-section {
  display: flex;
  flex-direction: column;
}

.spec-section.empty {
  min-height: 1px;
}

.spec-sec-title {
  font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
  font-size: 7.8pt;
  font-weight: 800;
  letter-spacing: 0.8px;
  color: #111827;
  text-transform: uppercase;
  padding-bottom: 2px;
  border-bottom: 1.2px solid #111827;
  margin-bottom: 1px;
}

.spec-row {
  display: flex;
  justify-content: space-between;
  align-items: baseline;
  padding: 2.4px 0;
  border-bottom: 1px solid #e5e7eb;
  font-size: 7.2pt;
  line-height: 1.35;
}

.spec-row .k {
  color: #374151;
  font-weight: 400;
  text-align: left;
  padding-right: 8px;
  flex: 1 1 auto;
}

.spec-row .v {
  color: #111827;
  font-weight: 600;
  text-align: right;
  flex: 0 1 auto;
  max-width: 65%;
}

/* Dynamic Specifications Table (When rendered from Airtable specs) */
.dynamic-specs-block table.params {
  width: 100%;
  border-collapse: collapse;
  font-size: 7pt;
  margin-top: 2px;
}

.dynamic-specs-block .params-title {
  font-size: 7pt;
  font-weight: 800;
  letter-spacing: 1px;
  color: #111827;
  text-transform: uppercase;
  padding-bottom: 1.5px;
  border-bottom: 1px solid #111827;
  margin: 3mm 0 2px;
}

.dynamic-specs-block table.params tr {
  border-bottom: 1px solid #f3f4f6;
}

.dynamic-specs-block table.params td {
  padding: 2px 0;
  background: transparent !important;
}

.dynamic-specs-block table.params td:first-child {
  color: #4b5563;
  font-weight: 500;
  width: 50%;
}

.dynamic-specs-block table.params td:last-child {
  color: #111827;
  font-weight: 600;
  text-align: right;
}

/* 6. Footer */
.footer-divider {
  height: 1px;
  background: #111827;
  width: 100%;
  margin-top: auto;
  margin-bottom: 2mm;
}

.spec-footer {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  gap: 6mm;
  font-size: 6.8pt;
  color: #4b5563;
  padding-bottom: 1mm;
}

.footer-left .company-web {
  font-weight: 800;
  color: #111827;
  margin-bottom: 1px;
  font-size: 7.2pt;
}

.footer-left .company-contact {
  color: #4b5563;
}

.footer-right {
  max-width: 55%;
  text-align: right;
  color: #6b7280;
  line-height: 1.35;
}

@media print {
  html, body {
    background: #ffffff;
  }
  .ds-print {
    display: none !important;
  }
  .page {
    margin: 0 !important;
    box-shadow: none !important;
    height: 297mm !important;
  }
}
CSS;
    }
}
