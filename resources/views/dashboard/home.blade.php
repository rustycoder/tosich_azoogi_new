@extends('layouts.dashboard')

@section('title', 'Dashboard')

@section('content')
<div class="dash-head">
    <div>
        <h1>Dashboard</h1>
        <p class="dash-lead">Signed in as {{ auth()->user()->name }} ({{ auth()->user()->user_type->label() }}).</p>
    </div>
</div>

@if ($canManagePages || $canManageProjects || $isAdmin)
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
        @if ($canManagePages)
            <a class="dash-home-card" href="{{ route('dashboard.pages.index') }}">
                <span class="dash-home-icon" aria-hidden="true">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M7 3h7l5 5v13a1 1 0 0 1-1 1H7a1 1 0 0 1-1-1V4a1 1 0 0 1 1-1z"/><path d="M14 3v6h6"/></svg>
                </span>
                <h2>Pages</h2>
                <p>Edit site content for the pages you can manage.</p>
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
@else
    <div class="dash-card">
        <p class="dash-lead">Content tools for this account will be planned later.</p>
    </div>
@endif
@endsection
