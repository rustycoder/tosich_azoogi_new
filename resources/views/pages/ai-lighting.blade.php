@extends('layouts.site')

@section('title')
AI Lighting — Intelligent Retail Lighting | Azoogi
@endsection

@section('description')
Azoogi AI lighting for retail: adaptive spectrum, colour temperature, space sensing, and store data insights — beyond illumination.
@endsection

@section('bodyClass', 'ai-page')

@section('chrome', 'full')

@section('topbarClass', 'solid')
@section('logo', 'logo_dark.png')

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/css/ai-lighting.v-1.2.css') }}?v={{ config('app.asset_version') }}">
@endpush

@section('content')
<main class="ai-main">

  <section class="ai-hero">
    <div class="ai-hero-media" aria-hidden="true">
      <img src="/assets/img/ai-lighting/hero.jpg" alt="" loading="eager">
    </div>
    <div class="ai-hero-copy">
      <div class="kicker">AI Lighting</div>
      <h1>Lighting that <span>thinks</span> for retail.</h1>
      <p>Adaptive spectrum. Live store insight. Energy that follows the floor.</p>
    </div>
  </section>

  <section class="ai-band">
    <div class="wrap ai-split">
      <div class="ai-split-copy">
        <div class="kicker">Beyond illumination</div>
        <h2>One intelligent platform.<br><span>Four hard advantages.</span></h2>
        <p>AI-assisted colour recognition, adaptive control, occupancy sensing, and store data — built to sell product, protect stock, and cut waste.</p>
      </div>
      <ol class="ai-caps">
        <li>
          <span class="ai-num">01</span>
          <h3>Adaptive Light Spectrum</h3>
        </li>
        <li>
          <span class="ai-num">02</span>
          <h3>Advanced Space Management</h3>
        </li>
        <li>
          <span class="ai-num">03</span>
          <h3>Intelligent Data Analysis</h3>
        </li>
        <li>
          <span class="ai-num">04</span>
          <h3>Adaptive Colour Temperature</h3>
        </li>
      </ol>
    </div>
  </section>

  <section class="ai-band ai-band--tight">
    <div class="wrap ai-feature">
      <div class="ai-feature-copy">
        <div class="kicker">Spectrum</div>
        <h2>Adaptive light <span>spectrum</span></h2>
        <p>AI tunes the spectrum to product category and colour — so every zone gets the light that makes goods look right, not average.</p>
        <ul class="ai-ticks">
          <li>Category-aware spectrum control</li>
          <li>Colour-true merchandising</li>
          <li>Zone-by-zone optimisation</li>
        </ul>
      </div>
      <div class="ai-compare">
        <figure>
          <img src="/assets/img/ai-lighting/compare-traditional.jpg" alt="Retail display under traditional lighting" loading="lazy">
          <figcaption>Traditional</figcaption>
        </figure>
        <figure class="is-accent">
          <img src="/assets/img/ai-lighting/compare-ai.jpg" alt="Retail display under AI-optimised lighting" loading="lazy">
          <figcaption>AI-optimised</figcaption>
        </figure>
      </div>
    </div>
  </section>

  <section class="ai-insights card-in">
    <div class="wrap-sm">
      <div class="ai-row-head">
        <div>
          <div class="kicker">Insights</div>
          <h2>Business data <span>analysis</span></h2>
        </div>
        <p>Physical-store signals in near real time — views, picks, trends — so the floor runs with the clarity online teams expect.</p>
      </div>

      <div class="container max-width-adaptive-md">
        <ul id="cards" style="--numcards: 2">
          <li class="card-main" id="card_1" style="--index: 1">
            <div class="card__content">
              <div>
                <span class="ai-num">01</span>
                <h2>People counting</h2>
                <p>Spot strengths and gaps in performance, then refine campaigns, pricing, and floor priorities with clearer evidence.</p>
              </div>
              <figure>
                <img src="/assets/img/ai-lighting/people-counting.gif" alt="People counting visualisation">
              </figure>
            </div>
          </li>
          <li class="card-main" id="card_2" style="--index: 2">
            <div class="card__content">
              <div>
                <span class="ai-num">02</span>
                <h2>Store heatmap</h2>
                <p>Track dwell and movement to guide inventory and layout — highlight high- and low-traffic zones that shape the shopper journey.</p>
              </div>
              <figure>
                <img src="/assets/img/ai-lighting/heatmap.gif" alt="Store heatmap visualisation">
              </figure>
            </div>
          </li>
        </ul>
      </div>
    </div>
  </section>

  <section class="ai-cct">
    <div class="ai-cct-media" aria-hidden="true">
      <img src="/assets/img/ai-lighting/cct-bg.jpg" alt="" loading="lazy">
    </div>
    <div class="wrap ai-cct-inner">
      <div class="kicker">Colour</div>
      <h2>Adaptive colour <span>temperature</span></h2>
      <p>AI shifts CCT to merchandise colour and category — so each product is lit to show its true character on the shelf.</p>
    </div>
  </section>

  <section class="ai-band">
    <div class="wrap">
      <div class="ai-row-head">
        <div>
          <div class="kicker">Space</div>
          <h2>Advanced space <span>management</span></h2>
        </div>
        <p>Absence lighting, merchandise care, and energy management in one system — responding to traffic and conditions.</p>
      </div>

      <div class="ai-space">
        <div class="ai-space-visual" id="aiSpaceVisual">
          <img class="is-active" src="/assets/img/ai-lighting/space-absence.gif" alt="Absence lighting" data-panel="0">
          <img src="/assets/img/ai-lighting/space-protect.gif" alt="Commodity protection" data-panel="1">
          <img src="/assets/img/ai-lighting/space-energy.gif" alt="Energy management" data-panel="2">
        </div>

        <div class="ai-accordion" id="aiAccordion">
          <div class="ai-acc-item is-open">
            <button type="button" class="ai-acc-btn" aria-expanded="true">
              <span class="ai-num">01</span>
              <h3>Absence lighting</h3>
              <span class="chev" aria-hidden="true"></span>
            </button>
            <div class="ai-acc-panel">
              <p>Absence sensing dims empty zones — welcoming where shoppers are, lean where they aren’t.</p>
            </div>
          </div>
          <div class="ai-acc-item">
            <button type="button" class="ai-acc-btn" aria-expanded="false">
              <span class="ai-num">02</span>
              <h3>Commodity protection</h3>
              <span class="chev" aria-hidden="true"></span>
            </button>
            <div class="ai-acc-panel">
              <p>Tuned light recipes help shield sensitive materials from unnecessary exposure — preserving look and display life.</p>
            </div>
          </div>
          <div class="ai-acc-item">
            <button type="button" class="ai-acc-btn" aria-expanded="false">
              <span class="ai-num">03</span>
              <h3>Energy management</h3>
              <span class="chev" aria-hidden="true"></span>
            </button>
            <div class="ai-acc-panel">
              <p>Scenes follow live traffic so energy goes where it matters — consistent brand look, lower running cost.</p>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- <section class="ai-band ai-band--soft ai-app">
    <div class="wrap ai-app-grid">
      <div class="ai-app-phones">
        <figure>
          <img src="/assets/img/ai-lighting/app-phone-1.jpg" alt="AI lighting control app on mobile" loading="lazy">
        </figure>
        <figure>
          <img src="/assets/img/ai-lighting/app-phone-2.jpg" alt="Mobile app for store lighting management" loading="lazy">
        </figure>
      </div>
      <div class="ai-app-copy">
        <div class="kicker">Control</div>
        <h2>Lighting <span>control app</span></h2>
        <p>Configure scenes, monitor zones, and manage intelligent lighting from your phone.</p>
        <div class="ai-app-qr">
          <img src="/assets/img/ai-lighting/app-qr.png" width="140" height="140" alt="QR code to contact Azoogi about the control app">
          <p>Scan to get started — talk to us about the control app for your project.</p>
        </div>
      </div>
    </div>
  </section> -->

  <div class="ai-cta-wrap">
    <a class="ai-cta" href="/contact">
      <span class="ai-cta-check" aria-hidden="true">
        <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
          <path d="M5 12.5l4.5 4.5L19 7.5" stroke="currentColor" stroke-width="2.6" stroke-linecap="round" stroke-linejoin="round"/>
        </svg>
      </span>
      <span class="ai-cta-copy">
        <h3>Want to explore AI lighting for your project?</h3>
        <p>Talk to our team about intelligent spectrum, sensing, and retail-ready control.</p>
      </span>
      <span class="btn primary">Contact Us</span>
    </a>
  </div>

</main>
@endsection

@push('scripts')
@verbatim
<script>
document.getElementById('topbar')?.classList.add('solid');

  (function () {
    var items = document.querySelectorAll('#aiAccordion .ai-acc-item');
    var visuals = document.querySelectorAll('#aiSpaceVisual img');
    items.forEach(function (item, index) {
      var btn = item.querySelector('.ai-acc-btn');
      btn.addEventListener('click', function () {
        items.forEach(function (el, i) {
          var open = i === index;
          el.classList.toggle('is-open', open);
          el.querySelector('.ai-acc-btn').setAttribute('aria-expanded', open ? 'true' : 'false');
        });
        visuals.forEach(function (img) {
          img.classList.toggle('is-active', Number(img.getAttribute('data-panel')) === index);
        });
      });
    });
  })();
</script>
@endverbatim
@endpush
