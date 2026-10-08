@extends('layouts.dashboard')

@section('title', 'AI System Rules & Directives')

@section('content')
<div class="dash-head">
    <div class="dash-crumb">
        <a href="{{ route('dashboard.home') }}">Dashboard</a>
        <span>/</span>
        <span>AI</span>
        <span>/</span>
        <span>System Rules</span>
    </div>
    <div class="dash-head-title">
        <h1>AI System Rules &amp; Behavioral Directives</h1>
        <div class="dash-head-actions">
            <button type="button" class="btn primary" onclick="openRuleModal()" style="display: inline-flex; align-items: center; gap: 6px;">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width: 16px; height: 16px;"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                <span>+ Add Rule Item</span>
            </button>
        </div>
    </div>
    <p class="dash-lead">Manage dynamic behavioral guidelines, tone of voice, compliance standards, technical precision, strict prohibitions, and human escalation triggers.</p>
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
    
    <!-- Left Column: Rules List & Live Search -->
    <div style="display: flex; flex-direction: column; gap: 20px;">
        
        <!-- Search and Category Filter Bar -->
        <div class="dash-card" style="padding: 16px 20px;">
            <div style="display: flex; gap: 12px; align-items: center; flex-wrap: wrap;">
                <div style="flex: 1; min-width: 220px; position: relative;">
                    <input type="text" id="rule-search-input" class="dash-input" placeholder="Search rules, directives, or keywords..." oninput="filterRules()" style="padding-left: 36px;">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="position: absolute; left: 12px; top: 50%; transform: translateY(-50%); width: 15px; height: 15px; color: var(--dash-muted, var(--muted));"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                </div>
                <div style="display: flex; gap: 6px; align-items: center; flex-wrap: wrap;">
                    <button type="button" class="btn is-active rule-cat-btn" data-cat="all" onclick="filterRuleCategory('all', this)" style="font-size: 11.5px; padding: 6px 12px;">All ({{ count($rules) }})</button>
                    @php
                        $categories = array_values(array_unique(array_filter(array_column($rules, 'category'))));
                    @endphp
                    @foreach ($categories as $cat)
                        <button type="button" class="btn rule-cat-btn" data-cat="{{ $cat }}" onclick="filterRuleCategory('{{ $cat }}', this)" style="font-size: 11.5px; padding: 6px 12px;">{{ $cat }}</button>
                    @endforeach
                </div>
            </div>
        </div>

        <!-- Rules Cards Container -->
        <div id="rule-items-list" style="display: flex; flex-direction: column; gap: 14px;">
            @forelse ($rules as $rule)
                <div class="dash-card rule-card-item" data-category="{{ $rule['category'] ?? '' }}" data-search="{{ strtolower(($rule['category'] ?? '') . ' ' . ($rule['title'] ?? '') . ' ' . ($rule['content'] ?? '')) }}" style="padding: 20px; transition: transform 0.2s ease, border-color 0.2s ease;">
                    <div style="display: flex; align-items: flex-start; justify-content: space-between; gap: 12px; margin-bottom: 8px;">
                        <div style="display: flex; align-items: center; gap: 8px; flex-wrap: wrap;">
                            <span class="dash-pill is-active" style="font-size: 10.5px; text-transform: uppercase; letter-spacing: 0.04em;">{{ $rule['category'] ?? 'General' }}</span>
                            @if (($rule['is_active'] ?? true) === false)
                                <span class="dash-pill" style="font-size: 10px; background: rgba(239, 68, 68, 0.15); color: #ef4444; border-color: rgba(239,68,68,0.3);">Inactive</span>
                            @endif
                        </div>
                        <div style="display: flex; gap: 6px; flex-shrink: 0;">
                            <button type="button" class="btn" style="font-size: 11.5px; padding: 4px 10px;" onclick="editRuleItem(@js($rule))">Edit</button>
                            <form method="POST" action="{{ route('dashboard.ai.rules.delete', $rule['id']) }}" onsubmit="return confirm('Are you sure you want to delete this rule?');" style="display: inline;">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn" style="font-size: 11.5px; padding: 4px 8px; color: #ef4444;" title="Delete">✕</button>
                            </form>
                        </div>
                    </div>
                    <div style="font-size: 14.5px; font-weight: 700; color: var(--dash-ink, var(--ink)); margin-bottom: 8px; line-height: 1.35;">
                        {{ $rule['title'] ?? '' }}
                    </div>
                    <div style="font-size: 13px; color: var(--dash-muted, var(--muted)); line-height: 1.55; white-space: pre-wrap;">{{ $rule['content'] ?? '' }}</div>
                </div>
            @empty
                <div class="dash-card" style="padding: 40px; text-align: center; color: var(--dash-muted, var(--muted));">
                    <div style="font-size: 32px; margin-bottom: 10px;">📜</div>
                    <div style="font-size: 15px; font-weight: 600; color: var(--dash-ink, var(--ink)); margin-bottom: 6px;">No System Rules Defined</div>
                    <div style="font-size: 12.5px; margin-bottom: 16px;">Add behavioral directives and guidelines to instruct the AI assistant.</div>
                    <button type="button" class="btn primary" onclick="openRuleModal()">+ Add First Rule</button>
                </div>
            @endforelse
        </div>

    </div>

    <!-- Right Column: Live Prompt Inspector & Rule Guidelines -->
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
                Active rules are dynamically compiled and injected into the AI system instructions.
            </p>
            <div style="background: rgba(15, 15, 15, 0.95); border: 1px solid var(--line); border-radius: 8px; padding: 12px; max-height: 380px; overflow-y: auto; font-family: monospace; font-size: 11px; line-height: 1.5; color: #a3e635; white-space: pre-wrap; word-break: break-word;">{{ $compiledPrompt }}</div>
        </div>

        <!-- Rule Guidance Tips -->
        <div class="dash-card" style="padding: 18px;">
            <div style="font-size: 13px; font-weight: 700; color: var(--dash-ink, var(--ink)); margin-bottom: 8px; display: flex; align-items: center; gap: 6px;">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width: 15px; height: 15px; color: var(--accent);"><polyline points="9 11 12 14 22 4"/><path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"/></svg>
                <span>Rule Formulation Tips</span>
            </div>
            <ul style="font-size: 11.5px; color: var(--dash-muted, var(--muted)); margin: 0; padding-left: 18px; line-height: 1.6;">
                <li><strong>Tone &amp; Persona:</strong> Define trade-level technical rigor (e.g. CRI90+, SDCM<3, IP ratings).</li>
                <li><strong>Prohibitions:</strong> Ensure strict pricing lockouts and prevent hallucinated item codes.</li>
                <li><strong>Escalations:</strong> Specify when to trigger transcript handoffs to sales engineers.</li>
                <li><strong>Toggles:</strong> Toggle rules inactive rather than deleting them if testing variations.</li>
            </ul>
        </div>

    </div>

</div>

<!-- System Rule Modal (Add & Edit) -->
<div id="rule-modal" style="display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.6); backdrop-filter: blur(8px); z-index: 9999; align-items: center; justify-content: center; padding: 20px;">
    <div class="dash-card" style="width: 100%; max-width: 620px; padding: 28px; background: var(--dash-card); border: 1px solid var(--dash-line); color: var(--dash-ink); box-shadow: 0 24px 60px rgba(0,0,0,0.35); border-radius: 14px;">
        <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 18px;">
            <h3 id="rule-modal-title" style="margin: 0; font-size: 17px; font-weight: 700; color: var(--dash-ink);">Add System Rule Item</h3>
            <button type="button" class="btn" onclick="closeRuleModal()" style="padding: 4px 8px; font-size: 13px;">✕</button>
        </div>

        <form method="POST" action="{{ route('dashboard.ai.rules.store') }}" id="rule-form">
            @csrf
            <input type="hidden" name="id" id="modal-rule-id">

            <div style="display: flex; flex-direction: column; gap: 16px;">
                <div>
                    <label class="dash-label" for="modal-rule-category" style="font-size: 12.5px; font-weight: 600;">Category</label>
                    <input type="text" name="category" id="modal-rule-category" class="dash-input" placeholder="e.g. Tone & Persona, Strict Prohibitions, Escalation & Handoff" required list="rule-category-suggestions">
                    <datalist id="rule-category-suggestions">
                        <option value="Tone & Persona">
                        <option value="Compliance & Standards">
                        <option value="Technical Precision">
                        <option value="Strict Prohibitions">
                        <option value="Escalation & Handoff">
                        <option value="Custom Directives">
                    </datalist>
                </div>

                <div>
                    <label class="dash-label" for="modal-rule-title" style="font-size: 12.5px; font-weight: 600;">Rule Title / Directive Name</label>
                    <input type="text" name="title" id="modal-rule-title" class="dash-input" placeholder="e.g. Australian Standards & Building Compliance" required>
                </div>

                <div>
                    <label class="dash-label" for="modal-rule-content" style="font-size: 12.5px; font-weight: 600;">Rule Directive &amp; Instructions</label>
                    <textarea name="content" id="modal-rule-content" class="dash-textarea" rows="5" placeholder="Define exact instructions, technical boundaries, compliance standards, or conversational guidance." required></textarea>
                </div>

                <div style="display: flex; align-items: center; gap: 10px;">
                    <input type="checkbox" name="is_active" id="modal-rule-is-active" value="1" checked style="width: 16px; height: 16px; accent-color: var(--accent);">
                    <label for="modal-rule-is-active" style="font-size: 13px; font-weight: 600; color: var(--dash-ink); cursor: pointer;">
                        Active (Compiled into live AI system prompt)
                    </label>
                </div>
            </div>

            <div style="display: flex; justify-content: flex-end; gap: 10px; margin-top: 24px; padding-top: 16px; border-top: 1px solid var(--dash-line);">
                <button type="button" class="btn" onclick="closeRuleModal()">Cancel</button>
                <button type="submit" class="btn primary">Save Rule Item</button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
let currentRuleCategoryFilter = 'all';

function filterRules() {
    const query = document.getElementById('rule-search-input')?.value.toLowerCase().trim() || '';
    const cards = document.querySelectorAll('.rule-card-item');

    cards.forEach(card => {
        const cat = card.getAttribute('data-category') || '';
        const searchContent = card.getAttribute('data-search') || '';

        const matchesCat = (currentRuleCategoryFilter === 'all' || cat === currentRuleCategoryFilter);
        const matchesQuery = (!query || searchContent.includes(query));

        if (matchesCat && matchesQuery) {
            card.style.display = 'block';
        } else {
            card.style.display = 'none';
        }
    });
}

function filterRuleCategory(cat, btn) {
    currentRuleCategoryFilter = cat;
    document.querySelectorAll('.rule-cat-btn').forEach(b => b.classList.remove('is-active'));
    if (btn) btn.classList.add('is-active');
    filterRules();
}

function openRuleModal() {
    document.getElementById('rule-modal-title').textContent = 'Add System Rule Item';
    document.getElementById('modal-rule-id').value = '';
    document.getElementById('modal-rule-category').value = '';
    document.getElementById('modal-rule-title').value = '';
    document.getElementById('modal-rule-content').value = '';
    document.getElementById('modal-rule-is-active').checked = true;

    const modal = document.getElementById('rule-modal');
    modal.style.display = 'flex';
}

function editRuleItem(rule) {
    document.getElementById('rule-modal-title').textContent = 'Edit System Rule Item';
    document.getElementById('modal-rule-id').value = rule.id || '';
    document.getElementById('modal-rule-category').value = rule.category || '';
    document.getElementById('modal-rule-title').value = rule.title || '';
    document.getElementById('modal-rule-content').value = rule.content || '';
    document.getElementById('modal-rule-is-active').checked = (rule.is_active !== false);

    const modal = document.getElementById('rule-modal');
    modal.style.display = 'flex';
}

function closeRuleModal() {
    document.getElementById('rule-modal').style.display = 'none';
}

window.addEventListener('keydown', (e) => {
    if (e.key === 'Escape') closeRuleModal();
});
</script>
@endpush
@endsection
