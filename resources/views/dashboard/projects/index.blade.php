@extends('layouts.dashboard')

@section('title', 'Projects')

@section('content')
<div class="dash-head">
    <div class="dash-head-title">
        <h1>Projects</h1>
        <div class="dash-head-actions">
            <a class="btn primary" href="{{ route('dashboard.projects.create') }}">Add project</a>
        </div>
    </div>
    <p class="dash-lead">Toggle featured to show a project on the home page. Drag rows to set that order.</p>
</div>

<div class="dash-table-wrap">
    <table class="dash-table">
        <thead>
            <tr>
                <th class="dash-drag-col"><span class="visually-hidden">Reorder</span></th>
                <th>Title</th>
                <th>Status</th>
                <th>Featured</th>
                <th>Last updated</th>
                <th></th>
            </tr>
        </thead>
        <tbody @if ($projects->isNotEmpty()) data-dash-sort="{{ route('dashboard.projects.reorder') }}" @endif>
            @forelse ($projects as $project)
                <tr data-id="{{ $project->id }}">
                    <td class="dash-drag">
                        <button type="button" class="dash-drag-handle" aria-label="Drag to set featured order">
                            <svg viewBox="0 0 16 16" fill="currentColor" aria-hidden="true">
                                <circle cx="5" cy="3" r="1.2"/>
                                <circle cx="11" cy="3" r="1.2"/>
                                <circle cx="5" cy="8" r="1.2"/>
                                <circle cx="11" cy="8" r="1.2"/>
                                <circle cx="5" cy="13" r="1.2"/>
                                <circle cx="11" cy="13" r="1.2"/>
                            </svg>
                        </button>
                    </td>
                    <td><a class="dash-row-link" href="{{ route('dashboard.projects.edit', $project) }}">{{ $project->title }}</a></td>
                    <td>
                        @include('dashboard.partials.toggle', [
                            'url' => route('dashboard.projects.toggle-status', $project),
                            'on' => $project->isActive(),
                            'label' => $project->status->label(),
                            'onClass' => 'is-active',
                            'offClass' => 'is-inactive',
                        ])
                    </td>
                    <td>
                        @include('dashboard.partials.toggle', [
                            'url' => route('dashboard.projects.toggle-featured', $project),
                            'on' => $project->featured,
                            'label' => $project->featured ? 'Yes' : 'No',
                            'onClass' => 'is-yes',
                            'offClass' => 'is-inactive',
                        ])
                    </td>
                    <td>
                        @include('dashboard.partials.updated', ['record' => $project])
                    </td>
                    <td>
                        @include('dashboard.partials.icon-actions', [
                            'edit' => route('dashboard.projects.edit', $project),
                            'view' => $project->publicPath(),
                        ])
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="dash-empty">No projects yet.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection
