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
    <p class="dash-lead">Toggle featured to show a project on the home page. Drag cards to set that order.</p>
</div>

@include('dashboard.partials.search', [
    'action' => route('dashboard.projects.index'),
    'search' => $search,
])

<div class="dash-list" @if ($projects->isNotEmpty() && $search === '') data-dash-sort="{{ route('dashboard.projects.reorder') }}" @endif>
    @forelse ($projects as $project)
        <article class="dash-list-card is-sortable" data-id="{{ $project->id }}">
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
            <div class="dash-list-card-copy">
                @include('dashboard.partials.title-link', [
                    'href' => route('dashboard.projects.edit', $project),
                    'label' => $project->title,
                    'view' => $project->publicPath(),
                ])
                @if ($project->summary)
                    <p class="dash-list-sub">{{ \Illuminate\Support\Str::limit($project->summary, 160) }}</p>
                @endif
            </div>
            <div class="dash-list-card-meta">
                <div class="dash-updated">
                    <span class="dash-list-label">Status</span>
                    @include('dashboard.partials.toggle', [
                        'url' => route('dashboard.projects.toggle-status', $project),
                        'on' => $project->isActive(),
                        'label' => $project->status->label(),
                        'onClass' => 'is-active',
                        'offClass' => 'is-inactive',
                    ])
                </div>
                <div class="dash-updated">
                    <span class="dash-list-label">Featured</span>
                    @include('dashboard.partials.toggle', [
                        'url' => route('dashboard.projects.toggle-featured', $project),
                        'on' => $project->featured,
                        'label' => $project->featured ? 'Yes' : 'No',
                        'onClass' => 'is-yes',
                        'offClass' => 'is-inactive',
                    ])
                </div>
                @include('dashboard.partials.updated', ['record' => $project])
            </div>
        </article>
    @empty
        <div class="dash-card dash-empty">{{ $search === '' ? 'No projects yet.' : 'No titles match that search.' }}</div>
    @endforelse
</div>
@endsection
