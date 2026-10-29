@extends('layouts.dashboard')

@section('title', 'Staff')

@section('content')
<div class="dash-head">
    <div class="dash-head-title">
        <h1>Staff</h1>
        <div class="dash-head-actions">
            <span class="dash-pill is-active">{{ $staff->total() }} Staff Members</span>
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

<!-- Staff Airtable Grid -->
<div class="dash-airtable-wrap">
    <table class="dash-airtable-table">
        <thead>
            <tr>
                <th scope="col" class="dash-sticky-col" style="min-width: 220px;">Staff Member</th>
                <th scope="col" style="min-width: 200px;">Email</th>
                <th scope="col" style="width: 110px; text-align: center;">Status</th>
                <th scope="col" style="min-width: 240px;">Assigned Permissions</th>
                <th scope="col" style="min-width: 170px;">Last Updated</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($staff as $member)
                <tr>
                    <!-- 1. Staff Member Name (Sticky Column) -->
                    <td class="dash-sticky-col">
                        @include('dashboard.partials.title-link', [
                            'href' => route('dashboard.staff.edit', $member),
                            'label' => $member->name,
                        ])
                    </td>

                    <!-- 2. Email Address -->
                    <td>
                        <span style="color: var(--dash-ink); font-size: 13px;">{{ $member->email }}</span>
                    </td>

                    <!-- 3. Status Toggle -->
                    <td style="text-align: center;">
                        @include('dashboard.partials.toggle', [
                            'url' => route('dashboard.staff.toggle-status', $member),
                            'on' => $member->isActive(),
                            'label' => $member->status->label(),
                            'onClass' => 'is-active',
                            'offClass' => 'is-inactive',
                        ])
                    </td>

                    <!-- 4. Assigned Permissions -->
                    <td>
                        @php
                            $permissions = $member->contentPermissions;
                        @endphp
                        @if ($permissions->isNotEmpty())
                            <div style="display: flex; flex-wrap: wrap; gap: 4px; max-width: 280px;">
                                @foreach ($permissions->take(3) as $perm)
                                    <span class="dash-tag is-primary" style="font-size: 11px;">{{ $perm->resource?->label() ?? $perm->resource->value }}</span>
                                @endforeach
                                @if ($permissions->count() > 3)
                                    <span class="dash-tag" style="font-size: 11px; color: var(--dash-muted);" title="{{ $permissions->slice(3)->map(fn ($p) => $p->resource?->label() ?? $p->resource->value)->join(', ') }}">+{{ $permissions->count() - 3 }} more</span>
                                @endif
                            </div>
                        @else
                            <span style="color: var(--dash-muted); font-size: 12px;">No permissions</span>
                        @endif
                    </td>

                    <!-- 5. Updated -->
                    <td>
                        @include('dashboard.partials.updated', ['record' => $member])
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="5">
                        <div class="dash-card dash-empty">{{ $search === '' ? 'No staff accounts yet.' : 'No names match that search.' }}</div>
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

<div style="margin-top: 18px;">
    {{ $staff->links('dashboard.partials.pagination') }}
</div>
@endsection
