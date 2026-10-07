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

<div style="display: grid; grid-template-columns: minmax(0, 1.4fr) minmax(320px, 1fr); gap: 24px; align-items: start;">
    
    <!-- Left Column: FAQ Knowledge Base List & Search -->
    <div style="display: flex; flex-direction: column; gap: 20px;">
        
        <!-- Search and Filter Bar -->
        <div class="dash-card" style="padding: 16px 20px;">
            <div style="display: flex; gap: 12px; align-items: center; flex-wrap: wrap;">
                <div style="flex: 1; min-width: 220px; position: relative;">
                    <input type="text" id="faq-search-input" class="dash-input" placeholder="Search questions, keywords, or answers..." oninput="filterFaqs()" style="padding-left: 36px;">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="position: absolute; left: 12px; top: 50%; transform: translateY(-50%); width: 15px; height: 15px; color: var(--dash-muted, var(--muted));"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                </div>
                <div style="display: flex; gap: 6px; align-items: center; flex-wrap: wrap;">
                    <button type="button" class="btn is-active faq-cat-btn" data-cat="all" onclick="filterFaqCategory('all', this)" style="font-size: 11.5px; padding: 6px 12px;">All ({{ count($faqs) }})</button>
                    @php
                        $categories = array_unique(array_filter(array_column($faqs, 'category')));
                    @endphp
                    @foreach ($categories as $cat)
                        <button type="button" class="btn faq-cat-btn" data-cat="{{ $cat }}" onclick="filterFaqCategory('{{ $cat }}', this)" style="font-size: 11.5px; padding: 6px 12px;">{{ $cat }}</button>
                    @endforeach
                </div>
            </div>
        </div>

        <!-- FAQ Cards Container -->
        <div id="faq-items-list" style="display: flex; flex-direction: column; gap: 14px;">
            @forelse ($faqs as $faq)
                <div class="dash-card faq-card-item" data-category="{{ $faq['category'] ?? '' }}" data-search="{{ strtolower(($faq['category'] ?? '') . ' ' . ($faq['question'] ?? '') . ' ' . ($faq['answer'] ?? '')) }}" style="padding: 20px; transition: transform 0.2s ease, border-color 0.2s ease;">
                    <div style="display: flex; align-items: flex-start; justify-content: space-between; gap: 12px; margin-bottom: 8px;">
                        <div style="display: flex; align-items: center; gap: 8px; flex-wrap: wrap;">
                            <span class="dash-pill is-active" style="font-size: 10.5px; text-transform: uppercase; letter-spacing: 0.04em;">{{ $faq['category'] ?? 'General' }}</span>
                            @if (($faq['is_active'] ?? true) === false)
                                <span class="dash-pill" style="font-size: 10px; background: rgba(239, 68, 68, 0.15); color: #ef4444; border-color: rgba(239,68,68,0.3);">Inactive</span>
                            @endif
                        </div>
                        <div style="display: flex; gap: 6px; flex-shrink: 0;">
                            <button type="button" class="btn" style="font-size: 11.5px; padding: 4px 10px;" onclick="editFaqItem(@js($faq))">Edit</button>
                            <form method="POST" action="{{ route('dashboard.ai.faqs.delete', $faq['id']) }}" onsubmit="return confirm('Are you sure you want to delete this FAQ item?');" style="display: inline;">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn" style="font-size: 11.5px; padding: 4px 8px; color: #ef4444;" title="Delete">✕</button>
                            </form>
                        </div>
                    </div>
                    <div style="font-size: 14.5px; font-weight: 700; color: var(--dash-ink, var(--ink)); margin-bottom: 8px; line-height: 1.35;">
                        {{ $faq['question'] ?? '' }}
                    </div>
                    <div style="font-size: 13px; color: var(--dash-muted, var(--muted)); line-height: 1.55; white-space: pre-wrap;">{{ $faq['answer'] ?? '' }}</div>
                </div>
            @empty
                <div class="dash-card" style="padding: 40px; text-align: center; color: var(--dash-muted, var(--muted));">
                    <div style="font-size: 32px; margin-bottom: 10px;">📋</div>
                    <div style="font-size: 15px; font-weight: 600; color: var(--dash-ink, var(--ink)); margin-bottom: 6px;">No FAQ Items Yet</div>
                    <div style="font-size: 12.5px; margin-bottom: 16px;">Add frequently asked questions to ground the AI with exact answers.</div>
                    <button type="button" class="btn primary" onclick="openFaqModal()">+ Add First FAQ</button>
                </div>
            @endforelse
        </div>

    </div>

    <!-- Right Column: Live Prompt Inspector & FAQ Stats -->
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
                Active FAQ entries are appended to the system prompt for immediate retrieval.
            </p>
            <div style="background: rgba(15, 15, 15, 0.95); border: 1px solid var(--line); border-radius: 8px; padding: 12px; max-height: 380px; overflow-y: auto; font-family: monospace; font-size: 11px; line-height: 1.5; color: #a3e635; white-space: pre-wrap; word-break: break-word;">{{ $compiledPrompt }}</div>
        </div>

        <!-- FAQ Guidance Card -->
        <div class="dash-card" style="padding: 18px;">
            <div style="font-size: 13px; font-weight: 700; color: var(--dash-ink, var(--ink)); margin-bottom: 8px; display: flex; align-items: center; gap: 6px;">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width: 15px; height: 15px; color: var(--accent);"><circle cx="12" cy="12" r="10"/><path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
                <span>Knowledge Retrieval Tips</span>
            </div>
            <ul style="font-size: 11.5px; color: var(--dash-muted, var(--muted)); margin: 0; padding-left: 18px; line-height: 1.6;">
                <li>Keep questions phrased as real clients would ask them.</li>
                <li>Include warranty durations, IP ratings, and driver compatibility details.</li>
                <li>Toggle inactive rather than deleting to temporarily hide seasonal information.</li>
            </ul>
        </div>

    </div>

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
let currentCategoryFilter = 'all';

function filterFaqs() {
    const query = document.getElementById('faq-search-input')?.value.toLowerCase().trim() || '';
    const cards = document.querySelectorAll('.faq-card-item');

    cards.forEach(card => {
        const cat = card.getAttribute('data-category') || '';
        const searchContent = card.getAttribute('data-search') || '';

        const matchesCat = (currentCategoryFilter === 'all' || cat === currentCategoryFilter);
        const matchesQuery = (!query || searchContent.includes(query));

        if (matchesCat && matchesQuery) {
            card.style.display = 'block';
        } else {
            card.style.display = 'none';
        }
    });
}

function filterFaqCategory(cat, btn) {
    currentCategoryFilter = cat;
    document.querySelectorAll('.faq-cat-btn').forEach(b => b.classList.remove('is-active'));
    if (btn) btn.classList.add('is-active');
    filterFaqs();
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
