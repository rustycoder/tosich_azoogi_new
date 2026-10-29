@extends('layouts.dashboard')

@section('title', 'New Datasheet Template')

@section('content')
<div class="dash-head">
    <div class="dash-crumb">
        <a href="{{ route('dashboard.datasheet-templates.index') }}">Datasheet Templates</a>
        <span>/</span>
        <span>New</span>
    </div>
    <div class="dash-head-title">
        <h1>New Datasheet Template</h1>
        <div class="dash-head-actions">
            <a href="{{ route('dashboard.datasheet-templates.index') }}" class="btn">Cancel</a>
            <button class="btn primary" type="submit" form="datasheet-template-form">Create Template</button>
        </div>
    </div>
    <p class="dash-lead">Build a custom HTML and CSS datasheet layout. Insert dynamic tokens and verify the appearance in the real-time A4 preview pane.</p>
</div>

@if (session('status'))
    <div class="dash-status" style="background:#e6f4ea;color:#137333;padding:12px 16px;border-radius:6px;margin-bottom:20px;font-size:14px;border:1px solid #ceead6;">
        {{ session('status') }}
    </div>
@endif

@if ($errors->any())
    <div class="dash-status" style="background:#fce8e6;color:#c5221f;padding:12px 16px;border-radius:6px;margin-bottom:20px;font-size:14px;border:1px solid #fad2cf;">
        <ul style="margin:0;padding-left:18px;">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<div class="email-template-layout">
    <!-- Left Column: Form & Code Editor -->
    <div style="display: flex; flex-direction: column; gap: 20px;">
        <form id="datasheet-template-form" class="dash-form" method="post" action="{{ route('dashboard.datasheet-templates.store') }}">
            @csrf

            <!-- Card 1: Template Metadata -->
            <div class="dash-card">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px;">
                    <h2 style="margin: 0;">Template Details</h2>
                    <span class="dash-pill is-inactive" style="font-size: 11px; padding: 3px 8px; font-weight: 600;">Draft (Inactive)</span>
                </div>
                
                <div class="dash-field">
                    <label for="name">Template Name <span style="color:var(--accent);">*</span></label>
                    <input id="name" name="name" value="{{ old('name', $template->name) }}" required placeholder="e.g. Architectural Specification A4">
                    @error('name')<p class="login-error">{{ $message }}</p>@enderror
                </div>

                <div class="dash-field">
                    <label for="slug">Identifier Slug <small class="dash-field-rec">(Optional, auto-generated from name)</small></label>
                    <input id="slug" name="slug" value="{{ old('slug', $template->slug) }}" placeholder="e.g. architectural-spec-a4">
                    @error('slug')<p class="login-error">{{ $message }}</p>@enderror
                </div>

                <div class="dash-field">
                    <label for="description">Description</label>
                    <textarea id="description" name="description" rows="2" placeholder="Brief summary of when this template is used">{{ old('description', $template->description) }}</textarea>
                    @error('description')<p class="login-error">{{ $message }}</p>@enderror
                </div>

                <div class="dash-field" style="margin-bottom: 0;">
                    <label style="display: flex; align-items: center; gap: 10px; cursor: pointer; user-select: none;">
                        <input type="checkbox" name="is_active" value="1" {{ old('is_active', $template->is_active) ? 'checked' : '' }} style="width: 18px; height: 18px; accent-color: var(--accent);">
                        <span style="font-size: 13px; font-weight: 600; color: var(--dash-ink);">Set as active template for live custom datasheet downloads</span>
                    </label>
                    <small style="color: var(--dash-muted); font-size: 12px; margin-top: 4px; display: block; margin-left: 28px;">Only one template can be active at a time. Activating this will replace the current active template.</small>
                </div>
            </div>

            <!-- Card 2: Placeholder Tokens -->
            <div class="dash-card">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px;">
                    <h2 style="margin: 0;">Dynamic Placeholder Tokens</h2>
                    <span style="font-size: 11.5px; color: var(--dash-muted);">Click chip to insert at cursor</span>
                </div>

                <div style="margin-bottom: 14px;">
                    <div style="display: flex; flex-wrap: wrap; gap: 6px;" id="token-chips">
                        @foreach ($template->available_tokens ?? [] as $token)
                            @php $tokenTag = '{{' . $token . '}}'; @endphp
                            <button type="button" class="token-chip" data-token="{{ $tokenTag }}" style="background: var(--rgba-hover); border: 1px solid var(--rgba-line); color: var(--dash-ink); padding: 4px 8px; border-radius: 4px; font-size: 11.5px; font-family: ui-monospace, monospace; cursor: pointer; transition: all 0.15s;" title="Click to insert {{ $tokenTag }}">
                                + {{ $tokenTag }}
                            </button>
                        @endforeach
                    </div>
                </div>

                <div class="dash-field" style="margin-bottom: 0;">
                    <label for="available_tokens">Configured Tokens <small class="dash-field-rec">(Comma-separated list of placeholder keys)</small></label>
                    <input id="available_tokens" name="available_tokens" value="{{ old('available_tokens', is_array($template->available_tokens) ? implode(', ', $template->available_tokens) : '') }}" placeholder="product_name, product_code, specifications_table, ...">
                    <small style="color: var(--dash-muted); font-size: 12px; margin-top: 4px; display: block;">You can add custom placeholder tokens here to expose them to your template interpolation engine.</small>
                </div>
            </div>

            <!-- Card 3: Tabbed Code Editor (HTML & CSS) -->
            <div class="dash-card">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px; border-bottom: 1px solid var(--rgba-line); padding-bottom: 8px;">
                    <!-- Editor Tabs (Icon Buttons) -->
                    <div style="display: flex; align-items: center; gap: 6px;">
                        <button type="button" class="btn primary" id="tab-btn-html" title="HTML Structure" aria-label="HTML Structure" style="display: inline-flex; align-items: center; justify-content: center; width: 34px; height: 32px; padding: 0; border-radius: 4px;">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" style="width: 16px; height: 16px;" aria-hidden="true">
                                <polyline points="16 18 22 12 16 6"/>
                                <polyline points="8 6 2 12 8 18"/>
                            </svg>
                        </button>
                        <button type="button" class="btn" id="tab-btn-css" title="Custom CSS" aria-label="Custom CSS" style="display: inline-flex; align-items: center; justify-content: center; width: 34px; height: 32px; padding: 0; border-radius: 4px;">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" style="width: 16px; height: 16px;" aria-hidden="true">
                                <path d="M12 20h9"/>
                                <path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5z"/>
                            </svg>
                        </button>
                        <span id="active-tab-label" style="font-size: 11px; font-weight: 700; color: var(--dash-muted); margin-left: 4px; text-transform: uppercase; letter-spacing: 0.6px;">HTML</span>
                    </div>

                    <div style="display: flex; align-items: center; gap: 10px;">
                        <span id="sync-status" style="font-size: 11.5px; color: var(--dash-muted);">Live preview ready</span>
                        <button type="button" class="btn" id="btn-refresh-preview" title="Refresh Preview" aria-label="Refresh Preview" style="display: inline-flex; align-items: center; justify-content: center; width: 34px; height: 32px; padding: 0; border-radius: 4px;">
                            <svg id="refresh-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width: 15px; height: 15px; transition: transform 0.4s ease;" aria-hidden="true">
                                <path d="M21.5 2v6h-6M21.34 15.57a10 10 0 1 1-.57-8.38l5.67-5.67"/>
                            </svg>
                        </button>
                    </div>
                </div>

                <!-- HTML Editor Panel -->
                <div id="panel-html" class="dash-field" style="margin-bottom: 0;">
                    <textarea id="body_html" name="body_html" rows="22" spellcheck="false" style="font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace; font-size: 12.5px; line-height: 1.5; white-space: pre; tab-size: 2;" required>{{ old('body_html', $template->body_html) }}</textarea>
                    @error('body_html')<p class="login-error">{{ $message }}</p>@enderror
                </div>

                <!-- CSS Editor Panel -->
                <div id="panel-css" class="dash-field" style="margin-bottom: 0; display: none;">
                    <textarea id="custom_css" name="custom_css" rows="22" spellcheck="false" placeholder="/* Add custom CSS rules here to override or extend styles */" style="font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace; font-size: 12.5px; line-height: 1.5; white-space: pre; tab-size: 2;">{{ old('custom_css', $template->custom_css) }}</textarea>
                    @error('custom_css')<p class="login-error">{{ $message }}</p>@enderror
                </div>
            </div>
        </form>
    </div>

    <!-- Right Column: Sticky Live A4 Preview -->
    <div style="position: sticky; top: 20px; display: flex; flex-direction: column; gap: 12px;">
        <div class="dash-card" style="padding: 16px;">
            <div style="display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid var(--rgba-line); padding-bottom: 10px; margin-bottom: 12px;">
                <div style="font-size: 13px; font-weight: 600; color: var(--dash-ink); display: flex; align-items: center; gap: 6px;">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" style="width: 15px; height: 15px;"><rect x="3" y="3" width="18" height="18" rx="2"/><path d="M3 9h18M9 21V9"/></svg>
                    Live A4 Preview
                </div>
                <div style="display: flex; align-items: center; gap: 8px;">
                    <span style="font-size: 11px; background: var(--rgba-hover); padding: 3px 8px; border-radius: 12px; color: var(--dash-muted);">Sample Data</span>
                    <button type="button" class="btn" id="btn-test-print" style="font-size: 11px; padding: 3px 8px; min-height: unset; line-height: 1.2;" title="Simulate print dialog">
                        Print / PDF
                    </button>
                </div>
            </div>

            <div style="border: 1px solid var(--rgba-line); border-radius: 6px; overflow: hidden; background: #525659; display: flex; justify-content: center; padding: 12px; box-shadow: inset 0 2px 6px rgba(0,0,0,0.25);">
                <iframe id="preview-frame" style="width: 100%; height: 740px; border: none; display: block; background: #ffffff; border-radius: 2px; box-shadow: 0 4px 12px rgba(0,0,0,0.3);" title="Datasheet Live Preview"></iframe>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    var previewFrame = document.getElementById('preview-frame');
    var bodyHtmlInput = document.getElementById('body_html');
    var customCssInput = document.getElementById('custom_css');
    var btnRefresh = document.getElementById('btn-refresh-preview');
    var btnTestPrint = document.getElementById('btn-test-print');
    var tokenChips = document.querySelectorAll('.token-chip');
    var syncStatus = document.getElementById('sync-status');
    var tabBtnHtml = document.getElementById('tab-btn-html');
    var tabBtnCss = document.getElementById('tab-btn-css');
    var panelHtml = document.getElementById('panel-html');
    var panelCss = document.getElementById('panel-css');
    var debounceTimer = null;
    var activeEditor = bodyHtmlInput;

    var activeTabLabel = document.getElementById('active-tab-label');
    var refreshIcon = document.getElementById('refresh-icon');

    // Tabs toggle
    tabBtnHtml.addEventListener('click', function () {
        tabBtnHtml.classList.add('primary');
        tabBtnCss.classList.remove('primary');
        panelHtml.style.display = 'block';
        panelCss.style.display = 'none';
        activeEditor = bodyHtmlInput;
        if (activeTabLabel) activeTabLabel.textContent = 'HTML';
    });

    tabBtnCss.addEventListener('click', function () {
        tabBtnCss.classList.add('primary');
        tabBtnHtml.classList.remove('primary');
        panelCss.style.display = 'block';
        panelHtml.style.display = 'none';
        activeEditor = customCssInput;
        if (activeTabLabel) activeTabLabel.textContent = 'CSS';
    });

    // Initial frame render
    updatePreviewFrame(@json($previewHtml));

    function updatePreviewFrame(html) {
        if (!previewFrame) return;
        var doc = previewFrame.contentDocument || previewFrame.contentWindow.document;
        doc.open();
        doc.write(html);
        doc.close();
        if (syncStatus) syncStatus.textContent = 'Preview updated ' + new Date().toLocaleTimeString([], { hour: '2-digit', minute: '2-digit', second: '2-digit' });
        if (refreshIcon) refreshIcon.style.transform = '';
    }

    function refreshPreviewAjax() {
        if (syncStatus) syncStatus.textContent = 'Updating preview...';
        if (refreshIcon) refreshIcon.style.transform = 'rotate(360deg)';
        var token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
        fetch('{{ route('dashboard.datasheet-templates.preview') }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': token,
                'Accept': 'application/json'
            },
            body: JSON.stringify({
                body_html: bodyHtmlInput ? bodyHtmlInput.value : '',
                custom_css: customCssInput ? customCssInput.value : ''
            })
        })
        .then(function (r) { return r.json(); })
        .then(function (data) {
            if (data.rendered) {
                updatePreviewFrame(data.rendered);
            }
        })
        .catch(function (err) {
            console.error('Preview update error:', err);
            if (syncStatus) syncStatus.textContent = 'Preview update failed';
        });
    }

    function schedulePreviewRefresh() {
        if (syncStatus) syncStatus.textContent = 'Typing...';
        clearTimeout(debounceTimer);
        debounceTimer = setTimeout(refreshPreviewAjax, 600);
    }

    if (btnRefresh) {
        btnRefresh.addEventListener('click', refreshPreviewAjax);
    }

    if (bodyHtmlInput) {
        bodyHtmlInput.addEventListener('input', schedulePreviewRefresh);
    }

    if (customCssInput) {
        customCssInput.addEventListener('input', schedulePreviewRefresh);
    }

    // Token insertion into active editor
    tokenChips.forEach(function (chip) {
        chip.addEventListener('click', function () {
            var token = this.getAttribute('data-token');
            var target = activeEditor || bodyHtmlInput;
            if (!target) return;

            var start = target.selectionStart;
            var end = target.selectionEnd;
            var text = target.value;

            target.value = text.substring(0, start) + token + text.substring(end);
            target.focus();
            target.selectionStart = target.selectionEnd = start + token.length;

            refreshPreviewAjax();
        });
    });

    // Test print button
    if (btnTestPrint && previewFrame) {
        btnTestPrint.addEventListener('click', function () {
            if (previewFrame.contentWindow) {
                previewFrame.contentWindow.focus();
                previewFrame.contentWindow.print();
            }
        });
    }
});
</script>
@endpush
