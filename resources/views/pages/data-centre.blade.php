@extends('layouts.site')

@section('title', $page->title)

@section('description', $page->meta_description)

@section('bodyClass', 'dc-page')

@section('chrome', 'full')

@section('topbarClass', '')
@section('logo', 'logo_white.png')

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/css/data-centre.v-1.0.css') }}?v={{ config('app.asset_version') }}">
@endpush

@section('content')
@php
    $whyItems = $meta->group('why.item');
    $hardwareTicks = $meta->list('hardware.tick');
    $controlTicks = $meta->list('control.tick');
    $emergencyItems = $meta->group('emergency.item');
    $zones = $meta->group('zones.item');
@endphp
<main class="dc-main">

  <section class="dc-hero" {!! cms_section_attr('hero') !!}>
    <div class="dc-hero-media" aria-hidden="true">
      <video class="dc-hero-video" autoplay muted loop playsinline preload="auto" poster="{{ media_url($meta->get('hero.poster')) }}">
        <source src="{{ media_url($meta->get('hero.video')) }}" type="video/webm">
      </video>
    </div>
    <div class="dc-hero-copy">
      <div class="kicker"{!! cms_style($meta, 'hero.kicker') !!}>{{ $meta->get('hero.kicker') }}</div>
      <h1{!! cms_style($meta, 'hero.title') !!}>{!! nl2br_html($meta->get('hero.title'), true) !!}</h1>
      <p{!! cms_style($meta, 'hero.lead') !!}>{{ $meta->get('hero.lead') }}</p>
    </div>
  </section>

  <section class="dc-band" {!! cms_section_attr('intro') !!}>
    <div class="wrap dc-intro reveal">
      <p{!! cms_style($meta, 'intro.body') !!}>{{ $meta->get('intro.body') }}</p>
      
      <div class="dc-actions">
        <a href="{{ $meta->get('intro.cta.primary.href', 0, '/contact') }}" class="btn primary"{!! cms_style($meta, 'intro.cta.primary.label') !!}>{{ $meta->get('intro.cta.primary.label') }}</a>
        <a href="{{ $meta->get('intro.cta.secondary.href', 0, '/contact') }}" class="btn"{!! cms_style($meta, 'intro.cta.secondary.label') !!}>{{ $meta->get('intro.cta.secondary.label') }}</a>
      </div>
    </div>
  </section>

  <section class="dc-band dc-band--alt" {!! cms_section_attr('why') !!}>
    <div class="wrap dc-split">
      <div class="dc-split-copy reveal">
        <div class="kicker"{!! cms_style($meta, 'why.kicker') !!}>{{ $meta->get('why.kicker') }}</div>
        <h2{!! cms_style($meta, 'why.heading') !!}>{!! nl2br_html($meta->get('why.heading'), true) !!}</h2>
        <p{!! cms_style($meta, 'why.body') !!}>{{ $meta->get('why.body') }}</p>
      </div>
      <ol class="dc-caps">
        @foreach ($whyItems as $item)
          <li class="reveal" style="transition-delay: {{ $loop->iteration * 0.1 }}s">
            <span class="dc-num">{{ str_pad((string) $loop->iteration, 2, '0', STR_PAD_LEFT) }}</span>
            <h3{!! cms_style($meta, 'why.item.title', $loop->index) !!}>{{ $item['title'] ?? '' }}</h3>
            <p{!! cms_style($meta, 'why.item.body', $loop->index) !!}>{{ $item['body'] ?? '' }}</p>
          </li>
        @endforeach
      </ol>
    </div>
  </section>

  <section class="dc-band dc-band--tight" {!! cms_section_attr('hardware') !!}>
    <div class="wrap dc-feature">
      <div class="dc-feature-copy reveal">
        <div class="kicker"{!! cms_style($meta, 'hardware.kicker') !!}>{{ $meta->get('hardware.kicker') }}</div>
        <h2{!! cms_style($meta, 'hardware.heading') !!}>{!! nl2br_html($meta->get('hardware.heading'), true) !!}</h2>
        <ul class="dc-ticks">
          @foreach ($hardwareTicks as $tick)
            <li{!! cms_style($meta, 'hardware.tick', $loop->index) !!}>{!! labelled_tick($tick) !!}</li>
          @endforeach
        </ul>
      </div>
      <div class="dc-feature-img reveal" style="transition-delay: 0.2s">
        <figure>
          <img src="{{ media_url($meta->get('hardware.image')) }}" alt="{{ $meta->get('hardware.heading') }}" loading="lazy">
        </figure>
      </div>
    </div>
  </section>

  <section class="dc-band dc-band--alt dc-band--tight" {!! cms_section_attr('control') !!}>
    <div class="wrap dc-feature dc-feature--flip">
      <div class="dc-feature-copy reveal">
        <div class="kicker"{!! cms_style($meta, 'control.kicker') !!}>{{ $meta->get('control.kicker') }}</div>
        <h2{!! cms_style($meta, 'control.heading') !!}>{!! nl2br_html($meta->get('control.heading'), true) !!}</h2>
        <ul class="dc-ticks">
          @foreach ($controlTicks as $tick)
            <li{!! cms_style($meta, 'control.tick', $loop->index) !!}>{!! labelled_tick($tick) !!}</li>
          @endforeach
        </ul>
      </div>
      <div class="dc-feature-img reveal" style="transition-delay: 0.2s">
        <figure>
          <img src="{{ media_url($meta->get('control.image')) }}" alt="{{ $meta->get('control.heading') }}" loading="lazy">
        </figure>
      </div>
    </div>
  </section>

  <section class="dc-band">
    <div class="wrap dc-grid-section">
      <div class="dc-grid-col reveal" {!! cms_section_attr('emergency') !!}>
        <h2{!! cms_style($meta, 'emergency.heading') !!}>{!! accent_html($meta->get('emergency.heading'), 'Emergency Lighting') !!}</h2>
        @foreach ($emergencyItems as $item)
          <div class="dc-grid-item">
            <h3{!! cms_style($meta, 'emergency.item.title', $loop->index) !!}>{{ $item['title'] ?? '' }}</h3>
            <p{!! cms_style($meta, 'emergency.item.body', $loop->index) !!}>{{ $item['body'] ?? '' }}</p>
          </div>
        @endforeach
      </div>
      
      <div class="dc-grid-col reveal" style="transition-delay: 0.2s" {!! cms_section_attr('zones') !!}>
        <h2{!! cms_style($meta, 'zones.heading') !!}>{!! accent_html($meta->get('zones.heading'), 'Across All Zones') !!}</h2>
        <ul class="dc-zone-list">
          @foreach ($zones as $item)
            <li{!! cms_style($meta, 'zones.item.title', $loop->index) !!}>{!! labelled_tick(($item['title'] ?? '').': '.($item['body'] ?? '')) !!}</li>
          @endforeach
        </ul>
      </div>
    </div>
  </section>

  <div class="dc-cta-wrap reveal" {!! cms_section_attr('cta') !!}>
    <div class="wrap">
      <div class="dc-cta">
        <div class="dc-cta-copy">
          <h2{!! cms_style($meta, 'cta.heading') !!}>{!! accent_html($meta->get('cta.heading'), 'Data Centre Project?') !!}</h2>
          <p{!! cms_style($meta, 'cta.body') !!}>{{ $meta->get('cta.body') }}</p>
          <div class="dc-actions">
            <a href="{{ $meta->get('cta.primary.href', 0, '/contact') }}" class="btn primary">{{ $meta->get('cta.primary.label') }}</a>
            <a href="{{ $meta->get('cta.secondary.href') }}" class="btn">{{ $meta->get('cta.secondary.label') }}</a>
          </div>
        </div>
      </div>
    </div>
  </div>

</main>
@endsection

@push('scripts')
@verbatim
<script>
const topbar = document.getElementById('topbar');
  let lastScrolled = null;

  function updateLogos() {
    const isScrolled = window.scrollY > 40;
    document.querySelectorAll('.logo img').forEach(img => {
      if (img.closest('.topbar')) {
        img.src = isScrolled ? '/assets/logo_dark.png' : '/assets/logo_white.png';
      } else {
        img.src = '/assets/logo_dark.png';
      }
    });
  }

  const onScroll = () => {
    const isScrolled = window.scrollY > 40;
    if (isScrolled !== lastScrolled) {
      topbar.classList.toggle('solid', isScrolled);
      lastScrolled = isScrolled;
      updateLogos();
    }
  };
  window.addEventListener('scroll', onScroll, { passive: true });
  onScroll();

  const io = new IntersectionObserver((es) => {
    es.forEach(e => { if (e.isIntersecting) { e.target.classList.add('in'); io.unobserve(e.target); } });
  }, { threshold: .12 });
  document.querySelectorAll('.reveal').forEach(el => io.observe(el));
</script>
@endverbatim
@endpush
