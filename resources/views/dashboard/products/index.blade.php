@extends('layouts.dashboard')

@section('title', 'Products')

@section('content')
<div class="dash-head">
    <div class="dash-head-title">
        <h1>Products</h1>
        <div class="dash-head-actions">
            <span class="dash-pill is-active">{{ $products->total() }} Products</span>
        </div>
    </div>
    <p class="dash-lead">Browse and manage product specifications, media assets, documentation, and SEO metadata.</p>
</div>

<!-- Search & Filter Controls Toolbar -->
<form class="dash-toolbar-row" method="get" action="{{ route('dashboard.products.index') }}" role="search" id="productFilterForm">
    <!-- Search Input Field -->
    <div class="dash-search">
        <label class="visually-hidden" for="dash-search-q">Search products</label>
        <div class="dash-search-field">
            <svg class="dash-search-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                <circle cx="11" cy="11" r="6.5"/>
                <path d="M16.5 16.5 21 21"/>
            </svg>
            <input
                id="dash-search-q"
                type="search"
                name="q"
                value="{{ $search }}"
                placeholder="Search by title or SKU"
                maxlength="100"
                autocomplete="off"
            >
            @if ($search !== '')
                <a class="dash-search-clear" href="{{ route('dashboard.products.index', array_filter(['category' => $activeCategory, 'status' => $activeStatus, 'per_page' => $perPage !== 50 ? $perPage : null])) }}" title="Clear search" aria-label="Clear search">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="M6 6l12 12M18 6 6 18"/></svg>
                </a>
            @endif
            <button type="submit" class="dash-search-submit">Search</button>
        </div>
    </div>

    <!-- Hierarchical Category Dropdown -->
    <div class="dash-select-wrap">
        <select name="category" class="dash-select" onchange="document.getElementById('productFilterForm').submit()" aria-label="Filter by category">
            <option value="">All Categories</option>
            @foreach ($categories as $cat)
                <option value="{{ $cat['name'] }}" {{ ($activeCategory ?? '') === $cat['name'] || ($activeCategory ?? '') === $cat['airtable_id'] ? 'selected' : '' }}>
                    {{ $cat['label'] }}
                </option>
            @endforeach
        </select>
        <svg class="dash-select-chevron" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><polyline points="6 9 12 15 18 9"/></svg>
    </div>

    <!-- Status Filter Dropdown -->
    <div class="dash-select-wrap">
        <select name="status" class="dash-select" onchange="document.getElementById('productFilterForm').submit()" aria-label="Filter by status">
            <option value="">All Statuses</option>
            <option value="publish" {{ ($activeStatus ?? '') === 'publish' ? 'selected' : '' }}>Published</option>
            <option value="draft" {{ ($activeStatus ?? '') === 'draft' ? 'selected' : '' }}>Draft</option>
            <option value="inactive" {{ ($activeStatus ?? '') === 'inactive' ? 'selected' : '' }}>Inactive</option>
        </select>
        <svg class="dash-select-chevron" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><polyline points="6 9 12 15 18 9"/></svg>
    </div>

    <!-- Items Per Page Dropdown -->
    <div class="dash-select-wrap">
        <select name="per_page" class="dash-select" onchange="document.getElementById('productFilterForm').submit()" aria-label="Products per page">
            @foreach ($perPageOptions as $option)
                <option value="{{ $option }}" {{ $perPage === $option ? 'selected' : '' }}>
                    Show {{ $option }}
                </option>
            @endforeach
        </select>
        <svg class="dash-select-chevron" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><polyline points="6 9 12 15 18 9"/></svg>
    </div>
</form>

<!-- Airtable-Style Products Table -->
<div class="dash-airtable-wrap">
    <table class="dash-airtable-table">
        <thead>
            <!-- Group Header Tier 1 -->
            <tr class="dash-group-header-row">
                <th scope="colgroup" colspan="6" class="dash-group-th is-primary" style="text-align: center;">
                    <span class="dash-group-badge is-primary">1. Primary ID &amp; Visuals</span>
                </th>
                <th scope="colgroup" colspan="3" class="dash-group-th is-media" style="text-align: center;">
                    <span class="dash-group-badge is-media">2. Media &amp; Schematics</span>
                </th>
                <th scope="colgroup" colspan="4" class="dash-group-th is-specs" style="text-align: center;">
                    <span class="dash-group-badge is-specs">3. Specifications &amp; Configurator</span>
                </th>
                <th scope="colgroup" colspan="3" class="dash-group-th is-supplier" style="text-align: center;">
                    <span class="dash-group-badge is-supplier">4. Supplier &amp; Inventory</span>
                </th>
                <th scope="colgroup" colspan="5" class="dash-group-th is-docs" style="text-align: center;">
                    <span class="dash-group-badge is-docs">5. Technical Documents</span>
                </th>
                <th scope="colgroup" colspan="5" class="dash-group-th is-seo" style="text-align: center;">
                    <span class="dash-group-badge is-seo">6. Copy &amp; SEO</span>
                </th>
                <th scope="colgroup" colspan="1" class="dash-group-th is-audit" style="text-align: center;">
                    <span class="dash-group-badge is-audit">7. Audit</span>
                </th>
            </tr>
            <!-- Column Header Tier 2 -->
            <tr>
                <!-- Group 1: Primary ID & Visuals (6 cols) -->
                <th scope="col" class="dash-col-th is-primary" style="width: 70px; text-align: center;">Order</th>
                <th scope="col" class="dash-col-th is-primary" style="width: 80px; text-align: center;">Product Image</th>
                <th scope="col" class="dash-col-th is-primary" style="min-width: 130px;">Product Code</th>
                <th scope="col" class="dash-col-th is-primary" style="min-width: 220px;">Product Name</th>
                <th scope="col" class="dash-col-th is-primary" style="width: 100px;">Status</th>
                <th scope="col" class="dash-col-th is-primary" style="min-width: 160px;">Category</th>

                <!-- Group 2: Media & Schematics (3 cols) -->
                <th scope="col" class="dash-col-th is-media" style="min-width: 220px;">Product Gallery</th>
                <th scope="col" class="dash-col-th is-media" style="width: 80px; text-align: center;">Product Dimension</th>
                <th scope="col" class="dash-col-th is-media" style="min-width: 220px;">Technical Icons</th>

                <!-- Group 3: Specifications & Configurator (4 cols) -->
                <th scope="col" class="dash-col-th is-specs" style="min-width: 200px;">Attributes</th>
                <th scope="col" class="dash-col-th is-specs" style="min-width: 180px;">Options</th>
                <th scope="col" class="dash-col-th is-specs" style="min-width: 160px;">SKU Mapping</th>
                <th scope="col" class="dash-col-th is-specs" style="width: 80px; text-align: center;">Dimming</th>

                <!-- Group 4: Supplier & Inventory (3 cols) -->
                <th scope="col" class="dash-col-th is-supplier" style="min-width: 140px;">Supplier Name</th>
                <th scope="col" class="dash-col-th is-supplier" style="min-width: 130px;">Supplier Code</th>
                <th scope="col" class="dash-col-th is-supplier" style="min-width: 110px;">Stock</th>

                <!-- Group 5: Technical Documents (5 cols) -->
                <th scope="col" class="dash-col-th is-docs" style="width: 85px; text-align: center;">Datasheet</th>
                <th scope="col" class="dash-col-th is-docs" style="min-width: 120px;">Datasheet File</th>
                <th scope="col" class="dash-col-th is-docs" style="min-width: 140px;">Installation Guide</th>
                <th scope="col" class="dash-col-th is-docs" style="min-width: 120px;">User Manual</th>
                <th scope="col" class="dash-col-th is-docs" style="min-width: 100px;">IES File</th>

                <!-- Group 6: Copy & SEO (5 cols) -->
                <th scope="col" class="dash-col-th is-seo" style="min-width: 160px;">URL Slug</th>
                <th scope="col" class="dash-col-th is-seo" style="min-width: 220px;">Product Description</th>
                <th scope="col" class="dash-col-th is-seo" style="min-width: 180px;">Meta Title</th>
                <th scope="col" class="dash-col-th is-seo" style="min-width: 200px;">Meta Description</th>
                <th scope="col" class="dash-col-th is-seo" style="min-width: 160px;">Meta Keywords</th>

                <!-- Group 7: Audit (1 col) -->
                <th scope="col" class="dash-col-th is-audit" style="min-width: 140px;">Updated</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($products as $product)
                @php
                    $cover = $product->coverUrl();
                    $gallery = $product->galleryImageUrls();
                    $dimension = $product->dimensionUrl();
                    $techIcons = $product->technicalIconUrls();
                    $isPublished = $product->isPublished();
                    $datasheetStatus = $product->datasheetStatus();
                    $datasheetFile = $product->datasheetFileUrl();
                    $manual = $product->manualUrl();
                    $guide = $product->guideUrl();
                    $ies = $product->iesUrl();
                @endphp
                <tr>
                    <!-- 1. Primary ID & Visuals -->
                    <!-- Order -->
                    <td style="text-align: center;">
                        @if ($product->sort_order !== null)
                            <span class="dash-code-badge" style="font-size: 11px; font-weight: 600;">#{{ $product->sort_order }}</span>
                        @endif
                    </td>

                    <!-- Product Image (Cover) -->
                    <td style="text-align: center;">
                        @if (filled($cover))
                            <div class="dash-preview-thumb" data-popover-img="{{ $cover }}" title="Hover to enlarge cover image">
                                <img src="{{ $cover }}" alt="{{ $product->product_name }}" loading="lazy">
                            </div>
                        @else
                            <div class="dash-preview-thumb is-empty" title="No cover image">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" style="width: 18px; height: 18px;"><rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8.5" cy="8.5" r="1.5"/><polyline points="21 15 16 10 5 21"/></svg>
                            </div>
                        @endif
                    </td>

                    <!-- Product Code (SKU) -->
                    <td>
                        @if (filled($product->product_code))
                            <span class="dash-code-badge" style="font-weight: 600;">{{ $product->product_code }}</span>
                        @endif
                    </td>

                    <!-- Product Name -->
                    <td>
                        <div class="dash-product-cell">
                            <a href="{{ $product->publicPath() }}" target="_blank" rel="noopener noreferrer" class="dash-product-title" style="cursor: pointer; font-weight: 600; color: var(--dash-ink); text-decoration: none; display: inline-block; line-height: 1.35;" title="Open product page: {{ $product->product_name }}">
                                {{ $product->product_name }}
                            </a>
                            @if (filled($product->airtable_id))
                                <span class="dash-airtable-id">{{ $product->airtable_id }}</span>
                            @endif
                        </div>
                    </td>

                    <!-- Status -->
                    <td>
                        <span class="dash-pill {{ $isPublished ? 'is-active' : 'is-inactive' }}">
                            {{ $product->status ? \Illuminate\Support\Str::headline($product->status) : 'Draft' }}
                        </span>
                    </td>

                    <!-- Category -->
                    <td>
                        <div class="dash-tag-list">
                            @if (filled($product->category))
                                <span class="dash-tag is-primary">{{ $product->category }}</span>
                            @endif
                            @if (is_array($product->categories))
                                @foreach ($product->categories as $extraCat)
                                    @if ($extraCat !== $product->category && filled($extraCat))
                                        <span class="dash-tag">{{ $extraCat }}</span>
                                    @endif
                                @endforeach
                            @endif
                        </div>
                    </td>

                    <!-- 2. Media & Schematics -->
                    <!-- Product Gallery -->
                    <td>
                        @if (!empty($gallery) && count($gallery) > 0)
                            <div class="dash-gallery-stack">
                                @foreach ($gallery as $gImg)
                                    <div class="dash-gallery-item" data-popover-img="{{ $gImg }}" title="Hover to enlarge gallery image">
                                        <img src="{{ $gImg }}" alt="Gallery Image" loading="lazy">
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </td>

                    <!-- Product Dimension -->
                    <td style="text-align: center;">
                        @if (filled($dimension))
                            <div class="dash-dimension-thumb" data-popover-img="{{ $dimension }}" title="Hover to view dimension schematic">
                                <img src="{{ $dimension }}" alt="Dimension Diagram" loading="lazy">
                            </div>
                        @endif
                    </td>

                    <!-- Technical Icons -->
                    <td>
                        @if (!empty($techIcons) && count($techIcons) > 0)
                            <div class="dash-tech-icons">
                                @foreach ($techIcons as $tIcon)
                                    <div class="dash-tech-icon" data-popover-img="{{ $tIcon }}" title="Technical icon">
                                        <img src="{{ $tIcon }}" alt="Icon" loading="lazy">
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </td>

                    <!-- 3. Specifications & Configurator -->
                    <!-- Attributes -->
                    <td>
                        @php
                            $features = is_array($product->product_features) ? $product->product_features : [];
                            $featureCount = count($features);
                        @endphp
                        @if ($featureCount > 0)
                            <button type="button" class="dash-specs-cell-btn" data-specs-trigger="{{ $product->id }}" data-specs-tab="attributes" title="Click to inspect all {{ $featureCount }} attributes &amp; features">
                                <div class="dash-specs-cell-header">
                                    <span class="dash-tag is-specs-tag">{{ $featureCount }} {{ \Illuminate\Support\Str::plural('Spec', $featureCount) }}</span>
                                    <span class="dash-specs-expand-icon">
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M15 3h6v6"/><path d="M9 21H3v-6"/><path d="M21 3l-7 7"/><path d="M3 21l7-7"/></svg>
                                    </span>
                                </div>
                                <div class="dash-tag-list" style="display: flex; flex-direction: column; gap: 3px; margin-top: 4px;">
                                    @foreach (array_slice($features, 0, 2, true) as $fKey => $fVal)
                                        @php
                                            $valStr = '';
                                            if (is_array($fVal)) {
                                                $vals = [];
                                                foreach ($fVal as $item) {
                                                    if (is_array($item) && isset($item['value'])) {
                                                        $vals[] = $item['value'];
                                                    } elseif (is_string($item)) {
                                                        $vals[] = $item;
                                                    }
                                                }
                                                $valStr = implode(', ', $vals);
                                            } elseif (is_scalar($fVal)) {
                                                $valStr = (string) $fVal;
                                            }
                                        @endphp
                                        <span class="dash-tag" style="font-size: 10.5px; max-width: 210px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;" title="{{ $fKey }}: {{ $valStr }}">
                                            <strong style="color: var(--dash-ink);">{{ $fKey }}:</strong> {{ $valStr }}
                                        </span>
                                    @endforeach
                                    @if ($featureCount > 2)
                                        <span style="font-size: 10px; color: var(--dash-muted); font-weight: 600;">+{{ $featureCount - 2 }} more...</span>
                                    @endif
                                </div>
                            </button>
                        @else
                            <span style="color: var(--dash-muted); font-size: 11px;">—</span>
                        @endif
                    </td>

                    <!-- Options -->
                    <td>
                        @php
                            $options = is_array($product->options) ? $product->options : [];
                            $optionCount = count($options);
                        @endphp
                        @if ($optionCount > 0)
                            <button type="button" class="dash-specs-cell-btn" data-specs-trigger="{{ $product->id }}" data-specs-tab="options" title="Click to inspect configurator option tree ({{ $optionCount }} groups)">
                                <div class="dash-specs-cell-header">
                                    <span class="dash-tag is-specs-tag">{{ $optionCount }} {{ \Illuminate\Support\Str::plural('Group', $optionCount) }}</span>
                                    <span class="dash-specs-expand-icon">
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M15 3h6v6"/><path d="M9 21H3v-6"/><path d="M21 3l-7 7"/><path d="M3 21l7-7"/></svg>
                                    </span>
                                </div>
                                <div class="dash-tag-list" style="display: flex; flex-direction: column; gap: 3px; margin-top: 4px;">
                                    @foreach (array_slice($options, 0, 2, true) as $optGroup => $optItems)
                                        @php
                                            $choices = [];
                                            if (is_array($optItems)) {
                                                foreach ($optItems as $item) {
                                                    if (is_array($item) && isset($item['name'])) {
                                                        $choices[] = $item['name'];
                                                    } elseif (is_string($item)) {
                                                        $choices[] = $item;
                                                    }
                                                }
                                            }
                                            $choiceStr = implode(', ', $choices);
                                        @endphp
                                        <span class="dash-tag" style="font-size: 10.5px; max-width: 190px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;" title="{{ $optGroup }}: {{ $choiceStr }}">
                                            <strong style="color: var(--dash-ink);">{{ $optGroup }}:</strong> {{ $choiceStr ?: (count($choices).' choices') }}
                                        </span>
                                    @endforeach
                                    @if ($optionCount > 2)
                                        <span style="font-size: 10px; color: var(--dash-muted); font-weight: 600;">+{{ $optionCount - 2 }} more...</span>
                                    @endif
                                </div>
                            </button>
                        @else
                            <span style="color: var(--dash-muted); font-size: 11px;">—</span>
                        @endif
                    </td>

                    <!-- SKU Mapping -->
                    <td>
                        @php
                            $mappings = is_array($product->sku_mappings) ? $product->sku_mappings : [];
                            $mappingCount = count($mappings);
                        @endphp
                        @if ($mappingCount > 0)
                            <button type="button" class="dash-specs-cell-btn" data-specs-trigger="{{ $product->id }}" data-specs-tab="sku" title="Click to inspect {{ $mappingCount }} variant SKU mappings">
                                <div class="dash-specs-cell-header">
                                    <span class="dash-pill is-active" style="font-size: 10.5px; font-weight: 700;">
                                        {{ $mappingCount }} {{ \Illuminate\Support\Str::plural('Variant', $mappingCount) }}
                                    </span>
                                    <span class="dash-specs-expand-icon">
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M15 3h6v6"/><path d="M9 21H3v-6"/><path d="M21 3l-7 7"/><path d="M3 21l7-7"/></svg>
                                    </span>
                                </div>
                                <div class="dash-tag-list" style="gap: 3px; max-width: 180px; margin-top: 4px;">
                                    @foreach (array_slice(array_values($mappings), 0, 2) as $skuCode)
                                        @if (filled($skuCode))
                                            <span class="dash-code-badge" style="font-size: 10.5px;">{{ $skuCode }}</span>
                                        @endif
                                    @endforeach
                                    @if ($mappingCount > 2)
                                        <span style="font-size: 10px; color: var(--dash-muted); font-weight: 600;">+{{ $mappingCount - 2 }}</span>
                                    @endif
                                </div>
                            </button>
                        @else
                            <span style="color: var(--dash-muted); font-size: 11px;">—</span>
                        @endif

                        <!-- Product Specs JSON Payload for Modal Inspector -->
                        <script type="application/json" id="product-specs-data-{{ $product->id }}">
                        {!! json_encode([
                            'id' => $product->id,
                            'product_name' => $product->product_name,
                            'cover_url' => $cover,
                            'category' => $product->category,
                            'product_features' => $product->product_features ?? [],
                            'options' => $product->options ?? [],
                            'sku_mappings' => $product->sku_mappings ?? [],
                        ], JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) !!}
                        </script>
                    </td>

                    <!-- Dimming Control -->
                    <td style="text-align: center;">
                        @if ($product->dimming_control)
                            <span class="dash-pill is-active" style="font-size: 10.5px;">Yes</span>
                        @else
                            <span style="color: var(--dash-muted); font-size: 11px;">No</span>
                        @endif
                    </td>

                    <!-- 4. Supplier & Inventory -->
                    <!-- Supplier Name -->
                    <td>
                        @if (filled($product->supplier_name))
                            <span class="dash-tag" style="font-size: 11px;">{{ $product->supplier_name }}</span>
                        @endif
                    </td>

                    <!-- Supplier Code -->
                    <td>
                        @if (filled($product->supplier_code))
                            <span class="dash-code-badge">{{ $product->supplier_code }}</span>
                        @endif
                    </td>

                    <!-- Stock Status -->
                    <td>
                        @if (filled($product->stocked_item))
                            <span class="dash-tag" style="font-size: 10.5px; width: fit-content;">{{ $product->stocked_item }}</span>
                        @endif
                    </td>

                    <!-- 5. Technical Documents -->
                    <!-- Datasheet (Status Yes/No) -->
                    <td style="text-align: center;">
                        @if ($datasheetStatus === 'Yes')
                            <span class="dash-pill is-active" style="font-size: 10.5px;">Yes</span>
                        @elseif ($datasheetStatus === 'No')
                            <span style="color: var(--dash-muted); font-size: 11px;">No</span>
                        @elseif (filled($datasheetStatus))
                            <span class="dash-pill" style="font-size: 10.5px;">{{ $datasheetStatus }}</span>
                        @endif
                    </td>

                    <!-- Datasheet File -->
                    <td>
                        @if (filled($datasheetFile))
                            <a href="{{ $datasheetFile }}" target="_blank" rel="noopener noreferrer" class="dash-asset-chip" title="Download Datasheet PDF">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
                                PDF
                            </a>
                        @endif
                    </td>

                    <!-- Installation Guide File -->
                    <td>
                        @if (filled($guide))
                            <a href="{{ $guide }}" target="_blank" rel="noopener noreferrer" class="dash-asset-chip" title="Download Installation Guide">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
                                Guide
                            </a>
                        @endif
                    </td>

                    <!-- User Manual -->
                    <td>
                        @if (filled($manual))
                            <a href="{{ $manual }}" target="_blank" rel="noopener noreferrer" class="dash-asset-chip" title="Download User Manual">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
                                Manual
                            </a>
                        @endif
                    </td>

                    <!-- IES File -->
                    <td>
                        @if (filled($ies))
                            <a href="{{ $ies }}" target="_blank" rel="noopener noreferrer" class="dash-asset-chip" title="Download IES Photometric File">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
                                IES
                            </a>
                        @endif
                    </td>

                    <!-- 6. Copy & SEO -->
                    <!-- URL Slug -->
                    <td>
                        @if (filled($product->slug))
                            <span class="dash-code-badge" title="Slug: {{ $product->slug }}">{{ $product->slug }}</span>
                        @endif
                    </td>

                    <!-- Product Description -->
                    <td>
                        @if (filled($product->product_description))
                            @php
                                $descLen = mb_strlen(strip_tags($product->product_description));
                            @endphp
                            <div class="dash-text-snippet" title="{{ strip_tags($product->product_description) }} ({{ $descLen }} chars)">
                                {{ \Illuminate\Support\Str::limit(strip_tags($product->product_description), 120) }}
                                <span style="display: block; font-size: 10px; color: var(--dash-muted); margin-top: 2px;">{{ $descLen }} chars</span>
                            </div>
                        @endif
                    </td>

                    <!-- Meta Title -->
                    <td>
                        @if (filled($product->meta_title))
                            @php
                                $titleLen = mb_strlen($product->meta_title);
                            @endphp
                            <div class="dash-text-snippet" title="{{ $product->meta_title }} ({{ $titleLen }} chars)">
                                {{ $product->meta_title }}
                                <span style="display: block; font-size: 10px; margin-top: 2px; color: {{ $titleLen > 60 ? '#f43f5e' : ($titleLen < 30 ? '#f59e0b' : 'var(--dash-muted)') }}; font-weight: {{ $titleLen > 60 || $titleLen < 30 ? '600' : 'normal' }};">
                                    {{ $titleLen }} chars {{ $titleLen > 60 ? '(&gt;60 max)' : ($titleLen < 30 ? '(&lt;30 min)' : '') }}
                                </span>
                            </div>
                        @endif
                    </td>

                    <!-- Meta Description -->
                    <td>
                        @if (filled($product->meta_description))
                            @php
                                $metaDescLen = mb_strlen($product->meta_description);
                            @endphp
                            <div class="dash-text-snippet" title="{{ $product->meta_description }} ({{ $metaDescLen }} chars)">
                                {{ $product->meta_description }}
                                <span style="display: block; font-size: 10px; margin-top: 2px; color: {{ $metaDescLen > 160 ? '#f43f5e' : ($metaDescLen < 70 ? '#f59e0b' : 'var(--dash-muted)') }}; font-weight: {{ $metaDescLen > 160 || $metaDescLen < 70 ? '600' : 'normal' }};">
                                    {{ $metaDescLen }} chars {{ $metaDescLen > 160 ? '(&gt;160 max)' : ($metaDescLen < 70 ? '(&lt;70 min)' : '') }}
                                </span>
                            </div>
                        @endif
                    </td>

                    <!-- Meta Keywords -->
                    <td>
                        @if (filled($product->meta_keywords))
                            <div class="dash-tag-list">
                                @foreach (array_slice(explode(',', (string) $product->meta_keywords), 0, 3) as $kw)
                                    @if (filled(trim($kw)))
                                        <span class="dash-tag" style="font-size: 10px;">{{ trim($kw) }}</span>
                                    @endif
                                @endforeach
                            </div>
                        @endif
                    </td>

                    <!-- 7. Audit -->
                    <!-- Updated -->
                    <td>
                        <div class="dash-updated" style="gap: 2px;">
                            <span class="dash-updated-value" style="font-size: 11.5px;">
                                <strong>{{ $product->updater?->name ?? 'Airtable Sync' }}</strong>
                                @if ($product->updated_at)
                                    <span class="dash-updated-sep" aria-hidden="true">·</span>
                                    <time datetime="{{ $product->updated_at->toIso8601String() }}">{{ $product->updated_at->timezone(config('app.timezone'))->format('j M, g:ia') }}</time>
                                @endif
                            </span>
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="27">
                        <div class="dash-card dash-empty">
                            {{ $search === '' ? 'No products yet. Run Sync to pull from Airtable.' : 'No products match "' . $search . '".' }}
                        </div>
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

<div style="margin-top: 18px;">
    {{ $products->links('dashboard.partials.pagination') }}
</div>

<!-- Floating Hover Image Popover Container -->
<div id="dash-global-img-popover" class="dash-img-popover">
    <img id="dash-global-img-popover-src" src="" alt="Enlarged Preview">
</div>

<!-- Specifications & Configurator Inspector Modal -->
<div id="dash-specs-modal" class="dash-specs-modal" aria-hidden="true" role="dialog" aria-modal="true">
    <div class="dash-specs-backdrop" data-specs-close></div>
    <div class="dash-specs-dialog">
        <!-- Modal Header -->
        <div class="dash-specs-header">
            <div class="dash-specs-header-main">
                <div id="dash-specs-modal-img-wrap" class="dash-specs-modal-img-wrap">
                    <img id="dash-specs-modal-img" src="" alt="Product Cover">
                </div>
                <div class="dash-specs-title-wrap">
                    <h3 id="dash-specs-modal-title" class="dash-specs-title">Product Name</h3>
                    <div class="dash-specs-subtitle">
                        <span id="dash-specs-modal-category" class="dash-tag is-primary" style="font-size: 11px;">Category</span>
                    </div>
                </div>
            </div>
            <div class="dash-specs-actions">
                <button type="button" class="dash-specs-close-btn" data-specs-close aria-label="Close inspector modal">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                </button>
            </div>
        </div>

        <!-- Segmented Nav Tabs -->
        <div class="dash-specs-nav-wrap">
            <div class="dash-specs-tabs-segmented">
                <button type="button" class="dash-specs-tab-btn is-active" data-specs-tab-target="attributes">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/></svg>
                    <span>Attributes</span>
                    <span id="dash-specs-count-attributes" class="dash-specs-tab-pill">0</span>
                </button>
                <button type="button" class="dash-specs-tab-btn" data-specs-tab-target="options">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1 0 2.83 2 2 0 0 1-2.83 0l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-2 2 2 2 0 0 1-2-2v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83 0 2 2 0 0 1 0-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1-2-2 2 2 0 0 1 2-2h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 0-2.83 2 2 0 0 1 2.83 0l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 2-2 2 2 0 0 1 2 2v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 0 2 2 0 0 1 0 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 2 2 2 2 0 0 1-2 2h-.09a1.65 1.65 0 0 0-1.51 1z"/></svg>
                    <span>Options</span>
                    <span id="dash-specs-count-options" class="dash-specs-tab-pill">0</span>
                </button>
                <button type="button" class="dash-specs-tab-btn" data-specs-tab-target="sku">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="22 12 18 12 15 21 9 3 6 12 2 12"/></svg>
                    <span>SKU Mappings</span>
                    <span id="dash-specs-count-sku" class="dash-specs-tab-pill">0</span>
                </button>
            </div>
        </div>

        <!-- Modal Body / Panels -->
        <div class="dash-specs-body">
            <!-- Panel 1: Attributes -->
            <div id="dash-specs-panel-attributes" class="dash-specs-panel is-active">
                <div id="dash-specs-attributes-content" class="dash-specs-content-wrap"></div>
            </div>

            <!-- Panel 2: Options -->
            <div id="dash-specs-panel-options" class="dash-specs-panel">
                <div id="dash-specs-options-content" class="dash-specs-groups-grid" style="padding: 0;"></div>
            </div>

            <!-- Panel 3: SKU Mappings -->
            <div id="dash-specs-panel-sku" class="dash-specs-panel">
                <div id="dash-specs-sku-content" class="dash-specs-content-wrap"></div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {
    // --- Image Popover Logic ---
    const popover = document.getElementById('dash-global-img-popover');
    const popoverImg = document.getElementById('dash-global-img-popover-src');

    if (popover && popoverImg) {
        let activeTrigger = null;

        document.querySelectorAll('[data-popover-img]').forEach(el => {
            el.addEventListener('mouseenter', () => {
                const src = el.getAttribute('data-popover-img');
                if (!src) return;

                activeTrigger = el;
                popoverImg.src = src;
                popover.classList.toggle('is-tech-icon', el.classList.contains('dash-tech-icon'));
                popover.classList.add('is-visible');

                const rect = el.getBoundingClientRect();
                let top = rect.top + (rect.height / 2);
                let left = rect.right + 12;

                if (left + 280 > window.innerWidth) {
                    left = rect.left - 272;
                }
                if (top + 130 > window.innerHeight) {
                    top = window.innerHeight - 140;
                }
                if (top - 130 < 10) {
                    top = 140;
                }

                popover.style.top = top + 'px';
                popover.style.left = left + 'px';
            });

            el.addEventListener('mouseleave', () => {
                activeTrigger = null;
                popover.classList.remove('is-visible', 'is-tech-icon');
            });
        });

        window.addEventListener('scroll', () => {
            if (activeTrigger) {
                popover.classList.remove('is-visible');
                activeTrigger = null;
            }
        }, { passive: true });
    }

    // --- Specifications & Configurator Inspector Modal Logic ---
    const specsModal = document.getElementById('dash-specs-modal');
    if (!specsModal) return;

    let currentSpecs = null;

    function openSpecsModal(productId, targetTab = 'attributes') {
        const scriptEl = document.getElementById(`product-specs-data-${productId}`);
        if (!scriptEl) return;

        try {
            currentSpecs = JSON.parse(scriptEl.textContent);
        } catch (e) {
            console.error('Failed to parse product specs JSON', e);
            return;
        }

        // Header info
        document.getElementById('dash-specs-modal-title').textContent = currentSpecs.product_name || 'Product Specifications';
        document.getElementById('dash-specs-modal-category').textContent = currentSpecs.category || 'Uncategorized';

        const imgWrap = document.getElementById('dash-specs-modal-img-wrap');
        if (currentSpecs.cover_url) {
            imgWrap.innerHTML = `<img id="dash-specs-modal-img" src="${escapeHtml(currentSpecs.cover_url)}" alt="${escapeHtml(currentSpecs.product_name || 'Product Cover')}">`;
            imgWrap.classList.remove('is-empty');
        } else {
            imgWrap.classList.add('is-empty');
            imgWrap.innerHTML = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6"><rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8.5" cy="8.5" r="1.5"/><polyline points="21 15 16 10 5 21"/></svg>';
        }

        // Render Tabs Content
        renderAttributesPanel(currentSpecs.product_features || {});
        renderOptionsPanel(currentSpecs.options || {});
        renderSkuMatrixPanel(currentSpecs.sku_mappings || {}, currentSpecs.options || {});

        // Switch to chosen tab
        switchTab(targetTab);

        // Show modal
        specsModal.classList.add('is-open');
        specsModal.setAttribute('aria-hidden', 'false');
        document.body.style.overflow = 'hidden';
    }

    function closeSpecsModal() {
        specsModal.classList.remove('is-open');
        specsModal.setAttribute('aria-hidden', 'true');
        document.body.style.overflow = '';
    }

    function switchTab(tabId) {
        document.querySelectorAll('.dash-specs-tab-btn').forEach(btn => {
            btn.classList.toggle('is-active', btn.getAttribute('data-specs-tab-target') === tabId);
        });
        document.querySelectorAll('.dash-specs-panel').forEach(panel => {
            panel.classList.toggle('is-active', panel.id === `dash-specs-panel-${tabId}`);
        });
    }

    // Tab buttons click
    document.querySelectorAll('.dash-specs-tab-btn').forEach(btn => {
        btn.addEventListener('click', () => {
            const target = btn.getAttribute('data-specs-tab-target');
            switchTab(target);
        });
    });

    // Triggers click from table cells
    document.querySelectorAll('[data-specs-trigger]').forEach(btn => {
        btn.addEventListener('click', (e) => {
            e.preventDefault();
            const productId = btn.getAttribute('data-specs-trigger');
            const initialTab = btn.getAttribute('data-specs-tab') || 'attributes';
            openSpecsModal(productId, initialTab);
        });
    });

    // Close button / backdrop
    document.querySelectorAll('[data-specs-close]').forEach(el => {
        el.addEventListener('click', closeSpecsModal);
    });

    // Escape key
    window.addEventListener('keydown', (e) => {
        if (e.key === 'Escape' && specsModal.classList.contains('is-open')) {
            closeSpecsModal();
        }
    });

    // --- Render Panel Helpers ---

    function renderAttributesPanel(features) {
        const container = document.getElementById('dash-specs-attributes-content');
        const countBadge = document.getElementById('dash-specs-count-attributes');
        const keys = Object.keys(features);
        countBadge.textContent = keys.length;

        if (keys.length === 0) {
            container.innerHTML = '<div style="padding: 24px; text-align: center; color: var(--dash-muted);">No product attributes configured yet.</div>';
            return;
        }

        let html = `
            <table class="dash-specs-table" id="dash-specs-attr-table">
                <thead>
                    <tr>
                        <th style="width: 220px;">Attribute</th>
                        <th>Value(s)</th>
                    </tr>
                </thead>
                <tbody>
        `;

        keys.forEach(key => {
            const valData = features[key];
            let items = [];

            if (Array.isArray(valData)) {
                valData.forEach(item => {
                    if (typeof item === 'object' && item !== null) {
                        items.push({ value: item.value || '', icon: item.icon || '' });
                    } else if (typeof item === 'string' || typeof item === 'number') {
                        items.push({ value: String(item), icon: '' });
                    }
                });
            } else if (typeof valData === 'object' && valData !== null) {
                items.push({ value: valData.value || '', icon: valData.icon || '' });
            } else if (valData !== null && valData !== undefined) {
                items.push({ value: String(valData), icon: '' });
            }

            const valueTagsHtml = items.length > 0 
                ? items.map(item => {
                    const iconHtml = item.icon ? `<img src="${escapeHtml(item.icon)}" alt="" loading="lazy" style="width: 15px; height: 15px; object-fit: contain; flex-shrink: 0; vertical-align: middle;">` : '';
                    return `<span class="dash-tag" style="display: inline-flex; align-items: center; gap: 6px; font-size: 11.5px; font-weight: 600; color: var(--dash-ink);">${iconHtml}<span>${escapeHtml(item.value)}</span></span>`;
                }).join(' ')
                : '<span style="color: var(--dash-muted);">—</span>';

            html += `
                <tr>
                    <td>
                        <strong style="color: var(--dash-ink); font-size: 12.5px;">${escapeHtml(key)}</strong>
                    </td>
                    <td>
                        <div class="dash-tag-list" style="gap: 6px; flex-wrap: wrap;">${valueTagsHtml}</div>
                    </td>
                </tr>
            `;
        });

        html += `</tbody></table>`;
        container.innerHTML = html;
    }

    function renderOptionsPanel(options) {
        const container = document.getElementById('dash-specs-options-content');
        const countBadge = document.getElementById('dash-specs-count-options');
        const groupKeys = Object.keys(options);
        countBadge.textContent = groupKeys.length;

        if (groupKeys.length === 0) {
            container.innerHTML = '<div class="dash-specs-content-wrap" style="padding: 24px; text-align: center; color: var(--dash-muted);">No configurator dropdown options defined for this product.</div>';
            return;
        }

        let html = '';
        groupKeys.forEach(groupName => {
            const items = Array.isArray(options[groupName]) ? options[groupName] : [];
            html += `
                <div class="dash-specs-group-card">
                    <div class="dash-specs-group-title">
                        <span>${escapeHtml(groupName)}</span>
                        <span class="dash-tag is-specs-tag" style="font-size: 10px;">${items.length} ${items.length === 1 ? 'choice' : 'choices'}</span>
                    </div>
                    <div style="display: flex; flex-direction: column; gap: 5px;">
            `;

            items.forEach(choice => {
                const name = typeof choice === 'object' && choice.name ? choice.name : (typeof choice === 'string' ? choice : 'Option');
                const id = typeof choice === 'object' && choice.id ? choice.id : '';
                html += `
                    <div class="dash-specs-choice-item">
                        <span style="font-weight: 600; color: var(--dash-ink);">${escapeHtml(name)}</span>
                        ${id ? `<span class="dash-code-badge" style="font-size: 10px; font-weight: 700;">ID: ${escapeHtml(id)}</span>` : ''}
                    </div>
                `;
            });

            html += `</div></div>`;
        });

        container.innerHTML = html;
    }

    function renderSkuMatrixPanel(mappings, options) {
        const container = document.getElementById('dash-specs-sku-content');
        const countBadge = document.getElementById('dash-specs-count-sku');

        // Build ID -> Choice Name lookup
        const idLookup = {};
        if (typeof options === 'object' && options !== null) {
            Object.values(options).forEach(groupList => {
                if (Array.isArray(groupList)) {
                    groupList.forEach(item => {
                        if (item && item.id && item.name) {
                            idLookup[String(item.id)] = item.name;
                        }
                    });
                }
            });
        }

        const keys = Object.keys(mappings);
        countBadge.textContent = keys.length;

        if (keys.length === 0) {
            container.innerHTML = '<div style="padding: 24px; text-align: center; color: var(--dash-muted);">No SKU variant mappings configured.</div>';
            return;
        }

        let html = `
            <table class="dash-specs-table" id="dash-specs-sku-table">
                <thead>
                    <tr>
                        <th style="width: 50px; text-align: center;">#</th>
                        <th style="width: 180px;">Option IDs</th>
                        <th>Resolved Choices</th>
                        <th style="width: 180px;">Mapped SKU Code</th>
                    </tr>
                </thead>
                <tbody>
        `;

        keys.forEach((comboKey, idx) => {
            const skuCode = mappings[comboKey];
            const ids = comboKey.split(',').map(s => s.trim()).filter(Boolean);
            const resolvedLabels = ids.map(id => idLookup[id] ? `<span class="dash-tag" style="font-size: 11px;">${escapeHtml(idLookup[id])}</span>` : `<span class="dash-tag" style="font-size: 11px; opacity: 0.7;">#${escapeHtml(id)}</span>`).join(' ');

            html += `
                <tr>
                    <td style="text-align: center; color: var(--dash-muted); font-size: 11px;">${idx + 1}</td>
                    <td>
                        <span class="dash-code-badge" style="font-size: 11px; font-weight: 600;">${escapeHtml(comboKey)}</span>
                    </td>
                    <td>
                        <div class="dash-tag-list" style="gap: 4px; flex-wrap: wrap;">${resolvedLabels}</div>
                    </td>
                    <td>
                        <span class="dash-code-badge" style="font-weight: 700; font-size: 12px; color: var(--dash-ink);">
                            ${escapeHtml(skuCode)}
                        </span>
                    </td>
                </tr>
            `;
        });

        html += `</tbody></table>`;
        container.innerHTML = html;
    }

    function escapeHtml(str) {
        if (!str) return '';
        return String(str)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }
});
</script>
@endpush
@endsection
