@extends('layouts.dashboard')

@section('title', 'Pages')

@section('content')
<div class="dash-head">
    <div class="dash-head-title">
        <h1>Pages</h1>
        <div class="dash-head-actions">
            <span class="dash-pill is-active">{{ $categoryCounts['all'] ?? $pages->count() }} Managed Pages</span>
        </div>
    </div>
    <p class="dash-lead">Explore and customize CMS content across marketing pages, technical solution portals, audience landing pages, legal policies, and system templates.</p>
</div>

<!-- Category Filter Tabs -->
<nav class="dash-cat-tabs" aria-label="Page categories" id="pageCatTabs">
    <a href="{{ route('dashboard.pages.index', array_filter(['q' => $search])) }}" 
       class="dash-cat-tab {{ empty($activeCategory) || $activeCategory === 'all' ? 'is-active' : '' }}" 
       data-cat="all">
        <span class="dash-cat-tab-label">All Pages</span>
        <span class="dash-cat-tab-count">{{ $categoryCounts['all'] ?? $pages->count() }}</span>
    </a>
    @foreach ($categories as $cat)
        <a href="{{ route('dashboard.pages.index', array_filter(['category' => $cat->value, 'q' => $search])) }}" 
           class="dash-cat-tab {{ ($activeCategory ?? '') === $cat->value ? 'is-active' : '' }}" 
           data-cat="{{ $cat->value }}">
            <span class="dash-cat-tab-label">{{ $cat->label() }}</span>
            <span class="dash-cat-tab-count">{{ $categoryCounts[$cat->value] ?? 0 }}</span>
        </a>
    @endforeach
</nav>

<!-- Search Toolbar -->
<form class="dash-search" method="get" action="{{ route('dashboard.pages.index') }}" role="search" id="pagesSearchForm">
    @if (!empty($activeCategory) && $activeCategory !== 'all')
        <input type="hidden" name="category" value="{{ $activeCategory }}" id="searchCategoryInput">
    @endif
    <label class="visually-hidden" for="dash-search-q">Search pages by name, slug, SEO title, or category</label>
    <div class="dash-search-field">
        <svg class="dash-search-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
            <circle cx="11" cy="11" r="6.5"/>
            <path d="M16.5 16.5 21 21"/>
        </svg>
        <input
            id="dash-search-q"
            type="search"
            name="q"
            value="{{ $search }}"
            placeholder="Search pages by name, slug, or SEO title..."
            maxlength="80"
            autocomplete="off"
        >
        @if ($search !== '')
            <a class="dash-search-clear" href="{{ route('dashboard.pages.index', array_filter(['category' => $activeCategory])) }}" title="Clear search" aria-label="Clear search">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="M6 6l12 12M18 6 6 18"/></svg>
            </a>
        @endif
        <button type="submit" class="dash-search-submit">Search</button>
    </div>
</form>

<!-- Pages Table -->
<div class="dash-table-wrap">
    <table class="dash-table dash-pages-table" id="pagesTable">
        <thead>
            <tr>
                <th scope="col" style="min-width: 220px;">Page Name & Path</th>
                <th scope="col" style="width: 160px;">Category</th>
                <th scope="col" style="min-width: 240px;">SEO Title</th>
                <th scope="col" style="width: 120px;">Status</th>
                <th scope="col" style="width: 200px;">Last Updated</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($pages as $page)
                @php
                    $def = \App\PageMeta\Catalog::for($page->slug);
                    $cat = \App\PageMeta\Catalog::categoryForSlug($page->slug);
                    $navLabel = $def->navLabel();
                @endphp
                <tr class="dash-page-row" 
                    data-cat="{{ $cat->value }}" 
                    data-title="{{ mb_strtolower($navLabel . ' ' . $page->title . ' ' . $page->slug . ' ' . $cat->label()) }}">
                    <td class="dash-td-page">
                        <div class="dash-page-primary">
                            @include('dashboard.partials.title-link', [
                                'href' => route('dashboard.pages.edit', $page),
                                'label' => $navLabel,
                                'view' => $page->publicPath(),
                            ])
                            <a href="{{ $page->publicPath() }}" target="_blank" rel="noopener noreferrer" class="dash-page-path" title="Open live URL {{ $page->publicPath() }}">
                                <span>{{ $page->publicPath() }}</span>
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" class="dash-ext-icon" aria-hidden="true">
                                    <path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6M15 3h6v6M10 14 21 3"/>
                                </svg>
                            </a>
                        </div>
                    </td>
                    <td>
                        <span class="dash-cat-badge {{ $cat->badgeClass() }}">
                            {{ $cat->shortLabel() }}
                        </span>
                    </td>
                    <td class="dash-td-seo">
                        <span class="dash-seo-title" title="{{ $page->title }}">{{ $page->title }}</span>
                    </td>
                    <td>
                        @include('dashboard.partials.toggle', [
                            'url' => route('dashboard.pages.toggle-status', $page),
                            'on' => $page->isActive(),
                            'label' => $page->status->label(),
                            'onClass' => 'is-active',
                            'offClass' => 'is-inactive',
                        ])
                    </td>
                    <td>
                        @include('dashboard.partials.updated', ['record' => $page])
                    </td>
                </tr>
            @empty
                <tr id="serverEmptyRow">
                    <td colspan="5">
                        <div class="dash-empty">
                            {{ $search === '' ? 'No pages found in this category.' : 'No pages match "' . $search . '".' }}
                        </div>
                    </td>
                </tr>
            @endforelse
            <tr id="clientEmptyRow" style="display: none;">
                <td colspan="5">
                    <div class="dash-empty">No pages match your filter or search query.</div>
                </td>
            </tr>
        </tbody>
    </table>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {
    const tabs = document.querySelectorAll('#pageCatTabs .dash-cat-tab');
    const table = document.getElementById('pagesTable');
    if (!table) return;

    const rows = table.querySelectorAll('tbody tr.dash-page-row');
    const clientEmpty = document.getElementById('clientEmptyRow');
    const searchInput = document.getElementById('dash-search-q');
    const searchCatInput = document.getElementById('searchCategoryInput');

    let currentCat = @json($activeCategory ?? 'all') || 'all';

    const filterRows = () => {
        const query = (searchInput ? searchInput.value : '').toLowerCase().trim();
        let visibleCount = 0;

        rows.forEach(row => {
            const cat = row.getAttribute('data-cat');
            const title = row.getAttribute('data-title') || '';
            const matchesCat = (currentCat === 'all' || cat === currentCat);
            const matchesQuery = query === '' || title.includes(query);

            if (matchesCat && matchesQuery) {
                row.style.display = '';
                visibleCount++;
            } else {
                row.style.display = 'none';
            }
        });

        if (clientEmpty) {
            clientEmpty.style.display = (visibleCount === 0 && rows.length > 0) ? '' : 'none';
        }
    };

    tabs.forEach(tab => {
        tab.addEventListener('click', (e) => {
            // When user clicks with ctrl/cmd or middle click, allow default link
            if (e.metaKey || e.ctrlKey || e.shiftKey) return;
            e.preventDefault();

            tabs.forEach(t => t.classList.remove('is-active'));
            tab.classList.add('is-active');

            currentCat = tab.getAttribute('data-cat') || 'all';
            if (searchCatInput) {
                searchCatInput.value = currentCat === 'all' ? '' : currentCat;
            }

            // Sync URL without reload
            const url = new URL(window.location);
            if (currentCat === 'all') {
                url.searchParams.delete('category');
            } else {
                url.searchParams.set('category', currentCat);
            }
            window.history.pushState({}, '', url);

            filterRows();
        });
    });

    if (searchInput) {
        searchInput.addEventListener('input', filterRows);
    }
});
</script>
@endpush
@endsection
