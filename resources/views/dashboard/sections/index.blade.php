@extends('layouts.dashboard')

@section('title', 'Sections')

@section('content')
<div class="dash-head">
    <div>
        <h1>Sections</h1>
        <p class="dash-lead">Edit header and footer copy shown on every public page.</p>
    </div>
</div>

@include('dashboard.partials.search', [
    'action' => route('dashboard.sections.index'),
    'search' => $search,
])

<div class="dash-list">
    @forelse ($pages as $page)
        <article class="dash-list-card">
            <div class="dash-list-card-copy">
                @include('dashboard.partials.title-link', [
                    'href' => route('dashboard.sections.edit', $page),
                    'label' => \App\PageMeta\Catalog::for($page->slug)->navLabel(),
                ])
                @if ($description = \App\PageMeta\Catalog::sectionDescription($page->slug))
                    <p class="dash-list-sub">{{ $description }}</p>
                @endif
            </div>
            <div class="dash-list-card-meta is-end">
                @include('dashboard.partials.updated', ['record' => $page])
            </div>
        </article>
    @empty
        <div class="dash-card dash-empty">{{ $search === '' ? 'No sections assigned to this account.' : 'No titles match that search.' }}</div>
    @endforelse
</div>
@endsection
