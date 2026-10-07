@extends('layouts.dashboard')

@section('title', 'AI Widget & Branding Customization')

@section('content')
<div class="dash-head">
    <div class="dash-crumb">
        <a href="{{ route('dashboard.home') }}">Dashboard</a>
        <span>/</span>
        <span>AI</span>
        <span>/</span>
        <span>Widget &amp; Branding</span>
    </div>
    <div class="dash-head-title">
        <h1>AI Widget &amp; Branding Customization</h1>
        <div class="dash-head-actions">
            <button class="btn primary" type="submit" form="ai-widget-form">Save Widget Settings</button>
        </div>
    </div>
    <p class="dash-lead">Customize the public AI chat widget persona, assistant icon, display name, header subtitle, startup welcome greeting, starter prompt chips, and pre-chat intake behavior.</p>

    <!-- AI Subnavigation Tabs -->
    <div style="display: flex; gap: 8px; margin-top: 16px; border-bottom: 1px solid var(--line); padding-bottom: 12px; flex-wrap: wrap;">
        <a href="{{ route('dashboard.ai.models') }}" class="btn" style="text-decoration: none; display: inline-flex; align-items: center; gap: 6px;">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width: 14px; height: 14px;"><path d="M12 2v4M12 18v4M4.93 4.93l2.83 2.83M16.24 16.24l2.83 2.83M2 12h4M18 12h4M4.93 19.07l2.83-2.83M16.24 7.76l2.83-2.83"/></svg>
            <span>Models &amp; Rates</span>
        </a>
        <a href="{{ route('dashboard.ai.widget') }}" class="btn" style="background: var(--accent); color: #0b0b0b; font-weight: 700; text-decoration: none; display: inline-flex; align-items: center; gap: 6px;">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width: 14px; height: 14px;"><rect x="3" y="3" width="18" height="18" rx="4"/><path d="M9 3v18M15 9h6M15 15h6"/></svg>
            <span>Widget &amp; Branding</span>
        </a>
        <a href="{{ route('dashboard.ai.knowledge') }}" class="btn" style="text-decoration: none; display: inline-flex; align-items: center; gap: 6px;">
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

<form id="ai-widget-form" method="POST" action="{{ route('dashboard.ai.widget.update') }}">
    @csrf
    @method('PUT')

    <div style="display: grid; grid-template-columns: minmax(0, 1.4fr) minmax(320px, 1fr); gap: 24px; align-items: start;">
        
        <!-- Left Column: Form Controls -->
        <div style="display: flex; flex-direction: column; gap: 24px;">
            
            <!-- Card 1: Persona & Identity -->
            <div class="dash-card" style="padding: 24px;">
                <div style="font-size: 15px; font-weight: 700; color: var(--dash-ink, var(--ink)); margin-bottom: 4px; display: flex; align-items: center; gap: 8px;">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width: 18px; height: 18px; color: var(--accent);"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                    <span>Assistant Persona &amp; Identity</span>
                </div>
                <p style="font-size: 12.5px; color: var(--dash-muted, var(--muted)); margin-top: 0; margin-bottom: 18px;">
                    Configure how the AI introduces itself in the chat drawer header and website launcher.
                </p>

                <!-- Icon / Avatar Selector -->
                <div style="margin-bottom: 20px;">
                    <label style="display: block; font-size: 13px; font-weight: 600; color: var(--dash-ink, var(--ink)); margin-bottom: 8px;">
                        Assistant Avatar Icon
                    </label>
                    <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(100px, 1fr)); gap: 10px;">
                        <!-- Preset 1: Spark -->
                        <label style="cursor: pointer; border: 1px solid {{ ($branding['ai_avatar'] ?? 'spark') === 'spark' ? 'var(--accent)' : 'var(--line)' }}; border-radius: 8px; padding: 12px; text-align: center; background: rgba(255, 255, 255, 0.02); display: flex; flex-direction: column; align-items: center; gap: 6px;" class="avatar-preset-card">
                            <input type="radio" name="ai_avatar" value="spark" {{ ($branding['ai_avatar'] ?? 'spark') === 'spark' ? 'checked' : '' }} style="display: none;" onchange="updateAvatarSelection('spark')">
                            <div style="width: 36px; height: 36px; border-radius: 50%; background: var(--accent); color: #0b0b0b; display: flex; align-items: center; justify-content: center; font-size: 18px;">⚡</div>
                            <span style="font-size: 11px; font-weight: 600; color: var(--dash-ink, var(--ink));">Spark (Default)</span>
                        </label>

                        <!-- Preset 2: Lightbulb -->
                        <label style="cursor: pointer; border: 1px solid {{ ($branding['ai_avatar'] ?? '') === 'lightbulb' ? 'var(--accent)' : 'var(--line)' }}; border-radius: 8px; padding: 12px; text-align: center; background: rgba(255, 255, 255, 0.02); display: flex; flex-direction: column; align-items: center; gap: 6px;" class="avatar-preset-card">
                            <input type="radio" name="ai_avatar" value="lightbulb" {{ ($branding['ai_avatar'] ?? '') === 'lightbulb' ? 'checked' : '' }} style="display: none;" onchange="updateAvatarSelection('lightbulb')">
                            <div style="width: 36px; height: 36px; border-radius: 50%; background: var(--accent); color: #0b0b0b; display: flex; align-items: center; justify-content: center; font-size: 18px;">💡</div>
                            <span style="font-size: 11px; font-weight: 600; color: var(--dash-ink, var(--ink));">Luminaire</span>
                        </label>

                        <!-- Preset 3: Leaf -->
                        <label style="cursor: pointer; border: 1px solid {{ ($branding['ai_avatar'] ?? '') === 'leaf' ? 'var(--accent)' : 'var(--line)' }}; border-radius: 8px; padding: 12px; text-align: center; background: rgba(255, 255, 255, 0.02); display: flex; flex-direction: column; align-items: center; gap: 6px;" class="avatar-preset-card">
                            <input type="radio" name="ai_avatar" value="leaf" {{ ($branding['ai_avatar'] ?? '') === 'leaf' ? 'checked' : '' }} style="display: none;" onchange="updateAvatarSelection('leaf')">
                            <div style="width: 36px; height: 36px; border-radius: 50%; background: var(--accent); color: #0b0b0b; display: flex; align-items: center; justify-content: center; font-size: 18px;">🌿</div>
                            <span style="font-size: 11px; font-weight: 600; color: var(--dash-ink, var(--ink));">Eco Leaf</span>
                        </label>

                        <!-- Preset 4: Building -->
                        <label style="cursor: pointer; border: 1px solid {{ ($branding['ai_avatar'] ?? '') === 'building' ? 'var(--accent)' : 'var(--line)' }}; border-radius: 8px; padding: 12px; text-align: center; background: rgba(255, 255, 255, 0.02); display: flex; flex-direction: column; align-items: center; gap: 6px;" class="avatar-preset-card">
                            <input type="radio" name="ai_avatar" value="building" {{ ($branding['ai_avatar'] ?? '') === 'building' ? 'checked' : '' }} style="display: none;" onchange="updateAvatarSelection('building')">
                            <div style="width: 36px; height: 36px; border-radius: 50%; background: var(--accent); color: #0b0b0b; display: flex; align-items: center; justify-content: center; font-size: 18px;">🏢</div>
                            <span style="font-size: 11px; font-weight: 600; color: var(--dash-ink, var(--ink));">Architectural</span>
                        </label>

                        <!-- Preset 5: Robot -->
                        <label style="cursor: pointer; border: 1px solid {{ ($branding['ai_avatar'] ?? '') === 'robot' ? 'var(--accent)' : 'var(--line)' }}; border-radius: 8px; padding: 12px; text-align: center; background: rgba(255, 255, 255, 0.02); display: flex; flex-direction: column; align-items: center; gap: 6px;" class="avatar-preset-card">
                            <input type="radio" name="ai_avatar" value="robot" {{ ($branding['ai_avatar'] ?? '') === 'robot' ? 'checked' : '' }} style="display: none;" onchange="updateAvatarSelection('robot')">
                            <div style="width: 36px; height: 36px; border-radius: 50%; background: var(--accent); color: #0b0b0b; display: flex; align-items: center; justify-content: center; font-size: 18px;">🤖</div>
                            <span style="font-size: 11px; font-weight: 600; color: var(--dash-ink, var(--ink));">Bot</span>
                        </label>

                        <!-- Preset 6: Custom URL -->
                        <label style="cursor: pointer; border: 1px solid {{ ($branding['ai_avatar'] ?? '') === 'custom' ? 'var(--accent)' : 'var(--line)' }}; border-radius: 8px; padding: 12px; text-align: center; background: rgba(255, 255, 255, 0.02); display: flex; flex-direction: column; align-items: center; gap: 6px;" class="avatar-preset-card">
                            <input type="radio" name="ai_avatar" value="custom" {{ ($branding['ai_avatar'] ?? '') === 'custom' ? 'checked' : '' }} style="display: none;" onchange="updateAvatarSelection('custom')">
                            <div style="width: 36px; height: 36px; border-radius: 50%; background: rgba(255,255,255,0.1); color: var(--dash-ink, var(--ink)); display: flex; align-items: center; justify-content: center; font-size: 14px;">🖼️</div>
                            <span style="font-size: 11px; font-weight: 600; color: var(--dash-ink, var(--ink));">Custom URL</span>
                        </label>
                    </div>

                    <div id="custom-avatar-url-row" style="margin-top: 10px; display: {{ ($branding['ai_avatar'] ?? '') === 'custom' ? 'block' : 'none' }};">
                        <input type="url" name="ai_custom_avatar_url" id="ai_custom_avatar_url" class="dash-input" placeholder="https://example.com/logo.png" value="{{ old('ai_custom_avatar_url', $branding['ai_custom_avatar_url'] ?? '') }}" oninput="updatePreviewAvatar()">
                        <span style="font-size: 11px; color: var(--dash-muted, var(--muted));">Paste full HTTPS URL to custom 1:1 image or SVG avatar.</span>
                    </div>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
                    <div>
                        <label class="dash-label" for="ai_name" style="font-weight: 600; font-size: 13px;">Assistant Display Name</label>
                        <input type="text" name="ai_name" id="ai_name" class="dash-input" required value="{{ old('ai_name', $branding['ai_name'] ?? 'Azoogi AI Assistant') }}" oninput="updateLivePreview()">
                        <span style="font-size: 11px; color: var(--dash-muted, var(--muted));">Shown in chat header and message titles.</span>
                    </div>
                    <div>
                        <label class="dash-label" for="ai_subtitle" style="font-weight: 600; font-size: 13px;">Subtitle / Role Title</label>
                        <input type="text" name="ai_subtitle" id="ai_subtitle" class="dash-input" required value="{{ old('ai_subtitle', $branding['ai_subtitle'] ?? 'Architectural & Smart Controls Specialist') }}" oninput="updateLivePreview()">
                        <span style="font-size: 11px; color: var(--dash-muted, var(--muted));">Displayed under the assistant name.</span>
                    </div>
                </div>
            </div>

            <!-- Card 2: Startup Message & Lead Greeting -->
            <div class="dash-card" style="padding: 24px;">
                <div style="font-size: 15px; font-weight: 700; color: var(--dash-ink, var(--ink)); margin-bottom: 4px; display: flex; align-items: center; gap: 8px;">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width: 18px; height: 18px; color: var(--accent);"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
                    <span>Startup Welcome &amp; Personalized Greetings</span>
                </div>
                <p style="font-size: 12.5px; color: var(--dash-muted, var(--muted)); margin-top: 0; margin-bottom: 18px;">
                    Control the initial message shown to first-time visitors and returning clients.
                </p>

                <div style="margin-bottom: 16px;">
                    <label class="dash-label" for="startup_message" style="font-weight: 600; font-size: 13px;">Default Welcome Message</label>
                    <textarea name="startup_message" id="startup_message" class="dash-textarea" rows="3" required oninput="updateLivePreview()">{{ old('startup_message', $branding['startup_message'] ?? '') }}</textarea>
                    <span style="font-size: 11px; color: var(--dash-muted, var(--muted));">Initial message presented when a visitor opens the chat.</span>
                </div>

                <div>
                    <label class="dash-label" for="lead_greeting_template" style="font-weight: 600; font-size: 13px;">Personalized Greeting Template (After Lead Intake)</label>
                    <textarea name="lead_greeting_template" id="lead_greeting_template" class="dash-textarea" rows="3" placeholder="Hi {name}! Thanks for connecting regarding {project}. How can our architectural engineering team assist with your fixture schedules today?">{{ old('lead_greeting_template', $branding['lead_greeting_template'] ?? '') }}</textarea>
                    <span style="font-size: 11px; color: var(--dash-muted, var(--muted));">Use <code>{name}</code> and <code>{project}</code> placeholders to automatically personalize greeting once visitor fills intake form.</span>
                </div>
            </div>

            <!-- Card 3: Starter Prompt Suggestion Chips -->
            <div class="dash-card" style="padding: 24px;">
                <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 4px;">
                    <div style="font-size: 15px; font-weight: 700; color: var(--dash-ink, var(--ink)); display: flex; align-items: center; gap: 8px;">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width: 18px; height: 18px; color: var(--accent);"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>
                        <span>Starter Prompt Suggestion Chips</span>
                    </div>
                    <button type="button" class="btn" onclick="addStarterChipRow()" style="font-size: 12px; padding: 6px 12px;">+ Add Chip</button>
                </div>
                <p style="font-size: 12.5px; color: var(--dash-muted, var(--muted)); margin-top: 0; margin-bottom: 16px;">
                    Quick 1-click prompt suggestion buttons displayed inside the chat window.
                </p>

                <div id="starter-chips-container" style="display: flex; flex-direction: column; gap: 10px;">
                    @foreach ($branding['starter_chips'] as $i => $chip)
                        <div class="chip-row" style="display: grid; grid-template-columns: 60px 140px 1fr 36px; gap: 8px; align-items: center; background: rgba(255,255,255,0.02); padding: 8px 10px; border-radius: 8px; border: 1px solid var(--line);">
                            <input type="text" name="starter_chips[{{ $i }}][icon]" value="{{ $chip['icon'] ?? '💡' }}" class="dash-input" style="text-align: center;" placeholder="Icon" oninput="updateLivePreview()">
                            <input type="text" name="starter_chips[{{ $i }}][label]" value="{{ $chip['label'] ?? '' }}" class="dash-input" placeholder="Chip Label" required oninput="updateLivePreview()">
                            <input type="text" name="starter_chips[{{ $i }}][prompt]" value="{{ $chip['prompt'] ?? '' }}" class="dash-input" placeholder="User Query Sent to AI" required>
                            <button type="button" class="btn" style="padding: 0; display: flex; align-items: center; justify-content: center; color: #ef4444;" onclick="this.closest('.chip-row').remove(); updateLivePreview();" title="Remove Chip">✕</button>
                        </div>
                    @endforeach
                </div>
            </div>

            <!-- Card 4: Pre-Chat Intake Form Settings -->
            <div class="dash-card" style="padding: 24px;">
                <div style="font-size: 15px; font-weight: 700; color: var(--dash-ink, var(--ink)); margin-bottom: 4px; display: flex; align-items: center; gap: 8px;">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width: 18px; height: 18px; color: var(--accent);"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/><polyline points="10 9 9 9 8 9"/></svg>
                    <span>Pre-Chat Lead Intake Controls</span>
                </div>
                <p style="font-size: 12.5px; color: var(--dash-muted, var(--muted)); margin-top: 0; margin-bottom: 18px;">
                    Control whether visitors see the intake form before chatting and which fields are required.
                </p>

                <div style="display: flex; flex-direction: column; gap: 14px;">
                    <label style="display: flex; align-items: center; gap: 10px; cursor: pointer;">
                        <input type="checkbox" name="intake_enabled" value="1" {{ !empty($branding['intake_enabled']) ? 'checked' : '' }} style="width: 18px; height: 18px; accent-color: var(--accent);">
                        <div>
                            <div style="font-size: 13.5px; font-weight: 600; color: var(--dash-ink, var(--ink));">Enable Pre-Chat Intake Card</div>
                            <div style="font-size: 12px; color: var(--dash-muted, var(--muted));">Prompt new visitors for their Name, Email, and Project Name before their first conversation.</div>
                        </div>
                    </label>

                    <div style="padding-left: 28px; display: flex; flex-direction: column; gap: 10px;">
                        <label style="display: flex; align-items: center; gap: 10px; cursor: pointer;">
                            <input type="checkbox" name="intake_require_project" value="1" {{ !empty($branding['intake_require_project']) ? 'checked' : '' }} style="width: 16px; height: 16px; accent-color: var(--accent);">
                            <span style="font-size: 13px; color: var(--dash-ink, var(--ink));">Make <strong>Project Reference / Name</strong> field strictly mandatory</span>
                        </label>
                        <label style="display: flex; align-items: center; gap: 10px; cursor: pointer;">
                            <input type="checkbox" name="intake_require_email" value="1" {{ !empty($branding['intake_require_email']) ? 'checked' : '' }} style="width: 16px; height: 16px; accent-color: var(--accent);">
                            <span style="font-size: 13px; color: var(--dash-ink, var(--ink));">Make <strong>Work Email</strong> field strictly mandatory</span>
                        </label>
                        <label style="display: flex; align-items: center; gap: 10px; cursor: pointer;">
                            <input type="checkbox" name="intake_require_name" value="1" {{ !empty($branding['intake_require_name']) ? 'checked' : '' }} style="width: 16px; height: 16px; accent-color: var(--accent);">
                            <span style="font-size: 13px; color: var(--dash-ink, var(--ink));">Make <strong>Full Name</strong> field strictly mandatory</span>
                        </label>
                    </div>
                </div>
            </div>

        </div>

        <!-- Right Column: Live Interactive Widget Preview -->
        <div style="position: sticky; top: 24px;">
            <div class="dash-card" style="padding: 20px; border-color: rgba(103, 208, 78, 0.3);">
                <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 12px;">
                    <div style="font-size: 12px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; color: var(--accent);">
                        Live Widget Preview
                    </div>
                    <span class="dash-pill is-active" style="font-size: 10px;">Real-time</span>
                </div>

                <!-- Mockup of Chat Container -->
                <div style="background: rgba(15, 15, 15, 0.96); border: 1px solid rgba(255, 255, 255, 0.12); border-radius: 16px; overflow: hidden; box-shadow: 0 16px 40px rgba(0,0,0,0.5); font-family: 'Inter', sans-serif;">
                    <!-- Preview Header -->
                    <div style="padding: 14px 16px; background: rgba(22, 22, 22, 0.98); border-bottom: 1px solid rgba(255, 255, 255, 0.1); display: flex; align-items: center; justify-content: space-between;">
                        <div style="display: flex; align-items: center; gap: 10px;">
                            <div id="preview-avatar-icon" style="width: 32px; height: 32px; border-radius: 50%; background: #67d04e; color: #0b0b0b; display: flex; align-items: center; justify-content: center; font-size: 16px; font-weight: 700; position: relative;">
                                ⚡
                                <div style="position: absolute; bottom: 0; right: 0; width: 8px; height: 8px; border-radius: 50%; background: #10b981; border: 1.5px solid #161616;"></div>
                            </div>
                            <div>
                                <div id="preview-ai-name" style="font-size: 13px; font-weight: 700; color: #ffffff; line-height: 1.2;">
                                    {{ $branding['ai_name'] ?? 'Azoogi AI Assistant' }}
                                </div>
                                <div id="preview-ai-subtitle" style="font-size: 10.5px; color: #9a9a9a; line-height: 1.2;">
                                    {{ $branding['ai_subtitle'] ?? 'Architectural & Smart Controls Specialist' }}
                                </div>
                            </div>
                        </div>
                        <div style="display: flex; gap: 6px; color: #9a9a9a;">
                            <span style="font-size: 12px;">✕</span>
                        </div>
                    </div>

                    <!-- Preview Body -->
                    <div style="padding: 16px; display: flex; flex-direction: column; gap: 12px; max-height: 380px; overflow-y: auto;">
                        <!-- Starter Chips Block -->
                        <div style="background: rgba(255,255,255,0.04); border: 1px solid rgba(255,255,255,0.1); border-radius: 10px; padding: 10px;">
                            <div style="font-size: 9.5px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; color: #9a9a9a; margin-bottom: 6px;">Suggested Starters</div>
                            <div id="preview-chips-list" style="display: flex; flex-wrap: wrap; gap: 5px;">
                                @foreach ($branding['starter_chips'] as $chip)
                                    <span style="font-size: 11px; background: #222222; border: 1px solid rgba(255,255,255,0.12); color: #f3f3f3; padding: 4px 8px; border-radius: 12px;">
                                        {{ $chip['icon'] ?? '💡' }} {{ $chip['label'] ?? '' }}
                                    </span>
                                @endforeach
                            </div>
                        </div>

                        <!-- Assistant Message Bubble -->
                        <div style="align-self: flex-start; max-width: 90%;">
                            <div id="preview-welcome-bubble" style="background: rgba(255, 255, 255, 0.05); border: 1px solid rgba(255, 255, 255, 0.1); border-radius: 14px; border-bottom-left-radius: 3px; padding: 10px 14px; font-size: 12.5px; color: #f3f3f3; line-height: 1.5;">
                                {{ $branding['startup_message'] ?? 'Welcome to Azoogi Lighting!' }}
                            </div>
                            <div style="font-size: 9px; color: #9a9a9a; margin-top: 3px; padding-left: 2px;">
                                <span id="preview-sender-name">{{ $branding['ai_name'] ?? 'Azoogi AI Assistant' }}</span> &bull; Just now
                            </div>
                        </div>
                    </div>

                    <!-- Preview Input Bar -->
                    <div style="padding: 10px 14px; background: rgba(22, 22, 22, 0.98); border-top: 1px solid rgba(255, 255, 255, 0.1); display: flex; align-items: center; gap: 8px;">
                        <input type="text" placeholder="Ask about beam angles, linear extrusions..." disabled style="flex: 1; background: #222222; border: 1px solid rgba(255, 255, 255, 0.12); border-radius: 20px; padding: 6px 12px; font-size: 11px; color: #9a9a9a;">
                        <button type="button" disabled style="width: 28px; height: 28px; border-radius: 50%; background: #67d04e; border: none; color: #0b0b0b; display: flex; align-items: center; justify-content: center; font-size: 12px; font-weight: 700;">➤</button>
                    </div>
                </div>

                <div style="margin-top: 14px; font-size: 11.5px; color: var(--dash-muted, var(--muted)); text-align: center;">
                    💡 Changes appear instantly in the chat drawer across all public website pages.
                </div>
            </div>
        </div>

    </div>
</form>

@push('scripts')
<script>
const avatarIcons = {
    spark: '⚡',
    lightbulb: '💡',
    leaf: '🌿',
    building: '🏢',
    robot: '🤖'
};

function updateAvatarSelection(preset) {
    document.querySelectorAll('.avatar-preset-card').forEach(card => {
        card.style.borderColor = 'var(--line)';
    });
    const selectedRadio = document.querySelector(`input[name="ai_avatar"][value="${preset}"]`);
    if (selectedRadio) {
        selectedRadio.checked = true;
        selectedRadio.closest('.avatar-preset-card').style.borderColor = 'var(--accent)';
    }

    const customRow = document.getElementById('custom-avatar-url-row');
    if (preset === 'custom') {
        customRow.style.display = 'block';
    } else {
        customRow.style.display = 'none';
    }

    updatePreviewAvatar();
}

function updatePreviewAvatar() {
    const selected = document.querySelector('input[name="ai_avatar"]:checked')?.value || 'spark';
    const previewEl = document.getElementById('preview-avatar-icon');
    if (!previewEl) return;

    if (selected === 'custom') {
        const url = document.getElementById('ai_custom_avatar_url')?.value;
        if (url) {
            previewEl.innerHTML = `<img src="${url}" style="width: 100%; height: 100%; object-fit: cover; border-radius: 50%;"><div style="position: absolute; bottom: 0; right: 0; width: 8px; height: 8px; border-radius: 50%; background: #10b981; border: 1.5px solid #161616;"></div>`;
            return;
        }
    }

    const iconChar = avatarIcons[selected] || '⚡';
    previewEl.innerHTML = `${iconChar}<div style="position: absolute; bottom: 0; right: 0; width: 8px; height: 8px; border-radius: 50%; background: #10b981; border: 1.5px solid #161616;"></div>`;
}

function updateLivePreview() {
    const name = document.getElementById('ai_name')?.value || 'Azoogi AI Assistant';
    const subtitle = document.getElementById('ai_subtitle')?.value || 'Architectural & Smart Controls Specialist';
    const startup = document.getElementById('startup_message')?.value || 'Welcome to Azoogi Lighting!';

    document.getElementById('preview-ai-name').textContent = name;
    document.getElementById('preview-ai-subtitle').textContent = subtitle;
    document.getElementById('preview-welcome-bubble').textContent = startup;
    document.getElementById('preview-sender-name').textContent = name;

    // Update chips preview
    const chipsList = document.getElementById('preview-chips-list');
    if (chipsList) {
        chipsList.innerHTML = '';
        document.querySelectorAll('.chip-row').forEach(row => {
            const icon = row.querySelector('input[name*="[icon]"]')?.value || '💡';
            const label = row.querySelector('input[name*="[label]"]')?.value || '';
            if (label) {
                const span = document.createElement('span');
                span.style.cssText = 'font-size: 11px; background: #222222; border: 1px solid rgba(255,255,255,0.12); color: #f3f3f3; padding: 4px 8px; border-radius: 12px;';
                span.textContent = `${icon} ${label}`;
                chipsList.appendChild(span);
            }
        });
    }

    updatePreviewAvatar();
}

function addStarterChipRow() {
    const container = document.getElementById('starter-chips-container');
    const index = container.querySelectorAll('.chip-row').length + Date.now();
    const row = document.createElement('div');
    row.className = 'chip-row';
    row.style.cssText = 'display: grid; grid-template-columns: 60px 140px 1fr 36px; gap: 8px; align-items: center; background: rgba(255,255,255,0.02); padding: 8px 10px; border-radius: 8px; border: 1px solid var(--line);';
    row.innerHTML = `
        <input type="text" name="starter_chips[${index}][icon]" value="💡" class="dash-input" style="text-align: center;" placeholder="Icon" oninput="updateLivePreview()">
        <input type="text" name="starter_chips[${index}][label]" value="" class="dash-input" placeholder="Chip Label" required oninput="updateLivePreview()">
        <input type="text" name="starter_chips[${index}][prompt]" value="" class="dash-input" placeholder="User Query Sent to AI" required>
        <button type="button" class="btn" style="padding: 0; display: flex; align-items: center; justify-content: center; color: #ef4444;" onclick="this.closest('.chip-row').remove(); updateLivePreview();" title="Remove Chip">✕</button>
    `;
    container.appendChild(row);
}
</script>
@endpush
@endsection
