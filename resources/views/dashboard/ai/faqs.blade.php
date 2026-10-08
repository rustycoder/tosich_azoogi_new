@extends('layouts.dashboard')

@section('title', 'FAQ Knowledge Base')

@section('content')
<div class="dash-head">
    <div class="dash-crumb">
        <a href="{{ route('dashboard.home') }}">Dashboard</a>
        <span>/</span>
        <span>AI</span>
        <span>/</span>
        <span>FAQ Knowledge Base</span>
    </div>
    <div class="dash-head-title">
        <h1>AI FAQ Knowledge Base</h1>
        <div class="dash-head-actions">
            <span class="dash-pill is-active">{{ count($faqs) }} FAQ Items</span>
            <button type="button" class="btn primary" onclick="openFaqModal()" style="display: inline-flex; align-items: center; gap: 6px;">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width: 16px; height: 16px;"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                <span>+ Add FAQ Item</span>
            </button>
        </div>
    </div>
    <p class="dash-lead">Maintain a verified question-and-answer repository that the AI references to deliver 100% accurate, certified answers to customer inquiries.</p>
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

<!-- Knowledge Retrieval Tips & Guidelines -->
<div class="dash-card" style="padding: 16px 20px; margin-bottom: 20px; border-left: 3px solid var(--accent); background: rgba(103, 208, 78, 0.04);">
    <div style="font-size: 13px; font-weight: 700; color: var(--dash-ink, var(--ink)); margin-bottom: 8px; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 8px;">
        <div style="display: flex; align-items: center; gap: 6px;">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width: 15px; height: 15px; color: var(--accent);"><circle cx="12" cy="12" r="10"/><path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
            <span>FAQ Knowledge Base Tips &amp; Retrieval Guidelines</span>
        </div>
        <a href="{{ route('dashboard.ai.prompt') }}" style="font-size: 11.5px; color: var(--accent); text-decoration: none; font-weight: 600; display: inline-flex; align-items: center; gap: 4px;">
            <span>View Full Live System Prompt</span>
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width: 12px; height: 12px;"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg>
        </a>
    </div>
    <ul style="font-size: 12px; color: var(--dash-muted, var(--muted)); margin: 0; padding-left: 18px; line-height: 1.6; display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 4px 18px;">
        <li><strong>Authentic Phrasing:</strong> Keep questions phrased as real architects, trade clients, and builders ask them.</li>
        <li><strong>Precision Data:</strong> Include warranty durations, IP ratings, and driver compatibility details in answers.</li>
        <li><strong>Active Toggles:</strong> Toggle inactive rather than deleting to temporarily hide seasonal information.</li>
        <li><strong>Live Injection:</strong> Active questions and answers are formatted and injected directly into the system prompt.</li>
    </ul>
</div>

<!-- Search & Filter Controls Toolbar -->
<div class="dash-toolbar-row" style="margin-bottom: 20px;">
    <!-- Search Input Field -->
    <div class="dash-search">
        <label class="visually-hidden" for="faq-search-input">Search FAQs</label>
        <div class="dash-search-field">
            <svg class="dash-search-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                <circle cx="11" cy="11" r="6.5"/>
                <path d="M16.5 16.5 21 21"/>
            </svg>
            <input
                id="faq-search-input"
                type="search"
                placeholder="Search questions, categories, keywords, or verified answers..."
                oninput="filterFaqs()"
                autocomplete="off"
            >
            <button type="button" class="dash-search-clear" id="faq-search-clear" onclick="clearFaqSearch()" style="display:none;" title="Clear search" aria-label="Clear search">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="M6 6l12 12M18 6 6 18"/></svg>
            </button>
        </div>
    </div>

    <!-- Category Filter Dropdown -->
    <div class="dash-select-wrap">
        <select id="faq-category-select" class="dash-select" onchange="filterFaqs()" aria-label="Filter by category">
            <option value="all">All Categories</option>
            @php
                $categories = array_values(array_unique(array_filter(array_column($faqs, 'category'))));
            @endphp
            @foreach ($categories as $cat)
                <option value="{{ $cat }}">{{ $cat }}</option>
            @endforeach
        </select>
        <svg class="dash-select-chevron" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><polyline points="6 9 12 15 18 9"/></svg>
    </div>

    <!-- Status Filter Dropdown -->
    <div class="dash-select-wrap">
        <select id="faq-status-select" class="dash-select" onchange="filterFaqs()" aria-label="Filter by status">
            <option value="all">All Statuses</option>
            <option value="active">Active</option>
            <option value="inactive">Inactive</option>
        </select>
        <svg class="dash-select-chevron" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><polyline points="6 9 12 15 18 9"/></svg>
    </div>
</div>

<!-- FAQ Knowledge Base Airtable-Style Table -->
<div class="dash-airtable-wrap" style="margin-bottom: 28px;">
    <table class="dash-airtable-table" id="faqs-table">
        <thead>
            <tr>
                <th scope="col" style="width: 90px; text-align: center;">Status</th>
                <th scope="col" style="width: 170px;">Category</th>
                <th scope="col" class="dash-sticky-col" style="min-width: 240px;">Question</th>
                <th scope="col" style="min-width: 380px;">Verified Answer</th>
                <th scope="col" style="width: 120px; text-align: right;">Actions</th>
            </tr>
        </thead>
        <tbody id="faqs-table-body">
            @forelse ($faqs as $faq)
                @php
                    $isActive = ($faq['is_active'] ?? true) !== false;
                @endphp
                <tr class="faq-table-row" data-category="{{ $faq['category'] ?? '' }}" data-status="{{ $isActive ? 'active' : 'inactive' }}" data-search="{{ strtolower(($faq['category'] ?? '') . ' ' . ($faq['question'] ?? '') . ' ' . ($faq['answer'] ?? '')) }}">
                    <!-- Status (Clickable Toggle just like in projects) -->
                    <td style="text-align: center;">
                        @include('dashboard.partials.toggle', [
                            'url' => route('dashboard.ai.faqs.toggle-status', $faq['id']),
                            'on' => $isActive,
                            'label' => $isActive ? 'Active' : 'Inactive',
                            'onClass' => 'is-active',
                            'offClass' => 'is-inactive',
                        ])
                    </td>

                    <!-- Category -->
                    <td>
                        <span class="dash-tag is-primary" style="font-weight: 600;">
                            {{ $faq['category'] ?? 'General' }}
                        </span>
                    </td>

                    <!-- Question (Sticky Left) -->
                    <td class="dash-sticky-col">
                        <div style="display: flex; flex-direction: column; gap: 3px;">
                            <strong style="color: var(--dash-ink); font-size: 13.5px;">{{ $faq['question'] ?? '' }}</strong>
                            <span class="dash-airtable-id">{{ $faq['id'] ?? '' }}</span>
                        </div>
                    </td>

                    <!-- Answer -->
                    <td>
                        <p style="margin: 0; font-size: 12.5px; color: var(--dash-muted, var(--muted)); max-width: 580px; line-height: 1.5; white-space: pre-wrap;">{{ $faq['answer'] ?? '' }}</p>
                    </td>

                    <!-- Actions (Edit icon & Delete icon) -->
                    <td style="text-align: right;">
                        <div style="display: inline-flex; gap: 6px; align-items: center; justify-content: flex-end;">
                            <button type="button" class="btn" style="padding: 5px 8px; display: inline-flex; align-items: center; justify-content: center;" onclick="editFaqItem(@js($faq))" title="Edit FAQ Item" aria-label="Edit FAQ Item">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width: 14px; height: 14px;"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                            </button>
                            <form method="POST" action="{{ route('dashboard.ai.faqs.delete', $faq['id']) }}" onsubmit="return confirm('Are you sure you want to delete this FAQ item?');" style="display: inline;">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn" style="padding: 5px 8px; color: #ef4444; border-color: rgba(239,68,68,0.3); display: inline-flex; align-items: center; justify-content: center;" title="Delete FAQ Item" aria-label="Delete FAQ Item">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width: 14px; height: 14px;"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/><line x1="10" y1="11" x2="10" y2="17"/><line x1="14" y1="11" x2="14" y2="17"/></svg>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
            @empty
                <tr id="faqs-empty-row">
                    <td colspan="5">
                        <div class="dash-card dash-empty" style="text-align: center; padding: 36px 20px;">
                            No FAQ items defined yet. Click "+ Add FAQ Item" to create one.
                        </div>
                    </td>
                </tr>
            @endforelse
            <tr id="faqs-no-results-row" style="display: none;">
                <td colspan="5">
                    <div class="dash-card dash-empty" style="text-align: center; padding: 36px 20px;">
                        No FAQ items match your search or filter criteria.
                    </div>
                </td>
            </tr>
        </tbody>
    </table>
</div>

<!-- FAQ Modal (Add & Edit) -->
<div id="faq-modal" style="display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.6); backdrop-filter: blur(8px); z-index: 9999; align-items: center; justify-content: center; padding: 20px;">
    <div class="dash-card" style="width: 100%; max-width: 600px; padding: 28px; background: var(--dash-card); border: 1px solid var(--dash-line); color: var(--dash-ink); box-shadow: 0 24px 60px rgba(0,0,0,0.35); border-radius: 14px;">
        <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 18px;">
            <h3 id="faq-modal-title" style="margin: 0; font-size: 17px; font-weight: 700; color: var(--dash-ink);">Add FAQ Item</h3>
            <button type="button" class="btn" onclick="closeFaqModal()" style="padding: 4px 8px; font-size: 13px;">✕</button>
        </div>

        <form method="POST" action="{{ route('dashboard.ai.faqs.store') }}" id="faq-form">
            @csrf
            <input type="hidden" name="id" id="modal-faq-id">

            <div style="display: flex; flex-direction: column; gap: 16px;">
                <div>
                    <label class="dash-label" for="modal-faq-category" style="font-size: 12.5px; font-weight: 600;">Category</label>
                    <input type="text" name="category" id="modal-faq-category" class="dash-input" placeholder="e.g. Casambi, Linear Profiles, Warranty, Downlights" required list="faq-category-suggestions">
                    <datalist id="faq-category-suggestions">
                        <option value="Smart Controls">
                        <option value="Linear Extrusions">
                        <option value="Architectural Downlights">
                        <option value="Track Lighting">
                        <option value="Warranty & Standards">
                        <option value="Custom Assembly">
                    </datalist>
                </div>

                <div>
                    <label class="dash-label" for="modal-faq-question" style="font-size: 12.5px; font-weight: 600;">Question</label>
                    <input type="text" name="question" id="modal-faq-question" class="dash-input" placeholder="e.g. Do Azoogi linear profiles support Casambi Bluetooth dimming?" required>
                </div>

                <div>
                    <label class="dash-label" for="modal-faq-answer" style="font-size: 12.5px; font-weight: 600;">Verified Answer</label>
                    <textarea name="answer" id="modal-faq-answer" class="dash-textarea" rows="5" placeholder="Provide accurate, detailed response including model codes, wiring constraints, or Sydney facility capabilities." required></textarea>
                </div>

                <div style="display: flex; align-items: center; gap: 10px;">
                    <input type="checkbox" name="is_active" id="modal-faq-is-active" value="1" checked style="width: 16px; height: 16px; accent-color: var(--accent);">
                    <label for="modal-faq-is-active" style="font-size: 13px; font-weight: 600; color: var(--dash-ink); cursor: pointer;">
                        Active (Injected into AI system prompt)
                    </label>
                </div>
            </div>

            <div style="display: flex; justify-content: flex-end; gap: 10px; margin-top: 24px; padding-top: 16px; border-top: 1px solid var(--dash-line);">
                <button type="button" class="btn" onclick="closeFaqModal()">Cancel</button>
                <button type="submit" class="btn primary">Save FAQ Item</button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
function filterFaqs() {
    const searchInput = document.getElementById('faq-search-input');
    const clearBtn = document.getElementById('faq-search-clear');
    const query = searchInput?.value.toLowerCase().trim() || '';
    const selectedCategory = document.getElementById('faq-category-select')?.value || 'all';
    const selectedStatus = document.getElementById('faq-status-select')?.value || 'all';

    if (clearBtn) {
        clearBtn.style.display = query ? 'flex' : 'none';
    }

    const rows = document.querySelectorAll('.faq-table-row');
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

    const noResultsRow = document.getElementById('faqs-no-results-row');
    if (noResultsRow) {
        noResultsRow.style.display = (visibleCount === 0 && rows.length > 0) ? '' : 'none';
    }
}

function clearFaqSearch() {
    const searchInput = document.getElementById('faq-search-input');
    if (searchInput) {
        searchInput.value = '';
        filterFaqs();
        searchInput.focus();
    }
}

function openFaqModal() {
    document.getElementById('faq-modal-title').textContent = 'Add FAQ Item';
    document.getElementById('modal-faq-id').value = '';
    document.getElementById('modal-faq-category').value = '';
    document.getElementById('modal-faq-question').value = '';
    document.getElementById('modal-faq-answer').value = '';
    document.getElementById('modal-faq-is-active').checked = true;

    const modal = document.getElementById('faq-modal');
    modal.style.display = 'flex';
}

function editFaqItem(faq) {
    document.getElementById('faq-modal-title').textContent = 'Edit FAQ Item';
    document.getElementById('modal-faq-id').value = faq.id || '';
    document.getElementById('modal-faq-category').value = faq.category || '';
    document.getElementById('modal-faq-question').value = faq.question || '';
    document.getElementById('modal-faq-answer').value = faq.answer || '';
    document.getElementById('modal-faq-is-active').checked = (faq.is_active !== false);

    const modal = document.getElementById('faq-modal');
    modal.style.display = 'flex';
}

function closeFaqModal() {
    document.getElementById('faq-modal').style.display = 'none';
}

window.addEventListener('keydown', (e) => {
    if (e.key === 'Escape') closeFaqModal();
});
</script>
@endpush
@endsection
