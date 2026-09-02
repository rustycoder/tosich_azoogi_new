@extends('layouts.site')

@section('title', $page->title)

@section('description', $page->meta_description)

@section('bodyClass', 'about-page')

@section('chrome', 'full')

@section('topbarClass', '')
@section('logo', 'logo_white.png')

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/css/about.v-1.0.css') }}?v={{ config('app.asset_version') }}">
@endpush

@section('content')
@php
    $whyItems = $meta->group('why.item');
    $pathItems = $meta->group('path.item');
@endphp
<main class="about-main">

  <section class="about-hero" {!! cms_section_attr('hero') !!}>
    <div class="about-hero-media" aria-hidden="true">
      <img src="{{ media_url($meta->get('hero.image')) }}" alt="" loading="eager">
    </div>
    <div class="about-hero-copy">
      <div class="kicker"{!! cms_style($meta, 'hero.kicker') !!}>{{ $meta->get('hero.kicker') }}</div>
      <h1{!! cms_style($meta, 'hero.title') !!}>{!! nl2br_html($meta->get('hero.title'), true) !!}</h1>
    </div>
  </section>

  <section class="about-band" {!! cms_section_attr('intro') !!}>
    <div class="wrap about-intro reveal">
      <p{!! cms_style($meta, 'intro.body') !!}>{{ $meta->get('intro.body') }}</p>
      @php
          $ctaLabel = $meta->get('intro.cta.label');
          $ctaHref = $meta->get('intro.cta.href', 0, '/contact');
      @endphp
      @if ($ctaLabel !== '')
        <div class="about-intro-action">
          <a href="{{ chrome_url($ctaHref) }}" class="btn primary"{!! cms_style($meta, 'intro.cta.label') !!}>{{ $ctaLabel }}</a>
        </div>
      @endif
    </div>
  </section>

  <section class="about-band about-band--alt" id="why" {!! cms_section_attr('why') !!}>
    <div class="wrap">
      <div class="about-split-copy about-why-head reveal">
        <div class="kicker"{!! cms_style($meta, 'why.kicker') !!}>{{ $meta->get('why.kicker') }}</div>
        <h2{!! cms_style($meta, 'why.heading') !!}>{!! accent_html($meta->get('why.heading'), 'Azoogi') !!}</h2>
      </div>

      <div class="about-why">
        <div class="about-why-sticky">
          <div class="about-why-visual" id="aboutWhyVisual">
            @foreach ($whyItems as $item)
              <img class="{{ $loop->first ? 'is-active' : '' }}" src="{{ media_url($item['image'] ?? '') }}" alt="" @unless($loop->first) loading="lazy" @endunless data-panel="{{ $loop->index }}">
            @endforeach
            <div class="about-why-meta">
              <span class="about-why-count"><b id="aboutWhyCount">01</b> / {{ str_pad((string) count($whyItems), 2, '0', STR_PAD_LEFT) }}</span>
              <span class="about-why-track"><i class="about-why-bar" id="aboutWhyBar"></i></span>
            </div>
          </div>

          <ol class="about-why-rail" id="aboutWhyRail">
            @foreach ($whyItems as $item)
              <li><button type="button" class="{{ $loop->first ? 'is-active' : '' }}">{{ str_pad((string) $loop->iteration, 2, '0', STR_PAD_LEFT) }}</button></li>
            @endforeach
          </ol>
        </div>

        <ol class="about-why-steps" id="aboutWhySteps">
          @foreach ($whyItems as $item)
            <li class="about-why-step">
              <span class="about-why-ghost" aria-hidden="true">{{ str_pad((string) $loop->iteration, 2, '0', STR_PAD_LEFT) }}</span>
              <h3{!! cms_style($meta, 'why.item.title', $loop->index) !!}>{{ $item['title'] ?? '' }}</h3>
              <p{!! cms_style($meta, 'why.item.body', $loop->index) !!}>{{ $item['body'] ?? '' }}</p>
            </li>
          @endforeach
        </ol>
      </div>
    </div>
  </section>

  <section class="about-reach" {!! cms_section_attr('reach') !!}>
    <div class="about-reach-media" aria-hidden="true">
      <img src="{{ media_url($meta->get('reach.image')) }}" alt="" loading="lazy">
    </div>
    <div class="wrap about-reach-inner reveal">
      <div class="kicker"{!! cms_style($meta, 'reach.kicker') !!}>{{ $meta->get('reach.kicker') }}</div>
      <h2{!! cms_style($meta, 'reach.heading') !!}>{!! accent_html($meta->get('reach.heading'), 'Reach') !!}</h2>
      <p{!! cms_style($meta, 'reach.body') !!}>{{ $meta->get('reach.body') }}</p>
    </div>
  </section>

  <section class="about-band" {!! cms_section_attr('path') !!}>
    <div class="wrap about-path-section">
      <div class="about-split-copy reveal">
        <div class="kicker"{!! cms_style($meta, 'path.kicker') !!}>{{ $meta->get('path.kicker') }}</div>
        <h2{!! cms_style($meta, 'path.heading') !!}>{!! nl2br_html($meta->get('path.heading'), true) !!}</h2>
      </div>
      <div class="about-path-list">
        @foreach ($pathItems as $item)
          <a class="about-path-row reveal" href="{{ $item['href'] ?? '#' }}" @if (! $loop->first) style="transition-delay: {{ ($loop->index * 0.08) }}s" @endif>
            <figure>
              <img src="{{ media_url($item['image'] ?? '') }}" alt="" loading="lazy">
            </figure>
            <div>
              <h3{!! cms_style($meta, 'path.item.title', $loop->index) !!}>{{ $item['title'] ?? '' }}</h3>
              <p{!! cms_style($meta, 'path.item.body', $loop->index) !!}>{{ $item['body'] ?? '' }}</p>
            </div>
          </a>
        @endforeach
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

  (function () {
    const list = document.getElementById('aboutWhySteps');
    if (!list) return;

    const sticky = document.querySelector('.about-why-sticky');
    const steps = Array.from(list.querySelectorAll('.about-why-step'));
    const visuals = Array.from(document.querySelectorAll('#aboutWhyVisual img'));
    const rail = Array.from(document.querySelectorAll('#aboutWhyRail button'));
    const bar = document.getElementById('aboutWhyBar');
    const count = document.getElementById('aboutWhyCount');
    if (!sticky || !steps.length) return;

    list.classList.add('is-live');

    let current = -1;

    function setActive(index) {
      if (index < 0 || index === current) return;
      current = index;
      steps.forEach(function (el, i) { el.classList.toggle('is-active', i === index); });
      visuals.forEach(function (img, i) { img.classList.toggle('is-active', i === index); });
      rail.forEach(function (btn, i) { btn.classList.toggle('is-active', i === index); });
      if (bar) bar.style.width = ((index + 1) / steps.length * 100) + '%';
      if (count) count.textContent = ('0' + (index + 1)).slice(-2);
    }

    function readingCentre() {
      const viewport = window.innerHeight;
      const stickyBox = sticky.getBoundingClientRect();
      const listBox = list.getBoundingClientRect();
      const sideBySide = stickyBox.right <= listBox.left + 2 ||
        listBox.right <= stickyBox.left + 2;
      if (sideBySide) return viewport / 2;
      const top = Math.min(Math.max(stickyBox.bottom, 0), viewport * 0.6);
      return (top + viewport) / 2;
    }

    function sync() {
      const listBox = list.getBoundingClientRect();
      if (listBox.bottom < 0 || listBox.top > window.innerHeight) return;
      const centre = readingCentre();
      let best = 0;
      let bestDistance = Infinity;
      steps.forEach(function (el, i) {
        const box = el.getBoundingClientRect();
        const distance = Math.abs(box.top + box.height / 2 - centre);
        if (distance < bestDistance) {
          bestDistance = distance;
          best = i;
        }
      });
      setActive(best);
    }

    rail.forEach(function (btn, i) {
      btn.addEventListener('click', function () {
        const box = steps[i].getBoundingClientRect();
        window.scrollTo({
          top: box.top + window.scrollY + box.height / 2 - readingCentre(),
          behavior: 'smooth'
        });
      });
    });

    window.addEventListener('scroll', sync, { passive: true });
    window.addEventListener('resize', sync);
    window.addEventListener('orientationchange', sync);
    sync();
  })();
</script>
@endverbatim
@endpush
