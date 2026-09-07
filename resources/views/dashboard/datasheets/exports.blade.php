@extends('layouts.dashboard')

@section('title', 'Exports')

@section('content')
<div class="dash-head">
    <div class="dash-head-title">
        <h1>Exports</h1>
    </div>
    <p class="dash-lead">Project datasheets generated from product pages, kept for follow-up and analytics.</p>
</div>

@include('dashboard.partials.search', [
    'action' => route('dashboard.datasheets.exports'),
    'search' => $search,
    'placeholder' => 'Search by project, client, or product',
])

<div class="dash-list">
    @forelse ($exports as $export)
        <article class="dash-list-card" data-export-card data-export-title="{{ $export->listingDialogTitle() }}" data-export-sub="{{ $export->listingDialogSubtitle() }}">
            <div class="dash-list-card-main">
                @include('dashboard.partials.thumb', [
                    'src' => $export->listingImageUrl(),
                    'alt' => $export->product_name ?: $export->listingTitle(),
                ])
                <div class="dash-list-card-copy">
                    @include('dashboard.partials.title-link', [
                        'label' => $export->listingTitle(),
                        'view' => route('products.datasheet.show', $export),
                        'info' => true,
                    ])
                    @if ($export->listingDescription() !== '')
                        <p class="dash-list-sub">{{ $export->listingDescription() }}</p>
                    @endif
                </div>
            </div>
            <div class="dash-list-card-meta is-end">
                <div class="dash-updated">
                    <span class="dash-list-label">Exported</span>
                    <span class="dash-updated-value">
                        <strong>{{ filled($export->person_name) ? $export->person_name : '—' }}</strong>
                        @if ($export->created_at)
                            <span class="dash-updated-sep" aria-hidden="true">·</span>
                            <time datetime="{{ $export->created_at->toIso8601String() }}">{{ $export->created_at->timezone(config('app.timezone'))->format('j M Y, g:i A') }}</time>
                        @endif
                    </span>
                </div>
            </div>
            <template data-export-detail>
                <dl class="dash-enquiry-facts">
                    @foreach ($export->listingInfoRows() as $row)
                        <div>
                            <dt>{{ $row['label'] }}</dt>
                            <dd>{{ $row['value'] }}</dd>
                        </div>
                    @endforeach
                </dl>
            </template>
        </article>
    @empty
        <div class="dash-card dash-empty">{{ $search === '' ? 'No datasheet exports yet.' : 'No exports match that search.' }}</div>
    @endforelse
</div>

{{ $exports->links('dashboard.partials.pagination') }}

@include('dashboard.partials.info-dialog')
@endsection
