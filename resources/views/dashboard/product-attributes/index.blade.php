@extends('layouts.dashboard')

@section('title', 'Product Attributes')

@section('content')
<div class="dash-head">
    <div class="dash-head-title">
        <h1>Product Attributes</h1>
        <div class="dash-head-actions">
            <span class="dash-pill is-active">{{ $attributes->total() }} Attributes</span>
        </div>
    </div>
    <p class="dash-lead">Manage technical attributes, specification values, icon graphics, and public filter visibility rules synced from Airtable.</p>
</div>

<!-- Search & Filter Controls Toolbar -->
<form id="attrFilterForm" class="dash-toolbar-row" method="get" action="{{ route('dashboard.product-attributes.index') }}" role="search">
    <!-- Search Input Field -->
    <div class="dash-search">
        <label class="visually-hidden" for="dash-search-q">Search product attributes</label>
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
                placeholder="Search by attribute group, value / term, or Airtable ID..."
                maxlength="100"
                autocomplete="off"
            >
            @if ($search !== '')
                <a class="dash-search-clear" href="{{ route('dashboard.product-attributes.index', array_filter(['group' => $activeGroup, 'visibility' => $visibility, 'per_page' => $perPage !== 50 ? $perPage : null])) }}" title="Clear search" aria-label="Clear search">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="M6 6l12 12M18 6 6 18"/></svg>
                </a>
            @endif
            <button type="submit" class="dash-search-submit">Search</button>
        </div>
    </div>

    <!-- Attribute Group Dropdown -->
    <div class="dash-select-wrap">
        <select name="group" class="dash-select" onchange="document.getElementById('attrFilterForm').submit()" aria-label="Filter by attribute group">
            <option value="">All Groups</option>
            @foreach ($groups as $grpName)
                <option value="{{ $grpName }}" {{ ($activeGroup ?? '') === $grpName ? 'selected' : '' }}>
                    {{ $grpName }}
                </option>
            @endforeach
        </select>
        <svg class="dash-select-chevron" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><polyline points="6 9 12 15 18 9"/></svg>
    </div>

    <!-- Filter Visibility Dropdown -->
    <div class="dash-select-wrap">
        <select name="visibility" class="dash-select" onchange="document.getElementById('attrFilterForm').submit()" aria-label="Filter by filter visibility">
            <option value="">All Visibility</option>
            <option value="visible" {{ ($visibility ?? '') === 'visible' ? 'selected' : '' }}>Visible on Filters</option>
            <option value="hidden" {{ ($visibility ?? '') === 'hidden' ? 'selected' : '' }}>Internal Only</option>
        </select>
        <svg class="dash-select-chevron" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><polyline points="6 9 12 15 18 9"/></svg>
    </div>

    <!-- Items Per Page Dropdown -->
    <div class="dash-select-wrap">
        <select name="per_page" class="dash-select" onchange="document.getElementById('attrFilterForm').submit()" aria-label="Attributes per page">
            @foreach ($perPageOptions as $option)
                <option value="{{ $option }}" {{ $perPage === $option ? 'selected' : '' }}>
                    Show {{ $option }}
                </option>
            @endforeach
        </select>
        <svg class="dash-select-chevron" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><polyline points="6 9 12 15 18 9"/></svg>
    </div>
</form>

<!-- Attributes Airtable Grid -->
<div class="dash-airtable-wrap">
    <table class="dash-airtable-table">
        <thead>
            <tr>
                <th scope="col" style="width: 70px; text-align: center;">Order</th>
                <th scope="col" style="min-width: 180px;">Attribute Group</th>
                <th scope="col" class="dash-sticky-col" style="min-width: 220px;">Attribute Value (Term)</th>
                <th scope="col" style="width: 80px; text-align: center;">Icon</th>
                <th scope="col" style="width: 160px; text-align: center;">Filter Visibility</th>
                <th scope="col" style="min-width: 150px;">Updated</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($attributes as $attr)
                @php
                    $icon = $attr->iconUrl();
                    $isVisible = (bool) $attr->is_visible_on_filters;
                @endphp
                <tr>
                    <!-- Sort Order (1st Column) -->
                    <td style="text-align: center;">
                        @if ($attr->sort_order !== null)
                            <span class="dash-code-badge" style="font-size: 11px; font-weight: 600;">#{{ $attr->sort_order }}</span>
                        @else
                            <span style="color: var(--dash-muted); font-size: 11px;">—</span>
                        @endif
                    </td>

                    <!-- Attribute Group Name -->
                    <td>
                        <span class="dash-tag is-primary" style="font-weight: 700; font-size: 12px;">{{ $attr->name }}</span>
                    </td>

                    <!-- Attribute Value (Term) (Sticky Left) -->
                    <td class="dash-sticky-col">
                        <div style="display: flex; flex-direction: column; gap: 3px;">
                            <strong style="color: var(--dash-ink); font-size: 13.5px;">{{ $attr->value ?: '—' }}</strong>
                            <span class="dash-airtable-id">{{ $attr->airtable_id }}</span>
                        </div>
                    </td>

                    <!-- Icon Preview -->
                    <td style="text-align: center;">
                        @if (filled($icon))
                            <div class="dash-tech-icon" data-popover-img="{{ $icon }}" style="width: 32px; height: 32px; display: inline-flex;" title="Attribute icon">
                                <img src="{{ $icon }}" alt="Icon" loading="lazy">
                            </div>
                        @else
                            <span style="color: var(--dash-muted); font-size: 11px;">—</span>
                        @endif
                    </td>

                    <!-- Filter Visibility Badge -->
                    <td style="text-align: center;">
                        @if ($isVisible)
                            <span class="dash-pill is-active" style="font-size: 11px;">Visible on Filters</span>
                        @else
                            <span class="dash-tag" style="color: var(--dash-muted); font-size: 11px;">Internal Only</span>
                        @endif
                    </td>

                    <!-- Updated -->
                    <td>
                        <div class="dash-updated" style="gap: 2px;">
                            <span class="dash-updated-value" style="font-size: 11.5px;">
                                <strong>{{ $attr->updater?->name ?? 'Airtable Sync' }}</strong>
                                @if ($attr->updated_at)
                                    <span class="dash-updated-sep" aria-hidden="true">·</span>
                                    <time datetime="{{ $attr->updated_at->toIso8601String() }}">{{ $attr->updated_at->timezone(config('app.timezone'))->format('j M, g:ia') }}</time>
                                @endif
                            </span>
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="6">
                        <div class="dash-card dash-empty">
                            {{ $search === '' ? 'No product attributes synced yet.' : 'No attributes match "' . $search . '".' }}
                        </div>
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

<div style="margin-top: 18px;">
    {{ $attributes->links('dashboard.partials.pagination') }}
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
            popover.classList.remove('is-visible');
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
