@extends('layouts.dashboard')

@section('title', 'Dashboard')

@section('content')
<div class="dash-home">
    <div class="dash-head">
        <div>
            <h1>Overview</h1>
            <p class="dash-lead">{{ now()->timezone(config('app.timezone'))->format('l, j F Y') }}</p>
        </div>
    </div>

    @if ($engagementMetrics !== null)
        @include('dashboard.partials.engagement-metrics', ['metrics' => $engagementMetrics])
    @endif

    @if ($enquiryMetrics !== null || $datasheetMetrics !== null)
        <div class="dash-metrics">
            @if ($enquiryMetrics !== null)
                @include('dashboard.partials.origin-metrics', [
                    'title' => 'Enquiries',
                    'metrics' => $enquiryMetrics,
                ])
            @endif
            @if ($datasheetMetrics !== null)
                @include('dashboard.partials.origin-metrics', [
                    'title' => 'Datasheets',
                    'metrics' => $datasheetMetrics,
                ])
            @endif
        </div>
    @endif

    @if ($pendingBoards === [] && $enquiryMetrics === null && $datasheetMetrics === null && $engagementMetrics === null)
        @unless ($canManagePages || $canManageProjects || $canManageProducts || $canManageSections || $isAdmin || $canManageDatasheets || $canManageEnquiries)
            <div class="dash-metric-card">
                <p class="dash-lead">Content tools for this account will be planned later.</p>
            </div>
        @endunless
    @endif

    @if ($pendingBoards !== [])
        <div class="dash-home-boards" data-enquiry-kanban data-pending-only>
            @foreach ($pendingBoards as $board)
                <article class="dash-metric-card" data-kanban-col aria-labelledby="dash-home-board-{{ $board['type']->value }}">
                    <header class="dash-metric-head">
                        <div class="dash-metric-title">
                            <h2 id="dash-home-board-{{ $board['type']->value }}">{{ $board['type']->label() }}</h2>
                            <span class="dash-metric-year" data-kanban-count>{{ $board['enquiries']->count() }}</span>
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
                        <p class="dash-kanban-empty">No pending items.</p>
                    </div>
                </article>
            @endforeach
        </div>

        @include('dashboard.enquiries._dialog')
    @endif
</div>
@endsection
