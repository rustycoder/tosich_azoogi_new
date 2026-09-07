@extends('layouts.site')

@section('title')
LED Strip Calculator — Azoogi
@endsection

@section('description')
Build your perfect LED strip setup with Azoogi’s LED Strip Calculator. Choose location, chip, colour, voltage, power and more.
@endsection

@section('bodyClass', 'calc-page')

@section('chrome', 'full')

@section('topbarClass', 'solid')
@section('logo', 'logo_dark.png')

@push('styles')
<link rel="stylesheet" href="{{ versioned_asset('assets/css/led_calculator.css') }}">
@endpush

@section('content')
<!-- Hero -->
<section class="calc-hero">
  <div class="calc-hero-glow" aria-hidden="true"></div>
  <div class="calc-hero-inner">
    <h1 class="h2 calc-hero-title">LED Strip <span>Calculator</span></h1>
    <p class="calc-hero-lead">
      Azoogi’s versatile range of interior and exterior LED Strip Lights is perfect for both residential and commercial spaces.
      With smart control options and custom configurations, creating the ideal lighting solution has never been easier.
      Use our simple selector tools to build your perfect LED strip setup today.
    </p>
    <a href="#led-selector" class="btn primary">Begin LED Selector</a>
  </div>
</section>

<!-- Wizard -->
<section id="led-selector" class="calc-selector">
  <div class="wrap calc-wrap">
    <div class="calc-shell">

      <aside class="calc-steps" aria-label="Calculator steps">
        <ul id="calcStepList">
          <li class="is-active" data-step-label="1" aria-current="step">Location</li>
          <li data-step-label="2">Category</li>
          <li data-step-label="3">Chip Type</li>
          <li data-step-label="4">Color Type</li>
          <li data-step-label="5">Voltage</li>
          <li data-step-label="6">Power</li>
          <li data-step-label="7">Width</li>
          <li data-step-label="8">Driver Type</li>
          <li data-step-label="9">Controller</li>
        </ul>
      </aside>

      <section class="calc-content">
        <div class="steps-container" id="stepsContainer">

          <div class="step is-current" data-step="1">
            <h2>Where will the LED strip be installed?</h2>
            <p class="step-hint" id="step1Hint">Your selection is limited to IP ratings in the current Azoogi catalogue.</p>
            <div class="options options-4" data-key="ip" id="step1Options"></div>
          </div>

          <div class="step" data-step="2" hidden>
            <h2>What category of LED strip do you need?</h2>
            <p class="step-hint" id="step2Hint"></p>
            <div class="options options-3" data-key="group" id="step2Options"></div>
          </div>

          <div class="step" data-step="3" hidden>
            <h2>Chip Type</h2>
            <p class="step-hint" id="step3Hint">Choose the chip technology that suits your needs</p>
            <div class="options options-3" data-key="chip" id="step3Options"></div>
            <div class="chip-info" id="chipInfoDynamic"></div>
          </div>

          <div class="step" data-step="4" hidden>
            <h2 id="step4Title">Select colour</h2>
            <p class="step-hint" id="step4Hint">Options come from matching products in the catalogue.</p>
            <div class="options options-4" data-key="color" id="step4Options"></div>
          </div>

          <div class="step" data-step="5" hidden>
            <h2>Select voltage</h2>
            <p class="step-hint" id="step5Hint">Operating voltages available on the matching products.</p>
            <div class="options options-2" data-key="voltage" id="step5Options"></div>
          </div>

          <div class="step" data-step="6" hidden>
            <h2>Select power output</h2>
            <p class="step-hint" id="step6Hint">Power ratings from the matching Azoogi products.</p>
            <div class="options options-4" data-key="power" id="step6Options"></div>
          </div>

          <div class="step" data-step="7" hidden>
            <h2>Select size</h2>
            <p class="step-hint" id="step7Hint">Strip or neon widths from the matching products.</p>
            <div class="options options-3" data-key="width" id="step7Options"></div>
          </div>

          <div class="step" data-step="8" hidden>
            <h2>Select driver type</h2>
            <p class="step-hint" id="step8Hint">Choose a driver from the catalogue that suits this strip.</p>
            <div class="options options-3" data-key="driver_type" id="step8Options"></div>
          </div>

          <div class="step" data-step="9" hidden>
            <h2>Would you like to add a controller?</h2>
            <p class="step-hint" id="step9Hint">Optional. Only catalogue controllers are listed.</p>
            <div class="options options-4" data-key="controller" id="step9Options"></div>
          </div>

        </div>

        <div class="calc-nav">
          <button type="button" class="btn primary" id="calcNext">Next</button>
          <button type="button" class="btn" id="calcBack" hidden>Go Back</button>
          <p class="calc-nav-hint" id="calcNavHint">Select an option to continue</p>
        </div>
      </section>
    </div>
  </div>
</section>

<!-- Results -->
<section id="led-selector-results" class="calc-results" hidden>
  <div class="wrap calc-wrap">
    <div class="ls-results-container">
      <h2 class="ls-results-title">Your perfect LED strip setup...</h2>
      <div class="ls-results-grid" id="resultsGrid"></div>
    </div>
  </div>
</section>
@endsection

@push('scripts')
<script>window.AZOOGI_LED_CALC = @json($calculatorCatalog, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);</script>
<script src="{{ versioned_asset('assets/js/led_calculator.js') }}"></script>
@endpush
