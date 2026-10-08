@extends('layouts.dashboard')

@section('title', 'Company Context & Policies')

@section('content')
<div class="dash-head">
    <div class="dash-crumb">
        <a href="{{ route('dashboard.home') }}">Dashboard</a>
        <span>/</span>
        <span>AI</span>
        <span>/</span>
        <span>Company Context</span>
    </div>
    <div class="dash-head-title">
        <h1>Company Context &amp; Manufacturing Policies</h1>
        <div class="dash-head-actions">
            <span class="dash-pill is-active">{{ count($contextItems) }} Context Items</span>
            <button type="button" class="btn primary" onclick="openContextModal()" style="display: inline-flex; align-items: center; gap: 6px;">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width: 16px; height: 16px;"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                <span>Add Context Item</span>
            </button>
        </div>
    </div>
    <p class="dash-lead">Manage operational background, Sydney manufacturing capabilities, custom turnaround schedules, shipping logistics, warranty policies, and photometric design support.</p>
</div>

@if (session('status'))
    <div class="dash-card" style="padding: 14px 18px; background: rgba(16, 185, 129, 0.1); border: 1px solid rgba(16, 185, 129, 0.25); color: #10b981; margin-bottom: 24px; display: flex; align-items: center; gap: 10px;">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width: 18px; height: 18px; flex-shrink: 0;"><polyline points="20 6 9 17 4 12"/></svg>
        <span>{{ session('status') }}</span>
    </div>
@endif

@if ($errors->any())
    <div class="dash-card" style="padding: 14px 18px; background: rgba(239, 68, 68, 0.1); border: 1px solid rgba(239, 68, 68, 0.25); color: #ef4444; margin-bottom: 24px;">
        <div style="font-weight: 600; margin-bottom: 4px;">Please correct the errors below:</div>
        <ul style="margin: 0; padding-left: 20px;">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<!-- Context Guidelines Banner -->
<div class="dash-card" style="padding: 16px 20px; margin-bottom: 20px; border-left: 3px solid var(--accent); background: rgba(103, 208, 78, 0.04);">
    <div style="font-size: 13px; font-weight: 700; color: var(--dash-ink, var(--ink)); margin-bottom: 8px; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 8px;">
        <div style="display: flex; align-items: center; gap: 6px;">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width: 15px; height: 15px; color: var(--accent);"><circle cx="12" cy="12" r="10"/><path d="M12 16v-4M12 8h.01"/></svg>
            <span>Company Context Guidelines &amp; Operational Policies</span>
        </div>
        <a href="{{ route('dashboard.ai.prompt') }}" style="font-size: 11.5px; color: var(--accent); text-decoration: none; font-weight: 600; display: inline-flex; align-items: center; gap: 4px;">
            <span>View Full Live System Prompt</span>
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width: 12px; height: 12px;"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg>
        </a>
    </div>
    <ul style="font-size: 12px; color: var(--dash-muted, var(--muted)); margin: 0; padding-left: 18px; line-height: 1.6; display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 4px 18px;">
        <li><strong>Sydney Workshop:</strong> Mention local custom profile cutting and quick 3-5 day turnarounds.</li>
        <li><strong>Logistics:</strong> Detail warehouse dispatch speeds (24-48h for in-stock items).</li>
        <li><strong>Engineering:</strong> Highlight IES/LDT photometric files available for lighting designers.</li>
        <li><strong>Warranty:</strong> Ensure standard 5-year commercial warranty terms are highlighted.</li>
    </ul>
</div>

<!-- Search & Filter Controls Toolbar -->
<div class="dash-toolbar-row" style="margin-bottom: 20px;">
    <!-- Search Input Field -->
    <div class="dash-search">
        <label class="visually-hidden" for="context-search-input">Search context</label>
        <div class="dash-search-field">
            <svg class="dash-search-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                <circle cx="11" cy="11" r="6.5"/>
                <path d="M16.5 16.5 21 21"/>
            </svg>
            <input
                id="context-search-input"
                type="search"
                placeholder="Search facilities, products, turnaround, shipping, or policies..."
                oninput="filterContext()"
                autocomplete="off"
            >
            <button type="button" class="dash-search-clear" id="context-search-clear" onclick="clearContextSearch()" style="display:none;" title="Clear search" aria-label="Clear search">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="M6 6l12 12M18 6 6 18"/></svg>
            </button>
        </div>
    </div>

    <!-- Category Filter Dropdown -->
    <div class="dash-select-wrap">
        <select id="context-category-select" class="dash-select" onchange="filterContext()" aria-label="Filter by category">
            <option value="all">All Categories</option>
            @php
                $categories = array_values(array_unique(array_filter(array_column($contextItems, 'category'))));
            @endphp
            @foreach ($categories as $cat)
                <option value="{{ $cat }}">{{ $cat }}</option>
            @endforeach
        </select>
        <svg class="dash-select-chevron" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><polyline points="6 9 12 15 18 9"/></svg>
    </div>

    <!-- Status Filter Dropdown -->
    <div class="dash-select-wrap">
        <select id="context-status-select" class="dash-select" onchange="filterContext()" aria-label="Filter by status">
            <option value="all">All Statuses</option>
            <option value="active">Active</option>
            <option value="inactive">Inactive</option>
        </select>
        <svg class="dash-select-chevron" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><polyline points="6 9 12 15 18 9"/></svg>
    </div>
</div>

<!-- Company Context Airtable-Style Table -->
<div class="dash-airtable-wrap" style="margin-bottom: 28px;">
    <table class="dash-airtable-table" id="context-table">
        <thead>
            <tr>
                <th scope="col" style="width: 90px; text-align: center;">Status</th>
                <th scope="col" style="width: 170px;">Category</th>
                <th scope="col" class="dash-sticky-col" style="min-width: 220px;">Topic / Title</th>
                <th scope="col" style="min-width: 380px;">Context Details &amp; Operational Policies</th>
                <th scope="col" style="width: 120px; text-align: right;">Actions</th>
            </tr>
        </thead>
        <tbody id="context-table-body">
            @forelse ($contextItems as $item)
                @php
                    $isActive = ($item['is_active'] ?? true) !== false;
                @endphp
                <tr class="context-table-row" data-category="{{ $item['category'] ?? '' }}" data-status="{{ $isActive ? 'active' : 'inactive' }}" data-search="{{ strtolower(($item['category'] ?? '') . ' ' . ($item['title'] ?? '') . ' ' . ($item['content'] ?? '')) }}">
                    <!-- Status (Clickable Toggle just like in projects) -->
                    <td style="text-align: center;">
                        @include('dashboard.partials.toggle', [
                            'url' => route('dashboard.ai.context.toggle-status', $item['id']),
                            'on' => $isActive,
                            'label' => $isActive ? 'Active' : 'Inactive',
                            'onClass' => 'is-active',
                            'offClass' => 'is-inactive',
                        ])
                    </td>

                    <!-- Category -->
                    <td>
                        <span class="dash-tag is-primary" style="font-weight: 600;">
                            {{ $item['category'] ?? 'General' }}
                        </span>
                    </td>

                    <!-- Topic / Title (Sticky Left) -->
                    <td class="dash-sticky-col">
                        <div style="display: flex; flex-direction: column; gap: 3px;">
                            <strong style="color: var(--dash-ink); font-size: 13.5px;">{{ $item['title'] ?? '' }}</strong>
                            <span class="dash-airtable-id">{{ $item['id'] ?? '' }}</span>
                        </div>
                    </td>

                    <!-- Context Details -->
                    <td>
                        <p style="margin: 0; font-size: 12.5px; color: var(--dash-muted, var(--muted)); max-width: 580px; line-height: 1.5; white-space: pre-wrap;">{{ $item['content'] ?? '' }}</p>
                    </td>

                    <!-- Actions (Edit icon & Delete icon) -->
                    <td style="text-align: right;">
                        <div style="display: inline-flex; gap: 6px; align-items: center; justify-content: flex-end;">
                            <button type="button" class="btn" style="padding: 5px 8px; display: inline-flex; align-items: center; justify-content: center;" onclick="editContextItem(@js($item))" title="Edit Context Item" aria-label="Edit Context Item">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width: 14px; height: 14px;"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                            </button>
                            <form method="POST" action="{{ route('dashboard.ai.context.delete', $item['id']) }}" onsubmit="return confirm('Are you sure you want to delete this context item?');" style="display: inline;">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn" style="padding: 5px 8px; color: #ef4444; border-color: rgba(239,68,68,0.3); display: inline-flex; align-items: center; justify-content: center;" title="Delete Context Item" aria-label="Delete Context Item">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width: 14px; height: 14px;"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/><line x1="10" y1="11" x2="10" y2="17"/><line x1="14" y1="11" x2="14" y2="17"/></svg>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
            @empty
                <tr id="context-empty-row">
                    <td colspan="5">
                        <div class="dash-card dash-empty" style="text-align: center; padding: 36px 20px;">
                            No company context items defined yet. Click "Add Context Item" to create one.
                        </div>
                    </td>
                </tr>
            @endforelse
            <tr id="context-no-results-row" style="display: none;">
                <td colspan="5">
                    <div class="dash-card dash-empty" style="text-align: center; padding: 36px 20px;">
                        No context items match your search or filter criteria.
                    </div>
                </td>
            </tr>
        </tbody>
    </table>
</div>

<!-- Company Context Modal (Add & Edit) -->
<div id="context-modal" style="display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.6); backdrop-filter: blur(8px); z-index: 9999; align-items: center; justify-content: center; padding: 20px;">
    <div class="dash-card" style="width: 100%; max-width: 620px; padding: 28px; background: var(--dash-card); border: 1px solid var(--dash-line); color: var(--dash-ink); box-shadow: 0 24px 60px rgba(0,0,0,0.35); border-radius: 14px;">
        <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 18px;">
            <h3 id="context-modal-title" style="margin: 0; font-size: 17px; font-weight: 700; color: var(--dash-ink);">Add Company Context Item</h3>
            <button type="button" class="btn" onclick="closeContextModal()" style="padding: 4px 8px; font-size: 13px;">✕</button>
        </div>

        <form method="POST" action="{{ route('dashboard.ai.context.store') }}" id="context-form">
            @csrf
            <input type="hidden" name="id" id="modal-context-id">

            <div style="display: flex; flex-direction: column; gap: 16px;">
                <div>
                    <label class="dash-label" for="modal-context-category" style="font-size: 12.5px; font-weight: 600;">Category</label>
                    <input type="text" name="category" id="modal-context-category" class="dash-input" placeholder="e.g. Facility & Workshop, Shipping & Logistics, Warranty & Policies" required list="context-category-suggestions">
                    <datalist id="context-category-suggestions">
                        <option value="Facility & Workshop">
                        <option value="Products & Capabilities">
                        <option value="Turnaround & Fabrication">
                        <option value="Shipping & Logistics">
                        <option value="Warranty & Policies">
                        <option value="Photometrics & Support">
                    </datalist>
                </div>

                <div>
                    <label class="dash-label" for="modal-context-title" style="font-size: 12.5px; font-weight: 600;">Context Topic / Title</label>
                    <input type="text" name="title" id="modal-context-title" class="dash-input" placeholder="e.g. Headquarters & Sydney Assembly Facility" required>
                </div>

                <div>
                    <label class="dash-label" for="modal-context-content" style="font-size: 12.5px; font-weight: 600;">Context Details &amp; Operational Policies</label>
                    <textarea name="content" id="modal-context-content" class="dash-textarea" rows="5" placeholder="Detail the facility capabilities, turnaround times, shipping terms, or engineering support." required></textarea>
                </div>

                <div style="display: flex; align-items: center; gap: 10px;">
                    <input type="checkbox" name="is_active" id="modal-context-is-active" value="1" checked style="width: 16px; height: 16px; accent-color: var(--accent);">
                    <label for="modal-context-is-active" style="font-size: 13px; font-weight: 600; color: var(--dash-ink); cursor: pointer;">
                        Active (Compiled into live AI system prompt)
                    </label>
                </div>
            </div>

            <div style="display: flex; justify-content: flex-end; gap: 10px; margin-top: 24px; padding-top: 16px; border-top: 1px solid var(--dash-line);">
                <button type="button" class="btn" onclick="closeContextModal()">Cancel</button>
                <button type="submit" class="btn primary">Save Context Item</button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
function filterContext() {
    const searchInput = document.getElementById('context-search-input');
    const clearBtn = document.getElementById('context-search-clear');
    const query = searchInput?.value.toLowerCase().trim() || '';
    const selectedCategory = document.getElementById('context-category-select')?.value || 'all';
    const selectedStatus = document.getElementById('context-status-select')?.value || 'all';

    if (clearBtn) {
        clearBtn.style.display = query ? 'flex' : 'none';
    }

    const rows = document.querySelectorAll('.context-table-row');
    let visibleCount = 0;

    rows.forEach(row => {
        const cat = row.getAttribute('data-category') || '';
        const status = row.getAttribute('data-status') || '';
        const searchContent = row.getAttribute('data-search') || '';

        const matchesCat = (selectedCategory === 'all' || cat === selectedCategory);
        const matchesStatus = (selectedStatus === 'all' || status === selectedStatus);
        const matchesQuery = (!query || searchContent.includes(query));

        if (matchesCat && matchesStatus && matchesQuery) {
            row.style.display = '';
            visibleCount++;
        } else {
            row.style.display = 'none';
        }
    });

    const noResultsRow = document.getElementById('context-no-results-row');
    if (noResultsRow) {
        noResultsRow.style.display = (visibleCount === 0 && rows.length > 0) ? '' : 'none';
    }
}

function clearContextSearch() {
    const searchInput = document.getElementById('context-search-input');
    if (searchInput) {
        searchInput.value = '';
        filterContext();
        searchInput.focus();
    }
}

function openContextModal() {
    document.getElementById('context-modal-title').textContent = 'Add Company Context Item';
    document.getElementById('modal-context-id').value = '';
    document.getElementById('modal-context-category').value = '';
    document.getElementById('modal-context-title').value = '';
    document.getElementById('modal-context-content').value = '';
    document.getElementById('modal-context-is-active').checked = true;

    const modal = document.getElementById('context-modal');
    modal.style.display = 'flex';
}

function editContextItem(item) {
    document.getElementById('context-modal-title').textContent = 'Edit Company Context Item';
    document.getElementById('modal-context-id').value = item.id || '';
    document.getElementById('modal-context-category').value = item.category || '';
    document.getElementById('modal-context-title').value = item.title || '';
    document.getElementById('modal-context-content').value = item.content || '';
    document.getElementById('modal-context-is-active').checked = (item.is_active !== false);

    const modal = document.getElementById('context-modal');
    modal.style.display = 'flex';
}

function closeContextModal() {
    document.getElementById('context-modal').style.display = 'none';
}

window.addEventListener('keydown', (e) => {
    if (e.key === 'Escape') closeContextModal();
});
</script>
@endpush
@endsection
