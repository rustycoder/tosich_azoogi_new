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
            <button class="btn primary" type="submit" form="ai-rules-form">Save System Rules</button>
        </div>
    </div>
    <p class="dash-lead">Define behavioral guidelines, tone of voice, compliance standards, technical precision, forbidden claims, and escalation triggers across dedicated template sections.</p>
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

<form id="ai-rules-form" method="POST" action="{{ route('dashboard.ai.rules.update') }}">
    @csrf
    @method('PUT')

    <div style="display: grid; grid-template-columns: minmax(0, 1.4fr) minmax(320px, 1fr); gap: 24px; align-items: start;">
        
        <!-- Left Column: Separate Template Fields -->
        <div style="display: flex; flex-direction: column; gap: 20px;">
            
            <!-- Section 1: Tone & Persona -->
            <div class="dash-card" style="padding: 22px;">
                <div style="display: flex; align-items: center; gap: 8px; font-size: 14px; font-weight: 700; color: var(--dash-ink); margin-bottom: 4px;">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width: 17px; height: 17px; color: var(--accent);"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
                    <span>1. Role &amp; Tone of Voice</span>
                </div>
                <p style="font-size: 12px; color: var(--dash-muted); margin: 0 0 12px 0;">
                    Define the conversational style, consultative demeanor, and architectural professionalism expected from the assistant.
                </p>
                <div class="dash-field" style="margin-bottom: 0;">
                    <textarea name="rules_tone" id="rules_tone" class="dash-textarea" rows="3" placeholder="Provide concise, professional, and technically accurate responses tailored for architects, lighting designers, and engineers.">{{ old('rules_tone', $rulesSections['tone'] ?? '') }}</textarea>
                </div>
            </div>

            <!-- Section 2: Standards & Compliance -->
            <div class="dash-card" style="padding: 22px;">
                <div style="display: flex; align-items: center; gap: 8px; font-size: 14px; font-weight: 700; color: var(--dash-ink); margin-bottom: 4px;">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width: 17px; height: 17px; color: var(--accent);"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
                    <span>2. Australian Standards &amp; Building Compliance</span>
                </div>
                <p style="font-size: 12px; color: var(--dash-muted); margin: 0 0 12px 0;">
                    Mandatory compliance guidelines (e.g. AS/NZS 1158, AS/NZS 1680, AS/NZS 60598, and NCC Section J).
                </p>
                <div class="dash-field" style="margin-bottom: 0;">
                    <textarea name="rules_standards" id="rules_standards" class="dash-textarea" rows="3" placeholder="Strictly adhere to Australian Standards (AS/NZS 1158, AS/NZS 1680, AS/NZS 60598, NCC Section J energy compliance).">{{ old('rules_standards', $rulesSections['standards'] ?? '') }}</textarea>
                </div>
            </div>

            <!-- Section 3: Technical Specifications & Suggestions -->
            <div class="dash-card" style="padding: 22px;">
                <div style="display: flex; align-items: center; gap: 8px; font-size: 14px; font-weight: 700; color: var(--dash-ink); margin-bottom: 4px;">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width: 17px; height: 17px; color: var(--accent);"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                    <span>3. Technical Precision &amp; Specification Suggestions</span>
                </div>
                <p style="font-size: 12px; color: var(--dash-muted); margin: 0 0 12px 0;">
                    Directives for suggesting relevant beam angles, CCTs (2700K/3000K/4000K), CRI90+, IP ratings, and control protocols (Casambi, DALI-2).
                </p>
                <div class="dash-field" style="margin-bottom: 0;">
                    <textarea name="rules_specs" id="rules_specs" class="dash-textarea" rows="3" placeholder="Suggest relevant beam angles, CCTs, CRI90+, IP ratings, and control protocols (DALI-2, Casambi BLE mesh, 0-10V, Triac).">{{ old('rules_specs', $rulesSections['specs'] ?? '') }}</textarea>
                </div>
            </div>

            <!-- Section 4: Strict Prohibitions & Forbidden Claims -->
            <div class="dash-card" style="padding: 22px;">
                <div style="display: flex; align-items: center; gap: 8px; font-size: 14px; font-weight: 700; color: var(--dash-ink); margin-bottom: 4px;">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width: 17px; height: 17px; color: #ef4444;"><circle cx="12" cy="12" r="10"/><line x1="4.93" y1="4.93" x2="19.07" y2="19.07"/></svg>
                    <span>4. Strict Prohibitions &amp; Forbidden Claims</span>
                </div>
                <p style="font-size: 12px; color: var(--dash-muted); margin: 0 0 12px 0;">
                    Safeguards against hallucinated product codes, unauthorized discounts, false pricing guarantees, or unverifiable claims.
                </p>
                <div class="dash-field" style="margin-bottom: 0;">
                    <textarea name="rules_prohibitions" id="rules_prohibitions" class="dash-textarea" rows="3" placeholder="Never provide fake product codes, false pricing guarantees, or unverifiable claims.">{{ old('rules_prohibitions', $rulesSections['prohibitions'] ?? '') }}</textarea>
                </div>
            </div>

            <!-- Section 5: Human Escalation & Sales Desk Routing -->
            <div class="dash-card" style="padding: 22px;">
                <div style="display: flex; align-items: center; gap: 8px; font-size: 14px; font-weight: 700; color: var(--dash-ink); margin-bottom: 4px;">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width: 17px; height: 17px; color: var(--accent);"><path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="8.5" cy="7" r="4"/><line x1="20" y1="8" x2="20" y2="14"/><line x1="23" y1="11" x2="17" y2="11"/></svg>
                    <span>5. Human Escalation &amp; Sales Desk Routing</span>
                </div>
                <p style="font-size: 12px; color: var(--dash-muted); margin: 0 0 12px 0;">
                    Define when the AI should prompt the visitor for their contact info or route to the Sydney sales engineering desk.
                </p>
                <div class="dash-field" style="margin-bottom: 0;">
                    <textarea name="rules_escalation" id="rules_escalation" class="dash-textarea" rows="3" placeholder="When discussing custom LED profile lengths, bespoke extrusion joinery, or large project schedules over 50 fittings, direct visitors to our Sydney sales engineering desk.">{{ old('rules_escalation', $rulesSections['escalation'] ?? '') }}</textarea>
                </div>
            </div>

            <!-- Section 6: Additional Directives (Optional) -->
            <div class="dash-card" style="padding: 22px;">
                <div style="display: flex; align-items: center; gap: 8px; font-size: 14px; font-weight: 700; color: var(--dash-ink); margin-bottom: 4px;">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width: 17px; height: 17px; color: var(--dash-muted);"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                    <span>6. Additional Directives (Optional)</span>
                </div>
                <p style="font-size: 12px; color: var(--dash-muted); margin: 0 0 12px 0;">
                    Any additional specialized rules or specific directives for the AI model.
                </p>
                <div class="dash-field" style="margin-bottom: 0;">
                    <textarea name="rules_additional" id="rules_additional" class="dash-textarea" rows="3" placeholder="Add any extra custom rules or instructions here...">{{ old('rules_additional', $rulesSections['additional'] ?? '') }}</textarea>
                </div>
            </div>

        </div>

        <!-- Right Column: Compiled Prompt Inspector & Guidelines -->
        <div style="position: sticky; top: 24px; display: flex; flex-direction: column; gap: 20px;">
            
            <!-- Live System Prompt Inspector -->
            <div class="dash-card" style="padding: 20px; border-color: rgba(103, 208, 78, 0.3);">
                <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 12px;">
                    <div style="font-size: 12px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; color: var(--accent);">
                        Compiled System Prompt Preview
                    </div>
                    <span class="dash-pill is-active" style="font-size: 10px;">Injected into LLM</span>
                </div>
                <p style="font-size: 11.5px; color: var(--dash-muted, var(--muted)); margin-top: 0; margin-bottom: 10px;">
                    All separate rule sections are dynamically assembled and injected into every chat invocation.
                </p>
                <div style="background: var(--dash-fill-strong); border: 1px solid var(--dash-line); border-radius: 8px; padding: 12px; max-height: 380px; overflow-y: auto; font-family: monospace; font-size: 11px; line-height: 1.5; color: var(--accent); white-space: pre-wrap; word-break: break-word;">{{ $compiledPrompt }}</div>
            </div>

            <!-- Best Practices Card -->
            <div class="dash-card" style="padding: 18px;">
                <div style="font-size: 13px; font-weight: 700; color: var(--dash-ink, var(--ink)); margin-bottom: 8px; display: flex; align-items: center; gap: 6px;">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width: 15px; height: 15px; color: var(--accent);"><circle cx="12" cy="12" r="10"/><line x1="12" y1="16" x2="12" y2="12"/><line x1="12" y1="8" x2="12.01" y2="8"/></svg>
                    <span>Rule Engineering Best Practices</span>
                </div>
                <ul style="font-size: 11.5px; color: var(--dash-muted, var(--muted)); margin: 0; padding-left: 18px; line-height: 1.6;">
                    <li>Keep each template section focused on its specific domain.</li>
                    <li>Use affirmative guidance alongside prohibitions to steer model behavior.</li>
                    <li>Specify exact standards codes (e.g. AS/NZS 60598.1) for verified compliance.</li>
                </ul>
            </div>

        </div>

    </div>
</form>
@endsection
