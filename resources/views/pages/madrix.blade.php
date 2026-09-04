@extends('layouts.site')

@section('title', $page->title)

@section('description', $page->meta_description)

@section('bodyClass', 'mx-page')

@section('chrome', 'full')

@section('topbarClass', '')
@section('logo', 'logo_white.png')

@push('styles')
<link rel="stylesheet" href="{{ versioned_asset('assets/css/madrix.css') }}">
@endpush

@section('content')
@php
    $slides = $meta->group('slide');
    $whyItems = $meta->group('why.item');
    $hardwareRows = $meta->group('hardware.row');
    $supportItems = $meta->group('support.item');
    $embed = trim($meta->get('video.embed'));
    $videoId = '';
    if (preg_match('/(?:v=|youtu\.be\/|embed\/)([A-Za-z0-9_-]{11})/', $embed, $matches) === 1) {
        $videoId = $matches[1];
    } elseif (preg_match('/^[A-Za-z0-9_-]{11}$/', $embed) === 1) {
        $videoId = $embed;
    }
@endphp
<main class="mx-main">

  <section class="mx-hero" {!! cms_section_attr('slide') !!}>
    <div class="mx-hero-slides" data-mx-slider>
      @foreach ($slides as $slide)
        @php $image = media_url($slide['image'] ?? ''); @endphp
        @if ($image !== '')
          <div class="mx-hero-slide{{ $loop->first ? ' is-active' : '' }}">
            <img src="{{ $image }}" alt="{{ $slide['alt'] ?? '' }}" @if ($loop->first) fetchpriority="high" @else loading="lazy" @endif>
          </div>
        @endif
      @endforeach
    </div>
    <div class="mx-hero-copy" {!! cms_section_attr('hero') !!}>
      <div class="kicker"{!! cms_style($meta, 'hero.kicker') !!}>{{ $meta->get('hero.kicker') }}</div>
      <h1{!! cms_style($meta, 'hero.title') !!}>{!! accent_html($meta->get('hero.title'), 'Pixel Mapping') !!}</h1>
      <p{!! cms_style($meta, 'hero.lead') !!}>{{ $meta->get('hero.lead') }}</p>
      @if (count($slides) > 1)
        <div class="mx-hero-controls">
          <button type="button" class="mx-hero-nav mx-hero-nav--prev" aria-label="Previous image">
            <svg viewBox="0 0 24 24" fill="none" aria-hidden="true">
              <path d="M15 6l-6 6 6 6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
          </button>
          <button type="button" class="mx-hero-nav mx-hero-nav--next" aria-label="Next image">
            <svg viewBox="0 0 24 24" fill="none" aria-hidden="true">
              <path d="M9 6l6 6-6 6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
          </button>
        </div>
      @endif
    </div>
    @if (count($slides) > 1)
      <div class="mx-hero-dots" role="tablist" aria-label="MADRIX project images">
        @foreach ($slides as $slide)
          <button type="button" class="mx-hero-dot{{ $loop->first ? ' is-active' : '' }}" aria-label="Show image {{ $loop->iteration }}" @if ($loop->first) aria-current="true" @endif></button>
        @endforeach
      </div>
    @endif
  </section>

  <section class="mx-band" {!! cms_section_attr('intro') !!}>
    <div class="wrap mx-intro reveal">
      <p{!! cms_style($meta, 'intro.body') !!}>{{ $meta->get('intro.body') }}</p>
    </div>
  </section>

  <section class="mx-band mx-band--alt" {!! cms_section_attr('why') !!}>
    <div class="wrap">
      <div class="mx-section-head reveal">
        <h2{!! cms_style($meta, 'why.heading') !!}>{{ $meta->get('why.heading') }}</h2>
      </div>
      <ol class="mx-caps">
        @foreach ($whyItems as $item)
          <li class="reveal" style="transition-delay: {{ $loop->iteration * 0.08 }}s">
            <span class="mx-num">{{ str_pad((string) $loop->iteration, 2, '0', STR_PAD_LEFT) }}</span>
            <h3{!! cms_style($meta, 'why.item.title', $loop->index) !!}>{{ $item['title'] ?? '' }}</h3>
            <p{!! cms_style($meta, 'why.item.body', $loop->index) !!}>{{ $item['body'] ?? '' }}</p>
          </li>
        @endforeach
      </ol>
    </div>
  </section>

  <section class="mx-band" {!! cms_section_attr('lineup') !!}>
    <div class="wrap">
      <div class="mx-section-head reveal">
        <h2{!! cms_style($meta, 'lineup.heading') !!}>{{ $meta->get('lineup.heading') }}</h2>
      </div>

      <div class="mx-feature reveal" {!! cms_section_attr('software') !!}>
        <div class="mx-feature-copy">
          <h3 class="mx-lineup-heading"{!! cms_style($meta, 'software.heading') !!}>{{ $meta->get('software.heading') }}</h3>
          <div class="kicker"{!! cms_style($meta, 'software.title') !!}>{{ $meta->get('software.title') }}</div>
          <p{!! cms_style($meta, 'software.body') !!}>{{ $meta->get('software.body') }}</p>
        </div>
        @php $softwareImage = media_url($meta->get('software.image')); @endphp
        @if ($softwareImage !== '')
          <div class="mx-feature-img">
            <figure>
              <img src="{{ $softwareImage }}" alt="{{ $meta->get('software.title') }}" loading="lazy">
            </figure>
          </div>
        @endif
      </div>

      <div class="mx-hardware reveal" {!! cms_section_attr('hardware') !!}>
        <h3 class="mx-lineup-heading"{!! cms_style($meta, 'hardware.heading') !!}>{{ $meta->get('hardware.heading') }}</h3>
        <div class="mx-table-wrap">
          <table class="spec-table">
            <thead>
              <tr>
                <th{!! cms_style($meta, 'hardware.col.product') !!}>{{ $meta->get('hardware.col.product') }}</th>
                <th{!! cms_style($meta, 'hardware.col.type') !!}>{{ $meta->get('hardware.col.type') }}</th>
                <th{!! cms_style($meta, 'hardware.col.features') !!}>{{ $meta->get('hardware.col.features') }}</th>
              </tr>
            </thead>
            <tbody>
              @foreach ($hardwareRows as $row)
                @php $preview = media_url($row['image'] ?? ''); @endphp
                <tr>
                  <td>
                    @if ($preview !== '')
                      <span class="mx-product" data-preview="{{ $preview }}" tabindex="0"{!! cms_style($meta, 'hardware.row.product', $loop->index) !!}>{{ $row['product'] ?? '' }}</span>
                    @else
                      <span{!! cms_style($meta, 'hardware.row.product', $loop->index) !!}>{{ $row['product'] ?? '' }}</span>
                    @endif
                  </td>
                  <td{!! cms_style($meta, 'hardware.row.type', $loop->index) !!}>{{ $row['type'] ?? '' }}</td>
                  <td{!! cms_style($meta, 'hardware.row.features', $loop->index) !!}>{{ $row['features'] ?? '' }}</td>
                </tr>
              @endforeach
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </section>

  <section class="mx-band mx-band--alt" {!! cms_section_attr('support') !!}>
    <div class="wrap">
      <div class="mx-section-head reveal">
        <h2{!! cms_style($meta, 'support.heading') !!}>{{ $meta->get('support.heading') }}</h2>
        <p{!! cms_style($meta, 'support.lead') !!}>{{ $meta->get('support.lead') }}</p>
      </div>
      <ul class="mx-support">
        @foreach ($supportItems as $item)
          <li class="reveal" style="transition-delay: {{ $loop->iteration * 0.08 }}s">
            <h3{!! cms_style($meta, 'support.item.title', $loop->index) !!}>{{ $item['title'] ?? '' }}</h3>
            <p{!! cms_style($meta, 'support.item.body', $loop->index) !!}>{{ $item['body'] ?? '' }}</p>
          </li>
        @endforeach
      </ul>
    </div>
  </section>

  @if ($videoId !== '')
    <section class="mx-band" {!! cms_section_attr('video') !!}>
      <div class="wrap">
        <div class="mx-video reveal">
          <iframe
            src="https://www.youtube-nocookie.com/embed/{{ $videoId }}?rel=0&modestbranding=1"
            title="Welcome to the world of MADRIX"
            allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share"
            allowfullscreen
            loading="lazy"
          ></iframe>
        </div>
      </div>
    </section>
  @endif

  <div class="mx-cta-wrap reveal" {!! cms_section_attr('cta') !!}>
    <div class="wrap">
      <div class="mx-cta">
        <h2{!! cms_style($meta, 'cta.heading') !!}>{{ $meta->get('cta.heading') }}</h2>
        <p{!! cms_style($meta, 'cta.body') !!}>{{ $meta->get('cta.body') }}</p>
        <a class="btn primary" href="{{ chrome_url($meta->get('cta.href', 0, '/contact')) }}"{!! cms_style($meta, 'cta.label') !!}>{{ $meta->get('cta.label') }}</a>
      </div>
    </div>
  </div>

  <div class="mx-cursor-preview" data-product-preview="mx-product" hidden aria-hidden="true">
    <img alt="">
  </div>

</main>
@endsection

@push('scripts')
<script src="{{ versioned_asset('assets/js/product-preview.js') }}"></script>
@verbatim
<script>
(function () {
  const topbar = document.getElementById('topbar');
  let lastScrolled = null;

  function updateLogos() {
    const isScrolled = window.scrollY > 40;
    document.querySelectorAll('.logo img').forEach(function (img) {
      if (img.closest('.topbar')) {
        img.src = isScrolled ? '/assets/logo_dark.png' : '/assets/logo_white.png';
      } else {
        img.src = '/assets/logo_dark.png';
      }
    });
  }

  const onScroll = function () {
    const isScrolled = window.scrollY > 40;
    if (isScrolled !== lastScrolled) {
      topbar?.classList.toggle('solid', isScrolled);
      lastScrolled = isScrolled;
      updateLogos();
    }
  };
  window.addEventListener('scroll', onScroll, { passive: true });
  onScroll();

  const io = new IntersectionObserver(function (entries) {
    entries.forEach(function (entry) {
      if (entry.isIntersecting) {
        entry.target.classList.add('in');
        io.unobserve(entry.target);
      }
    });
  }, { threshold: 0.12 });
  document.querySelectorAll('.reveal').forEach(function (el) {
    io.observe(el);
  });

  const root = document.querySelector('[data-mx-slider]');
  if (!root) {
    return;
  }

  const slides = Array.from(root.querySelectorAll('.mx-hero-slide'));
  const dots = Array.from(document.querySelectorAll('.mx-hero-dot'));
  const prev = document.querySelector('.mx-hero-nav--prev');
  const next = document.querySelector('.mx-hero-nav--next');
  if (slides.length < 2) {
    return;
  }

  let index = 0;
  let timer = null;
  const delay = 5000;

  function show(next) {
    index = (next + slides.length) % slides.length;
    slides.forEach(function (slide, i) {
      slide.classList.toggle('is-active', i === index);
    });
    dots.forEach(function (dot, i) {
      const active = i === index;
      dot.classList.toggle('is-active', active);
      if (active) {
        dot.setAttribute('aria-current', 'true');
      } else {
        dot.removeAttribute('aria-current');
      }
    });
  }

  function play() {
    stop();
    timer = window.setInterval(function () {
      show(index + 1);
    }, delay);
  }

  function stop() {
    if (timer) {
      window.clearInterval(timer);
      timer = null;
    }
  }

  dots.forEach(function (dot, i) {
    dot.addEventListener('click', function () {
      show(i);
      play();
    });
  });

  prev?.addEventListener('click', function () {
    show(index - 1);
    play();
  });
  next?.addEventListener('click', function () {
    show(index + 1);
    play();
  });

  const hero = root.closest('.mx-hero');
  hero?.addEventListener('mouseenter', stop);
  hero?.addEventListener('mouseleave', play);

  let startX = 0;
  root.addEventListener('touchstart', function (event) {
    startX = event.changedTouches[0].clientX;
    stop();
  }, { passive: true });
  root.addEventListener('touchend', function (event) {
    const delta = event.changedTouches[0].clientX - startX;
    if (Math.abs(delta) > 40) {
      show(index + (delta < 0 ? 1 : -1));
    }
    play();
  }, { passive: true });

  play();
})();
</script>
@endverbatim
@endpush
