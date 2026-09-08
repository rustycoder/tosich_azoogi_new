@extends('layouts.site')

@section('title')
    AZ-NF360 Neon Flex Series 360 — Azoogi LED Lighting Solutions
@endsection

@section('description')
    Azoogi designs and supplies premium LED lighting — strips, neon, garden lights, drivers and architectural fittings for
    projects that demand more.
@endsection

@section('chrome', 'full')

@section('topbarClass', 'solid')
@section('logo', 'logo_dark.png')

@push('styles')
    <link rel="stylesheet" href="{{ versioned_asset('assets/css/product_detail.css') }}">
@endpush

@section('content')
    <!-- ========== BREADCRUMBS ========== -->
    <div class="product-page-wrapper">
        <div class="wrap">
            <!-- ==================== BREADCRUMBS START ==================== -->
            <div class="breadcrumbs" id="breadcrumbs">
                <a href="/">Home</a>
                <span>/</span>
                <a href="/products">Products</a>
                <span>/</span>
                <a href="#">IP67 Neon</a>
                <span>/</span>
                <span style="color: var(--ink);">Neon Flex Series 360</span>
            </div>
            <!-- ==================== BREADCRUMBS END ==================== -->

            <!-- ==================== PRODUCT DETAIL GRID START ==================== -->
            <div class="product-grid">

                <!-- ==================== LEFT COLUMN: DETAILS START ==================== -->
                <div class="product-details-info">

                    <!-- PRODUCT TITLE GROUP START -->
                    <div class="product-title-group">
                        <h1 id="product-name">Neon Flex Series 360</h1>
                        <!-- <div class="product-code" id="product-code-label">MODEL: AZ-NF360-3000K-DOME</div> -->
                    </div>
                    <!-- PRODUCT TITLE GROUP END -->

                    <!-- PRODUCT METADATA BADGES START -->
                    <div class="product-meta-badges">
                        <span class="meta-badge" id="badge-ip">IP67 Waterproof</span>
                        <span class="meta-badge">360° Light Emission</span>
                        <a href="/warranty-returns" class="meta-badge">5-Year Warranty</a>
                        <span class="meta-badge">24V DC Input</span>
                        <span class="meta-badge">CRI 90+</span>
                    </div>
                    <!-- PRODUCT METADATA BADGES END -->

                    <!-- PRODUCT SHORT DESCRIPTION START -->
                    <div class="product-desc" id="product-desc">
                        Lorem ipsum dolor sit amet, consectetur adipiscing elit. Donec egestas condimentum lacinia.
                        Suspendisse nec
                        tincidunt ante, sed sodales tellus. Aliquam quis pulvinar mi. Nunc at feugiat sem. Pellentesque
                        molestie mi
                        eget faucibus interdum. Quisque ornare leo sed neque ullamcorper, in viverra tellus feugiat.
                        Phasellus augue
                        magna, imperdiet id libero eu, ultricies cursus leo.

                        Nulla neque dolor, sagittis in felis sit amet, congue iaculis magna. Fusce sodales enim sit amet dui
                        gravida
                        accumsan. Orci varius natoque penatibus et magnis dis parturient montes, nascetur ridiculus mus.
                        Morbi non
                        dolor et dui finibus commodo rutrum et tortor.
                    </div>
                    <!-- PRODUCT SHORT DESCRIPTION END -->

                    <!-- B2B ACTION BUTTONS START -->
                    <div class="gallery-actions"
                        style="display: flex; gap: 16px; justify-content: flex-start; margin-top: 24px;">

                        <a class="btn --outline" id="add-to-spec-btn"
                            style="display:flex; align-items:center; justify-content:center; gap:8px;">
                            <svg xmlns="http://www.w3.org/2000/svg" height="18px" viewBox="0 -960 960 960" width="18px"
                                fill="#1f1f1f">
                                <path d="M440-440H200v-80h240v-240h80v240h240v80H520v240h-80v-240Z" />
                            </svg> Add to Quote List</a>
                    </div>
                    <!-- B2B ACTION BUTTONS END -->

                </div>
                <!-- ==================== LEFT COLUMN: DETAILS END ==================== -->

                <!-- ==================== RIGHT COLUMN: GALLERY START ==================== -->
                <div class="product-gallery">

                    <!-- MAIN IMAGE COVER START -->
                    <div class="gallery-main-wrapper">
                        <img class="gallery-main-image" src="" id="gallery-main-img" alt="Product Image"
                            style="display:none;">
                    </div>
                    <!-- MAIN IMAGE COVER END -->

                    <!-- IMAGE VARIANT THUMBNAILS LIST START -->
                    <div class="gallery-thumbs" id="gallery-thumbs">
                        <!-- Dynamically populated thumbs from JSON -->
                    </div>
                    <!-- IMAGE VARIANT THUMBNAILS LIST END -->
                </div>
                <!-- ==================== RIGHT COLUMN: GALLERY END ==================== -->

            </div>
            <!-- ==================== PRODUCT DETAIL GRID END ==================== -->

            <!-- ==================== PRODUCT CONFIGURATION & TECHNICAL SPECIFICATIONS SECTION START ==================== -->
            <div class="options-section"
                style="margin-top: 25px; border-top: 1px solid var(--line); padding-top: 25px; margin-bottom: 25px;">

                <div class="options-grid-layout"
                    style="display: grid; grid-template-columns: 1.25fr 0.75fr; gap: 48px; align-items: start;">

                    <!-- LEFT COLUMN: CONFIGURATOR + SPECS + ICONS + DOWNLOADS START -->
                    <div class="options-specs-left-col" style="display: flex; flex-direction: column; gap: 10px;">

                        <!-- DYNAMIC CONFIGURATOR WORKSPACE START -->
                        <div class="config-workspace" id="dynamic-configurator">
                            <!-- Dynamically populated options -->
                        </div>
                        <!-- DYNAMIC CONFIGURATOR WORKSPACE END -->

                        <!-- PRODUCT B2B ICONS ROW START -->
                        <div class="product-icons-row-wrapper"
                            style="display: none; margin-top: 0; padding-top: 0; padding-bottom: 24px; border-bottom: 1px solid var(--line);">
                            <div class="product-icons-row"
                                style="display: flex; flex-wrap: wrap; gap: 20px; align-items: center;">
                            </div>
                        </div>
                        <!-- PRODUCT B2B ICONS ROW END -->

                        <!-- PRODUCT SPECIFICATION DOWNLOAD OPTION START -->
                        <div class="specification-download-section"
                            style="margin-top: 0; padding-top: 0; margin-bottom: 0;">
                            <h3 style="font-family: var(--font-serif); font-size: 24px; margin-bottom: 20px;">Downloadable
                                Resources
                            </h3>
                            <div class="download-options" style="display: flex; gap: 16px; flex-wrap: wrap;">
                                <a href="#" id="product-datasheet-link" class="btn --accent"
                                    style="display:none; align-items:center; justify-content:center; gap:8px;"
                                    target="_blank" rel="noopener noreferrer">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                        width="18" height="18">
                                        <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4" />
                                        <polyline points="7 10 12 15 17 10" />
                                        <line x1="12" y1="15" x2="12" y2="3" />
                                    </svg>
                                    Datasheet
                                </a>
                                <!-- <a href="#" class="btn --accent"
                                style="display:flex; align-items:center; justify-content:center; gap:8px;">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="18" height="18">
                                  <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4" />
                                  <polyline points="7 10 12 15 17 10" />
                                  <line x1="12" y1="15" x2="12" y2="3" />
                                </svg>
                                Installation Guide
                              </a>
                              <a href="#" class="btn --accent"
                                style="display:flex; align-items:center; justify-content:center; gap:8px;">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="18" height="18">
                                  <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4" />
                                  <polyline points="7 10 12 15 17 10" />
                                  <line x1="12" y1="15" x2="12" y2="3" />
                                </svg>
                                User manual
                              </a>
                              <a href="#" class="btn --accent"
                                style="display:flex; align-items:center; justify-content:center; gap:8px;">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="18" height="18">
                                  <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4" />
                                  <polyline points="7 10 12 15 17 10" />
                                  <line x1="12" y1="15" x2="12" y2="3" />
                                </svg>
                                IES files
                              </a> -->
                            </div>
                        </div>
                        <!-- PRODUCT SPECIFICATION DOWNLOAD OPTION END -->

                    </div>
                    <!-- LEFT COLUMN: CONFIGURATOR + SPECS + ICONS + DOWNLOADS END -->

                    <!-- RIGHT COLUMN: SELECTED SUMMARY CARD START -->
                    <div class="config-summary-card"
                        style="background: var(--card-bg); border: 1px solid var(--border-light); border-radius: 8px; padding: 24px; display: flex; flex-direction: column; gap: 10px; position: sticky; top: 100px;">
                        <h4
                            style="font-family: var(--font-serif); font-size: 20px; margin: 0; color: var(--ink); border-bottom: 1px solid var(--line); padding-bottom: 12px;">
                            Selected Configuration</h4>

                        <div id="selected-summary-list"
                            style="display: flex; flex-direction: column; gap: 10px; font-size: 12px; color: var(--muted);">
                            <!-- Dynamically populated list from Javascript selection updates -->
                        </div>

                        <!-- ACTION BUTTONS -->
                        <div class="gallery-actions"
                            style="display: flex; flex-direction: column; gap: 12px; margin-top: 10px;">
                            <button type="button" id="download-custom-datasheet" class="btn --accent"
                                disabled aria-disabled="true" title="Select configuration options first"
                                style="display:flex; align-items:center; justify-content:center; gap:8px; width: 100%;">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                    width="18" height="18">
                                    <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4" />
                                    <polyline points="7 10 12 15 17 10" />
                                    <line x1="12" y1="15" x2="12" y2="3" />
                                </svg>
                                Download Custom Datasheet
                            </button>
                            <a href="#quote-section-anchor" class="btn --accent"
                                style="display:flex; justify-content:center; gap:8px; width: 100%;">
                                <svg viewBox="0 -960 960 960" fill="#111111" stroke="currentColor" stroke-width="2"
                                    width="18" height="18">
                                    <path
                                        d="M160-160q-33 0-56.5-23.5T80-240v-480q0-33 23.5-56.5T160-800h640q33 0 56.5 23.5T880-720v480q0 33-23.5 56.5T800-160H160Zm320-280L160-640v400h640v-400L480-440Zm0-80 320-200H160l320 200ZM160-640v-80 480-400Z" />
                                </svg>
                                Enquiry
                            </a>
                        </div>
                    </div>
                    <!-- RIGHT COLUMN: SELECTED SUMMARY CARD END -->

                </div>
            </div>
            <!-- ==================== PRODUCT CONFIGURATION & TECHNICAL SPECIFICATIONS SECTION END ==================== -->

        </div>
    </div>

    <!-- ==================== COMPATIBLE ACCESSORIES SECTION START ==================== -->
    <section class="accessories-section"
        style="margin-top: 25px; border-top: 1px solid var(--line); padding-top: 20px; padding-bottom: 40px;">
        <div class="wrap">
            <h3 style="font-family: var(--font-serif); font-size: 28px; margin-bottom: 24px;">Recommended Products</h3>
            <div class="prod-grid" id="productGrid">
                <div class="prod-card-img">
                    <img class="prod-swatch" src="/assets/img/drivers.webp" alt="DALI-2 Dimmable Driver" loading="lazy"
                        style="object-fit: cover;">
                </div>
                <div class="prod-card-title">
                    <div class="prod-card-title-text"><span class="cat-label">DALI / DALI-2</span>DALI-2 Dimmable Driver
                    </div>
                    <button class="add-quote-btn" aria-label="Add to quote"
                        onclick="event.stopPropagation(); this.classList.add('added'); this.innerHTML='✓';">+</button>
                </div>
            </div>
        </div>
    </section>
    <!-- ==================== COMPATIBLE ACCESSORIES SECTION END ==================== -->

    <!-- ========== RELATED PROJECTS GALLERY ========== -->
    <!-- <section class="related-projects-section">
              <div class="wrap">
                <h3>Featured Project Applications</h3>
                <div class="projects-small-grid">
                  <div class="proj-small-card">
                    <img src="/assets/img/img-1.jpg" alt="Zushi Restaurant">
                    <div class="proj-small-overlay">
                      <small>Hospitality · Sydney</small>
                      <h4>Zushi Restaurant Custom Facade Outlines</h4>
                    </div>
                  </div>
                  <div class="proj-small-card">
                    <img src="/assets/img/eve.jpg" alt="The Eve Hotel">
                    <div class="proj-small-overlay">
                      <small>Commercial · Redfern</small>
                      <h4>The Eve Hotel Curved Lounge Cove Illumination</h4>
                    </div>
                  </div>
                </div>
              </div>
            </section> -->

    <!-- ========== B2B INQUIRY FORM SECTION ========== -->
    <section class="quote-section" id="quote-section-anchor">
        <div class="wrap">
            <div class="quote-grid">

                <div class="quote-intro">
                    <h2>Let's light up your project.</h2>
                    <p style="margin-bottom: 20px;">
                        Request a B2B project quote or coordinate a specsheet request. Fill out your details, and an Azoogi
                        commercial specialist will follow up with pricing, lead times, and trade discount rates.
                    </p>
                    <div style="font-size:13px; color:var(--muted); line-height: 2;">
                        <div><strong>Sales Support:</strong> 1300 641 261</div>
                        <div><strong>Email Response:</strong> sales@azoogi.com</div>
                        <div><strong>B2B Turnaround:</strong> Within 4 business hours</div>
                    </div>
                </div>

                <div class="quote-form-card">
                    <form id="quote-form" action="{{ route('product-enquiry.submit') }}" method="post">
                        @csrf
                        <div class="form-row-2">
                            <div class="form-group">
                                <label class="form-label" for="quote-name">Your Name *</label>
                                <input class="form-input" id="quote-name" name="quote-name" type="text" required
                                    maxlength="191" placeholder="e.g. John Doe" value="{{ old('quote-name') }}">
                            </div>
                            <div class="form-group">
                                <label class="form-label" for="quote-email">Email Address *</label>
                                <input class="form-input" id="quote-email" name="quote-email" type="email" required
                                    maxlength="191" placeholder="e.g. name@company.com.au" value="{{ old('quote-email') }}">
                            </div>
                        </div>

                        <div class="form-row-2">
                            <div class="form-group">
                                <label class="form-label" for="quote-company">Company / Trade Name *</label>
                                <input class="form-input" id="quote-company" name="quote-company" type="text" required
                                    maxlength="191" placeholder="e.g. Summit Electrical" value="{{ old('quote-company') }}">
                            </div>
                            <div class="form-group">
                                <label class="form-label" for="quote-project">Project Name *</label>
                                <input class="form-input" id="quote-project" name="quote-project" type="text" required
                                    maxlength="191" placeholder="e.g. Sydney Office fitout" value="{{ old('quote-project') }}">
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="form-label" for="quote-spec">Configured Specification *</label>
                            <textarea class="form-textarea" id="quote-spec" name="quote-spec" required readonly wrap="off" rows="10"
                                style="opacity: 0.8; background: var(--rgba-hover); cursor: not-allowed;">{{ old('quote-spec') }}</textarea>
                        </div>

                        <div class="form-group">
                            <label class="form-label" for="quote-message">Additional Project Details</label>
                            <textarea class="form-textarea" id="quote-message" name="quote-message"
                                placeholder="Provide extra requirements, dimming requests, or custom connector feeds...">{{ old('quote-message') }}</textarea>
                        </div>

                        @if ($errors->any())
                            <p class="form-status is-error">{{ $errors->first() }}</p>
                        @endif

                        <button class="btn" type="submit">Submit Spec Inquiry</button>
                    </form>
                </div>

            </div>
        </div>
    </section>

    <dialog class="datasheet-dialog" id="datasheet-export-dialog" aria-labelledby="datasheet-export-title">
        <div class="datasheet-dialog-panel">
            <header class="datasheet-dialog-head">
                <h2 id="datasheet-export-title">Download custom datasheet</h2>
                <button type="button" class="datasheet-dialog-close" id="datasheet-export-close" aria-label="Close">&times;</button>
            </header>
            <form id="datasheet-export-form" class="datasheet-dialog-form" method="post" action="{{ route('products.datasheet.store') }}">
                @csrf
                <p class="datasheet-dialog-lead">Enter the project and client name. Both are saved with the export.</p>
                <div class="form-group">
                    <label class="form-label" for="datasheet-project-name">Project name *</label>
                    <input class="form-input" id="datasheet-project-name" name="project_name" type="text" required maxlength="191" placeholder="e.g. White City" autocomplete="organization">
                    <p class="datasheet-field-error" data-error-for="project_name" hidden></p>
                </div>
                <div class="form-group">
                    <label class="form-label" for="datasheet-person-name">Client name *</label>
                    <input class="form-input" id="datasheet-person-name" name="person_name" type="text" required maxlength="191" placeholder="e.g. Alex Chen" autocomplete="name">
                    <p class="datasheet-field-error" data-error-for="person_name" hidden></p>
                </div>
                <p class="datasheet-field-error" data-error-for="form" hidden></p>
                <div class="datasheet-dialog-actions">
                    <button type="button" class="btn" id="datasheet-export-cancel">Cancel</button>
                    <button type="submit" class="btn primary" id="datasheet-export-submit">Generate datasheet</button>
                </div>
            </form>
        </div>
    </dialog>
@endsection

@push('scripts')
    @verbatim
        <script>
            /* ===== Header solid ===== */
            document.getElementById('topbar')?.classList.add('solid');

            /* ===== Mega Menu Tabs ===== */
            (function() {
                const tabs = document.querySelectorAll('.mega-tab');
                const panels = document.querySelectorAll('.mega-panel');

                tabs.forEach(tab => {
                    tab.addEventListener('mouseenter', () => {
                        if (window.innerWidth > 960) switchTab(tab);
                    });
                    tab.addEventListener('click', (e) => {
                        e.preventDefault();
                        switchTab(tab);
                    });
                });

                function switchTab(activeTab) {
                    tabs.forEach(t => t.classList.remove('active'));
                    panels.forEach(p => p.classList.remove('active'));

                    activeTab.classList.add('active');
                    const targetId = activeTab.getAttribute('data-target');
                    const targetPanel = document.getElementById(targetId);
                    if (targetPanel) targetPanel.classList.add('active');
                }

            })
            ();

            document.addEventListener("DOMContentLoaded", () => {
                document.querySelectorAll('.logo img').forEach((img) => {
                    img.src = '/assets/logo_dark.png';
                });
                initDynamicProductPage();
            });

            function initDynamicProductPage() {
                const urlParams = new URLSearchParams(window.location.search);
                const productId = urlParams.get('id');
                const productCode = urlParams.get('product') || urlParams.get('name') || urlParams.get('variant') || urlParams
                    .get('file');

                let product = null;

                // Load product details from AZOOGI_PRODUCTS (products array and tree variants)
                if (typeof AZOOGI_PRODUCTS !== 'undefined') {
                    const allProducts = [];
                    const seenKeys = new Set();

                    const addProduct = (p) => {
                        if (!p) return;
                        if (p.status && String(p.status).toLowerCase().trim() !== 'publish') return;
                        const key = p.id || p.sku || p.product_name || p.name;
                        if (key && !seenKeys.has(key)) {
                            seenKeys.add(key);
                            allProducts.push(p);
                        }
                    };

                    const productsById = {};
                    if (AZOOGI_PRODUCTS.products) {
                        const list = Array.isArray(AZOOGI_PRODUCTS.products) ?
                            AZOOGI_PRODUCTS.products :
                            Object.values(AZOOGI_PRODUCTS.products);
                        list.forEach(p => {
                            if (p && p.id) productsById[p.id] = p;
                            addProduct(p);
                        });
                    }

                    function collectFromTree(treeNodes) {
                        if (!treeNodes || !Array.isArray(treeNodes)) return;
                        treeNodes.forEach(node => {
                            if (node.variants) {
                                for (const vName in node.variants) {
                                    if (node.variants.hasOwnProperty(vName)) {
                                        let vData = node.variants[vName];
                                        if (typeof vData === 'string' && productsById[vData]) {
                                            vData = productsById[vData];
                                        }
                                        if (vData && typeof vData === 'object') {
                                            vData.variantName = vName;
                                            vData.modelName = node.name || vName;
                                            if (!vData.sku_mappings && node.sku_mappings) {
                                                vData.sku_mappings = node.sku_mappings;
                                            }
                                            if (!vData.options && node.options) {
                                                vData.options = node.options;
                                            }
                                            addProduct(vData);
                                        }
                                    }
                                }
                            }
                            if (node.children) collectFromTree(node.children);
                        });
                    }
                    if (AZOOGI_PRODUCTS.tree) collectFromTree(AZOOGI_PRODUCTS.tree);

                    if (productId) {
                        const idLower = decodeURIComponent(productId).toLowerCase().trim();
                        product = allProducts.find(p => p.id && p.id.toLowerCase() === idLower);
                    }
                    if (!product && productCode) {
                        const codeLower = decodeURIComponent(productCode).toLowerCase().trim();
                        product = allProducts.find(p =>
                            (p.id && p.id.toLowerCase() === codeLower) ||
                            (p.sku && String(p.sku).toLowerCase().trim() === codeLower) ||
                            (p.product_name && p.product_name.toLowerCase().trim() === codeLower) ||
                            (p.variantName && p.variantName.toLowerCase().trim() === codeLower) ||
                            (p.name && p.name.toLowerCase().trim() === codeLower) ||
                            (p.file_path && p.file_path.toLowerCase().trim() === codeLower) ||
                            (p.product_name && p.product_name.toLowerCase().includes(codeLower))
                        );
                    }
                    if (!product && allProducts.length > 0) {
                        product = allProducts[0];
                    }
                }

                if (!product) return;

                setupPageWithData(product);
            }

            function setupPageWithData(product) {
                // Normalize options so values are objects { id, name }
                const rawOptions = product.options || {};
                const normalizedOptions = {};
                for (const optKey in rawOptions) {
                    if (!rawOptions.hasOwnProperty(optKey)) continue;
                    const rawVals = rawOptions[optKey];
                    if (Array.isArray(rawVals)) {
                        normalizedOptions[optKey] = rawVals.map((item, idx) => {
                            if (typeof item === 'object' && item !== null) {
                                return {
                                    id: String(item.id || idx + 1),
                                    name: String(item.name || item.id || idx + 1)
                                };
                            }
                            return {
                                id: String(item),
                                name: String(item)
                            };
                        });
                    }
                }
                product.options = normalizedOptions;

                // Configuration state: starts unselected until the user picks options
                let selectedOptions = {};

                let selectedLength = 5.0;
                let datasheetSku = '';

                // DOM Elements
                const productNameEl = document.getElementById('product-name');
                // const productCodeEl = document.getElementById('product-code-label');
                const breadcrumbsEl = document.getElementById('breadcrumbs');
                const galleryMainImg = document.getElementById('gallery-main-img');
                const galleryThumbs = document.getElementById('gallery-thumbs');
                const configurator = document.getElementById('dynamic-configurator');
                const descEl = document.getElementById('product-desc');

                const calcPower = document.getElementById('calc-power');
                const calcLumens = document.getElementById('calc-lumens');
                const calcDriver = document.getElementById('calc-driver');
                const capacityPct = document.getElementById('calc-capacity-pct');
                const driverBarFill = document.getElementById('driver-bar-fill');
                const driverWarning = document.getElementById('driver-warning-msg');
                const calcPanel = document.querySelector('.calc-panel');

                // Helper to parse SKU mappings matrix
                function getParsedSkuMatrix(prod) {
                    const mappings = prod.sku_mappings;
                    if (!mappings || typeof mappings !== 'object') return [];

                    const rows = [];
                    for (const [rawKey, sku] of Object.entries(mappings)) {
                        if (!sku && sku !== 0) continue;
                        const rawTokens = String(rawKey)
                            .split(/[|,;]/)
                            .map(s => s.trim().toLowerCase())
                            .filter(Boolean);
                        if (rawTokens.length === 0) continue;
                        rows.push({
                            sku: String(sku),
                            rawKey: String(rawKey),
                            tokens: rawTokens,
                        });
                    }
                    return rows;
                }

                // Check if an option value matches any token in a SKU row
                function optionMatchesRowToken(valObj, rowTokens) {
                    if (!valObj) return false;
                    const id = String(valObj.id || '').trim().toLowerCase();
                    const name = String(valObj.name || '').trim().toLowerCase();

                    return rowTokens.some(token => {
                        const t = String(token || '').trim().toLowerCase();
                        if (!t) return false;

                        // Direct exact match
                        if (t === id || t === name) return true;

                        // Substring match (e.g. token "3000k" in name "3000k warm white" or vice versa)
                        if (id && (t.includes(id) || id.includes(t))) return true;
                        if (name && (t.includes(name) || name.includes(t))) return true;

                        return false;
                    });
                }

                // Check if an option group participates in the SKU mappings matrix
                function groupParticipatesInMatrix(groupKey, allOptions, skuMatrix) {
                    const groupVals = allOptions[groupKey] || [];
                    return groupVals.some(valObj => {
                        return skuMatrix.some(row => optionMatchesRowToken(valObj, row.tokens));
                    });
                }

                // Check if a candidate option value is available given other current selections
                function isCombinationAvailable(testingKey, testingValObj, existingSelections, skuMatrix, allOptions) {
                    const candidateSelections = { ...existingSelections, [testingKey]: testingValObj.id };
                    const candidateEntries = Object.entries(candidateSelections).filter(([k, v]) => 
                        v !== undefined && v !== null && String(v).trim() !== ''
                    );

                    if (candidateEntries.length === 0) return true;

                    // Filter candidate entries to only those option groups that participate in the SKU matrix
                    const matrixEntries = candidateEntries.filter(([k]) => groupParticipatesInMatrix(k, allOptions, skuMatrix));
                    if (matrixEntries.length === 0) return true;

                    // Check if at least one row in skuMatrix satisfies all candidate matrix selections
                    return skuMatrix.some(row => {
                        return matrixEntries.every(([k, optId]) => {
                            const groupVals = allOptions[k] || [];
                            const valObj = (k === testingKey)
                                ? testingValObj
                                : groupVals.find(v => String(v.id) === String(optId));
                            if (!valObj) return true;
                            return optionMatchesRowToken(valObj, row.tokens);
                        });
                    });
                }

                function getMappedSku(product, selectedOpts) {
                    const features = product.product_features || {};
                    const defaultCode = product.product_code || features["Product Code"] || features["Product code"] || product
                        .sku || product.id || "";
                    const mappings = product.sku_mappings;
                    const options = product.options || {};
                    const optionKeys = Object.keys(options).filter(k => Array.isArray(options[k]) && options[k].length > 0);

                    // If no options exist for this product, defaultCode is the matching code
                    if (optionKeys.length === 0) {
                        return defaultCode;
                    }

                    const selectedEntries = Object.entries(selectedOpts || {}).filter(([k, v]) => v !== undefined && v !==
                        null && String(v).trim() !== '');
                    if (selectedEntries.length === 0) {
                        return "";
                    }

                    const allOptionsSelected = optionKeys.every(k => selectedOpts && selectedOpts[k] !== undefined &&
                        selectedOpts[k] !== null && String(selectedOpts[k]).trim() !== '');

                    if (!mappings || typeof mappings !== 'object' || Object.keys(mappings).length === 0) {
                        return allOptionsSelected ? defaultCode : "";
                    }

                    const skuMatrix = getParsedSkuMatrix(product);
                    if (skuMatrix.length === 0) {
                        return allOptionsSelected ? defaultCode : "";
                    }

                    // Check each SKU row in the matrix:
                    // 1. All user selections must match this row's tokens (for participating groups)
                    // 2. All tokens in this row must be satisfied by the user's selections
                    for (const row of skuMatrix) {
                        const allSelectionsMatchRow = selectedEntries.every(([k, optId]) => {
                            if (!groupParticipatesInMatrix(k, options, skuMatrix)) return true;
                            const groupVals = options[k] || [];
                            const valObj = groupVals.find(v => String(v.id) === String(optId));
                            if (!valObj) return false;
                            return optionMatchesRowToken(valObj, row.tokens);
                        });

                        if (!allSelectionsMatchRow) continue;

                        const allRowTokensSatisfied = row.tokens.every(token => {
                            return selectedEntries.some(([k, optId]) => {
                                const groupVals = options[k] || [];
                                const valObj = groupVals.find(v => String(v.id) === String(optId));
                                return valObj && optionMatchesRowToken(valObj, [token]);
                            });
                        });

                        if (allRowTokensSatisfied) {
                            return row.sku;
                        }
                    }

                    if (allOptionsSelected) {
                        return defaultCode;
                    }

                    return "";
                }

                // Extract Product Name, SKU, Short & Long Descriptions
                const pName = product.product_name || product.name || "Azoogi Lighting Product";
                const features = product.product_features || {};
                const sku = getMappedSku(product, selectedOptions);
                const pShortDesc = product.product_short_description || product.short_description || features[
                    "Product short description"] || "";
                const pLongDesc = product.product_description || product.description || features["Product long description"] ||
                    pShortDesc;

                // Update Basic Info & Descriptions
                if (productNameEl) productNameEl.textContent = pName;

                const manufacturerDatasheet = document.getElementById('product-datasheet-link');
                if (manufacturerDatasheet) {
                    const sheets = Array.isArray(product.datasheet)
                        ? product.datasheet
                        : (product.datasheet ? [product.datasheet] : []);
                    const sheetUrl = sheets.find((item) => typeof item === 'string' && /^https?:\/\//i.test(item));
                    if (sheetUrl) {
                        manufacturerDatasheet.href = sheetUrl;
                        manufacturerDatasheet.style.display = 'flex';
                    } else {
                        manufacturerDatasheet.removeAttribute('href');
                        manufacturerDatasheet.style.display = 'none';
                    }
                }
                // if (productCodeEl) productCodeEl.textContent = sku ? `PRODUCT CODE: ${sku}` : `PRODUCT CODE: ${pName}`;
                if (descEl) descEl.innerHTML = pLongDesc || pShortDesc ||
                    "Experience discreet luxury and a sophisticated, seamless glow that beautifully enhances your elegant spaces.";

                // Update Document Title
                document.title = `${pName} — Azoogi LED Lighting Solutions`;

                // Render Meta Keywords Badges
                const metaBadgesEl = document.querySelector('.product-meta-badges');
                if (metaBadgesEl) {
                    const rawMeta = product.meta_keywords || features["Meta Keywords"] || features["Meta keywords"] || features[
                        "meta_keywords"] || "";
                    let metaItems = [];
                    if (Array.isArray(rawMeta)) {
                        rawMeta.forEach(item => {
                            if (typeof item === 'string') {
                                item.split(',').forEach(k => {
                                    if (k.trim()) metaItems.push(k.trim());
                                });
                            }
                        });
                    } else if (typeof rawMeta === 'string' && rawMeta.trim()) {
                        rawMeta.split(',').forEach(k => {
                            if (k.trim()) metaItems.push(k.trim());
                        });
                    }

                    function toTitleCase(str) {
                        if (!str) return '';
                        return str.split(' ').map(w => w ? (w.charAt(0).toUpperCase() + w.slice(1)) : '').join(' ');
                    }

                    metaItems = Array.from(new Set(metaItems)).map(toTitleCase);

                    if (metaItems.length > 0) {
                        metaBadgesEl.style.display = 'flex';
                        metaBadgesEl.innerHTML = metaItems.map(item => `<span class="meta-badge">${item}</span>`).join('');
                    } else {
                        metaBadgesEl.style.display = 'none';
                        metaBadgesEl.innerHTML = '';
                    }
                }

                // Resolve Category Path values dynamically
                let categorySegments = [];
                const urlParams = new URLSearchParams(window.location.search);
                const queryCat = urlParams.get('category');

                if (queryCat && product.category_paths && Array.isArray(product.category_paths)) {
                    const matchingPath = product.category_paths.find(cp => cp && cp.some(s => s.trim().toLowerCase() ===
                        queryCat.trim().toLowerCase()));
                    if (matchingPath) {
                        categorySegments = matchingPath.filter(s => s && s.trim() && s !== 'undefined');
                    }
                }

                if (categorySegments.length === 0) {
                    if (product.category_path && Array.isArray(product.category_path) && product.category_path.length > 0) {
                        categorySegments = product.category_path.filter(s => s && s.trim() && s !== 'undefined');
                    } else if (product.category && product.category.trim()) {
                        categorySegments = [product.category.trim()];
                    }
                }

                // Deduplicate segments while preserving order
                categorySegments = Array.from(new Set(categorySegments));

                let breadcrumbHTML = `
                                                                                          <a href="/">Home</a>
                                                                                          <span>/</span>
                                                                                          <a href="/products">Products</a>
                                                                                        `;

                categorySegments.forEach(seg => {
                    breadcrumbHTML += `
                                                                                              <span>/</span>
                                                                                              <a href="/products?category=${encodeURIComponent(seg)}">${seg}</a>
                                                                                            `;
                });

                breadcrumbHTML += `
                                                                                          <span>/</span>
                                                                                          <span style="color: var(--ink);">${pName}</span>
                                                                                        `;

                breadcrumbsEl.innerHTML = breadcrumbHTML;

                // Helper to get image path
                function resolveImg(url) {
                    if (!url || typeof url !== 'string' || !url.trim()) return '/assets/bg_default.png';
                    var clean = url.trim();
                    if (clean.startsWith('http://') || clean.startsWith('https://')) return clean;
                    if (!clean.startsWith('/')) return '/' + clean;
                    return clean;
                }

                function extractProductCode(source) {
                    if (!source) return '';
                    var raw = source.product_code || source.productCode || '';
                    if (!raw && source.product_features) {
                        var feats = source.product_features;
                        raw = feats['Product Code'] || feats['Product code'] || '';
                    }
                    if (!raw && source.sku) {
                        raw = source.sku;
                    }
                    if (Array.isArray(raw)) {
                        raw = raw.map(function(v) {
                            return (v && typeof v === 'object' && v.value !== undefined) ? v.value : v;
                        }).filter(Boolean).join(', ');
                    } else if (raw && typeof raw === 'object' && raw.value !== undefined) {
                        raw = raw.value;
                    }
                    return String(raw || '').trim();
                }

                function primaryProductCode(sku) {
                    if (!sku) return '';
                    return String(sku).split(',')[0].trim();
                }

                function productCodeHtml(sku) {
                    var code = primaryProductCode(sku);
                    if (!code) return '';
                    return ' <span class="prod-card-code">' + code + '</span>';
                }

                // Render Recommended Compatible Accessories from the same category
                function renderRecommendedAccessories() {
                    const accessoriesGrid = document.getElementById('productGrid');
                    if (!accessoriesGrid || typeof AZOOGI_PRODUCTS === 'undefined' || !AZOOGI_PRODUCTS.products) return;

                    const currentCatPath = product.category_path || [product.category || ''];
                    const topCat = currentCatPath[0] || '';
                    const subCat = currentCatPath.length > 1 ? currentCatPath[currentCatPath.length - 1] : topCat;

                    const sameSubCatProducts = [];
                    const sameTopCatProducts = [];
                    const otherProducts = [];

                    const rawList = Array.isArray(AZOOGI_PRODUCTS.products) ?
                        AZOOGI_PRODUCTS.products :
                        Object.values(AZOOGI_PRODUCTS.products);

                    rawList.forEach(pRow => {
                        if (pRow.status && String(pRow.status).toLowerCase().trim() !== 'publish') return;
                        const pId = pRow.id || '';
                        const pName = pRow.product_name || pRow.name || '';
                        if (pId === product.id || pName === product.product_name || pName === product.name) return;

                        const variants = pRow.variants || {};
                        const vKeys = Object.keys(variants);
                        const firstVar = vKeys.length > 0 ? variants[vKeys[0]] : pRow;

                        const catPath = pRow.category_path || [pRow.category || ''];
                        const pTopCat = catPath[0] || '';
                        const pSubCat = catPath.length > 1 ? catPath[catPath.length - 1] : pTopCat;

                        const images = firstVar.product_images || pRow.product_images || [];
                        const rawImg = images.length > 0 ? images[0] : '/assets/bg_default.png';
                        const localImg = resolveImg(rawImg, firstVar.file_path);
                        const sku = extractProductCode(firstVar) || extractProductCode(pRow) || '';

                        const item = {
                            id: pId,
                            name: pName,
                            sub: pSubCat,
                            cat: pTopCat,
                            filePath: firstVar.file_path || '',
                            img: localImg,
                            sku: sku,
                            specs: firstVar.product_features || pRow.product_features || {}
                        };

                        if (subCat && (pSubCat === subCat || catPath.includes(subCat))) {
                            sameSubCatProducts.push(item);
                        } else if (topCat && (pTopCat === topCat || catPath.includes(topCat))) {
                            sameTopCatProducts.push(item);
                        } else {
                            otherProducts.push(item);
                        }
                    });

                    const recommended = [...sameSubCatProducts, ...sameTopCatProducts, ...otherProducts].slice(0, 4);
                    const accessoriesSection = document.querySelector('.accessories-section');

                    if (recommended.length === 0) {
                        if (accessoriesSection) accessoriesSection.style.display = 'none';
                        accessoriesGrid.innerHTML = '';
                        return;
                    }

                    if (accessoriesSection) accessoriesSection.style.display = 'block';

                    accessoriesGrid.innerHTML = recommended.map(p => {
                        const detailUrl = p.id ? ('/product-detail?id=' + encodeURIComponent(p.id)) : (p.filePath ? (
                            '/product-detail?file=' + encodeURIComponent(p.filePath)) : (
                            '/product-detail?product=' + encodeURIComponent(p.name)));
                        const codeHtml = productCodeHtml(p.sku);
                        const isFallback = !p.img || p.img === '/assets/bg_default.png' || p.img ===
                            '/assets/logo_dark.png';
                        const fallbackStyle = isFallback ? ' filter: grayscale(100%); opacity: 0.7;' : '';
                        return `
                                                                                                <div class="prod-card" onclick="window.location.href='${detailUrl}'">
                                                                                                  <div class="prod-card-img">
                                                                                                    <img class="prod-swatch${isFallback ? ' is-fallback' : ''}" src="${p.img || '/assets/bg_default.png'}" alt="${p.name || 'Recommended Product'}" loading="lazy" onerror="this.onerror=null; this.src='/assets/bg_default.png'; this.classList.add('is-fallback');" style="${fallbackStyle}">
                                                                                                  </div>
                                                                                                  <div class="prod-card-title">
                                                                                                    <div class="prod-card-title-text"><span class="cat-label">${p.sub}</span>${p.name}${codeHtml}</div>
                                                                                                    <button class="add-quote-btn" aria-label="Add to quote" data-quote-id="${p.id || p.sku || p.name || ''}" data-quote-name="${p.name || ''}" data-quote-sku="${primaryProductCode(p.sku)}" data-quote-image="${p.img || ''}" data-quote-url="${detailUrl}" onclick="event.stopPropagation();">+</button>
                                                                                                  </div>
                                                                                                </div>
                                                                                              `;
                    }).join('');
                }

                // Render Gallery (including dimension diagrams)
                function renderGallery() {
                    galleryThumbs.innerHTML = '';
                    const features = product.product_features || {};

                    let galleryItems = []; // Array of { url: string, isDimension: boolean }

                    function addImagesFrom(source, isDim) {
                        if (!source) return;
                        if (Array.isArray(source)) {
                            source.forEach(item => {
                                if (typeof item === 'string' && item.trim()) {
                                    galleryItems.push({
                                        url: item.trim(),
                                        isDimension: !!isDim
                                    });
                                } else if (typeof item === 'object' && item !== null && item.url) {
                                    galleryItems.push({
                                        url: item.url,
                                        isDimension: !!isDim
                                    });
                                }
                            });
                        } else if (typeof source === 'string' && source.trim()) {
                            galleryItems.push({
                                url: source.trim(),
                                isDimension: !!isDim
                            });
                        }
                    }

                    // 1. Add standard product photos
                    addImagesFrom(product.product_images, false);
                    addImagesFrom(features["Product image"], false);
                    addImagesFrom(features["Product gallery"], false);
                    addImagesFrom(features["Product Image"], false);
                    addImagesFrom(features["Product Gallery"], false);

                    // 2. Add product dimension diagrams to gallery
                    addImagesFrom(product.product_dimension, true);
                    addImagesFrom(features["Product Dimension"], true);
                    addImagesFrom(features["Product dimension"], true);

                    // Remove duplicate URLs
                    const seenUrls = new Set();
                    galleryItems = galleryItems.filter(item => {
                        if (seenUrls.has(item.url)) return false;
                        seenUrls.add(item.url);
                        return true;
                    });

                    if (!galleryItems || galleryItems.length === 0) {
                        galleryMainImg.src = '/assets/bg_default.png';
                        galleryMainImg.style.filter = 'grayscale(100%)';
                        galleryMainImg.style.display = 'block';
                        return;
                    }

                    function setMainImage(item) {
                        const localSrc = resolveImg(item.url);
                        galleryMainImg.src = localSrc;
                        galleryMainImg.style.display = 'block';
                        galleryMainImg.style.opacity = '1';
                        galleryMainImg.style.objectFit = 'contain';
                        galleryMainImg.style.background = '';
                        galleryMainImg.style.padding = '0';
                        galleryMainImg.style.filter = (!item.isDimension && (localSrc === '/assets/bg_default.png' ||
                            localSrc === '/assets/logo_dark.png')) ? 'grayscale(100%)' : 'none';
                    }

                    setMainImage(galleryItems[0]);

                    galleryMainImg.onerror = () => {
                        galleryMainImg.src = '/assets/bg_default.png';
                        galleryMainImg.style.filter = 'grayscale(100%)';
                    };

                    galleryItems.forEach((item, idx) => {
                        const localSrc = resolveImg(item.url);
                        const thumb = document.createElement('div');
                        thumb.className =
                            `thumb-card${idx === 0 ? ' active' : ''}${item.isDimension ? ' is-dimension' : ''}`;

                        const thumbImg = document.createElement('img');
                        thumbImg.src = localSrc;
                        thumbImg.alt = item.isDimension ? 'Product Dimension Diagram' : `Product Thumbnail ${idx + 1}`;
                        if (!item.isDimension && (localSrc === '/assets/bg_default.png' || localSrc ===
                                '/assets/logo_dark.png')) {
                            thumbImg.style.filter = 'grayscale(100%)';
                        }
                        thumbImg.onerror = () => {
                            thumbImg.src = '/assets/bg_default.png';
                            thumbImg.style.filter = 'grayscale(100%)';
                        };
                        thumb.appendChild(thumbImg);

                        thumb.addEventListener('click', () => {
                            galleryThumbs.querySelectorAll('.thumb-card').forEach(t => t.classList.remove(
                                'active'));
                            thumb.classList.add('active');
                            setMainImage(item);
                        });

                        galleryThumbs.appendChild(thumb);
                    });
                }

                // Helper to sort configurator option groups using Airtable Product Attributes order
                function getOrderedOptionKeys(options) {
                    const rawKeys = Object.keys(options).filter(k => Array.isArray(options[k]) && options[k].length > 0);
                    if (typeof AZOOGI_PRODUCTS !== 'undefined' && Array.isArray(AZOOGI_PRODUCTS.attribute_groups_order || AZOOGI_PRODUCTS.filterable_attributes)) {
                        const masterOrder = AZOOGI_PRODUCTS.attribute_groups_order || AZOOGI_PRODUCTS.filterable_attributes;
                        const orderMap = {};
                        masterOrder.forEach((name, idx) => {
                            orderMap[String(name).trim().toLowerCase()] = idx;
                        });
                        return rawKeys.slice().sort((a, b) => {
                            const aIdx = orderMap.hasOwnProperty(a.trim().toLowerCase()) ? orderMap[a.trim().toLowerCase()] : 999999;
                            const bIdx = orderMap.hasOwnProperty(b.trim().toLowerCase()) ? orderMap[b.trim().toLowerCase()] : 999999;
                            if (aIdx !== bIdx) return aIdx - bIdx;
                            return a.localeCompare(b, undefined, { numeric: true, sensitivity: 'base' });
                        });
                    }
                    return rawKeys;
                }

                // Helper to sort configurator option values (buttons) using Airtable Product Attributes term order
                function sortConfigOptionValues(groupKey, valList) {
                    if (!Array.isArray(valList)) return [];
                    let orderMap = null;
                    if (typeof AZOOGI_PRODUCTS !== 'undefined' && AZOOGI_PRODUCTS.attribute_values_order) {
                        const targetLower = String(groupKey).trim().toLowerCase();
                        for (const k in AZOOGI_PRODUCTS.attribute_values_order) {
                            if (k.trim().toLowerCase() === targetLower) {
                                const list = AZOOGI_PRODUCTS.attribute_values_order[k];
                                if (Array.isArray(list)) {
                                    orderMap = {};
                                    list.forEach((val, idx) => {
                                        orderMap[String(val).trim().toLowerCase()] = idx;
                                    });
                                }
                                break;
                            }
                        }
                    }

                    return valList.slice().sort((a, b) => {
                        const aName = String(a.name || '').trim();
                        const bName = String(b.name || '').trim();
                        const aLower = aName.toLowerCase();
                        const bLower = bName.toLowerCase();

                        if (orderMap) {
                            const aIdx = orderMap.hasOwnProperty(aLower) ? orderMap[aLower] : 999999;
                            const bIdx = orderMap.hasOwnProperty(bLower) ? orderMap[bLower] : 999999;
                            if (aIdx !== bIdx) return aIdx - bIdx;
                        }

                        return aName.localeCompare(bName, undefined, { numeric: true, sensitivity: 'base' });
                    });
                }

                // Render Configurator Options
                function renderConfigurator() {
                    const configuratorWrapper = document.getElementById('dynamic-configurator');
                    const summaryCard = document.querySelector('.config-summary-card');
                    const optionsGridLayout = document.querySelector('.options-grid-layout');
                    const options = product.options || {};
                    const optionKeys = getOrderedOptionKeys(options);

                    if (optionKeys.length === 0) {
                        if (configuratorWrapper) {
                            configuratorWrapper.style.display = 'none';
                            configuratorWrapper.innerHTML = '';
                        }
                        if (summaryCard) {
                            summaryCard.style.display = 'none';
                        }
                        if (optionsGridLayout) {
                            optionsGridLayout.style.gridTemplateColumns = '1fr';
                        }
                        return;
                    }

                    if (configuratorWrapper) configuratorWrapper.style.display = 'block';
                    if (summaryCard) summaryCard.style.display = 'flex';
                    if (optionsGridLayout) optionsGridLayout.style.gridTemplateColumns = '1.25fr 0.75fr';

                    configurator.innerHTML =
                        '<div class="reset-selection" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;"><h3 style="font-family: var(--font-serif); font-size: 28px; margin: 0;">Product Configuration</h3><a href="#" id="btn-clear-selection" class="btn sm" style="display: flex; align-items: center; gap: 6px;"><svg xmlns="http://www.w3.org/2000/svg" height="15px" viewBox="0 -960 960 960" fill="currentColor"><path d="m336-280 144-144 144 144 56-56-144-144 144-144-56-56-144 144-144-144-56 56 144 144-144 144 56 56ZM480-80q-83 0-156-31.5T197-197q-54-54-85.5-127T80-480q0-83 31.5-156T197-763q54-54 127-85.5T480-880q83 0 156 31.5T763-763q54 54 85.5 127T880-480q0 83-31.5 156T763-197q-54 54-127 85.5T480-80Z"/></svg>Reset Selection</a></div>';

                    const clearBtn = document.getElementById('btn-clear-selection');
                    if (clearBtn) {
                        clearBtn.addEventListener('click', (e) => {
                            e.preventDefault();
                            selectedOptions = {};
                            renderConfigurator();
                            updateOptionAvailability();
                            recalculate();
                        });
                    }

                    const features = product.product_features || {};

                    optionKeys.forEach(optKey => {
                        const optVals = sortConfigOptionValues(optKey, options[optKey]);
                        if (!Array.isArray(optVals) || optVals.length === 0) return;
                        const safeKey = optKey.replace(/[^a-zA-Z0-9_-]/g, '-');
                        const group = document.createElement('div');
                        group.className = 'config-group';

                        group.innerHTML = `
                                                                                              <div class="config-group-title">
                                                                                                <span>${optKey}</span>
                                                                                              </div>
                                                                                              <div class="config-options-flex" id="options-flex-${safeKey}" style="display: flex; flex-wrap: wrap; gap: 8px;"></div>
                                                                                            `;
                        configurator.appendChild(group);

                        const flex = group.querySelector(`#options-flex-${safeKey}`);

                        optVals.forEach(valObj => {
                            const btn = document.createElement('button');
                            btn.className = 'config-btn';
                            btn.setAttribute('data-opt-key', optKey);
                            btn.setAttribute('data-opt-id', valObj.id);
                            btn.setAttribute('data-opt-name', valObj.name);

                            // Check if an attribute icon is available for this option value in product_features
                            let iconUrl = null;
                            const featureGroup = features[optKey];
                            if (Array.isArray(featureGroup)) {
                                const match = featureGroup.find(item => typeof item === 'object' && item !== null && (
                                    item.value === valObj.name || String(item.value).toLowerCase() === String(
                                        valObj.name).toLowerCase()));
                                if (match && match.icon) {
                                    iconUrl = match.icon;
                                }
                            }

                            let iconHtml = '';
                            if (iconUrl) {
                                iconHtml =
                                    `<img class="config-btn-icon attr-icon" src="${resolveImg(iconUrl)}" alt="${valObj.name}" style="width: 14px; height: 14px; object-fit: contain; flex-shrink: 0;" />`;
                            }

                            btn.innerHTML = `${iconHtml}<span>${valObj.name}</span>`;

                            if (selectedOptions[optKey] && String(selectedOptions[optKey]) === String(valObj.id)) {
                                btn.classList.add('active');
                            }

                            btn.addEventListener('click', () => {
                                if (selectedOptions[optKey] === valObj.id) {
                                    delete selectedOptions[optKey];
                                    btn.classList.remove('active');
                                } else {
                                    selectedOptions[optKey] = valObj.id;
                                    flex.querySelectorAll('.config-btn').forEach(b => b.classList.remove(
                                        'active'));
                                    btn.classList.add('active');
                                }

                                updateOptionAvailability();
                                recalculate();
                            });

                            flex.appendChild(btn);
                        });
                    });
                }

                // Render Product Icons from Airtable "Product Icons"
                function renderProductIcons() {
                    const iconsRow = document.querySelector('.product-icons-row');
                    const iconsWrapper = document.querySelector('.product-icons-row-wrapper');
                    if (!iconsRow) return;

                    const features = product.product_features || {};
                    const rawIcons = product.technical_icons || product.product_icons || features["Technical Icons"] ||
                        features["Technical icons"] || features["technical_icons"] || features["Product Icons"] || features[
                            "Product icons"] || features["product_icons"] || features["Icons"] || features["Icon"];

                    let iconUrls = [];
                    if (Array.isArray(rawIcons)) {
                        rawIcons.forEach(item => {
                            if (typeof item === 'string' && item.trim()) {
                                iconUrls.push(item.trim());
                            } else if (typeof item === 'object' && item !== null && item.url) {
                                iconUrls.push(item.url);
                            }
                        });
                    } else if (typeof rawIcons === 'string' && rawIcons.trim()) {
                        iconUrls.push(rawIcons.trim());
                    }

                    iconUrls = Array.from(new Set(iconUrls));

                    if (iconUrls.length > 0) {
                        if (iconsWrapper) iconsWrapper.style.display = 'block';
                        iconsRow.innerHTML = iconUrls.map((url, idx) => `
                                                                                            <img src="${resolveImg(url)}" alt="Product Icon ${idx + 1}"
                                                                                              style="height: 42px; width: auto; opacity: 0.85; transition: opacity 0.2s; max-width: 100px; object-fit: contain;"
                                                                                              onmouseover="this.style.opacity=1" onmouseout="this.style.opacity=0.85"
                                                                                              onerror="this.style.display='none'">
                                                                                          `).join('');
                    } else {
                        if (iconsWrapper) iconsWrapper.style.display = 'none';
                        iconsRow.innerHTML = '';
                    }
                }

                // Matrix Availability Checker (Derived from SKU Mappings)
                function updateOptionAvailability() {
                    const skuMatrix = getParsedSkuMatrix(product);
                    const options = product.options || {};
                    const optionKeys = getOrderedOptionKeys(options);

                    if (skuMatrix.length === 0) {
                        return;
                    }

                    // 1. Auto-clean any currently selected options that are no longer valid with the active upstream selections
                    optionKeys.forEach((optKey, idx) => {
                        const currentSelectedId = selectedOptions[optKey];
                        if (!currentSelectedId) return;

                        const optVals = options[optKey] || [];
                        const valObj = optVals.find(v => String(v.id) === String(currentSelectedId));
                        if (!valObj) {
                            delete selectedOptions[optKey];
                            return;
                        }

                        // Collect upstream selections that constrain this option
                        const upstreamSelections = {};
                        optionKeys.forEach((k, kIdx) => {
                            if (k === optKey) return;
                            const isSolitary = (options[k] || []).length === 1;
                            if ((kIdx < idx || isSolitary) && selectedOptions[k]) {
                                upstreamSelections[k] = selectedOptions[k];
                            }
                        });

                        const isValid = isCombinationAvailable(optKey, valObj, upstreamSelections, skuMatrix, options);
                        if (!isValid) {
                            delete selectedOptions[optKey];
                        }
                    });

                    // 2. Update button disabled/enabled & active states based on upstream selections
                    optionKeys.forEach((optKey, idx) => {
                        const optVals = options[optKey] || [];
                        const safeKey = optKey.replace(/[^a-zA-Z0-9_-]/g, '-');
                        const flex = document.getElementById(`options-flex-${safeKey}`);
                        if (!flex) return;

                        // Only upstream selections and solitary groups constrain this group's options
                        const constrainingSelections = {};
                        optionKeys.forEach((k, kIdx) => {
                            if (k === optKey) return;
                            const isSolitary = (options[k] || []).length === 1;
                            if ((kIdx < idx || isSolitary) && selectedOptions[k]) {
                                constrainingSelections[k] = selectedOptions[k];
                            }
                        });

                        const btns = flex.querySelectorAll('.config-btn');
                        btns.forEach(btn => {
                            const optId = String(btn.getAttribute('data-opt-id'));
                            const valObj = optVals.find(v => String(v.id) === optId);

                            const isAvailable = valObj
                                ? isCombinationAvailable(optKey, valObj, constrainingSelections, skuMatrix, options)
                                : true;

                            if (!isAvailable) {
                                btn.disabled = true;
                                btn.classList.add('disabled');
                                btn.classList.remove('active');
                            } else {
                                btn.disabled = false;
                                btn.classList.remove('disabled');
                                if (selectedOptions[optKey] && String(selectedOptions[optKey]) === optId) {
                                    btn.classList.add('active');
                                } else {
                                    btn.classList.remove('active');
                                }
                            }
                        });
                    });
                }

                // Recalculate parameters, SKU, lumens, drivers
                function recalculate() {
                    const pName = product.product_name || product.name || "Product";
                    const features = product.product_features || {};
                    const skuDisplay = getMappedSku(product, selectedOptions);
                    datasheetSku = skuDisplay || product.product_code || '';
                    const specAddBtn = document.getElementById('add-to-spec-btn');
                    if (specAddBtn) {
                        specAddBtn.dataset.quoteSku = skuDisplay || '';
                    }

                    // if (productCodeEl) productCodeEl.textContent = `PRODUCT CODE: ${skuDisplay}`;

                    let wattageVal = 14.4;
                    let selectedCctText = "";
                    let selectedFinishText = "";

                    const options = product.options || {};
                    for (const optKey in options) {
                        const optVals = options[optKey] || [];
                        const selectedId = selectedOptions[optKey];
                        const valObj = optVals.find(v => String(v.id) === String(selectedId));
                        if (valObj) {
                            if (optKey.toUpperCase() === 'WATTAGE') {
                                wattageVal = parseFloat(valObj.name.replace(/[^\d.]/g, '')) || 14.4;
                            } else if (optKey.toUpperCase() === 'CCT') {
                                selectedCctText = valObj.name;
                            } else if (optKey.toUpperCase() === 'FINISH' || optKey.toUpperCase() === 'COLOUR') {
                                selectedFinishText = valObj.name;
                            }
                        }
                    }

                    const totalPower = selectedLength * wattageVal;
                    const totalLumens = selectedLength * (wattageVal * 85);

                    if (calcPower) calcPower.textContent = totalPower.toFixed(1) + ' W';
                    if (calcLumens) calcLumens.textContent = Math.round(totalLumens).toLocaleString() + ' lm';

                    const requiredCapacity = totalPower * 1.20;
                    let driverRecommendation = "Azoogi 20W 24V IP67";
                    let driverPowerLimit = 20;

                    if (requiredCapacity > 200) {
                        driverRecommendation = "Multiple feeds / 2x Azoogi 150W Drivers";
                        driverPowerLimit = 300;
                    } else if (requiredCapacity > 150) {
                        driverRecommendation = "Azoogi 240W 24V IP67";
                        driverPowerLimit = 240;
                    } else if (requiredCapacity > 120) {
                        driverRecommendation = "Azoogi 150W 24V IP67";
                        driverPowerLimit = 150;
                    } else if (requiredCapacity > 80) {
                        driverRecommendation = "Azoogi 120W 24V IP67";
                        driverPowerLimit = 120;
                    } else if (requiredCapacity > 50) {
                        driverRecommendation = "Azoogi 80W 24V IP67";
                        driverPowerLimit = 80;
                    } else if (requiredCapacity > 20) {
                        driverRecommendation = "Azoogi 50W 24V IP67";
                        driverPowerLimit = 50;
                    }

                    if (calcDriver) calcDriver.textContent = driverRecommendation;

                    const percentage = Math.min(100, Math.round((totalPower / driverPowerLimit) * 100));
                    if (capacityPct) capacityPct.textContent = percentage + '%';
                    if (driverBarFill) driverBarFill.style.width = percentage + '%';

                    if (selectedLength > 10.0) {
                        if (driverWarning) driverWarning.style.display = 'block';
                    } else {
                        if (driverWarning) driverWarning.style.display = 'none';
                    }

                    let selectedOptionsSummary = [];

                    for (const key in selectedOptions) {
                        const id = selectedOptions[key];
                        const optVals = options[key] || [];
                        const valObj = optVals.find(v => String(v.id) === String(id)) || {
                            name: String(id)
                        };
                        if (valObj) {
                            selectedOptionsSummary.push(`${key}: ${valObj.name}`);
                        }
                    }

                    // Render dynamic summary card list
                    const summaryListEl = document.getElementById('selected-summary-list');
                    if (summaryListEl) {
                        summaryListEl.innerHTML = '';

                        const selectedKeys = Object.keys(selectedOptions).filter(k => selectedOptions[k] !== undefined &&
                            selectedOptions[k] !== null && String(selectedOptions[k]).trim() !== '');

                        if (selectedKeys.length > 0) {
                            if (skuDisplay) {
                                const modelDiv = document.createElement('div');
                                modelDiv.style.display = 'flex';
                                modelDiv.style.justifyContent = 'space-between';
                                modelDiv.style.borderBottom = '1px solid var(--line)';
                                modelDiv.style.paddingBottom = '8px';
                                modelDiv.innerHTML = `
                              <strong style="color: var(--ink);">PRODUCT CODE:</strong>
                              <span style="color: var(--accent); font-weight: 600;">${skuDisplay}</span>
                            `;
                                summaryListEl.appendChild(modelDiv);
                            }

                            // Render each option key/value pair
                            for (const key of selectedKeys) {
                                const id = selectedOptions[key];
                                const optVals = options[key] || [];
                                const valObj = optVals.find(v => String(v.id) === String(id)) || {
                                    name: String(id)
                                };
                                if (valObj) {
                                    const itemDiv = document.createElement('div');
                                    itemDiv.style.display = 'flex';
                                    itemDiv.style.justifyContent = 'space-between';
                                    itemDiv.style.fontSize = '12px';
                                    itemDiv.style.marginBottom = '4px';
                                    itemDiv.innerHTML = `
                                <span style="font-weight: 500; text-transform: capitalize; color: var(--muted);">${key}:</span>
                                <span style="color: var(--ink); font-weight: 600;">${valObj.name}</span>
                              `;
                                    summaryListEl.appendChild(itemDiv);
                                }
                            }
                        } else {
                            summaryListEl.innerHTML =
                                '<div style="color: var(--muted); font-size: 13px; font-style: italic; padding: 4px 0;">Please select configuration options to view product code and summary.</div>';
                        }
                    }

                    // if (productCodeEl) productCodeEl.textContent = `PRODUCT CODE: ${skuDisplay}`;

                    const catLower = (product.category || "").toLowerCase();
                    const pNameLower = pName.toLowerCase();
                    const isLinear = catLower.includes('neon') || catLower.includes('linear') || pNameLower.includes('strip') ||
                        pNameLower.includes('nnr');
                    const specLines = [
                        `Product: ${pName} (${product.category || 'General'})`,
                        `Variant Model: ${skuDisplay}`,
                        'Selected Options:',
                        ...selectedOptionsSummary.map((s) => `  - ${s}`),
                    ];

                    if (isLinear) {
                        specLines.push(
                            `Requested custom cut length: ${selectedLength.toFixed(1)}m`,
                            `Total power load: ${totalPower.toFixed(1)}W`,
                            `Recommended system driver: ${driverRecommendation}`,
                        );
                    }

                    const specField = document.getElementById('quote-spec');
                    if (specField) {
                        specField.value = specLines.map((line) => String(line).replace(/[ \t]+$/g, '')).join('\n');
                    }

                    const datasheetBtn = document.getElementById('download-custom-datasheet');
                    if (datasheetBtn) {
                        const optionKeys = Object.keys(product.options || {}).filter((key) => Array.isArray(product.options[key]) && product.options[key].length > 0);
                        const hasConfiguration = optionKeys.length === 0 || optionKeys.some((key) => {
                            const value = selectedOptions[key];
                            return value !== undefined && value !== null && String(value).trim() !== '';
                        });
                        datasheetBtn.disabled = !hasConfiguration;
                        datasheetBtn.setAttribute('aria-disabled', hasConfiguration ? 'false' : 'true');
                        datasheetBtn.title = hasConfiguration ? '' : 'Select configuration options first';
                    }

                    // Visual color glow update
                    // const glowArea = document.getElementById('glow-area');
                    const neonPath = document.getElementById('neon-path');
                    const neonSvg = document.getElementById('neon-svg');
                    // if (glowArea) {
                    //   let glowColor = '103, 208, 78';
                    //   if (selectedCctText.includes('2300K') || selectedCctText.includes('2200K')) glowColor = '255, 166, 77';
                    //   else if (selectedCctText.includes('2700K')) glowColor = '255, 180, 90';
                    //   else if (selectedCctText.includes('3000K')) glowColor = '255, 200, 120';
                    //   else if (selectedCctText.includes('4000K') || selectedCctText.includes('4300K')) glowColor = '255, 235, 200';
                    //   else if (selectedCctText.includes('6000K') || selectedCctText.includes('6500K')) glowColor = '220, 240, 255';
                    //   document.documentElement.style.setProperty('--glow-rgb', glowColor);

                    //   if (neonPath) {
                    //     neonPath.style.transition = 'none';
                    //     neonPath.style.strokeWidth = '22';
                    //     setTimeout(() => {
                    //       neonPath.style.transition = 'stroke-width 0.8s ease, filter 0.5s ease';
                    //       neonPath.style.strokeWidth = '14';
                    //     }, 50);
                    //   }
                    // }
                }

                // Initialize
                const initialOptions = product.options || {};
                const initialOptionKeys = getOrderedOptionKeys(initialOptions);
                initialOptionKeys.forEach(optKey => {
                    const optVals = initialOptions[optKey] || [];
                    if (optVals.length === 1 && !selectedOptions[optKey]) {
                        selectedOptions[optKey] = String(optVals[0].id);
                    }
                });

                renderGallery();
                renderConfigurator();
                renderProductIcons();
                renderRecommendedAccessories();
                updateOptionAvailability();
                recalculate();

                // Accordion Tab controllers
                document.querySelectorAll('.tab-nav-btn').forEach(btn => {
                    btn.addEventListener('click', function() {
                        document.querySelectorAll('.tab-nav-btn').forEach(b => b.classList.remove('active'));
                        document.querySelectorAll('.tab-panel').forEach(p => p.classList.remove('active'));

                        this.classList.add('active');
                        const tabId = this.getAttribute('data-tab');
                        const panel = document.getElementById(tabId);
                        if (panel) panel.classList.add('active');
                    });
                });

                // Spec button click animation for both details and summary buttons
                const handleSpecAdd = function() {
                    const originalText = this.textContent;
                    this.textContent = 'Added to Quote List!';
                    this.style.background = 'var(--rgba-hover)';
                    this.style.borderColor = 'var(--accent)';
                    this.style.color = 'var(--accent)';

                    setTimeout(() => {
                        this.textContent = originalText;
                        this.style.background = 'none';
                        this.style.borderColor = 'var(--border-light)';
                        this.style.color = 'var(--ink)';
                    }, 2000);
                };

                const specBtn = document.getElementById('add-to-spec-btn');
                if (specBtn) {
                    specBtn.addEventListener('click', handleSpecAdd);
                }
                const specSummaryBtn = document.getElementById('add-to-spec-btn-summary');
                if (specSummaryBtn) {
                    specSummaryBtn.addEventListener('click', handleSpecAdd);
                }

                // Hover Zoom effect on main image
                if (galleryMainImg) {
                    galleryMainImg.addEventListener('mousemove', (e) => {
                        const rect = galleryMainImg.getBoundingClientRect();
                        const x = ((e.clientX - rect.left) / rect.width) * 100;
                        const y = ((e.clientY - rect.top) / rect.height) * 100;
                        galleryMainImg.style.transformOrigin = `${x}% ${y}%`;
                        galleryMainImg.style.transform = 'scale(1.8)';
                    });

                    galleryMainImg.addEventListener('mouseleave', () => {
                        galleryMainImg.style.transform = 'scale(1)';
                        galleryMainImg.style.transformOrigin = 'center center';
                    });
                }

                const datasheetDialog = document.getElementById('datasheet-export-dialog');
                const datasheetForm = document.getElementById('datasheet-export-form');
                const datasheetOpen = document.getElementById('download-custom-datasheet');
                const datasheetCancel = document.getElementById('datasheet-export-cancel');
                const datasheetClose = document.getElementById('datasheet-export-close');
                const datasheetSubmit = document.getElementById('datasheet-export-submit');

                function setDatasheetError(name, message) {
                    const el = datasheetForm?.querySelector('[data-error-for="' + name + '"]');
                    if (!el) {
                        return;
                    }
                    if (message) {
                        el.hidden = false;
                        el.textContent = message;
                    } else {
                        el.hidden = true;
                        el.textContent = '';
                    }
                }

                function clearDatasheetErrors() {
                    datasheetForm?.querySelectorAll('[data-error-for]').forEach((el) => {
                        el.hidden = true;
                        el.textContent = '';
                    });
                }

                datasheetOpen?.addEventListener('click', () => {
                    if (datasheetOpen.disabled) {
                        return;
                    }
                    clearDatasheetErrors();
                    datasheetDialog?.showModal();
                    document.getElementById('datasheet-project-name')?.focus();
                });

                const closeDatasheetDialog = () => datasheetDialog?.close();
                datasheetCancel?.addEventListener('click', closeDatasheetDialog);
                datasheetClose?.addEventListener('click', closeDatasheetDialog);
                datasheetDialog?.addEventListener('click', (event) => {
                    if (event.target === datasheetDialog) {
                        closeDatasheetDialog();
                    }
                });

                datasheetForm?.addEventListener('submit', async (event) => {
                    event.preventDefault();
                    clearDatasheetErrors();

                    const token = datasheetForm.querySelector('input[name="_token"]')?.value || '';
                    const selected = {};
                    const optionSet = product.options || {};
                    Object.keys(selectedOptions).forEach((key) => {
                        const id = selectedOptions[key];
                        if (id === undefined || id === null || String(id).trim() === '') {
                            return;
                        }
                        const optVals = optionSet[key] || [];
                        const valObj = optVals.find((item) => String(item.id) === String(id)) || { name: String(id) };
                        selected[key] = valObj.name;
                    });

                    const pNameLower = (product.product_name || product.name || '').toLowerCase();
                    const catLower = (product.category || '').toLowerCase();
                    const isLinear = catLower.includes('neon') || catLower.includes('linear') || pNameLower.includes('strip') || pNameLower.includes('nnr');

                    if (datasheetSubmit) {
                        datasheetSubmit.disabled = true;
                    }

                    try {
                        const response = await fetch(datasheetForm.action, {
                            method: 'POST',
                            headers: {
                                'Accept': 'application/json',
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': token,
                                'X-Requested-With': 'XMLHttpRequest',
                            },
                            body: JSON.stringify({
                                product_id: product.id,
                                project_name: document.getElementById('datasheet-project-name')?.value || '',
                                person_name: document.getElementById('datasheet-person-name')?.value || '',
                                product_code: datasheetSku || product.product_code || '',
                                selected_options: selected,
                                length: isLinear ? selectedLength : null,
                            }),
                        });

                        const payload = await response.json().catch(() => ({}));

                        if (response.status === 422) {
                            const errors = payload.errors || {};
                            Object.keys(errors).forEach((key) => {
                                setDatasheetError(key, Array.isArray(errors[key]) ? errors[key][0] : String(errors[key]));
                            });
                            if (!Object.keys(errors).length) {
                                setDatasheetError('form', 'Enter the project name and client name.');
                            }
                            return;
                        }

                        if (!response.ok || !payload.url) {
                            setDatasheetError('form', 'The datasheet could not be generated. Try again.');
                            return;
                        }

                        datasheetDialog?.close();
                        window.open(payload.url, '_blank', 'noopener');
                    } catch (error) {
                        setDatasheetError('form', 'The datasheet could not be generated. Try again.');
                    } finally {
                        if (datasheetSubmit) {
                            datasheetSubmit.disabled = false;
                        }
                    }
                });
            }

            // Inquiry form posts to the server; success is shown after redirect.
        </script>
    @endverbatim
@endpush
