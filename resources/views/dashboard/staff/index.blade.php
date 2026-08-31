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

<div class="dash-table-wrap">
    <table class="dash-table">
        <thead><tr><th>Name</th><th>Email</th><th>Status</th><th>Last updated</th><th></th></tr></thead>
        <tbody>
            @forelse ($staff as $member)
                <tr>
                    <td><a class="dash-row-link" href="{{ route('dashboard.staff.edit', $member) }}">{{ $member->name }}</a></td>
                    <td>{{ $member->email }}</td>
                    <td>
                        @include('dashboard.partials.toggle', [
                            'url' => route('dashboard.staff.toggle-status', $member),
                            'on' => $member->isActive(),
                            'label' => $member->status->label(),
                            'onClass' => 'is-active',
                            'offClass' => 'is-inactive',
                        ])
                    </td>
                    <td>
                        @include('dashboard.partials.updated', ['record' => $member])
                    </td>
                    <td>
                        @include('dashboard.partials.icon-actions', [
                            'edit' => route('dashboard.staff.edit', $member),
                        ])
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" class="dash-empty">No staff accounts yet.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection
