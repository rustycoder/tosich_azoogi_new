@extends('layouts.site')

@section('title')
Test Configuration Studio — Azoogi LED Lighting Solutions
@endsection

@section('description')
Interactive test configuration page for Azoogi LED products. Enter options and constraints JSON to dynamically generate and test product configurations.
@endsection

@section('htmlAttributes')
data-theme="dark"
@endsection

@section('chrome', 'full')

@section('topbarClass', 'solid')
@section('logo', 'logo_dark.png')

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/css/product_detail.css') }}?v={{ config('app.asset_version') }}">
<link rel="stylesheet" href="{{ asset('assets/css/test_configuration.css') }}?v={{ config('app.asset_version') }}">
@endpush

@push('head')
<script src="{{ asset('assets/js/test_configuration.js') }}?v={{ config('app.asset_version') }}" defer></script>
@endpush

@section('content')
<!-- ========== HERO SECTION ========== -->
  <section class="test-page-hero">
    <div class="test-hero-inner">
      <h1 class="test-hero-title">Test Configuration <span>Studio</span></h1>
      <p class="test-hero-subtitle">
        Enter your combined Options and Constraints JSON into the editor field below to dynamically generate the interactive product configuration interface in the exact product details style.
      </p>
    </div>
  </section>

  <!-- ========== MAIN TEST STUDIO LAYOUT ========== -->
  <main class="test-layout">

    <!-- SINGLE COMBINED JSON EDITOR -->
    <div class="editor-card-single">
      <div class="editor-header">
        <div class="editor-title">
          ⚙️ Product Options & Constraints JSON
          <span class="editor-badge" id="json-badge">Valid JSON</span>
        </div>
        <div class="editor-actions">
          <button type="button" class="icon-btn" id="format-json-btn" title="Format JSON">✨ Format JSON</button>
        </div>
      </div>
      <div class="editor-body">
        <textarea id="combined-json-input" class="json-textarea-single" spellcheck="false" placeholder="Enter combined Options and Constraints JSON..."></textarea>
      </div>
      <div class="editor-status">
        <span class="status-indicator valid" id="json-status">✓ Valid JSON Syntax</span>
        <span style="color:var(--muted); font-size:11px;">Schema: Combined object containing "Options" and "Constraints"</span>
      </div>
    </div>

    <!-- GLOBAL ACTION BAR -->
    <div class="action-bar">
      <div class="action-btns">
        <button type="button" class="btn-primary-glow" id="compile-btn">
          ⚡ Generate Configuration
        </button>
      </div>
      <div>
        <label class="toggle-switch">
          <input type="checkbox" id="auto-compile-toggle" class="toggle-checkbox" checked>
          <span>Auto-compile on change</span>
        </label>
      </div>
    </div>

    <!-- ALERT CONTAINER -->
    <div id="alert-container"></div>

    <!-- DYNAMIC PRODUCT DETAIL STYLE CONFIGURATOR WORKSPACE -->
    <section class="options-section" style="margin-top: 20px; border-top: 1px solid var(--line); padding-top: 30px; margin-bottom: 20px;">
      <h2 style="font-family: var(--font-serif); font-size: 32px; margin-bottom: 24px; color: var(--ink);">Generated Product Configuration</h2>
      
      <div class="options-grid-layout" style="display: grid; grid-template-columns: 1.25fr 0.75fr; gap: 48px; align-items: start;">

        <!-- LEFT COLUMN: DYNAMIC CONFIGURATOR WORKSPACE -->
        <div class="config-workspace" id="dynamic-configurator">
          <!-- Dynamically populated options with constraints -->
        </div>

        <!-- RIGHT COLUMN: SELECTED SUMMARY CARD -->
        <div class="config-summary-card" style="background: var(--card-bg); border: 1px solid var(--border-light); border-radius: 8px; padding: 24px; display: flex; flex-direction: column; gap: 20px; position: sticky; top: 100px;">
          <h4 style="font-family: var(--font-serif); font-size: 20px; margin: 0; color: var(--ink); border-bottom: 1px solid var(--line); padding-bottom: 12px;">
            Selected Configuration
          </h4>

          <div id="selected-summary-list" style="display: flex; flex-direction: column; gap: 10px; font-size: 12px; color: var(--muted);">
            <!-- Dynamically populated list from Javascript selection updates -->
          </div>
        </div>

      </div>
    </section>

  </main>

  <!-- ========== FOOTER ========== -->
@endsection
