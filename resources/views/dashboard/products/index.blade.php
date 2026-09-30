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
                <th scope="colgroup" colspan="4" class="dash-group-th is-supplier" style="text-align: center;">
                    <span class="dash-group-badge is-supplier">3. Supplier &amp; Inventory</span>
                </th>
                <th scope="colgroup" colspan="5" class="dash-group-th is-docs" style="text-align: center;">
                    <span class="dash-group-badge is-docs">4. Technical Documents</span>
                </th>
                <th scope="colgroup" colspan="5" class="dash-group-th is-seo" style="text-align: center;">
                    <span class="dash-group-badge is-seo">5. Copy &amp; SEO</span>
                </th>
                <th scope="colgroup" colspan="1" class="dash-group-th is-audit" style="text-align: center;">
                    <span class="dash-group-badge is-audit">6. Audit</span>
                </th>
            </tr>
            <!-- Column Header Tier 2 -->
            <tr>
                <!-- Group 1: Primary ID & Visuals (6 cols) -->
                <th scope="col" style="width: 70px; text-align: center;">Order</th>
                <th scope="col" style="width: 80px; text-align: center;">Product Image</th>
                <th scope="col" style="min-width: 130px;">Product Code</th>
                <th scope="col" style="min-width: 220px;">Product Name</th>
                <th scope="col" style="width: 100px;">Status</th>
                <th scope="col" style="min-width: 160px;">Category</th>

                <!-- Group 2: Media & Schematics (3 cols) -->
                <th scope="col" style="min-width: 220px;">Product Gallery</th>
                <th scope="col" style="width: 80px; text-align: center;">Product Dimension</th>
                <th scope="col" style="min-width: 220px;">Technical Icons</th>

                <!-- Group 3: Supplier & Inventory (4 cols) -->
                <th scope="col" style="min-width: 140px;">Supplier Name</th>
                <th scope="col" style="min-width: 130px;">Supplier Code</th>
                <th scope="col" style="min-width: 130px;">Type &amp; Stock</th>
                <th scope="col" style="width: 80px; text-align: center;">Dimming</th>

                <!-- Group 4: Technical Documents (5 cols) -->
                <th scope="col" style="width: 85px; text-align: center;">Datasheet</th>
                <th scope="col" style="min-width: 120px;">Datasheet File</th>
                <th scope="col" style="min-width: 140px;">Installation Guide</th>
                <th scope="col" style="min-width: 120px;">User Manual</th>
                <th scope="col" style="min-width: 100px;">IES File</th>

                <!-- Group 5: Copy & SEO (5 cols) -->
                <th scope="col" style="min-width: 160px;">URL Slug</th>
                <th scope="col" style="min-width: 220px;">Product Description</th>
                <th scope="col" style="min-width: 180px;">Meta Title</th>
                <th scope="col" style="min-width: 200px;">Meta Description</th>
                <th scope="col" style="min-width: 160px;">Meta Keywords</th>

                <!-- Group 6: Audit (1 col) -->
                <th scope="col" style="min-width: 140px;">Updated</th>
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
                        @else
                            <span style="color: var(--dash-muted); font-size: 11px;">—</span>
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
                        @else
                            <span style="color: var(--dash-muted); font-size: 11px;">—</span>
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
                        @else
                            <span style="color: var(--dash-muted); font-size: 11px;">—</span>
                        @endif
                    </td>

                    <!-- Product Dimension -->
                    <td style="text-align: center;">
                        @if (filled($dimension))
                            <div class="dash-dimension-thumb" data-popover-img="{{ $dimension }}" title="Hover to view dimension schematic">
                                <img src="{{ $dimension }}" alt="Dimension Diagram" loading="lazy">
                            </div>
                        @else
                            <span style="color: var(--dash-muted); font-size: 11px;">—</span>
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
                        @else
                            <span style="color: var(--dash-muted); font-size: 11px;">—</span>
                        @endif
                    </td>

                    <!-- 3. Supplier & Inventory -->
                    <!-- Supplier Name -->
                    <td>
                        @if (filled($product->supplier_name))
                            <span class="dash-tag" style="font-size: 11px;">{{ $product->supplier_name }}</span>
                        @else
                            <span style="color: var(--dash-muted); font-size: 11px;">—</span>
                        @endif
                    </td>

                    <!-- Supplier Code -->
                    <td>
                        @if (filled($product->supplier_code))
                            <span class="dash-code-badge">{{ $product->supplier_code }}</span>
                        @else
                            <span style="color: var(--dash-muted); font-size: 11px;">—</span>
                        @endif
                    </td>

                    <!-- Type & Stock -->
                    <td>
                        <div style="display: flex; flex-direction: column; gap: 3px;">
                            @if (filled($product->product_type))
                                <span style="font-size: 12px; font-weight: 600;">{{ $product->product_type }}</span>
                            @endif
                            @if (filled($product->stocked_item))
                                <span class="dash-tag" style="font-size: 10.5px; width: fit-content;">{{ $product->stocked_item }}</span>
                            @endif
                        </div>
                    </td>

                    <!-- Dimming Control -->
                    <td style="text-align: center;">
                        @if ($product->dimming_control)
                            <span class="dash-pill is-active" style="font-size: 10.5px;">Yes</span>
                        @else
                            <span style="color: var(--dash-muted); font-size: 11px;">No</span>
                        @endif
                    </td>

                    <!-- 4. Technical Documents -->
                    <!-- Datasheet (Status Yes/No) -->
                    <td style="text-align: center;">
                        @if ($datasheetStatus === 'Yes')
                            <span class="dash-pill is-active" style="font-size: 10.5px;">Yes</span>
                        @elseif ($datasheetStatus === 'No')
                            <span style="color: var(--dash-muted); font-size: 11px;">No</span>
                        @elseif (filled($datasheetStatus))
                            <span class="dash-pill" style="font-size: 10.5px;">{{ $datasheetStatus }}</span>
                        @else
                            <span style="color: var(--dash-muted); font-size: 11px;">—</span>
                        @endif
                    </td>

                    <!-- Datasheet File -->
                    <td>
                        @if (filled($datasheetFile))
                            <a href="{{ $datasheetFile }}" target="_blank" rel="noopener noreferrer" class="dash-asset-chip" title="Download Datasheet PDF">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
                                PDF
                            </a>
                        @else
                            <span style="color: var(--dash-muted); font-size: 11px;">—</span>
                        @endif
                    </td>

                    <!-- Installation Guide File -->
                    <td>
                        @if (filled($guide))
                            <a href="{{ $guide }}" target="_blank" rel="noopener noreferrer" class="dash-asset-chip" title="Download Installation Guide">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
                                Guide
                            </a>
                        @else
                            <span style="color: var(--dash-muted); font-size: 11px;">—</span>
                        @endif
                    </td>

                    <!-- User Manual -->
                    <td>
                        @if (filled($manual))
                            <a href="{{ $manual }}" target="_blank" rel="noopener noreferrer" class="dash-asset-chip" title="Download User Manual">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
                                Manual
                            </a>
                        @else
                            <span style="color: var(--dash-muted); font-size: 11px;">—</span>
                        @endif
                    </td>

                    <!-- IES File -->
                    <td>
                        @if (filled($ies))
                            <a href="{{ $ies }}" target="_blank" rel="noopener noreferrer" class="dash-asset-chip" title="Download IES Photometric File">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
                                IES
                            </a>
                        @else
                            <span style="color: var(--dash-muted); font-size: 11px;">—</span>
                        @endif
                    </td>

                    <!-- 5. Copy & SEO -->
                    <!-- URL Slug -->
                    <td>
                        @if (filled($product->slug))
                            <span class="dash-code-badge" title="Slug: {{ $product->slug }}">{{ $product->slug }}</span>
                        @else
                            <span style="color: var(--dash-muted); font-size: 11px;">—</span>
                        @endif
                    </td>

                    <!-- Product Description -->
                    <td>
                        @if (filled($product->product_description))
                            <div class="dash-text-snippet" title="{{ strip_tags($product->product_description) }}">
                                {{ \Illuminate\Support\Str::limit(strip_tags($product->product_description), 120) }}
                            </div>
                        @else
                            <span style="color: var(--dash-muted); font-size: 11px;">—</span>
                        @endif
                    </td>

                    <!-- Meta Title -->
                    <td>
                        @if (filled($product->meta_title))
                            <div class="dash-text-snippet" title="{{ $product->meta_title }}">
                                {{ $product->meta_title }}
                            </div>
                        @else
                            <span style="color: var(--dash-muted); font-size: 11px;">—</span>
                        @endif
                    </td>

                    <!-- Meta Description -->
                    <td>
                        @if (filled($product->meta_description))
                            <div class="dash-text-snippet" title="{{ $product->meta_description }}">
                                {{ $product->meta_description }}
                            </div>
                        @else
                            <span style="color: var(--dash-muted); font-size: 11px;">—</span>
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
                        @else
                            <span style="color: var(--dash-muted); font-size: 11px;">—</span>
                        @endif
                    </td>

                    <!-- 6. Audit -->
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
                    <td colspan="18">
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

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {
    const popover = document.getElementById('dash-global-img-popover');
    const popoverImg = document.getElementById('dash-global-img-popover-src');
    if (!popover || !popoverImg) return;

    let activeTrigger = null;

    document.querySelectorAll('[data-popover-img]').forEach(el => {
        el.addEventListener('mouseenter', () => {
            const src = el.getAttribute('data-popover-img');
            if (!src) return;

            activeTrigger = el;
            popoverImg.src = src;
            popover.classList.add('is-visible');

            const rect = el.getBoundingClientRect();
            let top = rect.top + (rect.height / 2);
            let left = rect.right + 12;

            // Keep within viewport boundaries
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
            popover.classList.remove('is-visible');
        });
    });

    window.addEventListener('scroll', () => {
        if (activeTrigger) {
            popover.classList.remove('is-visible');
            activeTrigger = null;
        }
    }, { passive: true });
});
</script>
@endpush
@endsection
