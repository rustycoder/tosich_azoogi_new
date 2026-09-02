@extends('layouts.site')

@section('title', $page->title)

@section('description', $page->meta_description)

@section('bodyClass', 'dc-page')

@section('chrome', 'full')

@section('topbarClass', 'solid')
@section('logo', 'logo_dark.png')

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/css/dali-centre.v-1.0.css') }}?v={{ config('app.asset_version') }}">
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
    <div class="wrap dc-hero-grid">
      <div class="dc-hero-copy">
        <img class="dc-hero-logo" src="{{ asset('assets/logo_dark.png') }}" alt="Azoogi">
        <p class="dc-kicker">{{ $meta->get('hero.kicker') }}</p>
        <h1 class="dc-title">{!! accent_html($meta->get('hero.title'), 'Smart DALI-2 Management') !!}</h1>
        <p class="dc-lead">{{ $meta->get('hero.lead') }}</p>
        <p class="dc-intro" {!! cms_section_attr('intro') !!}>{{ $meta->get('intro.body') }}</p>
      </div>
      @if ($videoId !== '')
        <div class="dc-hero-video" {!! cms_section_attr('video') !!}>
          <iframe
            src="https://www.youtube-nocookie.com/embed/{{ $videoId }}?rel=0&modestbranding=1"
            title="AZOOGI DALI Centre"
            allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share"
            allowfullscreen
          ></iframe>
        </div>
      @endif
    </div>
  </section>

  <section class="dc-band dc-band--alt" {!! cms_section_attr('why') !!}>
    <div class="wrap">
      <div class="dc-section-head reveal">
        <h2>{{ $meta->get('why.heading') }}</h2>
      </div>
      <ol class="dc-caps">
        @foreach ($whyItems as $item)
          <li class="reveal" style="transition-delay: {{ $loop->iteration * 0.08 }}s">
            <span class="dc-num">{{ str_pad((string) $loop->iteration, 2, '0', STR_PAD_LEFT) }}</span>
            <h3>{{ $item['title'] ?? '' }}</h3>
            <p>{{ $item['body'] ?? '' }}</p>
          </li>
        @endforeach
      </ol>
    </div>
  </section>

  @if ($hasFeatureImage)
    <section class="dc-band" {!! cms_section_attr('feature') !!}>
      <div class="wrap dc-diagram reveal">
        <div class="dc-diagram-copy">
          <h2>{{ $meta->get('feature.heading') }}</h2>
          @if (trim($meta->get('feature.lead')) !== '')
            <p class="dc-diagram-lead">{{ $meta->get('feature.lead') }}</p>
          @endif
          @if (count($diagramItems) > 0)
            <ol class="dc-diagram-points">
              @foreach ($diagramItems as $item)
                <li>
                  <span class="dc-num">{{ str_pad((string) $loop->iteration, 2, '0', STR_PAD_LEFT) }}</span>
                  <div>
                    <h3>{{ $item['title'] ?? '' }}</h3>
                    <p>{{ $item['body'] ?? '' }}</p>
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
        <h2>{{ $meta->get('hardware.heading') }}</h2>
      </div>
      <div class="dc-table-wrap reveal">
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
                    <span class="dc-product" data-preview="{{ $preview }}">{{ $row['product'] ?? '' }}</span>
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
  </section>

  <section class="dc-band dc-band--alt" {!! cms_section_attr('support') !!}>
    <div class="wrap">
      <div class="dc-section-head reveal">
        <h2>{{ $meta->get('support.heading') }}</h2>
        @if (trim($meta->get('support.lead')) !== '')
          <p>{{ $meta->get('support.lead') }}</p>
        @endif
      </div>
      <ul class="dc-support">
        @foreach ($supportItems as $item)
          <li class="reveal" style="transition-delay: {{ $loop->iteration * 0.08 }}s">
            <h3>{{ $item['title'] ?? '' }}</h3>
            <p>{{ $item['body'] ?? '' }}</p>
          </li>
        @endforeach
      </ul>
    </div>
  </section>

  <div class="dc-cta-wrap reveal" {!! cms_section_attr('cta') !!}>
    <div class="wrap">
      <div class="dc-cta">
        <h2>{{ $meta->get('cta.heading') }}</h2>
        <p>{{ $meta->get('cta.body') }}</p>
        <a class="btn primary" href="{{ chrome_url($meta->get('cta.href', 0, '/contact')) }}">{{ $meta->get('cta.label') }}</a>
      </div>
    </div>
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
</script>
@endverbatim
@endpush
