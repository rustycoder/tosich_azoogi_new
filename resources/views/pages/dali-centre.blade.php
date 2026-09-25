@extends('layouts.site')

@section('title', $page->title)

@section('description', $page->meta_description)

@section('bodyClass', 'dc-page')

@section('chrome', 'full')

@section('topbarClass', '')
@section('logo', 'logo_white.png')

@push('styles')
<link rel="stylesheet" href="{{ versioned_asset('assets/css/dali-centre.css') }}">
@endpush

@section('content')
@php
    $whyItems = $meta->group('why.item');
    $diagramItems = $meta->group('feature.item');
    $hardwareRows = $meta->group('hardware.row');
    $supportItems = $meta->group('support.item');
    $embed = trim($meta->get('video.embed'));
    $videoId = '';
    if (preg_match('/(?:v=|youtu\.be\/|embed\/)([A-Za-z0-9_-]{11})/', $embed, $matches) === 1) {
        $videoId = $matches[1];
    } elseif (preg_match('/^[A-Za-z0-9_-]{11}$/', $embed) === 1) {
        $videoId = $embed;
    }
    $featureImage = media_url($meta->get('feature.image'));
    $featurePath = ltrim((string) $meta->get('feature.image'), '/');
    $hasFeatureImage = $featurePath !== '' && is_file(public_path($featurePath));
@endphp
<main class="dc-main">

  <section class="dc-hero" {!! cms_section_attr('hero') !!}>
    <div class="dc-hero-media" aria-hidden="true">
      @if (filled($meta->get('hero.video')))
        <video class="dc-hero-video" autoplay muted loop playsinline preload="auto" poster="{{ media_url($meta->get('hero.poster', 0, '/assets/img/dali-system/video.jpg')) }}">
          <source src="{{ media_url($meta->get('hero.video')) }}" type="{{ video_mime_type($meta->get('hero.video')) }}">
        </video>
      @elseif (filled($meta->get('hero.poster')))
        <img src="{{ media_url($meta->get('hero.poster', 0, '/assets/img/dali-system/video.jpg')) }}" alt="" loading="eager">
      @endif
    </div>
    <div class="dc-hero-copy">
      <h1 class="dc-title"{!! cms_style($meta, 'hero.title') !!}>{!! accent_html($meta->get('hero.title', 0, 'Azoogi DALI Centre: {Smart DALI-2 Management}')) !!}</h1>
      @php
        $dcLead = $meta->get('hero.lead', 0, 'Centralised DALI-2 lighting management and emergency testing built for Australian compliance and building efficiency.');
        $dcIntro = $meta->get('intro.body', 0, 'Complete control, real-time diagnostic reporting, and scheduled testing across commercial installations.');
      @endphp
      @if (trim($dcLead) !== '')
        <p class="dc-lead"{!! cms_style($meta, 'hero.lead') !!}>{!! accent_html($dcLead) !!}</p>
      @endif
      @if (trim($dcIntro) !== '')
        <p class="dc-intro" {!! cms_section_attr('intro') !!}{!! cms_style($meta, 'intro.body') !!}>{!! accent_html($dcIntro) !!}</p>
      @endif
    </div>
  </section>

  @if ($videoId !== '')
    <section class="dc-band dc-video-band" {!! cms_section_attr('video') !!}>
      <div class="wrap">
        <div class="dc-video reveal">
          <iframe
            src="https://www.youtube-nocookie.com/embed/{{ $videoId }}?rel=0&modestbranding=1"
            title="AZOOGI DALI Centre"
            allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share"
            allowfullscreen
            loading="lazy"
          ></iframe>
        </div>
      </div>
    </section>
  @endif

  <section class="dc-band dc-band--alt" {!! cms_section_attr('why') !!}>
    <div class="wrap">
      <div class="dc-section-head reveal">
        <h2{!! cms_style($meta, 'why.heading') !!}>{{ $meta->get('why.heading') }}</h2>
      </div>
      <ol class="dc-caps">
        @foreach ($whyItems as $item)
          <li class="reveal" style="transition-delay: {{ $loop->iteration * 0.08 }}s">
            <span class="dc-num">{{ str_pad((string) $loop->iteration, 2, '0', STR_PAD_LEFT) }}</span>
            <h3{!! cms_style($meta, 'why.item.title', $loop->index) !!}>{{ $item['title'] ?? '' }}</h3>
            <p{!! cms_style($meta, 'why.item.body', $loop->index) !!}>{{ $item['body'] ?? '' }}</p>
          </li>
        @endforeach
      </ol>
    </div>
  </section>

  @if ($hasFeatureImage)
    <section class="dc-band" {!! cms_section_attr('feature') !!}>
      <div class="wrap dc-diagram reveal">
        <div class="dc-diagram-copy">
          <h2{!! cms_style($meta, 'feature.heading') !!}>{{ $meta->get('feature.heading') }}</h2>
          @if (trim($meta->get('feature.lead')) !== '')
            <p class="dc-diagram-lead"{!! cms_style($meta, 'feature.lead') !!}>{{ $meta->get('feature.lead') }}</p>
          @endif
          @if (count($diagramItems) > 0)
            <ol class="dc-diagram-points">
              @foreach ($diagramItems as $item)
                <li>
                  <span class="dc-num">{{ str_pad((string) $loop->iteration, 2, '0', STR_PAD_LEFT) }}</span>
                  <div>
                    <h3{!! cms_style($meta, 'feature.item.title', $loop->index) !!}>{{ $item['title'] ?? '' }}</h3>
                    <p{!! cms_style($meta, 'feature.item.body', $loop->index) !!}>{{ $item['body'] ?? '' }}</p>
                  </div>
                </li>
              @endforeach
            </ol>
          @endif
        </div>
        <figure class="dc-feature-img">
          <img src="{{ $featureImage }}" alt="AZOOGI DALI system architecture — gateways, sensors, switches and loads on one DALI bus" loading="lazy">
        </figure>
      </div>
    </section>
  @endif

  <section class="dc-band{{ $hasFeatureImage ? ' dc-band--alt' : '' }}" {!! cms_section_attr('hardware') !!}>
    <div class="wrap">
      <div class="dc-section-head reveal">
        <h2{!! cms_style($meta, 'hardware.heading') !!}>{{ $meta->get('hardware.heading') }}</h2>
      </div>
      <div class="dc-table-wrap reveal">
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
                    <span class="dc-product" data-preview="{{ $preview }}" tabindex="0"{!! cms_style($meta, 'hardware.row.product', $loop->index) !!}>{{ $row['product'] ?? '' }}</span>
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
  </section>

  <section class="dc-band dc-band--alt" {!! cms_section_attr('support') !!}>
    <div class="wrap">
      <div class="dc-section-head reveal">
        <h2{!! cms_style($meta, 'support.heading') !!}>{{ $meta->get('support.heading') }}</h2>
        @if (trim($meta->get('support.lead')) !== '')
          <p{!! cms_style($meta, 'support.lead') !!}>{{ $meta->get('support.lead') }}</p>
        @endif
      </div>
      <ul class="dc-support">
        @foreach ($supportItems as $item)
          <li class="reveal" style="transition-delay: {{ $loop->iteration * 0.08 }}s">
            <h3{!! cms_style($meta, 'support.item.title', $loop->index) !!}>{{ $item['title'] ?? '' }}</h3>
            <p{!! cms_style($meta, 'support.item.body', $loop->index) !!}>{{ $item['body'] ?? '' }}</p>
          </li>
        @endforeach
      </ul>
    </div>
  </section>

  <div class="wrap reveal" {!! cms_section_attr('cta') !!}>
      <div class="dc-cta">
        <h2{!! cms_style($meta, 'cta.heading') !!}>{{ $meta->get('cta.heading') }}</h2>
        <p{!! cms_style($meta, 'cta.body') !!}>{{ $meta->get('cta.body') }}</p>
        <a class="btn primary" href="{{ chrome_url($meta->get('cta.href', 0, '/contact')) }}"{!! cms_style($meta, 'cta.label') !!}>{{ $meta->get('cta.label') }}</a>
      </div>
  </div>

  <div class="dc-cursor-preview" data-product-preview="dc-product" hidden aria-hidden="true">
    <img alt="">
  </div>

</main>
@endsection

@push('scripts')
<script src="{{ versioned_asset('assets/js/product-preview.js') }}"></script>
@verbatim
<script>
const topbar = document.getElementById('topbar');
let lastScrolled = null;

const onScroll = () => {
  const isScrolled = window.scrollY > 40;
  if (isScrolled !== lastScrolled) {
    topbar?.classList.toggle('solid', isScrolled);
    lastScrolled = isScrolled;
    if (typeof updateLogos === 'function') updateLogos();
  }
};
window.addEventListener('scroll', onScroll, { passive: true });
onScroll();

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
</script>
@endverbatim
@endpush
