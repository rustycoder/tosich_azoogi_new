@extends('layouts.dashboard')

@section('title', 'AI Ruleset, Context & FAQ Knowledge Base')

@section('content')
<div class="dash-head">
    <div class="dash-crumb">
        <a href="{{ route('dashboard.home') }}">Dashboard</a>
        <span>/</span>
        <span>AI</span>
        <span>/</span>
        <span>Knowledge, Rules &amp; FAQ</span>
    </div>
    <div class="dash-head-title">
        <h1>AI Ruleset, Context &amp; FAQ Knowledge Base</h1>
        <div class="dash-head-actions">
            <button type="button" class="btn" onclick="openFaqModal()" style="display: inline-flex; align-items: center; gap: 6px; background: var(--card-bg); border: 1px solid var(--accent); color: var(--accent); font-weight: 600;">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width: 16px; height: 16px;"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                <span>+ Add FAQ Item</span>
            </button>
            <button class="btn primary" type="submit" form="ai-knowledge-form">Save Rules &amp; Context</button>
        </div>
    </div>
    <p class="dash-lead">Define behavioral guidelines, tone of voice, forbidden claims, Sydney engineering &amp; manufacturing context, and maintain a verified FAQ knowledge base that the AI references to deliver 100% accurate responses.</p>

    <!-- AI Subnavigation Tabs -->
    <div style="display: flex; gap: 8px; margin-top: 16px; border-bottom: 1px solid var(--line); padding-bottom: 12px; flex-wrap: wrap;">
        <a href="{{ route('dashboard.ai.models') }}" class="btn" style="text-decoration: none; display: inline-flex; align-items: center; gap: 6px;">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width: 14px; height: 14px;"><path d="M12 2v4M12 18v4M4.93 4.93l2.83 2.83M16.24 16.24l2.83 2.83M2 12h4M18 12h4M4.93 19.07l2.83-2.83M16.24 7.76l2.83-2.83"/></svg>
            <span>Models &amp; Rates</span>
        </a>
        <a href="{{ route('dashboard.ai.widget') }}" class="btn" style="text-decoration: none; display: inline-flex; align-items: center; gap: 6px;">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width: 14px; height: 14px;"><rect x="3" y="3" width="18" height="18" rx="4"/><path d="M9 3v18M15 9h6M15 15h6"/></svg>
            <span>Widget &amp; Branding</span>
        </a>
        <a href="{{ route('dashboard.ai.knowledge') }}" class="btn" style="background: var(--accent); color: #0b0b0b; font-weight: 700; text-decoration: none; display: inline-flex; align-items: center; gap: 6px;">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width: 14px; height: 14px;"><path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"/><path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"/></svg>
            <span>Knowledge, Rules &amp; FAQ</span>
        </a>
        <a href="{{ route('dashboard.chat-sessions.index') }}" class="btn" style="text-decoration: none; display: inline-flex; align-items: center; gap: 6px;">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width: 14px; height: 14px;"><path d="M21 11.5a8.38 8.38 0 0 1-.9 3.8 8.5 8.5 0 0 1-7.6 4.7 8.38 8.38 0 0 1-3.8-.9L3 21l1.9-5.7a8.38 8.38 0 0 1-.9-3.8 8.5 8.5 0 0 1 4.7-7.6 8.38 8.38 0 0 1 3.8-.9h.5a8.48 8.48 0 0 1 8 8v.5z"/></svg>
            <span>AI Chat Logs</span>
        </a>
    </div>
</div>

@if (session('status'))
    <div class="dash-card" style="padding: 14px 18px; background: rgba(16, 185, 129, 0.1); border: 1px solid rgba(16, 185, 129, 0.25); color: #10b981; margin-bottom: 24px; display: flex; align-items: center; gap: 10px;">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width: 18px; height: 18px; flex-shrink: 0;"><polyline points="20 6 9 17 4 12"/></svg>
        <span>{{ session('status') }}</span>
    </div>
@endif

<div style="display: grid; grid-template-columns: minmax(0, 1.4fr) minmax(320px, 1fr); gap: 24px; align-items: start;">
    
    <!-- Left Column: Rules & Context Form + FAQ List -->
    <div style="display: flex; flex-direction: column; gap: 24px;">
        
        <form id="ai-knowledge-form" method="POST" action="{{ route('dashboard.ai.knowledge.update') }}">
            @csrf
            @method('PUT')

            <!-- Card 1: Custom Ruleset & Directives -->
            <div class="dash-card" style="padding: 24px; margin-bottom: 24px;">
                <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 4px;">
                    <div style="font-size: 15px; font-weight: 700; color: var(--dash-ink, var(--ink)); display: flex; align-items: center; gap: 8px;">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width: 18px; height: 18px; color: var(--accent);"><polyline points="9 11 12 14 22 4"/><path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"/></svg>
                        <span>System Ruleset &amp; Behavioral Guidelines</span>
                    </div>
                </div>
                <p style="font-size: 12.5px; color: var(--dash-muted, var(--muted)); margin-top: 0; margin-bottom: 16px;">
                    Instructions on tone, compliance, forbidden claims, and product recommendation guidelines.
                </p>

                <div>
                    <label class="dash-label" for="ruleset" style="font-weight: 600; font-size: 13px;">Directives (Markdown Supported)</label>
                    <textarea name="ruleset" id="ruleset" class="dash-textarea" rows="7" required style="font-family: monospace; font-size: 12.5px; line-height: 1.5;">{{ old('ruleset', $knowledge['ruleset'] ?? '') }}</textarea>
                    <span style="font-size: 11px; color: var(--dash-muted, var(--muted));">Bullet points are recommended. The AI will strictly follow these rules during all turns.</span>
                </div>
            </div>

            <!-- Card 2: Company Context & Manufacturing Policies -->
            <div class="dash-card" style="padding: 24px;">
                <div style="font-size: 15px; font-weight: 700; color: var(--dash-ink, var(--ink)); margin-bottom: 4px; display: flex; align-items: center; gap: 8px;">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width: 18px; height: 18px; color: var(--accent);"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>
                    <span>Company Context &amp; Manufacturing Policies</span>
                </div>
                <p style="font-size: 12.5px; color: var(--dash-muted, var(--muted)); margin-top: 0; margin-bottom: 16px;">
                    Background knowledge regarding Azoogi’s Sydney engineering facility, warranty terms, lead times, and Australian Standards compliance.
                </p>

                <div>
                    <label class="dash-label" for="company_context" style="font-weight: 600; font-size: 13px;">Company Background &amp; Logistics</label>
                    <textarea name="company_context" id="company_context" class="dash-textarea" rows="7" required style="font-family: monospace; font-size: 12.5px; line-height: 1.5;">{{ old('company_context', $knowledge['company_context'] ?? '') }}</textarea>
                    <span style="font-size: 11px; color: var(--dash-muted, var(--muted));">Included in every system prompt to give the AI accurate company knowledge.</span>
                </div>
            </div>
        </form>

        <!-- Card 3: Verified FAQ Knowledge Base Manager -->
        <div class="dash-card" style="padding: 24px;">
            <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 4px;">
                <div style="font-size: 15px; font-weight: 700; color: var(--dash-ink, var(--ink)); display: flex; align-items: center; gap: 8px;">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width: 18px; height: 18px; color: var(--accent);"><circle cx="12" cy="12" r="10"/><path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
                    <span>Verified FAQ Knowledge Base ({{ count($faqs) }})</span>
                </div>
                <button type="button" class="btn primary" onclick="openFaqModal()" style="font-size: 12px; padding: 6px 12px;">+ Add Question</button>
            </div>
            <p style="font-size: 12.5px; color: var(--dash-muted, var(--muted)); margin-top: 0; margin-bottom: 18px;">
                Structured Q&amp;A pairs injected directly into the LLM system prompt for instant, factual responses.
            </p>

            <div style="display: flex; flex-direction: column; gap: 12px;">
                @forelse ($faqs as $faq)
                    <div style="background: rgba(255, 255, 255, 0.02); border: 1px solid var(--line); border-radius: 8px; padding: 14px 16px; display: flex; justify-content: space-between; gap: 16px;">
                        <div style="flex: 1;">
                            <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 6px;">
                                <span class="dash-pill" style="font-size: 10px; text-transform: uppercase;">{{ $faq['category'] ?? 'General' }}</span>
                                @if (!empty($faq['is_active']))
                                    <span class="dash-pill is-active" style="font-size: 10px;">Active in Prompt</span>
                                @else
                                    <span class="dash-pill is-inactive" style="font-size: 10px;">Disabled</span>
                                @endif
                            </div>
                            <div style="font-size: 13.5px; font-weight: 700; color: var(--dash-ink, var(--ink)); margin-bottom: 4px;">
                                Q: {{ $faq['question'] }}
                            </div>
                            <div style="font-size: 12.5px; color: var(--dash-muted, var(--muted)); line-height: 1.5;">
                                A: {{ $faq['answer'] }}
                            </div>
                        </div>
                        <div style="display: flex; flex-direction: column; gap: 6px; align-items: flex-end;">
                            <button type="button" class="btn" style="padding: 4px 8px; font-size: 11px;" onclick="editFaq({{ json_encode($faq) }})">Edit</button>
                            <form method="POST" action="{{ route('dashboard.ai.faqs.delete', $faq['id']) }}" onsubmit="return confirm('Remove this FAQ item from the knowledge base?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn" style="padding: 4px 8px; font-size: 11px; color: #ef4444; border-color: rgba(239, 68, 68, 0.3);">Delete</button>
                            </form>
                        </div>
                    </div>
                @empty
                    <div style="padding: 32px; text-align: center; color: var(--dash-muted, var(--muted));">
                        No custom FAQ items defined yet. Click "+ Add Question" to register verified answers.
                    </div>
                @endforelse
            </div>
        </div>

    </div>

    <!-- Right Column: Live Compiled System Prompt Inspector -->
    <div style="position: sticky; top: 24px;">
        <div class="dash-card" style="padding: 20px;">
            <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 12px;">
                <div style="font-size: 12px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; color: var(--accent);">
                    Compiled System Prompt
                </div>
                <span class="dash-pill is-active" style="font-size: 10px;">Active Engine</span>
            </div>
            <p style="font-size: 11.5px; color: var(--dash-muted, var(--muted)); margin-top: 0; margin-bottom: 12px;">
                This is the exact structured prompt assembled and sent to Claude, Gemini, or OpenAI on every chat turn:
            </p>

            <pre style="background: rgba(0,0,0,0.5); border: 1px solid var(--line); border-radius: 8px; padding: 14px; font-size: 11px; font-family: monospace; line-height: 1.5; color: var(--dash-ink, var(--ink)); max-height: 520px; overflow-y: auto; white-space: pre-wrap; word-break: break-word;">{{ $compiledPrompt }}</pre>

            <div style="margin-top: 14px; font-size: 11px; color: var(--dash-muted, var(--muted));">
                💡 When visitors submit their Name and Project Name in the intake form, dynamic client context is appended at runtime automatically.
            </div>
        </div>
    </div>

</div>

<!-- FAQ Modal (Add / Edit) -->
<div id="faq-modal" style="display: none; position: fixed; inset: 0; background: rgba(0, 0, 0, 0.75); backdrop-filter: blur(8px); z-index: 100000; align-items: center; justify-content: center; padding: 20px;">
    <div class="dash-card" style="width: 100%; max-width: 540px; padding: 24px; position: relative; max-height: 90vh; overflow-y: auto;">
        <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 16px;">
            <h3 id="faq-modal-title" style="margin: 0; font-size: 16px; font-weight: 700; color: var(--dash-ink, var(--ink));">Add FAQ Knowledge Item</h3>
            <button type="button" class="btn" onclick="closeFaqModal()" style="padding: 4px 8px;">✕</button>
        </div>

        <form method="POST" action="{{ route('dashboard.ai.faqs.store') }}">
            @csrf
            <input type="hidden" name="id" id="faq_id" value="">

            <div style="margin-bottom: 14px;">
                <label class="dash-label" for="faq_category" style="font-weight: 600; font-size: 12.5px;">Category</label>
                <select name="category" id="faq_category" class="dash-input" required>
                    <option value="General">General</option>
                    <option value="Shipping & Lead Time">Shipping & Lead Time</option>
                    <option value="Technical & Smart Controls">Technical & Smart Controls</option>
                    <option value="Warranty & Compliance">Warranty & Compliance</option>
                    <option value="Photometrics & Testing">Photometrics & Testing</option>
                </select>
            </div>

            <div style="margin-bottom: 14px;">
                <label class="dash-label" for="faq_question" style="font-weight: 600; font-size: 12.5px;">Question / Inquiry Pattern</label>
                <input type="text" name="question" id="faq_question" class="dash-input" placeholder="e.g. What are the lead times for custom cutting?" required>
            </div>

            <div style="margin-bottom: 14px;">
                <label class="dash-label" for="faq_answer" style="font-weight: 600; font-size: 12.5px;">Verified Answer Fact</label>
                <textarea name="answer" id="faq_answer" class="dash-textarea" rows="4" placeholder="e.g. Standard lead time is 3-5 business days from our Sydney warehouse." required></textarea>
            </div>

            <div style="margin-bottom: 20px;">
                <label style="display: flex; align-items: center; gap: 8px; cursor: pointer;">
                    <input type="checkbox" name="is_active" id="faq_is_active" value="1" checked style="width: 16px; height: 16px; accent-color: var(--accent);">
                    <span style="font-size: 13px; color: var(--dash-ink, var(--ink));">Active in AI System Prompt</span>
                </label>
            </div>

            <div style="display: flex; justify-content: flex-end; gap: 10px;">
                <button type="button" class="btn" onclick="closeFaqModal()">Cancel</button>
                <button type="submit" class="btn primary">Save FAQ Item</button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
function openFaqModal() {
    document.getElementById('faq-modal-title').textContent = 'Add FAQ Knowledge Item';
    document.getElementById('faq_id').value = '';
    document.getElementById('faq_question').value = '';
    document.getElementById('faq_answer').value = '';
    document.getElementById('faq_category').value = 'General';
    document.getElementById('faq_is_active').checked = true;

    const modal = document.getElementById('faq-modal');
    modal.style.display = 'flex';
}

function editFaq(faq) {
    document.getElementById('faq-modal-title').textContent = 'Edit FAQ Knowledge Item';
    document.getElementById('faq_id').value = faq.id || '';
    document.getElementById('faq_question').value = faq.question || '';
    document.getElementById('faq_answer').value = faq.answer || '';
    document.getElementById('faq_category').value = faq.category || 'General';
    document.getElementById('faq_is_active').checked = faq.is_active !== false;

    const modal = document.getElementById('faq-modal');
    modal.style.display = 'flex';
}

function closeFaqModal() {
    document.getElementById('faq-modal').style.display = 'none';
}
</script>
@endpush
@endsection
