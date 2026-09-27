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
            placeholder="Search pages by name, slug, SEO title, or description..."
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
                <th scope="col" style="min-width: 240px; width: 28%;">Page Name & Path</th>
                <th scope="col" style="min-width: 320px; width: 44%;">SEO & Social Meta</th>
                <th scope="col" style="width: 130px;">Status</th>
                <th scope="col" style="min-width: 170px; width: 18%;">Last Updated</th>
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
                    data-title="{{ mb_strtolower($navLabel . ' ' . $page->title . ' ' . $page->meta_description . ' ' . $page->slug . ' ' . $cat->label()) }}">
                    <td class="dash-td-page">
                        <div class="dash-page-primary">
                            <div class="dash-page-header-line">
                                @include('dashboard.partials.title-link', [
                                    'href' => route('dashboard.pages.edit', $page),
                                    'label' => $navLabel,
                                    'view' => $page->publicPath(),
                                ])
                                <span class="dash-cat-badge {{ $cat->badgeClass() }}">
                                    {{ $cat->shortLabel() }}
                                </span>
                            </div>
                            <a href="{{ $page->publicPath() }}" target="_blank" rel="noopener noreferrer" class="dash-page-path" title="Open live URL {{ $page->publicPath() }}">
                                <span>{{ $page->publicPath() }}</span>
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" class="dash-ext-icon" aria-hidden="true">
                                    <path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6M15 3h6v6M10 14 21 3"/>
                                </svg>
                            </a>
                        </div>
                    </td>
                    <td class="dash-td-seo">
                        <div class="dash-seo-card">
                            @if (!empty($page->og_image))
                                <div class="dash-seo-og-thumb" title="OG Social Share Image: {{ basename($page->og_image) }}">
                                    <img src="{{ media_url($page->og_image) }}" alt="{{ $navLabel }} Social Share" loading="lazy">
                                </div>
                            @else
                                <div class="dash-seo-og-thumb is-empty" title="No social share (OG) image uploaded">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" aria-hidden="true">
                                        <rect x="3" y="3" width="18" height="18" rx="2"/>
                                        <circle cx="8.5" cy="8.5" r="1.5"/>
                                        <polyline points="21 15 16 10 5 21"/>
                                    </svg>
                                    <span>No OG</span>
                                </div>
                            @endif
                            <div class="dash-seo-info">
                                <div class="dash-seo-title-row">
                                    <strong class="dash-seo-title" title="{{ $page->title }}">{{ $page->title }}</strong>
                                </div>
                                @if (filled($page->meta_description))
                                    <p class="dash-seo-desc" title="{{ $page->meta_description }}">{{ $page->meta_description }}</p>
                                @else
                                    <p class="dash-seo-desc is-empty">No meta description configured</p>
                                @endif
                            </div>
                        </div>
                    </td>
                    <td class="dash-td-status">
                        <label class="dash-switch-label" title="Toggle active status">
                            <input 
                                type="checkbox" 
                                class="dash-switch-input" 
                                {{ $page->isActive() ? 'checked' : '' }} 
                                data-dash-toggle-switch="{{ route('dashboard.pages.toggle-status', $page) }}"
                                aria-label="Toggle {{ $navLabel }} status"
                            >
                            <span class="dash-switch-slider" aria-hidden="true"></span>
                            <span class="dash-switch-status {{ $page->isActive() ? 'is-active' : 'is-inactive' }}">
                                {{ $page->status->label() }}
                            </span>
                        </label>
                    </td>
                    <td class="dash-td-updated">
                        @include('dashboard.partials.updated', ['record' => $page])
                    </td>
                </tr>
            @empty
                <tr id="serverEmptyRow">
                    <td colspan="4">
                        <div class="dash-empty">
                            {{ $search === '' ? 'No pages found in this category.' : 'No pages match "' . $search . '".' }}
                        </div>
                    </td>
                </tr>
            @endforelse
            <tr id="clientEmptyRow" style="display: none;">
                <td colspan="4">
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
            if (e.metaKey || e.ctrlKey || e.shiftKey) return;
            e.preventDefault();

            tabs.forEach(t => t.classList.remove('is-active'));
            tab.classList.add('is-active');

            currentCat = tab.getAttribute('data-cat') || 'all';
            if (searchCatInput) {
                searchCatInput.value = currentCat === 'all' ? '' : currentCat;
            }

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
