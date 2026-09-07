@extends('layouts.dashboard')

@section('title', 'Datasheet exports')

@section('content')
<div class="dash-head">
    <div class="dash-head-title">
        <h1>Datasheet exports</h1>
        <div class="dash-head-actions">
            <a class="btn secondary" href="{{ route('dashboard.products.index') }}">Products</a>
        </div>
    </div>
    <p class="dash-lead">Project datasheets generated from product pages, kept for follow-up and analytics.</p>
</div>

@include('dashboard.partials.search', [
    'action' => route('dashboard.products.datasheets'),
    'search' => $search,
    'placeholder' => 'Search by project, client, or product',
])

<div class="dash-list">
    @forelse ($exports as $export)
        <article class="dash-list-card">
            <div class="dash-list-card-main">
                <div class="dash-list-card-copy">
                    @include('dashboard.partials.title-link', [
                        'label' => $export->project_name,
                        'view' => route('products.datasheet.show', $export),
                    ])
                    <p class="dash-list-sub">{{ $export->person_name }}</p>
                </div>
            </div>
            <div class="dash-list-card-meta">
                <div class="dash-updated">
                    <span class="dash-list-label">Product</span>
                    <span>{{ $export->product_code ?: $export->product_name }}</span>
                </div>
                <div class="dash-updated">
                    <span class="dash-list-label">Exported</span>
                    <span>{{ $export->created_at?->timezone(config('app.timezone'))->format('d M Y, g:ia') }}</span>
                </div>
            </div>
        </article>
    @empty
        <div class="dash-card dash-empty">{{ $search === '' ? 'No datasheet exports yet.' : 'No exports match that search.' }}</div>
    @endforelse
</div>

{{ $exports->links('dashboard.partials.pagination') }}
@endsection
