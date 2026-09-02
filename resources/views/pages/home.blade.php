@extends('layouts.site')

@section('title', $page->title)

@section('description', $page->meta_description)

@section('chrome', 'full')

@section('topbarClass', '')
@section('logo', 'logo_white.png')

@section('content')
@php
    $slides = $meta->group('slide');
    $valueCards = $meta->group('values.card');
    $rangeItems = \App\Support\ProductCatalog::parentCategories();
    $stats = $meta->group('stats.item');
@endphp
<!-- ========== HERO SLIDER ========== -->
<section class="hero" id="hero" {!! cms_section_attr('slide') !!}>
  @foreach ($slides as $slide)
    @php
        $isVideo = ($slide['media.type'] ?? '') === 'video';
        $classes = 'slide';
        if ($loop->first) {
            $classes .= ' active';
        }
        if ($isVideo) {
            $classes .= ' has-video';
        }
        $image = media_url($slide['media.image'] ?? '');
    @endphp
    <div class="{{ $classes }}" @if (! $isVideo && $image) style="background-image:url('{{ $image }}')" @endif>
      @if ($isVideo)
        <video class="bg-video" @if ($loop->first) autoplay @endif muted loop playsinline preload="auto" poster="{{ media_url($slide['media.poster'] ?? '') }}">
          <source src="{{ media_url($slide['media.video'] ?? '') }}" type="video/webm">
        </video>
      @endif
      <div class="slide-inner">
        <div class="eyebrow"{!! cms_style($meta, 'slide.eyebrow', $loop->index) !!}>{{ $slide['eyebrow'] ?? '' }}</div>
        <h1 class="slide-title"{!! cms_style($meta, 'slide.title', $loop->index) !!}>{!! nl2br_html($slide['title'] ?? '') !!}</h1>
        <p class="slide-sub"{!! cms_style($meta, 'slide.subtitle', $loop->index) !!}>{{ $slide['subtitle'] ?? '' }}</p>
        <div class="slide-actions">
          <a class="btn primary" href="{{ $slide['cta.primary.href'] ?? '#' }}"{!! cms_style($meta, 'slide.cta.primary.label', $loop->index) !!}>{{ $slide['cta.primary.label'] ?? '' }}</a>
          <a class="btn" href="{{ $slide['cta.secondary.href'] ?? '#' }}"{!! cms_style($meta, 'slide.cta.secondary.label', $loop->index) !!}>{{ $slide['cta.secondary.label'] ?? '' }}</a>
        </div>
      </div>
    </div>
  @endforeach

  <div class="slider-ctrl">
    <div class="lines" id="lines"></div>
    <div class="counter"><b id="cur">01</b> <span>/ <b id="tot">04</b></span></div>
    <div class="pp" id="pp" aria-label="Pause">
      <svg id="ppIcon" viewBox="0 0 24 24" fill="currentColor"><rect x="6" y="5" width="4" height="14"/><rect x="14" y="5" width="4" height="14"/></svg>
    </div>
  </div>
</section>

<!-- ========== INTRO ========== -->
<section class="intro" {!! cms_section_attr('intro') !!}>
  <div class="wrap v-head">
    <div class="kicker reveal"{!! cms_style($meta, 'intro.kicker') !!}>{{ $meta->get('intro.kicker') }}</div>
    <h2 class="h2 reveal"{!! cms_style($meta, 'intro.heading') !!}>{{ $meta->get('intro.heading') }}</h2>
    <div class="audience reveal">
      <a href="{{ url('/architect-designer') }}">Architect / Designer</a>
      <a href="{{ url('/electrician-builder') }}">Electrician / Builder</a>
      <a href="{{ url('/wholesaler') }}">Wholesaler</a>
      <a href="{{ url('/home-owner') }}">Home Owner</a>
    </div>
  </div>
</section>

<!-- ========== VALUES ========== -->


<section class="card-in" id="about" {!! cms_section_attr('values') !!}>
  <div class="wrap-sm">
    <div class="head">
      <div class="kicker reveal"{!! cms_style($meta, 'values.kicker') !!}>{{ $meta->get('values.kicker') }}</div>
      <h2 class="h2 reveal"{!! cms_style($meta, 'values.heading') !!}>{{ $meta->get('values.heading') }}</h2>
    </div>
  
    <div class="container max-width-adaptive-md">
      <ul id="cards">
        @foreach ($valueCards as $card)
          <li class="card-main" id="card_{{ $loop->iteration }}">
            <div class="card__content">
              <div>
                <h2{!! cms_style($meta, 'values.card.title', $loop->index) !!}>{{ $card['title'] ?? '' }}</h2>
                <p{!! cms_style($meta, 'values.card.body', $loop->index) !!}>{{ $card['body'] ?? '' }}</p>
                <p><a href="{{ $card['href'] ?? '#top' }}" class="btn --accent">Read more</a></p>
              </div>
              <figure>
                <img src="{{ media_url($card['image'] ?? '') }}" alt="{{ $card['title'] ?? '' }}">
              </figure>
            </div>
          </li>
        @endforeach
      </ul>
    </div>
  </div>

</section>

<!-- ========== PRODUCTS MARQUEE ========== -->
<section class="products" id="products" {!! cms_section_attr('range') !!}>
  <div class="wrap">
    <div class="head">
      <div>
        <div class="kicker reveal"{!! cms_style($meta, 'range.kicker') !!}>{{ $meta->get('range.kicker') }}</div>
        <h2 class="h2 reveal"{!! cms_style($meta, 'range.heading') !!}>{{ $meta->get('range.heading') }}</h2>
      </div>
      <a href="{{ $meta->get('range.cta.href', 0, '/products') }}" class="btn --accent reveal"{!! cms_style($meta, 'range.cta.label') !!}>{{ $meta->get('range.cta.label') }}</a>
    </div>
  </div>
  <div class="marquee">
    <div class="track" id="track">
      @foreach ([1, 2] as $set)
        @foreach ($rangeItems as $item)
          <a class="card" href="{{ $item['href'] ?? '#' }}"><div class="img" style="background-image:url('{{ media_url($item['image'] ?? '') }}')"></div><div class="body"><h4>{{ $item['title'] ?? '' }}</h4><p>{{ $item['body'] ?? '' }}</p><span class="more">View Range &rarr;</span></div></a>
        @endforeach
      @endforeach
    </div>
  </div>
</section>

<!-- ========== PROJECTS ========== -->
<section class="projects" id="projects" {!! cms_section_attr('projects') !!}>
  <div class="wrap">
    <div class="head" style="display:flex;justify-content:space-between;align-items:flex-end;gap:40px;flex-wrap:wrap">
      <div>
        <div class="kicker reveal"{!! cms_style($meta, 'projects.kicker') !!}>{{ $meta->get('projects.kicker') }}</div>
        <h2 class="h2 reveal"{!! cms_style($meta, 'projects.heading') !!}>{{ $meta->get('projects.heading') }}</h2>
      </div>
      <a href="{{ $meta->get('projects.cta.href', 0, '/projects') }}" class="btn reveal"{!! cms_style($meta, 'projects.cta.label') !!}>{{ $meta->get('projects.cta.label') }}</a>
    </div>

    <div class="grid">
      @foreach ($featuredProjects as $project)
        <div class="proj reveal"><a href="{{ route('project-detail', ['slug' => $project->slug]) }}"><img src="{{ $project->coverUrl() }}" alt="{{ $project->title }}"/><div class="cap"><small>{{ $project->tag }}@if ($project->location) &mdash; {{ $project->location }}@endif</small><h3>{{ $project->title }}</h3></div></a></div>
      @endforeach
    </div>
  </div>
</section>

<!-- ========== STATS ========== -->
<section class="stats" {!! cms_section_attr('stats') !!}>
  <div class="wrap">
    <div class="kicker reveal"{!! cms_style($meta, 'stats.kicker', 0, 'text-align:center') !!}>{{ $meta->get('stats.kicker') }}</div>
    <h2 class="h2 reveal"{!! cms_style($meta, 'stats.heading', 0, 'text-align:center; max-width:900px; margin:0 auto') !!}>{!! nl2br_html($meta->get('stats.heading')) !!}</h2>
    <div class="stats-grid">
      @foreach ($stats as $stat)
        <div class="stat reveal"><div class="num" data-c="{{ (int) ($stat['value'] ?? 0) }}"{!! cms_style($meta, 'stats.item.value', $loop->index) !!}>0</div><div class="lbl"{!! cms_style($meta, 'stats.item.label', $loop->index) !!}>{{ $stat['label'] ?? '' }}</div></div>
      @endforeach
    </div>
  </div>
</section>

<!-- ========== FOOTER ========== -->
@endsection

@push('scripts')
@verbatim
<script>
/* ===== Header solid on scroll ===== */
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
  window.addEventListener('scroll', onScroll, { passive: true }); onScroll();

  /* ===== Hero slider with line progress + play/pause ===== */
  (() => {
    const slides = document.querySelectorAll('.hero .slide');
    const n = slides.length;
    const linesEl = document.getElementById('lines');
    const cur = document.getElementById('cur');
    const tot = document.getElementById('tot');
    const pp = document.getElementById('pp');
    const ppIcon = document.getElementById('ppIcon');
    const DUR = 6000;
    let idx = 0, playing = true, start = performance.now(), raf;

    tot.textContent = String(n).padStart(2, '0');
    for (let i = 0; i < n; i++) {
      const ln = document.createElement('div'); ln.className = 'line'; ln.innerHTML = '<div class="fill"></div>';
      ln.addEventListener('click', () => goto(i, true));
      linesEl.appendChild(ln);
    }
    const lines = linesEl.querySelectorAll('.line');

    function paint(p) {
      lines.forEach((l, i) => {
        l.classList.toggle('active', i === idx);
        l.classList.toggle('done', i < idx);
        if (i === idx) l.style.setProperty('--p', p.toFixed(3));
        else if (i < idx) l.style.setProperty('--p', '1');
        else l.style.setProperty('--p', '0');
      });
    }
    function show(i) {
      slides.forEach((s, k) => {
        s.classList.toggle('active', k === i);
        const vid = s.querySelector('video.bg-video');
        if (!vid) return;
        if (k === i) {
          vid.currentTime = 0;
          vid.play().catch(() => {});
        } else {
          vid.pause();
        }
      });
      cur.textContent = String(i + 1).padStart(2, '0');
      window.dispatchEvent(new CustomEvent('hero:slide', { detail: { index: i } }));
    }
    function goto(i, reset) {
      idx = (i + n) % n; show(idx);
      if (reset) { start = performance.now(); paint(0); }
    }
    function loop(t) {
      if (!playing) { raf = requestAnimationFrame(loop); return; }
      const p = Math.min(1, (t - start) / DUR);
      paint(p);
      if (p >= 1) { idx = (idx + 1) % n; show(idx); start = t; }
      raf = requestAnimationFrame(loop);
    }
    pp.addEventListener('click', () => {
      playing = !playing;
      ppIcon.innerHTML = playing
        ? '<rect x="6" y="5" width="4" height="14"/><rect x="14" y="5" width="4" height="14"/>'
        : '<polygon points="7,4 20,12 7,20"/>';
      if (playing) start = performance.now() - DUR * getCurrentP();
    });
    function getCurrentP() {
      const f = lines[idx].querySelector('.fill');
      const m = getComputedStyle(f).transform;
      if (m && m !== 'none') { const v = m.match(/matrix\(([-\d.]+)/); if (v) return parseFloat(v[1]); }
      return 0;
    }
    show(0); paint(0); raf = requestAnimationFrame(loop);
  })();

  /* ===== Reveal on scroll ===== */
  const io = new IntersectionObserver((es) => {
    es.forEach(e => { if (e.isIntersecting) { e.target.classList.add('in'); io.unobserve(e.target); } });
  }, { threshold: .12 });
  document.querySelectorAll('.reveal').forEach(el => io.observe(el));

  /* ===== Stat counter ===== */
  const sio = new IntersectionObserver((es) => {
    es.forEach(e => {
      if (!e.isIntersecting) return;
      const el = e.target; const target = parseInt(el.dataset.c, 10); const dur = 1600; const t0 = performance.now();
      const step = (t) => {
        const p = Math.min(1, (t - t0) / dur);
        const v = Math.floor(target * (1 - Math.pow(1 - p, 3)));
        el.textContent = v.toLocaleString() + (target >= 100 ? '+' : '');
        if (p < 1) requestAnimationFrame(step);
      };
      key = requestAnimationFrame(step);
      sio.unobserve(el);
    });
  }, { threshold: .5 });
  document.querySelectorAll('.stat .num').forEach(el => sio.observe(el));

  /* ===== Hero parallax zoom ===== */
  const heroSlides = document.querySelectorAll('.hero .slide');
  function heroParallax() {
    const y = window.scrollY;
    const vh = window.innerHeight;
    const p = Math.min(1, Math.max(0, y / vh));
    const scale = 1 + p * 0.18;
    const ty = p * 40;
    heroSlides.forEach(s => { s.style.setProperty('--z', scale.toFixed(4)); s.style.setProperty('--ty', ty.toFixed(1) + 'px'); });
  }
  heroParallax();
  window.addEventListener('scroll', heroParallax, { passive: true });

  function updateLogos() {
    const isScrolled = window.scrollY > 40;
    const logos = document.querySelectorAll('.logo img');
    logos.forEach(img => {
      if (img.closest('.topbar')) {
        img.src = isScrolled ? '/assets/logo_dark.png' : '/assets/logo_white.png';
      } else {
        img.src = '/assets/logo_dark.png';
      }
    });
  }

  document.addEventListener("DOMContentLoaded", () => {
    localStorage.removeItem('theme');
    updateLogos();
  });
</script>
@endverbatim
@endpush
