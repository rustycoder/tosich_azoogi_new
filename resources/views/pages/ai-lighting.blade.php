@extends('layouts.site')

@section('title', $page->title)

@section('description', $page->meta_description)

@section('bodyClass', 'ai-page')

@section('chrome', 'full')

@section('topbarClass', 'solid')
@section('logo', 'logo_dark.png')

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/css/ai-lighting.v-1.2.css') }}?v={{ config('app.asset_version') }}">
@endpush

@section('content')
@php
    $caps = $meta->list('caps.item.title');
    $ticks = $meta->list('spectrum.tick');
    $insights = $meta->group('insights.item');
    $spaceItems = $meta->group('space.item');
@endphp
<main class="ai-main">

  <section class="ai-hero" {!! cms_section_attr('hero') !!}>
    <div class="ai-hero-media" aria-hidden="true">
      <img src="{{ media_url($meta->get('hero.image')) }}" alt="" loading="eager">
    </div>
    <div class="ai-hero-copy">
      <div class="kicker">{{ $meta->get('hero.kicker') }}</div>
      <h1>{!! accent_html($meta->get('hero.title'), 'thinks') !!}</h1>
      <p>{{ $meta->get('hero.lead') }}</p>
    </div>
  </section>

  <section class="ai-band" {!! cms_section_attr('caps') !!}>
    <div class="wrap ai-split">
      <div class="ai-split-copy">
        <div class="kicker">{{ $meta->get('caps.kicker') }}</div>
        <h2>{!! nl2br_html($meta->get('caps.heading'), true) !!}</h2>
        <p>{{ $meta->get('caps.body') }}</p>
      </div>
      <ol class="ai-caps">
        @foreach ($caps as $title)
          <li>
            <span class="ai-num">{{ str_pad((string) $loop->iteration, 2, '0', STR_PAD_LEFT) }}</span>
            <h3>{{ $title }}</h3>
          </li>
        @endforeach
      </ol>
    </div>
  </section>

  <section class="ai-band ai-band--tight" {!! cms_section_attr('spectrum') !!}>
    <div class="wrap ai-feature">
      <div class="ai-feature-copy">
        <div class="kicker">{{ $meta->get('spectrum.kicker') }}</div>
        <h2>{!! accent_html($meta->get('spectrum.heading'), 'spectrum') !!}</h2>
        <p>{{ $meta->get('spectrum.body') }}</p>
        <ul class="ai-ticks">
          @foreach ($ticks as $tick)
            <li>{{ $tick }}</li>
          @endforeach
        </ul>
      </div>
      <div class="ai-compare">
        <figure>
          <img src="{{ media_url($meta->get('spectrum.compare.traditional.image')) }}" alt="{{ $meta->get('spectrum.compare.traditional.caption') }}" loading="lazy">
          <figcaption>{{ $meta->get('spectrum.compare.traditional.caption') }}</figcaption>
        </figure>
        <figure class="is-accent">
          <img src="{{ media_url($meta->get('spectrum.compare.ai.image')) }}" alt="{{ $meta->get('spectrum.compare.ai.caption') }}" loading="lazy">
          <figcaption>{{ $meta->get('spectrum.compare.ai.caption') }}</figcaption>
        </figure>
      </div>
    </div>
  </section>

  <section class="ai-insights card-in" {!! cms_section_attr('insights') !!}>
    <div class="wrap-sm">
      <div class="ai-row-head">
        <div>
          <div class="kicker">{{ $meta->get('insights.kicker') }}</div>
          <h2>{!! accent_html($meta->get('insights.heading'), 'analysis') !!}</h2>
        </div>
        <p>{{ $meta->get('insights.lead') }}</p>
      </div>

      <div class="container max-width-adaptive-md">
        <ul id="cards" style="--numcards: {{ count($insights) }}">
          @foreach ($insights as $item)
            <li class="card-main" id="card_{{ $loop->iteration }}" style="--index: {{ $loop->iteration }}">
              <div class="card__content">
                <div>
                  <span class="ai-num">{{ str_pad((string) $loop->iteration, 2, '0', STR_PAD_LEFT) }}</span>
                  <h2>{{ $item['title'] ?? '' }}</h2>
                  <p>{{ $item['body'] ?? '' }}</p>
                </div>
                <figure>
                  <img src="{{ media_url($item['image'] ?? '') }}" alt="{{ $item['title'] ?? '' }}">
                </figure>
              </div>
            </li>
          @endforeach
        </ul>
      </div>
    </div>
  </section>

  <section class="ai-cct" {!! cms_section_attr('cct') !!}>
    <div class="ai-cct-media" aria-hidden="true">
      <img src="{{ media_url($meta->get('cct.image')) }}" alt="" loading="lazy">
    </div>
    <div class="wrap ai-cct-inner">
      <div class="kicker">{{ $meta->get('cct.kicker') }}</div>
      <h2>{!! accent_html($meta->get('cct.heading'), 'temperature') !!}</h2>
      <p>{{ $meta->get('cct.body') }}</p>
    </div>
  </section>

  <section class="ai-band" {!! cms_section_attr('space') !!}>
    <div class="wrap">
      <div class="ai-row-head">
        <div>
          <div class="kicker">{{ $meta->get('space.kicker') }}</div>
          <h2>{!! accent_html($meta->get('space.heading'), 'management') !!}</h2>
        </div>
        <p>{{ $meta->get('space.lead') }}</p>
      </div>

      <div class="ai-space">
        <div class="ai-space-visual" id="aiSpaceVisual">
          @foreach ($spaceItems as $item)
            <img class="{{ $loop->first ? 'is-active' : '' }}" src="{{ media_url($item['image'] ?? '') }}" alt="{{ $item['title'] ?? '' }}" data-panel="{{ $loop->index }}">
          @endforeach
        </div>

        <div class="ai-accordion" id="aiAccordion">
          @foreach ($spaceItems as $item)
            <div class="ai-acc-item {{ $loop->first ? 'is-open' : '' }}">
              <button type="button" class="ai-acc-btn" aria-expanded="{{ $loop->first ? 'true' : 'false' }}">
                <span class="ai-num">{{ str_pad((string) $loop->iteration, 2, '0', STR_PAD_LEFT) }}</span>
                <h3>{{ $item['title'] ?? '' }}</h3>
                <span class="chev" aria-hidden="true"></span>
              </button>
              <div class="ai-acc-panel">
                <p>{{ $item['body'] ?? '' }}</p>
              </div>
            </div>
          @endforeach
        </div>
      </div>
    </div>
  </section>

  <div class="ai-cta-wrap" {!! cms_section_attr('cta') !!}>
    <a class="ai-cta" href="{{ $meta->get('cta.href', 0, '/contact') }}">
      <span class="ai-cta-check" aria-hidden="true">
        <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
          <path d="M5 12.5l4.5 4.5L19 7.5" stroke="currentColor" stroke-width="2.6" stroke-linecap="round" stroke-linejoin="round"/>
        </svg>
      </span>
      <span class="ai-cta-copy">
        <h3>{{ $meta->get('cta.heading') }}</h3>
        <p>{{ $meta->get('cta.body') }}</p>
      </span>
      <span class="btn primary">{{ $meta->get('cta.label') }}</span>
    </a>
  </div>

</main>
@endsection

@push('scripts')
@verbatim
<script>
document.getElementById('topbar')?.classList.add('solid');

  (function () {
    var items = document.querySelectorAll('#aiAccordion .ai-acc-item');
    var visuals = document.querySelectorAll('#aiSpaceVisual img');
    items.forEach(function (item, index) {
      var btn = item.querySelector('.ai-acc-btn');
      btn.addEventListener('click', function () {
        items.forEach(function (el, i) {
          var open = i === index;
          el.classList.toggle('is-open', open);
          el.querySelector('.ai-acc-btn').setAttribute('aria-expanded', open ? 'true' : 'false');
        });
        visuals.forEach(function (img) {
          img.classList.toggle('is-active', Number(img.getAttribute('data-panel')) === index);
        });
      });
    });
  })();
</script>
@endverbatim
@endpush
