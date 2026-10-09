@extends('layouts.dashboard')

@section('title', 'AI Models')

@section('content')
<div class="dash-head">
    <div class="dash-crumb">
        <a href="{{ route('dashboard.home') }}">Dashboard</a>
        <span>/</span>
        <span>AI</span>
        <span>/</span>
        <span>Models</span>
    </div>
    <div class="dash-head-title">
        <h1>AI Models</h1>
        <div class="dash-head-actions">
            <button class="btn primary" type="submit" form="ai-config-form">Save Model Settings</button>
        </div>
    </div>
    <p class="dash-lead">Configure AI models (Anthropic Claude 3.5 Sonnet, Google Gemini 2.5 Flash, OpenAI GPT-4o Mini), manage API keys, and test live diagnostic connections.</p>
</div>

<!-- Copy Notification Toast (Floating) -->
<div id="ai-copy-toast" style="display:none;position:fixed;bottom:24px;right:24px;background:#137333;color:#ffffff;padding:10px 18px;border-radius:8px;font-size:13px;font-weight:600;box-shadow:0 6px 20px rgba(0,0,0,0.3);z-index:10000;display:flex;align-items:center;gap:8px;transition:opacity 0.3s ease;opacity:0;">
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" style="width:16px;height:16px;"><polyline points="20 6 9 17 4 12"/></svg>
    <span id="ai-copy-toast-msg">API Key copied to clipboard!</span>
</div>

@if (session('status'))
    <div class="dash-status" style="background:#e6f4ea;color:#137333;padding:12px 16px;border-radius:6px;margin-bottom:20px;font-size:14px;border:1px solid #ceead6;display:flex;align-items:center;gap:8px;">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:18px;height:18px;flex-shrink:0;"><polyline points="20 6 9 17 4 12"/></svg>
        <span>{{ session('status') }}</span>
    </div>
@endif

@if ($errors->any())
    <div class="dash-status" style="background:#fce8e6;color:#c5221f;padding:12px 16px;border-radius:6px;margin-bottom:20px;font-size:14px;border:1px solid #fad2cf;">
        <div style="font-weight:600;margin-bottom:4px;">Please fix the following issues:</div>
        <ul style="margin:0;padding-left:18px;">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<!-- Quick Metrics Bar -->
<div class="dash-grid" style="display:grid;grid-template-columns:repeat(auto-fit,minmax(220px,1fr));gap:16px;margin-bottom:24px;">
    <div class="dash-card" style="padding:16px 20px;">
        <div style="font-size:12px;color:var(--muted);text-transform:uppercase;letter-spacing:0.05em;font-weight:600;margin-bottom:6px;">Active AI Model Engine</div>
        <div style="display:flex;align-items:center;gap:8px;">
            <span class="dash-pill-active" style="background:#e6f4ea;color:#137333;border-color:#ceead6;text-transform:capitalize;font-weight:700;">
                @if(isset($customProviders[$aiConfig['driver'] ?? '']))
                    {{ $customProviders[$aiConfig['driver']]['name'] }} (Custom)
                @else
                    {{ $aiConfig['driver'] ?? 'gemini' }}
                @endif
            </span>
        </div>
        <div style="font-size:11px;color:var(--muted);margin-top:6px;font-family:monospace;word-break:break-all;">
            @if(isset($customProviders[$aiConfig['driver'] ?? '']))
                {{ $customProviders[$aiConfig['driver']]['model'] ?? 'custom-model' }}
            @elseif(($aiConfig['driver'] ?? '') === 'anthropic')
                {{ $aiConfig['anthropic_model'] ?? 'claude-3-5-sonnet-20241022' }}
            @elseif(($aiConfig['driver'] ?? '') === 'openrouter')
                {{ $aiConfig['openrouter_model'] ?? 'anthropic/claude-3.5-sonnet' }}
            @elseif(($aiConfig['driver'] ?? '') === 'openai')
                {{ $aiConfig['openai_model'] ?? 'gpt-4o-mini' }}
            @else
                {{ $aiConfig['gemini_model'] ?? 'gemini-2.5-flash' }}
            @endif
        </div>
    </div>

    <div class="dash-card" style="padding:16px 20px;">
        <div style="font-size:12px;color:var(--muted);text-transform:uppercase;letter-spacing:0.05em;font-weight:600;margin-bottom:6px;">Total Tokens Consumed</div>
        <div style="font-size:24px;font-weight:700;color:var(--accent);font-family:monospace;">{{ number_format($totalTokens ?? 0) }}</div>
        <div style="font-size:11px;color:var(--muted);margin-top:4px;">Across all visitor sessions</div>
    </div>

    <div class="dash-card" style="padding:16px 20px;">
        <div style="font-size:12px;color:var(--muted);text-transform:uppercase;letter-spacing:0.05em;font-weight:600;margin-bottom:6px;">Total Estimated Spend</div>
        <div style="font-size:24px;font-weight:700;color:#10b981;font-family:monospace;">${{ number_format($totalCost ?? 0, 4) }} <span style="font-size:12px;font-weight:normal;color:var(--muted);">USD</span></div>
        <div style="font-size:11px;color:var(--muted);margin-top:4px;">Real-time computed LLM cost</div>
    </div>

    <div class="dash-card" style="padding:16px 20px;">
        <div style="font-size:12px;color:var(--muted);text-transform:uppercase;letter-spacing:0.05em;font-weight:600;margin-bottom:6px;">Total AI Providers</div>
        <div style="font-size:24px;font-weight:700;color:var(--ink);">{{ 4 + count($customProviders) }}</div>
        <div style="font-size:11px;color:var(--muted);margin-top:4px;">4 Built-in + {{ count($customProviders) }} Custom</div>
    </div>
</div>

<div style="display:grid;grid-template-columns:1fr;gap:24px;">
    <!-- AI Provider Selection & Key Settings -->
    <form id="ai-config-form" class="dash-form" method="post" action="{{ route('dashboard.ai.update') }}">
        @csrf
        @method('put')

        <div class="dash-card" style="margin-bottom:20px;">
            <div style="margin-bottom:20px;">
                <h2 style="margin:0 0 6px 0;font-size:18px;">Select Active AI Provider &amp; Enter API Keys</h2>
                <p style="margin:0;font-size:13px;color:var(--muted);max-width:800px;line-height:1.5;">
                    Choose the active provider for the public chat assistant by selecting its radio button. All API keys entered below are saved directly into the database. You can click the <strong>Eye icon</strong> to view any key or the <strong>Copy icon</strong> to copy it.
                </p>
            </div>

            <!-- Provider Cards Selector -->
            <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(300px,1fr));gap:20px;margin-bottom:24px;">
                <!-- Claude / Anthropic Option -->
                <div class="provider-radio-card" style="border:2px solid {{ ($aiConfig['driver'] ?? 'anthropic') === 'anthropic' ? 'var(--accent)' : 'var(--dash-line)' }};background:var(--dash-card);border-radius:8px;padding:20px;position:relative;display:flex;flex-direction:column;justify-content:space-between;">
                    <div>
                        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:12px;">
                            <label style="display:flex;align-items:center;gap:10px;cursor:pointer;margin:0;white-space:nowrap;text-transform:none;letter-spacing:normal;">
                                <input type="radio" name="driver" value="anthropic" {{ ($aiConfig['driver'] ?? 'anthropic') === 'anthropic' ? 'checked' : '' }} style="accent-color:var(--accent);width:16px;height:16px;">
                                <span style="font-weight:700;font-size:15px;color:var(--dash-ink);white-space:nowrap;">Anthropic Claude</span>
                            </label>
                        </div>
                        <p style="margin:0 0 16px 0;font-size:12px;color:var(--dash-muted);line-height:1.4;">
                            Elite technical accuracy, intelligent tool parsing, and structured architectural advice.
                        </p>

                        <!-- API Key Input -->
                        <div class="dash-field" style="margin-bottom:14px;">
                            <label class="dash-label" style="display:flex;justify-content:space-between;align-items:center;">
                                <span>Anthropic API Key</span>
                                <span style="text-transform:none;font-weight:400;color:var(--dash-muted);font-size:10.5px;">(Database Stored)</span>
                            </label>
                            <div style="position:relative;display:flex;align-items:center;">
                                <input type="password" name="anthropic_api_key" id="input_anthropic_api_key" class="dash-input" value="{{ $aiConfig['anthropic_api_key'] ?? '' }}" placeholder="Paste Anthropic API Key (sk-ant-...)" style="padding-right:72px;font-family:monospace;">
                                <div style="position:absolute;right:8px;display:flex;align-items:center;gap:4px;">
                                    <button type="button" class="btn js-toggle-key-visibility" data-target="input_anthropic_api_key" style="background:none;border:none;cursor:pointer;color:var(--dash-muted);padding:4px;display:flex;align-items:center;" title="View / Hide API Key">
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:15px;height:15px;"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                                    </button>
                                    <button type="button" class="btn js-copy-key-btn" data-target="input_anthropic_api_key" style="background:none;border:none;cursor:pointer;color:var(--dash-muted);padding:4px;display:flex;align-items:center;" title="Copy API Key to clipboard">
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:15px;height:15px;"><rect x="9" y="9" width="13" height="13" rx="2" ry="2"/><path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"/></svg>
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- Model Selector -->
                        <div class="dash-field" style="margin-bottom:0;">
                            <label class="dash-label" for="input_anthropic_model">Model Name</label>
                            <div class="dash-select-wrap" style="width:100%;">
                                <select name="anthropic_model" id="input_anthropic_model" class="dash-select" style="width:100%;font-family:monospace;font-size:12.5px;">
                                    <option value="claude-3-7-sonnet-latest" {{ old('anthropic_model', $aiConfig['anthropic_model'] ?? 'claude-3-5-sonnet-20241022') === 'claude-3-7-sonnet-latest' ? 'selected' : '' }}>claude-3-7-sonnet-latest (Recommended - Hybrid Reasoning Flagship)</option>
                                    <option value="claude-3-7-sonnet-20250219" {{ old('anthropic_model', $aiConfig['anthropic_model'] ?? '') === 'claude-3-7-sonnet-20250219' ? 'selected' : '' }}>claude-3-7-sonnet-20250219 (Pinned Release)</option>
                                    <option value="claude-3-5-sonnet-20241022" {{ old('anthropic_model', $aiConfig['anthropic_model'] ?? 'claude-3-5-sonnet-20241022') === 'claude-3-5-sonnet-20241022' ? 'selected' : '' }}>claude-3-5-sonnet-20241022 (High Quality)</option>
                                    <option value="claude-3-5-sonnet-latest" {{ old('anthropic_model', $aiConfig['anthropic_model'] ?? '') === 'claude-3-5-sonnet-latest' ? 'selected' : '' }}>claude-3-5-sonnet-latest</option>
                                    <option value="claude-3-5-haiku-20241022" {{ old('anthropic_model', $aiConfig['anthropic_model'] ?? '') === 'claude-3-5-haiku-20241022' ? 'selected' : '' }}>claude-3-5-haiku-20241022 (Fast &amp; Economical)</option>
                                    <option value="claude-3-5-haiku-latest" {{ old('anthropic_model', $aiConfig['anthropic_model'] ?? '') === 'claude-3-5-haiku-latest' ? 'selected' : '' }}>claude-3-5-haiku-latest</option>
                                    <option value="claude-3-opus-20240229" {{ old('anthropic_model', $aiConfig['anthropic_model'] ?? '') === 'claude-3-opus-20240229' ? 'selected' : '' }}>claude-3-opus-20240229 (Deep Complex Reasoning)</option>
                                    <option value="claude-3-haiku-20240307" {{ old('anthropic_model', $aiConfig['anthropic_model'] ?? '') === 'claude-3-haiku-20240307' ? 'selected' : '' }}>claude-3-haiku-20240307</option>
                                    <option value="claude-3-sonnet-20240229" {{ old('anthropic_model', $aiConfig['anthropic_model'] ?? '') === 'claude-3-sonnet-20240229' ? 'selected' : '' }}>claude-3-sonnet-20240229</option>
                                    @php
                                        $currAnthropic = old('anthropic_model', $aiConfig['anthropic_model'] ?? 'claude-3-5-sonnet-20241022');
                                        $standardAnthropic = ['claude-3-7-sonnet-latest', 'claude-3-7-sonnet-20250219', 'claude-3-5-sonnet-20241022', 'claude-3-5-sonnet-latest', 'claude-3-5-haiku-20241022', 'claude-3-5-haiku-latest', 'claude-3-opus-20240229', 'claude-3-haiku-20240307', 'claude-3-sonnet-20240229'];
                                    @endphp
                                    @if(!in_array($currAnthropic, $standardAnthropic) && filled($currAnthropic))
                                        <option value="{{ $currAnthropic }}" selected>{{ $currAnthropic }} (Custom)</option>
                                    @endif
                                </select>
                                <svg class="dash-select-chevron" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><polyline points="6 9 12 15 18 9"/></svg>
                            </div>

                            <!-- Diagnostic Test -->
                            <div style="margin-top:12px;">
                                <button type="button" class="btn js-card-diagnostic-btn" data-driver="anthropic" data-model-input="input_anthropic_model" style="width:100%;font-size:12px;padding:6px 12px;display:inline-flex;align-items:center;justify-content:center;gap:6px;background:var(--bg);border:1px solid var(--line);color:var(--ink);cursor:pointer;border-radius:6px;font-weight:600;">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:13px;height:13px;color:var(--accent);"><polyline points="22 12 18 12 15 21 9 3 6 12 2 12"/></svg>
                                    Diagnostic Ping Test
                                </button>
                                <div class="js-card-diagnostic-results" style="display:none;margin-top:8px;padding:10px 12px;border-radius:6px;background:var(--bg);border:1px solid var(--line);font-size:11px;font-family:monospace;line-height:1.4;">
                                    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:6px;">
                                        <span class="js-diag-status" style="font-weight:700;padding:2px 6px;border-radius:4px;"></span>
                                        <span class="js-diag-latency" style="color:var(--muted);"></span>
                                    </div>
                                    <pre class="js-diag-output" style="margin:0;white-space:pre-wrap;word-break:break-word;color:var(--ink);font-size:11px;font-family:monospace;max-height:120px;overflow-y:auto;"></pre>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- OpenRouter Option -->
                <div class="provider-radio-card" style="border:2px solid {{ ($aiConfig['driver'] ?? '') === 'openrouter' ? 'var(--accent)' : 'var(--dash-line)' }};background:var(--dash-card);border-radius:8px;padding:20px;position:relative;display:flex;flex-direction:column;justify-content:space-between;">
                    <div>
                        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:12px;">
                            <label style="display:flex;align-items:center;gap:10px;cursor:pointer;margin:0;white-space:nowrap;text-transform:none;letter-spacing:normal;">
                                <input type="radio" name="driver" value="openrouter" {{ ($aiConfig['driver'] ?? '') === 'openrouter' ? 'checked' : '' }} style="accent-color:var(--accent);width:16px;height:16px;">
                                <span style="font-weight:700;font-size:15px;color:var(--dash-ink);white-space:nowrap;">OpenRouter Gateway</span>
                            </label>
                        </div>
                        <p style="margin:0 0 16px 0;font-size:12px;color:var(--dash-muted);line-height:1.4;">
                            Single API key granting access to Claude, Llama 3.3, DeepSeek, Mistral, and more.
                        </p>

                        <!-- API Key Input -->
                        <div class="dash-field" style="margin-bottom:14px;">
                            <label class="dash-label" style="display:flex;justify-content:space-between;align-items:center;">
                                <span>OpenRouter API Key</span>
                                <span style="text-transform:none;font-weight:400;color:var(--dash-muted);font-size:10.5px;">(Database Stored)</span>
                            </label>
                            <div style="position:relative;display:flex;align-items:center;">
                                <input type="password" name="openrouter_api_key" id="input_openrouter_api_key" class="dash-input" value="{{ $aiConfig['openrouter_api_key'] ?? '' }}" placeholder="Paste OpenRouter API Key (sk-or-v1-...)" style="padding-right:72px;font-family:monospace;">
                                <div style="position:absolute;right:8px;display:flex;align-items:center;gap:4px;">
                                    <button type="button" class="btn js-toggle-key-visibility" data-target="input_openrouter_api_key" style="background:none;border:none;cursor:pointer;color:var(--dash-muted);padding:4px;display:flex;align-items:center;" title="View / Hide API Key">
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:15px;height:15px;"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                                    </button>
                                    <button type="button" class="btn js-copy-key-btn" data-target="input_openrouter_api_key" style="background:none;border:none;cursor:pointer;color:var(--dash-muted);padding:4px;display:flex;align-items:center;" title="Copy API Key to clipboard">
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:14px;height:14px;"><rect x="9" y="9" width="13" height="13" rx="2" ry="2"/><path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"/></svg>
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- Model Selector -->
                        <div class="dash-field" style="margin-bottom:0;">
                            <label class="dash-label" for="input_openrouter_model">Model Name</label>
                            <div class="dash-select-wrap" style="width:100%;">
                                <select name="openrouter_model" id="input_openrouter_model" class="dash-select" style="width:100%;font-family:monospace;font-size:12.5px;">
                                    <option value="anthropic/claude-3.7-sonnet" {{ old('openrouter_model', $aiConfig['openrouter_model'] ?? 'anthropic/claude-3.5-sonnet') === 'anthropic/claude-3.7-sonnet' ? 'selected' : '' }}>anthropic/claude-3.7-sonnet (Hybrid Reasoning Flagship)</option>
                                    <option value="anthropic/claude-3.5-sonnet" {{ old('openrouter_model', $aiConfig['openrouter_model'] ?? 'anthropic/claude-3.5-sonnet') === 'anthropic/claude-3.5-sonnet' ? 'selected' : '' }}>anthropic/claude-3.5-sonnet (High Quality)</option>
                                    <option value="anthropic/claude-3.5-haiku" {{ old('openrouter_model', $aiConfig['openrouter_model'] ?? '') === 'anthropic/claude-3.5-haiku' ? 'selected' : '' }}>anthropic/claude-3.5-haiku (Fast &amp; Economical)</option>
                                    <option value="google/gemini-2.5-flash" {{ old('openrouter_model', $aiConfig['openrouter_model'] ?? '') === 'google/gemini-2.5-flash' ? 'selected' : '' }}>google/gemini-2.5-flash (High Speed Multimodal)</option>
                                    <option value="google/gemini-2.5-pro" {{ old('openrouter_model', $aiConfig['openrouter_model'] ?? '') === 'google/gemini-2.5-pro' ? 'selected' : '' }}>google/gemini-2.5-pro (Advanced Multimodal)</option>
                                    <option value="google/gemini-2.0-flash-001" {{ old('openrouter_model', $aiConfig['openrouter_model'] ?? '') === 'google/gemini-2.0-flash-001' ? 'selected' : '' }}>google/gemini-2.0-flash-001</option>
                                    <option value="openai/gpt-4o" {{ old('openrouter_model', $aiConfig['openrouter_model'] ?? '') === 'openai/gpt-4o' ? 'selected' : '' }}>openai/gpt-4o (Flagship Omni)</option>
                                    <option value="openai/gpt-4o-mini" {{ old('openrouter_model', $aiConfig['openrouter_model'] ?? '') === 'openai/gpt-4o-mini' ? 'selected' : '' }}>openai/gpt-4o-mini (Fast &amp; Economical)</option>
                                    <option value="openai/o3-mini" {{ old('openrouter_model', $aiConfig['openrouter_model'] ?? '') === 'openai/o3-mini' ? 'selected' : '' }}>openai/o3-mini (STEM/Coding Reasoning)</option>
                                    <option value="openai/o1" {{ old('openrouter_model', $aiConfig['openrouter_model'] ?? '') === 'openai/o1' ? 'selected' : '' }}>openai/o1 (Deep Step-by-Step Reasoning)</option>
                                    <option value="deepseek/deepseek-r1" {{ old('openrouter_model', $aiConfig['openrouter_model'] ?? '') === 'deepseek/deepseek-r1' ? 'selected' : '' }}>deepseek/deepseek-r1 (Open Reasoning)</option>
                                    <option value="deepseek/deepseek-chat" {{ old('openrouter_model', $aiConfig['openrouter_model'] ?? '') === 'deepseek/deepseek-chat' ? 'selected' : '' }}>deepseek/deepseek-chat (Ultra-low Cost V3)</option>
                                    <option value="meta-llama/llama-3.3-70b-instruct" {{ old('openrouter_model', $aiConfig['openrouter_model'] ?? '') === 'meta-llama/llama-3.3-70b-instruct' ? 'selected' : '' }}>meta-llama/llama-3.3-70b-instruct (Open Weights Flagship)</option>
                                    <option value="meta-llama/llama-3.1-405b-instruct" {{ old('openrouter_model', $aiConfig['openrouter_model'] ?? '') === 'meta-llama/llama-3.1-405b-instruct' ? 'selected' : '' }}>meta-llama/llama-3.1-405b-instruct</option>
                                    <option value="mistralai/mistral-large-2411" {{ old('openrouter_model', $aiConfig['openrouter_model'] ?? '') === 'mistralai/mistral-large-2411' ? 'selected' : '' }}>mistralai/mistral-large-2411</option>
                                    <option value="qwen/qwen-2.5-72b-instruct" {{ old('openrouter_model', $aiConfig['openrouter_model'] ?? '') === 'qwen/qwen-2.5-72b-instruct' ? 'selected' : '' }}>qwen/qwen-2.5-72b-instruct</option>
                                    @php
                                        $currOr = old('openrouter_model', $aiConfig['openrouter_model'] ?? 'anthropic/claude-3.5-sonnet');
                                        $standardOr = ['anthropic/claude-3.7-sonnet', 'anthropic/claude-3.5-sonnet', 'anthropic/claude-3.5-haiku', 'google/gemini-2.5-flash', 'google/gemini-2.5-pro', 'google/gemini-2.0-flash-001', 'openai/gpt-4o', 'openai/gpt-4o-mini', 'openai/o3-mini', 'openai/o1', 'deepseek/deepseek-r1', 'deepseek/deepseek-chat', 'meta-llama/llama-3.3-70b-instruct', 'meta-llama/llama-3.1-405b-instruct', 'mistralai/mistral-large-2411', 'qwen/qwen-2.5-72b-instruct'];
                                    @endphp
                                    @if(!in_array($currOr, $standardOr) && filled($currOr))
                                        <option value="{{ $currOr }}" selected>{{ $currOr }} (Custom)</option>
                                    @endif
                                </select>
                                <svg class="dash-select-chevron" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><polyline points="6 9 12 15 18 9"/></svg>
                            </div>

                            <!-- Diagnostic Test -->
                            <div style="margin-top:12px;">
                                <button type="button" class="btn js-card-diagnostic-btn" data-driver="openrouter" data-model-input="input_openrouter_model" style="width:100%;font-size:12px;padding:6px 12px;display:inline-flex;align-items:center;justify-content:center;gap:6px;background:var(--bg);border:1px solid var(--line);color:var(--ink);cursor:pointer;border-radius:6px;font-weight:600;">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:13px;height:13px;color:var(--accent);"><polyline points="22 12 18 12 15 21 9 3 6 12 2 12"/></svg>
                                    Diagnostic Ping Test
                                </button>
                                <div class="js-card-diagnostic-results" style="display:none;margin-top:8px;padding:10px 12px;border-radius:6px;background:var(--bg);border:1px solid var(--line);font-size:11px;font-family:monospace;line-height:1.4;">
                                    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:6px;">
                                        <span class="js-diag-status" style="font-weight:700;padding:2px 6px;border-radius:4px;"></span>
                                        <span class="js-diag-latency" style="color:var(--muted);"></span>
                                    </div>
                                    <pre class="js-diag-output" style="margin:0;white-space:pre-wrap;word-break:break-word;color:var(--ink);font-size:11px;font-family:monospace;max-height:120px;overflow-y:auto;"></pre>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Gemini Option -->
                <div class="provider-radio-card" style="border:2px solid {{ ($aiConfig['driver'] ?? '') === 'gemini' ? 'var(--accent)' : 'var(--dash-line)' }};background:var(--dash-card);border-radius:8px;padding:20px;position:relative;display:flex;flex-direction:column;justify-content:space-between;">
                    <div>
                        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:12px;">
                            <label style="display:flex;align-items:center;gap:10px;cursor:pointer;margin:0;white-space:nowrap;text-transform:none;letter-spacing:normal;">
                                <input type="radio" name="driver" value="gemini" {{ ($aiConfig['driver'] ?? '') === 'gemini' ? 'checked' : '' }} style="accent-color:var(--accent);width:16px;height:16px;">
                                <span style="font-weight:700;font-size:15px;color:var(--dash-ink);white-space:nowrap;">Google Gemini</span>
                            </label>
                        </div>
                        <p style="margin:0 0 16px 0;font-size:12px;color:var(--dash-muted);line-height:1.4;">
                            High-speed multimodal models with fast reasoning and built-in function calling.
                        </p>

                        <!-- API Key Input -->
                        <div class="dash-field" style="margin-bottom:14px;">
                            <label class="dash-label" style="display:flex;justify-content:space-between;align-items:center;">
                                <span>Gemini API Key</span>
                                <span style="text-transform:none;font-weight:400;color:var(--dash-muted);font-size:10.5px;">(Database Stored)</span>
                            </label>
                            <div style="position:relative;display:flex;align-items:center;">
                                <input type="password" name="gemini_api_key" id="input_gemini_api_key" class="dash-input" value="{{ $aiConfig['gemini_api_key'] ?? '' }}" placeholder="Paste Gemini API Key (AIzaSy...)" style="padding-right:72px;font-family:monospace;">
                                <div style="position:absolute;right:8px;display:flex;align-items:center;gap:4px;">
                                    <button type="button" class="btn js-toggle-key-visibility" data-target="input_gemini_api_key" style="background:none;border:none;cursor:pointer;color:var(--dash-muted);padding:4px;display:flex;align-items:center;" title="View / Hide API Key">
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:15px;height:15px;"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                                    </button>
                                    <button type="button" class="js-copy-key-btn" data-target="input_gemini_api_key" style="background:none;border:none;cursor:pointer;color:var(--dash-muted);padding:4px;display:flex;align-items:center;" title="Copy API Key to clipboard">
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:14px;height:14px;"><rect x="9" y="9" width="13" height="13" rx="2" ry="2"/><path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"/></svg>
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- Model Selector -->
                        <div class="dash-field" style="margin-bottom:0;">
                            <label class="dash-label" for="input_gemini_model">Model Name</label>
                            <div class="dash-select-wrap" style="width:100%;">
                                <select name="gemini_model" id="input_gemini_model" class="dash-select" style="width:100%;font-family:monospace;font-size:12.5px;">
                                    <option value="gemini-2.5-flash" {{ old('gemini_model', $aiConfig['gemini_model'] ?? 'gemini-2.5-flash') === 'gemini-2.5-flash' ? 'selected' : '' }}>gemini-2.5-flash (Recommended - Fast &amp; Cost Effective)</option>
                                    <option value="gemini-2.5-pro" {{ old('gemini_model', $aiConfig['gemini_model'] ?? '') === 'gemini-2.5-pro' ? 'selected' : '' }}>gemini-2.5-pro (High Reasoning)</option>
                                    <option value="gemini-2.0-flash" {{ old('gemini_model', $aiConfig['gemini_model'] ?? '') === 'gemini-2.0-flash' ? 'selected' : '' }}>gemini-2.0-flash (High Speed Next-Gen)</option>
                                    <option value="gemini-2.0-flash-lite" {{ old('gemini_model', $aiConfig['gemini_model'] ?? '') === 'gemini-2.0-flash-lite' ? 'selected' : '' }}>gemini-2.0-flash-lite (Ultra Low Latency)</option>
                                    <option value="gemini-2.0-pro-exp-02-05" {{ old('gemini_model', $aiConfig['gemini_model'] ?? '') === 'gemini-2.0-pro-exp-02-05' ? 'selected' : '' }}>gemini-2.0-pro-exp-02-05</option>
                                    <option value="gemini-1.5-flash" {{ old('gemini_model', $aiConfig['gemini_model'] ?? '') === 'gemini-1.5-flash' ? 'selected' : '' }}>gemini-1.5-flash</option>
                                    <option value="gemini-1.5-flash-8b" {{ old('gemini_model', $aiConfig['gemini_model'] ?? '') === 'gemini-1.5-flash-8b' ? 'selected' : '' }}>gemini-1.5-flash-8b (Cost-Optimized)</option>
                                    <option value="gemini-1.5-pro" {{ old('gemini_model', $aiConfig['gemini_model'] ?? '') === 'gemini-1.5-pro' ? 'selected' : '' }}>gemini-1.5-pro</option>
                                    @php
                                        $currGemini = old('gemini_model', $aiConfig['gemini_model'] ?? 'gemini-2.5-flash');
                                        $standardGemini = ['gemini-2.5-flash', 'gemini-2.5-pro', 'gemini-2.0-flash', 'gemini-2.0-flash-lite', 'gemini-2.0-pro-exp-02-05', 'gemini-1.5-flash', 'gemini-1.5-flash-8b', 'gemini-1.5-pro'];
                                    @endphp
                                    @if(!in_array($currGemini, $standardGemini) && filled($currGemini))
                                        <option value="{{ $currGemini }}" selected>{{ $currGemini }} (Custom)</option>
                                    @endif
                                </select>
                                <svg class="dash-select-chevron" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><polyline points="6 9 12 15 18 9"/></svg>
                            </div>

                            <!-- Diagnostic Test -->
                            <div style="margin-top:12px;">
                                <button type="button" class="btn js-card-diagnostic-btn" data-driver="gemini" data-model-input="input_gemini_model" style="width:100%;font-size:12px;padding:6px 12px;display:inline-flex;align-items:center;justify-content:center;gap:6px;background:var(--bg);border:1px solid var(--line);color:var(--ink);cursor:pointer;border-radius:6px;font-weight:600;">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:13px;height:13px;color:var(--accent);"><polyline points="22 12 18 12 15 21 9 3 6 12 2 12"/></svg>
                                    Diagnostic Ping Test
                                </button>
                                <div class="js-card-diagnostic-results" style="display:none;margin-top:8px;padding:10px 12px;border-radius:6px;background:var(--bg);border:1px solid var(--line);font-size:11px;font-family:monospace;line-height:1.4;">
                                    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:6px;">
                                        <span class="js-diag-status" style="font-weight:700;padding:2px 6px;border-radius:4px;"></span>
                                        <span class="js-diag-latency" style="color:var(--muted);"></span>
                                    </div>
                                    <pre class="js-diag-output" style="margin:0;white-space:pre-wrap;word-break:break-word;color:var(--ink);font-size:11px;font-family:monospace;max-height:120px;overflow-y:auto;"></pre>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- OpenAI Option -->
                <div class="provider-radio-card" style="border:2px solid {{ ($aiConfig['driver'] ?? '') === 'openai' ? 'var(--accent)' : 'var(--dash-line)' }};background:var(--dash-card);border-radius:8px;padding:20px;position:relative;display:flex;flex-direction:column;justify-content:space-between;">
                    <div>
                        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:12px;">
                            <label style="display:flex;align-items:center;gap:10px;cursor:pointer;margin:0;white-space:nowrap;text-transform:none;letter-spacing:normal;">
                                <input type="radio" name="driver" value="openai" {{ ($aiConfig['driver'] ?? '') === 'openai' ? 'checked' : '' }} style="accent-color:var(--accent);width:16px;height:16px;">
                                <span style="font-weight:700;font-size:15px;color:var(--dash-ink);white-space:nowrap;">OpenAI Direct</span>
                            </label>
                        </div>
                        <p style="margin:0 0 16px 0;font-size:12px;color:var(--dash-muted);line-height:1.4;">
                            Direct API connection to OpenAI models including GPT-4o and lightweight mini models.
                        </p>

                        <!-- API Key Input -->
                        <div class="dash-field" style="margin-bottom:14px;">
                            <label class="dash-label" style="display:flex;justify-content:space-between;align-items:center;">
                                <span>OpenAI API Key</span>
                                <span style="text-transform:none;font-weight:400;color:var(--dash-muted);font-size:10.5px;">(Database Stored)</span>
                            </label>
                            <div style="position:relative;display:flex;align-items:center;">
                                <input type="password" name="openai_api_key" id="input_openai_api_key" class="dash-input" value="{{ $aiConfig['openai_api_key'] ?? '' }}" placeholder="Paste OpenAI API Key (sk-...)" style="padding-right:72px;font-family:monospace;">
                                <div style="position:absolute;right:8px;display:flex;align-items:center;gap:4px;">
                                    <button type="button" class="js-toggle-key-visibility" data-target="input_openai_api_key" style="background:none;border:none;cursor:pointer;color:var(--dash-muted);padding:4px;display:flex;align-items:center;" title="View / Hide API Key">
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:15px;height:15px;"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                                    </button>
                                    <button type="button" class="js-copy-key-btn" data-target="input_openai_api_key" style="background:none;border:none;cursor:pointer;color:var(--dash-muted);padding:4px;display:flex;align-items:center;" title="Copy API Key to clipboard">
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:14px;height:14px;"><rect x="9" y="9" width="13" height="13" rx="2" ry="2"/><path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"/></svg>
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- Model Selector -->
                        <div class="dash-field" style="margin-bottom:0;">
                            <label class="dash-label" for="input_openai_model">Model Name</label>
                            <div class="dash-select-wrap" style="width:100%;">
                                <select name="openai_model" id="input_openai_model" class="dash-select" style="width:100%;font-family:monospace;font-size:12.5px;">
                                    <option value="gpt-4o-mini" {{ old('openai_model', $aiConfig['openai_model'] ?? 'gpt-4o-mini') === 'gpt-4o-mini' ? 'selected' : '' }}>gpt-4o-mini (Recommended - Fast &amp; Cost Effective)</option>
                                    <option value="gpt-4o" {{ old('openai_model', $aiConfig['openai_model'] ?? '') === 'gpt-4o' ? 'selected' : '' }}>gpt-4o (Full Flagship Omni)</option>
                                    <option value="gpt-4o-2024-11-20" {{ old('openai_model', $aiConfig['openai_model'] ?? '') === 'gpt-4o-2024-11-20' ? 'selected' : '' }}>gpt-4o-2024-11-20 (Pinned Release)</option>
                                    <option value="o3-mini" {{ old('openai_model', $aiConfig['openai_model'] ?? '') === 'o3-mini' ? 'selected' : '' }}>o3-mini (High Speed STEM/Coding Reasoning)</option>
                                    <option value="o1" {{ old('openai_model', $aiConfig['openai_model'] ?? '') === 'o1' ? 'selected' : '' }}>o1 (Deep Reasoning)</option>
                                    <option value="o1-mini" {{ old('openai_model', $aiConfig['openai_model'] ?? '') === 'o1-mini' ? 'selected' : '' }}>o1-mini (Fast Reasoning)</option>
                                    <option value="gpt-4.5-preview" {{ old('openai_model', $aiConfig['openai_model'] ?? '') === 'gpt-4.5-preview' ? 'selected' : '' }}>gpt-4.5-preview (Frontier Model)</option>
                                    <option value="gpt-4-turbo" {{ old('openai_model', $aiConfig['openai_model'] ?? '') === 'gpt-4-turbo' ? 'selected' : '' }}>gpt-4-turbo</option>
                                    <option value="gpt-3.5-turbo" {{ old('openai_model', $aiConfig['openai_model'] ?? '') === 'gpt-3.5-turbo' ? 'selected' : '' }}>gpt-3.5-turbo</option>
                                    @php
                                        $currOpenai = old('openai_model', $aiConfig['openai_model'] ?? 'gpt-4o-mini');
                                        $standardOpenai = ['gpt-4o-mini', 'gpt-4o', 'gpt-4o-2024-11-20', 'o3-mini', 'o1', 'o1-mini', 'gpt-4.5-preview', 'gpt-4-turbo', 'gpt-3.5-turbo'];
                                    @endphp
                                    @if(!in_array($currOpenai, $standardOpenai) && filled($currOpenai))
                                        <option value="{{ $currOpenai }}" selected>{{ $currOpenai }} (Custom)</option>
                                    @endif
                                </select>
                                <svg class="dash-select-chevron" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><polyline points="6 9 12 15 18 9"/></svg>
                            </div>

                            <!-- Diagnostic Test -->
                            <div style="margin-top:12px;">
                                <button type="button" class="btn js-card-diagnostic-btn" data-driver="openai" data-model-input="input_openai_model" style="width:100%;font-size:12px;padding:6px 12px;display:inline-flex;align-items:center;justify-content:center;gap:6px;background:var(--bg);border:1px solid var(--line);color:var(--ink);cursor:pointer;border-radius:6px;font-weight:600;">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:13px;height:13px;color:var(--accent);"><polyline points="22 12 18 12 15 21 9 3 6 12 2 12"/></svg>
                                    Diagnostic Ping Test
                                </button>
                                <div class="js-card-diagnostic-results" style="display:none;margin-top:8px;padding:10px 12px;border-radius:6px;background:var(--bg);border:1px solid var(--line);font-size:11px;font-family:monospace;line-height:1.4;">
                                    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:6px;">
                                        <span class="js-diag-status" style="font-weight:700;padding:2px 6px;border-radius:4px;"></span>
                                        <span class="js-diag-latency" style="color:var(--muted);"></span>
                                    </div>
                                    <pre class="js-diag-output" style="margin:0;white-space:pre-wrap;word-break:break-word;color:var(--ink);font-size:11px;font-family:monospace;max-height:120px;overflow-y:auto;"></pre>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Custom Providers Dynamic List -->
                @foreach($customProviders as $cpId => $cp)
                    @php
                        $cpModels = !empty($cp['models']) && is_array($cp['models'])
                            ? $cp['models']
                            : array_values(array_filter(array_map('trim', preg_split('/[,\n\r;]+/', $cp['model'] ?? ''))));
                        if (empty($cpModels)) {
                            $cpModels = [$cp['model'] ?? 'custom-model'];
                        }
                        $activeCpModel = $cp['model'] ?? $cpModels[0];
                    @endphp
                    <div class="provider-radio-card" style="border:2px solid {{ ($aiConfig['driver'] ?? '') === $cpId ? 'var(--accent)' : 'var(--dash-line)' }};background:var(--dash-card);border-radius:8px;padding:20px;position:relative;display:flex;flex-direction:column;justify-content:space-between;">
                        <div>
                            <div style="display:flex;justify-content:space-between;align-items:flex-start;margin-bottom:10px;">
                                <label style="display:flex;align-items:center;gap:10px;cursor:pointer;margin:0;white-space:nowrap;text-transform:none;letter-spacing:normal;">
                                    <input type="radio" name="driver" value="{{ $cpId }}" {{ ($aiConfig['driver'] ?? '') === $cpId ? 'checked' : '' }} style="accent-color:var(--accent);width:16px;height:16px;">
                                    <span style="font-weight:700;font-size:15px;color:var(--dash-ink);white-space:nowrap;">{{ $cp['name'] }}</span>
                                </label>
                                <span style="font-size:10px;padding:2px 6px;border-radius:4px;background:var(--card-bg);border:1px solid var(--line);text-transform:uppercase;font-weight:700;color:var(--accent);white-space:nowrap;">
                                    {{ strtoupper($cp['type'] ?? 'openai') }} API
                                </span>
                            </div>

                            <p style="margin:0 0 16px 0;font-size:12px;color:var(--dash-muted);line-height:1.4;">
                                {{ $cp['description'] ?: 'Custom endpoint configured by administrator.' }}
                            </p>

                            <div style="background:var(--bg);padding:10px;border-radius:6px;border:1px solid var(--line);margin-bottom:12px;font-size:11px;font-family:monospace;">
                                <div style="color:var(--muted);margin-bottom:8px;"><strong style="color:var(--ink);">Endpoint:</strong> {{ $cp['base_url'] ?: 'Provider default base URL' }}</div>
                                
                                <div style="color:var(--muted);margin-bottom:8px;">
                                    <strong style="color:var(--ink);display:block;margin-bottom:4px;">Active Model:</strong>
                                    <div class="dash-select-wrap" style="width:100%;">
                                        <select name="custom_provider_models[{{ $cpId }}]" id="input_custom_model_{{ $cpId }}" class="dash-select" style="width:100%;font-family:monospace;font-size:12px;padding:4px 28px 4px 8px;height:auto;">
                                            @foreach($cpModels as $cm)
                                                <option value="{{ $cm }}" {{ $activeCpModel === $cm ? 'selected' : '' }}>{{ $cm }}</option>
                                            @endforeach
                                        </select>
                                        <svg class="dash-select-chevron" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><polyline points="6 9 12 15 18 9"/></svg>
                                    </div>
                                </div>

                                <div style="display:flex;align-items:center;justify-content:space-between;gap:8px;padding-top:6px;border-top:1px dashed var(--line);">
                                    <div style="overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">
                                        <strong style="color:var(--ink);">Key:</strong>
                                        <span id="cp_key_text_{{ $loop->index }}" data-real-key="{{ $cp['api_key'] ?? '' }}" data-masked="••••••••••••••••" style="color:var(--ink);">••••••••••••••••</span>
                                    </div>
                                    <div style="display:flex;gap:4px;flex-shrink:0;">
                                        <button type="button" class="btn js-toggle-span-visibility" data-target="cp_key_text_{{ $loop->index }}" style="padding:2px 6px;font-size:11px;background:none;border:1px solid var(--line);color:var(--muted);display:flex;align-items:center;" title="View / Hide API Key">
                                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:12px;height:12px;"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                                        </button>
                                        <button type="button" class="btn js-copy-raw-text-btn" data-copy="{{ $cp['api_key'] ?? '' }}" style="padding:2px 6px;font-size:11px;background:none;border:1px solid var(--line);color:var(--muted);display:flex;align-items:center;" title="Copy API Key to clipboard">
                                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:12px;height:12px;"><rect x="9" y="9" width="13" height="13" rx="2" ry="2"/><path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"/></svg>
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <!-- Diagnostic Test for Custom Provider -->
                            <div style="margin-bottom:12px;">
                                <button type="button" class="btn js-card-diagnostic-btn" data-driver="{{ $cpId }}" data-model-input="input_custom_model_{{ $cpId }}" style="width:100%;font-size:12px;padding:6px 12px;display:inline-flex;align-items:center;justify-content:center;gap:6px;background:var(--bg);border:1px solid var(--line);color:var(--ink);cursor:pointer;border-radius:6px;font-weight:600;">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:13px;height:13px;color:var(--accent);"><polyline points="22 12 18 12 15 21 9 3 6 12 2 12"/></svg>
                                    Diagnostic Ping Test
                                </button>
                                <div class="js-card-diagnostic-results" style="display:none;margin-top:8px;padding:10px 12px;border-radius:6px;background:var(--bg);border:1px solid var(--line);font-size:11px;font-family:monospace;line-height:1.4;">
                                    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:6px;">
                                        <span class="js-diag-status" style="font-weight:700;padding:2px 6px;border-radius:4px;"></span>
                                        <span class="js-diag-latency" style="color:var(--muted);"></span>
                                    </div>
                                    <pre class="js-diag-output" style="margin:0;white-space:pre-wrap;word-break:break-word;color:var(--ink);font-size:11px;font-family:monospace;max-height:120px;overflow-y:auto;"></pre>
                                </div>
                            </div>
                        </div>

                        <div style="display:flex;justify-content:flex-end;align-items:center;padding-top:10px;border-top:1px solid var(--line);">
                            <button type="button" class="btn js-delete-custom-provider" data-id="{{ $cpId }}" data-name="{{ $cp['name'] }}" style="padding:4px 8px;font-size:11px;color:#c5221f;border-color:rgba(197,34,31,0.3);background:none;">
                                Delete
                            </button>
                        </div>
                    </div>
                @endforeach

                <!-- Add New Provider Quick Card -->
                <div id="btn-card-add-provider" style="border:2px dashed var(--line);background:var(--card-bg);border-radius:8px;padding:24px;display:flex;flex-direction:column;align-items:center;justify-content:center;text-align:center;cursor:pointer;min-height:220px;transition:all 0.2s ease;">
                    <div style="width:44px;height:44px;border-radius:50%;background:var(--bg);display:flex;align-items:center;justify-content:center;margin-bottom:12px;border:1px solid var(--line);color:var(--accent);">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" style="width:22px;height:22px;"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                    </div>
                    <div style="font-weight:700;font-size:15px;color:var(--ink);margin-bottom:4px;">Add Custom AI Provider</div>
                    <p style="font-size:12px;color:var(--muted);margin:0;max-width:240px;line-height:1.4;">
                        Connect DeepSeek, Groq, Ollama Local, Together AI, Mistral, xAI, or any custom API endpoint.
                    </p>
                </div>
            </div>
        </div>
    </form>

</div>

<!-- Modal: Add Custom AI Provider -->
<div id="add-provider-modal" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,0.6);backdrop-filter:blur(6px);z-index:9999;align-items:center;justify-content:center;padding:20px;">
    <div style="background:var(--dash-card);border:1px solid var(--dash-line);border-radius:12px;width:100%;max-width:580px;box-shadow:0 24px 60px rgba(0,0,0,0.35);overflow:hidden;animation:fadeIn 0.2s ease;color:var(--dash-ink);">
        <div style="padding:20px 24px;border-bottom:1px solid var(--dash-line);display:flex;justify-content:space-between;align-items:center;">
            <div>
                <h3 style="margin:0 0 4px 0;font-size:18px;color:var(--dash-ink);">Add Custom AI Provider</h3>
                <p style="margin:0;font-size:12px;color:var(--dash-muted);">Connect any third-party or local OpenAI/Anthropic/Gemini compatible endpoint.</p>
            </div>
            <button type="button" id="btn-close-modal" style="background:none;border:none;color:var(--dash-muted);cursor:pointer;padding:4px;" aria-label="Close">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:20px;height:20px;"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
            </button>
        </div>

        <form method="post" action="{{ route('dashboard.ai.custom-provider.store') }}" style="padding:24px;">
            @csrf

            <!-- Provider Presets -->
            <div style="margin-bottom:18px;">
                <label style="font-size:11px;text-transform:uppercase;font-weight:700;color:var(--muted);letter-spacing:0.05em;display:block;margin-bottom:8px;">Quick Presets</label>
                <div style="display:flex;flex-wrap:wrap;gap:6px;">
                    <button type="button" class="btn js-preset-btn" data-name="DeepSeek" data-type="openai" data-url="https://api.deepseek.com/v1" data-model="deepseek-chat, deepseek-reasoner" data-desc="DeepSeek AI chat completions" style="padding:4px 10px;font-size:11px;">DeepSeek</button>
                    <button type="button" class="btn js-preset-btn" data-name="OpenWebUI" data-type="openai" data-url="https://ai.example.com/api" data-model="qwen2.5-coder:3b, llama3.2" data-desc="Self-hosted OpenWebUI proxy endpoint" data-openwebui="1" style="padding:4px 10px;font-size:11px;">OpenWebUI</button>
                    <button type="button" class="btn js-preset-btn" data-name="Groq" data-type="openai" data-url="https://api.groq.com/openai/v1" data-model="llama-3.3-70b-versatile, mixtral-8x7b-32768" data-desc="Groq Ultra-fast inference" style="padding:4px 10px;font-size:11px;">Groq</button>
                    <button type="button" class="btn js-preset-btn" data-name="Ollama (Local)" data-type="openai" data-url="http://localhost:11434/v1" data-model="llama3.2, mistral, qwen2.5" data-desc="Local self-hosted Ollama server" style="padding:4px 10px;font-size:11px;">Ollama</button>
                    <button type="button" class="btn js-preset-btn" data-name="Together AI" data-type="openai" data-url="https://api.together.xyz/v1" data-model="meta-llama/Llama-3.3-70B-Instruct-Turbo, mistralai/Mixtral-8x22B-Instruct-v0.1" data-desc="Together AI GPU inference cloud" style="padding:4px 10px;font-size:11px;">Together AI</button>
                    <button type="button" class="btn js-preset-btn" data-name="Mistral AI" data-type="openai" data-url="https://api.mistral.ai/v1" data-model="mistral-large-latest, mistral-small-latest" data-desc="Mistral AI official API" style="padding:4px 10px;font-size:11px;">Mistral AI</button>
                    <button type="button" class="btn js-preset-btn" data-name="xAI Grok" data-type="openai" data-url="https://api.x.ai/v1" data-model="grok-2-latest, grok-beta" data-desc="xAI Grok official API" style="padding:4px 10px;font-size:11px;">xAI Grok</button>
                </div>
            </div>

            <div style="display:grid;grid-template-columns:1fr 1fr;gap:14px;margin-bottom:14px;">
                <div class="dash-field" style="margin-bottom:0;">
                    <label class="dash-label" for="modal-name">Provider Display Name *</label>
                    <input type="text" name="name" id="modal-name" class="dash-input" required placeholder="e.g. DeepSeek AI">
                </div>

                <div class="dash-field" style="margin-bottom:0;">
                    <label class="dash-label" for="modal-type">API Protocol / Format *</label>
                    <select name="type" id="modal-type" class="dash-select" required>
                        <option value="openai">OpenAI Compatible (Most Common)</option>
                        <option value="anthropic">Anthropic Compatible</option>
                        <option value="gemini">Google Gemini Compatible</option>
                    </select>
                </div>
            </div>

            <div class="dash-field" style="margin-bottom:14px;">
                <label class="dash-label" for="modal-url">API Base Endpoint URL</label>
                <input type="url" name="base_url" id="modal-url" class="dash-input" placeholder="https://api.deepseek.com/v1" style="font-family:monospace;">
                <span style="font-size:11px;color:var(--dash-muted);margin-top:3px;display:block;">Base URL before <code>/chat/completions</code>. (e.g. <code>https://ai.domain.com/api</code> or <code>https://api.deepseek.com/v1</code>)</span>
            </div>

            <div class="dash-field" style="margin-bottom:14px;">
                <label class="dash-label" for="modal-key">API Key / Secret Token *</label>
                <div style="position:relative;display:flex;align-items:center;">
                    <input type="password" name="api_key" id="modal-key" class="dash-input" required placeholder="sk-..." style="padding-right:72px;font-family:monospace;">
                    <div style="position:absolute;right:8px;display:flex;align-items:center;gap:4px;">
                        <button type="button" class="btn js-toggle-key-visibility" data-target="modal-key" style="background:none;border:none;cursor:pointer;color:var(--dash-muted);padding:4px;display:flex;align-items:center;" title="View / Hide API Key">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:15px;height:15px;"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                        </button>
                        <button type="button" class="btn js-copy-key-btn" data-target="modal-key" style="background:none;border:none;cursor:pointer;color:var(--dash-muted);padding:4px;display:flex;align-items:center;" title="Copy API Key to clipboard">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:15px;height:15px;"><rect x="9" y="9" width="13" height="13" rx="2" ry="2"/><path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"/></svg>
                        </button>
                    </div>
                </div>
            </div>

            <div class="dash-field" style="margin-bottom:14px;">
                <label class="dash-label" for="modal-model">Model Identifier(s) *</label>
                <input type="text" name="model" id="modal-model" class="dash-input" required placeholder="e.g. deepseek-chat, deepseek-reasoner" style="font-family:monospace;">
                <span style="font-size:11px;color:var(--dash-muted);margin-top:3px;display:block;">You can enter multiple models separated by commas (e.g. <code>deepseek-chat, deepseek-reasoner</code>). A model selector dropdown will be created on the provider card.</span>
            </div>

            <div class="dash-field" style="margin-bottom:14px;">
                <label class="dash-label" for="modal-desc">Description / Notes (Optional)</label>
                <input type="text" name="description" id="modal-desc" class="dash-input" placeholder="e.g. High speed reasoning model for electrical architectural quotes">
            </div>

            <div style="margin-bottom:14px;">
                <label style="display:flex;align-items:center;gap:8px;font-size:13px;cursor:pointer;text-transform:none;letter-spacing:normal;">
                    <input type="checkbox" name="is_openwebui" id="modal-openwebui" value="1" style="accent-color:var(--accent);width:16px;height:16px;">
                    <span style="color:var(--dash-ink);font-weight:600;">Enable OpenWebUI Proxy Compatibility</span>
                </label>
                <span style="font-size:11px;color:var(--dash-muted);margin-top:3px;display:block;padding-left:24px;">Automatically supplies bypass parameters needed by self-hosted OpenWebUI backend routers.</span>
            </div>

            <div style="margin-bottom:20px;">
                <label style="display:flex;align-items:center;gap:8px;font-size:13px;cursor:pointer;text-transform:none;letter-spacing:normal;">
                    <input type="checkbox" name="set_active" value="1" style="accent-color:var(--accent);width:16px;height:16px;">
                    <span style="color:var(--dash-ink);font-weight:600;">Set as Active AI Provider immediately upon creation</span>
                </label>
            </div>

            <div style="display:flex;justify-content:flex-end;gap:10px;padding-top:16px;border-top:1px solid var(--dash-line);">
                <button type="button" id="btn-cancel-modal" class="btn">Cancel</button>
                <button type="submit" class="btn primary" style="font-weight:600;">Add & Save Provider</button>
            </div>
        </form>
    </div>
</div>

<!-- Hidden Delete Form -->
<form id="delete-provider-form" method="post" action="" style="display:none;">
    @csrf
    @method('delete')
</form>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const toast = document.getElementById('ai-copy-toast');
    const toastMsg = document.getElementById('ai-copy-toast-msg');
    let toastTimeout = null;

    const showCopyToast = (text = 'API Key copied to clipboard!') => {
        if (!toast) return;
        toastMsg.textContent = text;
        toast.style.display = 'flex';
        // Small delay for CSS transition
        setTimeout(() => {
            toast.style.opacity = '1';
        }, 10);
        if (toastTimeout) clearTimeout(toastTimeout);
        toastTimeout = setTimeout(() => {
            toast.style.opacity = '0';
            setTimeout(() => {
                toast.style.display = 'none';
            }, 300);
        }, 2200);
    };

    const copyToClipboard = async (text, btnElement = null) => {
        if (!text || text.trim() === '') {
            showCopyToast('No key value to copy!');
            return;
        }

        try {
            await navigator.clipboard.writeText(text);
            showCopyToast('API Key copied to clipboard!');
            
            if (btnElement) {
                const origHtml = btnElement.innerHTML;
                btnElement.innerHTML = '<svg viewBox="0 0 24 24" fill="none" stroke="#137333" stroke-width="2.5" style="width:14px;height:14px;"><polyline points="20 6 9 17 4 12"/></svg>';
                setTimeout(() => {
                    btnElement.innerHTML = origHtml;
                }, 1500);
            }
        } catch (e) {
            // Fallback for non-https or older browser environments
            const tempInput = document.createElement('textarea');
            tempInput.value = text;
            tempInput.style.position = 'fixed';
            tempInput.style.left = '-9999px';
            document.body.appendChild(tempInput);
            tempInput.select();
            document.execCommand('copy');
            document.body.removeChild(tempInput);
            showCopyToast('API Key copied to clipboard!');
        }
    };

    // Toggle API Key input visibility
    document.querySelectorAll('.js-toggle-key-visibility').forEach(btn => {
        btn.addEventListener('click', (e) => {
            e.preventDefault();
            e.stopPropagation();
            const targetId = btn.getAttribute('data-target');
            const targetInput = document.getElementById(targetId);
            if (targetInput) {
                if (targetInput.type === 'password') {
                    targetInput.type = 'text';
                    btn.style.color = 'var(--accent)';
                    btn.setAttribute('title', 'Hide API Key');
                    btn.innerHTML = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:14px;height:14px;"><path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"/><line x1="1" y1="1" x2="23" y2="23"/></svg>';
                } else {
                    targetInput.type = 'password';
                    btn.style.color = 'var(--muted)';
                    btn.setAttribute('title', 'View API Key');
                    btn.innerHTML = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:14px;height:14px;"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>';
                }
            }
        });
    });

    // Copy API Key input button
    document.querySelectorAll('.js-copy-key-btn').forEach(btn => {
        btn.addEventListener('click', (e) => {
            e.preventDefault();
            e.stopPropagation();
            const targetId = btn.getAttribute('data-target');
            const targetInput = document.getElementById(targetId);
            if (targetInput) {
                copyToClipboard(targetInput.value, btn);
            }
        });
    });

    // Toggle custom provider text span visibility
    document.querySelectorAll('.js-toggle-span-visibility').forEach(btn => {
        btn.addEventListener('click', (e) => {
            e.preventDefault();
            e.stopPropagation();
            const targetId = btn.getAttribute('data-target');
            const targetSpan = document.getElementById(targetId);
            if (targetSpan) {
                const realKey = targetSpan.getAttribute('data-real-key');
                const masked = targetSpan.getAttribute('data-masked') || '••••••••••••••••';
                if (targetSpan.textContent === masked) {
                    targetSpan.textContent = realKey;
                    btn.style.color = 'var(--accent)';
                    btn.setAttribute('title', 'Hide API Key');
                    btn.innerHTML = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:12px;height:12px;"><path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"/><line x1="1" y1="1" x2="23" y2="23"/></svg>';
                } else {
                    targetSpan.textContent = masked;
                    btn.style.color = 'var(--muted)';
                    btn.setAttribute('title', 'View API Key');
                    btn.innerHTML = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:12px;height:12px;"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>';
                }
            }
        });
    });

    // Copy raw text button
    document.querySelectorAll('.js-copy-raw-text-btn').forEach(btn => {
        btn.addEventListener('click', (e) => {
            e.preventDefault();
            e.stopPropagation();
            const rawKey = btn.getAttribute('data-copy');
            copyToClipboard(rawKey, btn);
        });
    });

    // Provider radio card visual toggle
    const providerRadios = document.querySelectorAll('input[name="driver"]');
    const providerCards = document.querySelectorAll('.provider-radio-card');
    providerRadios.forEach(radio => {
        radio.addEventListener('change', () => {
            providerCards.forEach(card => {
                const cardRadio = card.querySelector('input[type="radio"]');
                if (cardRadio && cardRadio.checked) {
                    card.style.borderColor = 'var(--accent)';
                } else {
                    card.style.borderColor = 'var(--line)';
                }
            });
            const testDriverSelect = document.getElementById('test-driver-select');
            if (testDriverSelect) {
                testDriverSelect.value = radio.value;
            }
        });
    });

    // Modal Add Provider Controls
    const addModal = document.getElementById('add-provider-modal');
    const openModalBtn1 = document.getElementById('btn-open-add-provider');
    const cardAddProvider = document.getElementById('btn-card-add-provider');
    const closeModalBtn = document.getElementById('btn-close-modal');
    const cancelModalBtn = document.getElementById('btn-cancel-modal');

    const showModal = () => {
        if (addModal) {
            addModal.style.display = 'flex';
        }
    };

    const hideModal = () => {
        if (addModal) {
            addModal.style.display = 'none';
        }
    };

    if (openModalBtn1) openModalBtn1.addEventListener('click', showModal);
    if (cardAddProvider) cardAddProvider.addEventListener('click', showModal);
    if (closeModalBtn) closeModalBtn.addEventListener('click', hideModal);
    if (cancelModalBtn) cancelModalBtn.addEventListener('click', hideModal);

    // Preset Fill Buttons
    document.querySelectorAll('.js-preset-btn').forEach(btn => {
        btn.addEventListener('click', () => {
            document.getElementById('modal-name').value = btn.getAttribute('data-name') || '';
            document.getElementById('modal-type').value = btn.getAttribute('data-type') || 'openai';
            document.getElementById('modal-url').value = btn.getAttribute('data-url') || '';
            document.getElementById('modal-model').value = btn.getAttribute('data-model') || '';
            document.getElementById('modal-desc').value = btn.getAttribute('data-desc') || '';
            const openwebuiEl = document.getElementById('modal-openwebui');
            if (openwebuiEl) {
                openwebuiEl.checked = btn.getAttribute('data-openwebui') === '1';
            }
            document.getElementById('modal-key').focus();
        });
    });

    // Delete Custom Provider handler
    const deleteForm = document.getElementById('delete-provider-form');
    document.querySelectorAll('.js-delete-custom-provider').forEach(btn => {
        btn.addEventListener('click', (e) => {
            e.preventDefault();
            e.stopPropagation();
            const id = btn.getAttribute('data-id');
            const name = btn.getAttribute('data-name');
            if (confirm(`Are you sure you want to remove custom AI provider "${name}"?`)) {
                deleteForm.action = `/dashboard/ai/custom-provider/${id}`;
                deleteForm.submit();
            }
        });
    });

    // Live AI Diagnostic Test Runner (Inline per card)
    document.querySelectorAll('.js-card-diagnostic-btn').forEach(btn => {
        btn.addEventListener('click', async (e) => {
            e.preventDefault();
            const driver = btn.getAttribute('data-driver');
            let model = null;

            const modelInputId = btn.getAttribute('data-model-input');
            if (modelInputId) {
                const modelInputEl = document.getElementById(modelInputId);
                if (modelInputEl && modelInputEl.value.trim()) {
                    model = modelInputEl.value.trim();
                }
            } else if (btn.getAttribute('data-model-val')) {
                model = btn.getAttribute('data-model-val');
            }

            const wrapper = btn.closest('div');
            const resultsBox = wrapper.querySelector('.js-card-diagnostic-results');
            const statusBadge = resultsBox.querySelector('.js-diag-status');
            const latencyEl = resultsBox.querySelector('.js-diag-latency');
            const outputEl = resultsBox.querySelector('.js-diag-output');

            const originalHtml = btn.innerHTML;
            btn.disabled = true;
            btn.innerHTML = '<span style="display:inline-block;animation:spin 1s linear infinite;">⏳</span> Connecting...';

            resultsBox.style.display = 'block';
            statusBadge.textContent = 'Testing in progress...';
            statusBadge.style.background = '#e8f0fe';
            statusBadge.style.color = '#1a73e8';
            latencyEl.textContent = 'Measuring latency...';
            outputEl.textContent = 'Sending prompt to LLM endpoint...';

            const payload = {
                driver: driver || 'gemini',
                model: model
            };

            try {
                const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') 
                    || document.querySelector('input[name="_token"]')?.value;

                const res = await fetch("{{ route('dashboard.ai.test-connection') }}", {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': csrfToken
                    },
                    body: JSON.stringify(payload)
                });

                const data = await res.json();

                if (res.ok && data.success) {
                    statusBadge.textContent = 'SUCCESS (200 OK)';
                    statusBadge.style.background = '#e6f4ea';
                    statusBadge.style.color = '#137333';
                    latencyEl.textContent = `Latency: ${data.latency_ms}ms | Tokens: ${data.tokens_used}`;
                    outputEl.textContent = `Driver: ${data.driver}\nModel: ${data.model || 'Default'}\nResponse:\n${data.response}`;
                } else {
                    statusBadge.textContent = 'FAILED / ERROR';
                    statusBadge.style.background = '#fce8e6';
                    statusBadge.style.color = '#c5221f';
                    latencyEl.textContent = `Latency: ${data.latency_ms || 0}ms`;
                    outputEl.textContent = `Error Message:\n${data.error || 'Unknown error occurred.'}`;
                }
            } catch (err) {
                statusBadge.textContent = 'NETWORK / HTTP ERROR';
                statusBadge.style.background = '#fce8e6';
                statusBadge.style.color = '#c5221f';
                latencyEl.textContent = 'N/A';
                outputEl.textContent = `Request failed: ${err.message}`;
            } finally {
                btn.disabled = false;
                btn.innerHTML = originalHtml;
            }
        });
    });
});
</script>
@endsection
