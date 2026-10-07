@extends('layouts.dashboard')

@section('title', 'Company Context & Manufacturing Policies')

@section('content')
<div class="dash-head">
    <div class="dash-crumb">
        <a href="{{ route('dashboard.home') }}">Dashboard</a>
        <span>/</span>
        <span>AI</span>
        <span>/</span>
        <span>Company Context</span>
    </div>
    <div class="dash-head-title">
        <h1>Company Context &amp; Manufacturing Policies</h1>
        <div class="dash-head-actions">
            <button class="btn primary" type="submit" form="ai-context-form">Save Company Context</button>
        </div>
    </div>
    <p class="dash-lead">Define company background, Sydney engineering facilities, custom manufacturing turnaround times, shipping logistics, warranty policies, and photometric support across dedicated sections.</p>
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

<form id="ai-context-form" method="POST" action="{{ route('dashboard.ai.context.update') }}">
    @csrf
    @method('PUT')

    <div style="display: grid; grid-template-columns: minmax(0, 1.4fr) minmax(320px, 1fr); gap: 24px; align-items: start;">
        
        <!-- Left Column: Separate Section Fields -->
        <div style="display: flex; flex-direction: column; gap: 20px;">
            
            <!-- Section 1: Headquarters & Sydney Facility -->
            <div class="dash-card" style="padding: 22px;">
                <div style="display: flex; align-items: center; gap: 8px; font-size: 14px; font-weight: 700; color: var(--dash-ink); margin-bottom: 4px;">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width: 17px; height: 17px; color: var(--accent);"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>
                    <span>1. Headquarters &amp; Sydney Assembly Facility</span>
                </div>
                <p style="font-size: 12px; color: var(--dash-muted); margin: 0 0 12px 0;">
                    Location and capabilities of Azoogi’s Sydney engineering facility, testing workshop, and local assembly lines.
                </p>
                <div class="dash-field" style="margin-bottom: 0;">
                    <textarea name="context_facility" id="context_facility" class="dash-textarea" rows="3" placeholder="Azoogi Lighting operates a dedicated testing and custom extrusion fabrication facility in Sydney, New South Wales, Australia.">{{ old('context_facility', $contextSections['facility'] ?? '') }}</textarea>
                </div>
            </div>

            <!-- Section 2: Product Ranges & Core Competencies -->
            <div class="dash-card" style="padding: 22px;">
                <div style="display: flex; align-items: center; gap: 8px; font-size: 14px; font-weight: 700; color: var(--dash-ink); margin-bottom: 4px;">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width: 17px; height: 17px; color: var(--accent);"><circle cx="12" cy="12" r="10"/><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>
                    <span>2. Product Ranges &amp; Core Competencies</span>
                </div>
                <p style="font-size: 12px; color: var(--dash-muted); margin: 0 0 12px 0;">
                    Core product catalog focus: architectural linear extrusions, custom LED strip profiles, high-CRI downlights, and smart controls ecosystems.
                </p>
                <div class="dash-field" style="margin-bottom: 0;">
                    <textarea name="context_products" id="context_products" class="dash-textarea" rows="3" placeholder="Architectural linear profiles, custom LED strip extrusions, commercial downlights, track lighting, and smart controls ecosystems (Casambi, DALI-2, MADRIX).">{{ old('context_products', $contextSections['products'] ?? '') }}</textarea>
                </div>
            </div>

            <!-- Section 3: Custom Cutting & Fabrication Turnaround -->
            <div class="dash-card" style="padding: 22px;">
                <div style="display: flex; align-items: center; gap: 8px; font-size: 14px; font-weight: 700; color: var(--dash-ink); margin-bottom: 4px;">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width: 17px; height: 17px; color: var(--accent);"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                    <span>3. Custom Cutting &amp; Fabrication Turnaround</span>
                </div>
                <p style="font-size: 12px; color: var(--dash-muted); margin: 0 0 12px 0;">
                    Turnaround timeframes for custom profile cutting, diffusers, custom joins, endcaps, and factory testing.
                </p>
                <div class="dash-field" style="margin-bottom: 0;">
                    <textarea name="context_fabrication" id="context_fabrication" class="dash-textarea" rows="3" placeholder="Standard 3 to 5 business day turnaround for custom extrusion cutting, diffusers, endcaps, soldering, and photometric testing.">{{ old('context_fabrication', $contextSections['fabrication'] ?? '') }}</textarea>
                </div>
            </div>

            <!-- Section 4: Warehouse, Shipping & Dispatch -->
            <div class="dash-card" style="padding: 22px;">
                <div style="display: flex; align-items: center; gap: 8px; font-size: 14px; font-weight: 700; color: var(--dash-ink); margin-bottom: 4px;">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width: 17px; height: 17px; color: var(--accent);"><rect x="1" y="3" width="15" height="13"/><polygon points="16 8 20 8 23 11 23 16 16 16 16 8"/><circle cx="5.5" cy="18.5" r="2.5"/><circle cx="18.5" cy="18.5" r="2.5"/></svg>
                    <span>4. Warehouse, Shipping &amp; Dispatch</span>
                </div>
                <p style="font-size: 12px; color: var(--dash-muted); margin: 0 0 12px 0;">
                    Dispatch speed and geographic reach across Australia and New Zealand.
                </p>
                <div class="dash-field" style="margin-bottom: 0;">
                    <textarea name="context_dispatch" id="context_dispatch" class="dash-textarea" rows="3" placeholder="Fast dispatch from Sydney warehouse across Australia and New Zealand (in-stock items dispatch within 24-48 hours).">{{ old('context_dispatch', $contextSections['dispatch'] ?? '') }}</textarea>
                </div>
            </div>

            <!-- Section 5: Commercial Warranty Policies -->
            <div class="dash-card" style="padding: 22px;">
                <div style="display: flex; align-items: center; gap: 8px; font-size: 14px; font-weight: 700; color: var(--dash-ink); margin-bottom: 4px;">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width: 17px; height: 17px; color: var(--accent);"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
                    <span>5. Commercial Warranty Policies</span>
                </div>
                <p style="font-size: 12px; color: var(--dash-muted); margin: 0 0 12px 0;">
                    Standard warranty period and terms across architectural fittings, linear extrusions, and certified power supplies.
                </p>
                <div class="dash-field" style="margin-bottom: 0;">
                    <textarea name="context_warranty" id="context_warranty" class="dash-textarea" rows="3" placeholder="5-year standard commercial warranty on architectural luminaires and certified LED drivers.">{{ old('context_warranty', $contextSections['warranty'] ?? '') }}</textarea>
                </div>
            </div>

            <!-- Section 6: Photometrics & Lighting Simulation Support -->
            <div class="dash-card" style="padding: 22px;">
                <div style="display: flex; align-items: center; gap: 8px; font-size: 14px; font-weight: 700; color: var(--dash-ink); margin-bottom: 4px;">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width: 17px; height: 17px; color: var(--accent);"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
                    <span>6. Photometrics &amp; Lighting Simulation Support</span>
                </div>
                <p style="font-size: 12px; color: var(--dash-muted); margin: 0 0 12px 0;">
                    Availability of photometric IES and LDT files for DiaLux and Relux simulations.
                </p>
                <div class="dash-field" style="margin-bottom: 0;">
                    <textarea name="context_photometrics" id="context_photometrics" class="dash-textarea" rows="3" placeholder="IES and LDT photometric data files available for DiaLux and Relux simulations upon request.">{{ old('context_photometrics', $contextSections['photometrics'] ?? '') }}</textarea>
                </div>
            </div>

            <!-- Section 7: Additional Operational Notes (Optional) -->
            <div class="dash-card" style="padding: 22px;">
                <div style="display: flex; align-items: center; gap: 8px; font-size: 14px; font-weight: 700; color: var(--dash-ink); margin-bottom: 4px;">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width: 17px; height: 17px; color: var(--dash-muted);"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                    <span>7. Additional Operational Notes (Optional)</span>
                </div>
                <p style="font-size: 12px; color: var(--dash-muted); margin: 0 0 12px 0;">
                    Any additional operations, contact details, or Sydney facility logistics.
                </p>
                <div class="dash-field" style="margin-bottom: 0;">
                    <textarea name="context_additional" id="context_additional" class="dash-textarea" rows="3" placeholder="Add any extra operational notes or background facts here...">{{ old('context_additional', $contextSections['additional'] ?? '') }}</textarea>
                </div>
            </div>

        </div>

        <!-- Right Column: Live Prompt Inspector & Logistics Information -->
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
                    Company context sections are dynamically compiled and inserted under the enterprise context block.
                </p>
                <div style="background: var(--dash-fill-strong); border: 1px solid var(--dash-line); border-radius: 8px; padding: 12px; max-height: 380px; overflow-y: auto; font-family: monospace; font-size: 11px; line-height: 1.5; color: var(--accent); white-space: pre-wrap; word-break: break-word;">{{ $compiledPrompt }}</div>
            </div>

            <!-- Logistics Reference Card -->
            <div class="dash-card" style="padding: 18px;">
                <div style="font-size: 13px; font-weight: 700; color: var(--dash-ink, var(--ink)); margin-bottom: 8px; display: flex; align-items: center; gap: 6px;">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width: 15px; height: 15px; color: var(--accent);"><rect x="1" y="3" width="15" height="13"/><polygon points="16 8 20 8 23 11 23 16 16 16 16 8"/><circle cx="5.5" cy="18.5" r="2.5"/><circle cx="18.5" cy="18.5" r="2.5"/></svg>
                    <span>Azoogi Operational Facts</span>
                </div>
                <ul style="font-size: 11.5px; color: var(--dash-muted, var(--muted)); margin: 0; padding-left: 18px; line-height: 1.6;">
                    <li><strong>Facility:</strong> Sydney, NSW local cutting and assembly line.</li>
                    <li><strong>Lead Times:</strong> Standard stock dispatched within 24-48h; bespoke extrusions 3-5 business days.</li>
                    <li><strong>Controls:</strong> Certified Casambi, DALI-2, Silvair, and MADRIX integrations.</li>
                </ul>
            </div>

        </div>

    </div>
</form>
@endsection
