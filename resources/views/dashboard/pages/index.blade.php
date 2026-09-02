@extends('layouts.dashboard')

@section('title', 'Pages')

@section('content')
<div class="dash-head">
    <div>
        <h1>Pages</h1>
        <p class="dash-lead">Open a page to edit its seeded fields. New pages cannot be created here.</p>
    </div>
</div>

@include('dashboard.partials.search', [
    'action' => route('dashboard.pages.index'),
    'search' => $search,
])

<div class="dash-list">
    @forelse ($pages as $page)
        <article class="dash-list-card">
            <div class="dash-list-card-copy">
                @include('dashboard.partials.title-link', [
                    'href' => route('dashboard.pages.edit', $page),
                    'label' => \App\PageMeta\Catalog::for($page->slug)->navLabel(),
                    'view' => $page->publicPath(),
                ])
                <p class="dash-list-sub">{{ $page->title }}</p>
            </div>
            <div class="dash-list-card-meta">
                <div class="dash-updated">
                    <span class="dash-list-label">Status</span>
                    @include('dashboard.partials.toggle', [
                        'url' => route('dashboard.pages.toggle-status', $page),
                        'on' => $page->isActive(),
                        'label' => $page->status->label(),
                        'onClass' => 'is-active',
                        'offClass' => 'is-inactive',
                    ])
                </div>
                @include('dashboard.partials.updated', ['record' => $page])
            </div>
        </article>
    @empty
        <div class="dash-card dash-empty">{{ $search === '' ? 'No pages assigned to this account.' : 'No titles match that search.' }}</div>
    @endforelse
</div>
@endsection
