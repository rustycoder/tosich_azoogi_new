@extends('layouts.dashboard')

@section('title', 'Staff')

@section('content')
<div class="dash-head">
    <div class="dash-head-title">
        <h1>Staff</h1>
        <div class="dash-head-actions">
            <a class="btn primary" href="{{ route('dashboard.staff.create') }}">Add staff</a>
        </div>
    </div>
    <p class="dash-lead">Assign which pages and projects each staff member can edit.</p>
</div>

@include('dashboard.partials.search', [
    'action' => route('dashboard.staff.index'),
    'search' => $search,
    'placeholder' => 'Search by name',
])

<div class="dash-list">
    @forelse ($staff as $member)
        <article class="dash-list-card">
            <div class="dash-list-card-copy">
                @include('dashboard.partials.title-link', [
                    'href' => route('dashboard.staff.edit', $member),
                    'label' => $member->name,
                ])
                <p class="dash-list-sub">{{ $member->email }}</p>
            </div>
            <div class="dash-list-card-meta">
                <div class="dash-updated">
                    <span class="dash-list-label">Status</span>
                    @include('dashboard.partials.toggle', [
                        'url' => route('dashboard.staff.toggle-status', $member),
                        'on' => $member->isActive(),
                        'label' => $member->status->label(),
                        'onClass' => 'is-active',
                        'offClass' => 'is-inactive',
                    ])
                </div>
                @include('dashboard.partials.updated', ['record' => $member])
            </div>
        </article>
    @empty
        <div class="dash-card dash-empty">{{ $search === '' ? 'No staff accounts yet.' : 'No names match that search.' }}</div>
    @endforelse
</div>

{{ $staff->links('dashboard.partials.pagination') }}
@endsection
