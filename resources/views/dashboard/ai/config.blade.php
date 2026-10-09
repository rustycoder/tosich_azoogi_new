@extends('layouts.dashboard')

@section('title', 'AI Configuration & Model Engine')

@section('content')
<div class="dash-head">
    <div class="dash-crumb">
        <a href="{{ route('dashboard.home') }}">Dashboard</a>
        <span>/</span>
        <span>AI</span>
        <span>/</span>
        <span>AI Configuration</span>
    </div>
    <div class="dash-head-title">
        <h1>AI Configuration & Model Engine</h1>
        <div class="dash-head-actions">
            <button type="button" class="btn" id="btn-open-add-provider" style="display:inline-flex;align-items:center;gap:6px;background:var(--card-bg);border:1px solid var(--accent);color:var(--accent);font-weight:600;">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:16px;height:16px;" aria-hidden="true"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                + Add AI Provider
            </button>
            <a href="{{ route('dashboard.chat-sessions.index') }}" class="btn" style="display:inline-flex;align-items:center;gap:6px;">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" style="width:16px;height:16px;" aria-hidden="true"><path d="M21 11.5a8.38 8.38 0 0 1-.9 3.8 8.5 8.5 0 0 1-7.6 4.7 8.38 8.38 0 0 1-3.8-.9L3 21l1.9-5.7a8.38 8.38 0 0 1-.9-3.8 8.5 8.5 0 0 1 4.7-7.6 8.38 8.38 0 0 1 3.8-.9h.5a8.48 8.48 0 0 1 8 8v.5z"/></svg>
                View Chat Logs
            </a>
            <button class="btn primary" type="submit" form="ai-config-form">Save All AI Settings</button>
        </div>
    </div>
    <p class="dash-lead">Manage API credentials, view and copy authentication keys, configure custom endpoints (DeepSeek, Groq, Ollama), and select the active intelligence engine powering the public Azoogi AI Chat Assistant.</p>
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
        <div style="font-size:12px;color:var(--muted);text-transform:uppercase;letter-spacing:0.05em;font-weight:600;margin-bottom:6px;">Custom AI Providers</div>
        <div style="font-size:24px;font-weight:700;color:var(--ink);">{{ count($customProviders) }}</div>
        <div style="font-size:11px;color:var(--muted);margin-top:4px;">DeepSeek, Groq, Ollama, Together, etc.</div>
    </div>

    <div class="dash-card" style="padding:16px 20px;">
        <div style="font-size:12px;color:var(--muted);text-transform:uppercase;letter-spacing:0.05em;font-weight:600;margin-bottom:6px;">Credential Storage</div>
        <div style="font-size:16px;font-weight:600;color:var(--ink);">Database Managed</div>
        <div style="font-size:11px;color:var(--muted);margin-top:4px;">View, copy &amp; toggle keys at any time</div>
    </div>
</div>

<div style="display:grid;grid-template-columns:1fr;gap:24px;">
    <!-- AI Provider Selection & Key Settings -->
    <form id="ai-config-form" class="dash-form" method="post" action="{{ route('dashboard.ai.update') }}">
        @csrf
        @method('put')

        <div class="dash-card" style="margin-bottom:20px;">
            <div style="margin-bottom:20px;">
                <div style="display:flex;justify-content:space-between;align-items:flex-start;flex-wrap:wrap;gap:12px;">
                    <div>
                        <h2 style="margin:0 0 6px 0;font-size:18px;">Select Active AI Provider & Enter API Keys</h2>
                        <p style="margin:0;font-size:13px;color:var(--muted);max-width:800px;line-height:1.5;">
                            Choose the active provider for the public chat assistant by selecting its radio button. All API keys entered below are saved directly into the database. You can click the <strong>Eye icon</strong> to view any key or the <strong>Copy icon</strong> to copy it.
                        </p>
                    </div>
                    <div style="display:flex;gap:10px;">
                        <button type="button" class="btn" id="btn-open-add-provider-2" style="font-weight:600;display:inline-flex;align-items:center;gap:4px;">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:14px;height:14px;"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                            Add Provider
                        </button>
                        <button type="submit" class="btn primary" style="font-weight:600;">Save All AI Settings</button>
                    </div>
                </div>
            </div>

            <!-- Provider Cards Selector -->
            <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(300px,1fr));gap:20px;margin-bottom:24px;">
                <!-- Claude / Anthropic Option -->
                <div class="provider-radio-card" style="border:2px solid {{ ($aiConfig['driver'] ?? 'anthropic') === 'anthropic' ? 'var(--accent)' : 'var(--line)' }};background:var(--bg-2);border-radius:8px;padding:18px;position:relative;display:flex;flex-direction:column;justify-content:space-between;">
                    <div>
                        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:12px;">
                            <label style="display:flex;align-items:center;gap:10px;cursor:pointer;margin:0;white-space:nowrap;">
                                <input type="radio" name="driver" value="anthropic" {{ ($aiConfig['driver'] ?? 'anthropic') === 'anthropic' ? 'checked' : '' }} style="accent-color:var(--accent);transform:scale(1.15);">
                                <span style="font-weight:700;font-size:16px;color:var(--ink);white-space:nowrap;">Anthropic Claude</span>
                            </label>
                        </div>
                        <p style="margin:0 0 16px 0;font-size:12px;color:var(--muted);line-height:1.4;">
                            Elite technical accuracy, intelligent tool parsing, and structured architectural advice.
                        </p>

                        <!-- API Key Input -->
                        <div class="dash-field" style="margin-bottom:12px;">
                            <label style="font-size:12px;font-weight:600;margin-bottom:4px;display:flex;justify-content:space-between;">
                                <span>Anthropic API Key</span>
                                <span style="font-weight:normal;color:var(--muted);font-size:11px;">(Database Stored)</span>
                            </label>
                            <div style="position:relative;display:flex;align-items:center;">
                                <input type="password" name="anthropic_api_key" id="input_anthropic_api_key" value="{{ $aiConfig['anthropic_api_key'] ?? '' }}" placeholder="Paste Anthropic API Key (sk-ant-...)" style="width:100%;font-size:12px;padding:8px 68px 8px 12px;background:var(--bg);border:1px solid var(--line);border-radius:6px;color:var(--ink);font-family:monospace;">
                                <div style="position:absolute;right:6px;display:flex;align-items:center;gap:2px;">
                                    <button type="button" class="js-toggle-key-visibility" data-target="input_anthropic_api_key" style="background:none;border:none;cursor:pointer;color:var(--muted);padding:4px;display:flex;align-items:center;" title="View / Hide API Key">
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:14px;height:14px;"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                                    </button>
                                    <button type="button" class="js-copy-key-btn" data-target="input_anthropic_api_key" style="background:none;border:none;cursor:pointer;color:var(--muted);padding:4px;display:flex;align-items:center;" title="Copy API Key to clipboard">
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:14px;height:14px;"><rect x="9" y="9" width="13" height="13" rx="2" ry="2"/><path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"/></svg>
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- Model Selector -->
                        <div class="dash-field" style="margin-bottom:0;">
                            <label style="font-size:12px;font-weight:600;margin-bottom:4px;display:block;">Model Name</label>
                            <input type="text" name="anthropic_model" id="input_anthropic_model" value="{{ old('anthropic_model', $aiConfig['anthropic_model'] ?? 'claude-3-5-sonnet-20241022') }}" list="anthropic_models_list" style="width:100%;font-size:12px;padding:8px 12px;background:var(--bg);border:1px solid var(--line);border-radius:6px;color:var(--ink);font-family:monospace;">
                            <datalist id="anthropic_models_list">
                                <option value="claude-3-5-sonnet-20241022">claude-3-5-sonnet-20241022 (Recommended - High Quality)</option>
                                <option value="claude-3-5-haiku-20241022">claude-3-5-haiku-20241022 (Fast & Economical)</option>
                                <option value="claude-3-opus-20240229">claude-3-opus-20240229</option>
                            </datalist>
                        </div>
                    </div>
                </div>

                <!-- OpenRouter Option -->
                <div class="provider-radio-card" style="border:2px solid {{ ($aiConfig['driver'] ?? '') === 'openrouter' ? 'var(--accent)' : 'var(--line)' }};background:var(--bg-2);border-radius:8px;padding:18px;position:relative;display:flex;flex-direction:column;justify-content:space-between;">
                    <div>
                        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:12px;">
                            <label style="display:flex;align-items:center;gap:10px;cursor:pointer;margin:0;white-space:nowrap;">
                                <input type="radio" name="driver" value="openrouter" {{ ($aiConfig['driver'] ?? '') === 'openrouter' ? 'checked' : '' }} style="accent-color:var(--accent);transform:scale(1.15);">
                                <span style="font-weight:700;font-size:16px;color:var(--ink);white-space:nowrap;">OpenRouter Gateway</span>
                            </label>
                        </div>
                        <p style="margin:0 0 16px 0;font-size:12px;color:var(--muted);line-height:1.4;">
                            Single API key granting access to Claude, Llama 3.3, DeepSeek, Mistral, and more.
                        </p>

                        <!-- API Key Input -->
                        <div class="dash-field" style="margin-bottom:12px;">
                            <label style="font-size:12px;font-weight:600;margin-bottom:4px;display:flex;justify-content:space-between;">
                                <span>OpenRouter API Key</span>
                                <span style="font-weight:normal;color:var(--muted);font-size:11px;">(Database Stored)</span>
                            </label>
                            <div style="position:relative;display:flex;align-items:center;">
                                <input type="password" name="openrouter_api_key" id="input_openrouter_api_key" value="{{ $aiConfig['openrouter_api_key'] ?? '' }}" placeholder="Paste OpenRouter API Key (sk-or-v1-...)" style="width:100%;font-size:12px;padding:8px 68px 8px 12px;background:var(--bg);border:1px solid var(--line);border-radius:6px;color:var(--ink);font-family:monospace;">
                                <div style="position:absolute;right:6px;display:flex;align-items:center;gap:2px;">
                                    <button type="button" class="js-toggle-key-visibility" data-target="input_openrouter_api_key" style="background:none;border:none;cursor:pointer;color:var(--muted);padding:4px;display:flex;align-items:center;" title="View / Hide API Key">
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:14px;height:14px;"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                                    </button>
                                    <button type="button" class="js-copy-key-btn" data-target="input_openrouter_api_key" style="background:none;border:none;cursor:pointer;color:var(--muted);padding:4px;display:flex;align-items:center;" title="Copy API Key to clipboard">
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:14px;height:14px;"><rect x="9" y="9" width="13" height="13" rx="2" ry="2"/><path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"/></svg>
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- Model Selector -->
                        <div class="dash-field" style="margin-bottom:0;">
                            <label style="font-size:12px;font-weight:600;margin-bottom:4px;display:block;">Model Name</label>
                            <input type="text" name="openrouter_model" id="input_openrouter_model" value="{{ old('openrouter_model', $aiConfig['openrouter_model'] ?? 'anthropic/claude-3.5-sonnet') }}" list="openrouter_models_list" style="width:100%;font-size:12px;padding:8px 12px;background:var(--bg);border:1px solid var(--line);border-radius:6px;color:var(--ink);font-family:monospace;">
                            <datalist id="openrouter_models_list">
                                <option value="anthropic/claude-3.5-sonnet">anthropic/claude-3.5-sonnet</option>
                                <option value="meta-llama/llama-3.3-70b-instruct">meta-llama/llama-3.3-70b-instruct</option>
                                <option value="google/gemini-2.5-flash">google/gemini-2.5-flash</option>
                                <option value="openai/gpt-4o">openai/gpt-4o</option>
                                <option value="deepseek/deepseek-r1">deepseek/deepseek-r1</option>
                            </datalist>
                        </div>
                    </div>
                </div>

                <!-- Gemini Option -->
                <div class="provider-radio-card" style="border:2px solid {{ ($aiConfig['driver'] ?? '') === 'gemini' ? 'var(--accent)' : 'var(--line)' }};background:var(--bg-2);border-radius:8px;padding:18px;position:relative;display:flex;flex-direction:column;justify-content:space-between;">
                    <div>
                        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:12px;">
                            <label style="display:flex;align-items:center;gap:10px;cursor:pointer;margin:0;white-space:nowrap;">
                                <input type="radio" name="driver" value="gemini" {{ ($aiConfig['driver'] ?? '') === 'gemini' ? 'checked' : '' }} style="accent-color:var(--accent);transform:scale(1.15);">
                                <span style="font-weight:700;font-size:16px;color:var(--ink);white-space:nowrap;">Google Gemini</span>
                            </label>
                        </div>
                        <p style="margin:0 0 16px 0;font-size:12px;color:var(--muted);line-height:1.4;">
                            High-speed multimodal models with fast reasoning and built-in function calling.
                        </p>

                        <!-- API Key Input -->
                        <div class="dash-field" style="margin-bottom:12px;">
                            <label style="font-size:12px;font-weight:600;margin-bottom:4px;display:flex;justify-content:space-between;">
                                <span>Gemini API Key</span>
                                <span style="font-weight:normal;color:var(--muted);font-size:11px;">(Database Stored)</span>
                            </label>
                            <div style="position:relative;display:flex;align-items:center;">
                                <input type="password" name="gemini_api_key" id="input_gemini_api_key" value="{{ $aiConfig['gemini_api_key'] ?? '' }}" placeholder="Paste Gemini API Key (AIzaSy...)" style="width:100%;font-size:12px;padding:8px 68px 8px 12px;background:var(--bg);border:1px solid var(--line);border-radius:6px;color:var(--ink);font-family:monospace;">
                                <div style="position:absolute;right:6px;display:flex;align-items:center;gap:2px;">
                                    <button type="button" class="js-toggle-key-visibility" data-target="input_gemini_api_key" style="background:none;border:none;cursor:pointer;color:var(--muted);padding:4px;display:flex;align-items:center;" title="View / Hide API Key">
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:14px;height:14px;"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                                    </button>
                                    <button type="button" class="js-copy-key-btn" data-target="input_gemini_api_key" style="background:none;border:none;cursor:pointer;color:var(--muted);padding:4px;display:flex;align-items:center;" title="Copy API Key to clipboard">
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:14px;height:14px;"><rect x="9" y="9" width="13" height="13" rx="2" ry="2"/><path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"/></svg>
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- Model Selector -->
                        <div class="dash-field" style="margin-bottom:0;">
                            <label style="font-size:12px;font-weight:600;margin-bottom:4px;display:block;">Model Name</label>
                            <input type="text" name="gemini_model" id="input_gemini_model" value="{{ old('gemini_model', $aiConfig['gemini_model'] ?? 'gemini-2.5-flash') }}" list="gemini_models_list" style="width:100%;font-size:12px;padding:8px 12px;background:var(--bg);border:1px solid var(--line);border-radius:6px;color:var(--ink);font-family:monospace;">
                            <datalist id="gemini_models_list">
                                <option value="gemini-2.5-flash">gemini-2.5-flash (Fast & Cost Effective)</option>
                                <option value="gemini-2.5-pro">gemini-2.5-pro (High Reasoning)</option>
                                <option value="gemini-1.5-flash">gemini-1.5-flash</option>
                                <option value="gemini-1.5-pro">gemini-1.5-pro</option>
                            </datalist>
                        </div>
                    </div>
                </div>

                <!-- Custom Providers Dynamic List -->
                @foreach($customProviders as $cpId => $cp)
                    <div class="provider-radio-card" style="border:2px solid {{ ($aiConfig['driver'] ?? '') === $cpId ? 'var(--accent)' : 'var(--line)' }};background:var(--bg-2);border-radius:8px;padding:18px;position:relative;display:flex;flex-direction:column;justify-content:space-between;">
                        <div>
                            <div style="display:flex;justify-content:space-between;align-items:flex-start;margin-bottom:10px;">
                                <label style="display:flex;align-items:center;gap:10px;cursor:pointer;margin:0;white-space:nowrap;">
                                    <input type="radio" name="driver" value="{{ $cpId }}" {{ ($aiConfig['driver'] ?? '') === $cpId ? 'checked' : '' }} style="accent-color:var(--accent);transform:scale(1.15);">
                                    <span style="font-weight:700;font-size:16px;color:var(--ink);white-space:nowrap;">{{ $cp['name'] }}</span>
                                </label>
                                <span style="font-size:10px;padding:2px 6px;border-radius:4px;background:var(--card-bg);border:1px solid var(--line);text-transform:uppercase;font-weight:700;color:var(--accent);white-space:nowrap;">
                                    {{ strtoupper($cp['type'] ?? 'openai') }} API
                                </span>
                            </div>

                            <p style="margin:0 0 12px 0;font-size:12px;color:var(--muted);line-height:1.4;">
                                {{ $cp['description'] ?: 'Custom endpoint configured by administrator.' }}
                            </p>

                            <div style="background:var(--bg);padding:10px;border-radius:6px;border:1px solid var(--line);margin-bottom:12px;font-size:11px;font-family:monospace;">
                                <div style="color:var(--muted);margin-bottom:6px;"><strong style="color:var(--ink);">Endpoint:</strong> {{ $cp['base_url'] ?: 'Provider default base URL' }}</div>
                                <div style="color:var(--muted);margin-bottom:6px;"><strong style="color:var(--ink);">Model:</strong> <span style="color:var(--accent);">{{ $cp['model'] }}</span></div>
                                <div style="display:flex;align-items:center;justify-content:space-between;gap:8px;padding-top:4px;border-top:1px dashed var(--line);">
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
                    <div style="font-weight:700;font-size:15px;color:var(--ink);margin-bottom:4px;">+ Add Custom AI Provider</div>
                    <p style="font-size:12px;color:var(--muted);margin:0;max-width:240px;line-height:1.4;">
                        Connect DeepSeek, Groq, Ollama Local, Together AI, Mistral, xAI, or any custom API endpoint.
                    </p>
                </div>
            </div>

            <div style="display:flex;justify-content:flex-end;padding-top:16px;border-top:1px solid var(--line);">
                <button type="submit" class="btn primary" style="font-weight:600;">Save All AI Settings</button>
            </div>
        </div>
    </form>

    <!-- Live Connectivity Diagnostic Box -->
    <div class="dash-card" style="margin-bottom:20px;">
        <div style="margin-bottom:16px;">
            <h3 style="margin:0 0 6px 0;font-size:16px;display:flex;align-items:center;gap:8px;">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:16px;height:16px;color:var(--accent);"><polyline points="22 12 18 12 15 21 9 3 6 12 2 12"/></svg>
                Live AI Model Connectivity & Diagnostic Ping
            </h3>
            <p style="margin:0;font-size:13px;color:var(--muted);">
                Test live API response latency, verify database authentication keys, and inspect model outputs for built-in or custom providers without leaving the dashboard.
            </p>
        </div>

        <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(220px,1fr));gap:12px;align-items:flex-end;margin-bottom:16px;">
            <div class="dash-field" style="margin-bottom:0;">
                <label style="font-size:12px;font-weight:600;margin-bottom:4px;display:block;">Provider to Ping</label>
                <select id="test-driver-select" style="width:100%;font-size:13px;padding:8px 12px;background:var(--bg-2);border:1px solid var(--line);border-radius:6px;color:var(--ink);">
                    <optgroup label="Standard Built-in Providers">
                        <option value="anthropic" {{ ($aiConfig['driver'] ?? '') === 'anthropic' ? 'selected' : '' }}>Anthropic Claude</option>
                        <option value="openrouter" {{ ($aiConfig['driver'] ?? '') === 'openrouter' ? 'selected' : '' }}>OpenRouter Gateway</option>
                        <option value="gemini" {{ ($aiConfig['driver'] ?? '') === 'gemini' ? 'selected' : '' }}>Google Gemini</option>
                    </optgroup>
                    @if(!empty($customProviders))
                        <optgroup label="Custom Added Providers">
                            @foreach($customProviders as $cpId => $cp)
                                <option value="{{ $cpId }}" {{ ($aiConfig['driver'] ?? '') === $cpId ? 'selected' : '' }}>{{ $cp['name'] }} ({{ $cp['model'] }})</option>
                            @endforeach
                        </optgroup>
                    @endif
                </select>
            </div>

            <div class="dash-field" style="margin-bottom:0;">
                <label style="font-size:12px;font-weight:600;margin-bottom:4px;display:block;">Model Override (Optional)</label>
                <input type="text" id="test-model-input" placeholder="Leave empty for provider default" style="width:100%;font-size:13px;padding:8px 12px;background:var(--bg-2);border:1px solid var(--line);border-radius:6px;color:var(--ink);font-family:monospace;">
            </div>

            <div>
                <button type="button" id="btn-run-ai-test" class="btn" style="width:100%;height:38px;display:inline-flex;align-items:center;justify-content:center;gap:6px;font-weight:600;">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:14px;height:14px;"><polygon points="5 3 19 12 5 21 5 3"/></svg>
                    Run Live Ping Test
                </button>
            </div>
        </div>

        <!-- Test Output Box -->
        <div id="ai-test-results" style="display:none;background:var(--bg-2);padding:16px;border-radius:6px;border:1px solid var(--line);">
            <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:8px;">
                <span id="ai-test-status-badge" style="font-size:12px;font-weight:700;padding:3px 8px;border-radius:4px;"></span>
                <span id="ai-test-latency" style="font-size:12px;color:var(--muted);font-family:monospace;"></span>
            </div>
            <pre id="ai-test-output" style="margin:0;font-family:monospace;font-size:12px;white-space:pre-wrap;word-break:break-word;color:var(--ink);"></pre>
        </div>
    </div>

    <!-- Cost Calculation Engine Rate Schedule Documentation Card -->
    <div class="dash-card" style="padding: 24px; margin-top: 24px;">
        <div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 12px; margin-bottom: 16px; border-bottom: 1px solid var(--line); padding-bottom: 14px;">
            <div style="display: flex; align-items: center; gap: 8px;">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width: 20px; height: 20px; color: var(--accent);"><line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg>
                <h3 style="margin: 0; font-size: 16px; font-weight: 700; color: var(--dash-ink, var(--ink));">
                    Cost Calculation Engine — Rate Schedule Definition
                </h3>
            </div>
            <span class="dash-doc-badge" style="background: rgba(16, 185, 129, 0.12); color: #10b981; border: 1px solid rgba(16, 185, 129, 0.3); font-weight: 600; font-size: 11px;">
                Precision: 6 Decimals ($ USD)
            </span>
        </div>

        <p style="font-size: 13px; color: var(--dash-muted, var(--muted)); margin: 0 0 16px 0; line-height: 1.55;">
            The system tracks prompt and completion tokens separately for every chat interaction and calculates exact compute costs using the mathematical formula:
            <br>
            <code style="display: inline-block; margin-top: 6px; padding: 4px 10px; background: var(--dash-bg, var(--bg)); border: 1px solid var(--line); border-radius: 4px; font-size: 12px; color: var(--dash-ink, var(--ink)); font-family: monospace;">
                Cost (USD) = ((Prompt Tokens / 1,000,000) × Input Rate) + ((Completion Tokens / 1,000,000) × Output Rate)
            </code>
        </p>

        <div style="overflow-x: auto;">
            <table style="width: 100%; border-collapse: collapse; font-size: 12.5px; text-align: left;">
                <thead>
                    <tr style="border-bottom: 1px solid var(--line); background: rgba(255, 255, 255, 0.02);">
                        <th style="padding: 10px 14px; font-weight: 600; color: var(--dash-muted, var(--muted));">Model Family / Pattern</th>
                        <th style="padding: 10px 14px; font-weight: 600; color: #38bdf8;">Prompt (Input) Rate / 1M</th>
                        <th style="padding: 10px 14px; font-weight: 600; color: var(--accent);">Completion (Output) Rate / 1M</th>
                        <th style="padding: 10px 14px; font-weight: 600; color: var(--dash-muted, var(--muted));">Provider / Engine</th>
                    </tr>
                </thead>
                <tbody>
                    <tr style="border-bottom: 1px solid var(--line);">
                        <td style="padding: 10px 14px;"><strong style="color: var(--dash-ink, var(--ink));">Claude 3.5 Sonnet</strong> <code style="font-size: 11px; color: var(--dash-muted, var(--muted));">(claude-3-5-sonnet*)</code></td>
                        <td style="padding: 10px 14px; font-family: monospace; font-weight: 600; color: #38bdf8;">$3.00 USD</td>
                        <td style="padding: 10px 14px; font-family: monospace; font-weight: 600; color: var(--accent);">$15.00 USD</td>
                        <td style="padding: 10px 14px;"><span class="dash-tag is-primary" style="font-size: 10px;">Anthropic / OpenRouter</span></td>
                    </tr>
                    <tr style="border-bottom: 1px solid var(--line);">
                        <td style="padding: 10px 14px;"><strong style="color: var(--dash-ink, var(--ink));">Claude 3.5 Haiku</strong> <code style="font-size: 11px; color: var(--dash-muted, var(--muted));">(claude-3-5-haiku*)</code></td>
                        <td style="padding: 10px 14px; font-family: monospace; font-weight: 600; color: #38bdf8;">$0.80 USD</td>
                        <td style="padding: 10px 14px; font-family: monospace; font-weight: 600; color: var(--accent);">$4.00 USD</td>
                        <td style="padding: 10px 14px;"><span class="dash-tag is-primary" style="font-size: 10px;">Anthropic / OpenRouter</span></td>
                    </tr>
                    <tr style="border-bottom: 1px solid var(--line);">
                        <td style="padding: 10px 14px;"><strong style="color: var(--dash-ink, var(--ink));">Claude 3 Opus</strong> <code style="font-size: 11px; color: var(--dash-muted, var(--muted));">(claude-3-opus*)</code></td>
                        <td style="padding: 10px 14px; font-family: monospace; font-weight: 600; color: #38bdf8;">$15.00 USD</td>
                        <td style="padding: 10px 14px; font-family: monospace; font-weight: 600; color: var(--accent);">$75.00 USD</td>
                        <td style="padding: 10px 14px;"><span class="dash-tag is-primary" style="font-size: 10px;">Anthropic</span></td>
                    </tr>
                    <tr style="border-bottom: 1px solid var(--line);">
                        <td style="padding: 10px 14px;"><strong style="color: var(--dash-ink, var(--ink));">Google Gemini 2.5 Flash</strong> <code style="font-size: 11px; color: var(--dash-muted, var(--muted));">(gemini-2.5-flash*, gemini-2.0-flash*)</code></td>
                        <td style="padding: 10px 14px; font-family: monospace; font-weight: 600; color: #38bdf8;">$0.15 USD</td>
                        <td style="padding: 10px 14px; font-family: monospace; font-weight: 600; color: var(--accent);">$0.60 USD</td>
                        <td style="padding: 10px 14px;"><span class="dash-tag" style="font-size: 10px;">Google Gemini</span></td>
                    </tr>
                    <tr style="border-bottom: 1px solid var(--line);">
                        <td style="padding: 10px 14px;"><strong style="color: var(--dash-ink, var(--ink));">Google Gemini 2.5 Pro / 1.5 Pro</strong> <code style="font-size: 11px; color: var(--dash-muted, var(--muted));">(gemini-2.5-pro*, gemini-1.5-pro*)</code></td>
                        <td style="padding: 10px 14px; font-family: monospace; font-weight: 600; color: #38bdf8;">$1.25 USD</td>
                        <td style="padding: 10px 14px; font-family: monospace; font-weight: 600; color: var(--accent);">$5.00 USD</td>
                        <td style="padding: 10px 14px;"><span class="dash-tag" style="font-size: 10px;">Google Gemini</span></td>
                    </tr>
                    <tr style="border-bottom: 1px solid var(--line);">
                        <td style="padding: 10px 14px;"><strong style="color: var(--dash-ink, var(--ink));">Google Gemini 1.5 Flash</strong> <code style="font-size: 11px; color: var(--dash-muted, var(--muted));">(gemini-1.5-flash*)</code></td>
                        <td style="padding: 10px 14px; font-family: monospace; font-weight: 600; color: #38bdf8;">$0.075 USD</td>
                        <td style="padding: 10px 14px; font-family: monospace; font-weight: 600; color: var(--accent);">$0.30 USD</td>
                        <td style="padding: 10px 14px;"><span class="dash-tag" style="font-size: 10px;">Google Gemini</span></td>
                    </tr>
                    <tr style="border-bottom: 1px solid var(--line);">
                        <td style="padding: 10px 14px;"><strong style="color: var(--dash-ink, var(--ink));">OpenAI GPT-4o Mini</strong> <code style="font-size: 11px; color: var(--dash-muted, var(--muted));">(gpt-4o-mini*)</code></td>
                        <td style="padding: 10px 14px; font-family: monospace; font-weight: 600; color: #38bdf8;">$0.15 USD</td>
                        <td style="padding: 10px 14px; font-family: monospace; font-weight: 600; color: var(--accent);">$0.60 USD</td>
                        <td style="padding: 10px 14px;"><span class="dash-tag" style="font-size: 10px;">OpenAI / OpenRouter</span></td>
                    </tr>
                    <tr style="border-bottom: 1px solid var(--line);">
                        <td style="padding: 10px 14px;"><strong style="color: var(--dash-ink, var(--ink));">OpenAI GPT-4o</strong> <code style="font-size: 11px; color: var(--dash-muted, var(--muted));">(gpt-4o*)</code></td>
                        <td style="padding: 10px 14px; font-family: monospace; font-weight: 600; color: #38bdf8;">$2.50 USD</td>
                        <td style="padding: 10px 14px; font-family: monospace; font-weight: 600; color: var(--accent);">$10.00 USD</td>
                        <td style="padding: 10px 14px;"><span class="dash-tag" style="font-size: 10px;">OpenAI / OpenRouter</span></td>
                    </tr>
                    <tr style="border-bottom: 1px solid var(--line);">
                        <td style="padding: 10px 14px;"><strong style="color: var(--dash-ink, var(--ink));">OpenAI o3-mini</strong> <code style="font-size: 11px; color: var(--dash-muted, var(--muted));">(o3-mini*)</code></td>
                        <td style="padding: 10px 14px; font-family: monospace; font-weight: 600; color: #38bdf8;">$1.10 USD</td>
                        <td style="padding: 10px 14px; font-family: monospace; font-weight: 600; color: var(--accent);">$4.40 USD</td>
                        <td style="padding: 10px 14px;"><span class="dash-tag" style="font-size: 10px;">OpenAI</span></td>
                    </tr>
                    <tr style="border-bottom: 1px solid var(--line);">
                        <td style="padding: 10px 14px;"><strong style="color: var(--dash-ink, var(--ink));">DeepSeek Chat (V3)</strong> <code style="font-size: 11px; color: var(--dash-muted, var(--muted));">(deepseek-chat, deepseek*)</code></td>
                        <td style="padding: 10px 14px; font-family: monospace; font-weight: 600; color: #38bdf8;">$0.14 USD</td>
                        <td style="padding: 10px 14px; font-family: monospace; font-weight: 600; color: var(--accent);">$0.28 USD</td>
                        <td style="padding: 10px 14px;"><span class="dash-tag" style="font-size: 10px;">DeepSeek / OpenRouter</span></td>
                    </tr>
                    <tr style="border-bottom: 1px solid var(--line);">
                        <td style="padding: 10px 14px;"><strong style="color: var(--dash-ink, var(--ink));">DeepSeek Reasoner (R1)</strong> <code style="font-size: 11px; color: var(--dash-muted, var(--muted));">(deepseek-reasoner, deepseek-r1)</code></td>
                        <td style="padding: 10px 14px; font-family: monospace; font-weight: 600; color: #38bdf8;">$0.55 USD</td>
                        <td style="padding: 10px 14px; font-family: monospace; font-weight: 600; color: var(--accent);">$2.19 USD</td>
                        <td style="padding: 10px 14px;"><span class="dash-tag" style="font-size: 10px;">DeepSeek / OpenRouter</span></td>
                    </tr>
                    <tr style="border-bottom: 1px solid var(--line);">
                        <td style="padding: 10px 14px;"><strong style="color: var(--dash-ink, var(--ink));">Groq Llama 3.3 70B</strong> <code style="font-size: 11px; color: var(--dash-muted, var(--muted));">(llama-3.3-70b*)</code></td>
                        <td style="padding: 10px 14px; font-family: monospace; font-weight: 600; color: #38bdf8;">$0.59 USD</td>
                        <td style="padding: 10px 14px; font-family: monospace; font-weight: 600; color: var(--accent);">$0.79 USD</td>
                        <td style="padding: 10px 14px;"><span class="dash-tag" style="font-size: 10px;">Groq / Meta</span></td>
                    </tr>
                    <tr>
                        <td style="padding: 10px 14px;"><strong style="color: var(--dash-ink, var(--ink));">Custom / Fallback Rate</strong> <code style="font-size: 11px; color: var(--dash-muted, var(--muted));">(unmatched models)</code></td>
                        <td style="padding: 10px 14px; font-family: monospace; font-weight: 600; color: #38bdf8;">$1.00 USD</td>
                        <td style="padding: 10px 14px; font-family: monospace; font-weight: 600; color: var(--accent);">$3.00 USD</td>
                        <td style="padding: 10px 14px;"><span class="dash-tag" style="font-size: 10px;">Default Fallback</span></td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal: Add Custom AI Provider -->
<div id="add-provider-modal" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,0.7);backdrop-filter:blur(4px);z-index:9999;align-items:center;justify-content:center;padding:20px;">
    <div style="background:var(--bg-2);border:1px solid var(--line);border-radius:12px;width:100%;max-width:580px;box-shadow:0 20px 40px rgba(0,0,0,0.5);overflow:hidden;animation:fadeIn 0.2s ease;">
        <div style="padding:20px 24px;border-bottom:1px solid var(--line);display:flex;justify-content:space-between;align-items:center;">
            <div>
                <h3 style="margin:0 0 4px 0;font-size:18px;color:var(--ink);">Add Custom AI Provider</h3>
                <p style="margin:0;font-size:12px;color:var(--muted);">Connect any third-party or local OpenAI/Anthropic/Gemini compatible endpoint.</p>
            </div>
            <button type="button" id="btn-close-modal" style="background:none;border:none;color:var(--muted);cursor:pointer;padding:4px;" aria-label="Close">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:20px;height:20px;"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
            </button>
        </div>

        <form method="post" action="{{ route('dashboard.ai.custom-provider.store') }}" style="padding:24px;">
            @csrf

            <!-- Provider Presets -->
            <div style="margin-bottom:18px;">
                <label style="font-size:11px;text-transform:uppercase;font-weight:700;color:var(--muted);letter-spacing:0.05em;display:block;margin-bottom:8px;">Quick Presets</label>
                <div style="display:flex;flex-wrap:wrap;gap:6px;">
                    <button type="button" class="btn js-preset-btn" data-name="DeepSeek" data-type="openai" data-url="https://api.deepseek.com/v1" data-model="deepseek-chat" data-desc="DeepSeek AI chat completions" style="padding:4px 10px;font-size:11px;">DeepSeek</button>
                    <button type="button" class="btn js-preset-btn" data-name="Groq" data-type="openai" data-url="https://api.groq.com/openai/v1" data-model="llama-3.3-70b-versatile" data-desc="Groq Ultra-fast inference" style="padding:4px 10px;font-size:11px;">Groq</button>
                    <button type="button" class="btn js-preset-btn" data-name="Ollama (Local)" data-type="openai" data-url="http://localhost:11434/v1" data-model="llama3.2" data-desc="Local self-hosted Ollama server" style="padding:4px 10px;font-size:11px;">Ollama</button>
                    <button type="button" class="btn js-preset-btn" data-name="Together AI" data-type="openai" data-url="https://api.together.xyz/v1" data-model="meta-llama/Llama-3.3-70B-Instruct-Turbo" data-desc="Together AI GPU inference cloud" style="padding:4px 10px;font-size:11px;">Together AI</button>
                    <button type="button" class="btn js-preset-btn" data-name="Mistral AI" data-type="openai" data-url="https://api.mistral.ai/v1" data-model="mistral-large-latest" data-desc="Mistral AI official API" style="padding:4px 10px;font-size:11px;">Mistral AI</button>
                    <button type="button" class="btn js-preset-btn" data-name="xAI Grok" data-type="openai" data-url="https://api.x.ai/v1" data-model="grok-2-latest" data-desc="xAI Grok official API" style="padding:4px 10px;font-size:11px;">xAI Grok</button>
                </div>
            </div>

            <div style="display:grid;grid-template-columns:1fr 1fr;gap:14px;margin-bottom:14px;">
                <div class="dash-field" style="margin-bottom:0;">
                    <label style="font-size:12px;font-weight:600;margin-bottom:4px;display:block;">Provider Display Name *</label>
                    <input type="text" name="name" id="modal-name" required placeholder="e.g. DeepSeek AI" style="width:100%;font-size:13px;padding:8px 12px;background:var(--bg);border:1px solid var(--line);border-radius:6px;color:var(--ink);">
                </div>

                <div class="dash-field" style="margin-bottom:0;">
                    <label style="font-size:12px;font-weight:600;margin-bottom:4px;display:block;">API Protocol / Format *</label>
                    <select name="type" id="modal-type" required style="width:100%;font-size:13px;padding:8px 12px;background:var(--bg);border:1px solid var(--line);border-radius:6px;color:var(--ink);">
                        <option value="openai">OpenAI Compatible (Most Common)</option>
                        <option value="anthropic">Anthropic Compatible</option>
                        <option value="gemini">Google Gemini Compatible</option>
                    </select>
                </div>
            </div>

            <div class="dash-field" style="margin-bottom:14px;">
                <label style="font-size:12px;font-weight:600;margin-bottom:4px;display:block;">API Base Endpoint URL</label>
                <input type="url" name="base_url" id="modal-url" placeholder="https://api.deepseek.com/v1" style="width:100%;font-size:13px;padding:8px 12px;background:var(--bg);border:1px solid var(--line);border-radius:6px;color:var(--ink);font-family:monospace;">
                <span style="font-size:11px;color:var(--muted);margin-top:3px;display:block;">Base URL before <code>/chat/completions</code>.</span>
            </div>

            <div class="dash-field" style="margin-bottom:14px;">
                <label style="font-size:12px;font-weight:600;margin-bottom:4px;display:block;">API Key / Secret Token *</label>
                <div style="position:relative;display:flex;align-items:center;">
                    <input type="password" name="api_key" id="modal-key" required placeholder="sk-..." style="width:100%;font-size:13px;padding:8px 68px 8px 12px;background:var(--bg);border:1px solid var(--line);border-radius:6px;color:var(--ink);font-family:monospace;">
                    <div style="position:absolute;right:6px;display:flex;align-items:center;gap:2px;">
                        <button type="button" class="js-toggle-key-visibility" data-target="modal-key" style="background:none;border:none;cursor:pointer;color:var(--muted);padding:4px;display:flex;align-items:center;" title="View / Hide API Key">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:14px;height:14px;"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                        </button>
                        <button type="button" class="js-copy-key-btn" data-target="modal-key" style="background:none;border:none;cursor:pointer;color:var(--muted);padding:4px;display:flex;align-items:center;" title="Copy API Key to clipboard">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:14px;height:14px;"><rect x="9" y="9" width="13" height="13" rx="2" ry="2"/><path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"/></svg>
                        </button>
                    </div>
                </div>
            </div>

            <div class="dash-field" style="margin-bottom:14px;">
                <label style="font-size:12px;font-weight:600;margin-bottom:4px;display:block;">Model Identifier *</label>
                <input type="text" name="model" id="modal-model" required placeholder="e.g. deepseek-chat, llama-3.3-70b" style="width:100%;font-size:13px;padding:8px 12px;background:var(--bg);border:1px solid var(--line);border-radius:6px;color:var(--ink);font-family:monospace;">
            </div>

            <div class="dash-field" style="margin-bottom:18px;">
                <label style="font-size:12px;font-weight:600;margin-bottom:4px;display:block;">Description / Notes (Optional)</label>
                <input type="text" name="description" id="modal-desc" placeholder="e.g. High speed reasoning model for electrical architectural quotes" style="width:100%;font-size:13px;padding:8px 12px;background:var(--bg);border:1px solid var(--line);border-radius:6px;color:var(--ink);">
            </div>

            <div style="margin-bottom:20px;">
                <label style="display:flex;align-items:center;gap:8px;font-size:13px;cursor:pointer;">
                    <input type="checkbox" name="set_active" value="1" style="accent-color:var(--accent);">
                    <span style="color:var(--ink);font-weight:600;">Set as Active AI Provider immediately upon creation</span>
                </label>
            </div>

            <div style="display:flex;justify-content:flex-end;gap:10px;padding-top:16px;border-top:1px solid var(--line);">
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
    const openModalBtn2 = document.getElementById('btn-open-add-provider-2');
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
    if (openModalBtn2) openModalBtn2.addEventListener('click', showModal);
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

    // Live AI Diagnostic Test Runner
    const btnRunAiTest = document.getElementById('btn-run-ai-test');
    const testDriverSelect = document.getElementById('test-driver-select');
    const testModelInput = document.getElementById('test-model-input');
    const testResultsBox = document.getElementById('ai-test-results');
    const testStatusBadge = document.getElementById('ai-test-status-badge');
    const testLatency = document.getElementById('ai-test-latency');
    const testOutput = document.getElementById('ai-test-output');

    if (btnRunAiTest) {
        btnRunAiTest.addEventListener('click', async () => {
            const originalHtml = btnRunAiTest.innerHTML;
            btnRunAiTest.disabled = true;
            btnRunAiTest.innerHTML = '<span style="display:inline-block;animation:spin 1s linear infinite;">⏳</span> Connecting...';

            testResultsBox.style.display = 'block';
            testStatusBadge.textContent = 'Testing in progress...';
            testStatusBadge.style.background = '#e8f0fe';
            testStatusBadge.style.color = '#1a73e8';
            testLatency.textContent = 'Measuring latency...';
            testOutput.textContent = 'Sending prompt to LLM endpoint...';

            const payload = {
                driver: testDriverSelect ? testDriverSelect.value : 'anthropic',
                model: testModelInput && testModelInput.value.trim() ? testModelInput.value.trim() : null
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
                    testStatusBadge.textContent = 'SUCCESS (200 OK)';
                    testStatusBadge.style.background = '#e6f4ea';
                    testStatusBadge.style.color = '#137333';
                    testLatency.textContent = `Latency: ${data.latency_ms}ms | Tokens: ${data.tokens_used}`;
                    testOutput.textContent = `Driver: ${data.driver}\nModel: ${data.model || 'Default'}\nResponse:\n${data.response}`;
                } else {
                    testStatusBadge.textContent = 'FAILED / ERROR';
                    testStatusBadge.style.background = '#fce8e6';
                    testStatusBadge.style.color = '#c5221f';
                    testLatency.textContent = `Latency: ${data.latency_ms || 0}ms`;
                    testOutput.textContent = `Error Message:\n${data.error || 'Unknown error occurred.'}`;
                }
            } catch (err) {
                testStatusBadge.textContent = 'NETWORK / HTTP ERROR';
                testStatusBadge.style.background = '#fce8e6';
                testStatusBadge.style.color = '#c5221f';
                testLatency.textContent = 'N/A';
                testOutput.textContent = `Request failed: ${err.message}`;
            } finally {
                btnRunAiTest.disabled = false;
                btnRunAiTest.innerHTML = originalHtml;
            }
        });
    }
});
</script>
@endsection
