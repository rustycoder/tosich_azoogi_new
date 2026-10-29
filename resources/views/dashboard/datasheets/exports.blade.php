@extends('layouts.dashboard')

@section('title', 'Datasheet Exports')

@section('content')
<div class="dash-head">
    <div class="dash-head-title">
        <h1>Datasheet Exports</h1>
        <div class="dash-head-actions">
            <span class="dash-pill is-active">{{ $exports->total() }} Exports</span>
        </div>
    </div>
    <p class="dash-lead">Project datasheets generated from product pages, kept for follow-up and analytics.</p>
</div>

@include('dashboard.partials.search', [
    'action' => route('dashboard.datasheets.exports'),
    'search' => $search,
    'placeholder' => 'Search by project, client, or product',
])

<!-- Datasheet Exports Airtable Grid -->
<div class="dash-airtable-wrap">
    <table class="dash-airtable-table">
        <thead>
            <tr>
                <th scope="col" style="width: 70px; text-align: center;">Image</th>
                <th scope="col" class="dash-sticky-col" style="min-width: 200px;">Product Code</th>
                <th scope="col" style="min-width: 170px;">Project Name</th>
                <th scope="col" style="min-width: 150px;">Client Name</th>
                <th scope="col" style="min-width: 130px;">Country</th>
                <th scope="col" style="min-width: 180px;">Exported</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($exports as $export)
                <tr data-export-card data-export-title="{{ $export->listingDialogTitle() }}" data-export-sub="{{ $export->listingDialogSubtitle() }}">
                    <!-- 1. Product Thumbnail -->
                    <td style="text-align: center;">
                        <div class="dash-preview-thumb" data-popover-img="{{ $export->listingImageUrl() }}" style="width: 40px; height: 40px; margin: 0 auto;" title="{{ $export->product_name ?: $export->listingTitle() }}">
                            @include('dashboard.partials.thumb', [
                                'src' => $export->listingImageUrl(),
                                'alt' => $export->product_name ?: $export->listingTitle(),
                            ])
                        </div>
                    </td>

                    <!-- 2. Product Code & Name (Sticky Column) -->
                    <td class="dash-sticky-col">
                        <div style="display: flex; flex-direction: column; gap: 3px;">
                            @include('dashboard.partials.title-link', [
                                'label' => $export->listingTitle(),
                                'view' => route('products.datasheet.show', $export),
                                'info' => true,
                            ])
                            @if ($export->listingDescription() !== '')
                                <p class="dash-list-sub">{{ $export->listingDescription() }}</p>
                            @endif
                        </div>
                    </td>

                    <!-- 3. Project Name -->
                    <td>
                        @if (filled($export->project_name))
                            <span class="dash-tag is-primary" style="font-weight: 600;">{{ $export->project_name }}</span>
                        @else
                            <span style="color: var(--dash-muted); font-size: 12px;">—</span>
                        @endif
                    </td>

                    <!-- 4. Client Name -->
                    <td>
                        <strong style="color: var(--dash-ink); font-size: 13px;">{{ filled($export->person_name) ? $export->person_name : '—' }}</strong>
                    </td>

                    <!-- 5. Country / Region -->
                    <td>
                        @php
                            $country = country_name($export->country);
                        @endphp
                        @if ($country !== '')
                            <span class="dash-tag" style="font-size: 11.5px;">{{ $country }}</span>
                        @else
                            <span style="color: var(--dash-muted); font-size: 12px;">—</span>
                        @endif
                    </td>

                    <!-- 6. Exported Details -->
                    <td>
                        <div class="dash-list-card-meta is-end" style="margin: 0; justify-content: flex-start;">
                            <div class="dash-updated" style="gap: 2px;">
                                <span class="dash-list-label">Exported</span>
                                <span class="dash-updated-value" style="font-size: 11.5px;">
                                    <strong>{{ filled($export->person_name) ? $export->person_name : '—' }}</strong>
                                    @if ($export->created_at)
                                        <span class="dash-updated-sep" aria-hidden="true">·</span>
                                        <time datetime="{{ $export->created_at->toIso8601String() }}">{{ $export->created_at->timezone(config('app.timezone'))->format('j M Y, g:i A') }}</time>
                                    @endif
                                </span>
                            </div>
                        </div>
                    </td>

                    <!-- Template for Info Modal -->
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
                </tr>
            @empty
                <tr>
                    <td colspan="6">
                        <div class="dash-card dash-empty">{{ $search === '' ? 'No datasheet exports yet.' : 'No exports match that search.' }}</div>
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

<div style="margin-top: 18px;">
    {{ $exports->links('dashboard.partials.pagination') }}
</div>

@include('dashboard.partials.info-dialog')
@endsection
