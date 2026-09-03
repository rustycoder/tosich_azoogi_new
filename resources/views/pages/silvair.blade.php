@extends('layouts.site')

@section('title', $page->title)

@section('description', $page->meta_description)

@section('bodyClass', 'sv-page')

@section('chrome', 'full')

@section('topbarClass', 'solid')
@section('logo', 'logo_dark.png')

@push('styles')
<link rel="stylesheet" href="{{ versioned_asset('assets/css/silvair.css') }}">
@endpush

@section('content')
@php
    $whyItems = $meta->group('why.item');
    $statsItems = $meta->group('stats.item');
    $pillarItems = $meta->group('pillar.item');
    $standardItems = $meta->group('standard.item');
    $hardwareRows = $meta->group('hardware.row');
    $appsItems = $meta->group('apps.item');
    $flowItems = $meta->group('flow.item');
    $supportItems = $meta->group('support.item');
    $silvairLogo = media_url($meta->get('hero.logo'));
@endphp
<main class="sv-main">

  <section class="sv-hero" {!! cms_section_attr('hero') !!}>
    <div class="wrap">
      <div class="sv-lockup">
        <img class="sv-lockup-azoogi" src="{{ asset('assets/logo_dark.png') }}" alt="Azoogi">
        <span class="sv-lockup-x" aria-hidden="true">×</span>
        @if ($silvairLogo !== '')
          <img class="sv-lockup-silvair" src="{{ $silvairLogo }}" alt="Silvair">
        @endif
      </div>
      <h1 class="sv-title"{!! cms_style($meta, 'hero.title') !!}>{!! accent_html($meta->get('hero.title'), 'Qualified Mesh Lighting') !!}</h1>
      <p class="sv-lead"{!! cms_style($meta, 'hero.lead') !!}>{{ $meta->get('hero.lead') }}</p>
      <p class="sv-intro" {!! cms_section_attr('intro') !!}{!! cms_style($meta, 'intro.body') !!}>{{ $meta->get('intro.body') }}</p>
    </div>
  </section>

  <section class="sv-band sv-band--alt" {!! cms_section_attr('why') !!}>
    <div class="wrap">
      <div class="sv-section-head reveal">
        <h2{!! cms_style($meta, 'why.heading') !!}>{{ $meta->get('why.heading') }}</h2>
      </div>
      <ol class="sv-caps">
        @foreach ($whyItems as $item)
          <li class="reveal" style="transition-delay: {{ $loop->iteration * 0.08 }}s">
            <span class="sv-num">{{ str_pad((string) $loop->iteration, 2, '0', STR_PAD_LEFT) }}</span>
            <h3{!! cms_style($meta, 'why.item.title', $loop->index) !!}>{{ $item['title'] ?? '' }}</h3>
            <p{!! cms_style($meta, 'why.item.body', $loop->index) !!}>{{ $item['body'] ?? '' }}</p>
          </li>
        @endforeach
      </ol>
    </div>
  </section>

  @if (count($statsItems) > 0)
    <section class="sv-band" {!! cms_section_attr('stats') !!}>
      <div class="wrap">
        <div class="sv-stats">
          @foreach ($statsItems as $item)
            <div class="sv-stat reveal" style="transition-delay: {{ $loop->iteration * 0.08 }}s">
              <b{!! cms_style($meta, 'stats.item.value', $loop->index) !!}>{{ $item['value'] ?? '' }}</b>
              <span{!! cms_style($meta, 'stats.item.label', $loop->index) !!}>{{ $item['label'] ?? '' }}</span>
            </div>
          @endforeach
        </div>
      </div>
    </section>
  @endif

  <section class="sv-band sv-band--alt" {!! cms_section_attr('pillar') !!}>
    <div class="wrap">
      <div class="sv-section-head reveal">
        <h2{!! cms_style($meta, 'pillar.heading') !!}>{{ $meta->get('pillar.heading') }}</h2>
        @if (trim($meta->get('pillar.lead')) !== '')
          <p{!! cms_style($meta, 'pillar.lead') !!}>{{ $meta->get('pillar.lead') }}</p>
        @endif
      </div>
      <ul class="sv-pillars">
        @foreach ($pillarItems as $item)
          <li class="reveal" style="transition-delay: {{ $loop->iteration * 0.06 }}s">
            <span class="sv-num">{{ str_pad((string) $loop->iteration, 2, '0', STR_PAD_LEFT) }}</span>
            <h3{!! cms_style($meta, 'pillar.item.title', $loop->index) !!}>{{ $item['title'] ?? '' }}</h3>
            <p{!! cms_style($meta, 'pillar.item.body', $loop->index) !!}>{{ $item['body'] ?? '' }}</p>
          </li>
        @endforeach
      </ul>
    </div>
  </section>

  <section class="sv-band" {!! cms_section_attr('lineup') !!}>
    <div class="wrap">
      <div class="sv-section-head reveal">
        <h2{!! cms_style($meta, 'lineup.heading') !!}>{{ $meta->get('lineup.heading') }}</h2>
      </div>

      <div class="sv-feature reveal" {!! cms_section_attr('software') !!}>
        <div class="sv-feature-copy">
          <h3 class="sv-lineup-heading"{!! cms_style($meta, 'software.heading') !!}>{{ $meta->get('software.heading') }}</h3>
          <div class="kicker"{!! cms_style($meta, 'software.title') !!}>{{ $meta->get('software.title') }}</div>
          <p{!! cms_style($meta, 'software.body') !!}>{{ $meta->get('software.body') }}</p>
        </div>
        @php $softwareImage = media_url($meta->get('software.image')); @endphp
        @if ($softwareImage !== '')
          <div class="sv-feature-img sv-feature-img--contain">
            <figure>
              <img src="{{ $softwareImage }}" alt="{{ $meta->get('software.title') }}" loading="lazy">
            </figure>
          </div>
        @endif
      </div>

      <div class="sv-feature sv-feature--reverse reveal" {!! cms_section_attr('standard') !!}>
        @php $standardImage = media_url($meta->get('standard.image')); @endphp
        @if ($standardImage !== '')
          <div class="sv-feature-img">
            <figure>
              <img src="{{ $standardImage }}" alt="{{ $meta->get('standard.title') }}" loading="lazy">
            </figure>
          </div>
        @endif
        <div class="sv-feature-copy">
          <h3 class="sv-lineup-heading"{!! cms_style($meta, 'standard.heading') !!}>{{ $meta->get('standard.heading') }}</h3>
          <div class="kicker"{!! cms_style($meta, 'standard.title') !!}>{{ $meta->get('standard.title') }}</div>
          <p{!! cms_style($meta, 'standard.body') !!}>{{ $meta->get('standard.body') }}</p>
          @if (count($standardItems) > 0)
            <ul class="sv-points">
              @foreach ($standardItems as $item)
                <li>
                  <b{!! cms_style($meta, 'standard.item.title', $loop->index) !!}>{{ $item['title'] ?? '' }}</b>
                  <span{!! cms_style($meta, 'standard.item.body', $loop->index) !!}>{{ $item['body'] ?? '' }}</span>
                </li>
              @endforeach
            </ul>
          @endif
        </div>
      </div>

      <div class="sv-hardware reveal" {!! cms_section_attr('hardware') !!}>
        <h3 class="sv-lineup-heading"{!! cms_style($meta, 'hardware.heading') !!}>{{ $meta->get('hardware.heading') }}</h3>
        <div class="sv-table-wrap">
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
                      <span class="sv-product" data-preview="{{ $preview }}"{!! cms_style($meta, 'hardware.row.product', $loop->index) !!}>{{ $row['product'] ?? '' }}</span>
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

  <section class="sv-band sv-band--alt" {!! cms_section_attr('apps') !!}>
    <div class="wrap">
      <div class="sv-section-head reveal">
        <h2{!! cms_style($meta, 'apps.heading') !!}>{{ $meta->get('apps.heading') }}</h2>
        @if (trim($meta->get('apps.lead')) !== '')
          <p{!! cms_style($meta, 'apps.lead') !!}>{{ $meta->get('apps.lead') }}</p>
        @endif
      </div>
      <div class="sv-apps">
        @foreach ($appsItems as $item)
          @php $image = media_url($item['image'] ?? ''); @endphp
          <article class="sv-app reveal" style="transition-delay: {{ $loop->iteration * 0.08 }}s">
            @if ($image !== '')
              <figure>
                <img src="{{ $image }}" alt="{{ $item['title'] ?? '' }}" loading="lazy">
              </figure>
            @endif
            <div class="sv-app-copy">
              <h3{!! cms_style($meta, 'apps.item.title', $loop->index) !!}>{{ $item['title'] ?? '' }}</h3>
              <p{!! cms_style($meta, 'apps.item.body', $loop->index) !!}>{{ $item['body'] ?? '' }}</p>
            </div>
          </article>
        @endforeach
      </div>
    </div>
  </section>

  <section class="sv-band" {!! cms_section_attr('flow') !!}>
    <div class="wrap">
      <div class="sv-section-head reveal">
        <h2{!! cms_style($meta, 'flow.heading') !!}>{{ $meta->get('flow.heading') }}</h2>
        @if (trim($meta->get('flow.lead')) !== '')
          <p{!! cms_style($meta, 'flow.lead') !!}>{{ $meta->get('flow.lead') }}</p>
        @endif
      </div>
      <ol class="sv-flow">
        @foreach ($flowItems as $item)
          <li class="reveal" style="transition-delay: {{ $loop->iteration * 0.08 }}s">
            <span class="sv-num">{{ str_pad((string) $loop->iteration, 2, '0', STR_PAD_LEFT) }}</span>
            <h3{!! cms_style($meta, 'flow.item.title', $loop->index) !!}>{{ $item['title'] ?? '' }}</h3>
            <p{!! cms_style($meta, 'flow.item.body', $loop->index) !!}>{{ $item['body'] ?? '' }}</p>
          </li>
        @endforeach
      </ol>
    </div>
  </section>

  <section class="sv-band sv-band--alt" {!! cms_section_attr('support') !!}>
    <div class="wrap">
      <div class="sv-section-head reveal">
        <h2{!! cms_style($meta, 'support.heading') !!}>{{ $meta->get('support.heading') }}</h2>
        @if (trim($meta->get('support.lead')) !== '')
          <p{!! cms_style($meta, 'support.lead') !!}>{{ $meta->get('support.lead') }}</p>
        @endif
      </div>
      <ul class="sv-support">
        @foreach ($supportItems as $item)
          <li class="reveal" style="transition-delay: {{ $loop->iteration * 0.08 }}s">
            <h3{!! cms_style($meta, 'support.item.title', $loop->index) !!}>{{ $item['title'] ?? '' }}</h3>
            <p{!! cms_style($meta, 'support.item.body', $loop->index) !!}>{{ $item['body'] ?? '' }}</p>
          </li>
        @endforeach
      </ul>
    </div>
  </section>

  <div class="sv-cta-wrap reveal" {!! cms_section_attr('cta') !!}>
    <div class="wrap">
      <div class="sv-cta">
        <h2{!! cms_style($meta, 'cta.heading') !!}>{{ $meta->get('cta.heading') }}</h2>
        <p{!! cms_style($meta, 'cta.body') !!}>{{ $meta->get('cta.body') }}</p>
        <a class="btn primary" href="{{ chrome_url($meta->get('cta.href', 0, '/contact')) }}"{!! cms_style($meta, 'cta.label') !!}>{{ $meta->get('cta.label') }}</a>
      </div>
    </div>
  </div>

  <div class="sv-cursor-preview" hidden aria-hidden="true">
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

  const preview = document.querySelector('.sv-cursor-preview');
  const image = preview?.querySelector('img');
  const products = Array.from(document.querySelectorAll('.sv-product[data-preview]'));
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
