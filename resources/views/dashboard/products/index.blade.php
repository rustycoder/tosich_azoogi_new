@extends('layouts.dashboard')

@section('title', 'Products')

@section('content')
<div class="dash-head">
    <div class="dash-head-title">
        <h1>Products</h1>
        <div class="dash-head-actions">
            <form method="post" action="{{ route('dashboard.products.sync') }}">
                @csrf
                <button class="btn primary" type="submit">Sync</button>
            </form>
        </div>
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
