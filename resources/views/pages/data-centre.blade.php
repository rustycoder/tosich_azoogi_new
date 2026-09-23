@extends('layouts.site')

@section('title', $page->title)

@section('description', $page->meta_description)

@section('bodyClass', 'dc-page')

@section('chrome', 'full')

@section('topbarClass', '')
@section('logo', 'logo_white.png')

@push('styles')
<link rel="stylesheet" href="{{ versioned_asset('assets/css/data-centre.css') }}">
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
      @if (filled($meta->get('hero.video')))
        <video class="dc-hero-video" autoplay muted loop playsinline preload="auto" poster="{{ media_url($meta->get('hero.poster')) }}">
          <source src="{{ media_url($meta->get('hero.video')) }}" type="{{ video_mime_type($meta->get('hero.video')) }}">
        </video>
      @elseif (filled($meta->get('hero.poster')))
        <img src="{{ media_url($meta->get('hero.poster')) }}" alt="{{ $meta->get('hero.title') }}">
      @endif
    </div>
    <div class="dc-hero-copy">
      <h1{!! cms_style($meta, 'hero.title') !!}>{!! accent_html($meta->get('hero.title'), $meta->get('hero.title_accent')) !!}</h1>
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
    <div class="wrap">
      <div class="dc-section-head reveal">
        <h2{!! cms_style($meta, 'why.heading') !!}>{!! accent_html($meta->get('why.heading'), $meta->get('why.heading_accent')) !!}</h2>
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

  <section class="dc-band dc-band--feature">
    <div class="wrap dc-feature" {!! cms_section_attr('hardware') !!}>
      <div class="dc-feature-copy reveal">
        <h2{!! cms_style($meta, 'hardware.heading') !!}>{!! accent_html($meta->get('hardware.heading'), $meta->get('hardware.heading_accent')) !!}</h2>
        <ul class="dc-ticks">
          @foreach ($hardwareTicks as $tick)
            <li{!! cms_style($meta, 'hardware.tick', $loop->index) !!}>{!! labelled_tick($tick) !!}</li>
          @endforeach
        </ul>
      </div>
      @if (filled($meta->get('hardware.image')))
        <div class="dc-feature-img reveal" style="transition-delay: 0.2s">
          <figure>
            <img src="{{ media_url($meta->get('hardware.image')) }}" alt="{{ $meta->get('hardware.heading') }}" loading="lazy">
          </figure>
        </div>
      @endif
    </div>
  </section>

  <section class="dc-band dc-band--alt dc-band--feature">
    <div class="wrap dc-feature dc-feature--flip" {!! cms_section_attr('control') !!}>
      <div class="dc-feature-copy reveal">
        <h2{!! cms_style($meta, 'control.heading') !!}>{!! accent_html($meta->get('control.heading'), $meta->get('control.heading_accent')) !!}</h2>
        <ul class="dc-ticks">
          @foreach ($controlTicks as $tick)
            <li{!! cms_style($meta, 'control.tick', $loop->index) !!}>{!! labelled_tick($tick) !!}</li>
          @endforeach
        </ul>
      </div>
      @if (filled($meta->get('control.image')))
        <div class="dc-feature-img reveal" style="transition-delay: 0.2s">
          <figure>
            <img src="{{ media_url($meta->get('control.image')) }}" alt="{{ $meta->get('control.heading') }}" loading="lazy">
          </figure>
        </div>
      @endif
    </div>
  </section>

  <section class="dc-band dc-band--feature">
    <div class="wrap dc-feature" {!! cms_section_attr('emergency') !!}>
      <div class="dc-feature-copy reveal">
        <h2{!! cms_style($meta, 'emergency.heading') !!}>{!! accent_html($meta->get('emergency.heading'), $meta->get('emergency.heading_accent')) !!}</h2>
        <ul class="dc-ticks">
          @foreach ($emergencyItems as $item)
            <li{!! cms_style($meta, 'emergency.item.title', $loop->index) !!}>{!! labelled_tick(($item['title'] ?? '').': '.($item['body'] ?? '')) !!}</li>
          @endforeach
        </ul>
      </div>
      @if (filled($meta->get('emergency.image')))
        <div class="dc-feature-img reveal" style="transition-delay: 0.2s">
          <figure>
            <img src="{{ media_url($meta->get('emergency.image')) }}" alt="{{ $meta->get('emergency.heading') }}" loading="lazy">
          </figure>
        </div>
      @endif
    </div>
  </section>

  <section class="dc-band dc-band--alt dc-band--feature">
    <div class="wrap dc-feature dc-feature--flip" {!! cms_section_attr('zones') !!}>
      <div class="dc-feature-copy reveal">
        <h2{!! cms_style($meta, 'zones.heading') !!}>{!! accent_html($meta->get('zones.heading'), $meta->get('zones.heading_accent')) !!}</h2>
        <ul class="dc-ticks">
          @foreach ($zones as $item)
            <li{!! cms_style($meta, 'zones.item.title', $loop->index) !!}>{!! labelled_tick(($item['title'] ?? '').': '.($item['body'] ?? '')) !!}</li>
          @endforeach
        </ul>
      </div>
      @if (filled($meta->get('zones.image')))
        <div class="dc-feature-img reveal" style="transition-delay: 0.2s">
          <figure>
            <img src="{{ media_url($meta->get('zones.image')) }}" alt="{{ $meta->get('zones.heading') }}" loading="lazy">
          </figure>
        </div>
      @endif
    </div>
  </section>

  <section class="dc-cta reveal" {!! cms_section_attr('cta') !!}>
    <div class="wrap dc-cta-copy">
      <h2{!! cms_style($meta, 'cta.heading') !!}>{!! accent_html($meta->get('cta.heading'), $meta->get('cta.heading_accent')) !!}</h2>
      <p{!! cms_style($meta, 'cta.body') !!}>{{ $meta->get('cta.body') }}</p>
      <div class="dc-actions">
        <a href="{{ $meta->get('cta.primary.href', 0, '/contact') }}" class="btn primary"{!! cms_style($meta, 'cta.primary.label') !!}>{{ $meta->get('cta.primary.label') }}</a>
        <a href="{{ $meta->get('cta.secondary.href') }}" class="btn"{!! cms_style($meta, 'cta.secondary.label') !!}>{{ $meta->get('cta.secondary.label') }}</a>
      </div>
    </div>
  </section>

</main>
@endsection

@push('scripts')
@verbatim
<script>
const topbar = document.getElementById('topbar');
  let lastScrolled = null;

  const onScroll = () => {
    const isScrolled = window.scrollY > 40;
    if (isScrolled !== lastScrolled) {
      topbar.classList.toggle('solid', isScrolled);
      lastScrolled = isScrolled;
      if (typeof updateLogos === 'function') updateLogos();
    }
  };
  window.addEventListener('scroll', onScroll, { passive: true });
  onScroll();

  const io = new IntersectionObserver((es) => {
    es.forEach(e => { if (e.isIntersecting) { e.target.classList.add('in'); io.unobserve(e.target); } });
  }, { threshold: .12 });
  document.querySelectorAll('.reveal').forEach(el => io.observe(el));

  const dcVideo = document.querySelector('.dc-hero-video');
  if (dcVideo) {
    const playVideo = () => {
      dcVideo.muted = true;
      dcVideo.play().catch(() => {});
    };
    playVideo();
    document.addEventListener('visibilitychange', () => {
      if (!document.hidden) playVideo();
    });
  }
</script>
@endverbatim
@endpush
