@extends('layouts.dashboard')

@section('title', 'Dashboard')

@section('content')
<div class="dash-head">
    <div>
        <h1>Dashboard</h1>
        <p class="dash-lead">Signed in as {{ auth()->user()->name }} ({{ auth()->user()->user_type->label() }}).</p>
    </div>
</div>

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
@elseif ($pendingBoards === [])
    <div class="dash-card">
        <p class="dash-lead">Content tools for this account will be planned later.</p>
    </div>
@endif

@if ($pendingBoards !== [])
    <div class="dash-home-boards" data-enquiry-kanban data-pending-only>
        @foreach ($pendingBoards as $board)
            <section class="dash-kanban-col is-pending" data-kanban-col aria-labelledby="dash-home-board-{{ $board['type']->value }}">
                <header class="dash-kanban-col-head">
                    <h2 id="dash-home-board-{{ $board['type']->value }}">{{ $board['type']->menuLabel() }}</h2>
                    <span data-kanban-count>{{ $board['enquiries']->count() }}</span>
                    <a
                        class="dash-row-link-icon"
                        href="{{ route('dashboard.enquiries.index', ['type' => $board['type']->menuSlug()]) }}"
                        title="View all"
                        aria-label="View all"
                    >
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M7 17 17 7M8 7h9v9"/></svg>
                    </a>
                </header>
                <div class="dash-kanban-col-body" data-status="{{ $pendingStatus->value }}">
                    @foreach ($board['enquiries'] as $enquiry)
                        @include('dashboard.enquiries._card', [
                            'enquiry' => $enquiry,
                            'status' => $pendingStatus,
                            'draggable' => false,
                        ])
                    @endforeach
                    <p class="dash-kanban-empty">No pending cards.</p>
                </div>
            </section>
        @endforeach
    </div>

    @include('dashboard.enquiries._dialog')
@endif
@endsection
