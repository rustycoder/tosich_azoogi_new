@extends('layouts.dashboard')

@section('title', 'Airtable Sync & Catalog Audit')

@section('content')
<div class="dash-sync-page">
    <div class="dash-head">
        <div>
            <h1>Airtable Sync &amp; Catalog Audit</h1>
            <p class="dash-lead">
                Trigger on-demand synchronization with Airtable and review catalog completeness, WebP images, and SVG icons.
                @if ($audit && isset($audit['audited_at']))
                    <span style="display: block; margin-top: 4px; font-size: 11.5px; color: var(--dash-muted);">
                        Last Audited: <strong style="color: var(--dash-ink);">{{ \Carbon\Carbon::parse($audit['audited_at'])->diffForHumans() }}</strong> ({{ $audit['audited_at_human'] }})
                    </span>
                @endif
            </p>
        </div>
        <div class="dash-head-actions" style="display: flex; gap: 10px; align-items: center;">
            <form method="post" action="{{ route('dashboard.sync.audit') }}">
                @csrf
                <button class="btn secondary" type="submit" title="Audit catalog without fetching from Airtable">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width: 15px; height: 15px; margin-right: 6px; display: inline-block; vertical-align: -2px;"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
                    <span>Run Catalog Audit</span>
                </button>
            </form>
            <form id="dash-product-sync-form" method="post" action="{{ route('dashboard.sync.trigger') }}" data-stream-url="{{ route('dashboard.products.sync.stream') }}">
                @csrf
                <button id="dash-product-sync-btn" class="btn primary" type="submit">
                    <svg class="dash-sync-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width: 15px; height: 15px; margin-right: 6px; display: inline-block; vertical-align: -2px;"><path d="M21.5 2v6h-6M2.5 22v-6h6M2 11.5a10 10 0 0 1 18.8-4.3M22 12.5a10 10 0 0 1-18.8 4.2"/></svg>
                    <span>Sync Airtable Now</span>
                </button>
            </form>
        </div>
    </div>

    <!-- Live Sync Streaming Progress Panel -->
    <div id="dash-sync-panel" class="dash-sync-panel" style="display: none; margin-bottom: 24px;">
        <div class="dash-sync-header">
            <div class="dash-sync-title-group">
                <h2 class="dash-sync-title">Live Product Synchronization</h2>
                <span id="dash-sync-status-badge" class="dash-pill is-active">
                    <span class="dash-sync-dot is-pulsing"></span>
                    <span id="dash-sync-status-text">Connecting...</span>
                </span>
            </div>
            <div class="dash-sync-actions">
                <button type="button" id="dash-sync-toggle-logs" class="btn secondary" style="padding: 6px 12px; font-size: 12px; margin-right: 6px;">Hide Logs</button>
                <button type="button" id="dash-sync-close-panel" class="btn secondary" style="padding: 6px 12px; font-size: 12px; display: none;">Close</button>
            </div>
        </div>

        <div class="dash-sync-progress-wrap">
            <div class="dash-sync-progress-labels">
                <span id="dash-sync-step" class="dash-sync-step">Initializing sync...</span>
                <span id="dash-sync-pct" class="dash-sync-pct">0%</span>
            </div>
            <div class="dash-sync-bar-track">
                <div id="dash-sync-bar-fill" class="dash-sync-bar-fill" style="width: 0%;"></div>
            </div>
        </div>

        <div class="dash-sync-stats-grid">
            <div class="dash-sync-stat">
                <span class="dash-sync-stat-label">Elapsed Time</span>
                <span id="dash-sync-elapsed" class="dash-sync-stat-val">0s</span>
            </div>
            <div class="dash-sync-stat">
                <span class="dash-sync-stat-label">Estimated Remaining</span>
                <span id="dash-sync-eta" class="dash-sync-stat-val is-highlight">Calculating...</span>
            </div>
            <div class="dash-sync-stat">
                <span class="dash-sync-stat-label">Processed Items</span>
                <span id="dash-sync-counts" class="dash-sync-stat-val">—</span>
            </div>
        </div>

        <div id="dash-sync-log-terminal" class="dash-sync-log-terminal">
            <!-- Dynamic SSE event log lines -->
        </div>
    </div>

    @if ($audit)
        <!-- Executive Catalog Health Metrics (4 KPI Cards) -->
    <div class="dash-home-grid" style="grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); margin-bottom: 24px;">
        <!-- Health Score Card -->
        <div class="dash-card" style="display: flex; flex-direction: column; justify-content: space-between; border-left: 4px solid {{ $audit['summary']['health_score'] >= 90 ? '#67d04e' : ($audit['summary']['health_score'] >= 75 ? '#fbbf24' : '#fb7185') }};">
            <div style="display: flex; justify-content: space-between; align-items: flex-start;">
                <div>
                    <span style="font-size: 11.5px; font-weight: 700; text-transform: uppercase; letter-spacing: .06em; color: var(--dash-muted);">Health Score</span>
                    <div style="font-size: 32px; font-weight: 800; color: {{ $audit['summary']['health_score'] >= 90 ? '#67d04e' : ($audit['summary']['health_score'] >= 75 ? '#fbbf24' : '#fb7185') }}; margin-top: 4px;">
                        {{ $audit['summary']['health_score'] }}%
                    </div>
                </div>
                <span class="dash-pill {{ $audit['summary']['health_score'] >= 90 ? 'is-active' : 'is-pending' }}">
                    {{ $audit['summary']['health_score'] >= 90 ? 'Optimal' : ($audit['summary']['health_score'] >= 75 ? 'Good' : 'Needs Review') }}
                </span>
            </div>
            <p style="margin: 10px 0 0; font-size: 12px; color: var(--dash-muted);">
                Calculated across images, technical PDFs, SKUs, and SEO metadata.
            </p>
        </div>

        <!-- Total Products Card -->
        <div class="dash-card" style="display: flex; flex-direction: column; justify-content: space-between; border-left: 4px solid #38bdf8;">
            <div style="display: flex; justify-content: space-between; align-items: flex-start;">
                <div>
                    <span style="font-size: 11.5px; font-weight: 700; text-transform: uppercase; letter-spacing: .06em; color: var(--dash-muted);">Products</span>
                    <div style="font-size: 32px; font-weight: 800; color: var(--dash-ink); margin-top: 4px;">
                        {{ number_format($audit['summary']['total_products']) }}
                    </div>
                </div>
                <a href="{{ route('dashboard.products.index') }}" class="dash-asset-chip" style="font-size: 11px; padding: 4px 8px;">
                    View All
                </a>
            </div>
            <div style="display: flex; gap: 8px; margin-top: 10px; font-size: 12px; color: var(--dash-muted);">
                <span class="dash-tag is-primary" style="font-size: 10.5px;">{{ $audit['summary']['published_products'] }} Published</span>
                <span class="dash-tag" style="font-size: 10.5px;">{{ $audit['summary']['draft_products'] }} Draft</span>
                @if ($audit['summary']['inactive_products'] > 0)
                    <span class="dash-tag" style="font-size: 10.5px;">{{ $audit['summary']['inactive_products'] }} Inactive</span>
                @endif
            </div>
        </div>

        <!-- Categories Card -->
        <div class="dash-card" style="display: flex; flex-direction: column; justify-content: space-between; border-left: 4px solid #c084fc;">
            <div style="display: flex; justify-content: space-between; align-items: flex-start;">
                <div>
                    <span style="font-size: 11.5px; font-weight: 700; text-transform: uppercase; letter-spacing: .06em; color: var(--dash-muted);">Categories</span>
                    <div style="font-size: 32px; font-weight: 800; color: var(--dash-ink); margin-top: 4px;">
                        {{ number_format($audit['summary']['total_categories']) }}
                    </div>
                </div>
                <a href="{{ route('dashboard.categories.index') }}" class="dash-asset-chip" style="font-size: 11px; padding: 4px 8px;">
                    View All
                </a>
            </div>
            <p style="margin: 10px 0 0; font-size: 12px; color: var(--dash-muted);">
                <strong>{{ $audit['summary']['root_categories'] }}</strong> Root hierarchies with subcategories.
            </p>
        </div>

        <!-- Product Attributes Card -->
        <div class="dash-card" style="display: flex; flex-direction: column; justify-content: space-between; border-left: 4px solid #fbbf24;">
            <div style="display: flex; justify-content: space-between; align-items: flex-start;">
                <div>
                    <span style="font-size: 11.5px; font-weight: 700; text-transform: uppercase; letter-spacing: .06em; color: var(--dash-muted);">Attributes</span>
                    <div style="font-size: 32px; font-weight: 800; color: var(--dash-ink); margin-top: 4px;">
                        {{ number_format($audit['summary']['total_attributes']) }}
                    </div>
                </div>
                <a href="{{ route('dashboard.product-attributes.index') }}" class="dash-asset-chip" style="font-size: 11px; padding: 4px 8px;">
                    View All
                </a>
            </div>
            <p style="margin: 10px 0 0; font-size: 12px; color: var(--dash-muted);">
                <strong>{{ $audit['summary']['visible_attributes'] }}</strong> terms visible on website filter drawers.
            </p>
        </div>
    </div>

    <!-- Latest Sync Snapshot Card -->
    @if ($latestSync)
        <section class="dash-sync-log is-{{ $latestSync->status->value }}" aria-label="Airtable sync status" style="margin-bottom: 24px;">
            <div class="dash-sync-log-head">
                <strong>Latest Airtable Sync Run</strong>
                <span class="dash-pill is-{{ $latestSync->status->value === 'ok' ? 'active' : ($latestSync->status->value === 'running' ? 'pending' : 'cancelled') }}">
                    {{ $latestSync->status->value === 'ok' ? 'Complete (OK)' : ucfirst($latestSync->status->value) }}
                </span>
                <span class="dash-sync-log-meta">
                    @if ($latestSync->started_at)
                        Started {{ $latestSync->started_at->timezone(config('app.timezone'))->format('d M Y, g:ia') }}
                    @endif
                    @if ($latestSync->finished_at)
                        · Completed in {{ $latestSync->started_at ? $latestSync->started_at->diffInSeconds($latestSync->finished_at) . 's' : $latestSync->finished_at->timezone(config('app.timezone'))->format('g:ia') }}
                    @endif
                    @if ($latestSync->products_count)
                        · {{ $latestSync->products_count }} active products
                    @endif
                    @if ($latestSync->triggered_by)
                        · Triggered via <span class="dash-code-badge">{{ $latestSync->triggered_by }}</span>
                    @endif
                </span>
            </div>
            @if ($latestSync->error)
                <p class="dash-sync-log-error">{{ $latestSync->error }}</p>
            @endif
        </section>
    @endif

    <!-- Section: Image & Icon Format Standards (WebP & SVG Only) -->
    <div class="dash-card" style="margin-bottom: 28px; border-top: 4px solid var(--dash-green);">
        <div style="display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid var(--dash-line); padding-bottom: 14px; margin-bottom: 18px;">
            <div>
                <h3 style="font-size: 16px; font-weight: 700; margin: 0; color: var(--dash-ink); display: flex; align-items: center; gap: 8px;">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width: 20px; height: 20px; color: var(--dash-green);"><rect x="3" y="3" width="18" height="18" rx="2" ry="2"/><circle cx="8.5" cy="8.5" r="1.5"/><polyline points="21 15 16 10 5 21"/></svg>
                    Asset Format Standards: WebP Images &amp; SVG Icons
                </h3>
                <p style="margin: 4px 0 0; font-size: 12px; color: var(--dash-muted);">
                    Strict requirements: All catalog images must be <strong>.webp</strong> format, and all technical/attribute icons must be <strong>.svg</strong> format.
                </p>
            </div>
            @php
                $formatIssuesCount = $audit['image_standards']['non_webp_covers']['count']
                    + $audit['image_standards']['non_webp_gallery']['count']
                    + $audit['image_standards']['non_webp_dimensions']['count']
                    + $audit['image_standards']['non_webp_categories']['count']
                    + $audit['image_standards']['non_svg_tech_icons']['count']
                    + $audit['image_standards']['non_svg_category_icons']['count']
                    + $audit['image_standards']['non_svg_attribute_icons']['count'];
            @endphp
            <span class="dash-pill {{ $formatIssuesCount === 0 ? 'is-active' : 'is-cancelled' }}">
                {{ $formatIssuesCount === 0 ? '100% Format Compliant' : $formatIssuesCount . ' Format Violations' }}
            </span>
        </div>

        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(320px, 1fr)); gap: 16px; margin-bottom: 16px;">
            <!-- 1. WebP Image Standard Compliance -->
            <div style="background: var(--dash-fill); border: 1px solid var(--dash-line); border-radius: 8px; padding: 16px;">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px; border-bottom: 1px solid var(--dash-line); padding-bottom: 8px;">
                    <strong style="font-size: 13px; color: var(--dash-ink); display: flex; align-items: center; gap: 6px;">
                        <span class="dash-tag is-primary" style="font-size: 10px; text-transform: uppercase;">WebP</span>
                        Image Formats
                    </strong>
                    <span style="font-size: 11px; color: var(--dash-muted);">Expected: .webp</span>
                </div>
                <div style="display: flex; flex-direction: column; gap: 10px;">
                    <div style="display: flex; justify-content: space-between; align-items: center;">
                        <span style="font-size: 12.5px; color: var(--dash-ink);">Product Image</span>
                        @if ($audit['image_standards']['non_webp_covers']['count'] === 0)
                            <span class="dash-pill is-active" style="font-size: 10.5px;">All WebP</span>
                        @else
                            <span class="dash-pill is-cancelled" style="font-size: 10.5px;">{{ $audit['image_standards']['non_webp_covers']['count'] }} Non-WebP</span>
                        @endif
                    </div>
                    <div style="display: flex; justify-content: space-between; align-items: center;">
                        <span style="font-size: 12.5px; color: var(--dash-ink);">Product Gallery</span>
                        @if ($audit['image_standards']['non_webp_gallery']['count'] === 0)
                            <span class="dash-pill is-active" style="font-size: 10.5px;">All WebP</span>
                        @else
                            <span class="dash-pill is-cancelled" style="font-size: 10.5px;">{{ $audit['image_standards']['non_webp_gallery']['count'] }} Non-WebP</span>
                        @endif
                    </div>
                    <div style="display: flex; justify-content: space-between; align-items: center;">
                        <span style="font-size: 12.5px; color: var(--dash-ink);">Product Dimension</span>
                        @if ($audit['image_standards']['non_webp_dimensions']['count'] === 0)
                            <span class="dash-pill is-active" style="font-size: 10.5px;">All WebP</span>
                        @else
                            <span class="dash-pill is-cancelled" style="font-size: 10.5px;">{{ $audit['image_standards']['non_webp_dimensions']['count'] }} Non-WebP</span>
                        @endif
                    </div>
                    <div style="display: flex; justify-content: space-between; align-items: center;">
                        <span style="font-size: 12.5px; color: var(--dash-ink);">Category Banners</span>
                        @if ($audit['image_standards']['non_webp_categories']['count'] === 0)
                            <span class="dash-pill is-active" style="font-size: 10.5px;">All WebP</span>
                        @else
                            <span class="dash-pill is-cancelled" style="font-size: 10.5px;">{{ $audit['image_standards']['non_webp_categories']['count'] }} Non-WebP</span>
                        @endif
                    </div>
                </div>
            </div>

            <!-- 2. SVG Icon Standard Compliance -->
            <div style="background: var(--dash-fill); border: 1px solid var(--dash-line); border-radius: 8px; padding: 16px;">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px; border-bottom: 1px solid var(--dash-line); padding-bottom: 8px;">
                    <strong style="font-size: 13px; color: var(--dash-ink); display: flex; align-items: center; gap: 6px;">
                        <span class="dash-tag is-primary" style="font-size: 10px; text-transform: uppercase;">SVG</span>
                        Icon Formats
                    </strong>
                    <span style="font-size: 11px; color: var(--dash-muted);">Expected: .svg only</span>
                </div>
                <div style="display: flex; flex-direction: column; gap: 10px;">
                    <div style="display: flex; justify-content: space-between; align-items: center;">
                        <span style="font-size: 12.5px; color: var(--dash-ink);">Technical Icons</span>
                        @if ($audit['image_standards']['non_svg_tech_icons']['count'] === 0)
                            <span class="dash-pill is-active" style="font-size: 10.5px;">100% SVG</span>
                        @else
                            <span class="dash-pill is-cancelled" style="font-size: 10.5px;">{{ $audit['image_standards']['non_svg_tech_icons']['count'] }} Non-SVG</span>
                        @endif
                    </div>
                    <div style="display: flex; justify-content: space-between; align-items: center;">
                        <span style="font-size: 12.5px; color: var(--dash-ink);">Category Icons</span>
                        @if ($audit['image_standards']['non_svg_category_icons']['count'] === 0)
                            <span class="dash-pill is-active" style="font-size: 10.5px;">100% SVG</span>
                        @else
                            <span class="dash-pill is-cancelled" style="font-size: 10.5px;">{{ $audit['image_standards']['non_svg_category_icons']['count'] }} Non-SVG</span>
                        @endif
                    </div>
                    <div style="display: flex; justify-content: space-between; align-items: center;">
                        <span style="font-size: 12.5px; color: var(--dash-ink);">Attribute Term Icons</span>
                        @if ($audit['image_standards']['non_svg_attribute_icons']['count'] === 0)
                            <span class="dash-pill is-active" style="font-size: 10.5px;">100% SVG</span>
                        @else
                            <span class="dash-pill is-cancelled" style="font-size: 10.5px;">{{ $audit['image_standards']['non_svg_attribute_icons']['count'] }} Non-SVG</span>
                        @endif
                    </div>
                </div>
            </div>

            <!-- 3. Dimension & Aspect Ratio Checks -->
            <div style="background: var(--dash-fill); border: 1px solid var(--dash-line); border-radius: 8px; padding: 16px;">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px; border-bottom: 1px solid var(--dash-line); padding-bottom: 8px;">
                    <strong style="font-size: 13px; color: var(--dash-ink); display: flex; align-items: center; gap: 6px;">
                        <span class="dash-tag" style="font-size: 10px; text-transform: uppercase;">1:1</span>
                        Aspect Ratio &amp; Res
                    </strong>
                    <span style="font-size: 11px; color: var(--dash-muted);">Standard: Square &ge; 600px</span>
                </div>
                <div style="display: flex; flex-direction: column; gap: 10px;">
                    <div style="display: flex; justify-content: space-between; align-items: center;">
                        <span style="font-size: 12.5px; color: var(--dash-ink);">Square Covers (1:1 Ratio)</span>
                        @if ($audit['image_standards']['non_square_covers']['count'] === 0)
                            <span class="dash-pill is-active" style="font-size: 10.5px;">Optimal</span>
                        @else
                            <span class="dash-pill is-cancelled" style="font-size: 10.5px;">{{ $audit['image_standards']['non_square_covers']['count'] }} Non-Square</span>
                        @endif
                    </div>
                    <div style="display: flex; justify-content: space-between; align-items: center;">
                        <span style="font-size: 12.5px; color: var(--dash-ink);">High Resolution (&ge; 600px)</span>
                        @if ($audit['image_standards']['low_res_covers']['count'] === 0)
                            <span class="dash-pill is-active" style="font-size: 10.5px;">Optimal</span>
                        @else
                            <span class="dash-pill is-cancelled" style="font-size: 10.5px;">{{ $audit['image_standards']['low_res_covers']['count'] }} Low Res</span>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        @if (!empty($audit['image_standards']['non_webp_covers']['samples']) || !empty($audit['image_standards']['non_svg_tech_icons']['samples']))
            <div style="background: var(--dash-mix); border: 1px dashed var(--dash-line); border-radius: 6px; padding: 14px; font-size: 12px;">
                <strong style="color: var(--dash-ink); display: block; margin-bottom: 8px;">Sample Non-Compliant Items to Update in Airtable:</strong>
                <div style="display: flex; flex-wrap: wrap; gap: 8px;">
                    @foreach ($audit['image_standards']['non_webp_covers']['samples'] as $sample)
                        <div style="display: inline-flex; align-items: center; gap: 6px; background: var(--dash-fill); border: 1px solid var(--dash-line); border-radius: 4px; padding: 4px 8px;">
                            <span class="dash-tag is-cancelled" style="font-size: 9.5px;">{{ $sample['format'] }}</span>
                            <strong style="font-size: 11.5px; color: var(--dash-ink);">{{ $sample['code'] ?: $sample['name'] }}</strong>
                        </div>
                    @endforeach
                    @foreach ($audit['image_standards']['non_svg_tech_icons']['samples'] as $sample)
                        <div style="display: inline-flex; align-items: center; gap: 6px; background: var(--dash-fill); border: 1px solid var(--dash-line); border-radius: 4px; padding: 4px 8px;">
                            <span class="dash-tag is-cancelled" style="font-size: 9.5px;">ICON: {{ $sample['format'] }}</span>
                            <strong style="font-size: 11.5px; color: var(--dash-ink);">{{ $sample['code'] ?: $sample['name'] }}</strong>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif
    </div>

    <!-- Section: Catalog Quality & Asset Completeness Audit Master Card -->
    <div class="dash-card" style="margin-bottom: 28px; border-top: 4px solid #a855f7;">
        <div style="display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid var(--dash-line); padding-bottom: 14px; margin-bottom: 18px;">
            <div>
                <h3 style="font-size: 16px; font-weight: 700; margin: 0; color: var(--dash-ink); display: flex; align-items: center; gap: 8px;">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width: 20px; height: 20px; color: #a855f7;"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
                    Catalog Quality &amp; Asset Completeness Audit
                </h3>
                <p style="margin: 4px 0 0; font-size: 12px; color: var(--dash-muted);">
                    Comprehensive audit across media schematics, technical documentation, core identifiers, and URL routing completeness.
                </p>
            </div>
            @php
                $completenessIssuesCount = $audit['media']['missing_cover']['count']
                    + $audit['media']['missing_gallery']['count']
                    + $audit['media']['missing_dimensions']['count']
                    + $audit['media']['missing_tech_icons']['count']
                    + $audit['documents']['missing_datasheet_file']['count']
                    + $audit['documents']['missing_guide']['count']
                    + $audit['documents']['missing_manual']['count']
                    + $audit['documents']['missing_ies']['count']
                    + $audit['core']['missing_sku']['count']
                    + $audit['core']['missing_supplier_code']['count']
                    + $audit['core']['missing_category']['count']
                    + $audit['seo']['missing_slug']['count']
                    + $audit['seo']['missing_description']['count']
                    + $audit['seo']['missing_meta_title']['count']
                    + $audit['seo']['missing_meta_description']['count'];
            @endphp
            <span class="dash-pill {{ $completenessIssuesCount === 0 ? 'is-active' : 'is-cancelled' }}">
                {{ $completenessIssuesCount === 0 ? '100% Attributes Complete' : $completenessIssuesCount . ' Missing' }}
            </span>
        </div>

        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(320px, 1fr)); gap: 16px; margin-bottom: 16px;">
            <!-- 1. Media & Schematics Audit -->
            <div style="background: var(--dash-fill); border: 1px solid var(--dash-line); border-radius: 8px; padding: 16px;">
                <div style="margin-bottom: 12px; border-bottom: 1px solid var(--dash-line); padding-bottom: 8px;">
                    <strong style="font-size: 13px; color: var(--dash-ink);">Media &amp; Schematics</strong>
                </div>
                
                <div style="display: flex; flex-direction: column; gap: 10px;">
                    <!-- Missing Cover Image -->
                    <div style="display: flex; justify-content: space-between; align-items: center;">
                        <span style="font-size: 12.5px; color: var(--dash-ink);">Product Image</span>
                        @if ($audit['media']['missing_cover']['count'] === 0)
                            <span class="dash-pill is-active" style="font-size: 10.5px;">100% Present</span>
                        @else
                            <span class="dash-pill is-cancelled" style="font-size: 10.5px;">{{ $audit['media']['missing_cover']['count'] }} Missing</span>
                        @endif
                    </div>

                    <!-- Missing Gallery Images -->
                    <div style="display: flex; justify-content: space-between; align-items: center;">
                        <span style="font-size: 12.5px; color: var(--dash-ink);">Product Gallery</span>
                        @if ($audit['media']['missing_gallery']['count'] === 0)
                            <span class="dash-pill is-active" style="font-size: 10.5px;">100% Present</span>
                        @else
                            <span class="dash-pill is-cancelled" style="font-size: 10.5px;">{{ $audit['media']['missing_gallery']['count'] }} Missing</span>
                        @endif
                    </div>

                    <!-- Missing Dimensions -->
                    <div style="display: flex; justify-content: space-between; align-items: center;">
                        <span style="font-size: 12.5px; color: var(--dash-ink);">Product Dimension</span>
                        @if ($audit['media']['missing_dimensions']['count'] === 0)
                            <span class="dash-pill is-active" style="font-size: 10.5px;">100% Present</span>
                        @else
                            <span class="dash-pill is-cancelled" style="font-size: 10.5px;">{{ $audit['media']['missing_dimensions']['count'] }} Missing</span>
                        @endif
                    </div>

                    <!-- Missing Technical Icons -->
                    <div style="display: flex; justify-content: space-between; align-items: center;">
                        <span style="font-size: 12.5px; color: var(--dash-ink);">Technical Icons</span>
                        @if ($audit['media']['missing_tech_icons']['count'] === 0)
                            <span class="dash-pill is-active" style="font-size: 10.5px;">100% Present</span>
                        @else
                            <span class="dash-pill is-cancelled" style="font-size: 10.5px;">{{ $audit['media']['missing_tech_icons']['count'] }} Missing</span>
                        @endif
                    </div>
                </div>
            </div>

            <!-- 2. Technical Documents Audit -->
            <div style="background: var(--dash-fill); border: 1px solid var(--dash-line); border-radius: 8px; padding: 16px;">
                <div style="margin-bottom: 12px; border-bottom: 1px solid var(--dash-line); padding-bottom: 8px;">
                    <strong style="font-size: 13px; color: var(--dash-ink);">Technical Documents</strong>
                </div>

                <div style="display: flex; flex-direction: column; gap: 10px;">
                    <!-- Missing Datasheet PDF -->
                    <div style="display: flex; justify-content: space-between; align-items: center;">
                        <span style="font-size: 12.5px; color: var(--dash-ink);">Datasheet PDF (Flagged Yes)</span>
                        @if ($audit['documents']['missing_datasheet_file']['count'] === 0)
                            <span class="dash-pill is-active" style="font-size: 10.5px;">100% Attached</span>
                        @else
                            <span class="dash-pill is-cancelled" style="font-size: 10.5px;">{{ $audit['documents']['missing_datasheet_file']['count'] }} Missing PDF</span>
                        @endif
                    </div>

                    <!-- Missing Installation Guide -->
                    <div style="display: flex; justify-content: space-between; align-items: center;">
                        <span style="font-size: 12.5px; color: var(--dash-ink);">Installation Guides</span>
                        @if ($audit['documents']['missing_guide']['count'] === 0)
                            <span class="dash-pill is-active" style="font-size: 10.5px;">100% Attached</span>
                        @else
                            <span class="dash-pill is-cancelled" style="font-size: 10.5px;">{{ $audit['documents']['missing_guide']['count'] }} Missing</span>
                        @endif
                    </div>

                    <!-- Missing User Manual -->
                    <div style="display: flex; justify-content: space-between; align-items: center;">
                        <span style="font-size: 12.5px; color: var(--dash-ink);">User Manuals</span>
                        @if ($audit['documents']['missing_manual']['count'] === 0)
                            <span class="dash-pill is-active" style="font-size: 10.5px;">100% Attached</span>
                        @else
                            <span class="dash-pill is-cancelled" style="font-size: 10.5px;">{{ $audit['documents']['missing_manual']['count'] }} Missing</span>
                        @endif
                    </div>

                    <!-- Missing IES Photometrics -->
                    <div style="display: flex; justify-content: space-between; align-items: center;">
                        <span style="font-size: 12.5px; color: var(--dash-ink);">IES Photometric Files</span>
                        @if ($audit['documents']['missing_ies']['count'] === 0)
                            <span class="dash-pill is-active" style="font-size: 10.5px;">100% Attached</span>
                        @else
                            <span class="dash-pill is-cancelled" style="font-size: 10.5px;">{{ $audit['documents']['missing_ies']['count'] }} Missing</span>
                        @endif
                    </div>
                </div>
            </div>

            <!-- 3. Core ID & Specifications Audit -->
            <div style="background: var(--dash-fill); border: 1px solid var(--dash-line); border-radius: 8px; padding: 16px;">
                <div style="margin-bottom: 12px; border-bottom: 1px solid var(--dash-line); padding-bottom: 8px;">
                    <strong style="font-size: 13px; color: var(--dash-ink);">Core ID &amp; Inventory</strong>
                </div>

                <div style="display: flex; flex-direction: column; gap: 10px;">
                    <!-- Missing Product Code (SKU) -->
                    <div style="display: flex; justify-content: space-between; align-items: center;">
                        <span style="font-size: 12.5px; color: var(--dash-ink);">Product Code (SKU)</span>
                        @if ($audit['core']['missing_sku']['count'] === 0)
                            <span class="dash-pill is-active" style="font-size: 10.5px;">100% Populated</span>
                        @else
                            <span class="dash-pill is-cancelled" style="font-size: 10.5px;">{{ $audit['core']['missing_sku']['count'] }} Missing</span>
                        @endif
                    </div>

                    <!-- Missing Supplier Code -->
                    <div style="display: flex; justify-content: space-between; align-items: center;">
                        <span style="font-size: 12.5px; color: var(--dash-ink);">Supplier Code</span>
                        @if ($audit['core']['missing_supplier_code']['count'] === 0)
                            <span class="dash-pill is-active" style="font-size: 10.5px;">100% Populated</span>
                        @else
                            <span class="dash-pill is-cancelled" style="font-size: 10.5px;">{{ $audit['core']['missing_supplier_code']['count'] }} Missing</span>
                        @endif
                    </div>

                    <!-- Missing Category -->
                    <div style="display: flex; justify-content: space-between; align-items: center;">
                        <span style="font-size: 12.5px; color: var(--dash-ink);">Primary Category</span>
                        @if ($audit['core']['missing_category']['count'] === 0)
                            <span class="dash-pill is-active" style="font-size: 10.5px;">100% Assigned</span>
                        @else
                            <span class="dash-pill is-cancelled" style="font-size: 10.5px;">{{ $audit['core']['missing_category']['count'] }} Unassigned</span>
                        @endif
                    </div>
                </div>
            </div>

            <!-- 4. Copy & SEO Metadata Audit -->
            <div style="background: var(--dash-fill); border: 1px solid var(--dash-line); border-radius: 8px; padding: 16px;">
                <div style="margin-bottom: 12px; border-bottom: 1px solid var(--dash-line); padding-bottom: 8px;">
                    <strong style="font-size: 13px; color: var(--dash-ink);">Copy &amp; SEO Meta</strong>
                </div>

                <div style="display: flex; flex-direction: column; gap: 10px;">
                    <!-- Missing URL Slug -->
                    <div style="display: flex; justify-content: space-between; align-items: center;">
                        <span style="font-size: 12.5px; color: var(--dash-ink);">URL Slugs</span>
                        @if ($audit['seo']['missing_slug']['count'] === 0)
                            <span class="dash-pill is-active" style="font-size: 10.5px;">100% Populated</span>
                        @else
                            <span class="dash-pill is-cancelled" style="font-size: 10.5px;">{{ $audit['seo']['missing_slug']['count'] }} Missing</span>
                        @endif
                    </div>

                    <!-- Product Descriptions -->
                    <div style="display: flex; justify-content: space-between; align-items: center;">
                        <span style="font-size: 12.5px; color: var(--dash-ink);">Product Descriptions</span>
                        @if ($audit['seo']['missing_description']['count'] === 0)
                            <span class="dash-pill is-active" style="font-size: 10.5px;">100% Present</span>
                        @else
                            <span class="dash-pill is-cancelled" style="font-size: 10.5px;">{{ $audit['seo']['missing_description']['count'] }} Missing</span>
                        @endif
                    </div>

                    <!-- Meta Titles -->
                    <div style="display: flex; justify-content: space-between; align-items: center;">
                        <span style="font-size: 12.5px; color: var(--dash-ink);">Meta Titles (30–60 Chars)</span>
                        @if ($audit['seo']['missing_meta_title']['count'] === 0 && ($audit['seo']['standards']['meta_title']['too_long_count'] ?? 0) === 0 && ($audit['seo']['standards']['meta_title']['too_short_count'] ?? 0) === 0)
                            <span class="dash-pill is-active" style="font-size: 10.5px;">100% Optimal</span>
                        @elseif ($audit['seo']['missing_meta_title']['count'] > 0)
                            <span class="dash-pill is-cancelled" style="font-size: 10.5px;">{{ $audit['seo']['missing_meta_title']['count'] }} Missing</span>
                        @else
                            <span class="dash-pill is-cancelled" style="font-size: 10.5px;">{{ $audit['seo']['standards']['meta_title']['too_long_count'] + $audit['seo']['standards']['meta_title']['too_short_count'] }} Warnings</span>
                        @endif
                    </div>

                    <!-- Meta Descriptions -->
                    <div style="display: flex; justify-content: space-between; align-items: center;">
                        <span style="font-size: 12.5px; color: var(--dash-ink);">Meta Descriptions (70–160 Chars)</span>
                        @if ($audit['seo']['missing_meta_description']['count'] === 0 && ($audit['seo']['standards']['meta_description']['too_long_count'] ?? 0) === 0 && ($audit['seo']['standards']['meta_description']['too_short_count'] ?? 0) === 0)
                            <span class="dash-pill is-active" style="font-size: 10.5px;">100% Optimal</span>
                        @elseif ($audit['seo']['missing_meta_description']['count'] > 0)
                            <span class="dash-pill is-cancelled" style="font-size: 10.5px;">{{ $audit['seo']['missing_meta_description']['count'] }} Missing</span>
                        @else
                            <span class="dash-pill is-cancelled" style="font-size: 10.5px;">{{ $audit['seo']['standards']['meta_description']['too_long_count'] + $audit['seo']['standards']['meta_description']['too_short_count'] }} Limit Warnings</span>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Section: SEO & Copywriting Character Count Standards -->
    @if (isset($audit['seo']['standards']))
        <div class="dash-card" style="margin-bottom: 28px; border-top: 4px solid #38bdf8;">
            <div style="display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid var(--dash-line); padding-bottom: 14px; margin-bottom: 18px;">
                <div>
                    <h3 style="font-size: 16px; font-weight: 700; margin: 0; color: var(--dash-ink); display: flex; align-items: center; gap: 8px;">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width: 20px; height: 20px; color: #38bdf8;"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                        Copywriting &amp; SEO Character Count Standards
                    </h3>
                    <p style="margin: 4px 0 0; font-size: 12px; color: var(--dash-muted);">
                        Recommended limits for search engine snippet rendering and conversion: <br><strong>Meta Title (30–60 chars)</strong>, <strong>Meta Description (70–160 chars)</strong>, <strong>Product Description (80–1,500 chars)</strong>.
                    </p>
                </div>
                @php
                    $seoViolations = ($audit['seo']['standards']['meta_title']['too_short_count'] ?? 0)
                        + ($audit['seo']['standards']['meta_title']['too_long_count'] ?? 0)
                        + ($audit['seo']['standards']['meta_description']['too_short_count'] ?? 0)
                        + ($audit['seo']['standards']['meta_description']['too_long_count'] ?? 0)
                        + ($audit['seo']['standards']['product_description']['too_short_count'] ?? 0)
                        + ($audit['seo']['standards']['product_description']['too_long_count'] ?? 0);
                @endphp
                <span class="dash-pill {{ $seoViolations === 0 ? 'is-active' : 'is-cancelled' }}">
                    {{ $seoViolations === 0 ? '100% Within Recommended Limits' : $seoViolations . ' Warnings' }}
                </span>
            </div>

            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(320px, 1fr)); gap: 16px; margin-bottom: 16px;">
                <!-- 1. Meta Title Standards -->
                <div style="background: var(--dash-fill); border: 1px solid var(--dash-line); border-radius: 8px; padding: 16px;">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px; border-bottom: 1px solid var(--dash-line); padding-bottom: 8px;">
                        <strong style="font-size: 13px; color: var(--dash-ink); display: flex; align-items: center; gap: 6px;">
                            <span class="dash-tag is-primary" style="font-size: 10px;">TITLE</span>
                            Meta Title
                        </strong>
                        <span style="font-size: 11px; color: var(--dash-muted);">Target: 30–60 chars</span>
                    </div>
                    <div style="display: flex; flex-direction: column; gap: 10px;">
                        <div style="display: flex; justify-content: space-between; align-items: center;">
                            <span style="font-size: 12.5px; color: var(--dash-ink);">Optimal Length (30–60 chars)</span>
                            <span class="dash-pill is-active" style="font-size: 10.5px;">{{ $audit['seo']['standards']['meta_title']['optimal_count'] }} Products</span>
                        </div>
                        <div style="display: flex; justify-content: space-between; align-items: center;">
                            <span style="font-size: 12.5px; color: var(--dash-ink);">Too Short (&lt; 30 chars)</span>
                            @if ($audit['seo']['standards']['meta_title']['too_short_count'] === 0)
                                <span class="dash-pill is-active" style="font-size: 10.5px;">0</span>
                            @else
                                <span class="dash-pill is-cancelled" style="font-size: 10.5px;">{{ $audit['seo']['standards']['meta_title']['too_short_count'] }} Short</span>
                            @endif
                        </div>
                        <div style="display: flex; justify-content: space-between; align-items: center;">
                            <span style="font-size: 12.5px; color: var(--dash-ink);">Exceeds Limit (&gt; 60 chars)</span>
                            @if ($audit['seo']['standards']['meta_title']['too_long_count'] === 0)
                                <span class="dash-pill is-active" style="font-size: 10.5px;">0</span>
                            @else
                                <span class="dash-pill is-cancelled" style="font-size: 10.5px;">{{ $audit['seo']['standards']['meta_title']['too_long_count'] }} Over Limit</span>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- 2. Meta Description Standards -->
                <div style="background: var(--dash-fill); border: 1px solid var(--dash-line); border-radius: 8px; padding: 16px;">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px; border-bottom: 1px solid var(--dash-line); padding-bottom: 8px;">
                        <strong style="font-size: 13px; color: var(--dash-ink); display: flex; align-items: center; gap: 6px;">
                            <span class="dash-tag is-primary" style="font-size: 10px;">DESC</span>
                            Meta Description
                        </strong>
                        <span style="font-size: 11px; color: var(--dash-muted);">Target: 70–160 chars</span>
                    </div>
                    <div style="display: flex; flex-direction: column; gap: 10px;">
                        <div style="display: flex; justify-content: space-between; align-items: center;">
                            <span style="font-size: 12.5px; color: var(--dash-ink);">Optimal Length (70–160 chars)</span>
                            <span class="dash-pill is-active" style="font-size: 10.5px;">{{ $audit['seo']['standards']['meta_description']['optimal_count'] }} Products</span>
                        </div>
                        <div style="display: flex; justify-content: space-between; align-items: center;">
                            <span style="font-size: 12.5px; color: var(--dash-ink);">Too Short (&lt; 70 chars)</span>
                            @if ($audit['seo']['standards']['meta_description']['too_short_count'] === 0)
                                <span class="dash-pill is-active" style="font-size: 10.5px;">0</span>
                            @else
                                <span class="dash-pill is-cancelled" style="font-size: 10.5px;">{{ $audit['seo']['standards']['meta_description']['too_short_count'] }} Short</span>
                            @endif
                        </div>
                        <div style="display: flex; justify-content: space-between; align-items: center;">
                            <span style="font-size: 12.5px; color: var(--dash-ink);">Exceeds Limit (&gt; 160 chars)</span>
                            @if ($audit['seo']['standards']['meta_description']['too_long_count'] === 0)
                                <span class="dash-pill is-active" style="font-size: 10.5px;">0</span>
                            @else
                                <span class="dash-pill is-cancelled" style="font-size: 10.5px;">{{ $audit['seo']['standards']['meta_description']['too_long_count'] }} Over Limit</span>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- 3. Product Description Standards -->
                <div style="background: var(--dash-fill); border: 1px solid var(--dash-line); border-radius: 8px; padding: 16px;">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px; border-bottom: 1px solid var(--dash-line); padding-bottom: 8px;">
                        <strong style="font-size: 13px; color: var(--dash-ink); display: flex; align-items: center; gap: 6px;">
                            <span class="dash-tag" style="font-size: 10px;">DESC</span>
                            Product Description
                        </strong>
                        <span style="font-size: 11px; color: var(--dash-muted);">Target: 80–1,500 chars</span>
                    </div>
                    <div style="display: flex; flex-direction: column; gap: 10px;">
                        <div style="display: flex; justify-content: space-between; align-items: center;">
                            <span style="font-size: 12.5px; color: var(--dash-ink);">Optimal Length (80–1,500 chars)</span>
                            <span class="dash-pill is-active" style="font-size: 10.5px;">{{ $audit['seo']['standards']['product_description']['optimal_count'] }} Products</span>
                        </div>
                        <div style="display: flex; justify-content: space-between; align-items: center;">
                            <span style="font-size: 12.5px; color: var(--dash-ink);">Thin Content (&lt; 80 chars)</span>
                            @if ($audit['seo']['standards']['product_description']['too_short_count'] === 0)
                                <span class="dash-pill is-active" style="font-size: 10.5px;">0</span>
                            @else
                                <span class="dash-pill is-cancelled" style="font-size: 10.5px;">{{ $audit['seo']['standards']['product_description']['too_short_count'] }} Thin</span>
                            @endif
                        </div>
                        <div style="display: flex; justify-content: space-between; align-items: center;">
                            <span style="font-size: 12.5px; color: var(--dash-ink);">Very Long (&gt; 1,500 chars)</span>
                            @if ($audit['seo']['standards']['product_description']['too_long_count'] === 0)
                                <span class="dash-pill is-active" style="font-size: 10.5px;">0</span>
                            @else
                                <span class="dash-pill is-cancelled" style="font-size: 10.5px;">{{ $audit['seo']['standards']['product_description']['too_long_count'] }} Long</span>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            @if (!empty($audit['seo']['standards']['meta_title']['too_long_samples']) || !empty($audit['seo']['standards']['meta_title']['too_short_samples']) || !empty($audit['seo']['standards']['meta_description']['too_short_samples']) || !empty($audit['seo']['standards']['meta_description']['too_long_samples']))
                <div style="background: var(--dash-mix); border: 1px dashed var(--dash-line); border-radius: 6px; padding: 14px; font-size: 12px;">
                    <strong style="color: var(--dash-ink); display: block; margin-bottom: 8px;">Out-of-Limit SEO Samples to Review in Airtable:</strong>
                    <div style="display: flex; flex-direction: column; gap: 8px;">
                        @foreach ($audit['seo']['standards']['meta_title']['too_long_samples'] as $sample)
                            <div style="display: flex; align-items: center; gap: 10px; background: var(--dash-fill); padding: 7px 12px; border-radius: 4px; border: 1px solid var(--dash-line);">
                                <span class="dash-tag is-cancelled" style="font-size: 9.5px; flex-shrink: 0;">Title: {{ $sample['length'] }} Chars (&gt;60)</span>
                                <strong style="color: var(--dash-ink); font-size: 12px; flex-shrink: 0;">{{ $sample['code'] ?: $sample['name'] }}</strong>
                                <span style="color: var(--dash-muted); font-size: 11.5px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">&ldquo;{{ $sample['value'] }}&rdquo;</span>
                            </div>
                        @endforeach
                        @foreach ($audit['seo']['standards']['meta_title']['too_short_samples'] as $sample)
                            <div style="display: flex; align-items: center; gap: 10px; background: var(--dash-fill); padding: 7px 12px; border-radius: 4px; border: 1px solid var(--dash-line);">
                                <span class="dash-tag is-cancelled" style="font-size: 9.5px; flex-shrink: 0;">Title: {{ $sample['length'] }} Chars (&lt;30)</span>
                                <strong style="color: var(--dash-ink); font-size: 12px; flex-shrink: 0;">{{ $sample['code'] ?: $sample['name'] }}</strong>
                                <span style="color: var(--dash-muted); font-size: 11.5px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">&ldquo;{{ $sample['value'] }}&rdquo;</span>
                            </div>
                        @endforeach
                        @foreach ($audit['seo']['standards']['meta_description']['too_short_samples'] as $sample)
                            <div style="display: flex; align-items: center; gap: 10px; background: var(--dash-fill); padding: 7px 12px; border-radius: 4px; border: 1px solid var(--dash-line);">
                                <span class="dash-tag is-cancelled" style="font-size: 9.5px; flex-shrink: 0;">Meta Desc: {{ $sample['length'] }} Chars (&lt;70)</span>
                                <strong style="color: var(--dash-ink); font-size: 12px; flex-shrink: 0;">{{ $sample['code'] ?: $sample['name'] }}</strong>
                                <span style="color: var(--dash-muted); font-size: 11.5px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">&ldquo;{{ $sample['value'] }}&rdquo;</span>
                            </div>
                        @endforeach
                        @foreach ($audit['seo']['standards']['meta_description']['too_long_samples'] as $sample)
                            <div style="display: flex; align-items: center; gap: 10px; background: var(--dash-fill); padding: 7px 12px; border-radius: 4px; border: 1px solid var(--dash-line);">
                                <span class="dash-tag is-cancelled" style="font-size: 9.5px; flex-shrink: 0;">Meta Desc: {{ $sample['length'] }} Chars (&gt;160)</span>
                                <strong style="color: var(--dash-ink); font-size: 12px; flex-shrink: 0;">{{ $sample['code'] ?: $sample['name'] }}</strong>
                                <span style="color: var(--dash-muted); font-size: 11.5px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">&ldquo;{{ $sample['value'] }}&rdquo;</span>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>
    @endif

    <!-- Data Integrity & Relational Anomalies -->
    <div class="dash-card" style="margin-bottom: 28px;">
        <div style="display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid var(--dash-line); padding-bottom: 12px; margin-bottom: 16px;">
            <h3 style="font-size: 15px; font-weight: 700; margin: 0; color: var(--dash-ink); display: flex; align-items: center; gap: 8px;">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width: 18px; height: 18px; color: #38bdf8;"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                Relational Integrity &amp; Collision Checks
            </h3>
            <span class="dash-pill {{ $audit['integrity']['duplicate_slugs']['count'] === 0 && $audit['integrity']['orphan_categories']['count'] === 0 ? 'is-active' : 'is-cancelled' }}">
                {{ $audit['integrity']['duplicate_slugs']['count'] === 0 && $audit['integrity']['orphan_categories']['count'] === 0 ? 'No Anomalies Found' : 'Anomalies Detected' }}
            </span>
        </div>

        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 16px;">
            <!-- Duplicate Slugs -->
            <div style="background: var(--dash-fill); border: 1px solid var(--dash-line); border-radius: 8px; padding: 14px;">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 6px;">
                    <strong style="font-size: 13px; color: var(--dash-ink);">Duplicate URL Slugs</strong>
                    <span class="dash-tag {{ $audit['integrity']['duplicate_slugs']['count'] === 0 ? 'is-primary' : 'is-cancelled' }}">
                        {{ $audit['integrity']['duplicate_slugs']['count'] }} Found
                    </span>
                </div>
                <p style="margin: 0; font-size: 11.5px; color: var(--dash-muted);">
                    {{ $audit['integrity']['duplicate_slugs']['count'] === 0 ? 'Zero collisions detected. All product slugs are uniquely routeable.' : 'Duplicate slugs detected. URLs will conflict when routing.' }}
                </p>
            </div>

            <!-- Duplicate SKUs -->
            <div style="background: var(--dash-fill); border: 1px solid var(--dash-line); border-radius: 8px; padding: 14px;">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 6px;">
                    <strong style="font-size: 13px; color: var(--dash-ink);">Duplicate SKUs</strong>
                    <span class="dash-tag {{ $audit['integrity']['duplicate_skus']['count'] === 0 ? 'is-primary' : 'is-cancelled' }}">
                        {{ $audit['integrity']['duplicate_skus']['count'] }} Found
                    </span>
                </div>
                <p style="margin: 0; font-size: 11.5px; color: var(--dash-muted);">
                    {{ $audit['integrity']['duplicate_skus']['count'] === 0 ? 'Zero duplicate SKUs detected across active inventory.' : 'Multiple products share identical product codes.' }}
                </p>
            </div>

            <!-- Orphan Categories -->
            <div style="background: var(--dash-fill); border: 1px solid var(--dash-line); border-radius: 8px; padding: 14px;">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 6px;">
                    <strong style="font-size: 13px; color: var(--dash-ink);">Orphan Category Names</strong>
                    <span class="dash-tag {{ $audit['integrity']['orphan_categories']['count'] === 0 ? 'is-primary' : 'is-cancelled' }}">
                        {{ $audit['integrity']['orphan_categories']['count'] }} Found
                    </span>
                </div>
                <p style="margin: 0; font-size: 11.5px; color: var(--dash-muted);">
                    {{ $audit['integrity']['orphan_categories']['count'] === 0 ? 'All product categories exist in the verified Category master table.' : 'Products assigned to unlisted category names.' }}
                </p>
            </div>
        </div>
    </div>
    @else
        <!-- Empty state when audit has not yet been executed -->
        <div class="dash-card" style="text-align: center; padding: 56px 24px; margin-bottom: 28px;">
            <div style="width: 56px; height: 56px; border-radius: 50%; background: var(--dash-mark-bg); color: var(--dash-green); display: inline-flex; align-items: center; justify-content: center; margin-bottom: 16px;">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width: 28px; height: 28px;"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
            </div>
            <h3 style="font-size: 18px; font-weight: 700; margin: 0 0 8px; color: var(--dash-ink);">No Catalog Audit Report Available</h3>
            <p style="font-size: 13px; color: var(--dash-muted); max-width: 520px; margin: 0 auto 24px; line-height: 1.5;">
                Audits analyze WebP image standards, SVG icon formats, required PDFs, and relational integrity. Audits run automatically when an Airtable sync completes or on demand using the button below.
            </p>
            <div style="display: flex; justify-content: center; gap: 12px;">
                <form method="post" action="{{ route('dashboard.sync.audit') }}">
                    @csrf
                    <button class="btn primary" type="submit" style="padding: 10px 20px;">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width: 16px; height: 16px; margin-right: 6px; display: inline-block; vertical-align: -2px;"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
                        <span>Run Catalog Audit Now</span>
                    </button>
                </form>
            </div>
        </div>
    @endif
</div>
@endsection

