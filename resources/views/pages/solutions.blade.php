@extends('layouts.site')

@section('title', $page->title)

@section('description', $page->meta_description)

@section('bodyClass', 'solutions-page')

@section('chrome', 'full')

@section('topbarClass', 'solid')
@section('logo', 'logo_dark.png')

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/css/solutions.v-1.7.css') }}?v={{ config('app.asset_version') }}">
@endpush

@section('content')
@php
    $ecosystems = $meta->group('eco.item');
    $sectors = $meta->group('sector.item');
@endphp
<main class="solutions-main">
  <section class="solutions-hero" {!! cms_section_attr('hero') !!}>
    <div class="wrap">
      <div class="solutions-hero-logo">
        <img src="/assets/logo_dark.png" width="280" alt="Azoogi">
      </div>
      <h1 class="solutions-title">{!! accent_html($meta->get('hero.title'), 'Intelligent Controls') !!}</h1>
      <p class="solutions-lead">{{ $meta->get('hero.lead') }}</p>
      <p class="solutions-claim">{{ $meta->get('hero.claim') }}</p>
      <p class="solutions-sub">{{ $meta->get('hero.sub') }}</p>
    </div>
  </section>

  <section class="solutions-eco" aria-labelledby="ecoTitle" {!! cms_section_attr('eco') !!}>
    <div class="wrap">
      <div class="solutions-eco-head">
        <h2 id="ecoTitle">{{ $meta->get('eco.heading') }}</h2>
        <p>{{ $meta->get('eco.lead') }}</p>
      </div>

      <ul class="solutions-eco-grid">
        @foreach ($ecosystems as $item)
          <li>
            <a class="sol-eco" href="{{ $item['href'] ?? '#' }}">
              <span class="sol-eco-name">{{ $item['name'] ?? '' }}</span>
              <span class="sol-eco-sub">{{ $item['sub'] ?? '' }}</span>
              <span class="sol-eco-go">View platform
                <svg viewBox="0 0 24 24" fill="none" aria-hidden="true">
                  <path d="M5 12h13M13 6l6 6-6 6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
              </span>
            </a>
          </li>
        @endforeach
      </ul>

      <a class="solutions-cta" href="{{ $meta->get('eco.cta.href', 0, '/contact') }}">
        <span class="solutions-cta-check" aria-hidden="true">
          <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
            <path d="M5 12.5l4.5 4.5L19 7.5" stroke="currentColor" stroke-width="2.6" stroke-linecap="round" stroke-linejoin="round"/>
          </svg>
        </span>
        <span class="solutions-cta-copy">
          <h3>{{ $meta->get('eco.cta.heading') }}</h3>
          <p>{{ $meta->get('eco.cta.body') }}</p>
        </span>
        <span class="btn primary">{{ $meta->get('eco.cta.label') }}</span>
      </a>
    </div>
  </section>

  <section class="solutions-sectors" aria-labelledby="sectorTitle" {!! cms_section_attr('sector') !!}>
    <div class="wrap">
      <div class="solutions-sector-head">
        <h2 id="sectorTitle">{!! accent_html($meta->get('sector.heading'), 'Sector') !!}</h2>
        <p class="solutions-sector-hint">{{ $meta->get('sector.hint') }}</p>
      </div>

      <ul class="sol-sectors">
        @foreach ($sectors as $item)
          <li class="sol-sector">
            <button type="button" class="sol-sector-inner" aria-expanded="false">
              <span class="sol-sector-face sol-sector-front">
                <span class="sol-sector-num">{{ str_pad((string) $loop->iteration, 2, '0', STR_PAD_LEFT) }}</span>
                <span class="sol-sector-title">{{ $item['title'] ?? '' }}</span>
              </span>
              <span class="sol-sector-face sol-sector-back">
                <span class="sol-sector-desc">{{ $item['body'] ?? '' }}</span>
              </span>
            </button>
          </li>
        @endforeach
      </ul>

      <div class="solutions-sector-cta">
        <a class="btn" href="{{ $meta->get('sector.cta.href', 0, '/data-centre') }}">{{ $meta->get('sector.cta.label') }}
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
