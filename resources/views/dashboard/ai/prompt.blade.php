@extends('layouts.dashboard')

@section('title', 'AI Prompt')

@section('content')
<div class="dash-head">
    <div class="dash-crumb">
        <a href="{{ route('dashboard.home') }}">Dashboard</a>
        <span>/</span>
        <span>AI</span>
        <span>/</span>
        <span>Prompt</span>
    </div>
    <div class="dash-head-title">
        <h1>AI Prompt</h1>
        <div class="dash-head-actions">
            <button type="button" class="btn primary" onclick="copySystemPrompt()" style="display: inline-flex; align-items: center; gap: 6px;">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width: 15px; height: 15px;"><rect x="9" y="9" width="13" height="13" rx="2" ry="2"/><path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"/></svg>
                <span id="copy-btn-text">Copy Full Prompt</span>
            </button>
        </div>
    </div>
    <p class="dash-lead">Inspect the complete runtime system prompt assembled dynamically from your active System Rules, Company Context, and FAQ Knowledge Base.</p>
</div>

<!-- Key Prompt Assembly Metrics -->
<div class="dash-grid-stats" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 16px; margin-bottom: 24px;">
    <a href="{{ route('dashboard.ai.rules') }}" class="dash-card" style="padding: 18px; text-decoration: none; display: flex; flex-direction: column; gap: 6px; transition: transform 0.15s ease, border-color 0.15s ease;">
        <div style="display: flex; align-items: center; justify-content: space-between;">
            <span style="font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; color: var(--dash-muted, var(--muted));">System Rules</span>
            <span class="dash-pill is-active" style="font-size: 10px;">{{ $activeRulesCount }} Active</span>
        </div>
        <div style="font-size: 26px; font-weight: 800; color: var(--dash-ink);">{{ count($rules) }}</div>
        <span style="font-size: 11px; color: var(--accent);">Manage behavioral rules &rarr;</span>
    </a>

    <a href="{{ route('dashboard.ai.context') }}" class="dash-card" style="padding: 18px; text-decoration: none; display: flex; flex-direction: column; gap: 6px; transition: transform 0.15s ease, border-color 0.15s ease;">
        <div style="display: flex; align-items: center; justify-content: space-between;">
            <span style="font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; color: var(--dash-muted, var(--muted));">Company Context</span>
            <span class="dash-pill is-active" style="font-size: 10px;">{{ $activeContextCount }} Active</span>
        </div>
        <div style="font-size: 26px; font-weight: 800; color: var(--dash-ink);">{{ count($contextItems) }}</div>
        <span style="font-size: 11px; color: var(--accent);">Manage factory context &rarr;</span>
    </a>

    <a href="{{ route('dashboard.ai.faqs') }}" class="dash-card" style="padding: 18px; text-decoration: none; display: flex; flex-direction: column; gap: 6px; transition: transform 0.15s ease, border-color 0.15s ease;">
        <div style="display: flex; align-items: center; justify-content: space-between;">
            <span style="font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; color: var(--dash-muted, var(--muted));">FAQ Knowledge</span>
            <span class="dash-pill is-active" style="font-size: 10px;">{{ $activeFaqsCount }} Active</span>
        </div>
        <div style="font-size: 26px; font-weight: 800; color: var(--dash-ink);">{{ count($faqs) }}</div>
        <span style="font-size: 11px; color: var(--accent);">Manage verified answers &rarr;</span>
    </a>

    <div class="dash-card" style="padding: 18px; display: flex; flex-direction: column; gap: 6px;">
        <div style="display: flex; align-items: center; justify-content: space-between;">
            <span style="font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; color: var(--dash-muted, var(--muted));">Token Estimate</span>
            <span class="dash-tag" style="font-size: 10px;">~{{ number_format(strlen($compiledPrompt)) }} chars</span>
        </div>
        <div style="font-size: 26px; font-weight: 800; color: var(--accent);">~{{ number_format($promptTokensEstimate) }}</div>
        <span style="font-size: 11px; color: var(--dash-muted, var(--muted));">Injected per user session</span>
    </div>
</div>

<!-- Main Compiled Prompt Display Card -->
<div class="dash-card" style="padding: 24px; border-color: rgba(103, 208, 78, 0.35);">
    <div style="margin-bottom: 14px;">
        <div style="font-size: 14px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; color: var(--accent); display: flex; align-items: center; gap: 8px;">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width: 18px; height: 18px;"><polyline points="4 17 10 11 4 5"/><line x1="12" y1="19" x2="20" y2="19"/></svg>
            <span>Full Compiled System Prompt (Runtime Feed)</span>
        </div>
        <p style="font-size: 12px; color: var(--dash-muted, var(--muted)); margin: 4px 0 0 0;">
            This exact prompt is delivered to the configured LLM provider (Anthropic / OpenAI / Gemini) at the beginning of each chat conversation.
        </p>
    </div>

    <div style="position: relative;">
        <pre id="compiled-prompt-code" style="background: rgba(12, 12, 12, 0.95); border: 1px solid var(--line); border-radius: 10px; padding: 18px; max-height: 520px; overflow-y: auto; font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace; font-size: 12px; line-height: 1.6; color: #a3e635; white-space: pre-wrap; word-break: break-word; margin: 0;">{{ $compiledPrompt }}</pre>
    </div>
</div>

@push('scripts')
<script>
function copySystemPrompt() {
    const promptText = document.getElementById('compiled-prompt-code')?.innerText || '';
    if (!promptText) return;

    navigator.clipboard.writeText(promptText).then(() => {
        const btnText = document.getElementById('copy-btn-text');
        if (btnText) {
            const orig = btnText.textContent;
            btnText.textContent = 'Copied to Clipboard!';
            setTimeout(() => {
                btnText.textContent = orig;
            }, 2000);
        }
        if (window.showToast) {
            window.showToast('System prompt copied to clipboard.');
        }
    }).catch(err => {
        console.error('Copy failed:', err);
    });
}
</script>
@endpush
@endsection
