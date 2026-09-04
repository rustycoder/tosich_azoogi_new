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
