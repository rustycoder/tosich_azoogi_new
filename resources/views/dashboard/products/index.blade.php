@extends('layouts.dashboard')

@section('title', 'Products')

@section('content')
<div class="dash-head">
    <div class="dash-head-title">
        <h1>Products</h1>
        <div class="dash-head-actions">
            <form id="dash-product-sync-form" method="post" action="{{ route('dashboard.products.sync') }}" data-stream-url="{{ route('dashboard.products.sync.stream') }}">
                @csrf
                <button id="dash-product-sync-btn" class="btn primary" type="submit">
                    <svg class="dash-sync-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width: 15px; height: 15px; margin-right: 6px; display: inline-block; vertical-align: -2px;"><path d="M21.5 2v6h-6M2.5 22v-6h6M2 11.5a10 10 0 0 1 18.8-4.3M22 12.5a10 10 0 0 1-18.8 4.2"/></svg>
                    <span>Sync</span>
                </button>
            </form>
        </div>
    </div>
</div>

<div id="dash-sync-panel" class="dash-sync-panel" style="display: none;">
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

@if ($latestSync)
    <section class="dash-sync-log is-{{ $latestSync->status->value }}" aria-label="Airtable sync log">
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

@include('dashboard.partials.search', [
    'action' => route('dashboard.products.index'),
    'search' => $search,
    'placeholder' => 'Search by title or SKU',
])

<div class="dash-list">
    @forelse ($products as $product)
        <article class="dash-list-card">
            <div class="dash-list-card-main">
                @include('dashboard.partials.thumb', [
                    'src' => $product->coverUrl(),
                    'alt' => $product->product_name,
                ])
                <div class="dash-list-card-copy">
                    @include('dashboard.partials.title-link', [
                        'label' => $product->product_code ?: $product->product_name,
                        'view' => $product->publicPath(),
                    ])
                    @if ($product->product_code && $product->product_name)
                        <p class="dash-list-sub">{{ $product->product_name }}</p>
                    @endif
                </div>
            </div>
            <div class="dash-list-card-meta">
                <div class="dash-updated">
                    <span class="dash-list-label">Status</span>
                    <span class="dash-pill {{ strtolower((string) $product->status) === 'publish' ? 'is-active' : 'is-inactive' }}">
                        {{ $product->status ? \Illuminate\Support\Str::headline($product->status) : '—' }}
                    </span>
                </div>
                @include('dashboard.partials.updated', ['record' => $product])
            </div>
        </article>
    @empty
        <div class="dash-card dash-empty">{{ $search === '' ? 'No products yet. Run Sync to pull from Airtable.' : 'No titles match that search.' }}</div>
    @endforelse
</div>

{{ $products->links('dashboard.partials.pagination') }}
@endsection
