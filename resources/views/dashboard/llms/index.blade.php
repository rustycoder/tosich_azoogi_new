@extends('layouts.dashboard')

@section('title', 'AI & LLM Feeds (/llms.txt)')

@section('content')
<div class="dash-head">
    <div class="dash-crumb">
        <a href="{{ route('dashboard.home') }}">Dashboard</a>
        <span>/</span>
        <span>Content Management</span>
        <span>/</span>
        <span>LLM Feeds (/llms.txt)</span>
    </div>
    <div class="dash-head-title">
        <h1>LLM Feeds (<code>/llms.txt</code> &amp; <code>/llms-full.txt</code>)</h1>
        <div class="dash-head-actions">
            <a href="{{ route('dashboard.ai.config') }}" class="btn" style="display:inline-flex;align-items:center;gap:6px;">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" style="width:16px;height:16px;" aria-hidden="true"><path d="M12 2v4M12 18v4M4.93 4.93l2.83 2.83M16.24 16.24l2.83 2.83M2 12h4M18 12h4M4.93 19.07l2.83-2.83M16.24 7.76l2.83-2.83"/></svg>
                AI Model &amp; Key Settings &rarr;
            </a>
            <a href="{{ url('/llms.txt') }}" target="_blank" class="btn" style="display:inline-flex;align-items:center;gap:6px;">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" style="width:16px;height:16px;" aria-hidden="true"><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"/><polyline points="15 3 21 3 21 9"/><line x1="10" y1="14" x2="21" y2="3"/></svg>
                View /llms.txt
            </a>
            <a href="{{ url('/llms-full.txt') }}" target="_blank" class="btn" style="display:inline-flex;align-items:center;gap:6px;">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" style="width:16px;height:16px;" aria-hidden="true"><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"/><polyline points="15 3 21 3 21 9"/><line x1="10" y1="14" x2="21" y2="3"/></svg>
                View /llms-full.txt
            </a>
            <form method="post" action="{{ route('dashboard.llms.generate') }}" style="display:inline-block;">
                @csrf
                <button type="submit" class="btn" title="Clear and regenerate cache">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" style="width:16px;height:16px;vertical-align:middle;margin-right:4px;" aria-hidden="true"><path d="M21.5 2v6h-6M21.34 15.57a10 10 0 1 1-.57-8.38l5.67-5.67"/></svg>
                    Refresh Cache
                </button>
            </form>
            <button class="btn primary" type="submit" form="llm-feed-form">Save Changes</button>
        </div>
    </div>
    <p class="dash-lead">Manage machine-readable Markdown files consumed by external AI search engines (ChatGPT, Perplexity, Claude, Gemini, Copilot) for brand authority and product citations.</p>
</div>

@if (session('status'))
    <div class="dash-status" style="background:#e6f4ea;color:#137333;padding:12px 16px;border-radius:6px;margin-bottom:20px;font-size:14px;border:1px solid #ceead6;display:flex;align-items:center;gap:8px;">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:18px;height:18px;flex-shrink:0;"><polyline points="20 6 9 17 4 12"/></svg>
        <span>{{ session('status') }}</span>
    </div>
@endif

<!-- Quick Metrics Bar -->
<div class="dash-grid" style="display:grid;grid-template-columns:repeat(auto-fit,minmax(220px,1fr));gap:16px;margin-bottom:24px;">
    <div class="dash-card" style="padding:16px 20px;">
        <div style="font-size:12px;color:var(--muted);text-transform:uppercase;letter-spacing:0.05em;font-weight:600;margin-bottom:6px;">Summary Mode</div>
        <div style="display:flex;align-items:center;gap:8px;">
            <span class="dash-pill-active" style="{{ $isCustom ? 'background:#e8f0fe;color:#1a73e8;border-color:#d2e3fc;' : 'background:#e6f4ea;color:#137333;border-color:#ceead6;' }}">
                {{ $isCustom ? 'Custom Override Active' : 'Curated Dynamic Mode' }}
            </span>
        </div>
        <div style="font-size:11px;color:var(--muted);margin-top:6px;">
            {{ $isCustom ? 'Serving custom markdown edits below' : 'Auto-generating from curated standard' }}
        </div>
    </div>

    <div class="dash-card" style="padding:16px 20px;">
        <div style="font-size:12px;color:var(--muted);text-transform:uppercase;letter-spacing:0.05em;font-weight:600;margin-bottom:6px;">Published SKUs in Full Feed</div>
        <div style="font-size:24px;font-weight:700;color:var(--ink);">{{ number_format($productsCount) }}</div>
        <div style="font-size:11px;color:var(--muted);margin-top:4px;">Auto-synced from Airtable catalogue</div>
    </div>

    <div class="dash-card" style="padding:16px 20px;">
        <div style="font-size:12px;color:var(--muted);text-transform:uppercase;letter-spacing:0.05em;font-weight:600;margin-bottom:6px;">Showcase Projects</div>
        <div style="font-size:24px;font-weight:700;color:var(--ink);">{{ number_format($projectsCount) }}</div>
        <div style="font-size:11px;color:var(--muted);margin-top:4px;">Included with descriptions &amp; tags</div>
    </div>

    <div class="dash-card" style="padding:16px 20px;">
        <div style="font-size:12px;color:var(--muted);text-transform:uppercase;letter-spacing:0.05em;font-weight:600;margin-bottom:6px;">Cache Policy</div>
        <div style="font-size:16px;font-weight:600;color:var(--ink);">24h Edge Cached</div>
        <div style="font-size:11px;color:var(--muted);margin-top:4px;">Auto-purged on catalog/project save</div>
    </div>
</div>

<!-- Tab Navigation -->
<div class="dash-tabs" style="display:flex;gap:8px;border-bottom:1px solid var(--line);margin-bottom:20px;flex-wrap:wrap;">
    <button type="button" class="dash-tab is-active" data-tab-target="tab-editor" style="padding:10px 16px;font-weight:600;font-size:14px;cursor:pointer;border:none;background:none;border-bottom:2px solid var(--accent);color:var(--ink);">
        Editor (<code>/llms.txt</code>)
    </button>
    <button type="button" class="dash-tab" data-tab-target="tab-summary-preview" style="padding:10px 16px;font-weight:600;font-size:14px;cursor:pointer;border:none;background:none;border-bottom:2px solid transparent;color:var(--muted);">
        Live Summary Feed Preview
    </button>
    <button type="button" class="dash-tab" data-tab-target="tab-full-preview" style="padding:10px 16px;font-weight:600;font-size:14px;cursor:pointer;border:none;background:none;border-bottom:2px solid transparent;color:var(--muted);">
        Full Feed Preview (<code>/llms-full.txt</code>)
    </button>
    <button type="button" class="dash-tab" data-tab-target="tab-guidelines" style="padding:10px 16px;font-weight:600;font-size:14px;cursor:pointer;border:none;background:none;border-bottom:2px solid transparent;color:var(--muted);">
        AI Engine &amp; GEO Handbook
    </button>
</div>

<!-- TAB 1: Editor -->
<div id="tab-editor" class="dash-tab-content">
    <form id="llm-feed-form" class="dash-form" method="post" action="{{ route('dashboard.llms.update') }}">
        @csrf
        @method('put')

        <div class="dash-card" style="margin-bottom:20px;">
            <div style="display:flex;justify-content:space-between;align-items:flex-start;flex-wrap:wrap;gap:12px;margin-bottom:16px;">
                <div>
                    <h2 style="margin:0 0 4px 0;font-size:18px;">Summary Feed Customization</h2>
                    <p style="margin:0;font-size:13px;color:var(--muted);">
                        Customize the primary Markdown document served at <a href="{{ url('/llms.txt') }}" target="_blank"><code>/llms.txt</code></a>. You can use <code>{baseUrl}</code> as a token for dynamic domain replacement.
                    </p>
                </div>
                <div style="display:flex;align-items:center;gap:12px;">
                    <label style="display:inline-flex;align-items:center;gap:8px;font-size:13px;font-weight:600;cursor:pointer;background:var(--bg-2);padding:8px 12px;border-radius:6px;border:1px solid var(--line);">
                        <input type="checkbox" name="is_custom" value="1" id="is_custom_toggle" {{ $isCustom ? 'checked' : '' }}>
                        <span>Enable Custom Override</span>
                    </label>
                </div>
            </div>

            <div class="dash-field" style="margin-bottom:12px;">
                <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:6px;">
                    <label for="llm-content" style="font-weight:600;font-size:13px;">Markdown Content</label>
                    <div style="display:flex;gap:16px;font-size:12px;color:var(--muted);">
                        <span>Lines: <strong id="line-count">0</strong></span>
                        <span>Words: <strong id="word-count">0</strong></span>
                        <span>Characters: <strong id="char-count">0</strong></span>
                    </div>
                </div>
                <textarea 
                    id="llm-content" 
                    name="content" 
                    rows="26" 
                    style="font-family:ui-monospace,SFMono-Regular,Menlo,Monaco,Consolas,'Liberation Mono','Courier New',monospace;font-size:13px;line-height:1.6;width:100%;padding:14px;border-radius:6px;border:1px solid var(--line);background:var(--bg-2);color:var(--ink);resize:vertical;"
                    placeholder="Enter custom markdown..."
                >{{ old('content', $summaryContent) }}</textarea>
                @error('content')<p class="login-error">{{ $message }}</p>@enderror
            </div>

            <div style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:12px;padding-top:12px;border-top:1px solid var(--line);">
                <div style="display:flex;gap:8px;">
                    <button type="button" class="btn" id="btn-copy-editor" style="font-size:12px;">Copy Markdown</button>
                    <button type="button" class="btn" id="btn-load-default" style="font-size:12px;">Load Recommended Standard</button>
                </div>
                <div style="display:flex;gap:8px;">
                    <button type="button" class="btn danger" data-reset-modal-trigger style="font-size:12px;">Reset to Factory Default</button>
                    <button class="btn primary" type="submit">Save Changes</button>
                </div>
            </div>
        </div>
    </form>

    <form id="llm-feed-reset-form" method="post" action="{{ route('dashboard.llms.reset') }}" style="display:none;">
        @csrf
    </form>
</div>

<!-- TAB 2: Live Summary Preview -->
<div id="tab-summary-preview" class="dash-tab-content" style="display:none;">
    <div class="dash-card">
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:12px;flex-wrap:wrap;gap:10px;">
            <div>
                <h2 style="margin:0 0 4px 0;font-size:18px;">Live Rendered <code>/llms.txt</code> Output</h2>
                <p style="margin:0;font-size:13px;color:var(--muted);">This is the exact plain-text response returned over HTTP to AI agents.</p>
            </div>
            <div style="display:flex;gap:8px;">
                <button type="button" class="btn" id="btn-copy-summary" style="font-size:12px;">Copy Output</button>
                <a href="{{ url('/llms.txt') }}" target="_blank" class="btn" style="font-size:12px;">Open in New Tab &rarr;</a>
            </div>
        </div>
        <pre id="summary-raw-view" style="font-family:ui-monospace,SFMono-Regular,Menlo,Monaco,Consolas,monospace;font-size:13px;line-height:1.6;background:var(--bg-2);padding:16px;border-radius:6px;border:1px solid var(--line);white-space:pre-wrap;word-break:break-word;max-height:600px;overflow-y:auto;color:var(--ink);">{{ $liveSummary }}</pre>
    </div>
</div>

<!-- TAB 3: Full Feed Preview -->
<div id="tab-full-preview" class="dash-tab-content" style="display:none;">
    <div class="dash-card">
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:12px;flex-wrap:wrap;gap:10px;">
            <div>
                <h2 style="margin:0 0 4px 0;font-size:18px;">Full Technical Feed (<code>/llms-full.txt</code>)</h2>
                <p style="margin:0;font-size:13px;color:var(--muted);">Includes summary feed plus full SKU-level specifications and project case studies.</p>
            </div>
            <div style="display:flex;gap:8px;">
                <button type="button" class="btn" id="btn-copy-full" style="font-size:12px;">Copy Full Output</button>
                <a href="{{ url('/llms-full.txt') }}" target="_blank" class="btn" style="font-size:12px;">Open in New Tab &rarr;</a>
            </div>
        </div>
        <pre id="full-raw-view" style="font-family:ui-monospace,SFMono-Regular,Menlo,Monaco,Consolas,monospace;font-size:13px;line-height:1.6;background:var(--bg-2);padding:16px;border-radius:6px;border:1px solid var(--line);white-space:pre-wrap;word-break:break-word;max-height:600px;overflow-y:auto;color:var(--ink);">{{ $liveFull }}</pre>
    </div>
</div>

<!-- TAB 4: AI & GEO Guidelines -->
<div id="tab-guidelines" class="dash-tab-content" style="display:none;">
    <div class="dash-card" style="margin-bottom:20px;">
        <h2 style="margin:0 0 12px 0;font-size:18px;">Generative Engine Optimization (GEO) Standards</h2>
        <p style="font-size:14px;line-height:1.6;color:var(--ink);">
            The <code>/llms.txt</code> standard allows Large Language Models (LLMs) like OpenAI ChatGPT, Anthropic Claude, Perplexity AI, Google Gemini, and Microsoft Copilot to understand, summarize, and cite Azoogi's architectural lighting solutions and technical capabilities accurately.
        </p>

        <h3 style="font-size:15px;margin:20px 0 10px 0;">Key Best Practices for Architectural Lighting Feeds:</h3>
        <ul style="margin:0;padding-left:20px;font-size:14px;line-height:1.7;color:var(--muted);">
            <li><strong style="color:var(--ink);">Geographic &amp; Operational Clarity:</strong> Explicitly state HQ location (Matraville NSW) and clear business model (Sydney in-house assembly &amp; profile cutting, supply &amp; commissioning, with installation completed by electrical contractors).</li>
            <li><strong style="color:var(--ink);">Compliance &amp; Warranties:</strong> Mention Australian Standards compliance, EESS, and warranty terms (up to 5 years).</li>
            <li><strong style="color:var(--ink);">Engineering &amp; Photometric Tools:</strong> Highlight DIALux/AGi32 report support, direct IES file availability, and the LED Strip Calculator.</li>
            <li><strong style="color:var(--ink);">Structured Markdown Hierarchy:</strong> Use standard H1, H2, and H3 markdown tags with concise descriptive bullet points and markdown links.</li>
            <li><strong style="color:var(--ink);">Permitted AI Crawlers:</strong> Ensure robots.txt permits GPTBot, OAI-SearchBot, PerplexityBot, ClaudeBot, Google-Extended, and Bingbot.</li>
        </ul>

        <div style="margin-top:20px;padding:14px 18px;background:var(--bg-2);border-radius:6px;border:1px solid var(--line);">
            <div style="font-weight:600;font-size:13px;margin-bottom:4px;color:var(--ink);">Need CLI cache controls?</div>
            <p style="margin:0 0 8px 0;font-size:13px;color:var(--muted);">You can pre-warm or purge all GEO and sitemap caches using Artisan commands:</p>
            <code style="display:block;background:rgba(0,0,0,0.06);padding:8px 12px;border-radius:4px;font-size:12px;font-family:monospace;">php artisan geo:generate</code>
        </div>
    </div>
</div>

<!-- Reset Modal -->
<dialog id="reset-modal" class="dash-dialog" style="border:none;border-radius:8px;padding:24px;max-width:440px;background:var(--bg);color:var(--ink);box-shadow:0 10px 30px rgba(0,0,0,0.3);border:1px solid var(--line);">
    <h3 style="margin:0 0 10px 0;font-size:18px;">Reset LLM Feed to Factory Default?</h3>
    <p style="margin:0 0 20px 0;font-size:14px;color:var(--muted);line-height:1.5;">This will overwrite custom summary edits and restore the standard curated template with up-to-date compliance, company info, and range links.</p>
    <div style="display:flex;justify-content:flex-end;gap:10px;">
        <button type="button" class="btn" id="btn-cancel-reset">Cancel</button>
        <button type="button" class="btn danger" id="btn-confirm-reset">Reset to Default</button>
    </div>
</dialog>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Tab switching
    const tabs = document.querySelectorAll('[data-tab-target]');
    const tabContents = document.querySelectorAll('.dash-tab-content');

    tabs.forEach(tab => {
        tab.addEventListener('click', () => {
            const targetId = tab.getAttribute('data-tab-target');
            
            tabs.forEach(t => {
                t.classList.remove('is-active');
                t.style.borderBottomColor = 'transparent';
                t.style.color = 'var(--muted)';
            });

            tabContents.forEach(c => {
                c.style.display = 'none';
            });

            tab.classList.add('is-active');
            tab.style.borderBottomColor = 'var(--accent)';
            tab.style.color = 'var(--ink)';

            const activeContent = document.getElementById(targetId);
            if (activeContent) {
                activeContent.style.display = 'block';
            }
        });
    });

    // Character / Word / Line Counters
    const textarea = document.getElementById('llm-content');
    const charCount = document.getElementById('char-count');
    const wordCount = document.getElementById('word-count');
    const lineCount = document.getElementById('line-count');

    function updateCounters() {
        if (!textarea) return;
        const text = textarea.value || '';
        charCount.textContent = text.length.toLocaleString();
        const words = text.trim() ? text.trim().split(/\s+/).length : 0;
        wordCount.textContent = words.toLocaleString();
        const lines = text ? text.split(/\r\n|\r|\n/).length : 0;
        lineCount.textContent = lines.toLocaleString();
    }

    if (textarea) {
        textarea.addEventListener('input', updateCounters);
        updateCounters();
    }

    // Default template payload for client side restore
    const defaultTemplate = @json($defaultSummary);

    const btnLoadDefault = document.getElementById('btn-load-default');
    if (btnLoadDefault && textarea) {
        btnLoadDefault.addEventListener('click', () => {
            if (confirm('Load recommended standard template into editor? (Unsaved edits will be replaced)')) {
                textarea.value = defaultTemplate;
                updateCounters();
                const customToggle = document.getElementById('is_custom_toggle');
                if (customToggle) customToggle.checked = true;
            }
        });
    }

    // Copy handlers
    function copyText(text, btn) {
        navigator.clipboard.writeText(text).then(() => {
            const originalText = btn.textContent;
            btn.textContent = 'Copied!';
            setTimeout(() => { btn.textContent = originalText; }, 2000);
        });
    }

    const btnCopyEditor = document.getElementById('btn-copy-editor');
    if (btnCopyEditor && textarea) {
        btnCopyEditor.addEventListener('click', () => copyText(textarea.value, btnCopyEditor));
    }

    const btnCopySummary = document.getElementById('btn-copy-summary');
    const summaryRawView = document.getElementById('summary-raw-view');
    if (btnCopySummary && summaryRawView) {
        btnCopySummary.addEventListener('click', () => copyText(summaryRawView.textContent, btnCopySummary));
    }

    const btnCopyFull = document.getElementById('btn-copy-full');
    const fullRawView = document.getElementById('full-raw-view');
    if (btnCopyFull && fullRawView) {
        btnCopyFull.addEventListener('click', () => copyText(fullRawView.textContent, btnCopyFull));
    }

    // Reset Modal
    const resetModal = document.getElementById('reset-modal');
    const resetTrigger = document.querySelector('[data-reset-modal-trigger]');
    const btnCancelReset = document.getElementById('btn-cancel-reset');
    const btnConfirmReset = document.getElementById('btn-confirm-reset');
    const resetForm = document.getElementById('llm-feed-reset-form');

    if (resetTrigger && resetModal) {
        resetTrigger.addEventListener('click', () => resetModal.showModal());
    }
    if (btnCancelReset && resetModal) {
        btnCancelReset.addEventListener('click', () => resetModal.close());
    }
    if (btnConfirmReset && resetForm) {
        btnConfirmReset.addEventListener('click', () => resetForm.submit());
    }
});
</script>
@endsection
