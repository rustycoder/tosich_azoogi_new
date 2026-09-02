@extends('layouts.site')

@section('title', $page->title)

@section('description', $page->meta_description)

@section('bodyClass', 'cb-page')

@section('chrome', 'full')

@section('topbarClass', 'solid')
@section('logo', 'logo_dark.png')

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/css/casambi.v-1.1.css') }}?v={{ config('app.asset_version') }}">
@endpush

@section('content')
@php
    $whyItems = $meta->group('why.item');
    $hardwareRows = $meta->group('hardware.row');
    $supportItems = $meta->group('support.item');
    $casambiLogo = media_url($meta->get('hero.logo'));
    if ($casambiLogo === '/assets/img/casambi/logo.svg') {
        $casambiLogo = '/assets/img/casambi/logo-dark.svg';
    }
    $embed = trim($meta->get('video.embed'));
    $videoId = '';
    if (preg_match('/(?:v=|youtu\.be\/|embed\/)([A-Za-z0-9_-]{11})/', $embed, $matches) === 1) {
        $videoId = $matches[1];
    } elseif (preg_match('/^[A-Za-z0-9_-]{11}$/', $embed) === 1) {
        $videoId = $embed;
    }
@endphp
<main class="cb-main">

  <section class="cb-hero" {!! cms_section_attr('hero') !!}>
    <div class="wrap">
      <div class="cb-lockup">
        <img class="cb-lockup-azoogi" src="{{ asset('assets/logo_dark.png') }}" alt="Azoogi">
        <span class="cb-lockup-x" aria-hidden="true">×</span>
        @if ($casambiLogo !== '')
          <img class="cb-lockup-casambi" src="{{ $casambiLogo }}" alt="Casambi">
        @endif
      </div>
      <h1 class="cb-title">{!! accent_html($meta->get('hero.title'), 'Smart Ecosystems') !!}</h1>
      <p class="cb-lead">{{ $meta->get('hero.lead') }}</p>
      <p class="cb-intro" {!! cms_section_attr('intro') !!}>{{ $meta->get('intro.body') }}</p>
    </div>
  </section>

  <section class="cb-band cb-band--alt" {!! cms_section_attr('why') !!}>
    <div class="wrap">
      <div class="cb-section-head reveal">
        <h2>{{ $meta->get('why.heading') }}</h2>
      </div>
      <ol class="cb-caps">
        @foreach ($whyItems as $item)
          <li class="reveal" style="transition-delay: {{ $loop->iteration * 0.08 }}s">
            <span class="cb-num">{{ str_pad((string) $loop->iteration, 2, '0', STR_PAD_LEFT) }}</span>
            <h3>{{ $item['title'] ?? '' }}</h3>
            <p>{{ $item['body'] ?? '' }}</p>
          </li>
        @endforeach
      </ol>
    </div>
  </section>

  <section class="cb-band" {!! cms_section_attr('lineup') !!}>
    <div class="wrap">
      <div class="cb-section-head reveal">
        <h2>{{ $meta->get('lineup.heading') }}</h2>
      </div>

      <div class="cb-feature reveal" {!! cms_section_attr('software') !!}>
        <div class="cb-feature-copy">
          <h3 class="cb-lineup-heading">{{ $meta->get('software.heading') }}</h3>
          <div class="kicker">{{ $meta->get('software.title') }}</div>
          <p>{{ $meta->get('software.body') }}</p>
        </div>
        @php $softwareImage = media_url($meta->get('software.image')); @endphp
        @if ($softwareImage !== '')
          <div class="cb-feature-img">
            <figure>
              <img src="{{ $softwareImage }}" alt="{{ $meta->get('software.title') }}" loading="lazy">
            </figure>
          </div>
        @endif
      </div>

      <div class="cb-hardware reveal" {!! cms_section_attr('hardware') !!}>
        <h3 class="cb-lineup-heading">{{ $meta->get('hardware.heading') }}</h3>
        <div class="cb-table-wrap">
          <table class="spec-table">
            <thead>
              <tr>
                <th>{{ $meta->get('hardware.col.product') }}</th>
                <th>{{ $meta->get('hardware.col.type') }}</th>
                <th>{{ $meta->get('hardware.col.features') }}</th>
              </tr>
            </thead>
            <tbody>
              @foreach ($hardwareRows as $row)
                @php $preview = media_url($row['image'] ?? ''); @endphp
                <tr>
                  <td>
                    @if ($preview !== '')
                      <span class="cb-product" data-preview="{{ $preview }}">{{ $row['product'] ?? '' }}</span>
                    @else
                      {{ $row['product'] ?? '' }}
                    @endif
                  </td>
                  <td>{{ $row['type'] ?? '' }}</td>
                  <td>{{ $row['features'] ?? '' }}</td>
                </tr>
              @endforeach
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </section>

  <section class="cb-band cb-band--alt" {!! cms_section_attr('support') !!}>
    <div class="wrap">
      <div class="cb-section-head reveal">
        <h2>{{ $meta->get('support.heading') }}</h2>
        @if (trim($meta->get('support.lead')) !== '')
          <p>{{ $meta->get('support.lead') }}</p>
        @endif
      </div>
      <ul class="cb-support">
        @foreach ($supportItems as $item)
          <li class="reveal" style="transition-delay: {{ $loop->iteration * 0.08 }}s">
            <h3>{{ $item['title'] ?? '' }}</h3>
            <p>{{ $item['body'] ?? '' }}</p>
          </li>
        @endforeach
      </ul>
    </div>
  </section>

  @if ($videoId !== '')
    <section class="cb-band" {!! cms_section_attr('video') !!}>
      <div class="wrap">
        <div class="cb-video reveal">
          <iframe
            src="https://www.youtube-nocookie.com/embed/{{ $videoId }}?rel=0&modestbranding=1"
            title="Casambi wireless lighting control"
            allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share"
            allowfullscreen
            loading="lazy"
          ></iframe>
        </div>
      </div>
    </section>
  @endif

  <div class="cb-cta-wrap reveal" {!! cms_section_attr('cta') !!}>
    <div class="wrap">
      <div class="cb-cta">
        <h2>{{ $meta->get('cta.heading') }}</h2>
        <p>{{ $meta->get('cta.body') }}</p>
        <a class="btn primary" href="{{ chrome_url($meta->get('cta.href', 0, '/contact')) }}">{{ $meta->get('cta.label') }}</a>
      </div>
    </div>
  </div>

  <div class="cb-cursor-preview" hidden aria-hidden="true">
    <img alt="">
  </div>

</main>
@endsection

@push('scripts')
@verbatim
<script>
document.getElementById('topbar')?.classList.add('solid');

(function () {
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
})();

(function () {
  if (!window.matchMedia('(hover: hover) and (pointer: fine)').matches) {
    return;
  }

  const preview = document.querySelector('.cb-cursor-preview');
  const image = preview?.querySelector('img');
  const products = Array.from(document.querySelectorAll('.cb-product[data-preview]'));
  if (!preview || !image || products.length === 0) {
    return;
  }

  let visible = false;
  let mouseX = 0;
  let mouseY = 0;
  let frame = 0;

  function place() {
    const offset = 20;
    const width = preview.offsetWidth;
    const height = preview.offsetHeight;
    const maxX = window.innerWidth - width - 16;
    const maxY = window.innerHeight - height - 16;
    const left = Math.max(16, Math.min(mouseX + offset, maxX));
    const top = Math.max(16, Math.min(mouseY + offset, maxY));
    preview.style.transform = 'translate('+left+'px, '+top+'px)';
    frame = 0;
  }

  function show(src, alt) {
    if (image.getAttribute('src') !== src) {
      image.src = src;
    }
    image.alt = alt;
    preview.hidden = false;
    visible = true;
    place();
  }

  function hide() {
    visible = false;
    preview.hidden = true;
    image.removeAttribute('src');
    image.alt = '';
  }

  products.forEach(function (product) {
    product.addEventListener('mouseenter', function () {
      show(product.getAttribute('data-preview') || '', product.textContent.trim());
    });
    product.addEventListener('mousemove', function (event) {
      mouseX = event.clientX;
      mouseY = event.clientY;
      if (!frame) {
        frame = window.requestAnimationFrame(place);
      }
    });
    product.addEventListener('mouseleave', hide);
  });
})();
</script>
@endverbatim
@endpush
