@extends('layouts.dashboard')

@section('title', 'Projects')

@section('content')
<div class="dash-head">
    <div class="dash-head-title">
        <h1>Projects</h1>
        <div class="dash-head-actions">
            <span class="dash-pill is-active">{{ $projects->total() }} {{ $projects->total() === 1 ? 'Project' : 'Projects' }}</span>
            <a class="btn primary" href="{{ route('dashboard.projects.create') }}">Add project</a>
        </div>
    </div>
    <p class="dash-lead">Browse case studies, manage showcase galleries, and drag rows to arrange featured order.</p>
</div>

<!-- Search & Filter Controls Toolbar -->
<form id="projectFilterForm" class="dash-toolbar-row" method="get" action="{{ route('dashboard.projects.index') }}" role="search">
    <!-- Search Input Field -->
    <div class="dash-search">
        <label class="visually-hidden" for="dash-search-q">Search projects</label>
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
                placeholder="Search projects by title, tag, type, location, or summary..."
                maxlength="100"
                autocomplete="off"
            >
            @if ($search !== '')
                <a class="dash-search-clear" href="{{ route('dashboard.projects.index', array_filter(['status' => $activeStatus, 'featured' => $activeFeatured, 'per_page' => $perPage !== 15 ? $perPage : null])) }}" title="Clear search" aria-label="Clear search">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="M6 6l12 12M18 6 6 18"/></svg>
                </a>
            @endif
            <button type="submit" class="dash-search-submit">Search</button>
        </div>
    </div>

    <!-- Status Filter Dropdown -->
    <div class="dash-select-wrap">
        <select name="status" class="dash-select" onchange="document.getElementById('projectFilterForm').submit()" aria-label="Filter by status">
            <option value="">All Statuses</option>
            <option value="active" {{ ($activeStatus ?? '') === 'active' ? 'selected' : '' }}>Active</option>
            <option value="inactive" {{ ($activeStatus ?? '') === 'inactive' ? 'selected' : '' }}>Inactive</option>
        </select>
        <svg class="dash-select-chevron" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><polyline points="6 9 12 15 18 9"/></svg>
    </div>

    <!-- Featured Filter Dropdown -->
    <div class="dash-select-wrap">
        <select name="featured" class="dash-select" onchange="document.getElementById('projectFilterForm').submit()" aria-label="Filter by featured status">
            <option value="">All Projects</option>
            <option value="featured" {{ ($activeFeatured ?? '') === 'featured' ? 'selected' : '' }}>Featured Only</option>
            <option value="not_featured" {{ ($activeFeatured ?? '') === 'not_featured' ? 'selected' : '' }}>Not Featured</option>
        </select>
        <svg class="dash-select-chevron" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><polyline points="6 9 12 15 18 9"/></svg>
    </div>

    <!-- Items Per Page Dropdown -->
    <div class="dash-select-wrap">
        <select name="per_page" class="dash-select" onchange="document.getElementById('projectFilterForm').submit()" aria-label="Projects per page">
            @foreach ($perPageOptions as $option)
                <option value="{{ $option }}" {{ $perPage === $option ? 'selected' : '' }}>
                    Show {{ $option }}
                </option>
            @endforeach
        </select>
        <svg class="dash-select-chevron" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><polyline points="6 9 12 15 18 9"/></svg>
    </div>
</form>

<!-- Airtable-Style Projects Table -->
<div class="dash-airtable-wrap">
    <table class="dash-airtable-table">
        <thead>
            <tr>
                <th scope="col" style="width: 50px; text-align: center;">Order</th>
                <th scope="col" style="width: 70px; text-align: center;">Cover</th>
                <th scope="col" class="dash-sticky-col" style="min-width: 220px;">Title</th>
                <th scope="col" style="width: 100px; text-align: center;">Status</th>
                <th scope="col" style="width: 100px; text-align: center;">Featured</th>
                <th scope="col" style="min-width: 150px;">Tags</th>
                <th scope="col" style="min-width: 120px;">Type</th>
                <th scope="col" style="min-width: 130px;">Location</th>
                <th scope="col" style="min-width: 110px;">Completed</th>
                <th scope="col" style="min-width: 150px;">Gallery</th>
                <th scope="col" style="min-width: 130px;">Slug</th>
                <th scope="col" style="min-width: 220px;">Summary</th>
                <th scope="col" style="min-width: 220px;">Description</th>
                <th scope="col" style="min-width: 140px;">Updated</th>
                <th scope="col" style="width: 80px; text-align: center;">Actions</th>
            </tr>
        </thead>
        <tbody @if ($projects->isNotEmpty() && $search === '' && $activeStatus === null && $activeFeatured === null && ! $projects->hasPages()) data-dash-sort="{{ route('dashboard.projects.reorder') }}" @endif>
            @forelse ($projects as $project)
                @php
                    $coverUrl = $project->coverUrl();
                    $galleryImages = is_array($project->gallery) ? $project->gallery : [];
                    $projectTags = $project->tags();
                @endphp
                <tr class="is-sortable" data-id="{{ $project->id }}">
                    <!-- 1. Order & Drag Handle -->
                    <td style="text-align: center;">
                        <button type="button" class="dash-drag-handle" aria-label="Drag to set featured order" title="Drag to reorder" style="margin: 0 auto;">
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

                    <!-- 2. Cover Image -->
                    <td style="text-align: center;">
                        @if (filled($project->cover))
                            <div class="dash-preview-thumb" data-popover-img="{{ $coverUrl }}" style="width: 44px; height: 36px; border-radius: 4px; overflow: hidden; display: inline-block;" title="{{ $project->coverAlt() }}">
                                <img src="{{ $coverUrl }}" alt="{{ $project->coverAlt() }}" loading="lazy" style="width: 100%; height: 100%; object-fit: cover;">
                            </div>
                        @else
                            <span style="color: var(--dash-muted); font-size: 11px;">—</span>
                        @endif
                    </td>

                    <!-- 3. Title (Sticky Column) -->
                    <td class="dash-sticky-col">
                        <a href="{{ route('dashboard.projects.edit', $project) }}" class="dash-product-title">
                            <strong>{{ $project->title }}</strong>
                        </a>
                    </td>

                    <!-- 4. Status Toggle -->
                    <td style="text-align: center;">
                        @include('dashboard.partials.toggle', [
                            'url' => route('dashboard.projects.toggle-status', $project),
                            'on' => $project->isActive(),
                            'label' => $project->status->label(),
                            'onClass' => 'is-active',
                            'offClass' => 'is-inactive',
                        ])
                    </td>

                    <!-- 5. Featured Toggle -->
                    <td style="text-align: center;">
                        @include('dashboard.partials.toggle', [
                            'url' => route('dashboard.projects.toggle-featured', $project),
                            'on' => $project->featured,
                            'label' => $project->featured ? 'Yes' : 'No',
                            'onClass' => 'is-yes',
                            'offClass' => 'is-inactive',
                        ])
                    </td>

                    <!-- 6. Tags (Supports Multiple Badges) -->
                    <td>
                        @if (!empty($projectTags))
                            <div style="display: flex; flex-wrap: wrap; gap: 4px;">
                                @foreach ($projectTags as $tagItem)
                                    <span class="dash-tag is-primary" style="font-weight: 600;">{{ $tagItem }}</span>
                                @endforeach
                            </div>
                        @else
                            <span style="color: var(--dash-muted); font-size: 12px;">—</span>
                        @endif
                    </td>

                    <!-- 7. Type -->
                    <td>
                        @if (filled($project->type))
                            <span class="dash-tag" style="color: var(--dash-muted); font-size: 11px;">{{ $project->type }}</span>
                        @else
                            <span style="color: var(--dash-muted); font-size: 12px;">—</span>
                        @endif
                    </td>

                    <!-- 8. Location -->
                    <td>
                        @if (filled($project->location))
                            <span style="display: inline-flex; align-items: center; gap: 4px; font-size: 12.5px; color: var(--dash-ink);">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" style="width: 13px; height: 13px; color: var(--dash-muted); flex-shrink: 0;" aria-hidden="true">
                                    <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/>
                                    <circle cx="12" cy="10" r="3"/>
                                </svg>
                                {{ $project->location }}
                            </span>
                        @else
                            <span style="color: var(--dash-muted); font-size: 12px;">—</span>
                        @endif
                    </td>

                    <!-- 9. Completed -->
                    <td>
                        @if (filled($project->completed))
                            <span style="font-size: 12px; color: var(--dash-ink);">{{ $project->completed }}</span>
                        @else
                            <span style="color: var(--dash-muted); font-size: 12px;">—</span>
                        @endif
                    </td>

                    <!-- 10. Gallery Images -->
                    <td>
                        @if (!empty($galleryImages))
                            <div class="dash-gallery-stack">
                                @foreach ($galleryImages as $index => $gItem)
                                    @php
                                        $gUrl = media_url($gItem);
                                    @endphp
                                    <div class="dash-gallery-item" data-popover-img="{{ $gUrl }}" title="{{ $project->galleryAlt($index, $loop->iteration) }}">
                                        <img src="{{ $gUrl }}" alt="{{ $project->galleryAlt($index, $loop->iteration) }}" loading="lazy">
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <span style="color: var(--dash-muted); font-size: 12px;">—</span>
                        @endif
                    </td>

                    <!-- 11. Slug -->
                    <td>
                        <span class="dash-airtable-id">{{ $project->slug }}</span>
                    </td>

                    <!-- 12. Summary -->
                    <td>
                        @if (filled($project->summary))
                            <p style="margin: 0; font-size: 12px; color: var(--dash-muted); max-width: 260px; white-space: normal; line-height: 1.4;" title="{{ $project->summary }}">
                                {{ \Illuminate\Support\Str::limit($project->summary, 80) }}
                            </p>
                        @else
                            <span style="color: var(--dash-muted); font-size: 12px;">—</span>
                        @endif
                    </td>

                    <!-- 13. Description -->
                    <td>
                        @if (filled($project->description))
                            <p style="margin: 0; font-size: 12px; color: var(--dash-muted); max-width: 260px; white-space: normal; line-height: 1.4;" title="{{ strip_tags($project->description) }}">
                                {{ \Illuminate\Support\Str::limit(strip_tags($project->description), 80) }}
                            </p>
                        @else
                            <span style="color: var(--dash-muted); font-size: 12px;">—</span>
                        @endif
                    </td>

                    <!-- 14. Updated -->
                    <td>
                        <div class="dash-updated" style="gap: 2px;">
                            <span class="dash-updated-value" style="font-size: 11.5px;">
                                <strong>{{ $project->updater?->name ?? 'System' }}</strong>
                                @if ($project->updated_at)
                                    <span class="dash-updated-sep" aria-hidden="true">·</span>
                                    <time datetime="{{ $project->updated_at->toIso8601String() }}">{{ $project->updated_at->timezone(config('app.timezone'))->format('j M, g:ia') }}</time>
                                @endif
                            </span>
                        </div>
                    </td>

                    <!-- 15. Actions -->
                    <td style="text-align: center;">
                        <div style="display: inline-flex; align-items: center; gap: 6px;">
                            <a href="{{ route('dashboard.projects.edit', $project) }}" class="btn is-sm" style="padding: 4px 8px; font-size: 11px;" title="Edit Project">
                                Edit
                            </a>
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="15">
                        <div class="dash-card dash-empty">
                            {{ $search === '' ? 'No projects yet.' : 'No projects match "' . $search . '".' }}
                        </div>
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

<div style="margin-top: 18px;">
    {{ $projects->links('dashboard.partials.pagination') }}
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
