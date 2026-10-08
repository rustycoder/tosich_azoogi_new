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
            <button type="button" class="btn primary" onclick="openContextModal()" style="display: inline-flex; align-items: center; gap: 6px;">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width: 16px; height: 16px;"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                <span>+ Add Context Item</span>
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

<div style="display: grid; grid-template-columns: minmax(0, 1.4fr) minmax(320px, 1fr); gap: 24px; align-items: start;">
    
    <!-- Left Column: Context Items List & Live Search -->
    <div style="display: flex; flex-direction: column; gap: 20px;">
        
        <!-- Search and Category Filter Bar -->
        <div class="dash-card" style="padding: 16px 20px;">
            <div style="display: flex; gap: 12px; align-items: center; flex-wrap: wrap;">
                <div style="flex: 1; min-width: 220px; position: relative;">
                    <input type="text" id="context-search-input" class="dash-input" placeholder="Search facilities, products, turnaround, or policies..." oninput="filterContext()" style="padding-left: 36px;">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="position: absolute; left: 12px; top: 50%; transform: translateY(-50%); width: 15px; height: 15px; color: var(--dash-muted, var(--muted));"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                </div>
                <div style="display: flex; gap: 6px; align-items: center; flex-wrap: wrap;">
                    <button type="button" class="btn is-active context-cat-btn" data-cat="all" onclick="filterContextCategory('all', this)" style="font-size: 11.5px; padding: 6px 12px;">All ({{ count($contextItems) }})</button>
                    @php
                        $categories = array_values(array_unique(array_filter(array_column($contextItems, 'category'))));
                    @endphp
                    @foreach ($categories as $cat)
                        <button type="button" class="btn context-cat-btn" data-cat="{{ $cat }}" onclick="filterContextCategory('{{ $cat }}', this)" style="font-size: 11.5px; padding: 6px 12px;">{{ $cat }}</button>
                    @endforeach
                </div>
            </div>
        </div>

        <!-- Context Cards Container -->
        <div id="context-items-list" style="display: flex; flex-direction: column; gap: 14px;">
            @forelse ($contextItems as $item)
                <div class="dash-card context-card-item" data-category="{{ $item['category'] ?? '' }}" data-search="{{ strtolower(($item['category'] ?? '') . ' ' . ($item['title'] ?? '') . ' ' . ($item['content'] ?? '')) }}" style="padding: 20px; transition: transform 0.2s ease, border-color 0.2s ease;">
                    <div style="display: flex; align-items: flex-start; justify-content: space-between; gap: 12px; margin-bottom: 8px;">
                        <div style="display: flex; align-items: center; gap: 8px; flex-wrap: wrap;">
                            <span class="dash-pill is-active" style="font-size: 10.5px; text-transform: uppercase; letter-spacing: 0.04em;">{{ $item['category'] ?? 'General' }}</span>
                            @if (($item['is_active'] ?? true) === false)
                                <span class="dash-pill" style="font-size: 10px; background: rgba(239, 68, 68, 0.15); color: #ef4444; border-color: rgba(239,68,68,0.3);">Inactive</span>
                            @endif
                        </div>
                        <div style="display: flex; gap: 6px; flex-shrink: 0;">
                            <button type="button" class="btn" style="font-size: 11.5px; padding: 4px 10px;" onclick="editContextItem(@js($item))">Edit</button>
                            <form method="POST" action="{{ route('dashboard.ai.context.delete', $item['id']) }}" onsubmit="return confirm('Are you sure you want to delete this context item?');" style="display: inline;">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn" style="font-size: 11.5px; padding: 4px 8px; color: #ef4444;" title="Delete">✕</button>
                            </form>
                        </div>
                    </div>
                    <div style="font-size: 14.5px; font-weight: 700; color: var(--dash-ink, var(--ink)); margin-bottom: 8px; line-height: 1.35;">
                        {{ $item['title'] ?? '' }}
                    </div>
                    <div style="font-size: 13px; color: var(--dash-muted, var(--muted)); line-height: 1.55; white-space: pre-wrap;">{{ $item['content'] ?? '' }}</div>
                </div>
            @empty
                <div class="dash-card" style="padding: 40px; text-align: center; color: var(--dash-muted, var(--muted));">
                    <div style="font-size: 32px; margin-bottom: 10px;">🏢</div>
                    <div style="font-size: 15px; font-weight: 600; color: var(--dash-ink, var(--ink)); margin-bottom: 6px;">No Company Context Items Defined</div>
                    <div style="font-size: 12.5px; margin-bottom: 16px;">Add company facilities, manufacturing timelines, or warranty policies to ground the AI assistant.</div>
                    <button type="button" class="btn primary" onclick="openContextModal()">+ Add First Context Item</button>
                </div>
            @endforelse
        </div>

    </div>

    <!-- Right Column: Live Prompt Inspector & Context Guidelines -->
    <div style="position: sticky; top: 24px; display: flex; flex-direction: column; gap: 20px;">
        
        <!-- Live System Prompt Inspector -->
        <div class="dash-card" style="padding: 20px; border-color: rgba(103, 208, 78, 0.3);">
            <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 12px;">
                <div style="font-size: 12px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; color: var(--accent);">
                    Compiled System Prompt Preview
                </div>
                <span class="dash-pill is-active" style="font-size: 10px;">Live Injection</span>
            </div>
            <p style="font-size: 11.5px; color: var(--dash-muted, var(--muted)); margin-top: 0; margin-bottom: 10px;">
                Active context items are dynamically compiled and injected into the AI company background.
            </p>
            <div style="background: rgba(15, 15, 15, 0.95); border: 1px solid var(--line); border-radius: 8px; padding: 12px; max-height: 380px; overflow-y: auto; font-family: monospace; font-size: 11px; line-height: 1.5; color: #a3e635; white-space: pre-wrap; word-break: break-word;">{{ $compiledPrompt }}</div>
        </div>

        <!-- Context Guidance Tips -->
        <div class="dash-card" style="padding: 18px;">
            <div style="font-size: 13px; font-weight: 700; color: var(--dash-ink, var(--ink)); margin-bottom: 8px; display: flex; align-items: center; gap: 6px;">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width: 15px; height: 15px; color: var(--accent);"><circle cx="12" cy="12" r="10"/><path d="M12 16v-4M12 8h.01"/></svg>
                <span>Context Guidelines</span>
            </div>
            <ul style="font-size: 11.5px; color: var(--dash-muted, var(--muted)); margin: 0; padding-left: 18px; line-height: 1.6;">
                <li><strong>Sydney Workshop:</strong> Mention local custom profile cutting and quick 3-5 day turnarounds.</li>
                <li><strong>Logistics:</strong> Detail warehouse dispatch speeds (24-48h for in-stock items).</li>
                <li><strong>Engineering:</strong> Highlight IES/LDT photometric files available for lighting designers.</li>
                <li><strong>Warranty:</strong> Ensure standard 5-year commercial warranty terms are highlighted.</li>
            </ul>
        </div>

    </div>

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
let currentContextCategoryFilter = 'all';

function filterContext() {
    const query = document.getElementById('context-search-input')?.value.toLowerCase().trim() || '';
    const cards = document.querySelectorAll('.context-card-item');

    cards.forEach(card => {
        const cat = card.getAttribute('data-category') || '';
        const searchContent = card.getAttribute('data-search') || '';

        const matchesCat = (currentContextCategoryFilter === 'all' || cat === currentContextCategoryFilter);
        const matchesQuery = (!query || searchContent.includes(query));

        if (matchesCat && matchesQuery) {
            card.style.display = 'block';
        } else {
            card.style.display = 'none';
        }
    });
}

function filterContextCategory(cat, btn) {
    currentContextCategoryFilter = cat;
    document.querySelectorAll('.context-cat-btn').forEach(b => b.classList.remove('is-active'));
    if (btn) btn.classList.add('is-active');
    filterContext();
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
