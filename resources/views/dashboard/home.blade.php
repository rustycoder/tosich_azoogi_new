@extends('layouts.dashboard')

@section('title', 'Dashboard')

@section('content')
<div class="dash-home">
    <div class="dash-head">
        <div>
            <h1>Overview</h1>
            <p class="dash-lead">{{ now()->timezone(config('app.timezone'))->format('l, j F Y') }}</p>
        </div>
        @if ($canManageProducts || $isAdmin)
            <div class="dash-head-actions">
                <form id="dash-product-sync-form" method="post" action="{{ route('dashboard.products.sync') }}" data-stream-url="{{ route('dashboard.products.sync.stream') }}">
                    @csrf
                    <button id="dash-product-sync-btn" class="btn primary" type="submit">
                        <svg class="dash-sync-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width: 15px; height: 15px; margin-right: 6px; display: inline-block; vertical-align: -2px;"><path d="M21.5 2v6h-6M2.5 22v-6h6M2 11.5a10 10 0 0 1 18.8-4.3M22 12.5a10 10 0 0 1-18.8 4.2"/></svg>
                        <span>Sync Airtable</span>
                    </button>
                </form>
            </div>
        @endif
    </div>

    <div id="dash-sync-panel" class="dash-sync-panel" style="display: none; margin-bottom: 24px;">
        <div class="dash-sync-header">
            <div class="dash-sync-title-group">
                <h2 class="dash-sync-title">Product Synchronization</h2>
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
            <!-- Log lines will be appended here dynamically -->
        </div>
    </div>

    @if ($latestSync && ($canManageProducts || $isAdmin))
        <section class="dash-sync-log is-{{ $latestSync->status->value }}" aria-label="Airtable sync log" style="margin-bottom: 24px;">
            <div class="dash-sync-log-head">
                <strong>Airtable sync</strong>
                <span class="dash-pill is-{{ $latestSync->status->value === 'ok' ? 'active' : ($latestSync->status->value === 'running' ? 'pending' : 'cancelled') }}">
                    {{ $latestSync->status->value === 'ok' ? 'OK' : ucfirst($latestSync->status->value) }}
                </span>
                <span class="dash-sync-log-meta">
                    @if ($latestSync->started_at)
                        started {{ $latestSync->started_at->timezone(config('app.timezone'))->format('d M Y, g:ia') }}
                    @endif
                    @if ($latestSync->finished_at)
                        · finished {{ $latestSync->finished_at->timezone(config('app.timezone'))->format('g:ia') }}
                    @endif
                    @if ($latestSync->products_count)
                        · {{ $latestSync->products_count }} products
                    @endif
                </span>
            </div>
            @if ($latestSync->error)
                <p class="dash-sync-log-error">{{ $latestSync->error }}</p>
            @endif
            @if (filled($latestSync->log))
                <pre class="dash-sync-log-body">{{ $latestSync->log }}</pre>
            @endif
        </section>
    @endif

    @if ($canManagePages || $canManageProjects || $canManageProducts || $canManageSections || $isAdmin)
        <div class="dash-home-grid">
            @if ($canManageProjects)
                <a class="dash-home-card" href="{{ route('dashboard.projects.index') }}">
                    <span class="dash-home-icon" aria-hidden="true">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><rect x="3" y="4" width="18" height="14" rx="2"/><path d="M3 15l5-4 4 3 4-5 5 6"/></svg>
                    </span>
                    <h2>Projects</h2>
                    <p>Add, update, and archive project case studies.</p>
                </a>
            @endif
            @if ($canManageProducts)
                <a class="dash-home-card" href="{{ route('dashboard.products.index') }}">
                    <span class="dash-home-icon" aria-hidden="true">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M3 8.5 12 4l9 4.5-9 4.5L3 8.5z"/><path d="M3 8.5v7L12 20l9-4.5v-7M12 13v7"/></svg>
                    </span>
                    <h2>Products</h2>
                    <p>Preview products and sync from Airtable.</p>
                </a>
            @endif
            @if ($canManagePages)
                <a class="dash-home-card" href="{{ route('dashboard.pages.index') }}">
                    <span class="dash-home-icon" aria-hidden="true">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M7 3h7l5 5v13a1 1 0 0 1-1 1H7a1 1 0 0 1-1-1V4a1 1 0 0 1 1-1z"/><path d="M14 3v6h6"/></svg>
                    </span>
                    <h2>Pages</h2>
                    <p>Edit site content for the pages you can manage.</p>
                </a>
            @endif
            @if ($canManageSections)
                <a class="dash-home-card" href="{{ route('dashboard.sections.index') }}">
                    <span class="dash-home-icon" aria-hidden="true">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M4 6h16M4 12h16M4 18h10"/></svg>
                    </span>
                    <h2>Sections</h2>
                    <p>Edit header and footer copy shown across the site.</p>
                </a>
            @endif
            @if ($isAdmin)
                <a class="dash-home-card" href="{{ route('dashboard.staff.index') }}">
                    <span class="dash-home-icon" aria-hidden="true">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="9" cy="8" r="3"/><path d="M4 19a5 5 0 0 1 10 0"/><circle cx="17" cy="9" r="2.4"/><path d="M16 19a4.2 4.2 0 0 1 4-3"/></svg>
                    </span>
                    <h2>Staff</h2>
                    <p>Create staff accounts and assign content access.</p>
                </a>
            @endif
        </div>
    @endif

    @if ($engagementMetrics !== null)
        @include('dashboard.partials.engagement-metrics', ['metrics' => $engagementMetrics])
    @endif

    @if ($visitedPageMetrics !== null || $visitedCountryMetrics !== null)
        <div class="dash-metrics">
            @if ($visitedPageMetrics !== null)
                @include('dashboard.partials.rank-metrics', [
                    'title' => 'Most Visited Pages',
                    'icon' => 'pages',
                    'rows' => $visitedPageMetrics,
                ])
            @endif
            @if ($visitedCountryMetrics !== null)
                @include('dashboard.partials.rank-metrics', [
                    'title' => 'Visitors by Country',
                    'icon' => 'globe',
                    'rows' => $visitedCountryMetrics,
                ])
            @endif
        </div>
    @endif

    @if ($topProductMetrics !== null)
        @include('dashboard.partials.top-products', ['rows' => $topProductMetrics])
    @endif

    @if ($enquiryMetrics !== null || $datasheetMetrics !== null)
        <div class="dash-metrics">
            @if ($enquiryMetrics !== null)
                @include('dashboard.partials.origin-metrics', [
                    'title' => 'Enquiries Metrics',
                    'icon' => 'enquiries',
                    'metrics' => $enquiryMetrics,
                ])
            @endif
            @if ($datasheetMetrics !== null)
                @include('dashboard.partials.origin-metrics', [
                    'title' => 'Datasheet Metrics',
                    'icon' => 'datasheets',
                    'metrics' => $datasheetMetrics,
                ])
            @endif
        </div>
    @endif

    @if ($pendingBoards === [] && $enquiryMetrics === null && $datasheetMetrics === null && $engagementMetrics === null && $visitedPageMetrics === null && $visitedCountryMetrics === null && $topProductMetrics === null)
        @unless ($canManagePages || $canManageProjects || $canManageProducts || $canManageSections || $isAdmin || $canManageDatasheets || $canManageEnquiries)
            <div class="dash-metric-card">
                <p class="dash-lead">Content tools for this account will be planned later.</p>
            </div>
        @endunless
    @endif

    @if ($pendingBoards !== [])
        <div class="dash-home-boards" data-enquiry-kanban data-pending-only>
            @foreach ($pendingBoards as $board)
                <article class="dash-metric-card dash-home-board" data-kanban-col aria-labelledby="dash-home-board-{{ $board['type']->value }}">
                    <header class="dash-metric-head">
                        <div class="dash-metric-title">
                            @include('dashboard.partials.share-mark', ['icon' => $board['type']->value])
                            <h2 id="dash-home-board-{{ $board['type']->value }}">{{ $board['type']->menuLabel() }}</h2>
                            <span class="dash-kanban-count" data-kanban-count>{{ $board['enquiries']->count() }}</span>
                        </div>
                        <a
                            class="dash-row-link-icon"
                            href="{{ route('dashboard.enquiries.index', ['type' => $board['type']->menuSlug()]) }}"
                            title="View all"
                            aria-label="View all"
                        >
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M7 17 17 7M8 7h9v9"/></svg>
                        </a>
                    </header>
                    <div class="dash-home-board-body" data-status="{{ $pendingStatus->value }}">
                        @foreach ($board['enquiries'] as $enquiry)
                            @include('dashboard.enquiries._card', [
                                'enquiry' => $enquiry,
                                'status' => $pendingStatus,
                                'draggable' => false,
                            ])
                        @endforeach
                        <p class="dash-kanban-empty">No pending cards.</p>
                    </div>
                </article>
            @endforeach
        </div>

        @include('dashboard.enquiries._dialog')
    @endif
</div>
@endsection
