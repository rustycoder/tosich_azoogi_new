@extends('layouts.site')

@section('title')
Azoogi Solutions — End-to-End Lighting &amp; Intelligent Controls
@endsection

@section('description')
End-to-end lighting solutions and intelligent controls — Casambi, MADRIX, Silvair and DALI Center ecosystems, plus custom LED capabilities across eight sectors.
@endsection

@section('bodyClass', 'solutions-page')

@section('chrome', 'full')

@section('topbarClass', 'solid')
@section('logo', 'logo_dark.png')

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/css/solutions.v-1.7.css') }}?v={{ config('app.asset_version') }}">
@endpush

@section('content')
<main class="solutions-main">
  <section class="solutions-hero">
    <div class="wrap">
      <div class="solutions-hero-logo">
        <img src="/assets/logo_dark.png" width="280" alt="Azoogi">
      </div>
      <h1 class="solutions-title">End-to-End Lighting Solutions &amp; <span>Intelligent Controls</span></h1>
      <p class="solutions-lead">From initial plans through to final commissioning, we provide complete, custom packages - staying at the absolute forefront of modern lighting technology and smart control automation.</p>
      <p class="solutions-claim">Azoogi does it all.</p>
      <p class="solutions-sub">We design, engineer, customize, supply, control, and commission tailored lighting environments. By pairing custom hardware with seamless intelligent controls, we give you a single, trusted technology partner from concept through to final handover.</p>
    </div>
  </section>

  <section class="solutions-eco" aria-labelledby="ecoTitle">
    <div class="wrap">
      <div class="solutions-eco-head">
        <h2 id="ecoTitle">Explore Our Core Intelligent Control Ecosystems</h2>
        <p>Select a platform below to view technical specifications, system capabilities, and intro videos.</p>
      </div>

      <!-- Each platform needs its own page; swap href="#" for the real URL once built. -->
      <ul class="solutions-eco-grid">
        <li>
          <a class="sol-eco" href="#">
            <span class="sol-eco-name">Casambi</span>
            <span class="sol-eco-sub">Wireless BLE Mesh</span>
            <span class="sol-eco-go">View platform
              <svg viewBox="0 0 24 24" fill="none" aria-hidden="true">
                <path d="M5 12h13M13 6l6 6-6 6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
              </svg>
            </span>
          </a>
        </li>
        <li>
          <a class="sol-eco" href="#">
            <span class="sol-eco-name">MADRIX</span>
            <span class="sol-eco-sub">Pixel Mapping &amp; Visuals</span>
            <span class="sol-eco-go">View platform
              <svg viewBox="0 0 24 24" fill="none" aria-hidden="true">
                <path d="M5 12h13M13 6l6 6-6 6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
              </svg>
            </span>
          </a>
        </li>
        <li>
          <a class="sol-eco" href="#">
            <span class="sol-eco-name">Silvair</span>
            <span class="sol-eco-sub">Enterprise Bluetooth Mesh</span>
            <span class="sol-eco-go">View platform
              <svg viewBox="0 0 24 24" fill="none" aria-hidden="true">
                <path d="M5 12h13M13 6l6 6-6 6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
              </svg>
            </span>
          </a>
        </li>
        <li>
          <a class="sol-eco" href="#">
            <span class="sol-eco-name">DALI Center</span>
            <span class="sol-eco-sub">Centralized DALI-2 Management &amp; Analytics</span>
            <span class="sol-eco-go">View platform
              <svg viewBox="0 0 24 24" fill="none" aria-hidden="true">
                <path d="M5 12h13M13 6l6 6-6 6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
              </svg>
            </span>
          </a>
        </li>
      </ul>

      <a class="solutions-cta" href="/contact">
        <span class="solutions-cta-check" aria-hidden="true">
          <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
            <path d="M5 12.5l4.5 4.5L19 7.5" stroke="currentColor" stroke-width="2.6" stroke-linecap="round" stroke-linejoin="round"/>
          </svg>
        </span>
        <span class="solutions-cta-copy">
          <h3>Need a custom or unlisted application?</h3>
          <p>We design, engineer, and custom-manufacture bespoke LED solutions for any project requirement.</p>
        </span>
        <span class="btn primary">Contact Us</span>
      </a>
    </div>
  </section>

  <section class="solutions-sectors" aria-labelledby="sectorTitle">
    <div class="wrap">
      <div class="solutions-sector-head">
        <h2 id="sectorTitle">Our Lighting Capabilities by <span>Sector</span></h2>
        <p class="solutions-sector-hint">Hover or tap a sector to read the detail.</p>
      </div>

      <ul class="sol-sectors">
        <li class="sol-sector">
          <button type="button" class="sol-sector-inner" aria-expanded="false">
            <span class="sol-sector-face sol-sector-front">
              <span class="sol-sector-num">01</span>
              <span class="sol-sector-title">Commercial &amp; Workspace</span>
            </span>
            <span class="sol-sector-face sol-sector-back">
              <span class="sol-sector-desc">High-efficiency linear extrusion, low-glare task profiles, and acoustic-integrated lighting tailored for modern offices, education campuses, and corporate environments.</span>
            </span>
          </button>
        </li>
        <li class="sol-sector">
          <button type="button" class="sol-sector-inner" aria-expanded="false">
            <span class="sol-sector-face sol-sector-front">
              <span class="sol-sector-num">02</span>
              <span class="sol-sector-title">Urban, Facade &amp; Architectural</span>
            </span>
            <span class="sol-sector-face sol-sector-back">
              <span class="sol-sector-desc">Exterior-rated IP67/IP68 fixtures, dynamic RGBW colour washing, and architectural linear runs engineered to highlight building structures and public spaces.</span>
            </span>
          </button>
        </li>
        <li class="sol-sector">
          <button type="button" class="sol-sector-inner" aria-expanded="false">
            <span class="sol-sector-face sol-sector-front">
              <span class="sol-sector-num">03</span>
              <span class="sol-sector-title">Healthcare &amp; Wellness</span>
            </span>
            <span class="sol-sector-face sol-sector-back">
              <span class="sol-sector-desc">Circadian CCT tuning, high colour accuracy (CRI 95+), flicker-free dimming, and IP-sealed hygienic luminaires for hospitals, laboratories, and care facilities.</span>
            </span>
          </button>
        </li>
        <li class="sol-sector">
          <button type="button" class="sol-sector-inner" aria-expanded="false">
            <span class="sol-sector-face sol-sector-front">
              <span class="sol-sector-num">04</span>
              <span class="sol-sector-title">Retail, Display &amp; Hospitality</span>
            </span>
            <span class="sol-sector-face sol-sector-back">
              <span class="sol-sector-desc">High-R9 accent lighting, precision optical beam shaping, and custom ambient extrusions designed to enhance product textures, dining, and gallery environments.</span>
            </span>
          </button>
        </li>
        <li class="sol-sector">
          <button type="button" class="sol-sector-inner" aria-expanded="false">
            <span class="sol-sector-face sol-sector-front">
              <span class="sol-sector-num">05</span>
              <span class="sol-sector-title">Adverse, Security &amp; Custodial</span>
            </span>
            <span class="sol-sector-face sol-sector-back">
              <span class="sol-sector-desc">Heavy-duty, anti-ligature, and vandal-resistant (IK10+) luminaires built for high-security infrastructure, correctional facilities, and extreme environments.</span>
            </span>
          </button>
        </li>
        <li class="sol-sector">
          <button type="button" class="sol-sector-inner" aria-expanded="false">
            <span class="sol-sector-face sol-sector-front">
              <span class="sol-sector-num">06</span>
              <span class="sol-sector-title">Civil, Sports &amp; Infrastructure</span>
            </span>
            <span class="sol-sector-face sol-sector-back">
              <span class="sol-sector-desc">Built to AS standards for ovals, transit hubs, roadways, and council infrastructure. High-output floodlighting, pole packages, and rugged utility luminaires</span>
            </span>
          </button>
        </li>
        <li class="sol-sector">
          <button type="button" class="sol-sector-inner" aria-expanded="false">
            <span class="sol-sector-face sol-sector-front">
              <span class="sol-sector-num">07</span>
              <span class="sol-sector-title">Emergency &amp; Exit Safety Systems</span>
            </span>
            <span class="sol-sector-face sol-sector-back">
              <span class="sol-sector-desc">AS/NZS 2293-compliant escape route fittings, exit signage, and centralized battery monitoring designed for automated testing and life-safety integration.</span>
            </span>
          </button>
        </li>
        <li class="sol-sector">
          <button type="button" class="sol-sector-inner" aria-expanded="false">
            <span class="sol-sector-face sol-sector-front">
              <span class="sol-sector-num">08</span>
              <span class="sol-sector-title">Bespoke Residential &amp; Lifestyle</span>
            </span>
            <span class="sol-sector-face sol-sector-back">
              <span class="sol-sector-desc">Ultra-slim micro-profiles, plaster-in channels, custom cove extrusions, and tailored powder-coating finishes for luxury homes and high-end living spaces.</span>
            </span>
          </button>
        </li>
      </ul>

      <div class="solutions-sector-cta">
        <a class="btn" href="/data-centre">Explore Data Center Lighting Solutions
          <svg viewBox="0 0 24 24" fill="none" aria-hidden="true">
            <path d="M5 12h13M13 6l6 6-6 6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
          </svg>
        </a>
      </div>
    </div>
  </section>
</main>
@endsection

@push('scripts')
@verbatim
<script>
document.getElementById('topbar')?.classList.add('solid');

  (function () {
    const cards = Array.from(document.querySelectorAll('.sol-sector-inner'));
    if (!cards.length) return;

    /* Pointer devices flip on hover and keyboard focus through CSS alone.
       Touch has neither, so those get an explicit tap toggle. */
    if (window.matchMedia('(hover: hover)').matches) return;

    cards.forEach(function (card) {
      card.addEventListener('click', function () {
        const flipped = card.classList.toggle('is-flipped');
        card.setAttribute('aria-expanded', flipped ? 'true' : 'false');
      });
    });
  })();
</script>
@endverbatim
@endpush
