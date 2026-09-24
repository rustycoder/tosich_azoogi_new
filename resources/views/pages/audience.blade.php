@extends('layouts.site')

@section('title', $page->title)

@section('description', $leads[0] ?? $page->meta_description)

@section('bodyClass', 'audience-page')

@section('chrome', 'full')

@section('topbarClass', '')

@push('styles')
<link rel="stylesheet" href="{{ versioned_asset('assets/css/audience.css') }}">
@endpush

@php
    $defaultHeroImages = [
        'home-owner' => '/assets/img/img-0.jpg',
        'architect-designer' => '/assets/hero02.jpg',
        'electrician-builder' => '/assets/img/img-1.jpg',
        'wholesaler' => '/assets/img/img-2.jpg',
    ];
    $defaultLeads = [
        'home-owner' => ['Explore high-quality LED lighting solutions tailored for Australian homes — combining style, energy efficiency, and lasting performance.'],
        'architect-designer' => ['Specification-grade LED lighting crafted to enhance contemporary interiors and bring your architectural vision to life.'],
        'electrician-builder' => ['Engineered for straightforward installation, rapid turnarounds, and reliable performance on every residential and commercial build.'],
        'wholesaler' => ['Stock with confidence. Fast quotes, protected trade margins, and dependable nationwide supply for leading electrical distributors.'],
    ];
    $heroImage = $meta->get('hero.image', 0, $defaultHeroImages[$page->slug] ?? '/assets/img/img-0.jpg');
    $audienceLeads = ! empty($leads) ? $leads : ($defaultLeads[$page->slug] ?? []);
@endphp

@section('content')
<main class="audience-main" id="audienceRoot">
  <section class="audience-hero" {!! cms_section_attr('hero') !!}>
    <div class="audience-hero-media" aria-hidden="true">
      <img src="{{ media_url($heroImage) }}" alt="" loading="eager" decoding="async">
    </div>
    <div class="audience-hero-copy">
      <h1 class="h2 audience-hero-title"{!! cms_style($meta, 'hero.title') !!}>
        {!! accent_html($meta->get('hero.title')) !!}</h1>
      @if (! empty($audienceLeads))
        <div class="audience-hero-lead">
          @foreach ($audienceLeads as $index => $paragraph)
            <p{!! cms_style($meta, 'hero.lead', $index) !!}>{!! linkify_emails(accent_html($paragraph)) !!}</p>
          @endforeach
        </div>
      @endif
    </div>
  </section>

  <section class="audience-cards-wrap card-in" {!! cms_section_attr('card') !!}>
    <div class="wrap-sm">
      <ul id="cards" class="audience-cards" style="--numcards: {{ count($cards) }}">
        @foreach ($cards as $card)
          <li class="card-main" id="card_{{ $loop->iteration }}" style="--index: {{ $loop->iteration }}">
            <div class="card__content">
              <div class="card__body">
                <h2{!! cms_style($meta, 'card.heading', $loop->index) !!}>{!! accent_html($card['heading'] ?? '') !!}</h2>
                <div class="card__copy"{!! cms_style($meta, 'card.body', $loop->index) !!}>
                  @foreach (preg_split("/\n\n+/", $card['body'] ?? '') as $paragraph)
                    @if (trim($paragraph) !== '')
                      <p>{!! linkify_emails($paragraph) !!}</p>
                    @endif
                  @endforeach
                </div>
                @if (! empty($card['cta.label']))
                  <div class="card__cta"><a href="{{ $card['cta.href'] ?? '#' }}" class="btn"{!! cms_style($meta, 'card.cta.label', $loop->index) !!}>{{ $card['cta.label'] }}</a></div>
                @endif
              </div>
              <figure>
                <img src="{{ media_url($card['image'] ?? '') }}" alt="{{ $card['heading'] ?? '' }}" loading="lazy">
              </figure>
            </div>
          </li>
        @endforeach
      </ul>
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
          topbar?.classList.toggle('solid', isScrolled);
          lastScrolled = isScrolled;
          if (typeof updateLogos === 'function') updateLogos();
        }
      };
      window.addEventListener('scroll', onScroll, { passive: true });
      onScroll();

      const io = new IntersectionObserver((es) => {
        es.forEach(e => { if (e.isIntersecting) { e.target.classList.add('in'); io.unobserve(e.target); } });
      }, { threshold: .12 });
      document.querySelectorAll('.reveal, .audience-hero').forEach(el => io.observe(el));
    </script>
  @endverbatim
@endpush
