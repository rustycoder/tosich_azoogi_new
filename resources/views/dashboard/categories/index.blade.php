@extends('layouts.dashboard')

@section('title', 'Categories')

@section('content')
<div class="dash-head">
    <div class="dash-head-title">
        <h1>Categories</h1>
        <div class="dash-head-actions">
            <span class="dash-pill is-active">{{ $categories->total() }} Categories</span>
        </div>
    </div>
    <p class="dash-lead">Browse category hierarchies, parent relationships, featured images, and linked product counts synced from Airtable.</p>
</div>

<!-- Search & Filter Controls Toolbar -->
<form id="catFilterForm" class="dash-toolbar-row" method="get" action="{{ route('dashboard.categories.index') }}" role="search">
    <!-- Search Input Field -->
    <div class="dash-search">
        <label class="visually-hidden" for="dash-search-q">Search categories</label>
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
                placeholder="Search categories by name, Airtable ID, or description..."
                maxlength="100"
                autocomplete="off"
            >
            @if ($search !== '')
                <a class="dash-search-clear" href="{{ route('dashboard.categories.index', array_filter(['parent' => $activeParent, 'per_page' => $perPage !== 50 ? $perPage : null])) }}" title="Clear search" aria-label="Clear search">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="M6 6l12 12M18 6 6 18"/></svg>
                </a>
            @endif
            <button type="submit" class="dash-search-submit">Search</button>
        </div>
    </div>

    <!-- Root Category Filter Dropdown -->
    <div class="dash-select-wrap">
        <select name="parent" class="dash-select" onchange="document.getElementById('catFilterForm').submit()" aria-label="Filter by root category">
            <option value="">All Root Categories</option>
            <option value="root_only" {{ ($activeParent ?? '') === 'root_only' ? 'selected' : '' }}>Root Categories Only</option>
            @foreach ($parentCategories as $pCat)
                <option value="{{ $pCat->airtable_id }}" {{ ($activeParent ?? '') === $pCat->airtable_id || ($activeParent ?? '') === $pCat->name ? 'selected' : '' }}>
                    {{ $pCat->name }}
                </option>
            @endforeach
        </select>
        <svg class="dash-select-chevron" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><polyline points="6 9 12 15 18 9"/></svg>
    </div>

    <!-- Items Per Page Dropdown -->
    <div class="dash-select-wrap">
        <select name="per_page" class="dash-select" onchange="document.getElementById('catFilterForm').submit()" aria-label="Categories per page">
            @foreach ($perPageOptions as $option)
                <option value="{{ $option }}" {{ $perPage === $option ? 'selected' : '' }}>
                    Show {{ $option }}
                </option>
            @endforeach
        </select>
        <svg class="dash-select-chevron" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><polyline points="6 9 12 15 18 9"/></svg>
    </div>
</form>

<!-- Categories Airtable Grid -->
<div class="dash-airtable-wrap">
    <table class="dash-airtable-table">
        <thead>
            <tr>
                <th scope="col" style="width: 70px; text-align: center;">Order</th>
                <th scope="col" class="dash-sticky-col" style="min-width: 220px;">Category Name</th>
                <th scope="col" style="min-width: 170px;">Parent Category</th>
                <th scope="col" style="width: 80px; text-align: center;">Image</th>
                <th scope="col" style="width: 70px; text-align: center;">Icon</th>
                <th scope="col" style="min-width: 240px;">Description</th>
                <th scope="col" style="width: 130px; text-align: center;">Products</th>
                <th scope="col" style="min-width: 150px;">Updated</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($categories as $category)
                @php
                    $img = $category->featuredImageUrl();
                    $icon = $category->iconUrl();
                @endphp
                <tr>
                    <!-- Sort Order (1st Column) -->
                    <td style="text-align: center;">
                        @if ($category->sort_order !== null)
                            <span class="dash-code-badge" style="font-size: 11px; font-weight: 600;">#{{ $category->sort_order }}</span>
                        @endif
                    </td>

                    <!-- Name (Sticky Left) -->
                    <td class="dash-sticky-col">
                        <div style="display: flex; flex-direction: column; gap: 3px;">
                            <strong style="color: var(--dash-ink); font-size: 13.5px;">{{ $category->name }}</strong>
                            <span class="dash-airtable-id">{{ $category->airtable_id }}</span>
                        </div>
                    </td>

                    <!-- Parent Category -->
                    <td>
                        @if (filled($category->parent_name))
                            <span class="dash-tag is-primary" style="font-weight: 600;">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width: 11px; height: 11px; margin-right: 4px;"><path d="M3 7v13a1 1 0 0 0 1 1h16a1 1 0 0 0 1-1V9a1 1 0 0 0-1-1h-8l-2-2H4a1 1 0 0 0-1 1z"/></svg>
                                {{ $category->parent_name }}
                            </span>
                        @else
                            <span class="dash-tag" style="color: var(--dash-muted);">Root Category</span>
                        @endif
                    </td>

                    <!-- Featured Image Preview -->
                    <td style="text-align: center;">
                        @if (filled($img))
                            <div class="dash-preview-thumb" data-popover-img="{{ $img }}" style="width: 40px; height: 40px;" title="Featured category image">
                                <img src="{{ $img }}" alt="{{ $category->name }}" loading="lazy">
                            </div>
                        @endif
                    </td>

                    <!-- Icon Preview -->
                    <td style="text-align: center;">
                        @if (filled($icon))
                            <div class="dash-tech-icon" data-popover-img="{{ $icon }}" style="width: 32px; height: 32px; display: inline-flex;" title="Category icon">
                                <img src="{{ $icon }}" alt="Icon" loading="lazy">
                            </div>
                        @endif
                    </td>

                    <!-- Description -->
                    <td>
                        @if (filled($category->description))
                            <p style="margin: 0; font-size: 12px; color: var(--dash-muted); max-width: 300px; white-space: normal; line-height: 1.4;" title="{{ $category->description }}">
                                {{ \Illuminate\Support\Str::limit($category->description, 100) }}
                            </p>
                        @endif
                    </td>

                    <!-- Products Count -->
                    <td style="text-align: center;">
                        <span class="dash-pill {{ ($category->products_count ?? 0) > 0 ? 'is-active' : 'is-inactive' }}">
                            {{ $category->products_count ?? 0 }} {{ ($category->products_count ?? 0) === 1 ? 'product' : 'products' }}
                        </span>
                    </td>

                    <!-- Updated -->
                    <td>
                        <div class="dash-updated" style="gap: 2px;">
                            <span class="dash-updated-value" style="font-size: 11.5px;">
                                <strong>{{ $category->updater?->name ?? 'Airtable Sync' }}</strong>
                                @if ($category->updated_at)
                                    <span class="dash-updated-sep" aria-hidden="true">·</span>
                                    <time datetime="{{ $category->updated_at->toIso8601String() }}">{{ $category->updated_at->timezone(config('app.timezone'))->format('j M, g:ia') }}</time>
                                @endif
                            </span>
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="8">
                        <div class="dash-card dash-empty">
                            {{ $search === '' ? 'No categories synced yet.' : 'No categories match "' . $search . '".' }}
                        </div>
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

<div style="margin-top: 18px;">
    {{ $categories->links('dashboard.partials.pagination') }}
</div>

<!-- Floating Hover Image Popover Container -->
<div id="dash-global-img-popover" class="dash-img-popover">
    <img id="dash-global-img-popover-src" src="" alt="Enlarged Preview">
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {
    const popover = document.getElementById('dash-global-img-popover');
    const popoverImg = document.getElementById('dash-global-img-popover-src');
    if (!popover || !popoverImg) return;

    let activeTrigger = null;

    document.querySelectorAll('[data-popover-img]').forEach(el => {
        el.addEventListener('mouseenter', () => {
            const src = el.getAttribute('data-popover-img');
            if (!src) return;

            activeTrigger = el;
            popoverImg.src = src;
            popover.classList.toggle('is-tech-icon', el.classList.contains('dash-tech-icon'));
            popover.classList.add('is-visible');

            const rect = el.getBoundingClientRect();
            let top = rect.top + (rect.height / 2);
            let left = rect.right + 12;

            if (left + 280 > window.innerWidth) {
                left = rect.left - 272;
            }
            if (top + 130 > window.innerHeight) {
                top = window.innerHeight - 140;
            }
            if (top - 130 < 10) {
                top = 140;
            }

            popover.style.top = top + 'px';
            popover.style.left = left + 'px';
        });

        el.addEventListener('mouseleave', () => {
            activeTrigger = null;
            popover.classList.remove('is-visible', 'is-tech-icon');
        });
    });

    window.addEventListener('scroll', () => {
        if (activeTrigger) {
            popover.classList.remove('is-visible');
            activeTrigger = null;
        }
    }, { passive: true });
});
</script>
@endpush
@endsection
