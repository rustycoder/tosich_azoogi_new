@extends('layouts.site')

@section('title', $page->title)

@section('description', $page->meta_description)

@section('bodyClass')
  products-page {{ $showCatalog ? 'is-catalog' : 'is-gallery' }}
@endsection

@section('chrome', 'full')

@section('topbarClass', '')
@section('logo', 'logo_white.png')

@push('styles')
  <link rel="stylesheet" href="{{ versioned_asset('assets/css/products.css') }}">
  @if ($showCatalog)
    @verbatim
      <style>
        /* ===========================================================
                                                                       TWO-COLUMN LAYOUT: narrow sidebar | product grid
                                                                    =========================================================== */
        .prod-layout {
          display: grid;
          grid-template-columns: 240px 1fr;
          gap: 28px;
          padding: 28px 0 100px;
          align-items: start;
        }

        .prod-layout>main {
          min-width: 0;
        }

        @media(max-width:1280px) {
          .prod-layout {
            grid-template-columns: 220px 1fr;
            gap: 22px;
          }
        }

        @media(max-width:1024px) {
          .prod-layout {
            grid-template-columns: 1fr;
            padding: 16px 0 72px;
          }
        }

        /* ===========================================================
                                                                       SIDEBAR
                                                                    =========================================================== */
        .prod-sidebar {
          position: sticky;
          top: 120px;
          width: 100%;
          margin: 0;
          min-width: 0;
          max-height: calc(100vh - 120px - 20px);
          overflow-y: auto;
          padding-right: 12px;
        }

        .prod-sidebar::-webkit-scrollbar {
          width: 4px;
        }

        .prod-sidebar::-webkit-scrollbar-track {
          background: transparent;
        }

        .prod-sidebar::-webkit-scrollbar-thumb {
          background: var(--line);
          border-radius: 4px;
        }

        .sb-block {
          border-bottom: 1px solid var(--line);
          padding-bottom: 16px;
          margin-bottom: 16px;
        }

        .sb-title {
          display: flex;
          align-items: center;
          justify-content: space-between;
          font-size: var(--fs-caption);
          font-weight: 600;
          letter-spacing: .12em;
          text-transform: uppercase;
          color: var(--accent);
          margin-bottom: 12px;
          cursor: pointer;
        }

        .sb-title:hover {
          opacity: 0.8;
        }

        .sb-toggle {
          font-size: var(--fs-body);
          font-weight: 300;
          line-height: 1;
          color: var(--accent);
        }

        .sb-block.open .sb-title {
          border-bottom: 1px solid var(--border-light);
          padding-bottom: 12px;
          margin-bottom: 12px;
          padding-right: 12px;
        }

        .sb-block.open .sb-toggle::after {
          content: '\2013';
        }

        .sb-block:not(.open) .sb-toggle::after {
          content: '+';
        }

        .sb-content {
          max-height: 2000px;
          overflow: hidden;
          transition: max-height 0.3s ease, opacity 0.3s ease;
          opacity: 1;
        }

        .sb-block:not(.open) .sb-content {
          max-height: 0;
          opacity: 0;
        }

        .sb-block:not(.open) .sb-title {
          margin-bottom: 0;
          border-bottom: none;
          padding-bottom: 0;
          padding-right: 12px;
        }

        .cat-list {
          list-style: none;
          margin: 0;
          padding: 0;
        }

        .cat-row {
          display: flex;
          align-items: center;
          justify-content: space-between;
          padding: 5px 6px;
          cursor: pointer;
          font-size: var(--fs-caption);
          font-weight: 600;
          gap: 6px;
        }

        .cat-label-text {
          flex: 1;
          line-height: 1.3;
          word-break: break-word;
          cursor: pointer;
          color: var(--ink);
        }

        .cat-label-text.active,
        .cat-label-text:hover,
        .cat-row:hover {
          color: var(--accent);
        }

        /* cleaner +/- toggle icon to match filter accordion */
        .cat-toggle {
          font-size: var(--fs-body);
          font-weight: 300;
          color: var(--muted);
          width: 16px;
          text-align: center;
          line-height: 1;
          flex-shrink: 0;
        }

        .cat-node.open>.cat-row>.cat-toggle {
          color: var(--accent);
        }

        .sub-list {
          list-style: none;
          margin: 0;
          padding: 0;
          max-height: 0;
          overflow: hidden;
          transition: max-height .35s ease;
        }

        .cat-node.open>.sub-list {
          max-height: 2000px;
          margin-left: 6px;
          padding-left: 8px;
          border-left: 1px solid rgba(255, 255, 255, 0.1);
        }

        .sub-list li a,
        .cat-sub-link {
          display: block;
          padding: 4px 6px;
          font-size: var(--fs-caption);
          color: var(--muted);
          line-height: 1.3;
          text-decoration: none;
        }

        .cat-sub-link.level-0 {
          font-size: var(--fs-caption);
          font-weight: 600;
          color: var(--ink);
          padding: 5px 6px;
        }

        .sub-list li a.active,
        .sub-list li a:hover,
        .cat-sub-link.active,
        .cat-sub-link:hover {
          color: var(--accent);
        }

        /* Filter accordion */

        .filter-head {
          display: flex;
          align-items: center;
          justify-content: space-between;
          padding: 0 10px;
          cursor: pointer;
          font-size: var(--fs-caption);
          font-weight: 600;
        }

        .filter-head:hover {
          color: var(--accent);
        }

        .filter-toggle {
          font-size: var(--fs-body);
          font-weight: 300;
          color: var(--muted);
          width: 16px;
          text-align: center;
        }

        .filter-body {
          max-height: 0;
          overflow: hidden;
          transition: max-height .3s ease;
        }

        .filter-item.open .filter-body {
          max-height: min(50vh, 320px);
          overflow-y: auto;
          padding: 0 10px;
        }

        .filter-item.open .filter-toggle {
          color: var(--accent);
        }

        .opt-row {
          display: flex;
          align-items: center;
          gap: 8px;
          padding: 3px 0;
          font-size: var(--fs-caption);
          color: var(--ink);
          cursor: pointer;
        }

        .opt-row input {
          accent-color: var(--accent);
          width: 13px;
          height: 13px;
        }

        .clear-filters {
          font-size: var(--fs-kicker);
          color: var(--accent) !important;
          cursor: pointer;
          display: inline-block;
        }

        .clear-filters:hover {
          color: var(--accent);
        }

        /* ===========================================================
                                                                       TOOLBAR
                                                                    =========================================================== */
        .prod-toolbar {
          display: flex;
          align-items: flex-end;
          justify-content: space-between;
          gap: 14px;
          margin-bottom: 10px;
          flex-wrap: wrap;
          border-bottom: 1px solid #CCC;
          padding-bottom: 10px;
        }

        .result-count {
          font-size: var(--fs-kicker);
          color: var(--muted);
        }

        .result-count strong {
          color: var(--ink);
        }

        .toolbar-right {
          display: flex;
          align-items: center;
          gap: 12px;
          min-width: 0;
        }

        .prod-search-input {
          padding: 8px 12px;
          border-radius: 4px;
          border: 1px solid var(--border-light);
          background: var(--bg-2);
          color: var(--ink);
          font-size: var(--fs-kicker);
          width: 180px;
          min-width: 0;
          box-sizing: border-box;
        }

        .sort-select {
          border: 1px solid var(--line);
          background: var(--bg);
          color: var(--ink);
          font-size: var(--fs-kicker);
          padding: 8px 12px;
          border-radius: 4px;
          cursor: pointer;
          min-width: 0;
          max-width: 100%;
        }

        .prod-mobile-filter-btn {
          display: none;
          border: 1px solid var(--line);
          background: none;
          color: var(--ink);
          padding: 8px 12px;
          font-size: var(--fs-kicker);
          cursor: pointer;
          border-radius: 4px;
          white-space: nowrap;
        }

        @media(max-width:1024px) {
          .prod-toolbar {
            align-items: stretch;
            gap: 10px;
          }

          .result-count {
            width: 100%;
            margin-bottom: 2px;
          }

          .toolbar-right {
            flex: 1 1 100%;
            flex-wrap: wrap;
            gap: 8px;
          }

          .prod-search-input {
            flex: 1 1 100%;
            width: 100%;
            min-height: 44px;
          }

          .prod-mobile-filter-btn,
          .sort-select {
            flex: 1 1 calc(50% - 4px);
            min-height: 44px;
          }

          .prod-mobile-filter-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
          }

          .prod-sidebar {
            display: none;
          }

          .prod-sidebar.open {
            display: flex;
            flex-direction: column;
            position: fixed;
            top: max(12px, env(safe-area-inset-top, 0px));
            right: 16px;
            bottom: max(12px, env(safe-area-inset-bottom, 0px));
            left: 16px;
            width: auto;
            max-width: 400px;
            max-height: calc(100dvh - 24px);
            margin-inline: auto;
            z-index: 290;
            background: var(--bg);
            padding: 18px 18px 28px;
            overflow-y: auto;
            border: 1px solid var(--line);
            border-radius: 8px;
            box-shadow: 0 18px 48px rgba(0, 0, 0, .18);
          }

          .prod-filter-overlay {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(0, 0, 0, .4);
            z-index: 280;
          }

          .prod-filter-overlay.open {
            display: block;
          }

          .prod-sidebar-head {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            margin-bottom: 18px;
            padding-bottom: 12px;
            border-bottom: 1px solid var(--line);
          }

          .prod-sidebar-head strong {
            font-size: var(--fs-body);
            letter-spacing: .08em;
            text-transform: uppercase;
          }

          .prod-filter-close {
            min-width: 44px;
            min-height: 44px;
            border: 1px solid var(--line);
            background: none;
            color: var(--ink);
            font-size: 22px;
            line-height: 1;
            cursor: pointer;
            border-radius: 4px;
          }
        }

        @media (min-width: 1025px) {
          .prod-sidebar-head {
            display: none;
          }

          .prod-filter-overlay {
            display: none !important;
          }
        }

        .active-tags {
          display: flex;
          flex-wrap: wrap;
          gap: 8px;
          margin-bottom: 10px;
        }

        .p-tag {
          display: flex;
          align-items: center;
          gap: 6px;
          background: var(--card-bg);
          border: 1px solid var(--line);
          font-size: var(--fs-kicker);
          padding: 5px 10px;
          border-radius: 2px;
        }

        .p-tag .remove-tag {
          cursor: pointer;
          color: var(--muted);
          line-height: 1;
          padding-left: 4px;
          font-weight: bold;
        }

        .p-tag .remove-tag:hover {
          color: var(--accent);
        }

        /* ===========================================================
                                                                       PRODUCT CARD GRID — 4 columns
                                                                    =========================================================== */
        .prod-grid {
          display: grid;
          grid-template-columns: repeat(4, 1fr);
          gap: 14px;
        }

        @media(max-width:1280px) {
          .prod-grid {
            grid-template-columns: repeat(4, 1fr);
            gap: 12px;
          }
        }

        @media(max-width:1024px) {
          .prod-grid {
            grid-template-columns: repeat(2, 1fr);
            gap: 12px;
          }
        }

        @media(max-width:480px) {
          .prod-grid {
            grid-template-columns: 1fr;
          }
        }

        .prod-card {
          position: relative;
          border: 1px solid var(--border-light);
          background: var(--card-bg);
          overflow: hidden;
          border-radius: 3px;
          transition: border-color .25s;
          cursor: pointer;
          min-width: 0;
        }

        .prod-card:hover {
          border-color: var(--accent);
        }

        .prod-card-img {
          position: relative;
          aspect-ratio: 1/1;
          overflow: hidden;
          background-color: var(--bg-2);
        }

        .prod-card .prod-card-img img {
          width: 100%;
          height: 100%;
          object-fit: cover;
          transition: transform 1.2s ease;
        }

        .prod-card:hover .prod-card-img img {
          transform: scale(1.08);
        }

        .prod-swatch {
          position: absolute;
          inset: 0;
          width: 100%;
          height: 100%;
          object-fit: contain;
          background-color: var(--bg-2);
          display: flex;
          align-items: center;
          justify-content: center;
        }

        .prod-swatch.is-fallback {
          filter: grayscale(100%);
          opacity: 0.7;
        }

        .prod-swatch svg {
          width: 38%;
          opacity: .35;
        }

        .prod-card-title {
          padding: 12px 14px 14px;
          font-size: var(--fs-meta);
          display: flex;
          align-items: flex-start;
          justify-content: space-between;
          gap: 8px;
          line-height: 1.3;
        }

        .prod-card-title-text {
          flex: 1;
          white-space: pre-line;
          line-height: 1.3;
        }

        .prod-card-title .cat-label {
          display: block;
          font-size: var(--fs-caption);
          color: var(--accent);
          text-transform: uppercase;
          letter-spacing: .08em;
          margin-bottom: 4px;
        }

        .prod-card-title .prod-card-code {
          display: block;
          font-size: var(--fs-caption);
          color: var(--accent);
          text-transform: uppercase;
          letter-spacing: .08em;
          margin-top: 4px;
          font-weight: 400;
        }

        .add-quote-btn {
          background: var(--accent);
          border: 1px solid var(--line);
          color: var(--pure-bg);
          width: 28px;
          height: 28px;
          border-radius: 4px;
          display: flex;
          align-items: center;
          justify-content: center;
          cursor: pointer;
          transition: all .2s;
          flex-shrink: 0;
          font-size: 16px;
          font-weight: 300;
          padding: 0;
        }

        .add-quote-btn:hover {
          background: var(--card-bg);
          color: var(--ink);
          border-color: var(--accent);
        }

        .add-quote-btn.added {
          background: var(--accent-2);
          color: #fff;
          border-color: var(--accent-2);
        }

        .prod-overlay {
          display: none !important;
        }

        .spec-item {
          font-size: var(--fs-caption);
          color: rgba(255, 255, 255, 0.85);
          line-height: 1.4;
          border-bottom: 1px solid rgba(255, 255, 255, 0.1);
          padding-bottom: 6px;
          display: flex;
          justify-content: space-between;
          gap: 8px;
        }

        .spec-item:last-child {
          border-bottom: none;
        }

        .spec-item strong {
          color: #fff;
          font-weight: 500;
          font-size: var(--fs-caption);
          text-transform: uppercase;
          letter-spacing: .04em;
        }

        .spec-item span {
          text-align: right;
        }

        .p-btn-row {
          display: flex;
          gap: 8px;
        }

        .ov-btn {
          flex: 1;
          text-align: center;
          font-size: var(--fs-caption);
          padding: 9px 6px;
          cursor: pointer;
          border-radius: 2px;
          transition: all .2s;
          border: 1px solid rgba(255, 255, 255, .5);
          color: #fff;
          background: transparent;
        }

        .ov-btn:hover {
          background: rgba(255, 255, 255, .12);
        }

        .ov-btn.solid {
          background: var(--accent);
          border-color: var(--accent);
          color: var(--pure-bg);
          font-weight: 600;
        }

        .ov-btn.solid:hover {
          background: #fff;
          border-color: #fff;
          color: #000;
        }

        .ov-btn.added {
          background: var(--accent-2);
          border-color: var(--accent-2);
        }

        /* ===========================================================
                                                                       PAGINATION
                                                                    =========================================================== */
        .pagination {
          display: flex;
          justify-content: center;
          align-items: center;
          flex-wrap: wrap;
          gap: 8px;
          margin-top: 48px;
          padding: 0 4px;
        }

        .pg-btn {
          width: 36px;
          height: 36px;
          display: flex;
          align-items: center;
          justify-content: center;
          border: 1px solid var(--line);
          font-size: var(--fs-kicker);
          cursor: pointer;
          color: var(--muted);
        }

        .pg-btn.active {
          background: var(--accent);
          border-color: var(--accent);
          color: var(--pure-bg);
        }

        .pg-btn:hover:not(.active) {
          color: var(--ink);
          border-color: var(--accent);
        }
      </style>
    @endverbatim
  @endif
@endpush

@section('content')
<main class="prod-main">
  <section class="prod-hero" {!! cms_section_attr('hero') !!}>
    <div class="prod-hero-media" aria-hidden="true">
      @if (filled($meta->get('hero.video')))
        <video class="prod-hero-video" autoplay muted loop playsinline preload="auto" poster="{{ media_url($meta->get('hero.poster', 0, '/assets/hero02.jpg')) }}">
          <source src="{{ media_url($meta->get('hero.video')) }}" type="{{ video_mime_type($meta->get('hero.video')) }}">
        </video>
      @elseif (filled($meta->get('hero.poster')))
        <img src="{{ media_url($meta->get('hero.poster', 0, '/assets/hero02.jpg')) }}" alt="{{ $page->title }}" loading="eager">
      @else
        <img src="{{ media_url('/assets/hero02.jpg') }}" alt="{{ $page->title }}" loading="eager">
      @endif
    </div>
    <div class="prod-hero-copy">
      <h1{!! cms_style($meta, 'hero.title') !!}>
        @if ($showCatalog && filled($selectedCategory))
          {!! accent_html($selectedCategory) !!}
        @else
          {!! accent_html($meta->get('hero.title', 0, 'Our {Range}')) !!}
        @endif
      </h1>
      <p class="prod-hero-lead"{!! cms_style($meta, 'hero.lead') !!}>
        @if ($showCatalog)
          Explore our comprehensive range of commercial, architectural and smart LED lighting solutions.
        @else
          {!! accent_html($meta->get('hero.lead', 0, 'Explore the full Azoogi lighting catalogue. COB Strips, SMD Strips, Neon, Outdoor Lights, Aluminium Profiles, LED Drivers and more.')) !!}
        @endif
      </p>
    </div>
  </section>

  <div class="wrap">
    @if ($showCatalog)
      <div class="prod-layout">
        <!-- ===== Sidebar ===== -->
        <div class="prod-filter-overlay" id="prodFilterOverlay"></div>
        <aside class="prod-sidebar" id="prodSidebar">
          <div class="prod-sidebar-head">
            <strong>Filters</strong>
            <button type="button" class="prod-filter-close" id="prodFilterClose" aria-label="Close filters">&times;</button>
          </div>
          <div class="sb-block open">
            <div class="sb-title" onclick="this.parentElement.classList.toggle('open')">
              <span>Categories</span><span class="sb-toggle"></span>
            </div>
            <div class="sb-content">
              <ul class="cat-list" id="catList"><!-- populated by JS --></ul>
            </div>
          </div>
          <div class="sb-block open">
            <div class="sb-title" onclick="this.parentElement.classList.toggle('open')">
              <span>Tech Specification</span><span class="sb-toggle"></span>
            </div>
            <div class="sb-content">
              <div id="filterAccordion"></div>
            </div>
          </div>
        </aside>

        <!-- ===== Main ===== -->
        <main>


          <div class="prod-toolbar">
            <div class="result-count"><strong id="resultCount">0</strong> products found</div>
            <div class="toolbar-right">
              <input type="text" id="prodSearchInput" class="prod-search-input" placeholder="Search products...">
              <button type="button" class="prod-mobile-filter-btn" id="prodFilterOpen">Filters</button>
              <select class="sort-select" id="sortSelect" aria-label="Sort products">
                <option value="relevance">Sort: Relevance</option>
                <option value="name_asc">Name A&ndash;Z</option>
                <option value="name_desc">Name Z&ndash;A</option>
                <option value="cat_asc">Category A&ndash;Z</option>
              </select>
            </div>
          </div>

          <div class="active-tags" id="activeTags"></div>

          <div class="prod-grid" id="productGrid"></div>

          <div class="pagination" id="paginationContainer"></div>
        </main>
      </div>
    @else
      <section class="prod-gallery">
        <div class="prod-gallery-grid">
          @forelse ($rangeItems as $item)
            <a class="prod-gallery-card" href="{{ $item['href'] ?? '#' }}">
              <div class="body">
                <h3>{{ $item['title'] ?? '' }}</h3>
                @if (($item['body'] ?? '') !== '')
                  <p>{{ $item['body'] }}</p>
                @endif
                <span class="more">View Range &rarr;</span>
              </div>
            </a>
          @empty
            <div class="prod-gallery-empty">Catalogue coming soon.</div>
          @endforelse
        </div>
      </section>
    @endif
  </div>
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

      const io = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
          if (entry.isIntersecting) {
            entry.target.classList.add('in');
            io.unobserve(entry.target);
          }
        });
      }, { threshold: 0.12 });
      document.querySelectorAll('.reveal, .prod-hero').forEach(el => io.observe(el));
    </script>
  @endverbatim
@endpush

@if ($showCatalog)
  @push('scripts')
    @verbatim
      <script>
        /* ===== PRODUCTS PAGE INTERACTION & CATALOG ===== */
        (function () {

          // Mobile sidebar filter drawer
          var sidebar = document.getElementById('prodSidebar');
          var overlay = document.getElementById('prodFilterOverlay');
          var openBtn = document.getElementById('prodFilterOpen');
          var closeBtn = document.getElementById('prodFilterClose');

          function openFilters() {
            if (!sidebar) return;
            sidebar.classList.add('open');
            if (overlay) overlay.classList.add('open');
            document.body.style.overflow = 'hidden';
          }

          function closeFilters() {
            if (!sidebar) return;
            sidebar.classList.remove('open');
            if (overlay) overlay.classList.remove('open');
            document.body.style.overflow = '';
          }

          if (openBtn) openBtn.addEventListener('click', openFilters);
          if (closeBtn) closeBtn.addEventListener('click', closeFilters);
          if (overlay) overlay.addEventListener('click', closeFilters);
          document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape') closeFilters();
          });
          window.addEventListener('resize', function () {
            if (window.innerWidth > 1024) closeFilters();
          });

          // Touch: first tap reveals specs; second tap opens detail
          document.addEventListener('click', function (e) {
            var card = e.target.closest('.prod-card');
            var isTouch = window.matchMedia('(hover: none)').matches;
            if (!card) {
              document.querySelectorAll('.prod-card.is-specs').forEach(function (c) {
                c.classList.remove('is-specs');
              });
              return;
            }
            if (e.target.closest('a, button')) return;

            var detailUrl = card.getAttribute('data-href');
            if (!isTouch) {
              if (detailUrl) window.location.href = detailUrl;
              return;
            }

            e.preventDefault();
            e.stopPropagation();
            var wasOpen = card.classList.contains('is-specs');
            document.querySelectorAll('.prod-card.is-specs').forEach(function (c) {
              c.classList.remove('is-specs');
            });
            if (!wasOpen) {
              card.classList.add('is-specs');
            } else if (detailUrl) {
              window.location.href = detailUrl;
            }
          }, true);
        })();

        /* ===== State & Active Filters ===== */
        var activeFilters = {
          search: "",
          sort: "relevance",
          categories: [],
          specs: {}
        };

        var productsMap = {};
        var products = [];
        var extractedSpecsMap = {};
        var rawProductsById = {};
        var currentPage = 1;
        var pageSize = 24;

        function getLocalImg(imgUrl, filePath) {
          if (typeof window.getLocalImagePath === 'function') {
            var pathFromWin = window.getLocalImagePath(imgUrl, filePath);
            if (pathFromWin && !pathFromWin.startsWith('/') && !pathFromWin.startsWith('http')) {
              return '/' + pathFromWin;
            }
            return pathFromWin;
          }
          if (!imgUrl || typeof imgUrl !== 'string' || !imgUrl.trim()) return '/assets/bg_default.png';
          var clean = imgUrl.trim();
          if (!clean.startsWith('http')) {
            return clean.startsWith('/') ? clean : '/' + clean;
          }
          var filename = clean.split('/').pop().split('?')[0];
          if (!filename) return '/assets/bg_default.png';
          if (filePath) {
            var cleanFilePath = decodeURIComponent(filePath);
            var lastSlash = cleanFilePath.lastIndexOf('/');
            if (lastSlash !== -1) {
              var res = cleanFilePath.substring(0, lastSlash) + '/' + filename;
              return res.startsWith('/') ? res : '/' + res;
            }
          }
          return clean;
        }

        function extractSpecsFromFeatures(features) {
          if (!features || typeof features !== 'object') return;
          for (var featKey in features) {
            if (!features.hasOwnProperty(featKey)) continue;
            if (!featKey || typeof featKey !== 'string' || featKey.trim() === '') continue;
            var featVal = features[featKey];
            if (!extractedSpecsMap[featKey]) {
              extractedSpecsMap[featKey] = new Set();
            }
            if (Array.isArray(featVal)) {
              featVal.forEach(function (v) {
                if (v !== null && v !== undefined) {
                  var sVal = (typeof v === 'object' && v !== null && v.value !== undefined) ? String(v.value).trim() : String(v).trim();
                  if (sVal.length > 0 && sVal.length < 80) {
                    extractedSpecsMap[featKey].add(sVal);
                  }
                }
              });
            } else if (featVal !== null && featVal !== undefined) {
              var sVal = (typeof featVal === 'object' && featVal !== null && featVal.value !== undefined) ? String(featVal.value).trim() : String(featVal).trim();
              if (sVal.length > 0 && sVal.length < 80) {
                extractedSpecsMap[featKey].add(sVal);
              }
            }
          }
        }

        function extractProductCode(source) {
          if (!source) return '';
          var raw = source.product_code || source.productCode || '';
          if (!raw && source.product_features) {
            var feats = source.product_features;
            raw = feats['Product Code'] || feats['Product code'] || '';
          }
          if (Array.isArray(raw)) {
            raw = raw.map(function (v) {
              return (v && typeof v === 'object' && v.value !== undefined) ? v.value : v;
            }).filter(Boolean).join(', ');
          } else if (raw && typeof raw === 'object' && raw.value !== undefined) {
            raw = raw.value;
          }
          return String(raw || '').trim();
        }

        function primaryProductCode(sku) {
          if (!sku) return '';
          return String(sku).split(',')[0].trim();
        }

        function productCodeHtml(sku) {
          var code = primaryProductCode(sku);
          if (!code) return '';
          return ' <span class="prod-card-code">' + code + '</span>';
        }

        function escapeHtml(value) {
          return String(value || '')
            .replace(/&/g, '&amp;')
            .replace(/"/g, '&quot;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;');
        }

        function addProductToCatalog(vName, modelName, catPath, vData) {
          if (typeof vData === 'string' && rawProductsById[vData]) {
            vData = rawProductsById[vData];
          }
          if (!vData || typeof vData !== 'object') vData = {};

          // Only show products with status 'publish'
          if (vData.status && String(vData.status).toLowerCase().trim() !== 'publish') return;

          var itemKey = vData.id ? vData.id : vName;
          if (!vName) return;

          var parentCat = catPath[0] || 'General';
          var subCat = catPath.length > 1 ? catPath[catPath.length - 1] : parentCat;

          if (productsMap[itemKey]) {
            var existingItem = products.find(function (p) {
              if (vData.id) {
                return p.id === vData.id;
              }
              return !p.id && p.name === vName;
            });
            if (existingItem) {
              if (!existingItem.category_paths) {
                existingItem.category_paths = existingItem.category_path ? [existingItem.category_path] : [];
              }
              if (!existingItem.categories) {
                existingItem.categories = [existingItem.sub, existingItem.cat].filter(Boolean);
              }
              if (catPath && catPath.length > 0) {
                existingItem.category_paths.push(catPath);
                catPath.forEach(function (c) {
                  if (existingItem.categories.indexOf(c) === -1) {
                    existingItem.categories.push(c);
                  }
                });
              }
            }
            return;
          }

          var rawImg = (vData.product_images && vData.product_images.length > 0)
            ? vData.product_images[0]
            : '/assets/bg_default.png';

          var localImgPath = getLocalImg(rawImg, vData.file_path);
          var features = vData.product_features || {};

          extractSpecsFromFeatures(features);

          var initialCategories = (vData.categories && Array.isArray(vData.categories)) ? vData.categories.slice() : [];
          if (initialCategories.indexOf(parentCat) === -1) initialCategories.push(parentCat);
          if (initialCategories.indexOf(subCat) === -1) initialCategories.push(subCat);

          var initialCategoryPaths = (vData.category_paths && Array.isArray(vData.category_paths)) ? vData.category_paths.slice() : [];
          if (catPath && catPath.length > 0) initialCategoryPaths.push(catPath);

          var item = {
            id: (vData && vData.id) ? vData.id : '',
            name: vName,
            modelName: modelName,
            sub: subCat,
            cat: parentCat,
            categories: initialCategories,
            category_path: catPath,
            category_paths: initialCategoryPaths,
            filePath: vData.file_path || '',
            img: localImgPath,
            specs: features,
            sku: extractProductCode(vData)
          };

          productsMap[itemKey] = true;
          products.push(item);
        }

        function collectProductsFromTree(treeNodes, parentPath) {
          if (!treeNodes || !Array.isArray(treeNodes)) return;
          treeNodes.forEach(function (node) {
            var nodeName = node.name || 'Category';
            var currentPath = node.category_path || (parentPath ? parentPath.concat([nodeName]) : [nodeName]);

            if (node.variants) {
              for (var vName in node.variants) {
                if (node.variants.hasOwnProperty(vName)) {
                  var vData = node.variants[vName];
                  var vPath = vData.category_path || currentPath;
                  addProductToCatalog(vName, node.name || nodeName, vPath, vData);
                }
              }
            }

            if (node.children) {
              collectProductsFromTree(node.children, currentPath);
            }
          });
        }

        /* ===== Recursive Multi-Level Category Tree Generator ===== */
        function renderCategoryTree(treeNodes, containerEl, level) {
          if (!treeNodes || !Array.isArray(treeNodes) || treeNodes.length === 0) return;

          treeNodes.forEach(function (node) {
            if (node.type === 'product_row') return;

            var li = document.createElement('li');
            li.className = 'cat-node level-' + level;

            var nodeName = node.name || 'Category';
            var subCats = (node.children || []).filter(function (c) { return c.type === 'category'; });
            var hasChildren = subCats.length > 0;

            if (hasChildren) {
              var row = document.createElement('div');
              row.className = 'cat-row level-' + level;
              row.innerHTML = '<span class="cat-label-text" data-cat="' + nodeName + '">' + nodeName + '</span><span class="cat-toggle">+</span>';

              row.querySelector('.cat-label-text').addEventListener('click', function (e) {
                e.stopPropagation();
                toggleCategoryFilter(nodeName);
              });

              row.addEventListener('click', function () {
                li.classList.toggle('open');
                var toggle = row.querySelector('.cat-toggle');
                if (toggle) toggle.innerHTML = li.classList.contains('open') ? '&ndash;' : '+';
              });

              li.appendChild(row);

              var subUl = document.createElement('ul');
              subUl.className = 'sub-list level-' + (level + 1);
              renderCategoryTree(subCats, subUl, level + 1);
              li.appendChild(subUl);
            } else {
              var link = document.createElement('a');
              link.href = '#';
              link.className = 'cat-sub-link level-' + level;
              link.setAttribute('data-sub', nodeName);
              link.textContent = nodeName;
              if (activeFilters.categories.includes(nodeName)) {
                link.classList.add('active');
              }

              link.addEventListener('click', function (e) {
                e.preventDefault();
                toggleCategoryFilter(nodeName);
              });

              li.appendChild(link);
            }

            containerEl.appendChild(li);
          });
        }

        function productMatchesCategory(p, selectedCat) {
          var selLower = String(selectedCat || '').trim().toLowerCase();
          if (!selLower) return true;

          if (p.categories && p.categories.some(function (c) {
            return String(c).trim().toLowerCase() === selLower;
          })) return true;

          function pathContains(path) {
            return !!(path && path.some(function (cp) {
              return String(cp).trim().toLowerCase() === selLower;
            }));
          }

          if (pathContains(p.category_path)) return true;
          if (p.category_paths && p.category_paths.some(pathContains)) return true;
          if (p.sub && p.sub.trim().toLowerCase() === selLower) return true;
          if (p.cat && p.cat.trim().toLowerCase() === selLower) return true;

          return false;
        }

        function toggleCategoryFilter(categoryName) {
          if (activeFilters.categories.length === 1 && activeFilters.categories[0] === categoryName) {
            activeFilters.categories = []; // Deselect if clicking active category
          } else {
            activeFilters.categories = [categoryName]; // Filter exclusively to clicked category
          }
          updateActiveCategoryClasses();
          renderFilterAccordion();
          updateActiveTags();
        }

        function updateActiveCategoryClasses() {
          document.querySelectorAll('.cat-label-text, .cat-sub-link').forEach(function (el) {
            var catName = el.getAttribute('data-cat') || el.getAttribute('data-sub');
            if (catName && activeFilters.categories.includes(catName)) {
              el.classList.add('active');
            } else {
              el.classList.remove('active');
            }
          });
        }

        /* ===== Sort Attribute Values by Airtable Order or Natural Alphanumeric ===== */
        function sortAttributeValues(specName, values) {
          var orderMap = null;
          if (typeof AZOOGI_PRODUCTS !== 'undefined' && AZOOGI_PRODUCTS.attribute_values_order) {
            var targetLower = String(specName).trim().toLowerCase();
            for (var k in AZOOGI_PRODUCTS.attribute_values_order) {
              if (k.trim().toLowerCase() === targetLower) {
                var list = AZOOGI_PRODUCTS.attribute_values_order[k];
                if (Array.isArray(list)) {
                  orderMap = {};
                  list.forEach(function (val, idx) {
                    orderMap[String(val).trim().toLowerCase()] = idx;
                  });
                }
                break;
              }
            }
          }

          return values.slice().sort(function (a, b) {
            var aStr = String(a).trim();
            var bStr = String(b).trim();
            var aLower = aStr.toLowerCase();
            var bLower = bStr.toLowerCase();

            if (orderMap) {
              var aIdx = orderMap.hasOwnProperty(aLower) ? orderMap[aLower] : 999999;
              var bIdx = orderMap.hasOwnProperty(bLower) ? orderMap[bLower] : 999999;
              if (aIdx !== bIdx) return aIdx - bIdx;
            }

            // Natural alphanumeric sorting fallback so 10mm < 13.1mm < 116.1mm
            return aStr.localeCompare(bStr, undefined, { numeric: true, sensitivity: 'base' });
          });
        }

        /* ===== Dynamic Category-Scoped Specification Filter Accordion ===== */
        function renderFilterAccordion() {
          // 0. Determine allowed filterable attributes from Airtable config
          var allowedFilterKeys = null;
          if (typeof AZOOGI_PRODUCTS !== 'undefined' && Array.isArray(AZOOGI_PRODUCTS.filterable_attributes) && AZOOGI_PRODUCTS.filterable_attributes.length > 0) {
            allowedFilterKeys = new Set(
              AZOOGI_PRODUCTS.filterable_attributes.map(function (k) {
                return String(k).trim().toLowerCase();
              }).filter(Boolean)
            );
          }

          // 1. Determine target products (filtered by active category, or all products if no category selected)
          var targetProducts = products;
          if (activeFilters.categories && activeFilters.categories.length > 0) {
            targetProducts = products.filter(function (p) {
              return activeFilters.categories.some(function (selectedCat) {
                return productMatchesCategory(p, selectedCat);
              });
            });
          }

          // 2. Extract specs map ONLY from targetProducts in selected category (filtered by allowedFilterKeys)
          var catSpecsMap = {};
          targetProducts.forEach(function (prod) {
            var features = prod.specs || {};
            for (var featKey in features) {
              if (!features.hasOwnProperty(featKey)) continue;
              if (!featKey || typeof featKey !== 'string' || featKey.trim() === '') continue;
              if (allowedFilterKeys !== null && !allowedFilterKeys.has(featKey.trim().toLowerCase())) continue;
              var featVal = features[featKey];
              if (!catSpecsMap[featKey]) catSpecsMap[featKey] = new Set();
              if (Array.isArray(featVal)) {
                featVal.forEach(function (v) {
                  if (v !== null && v !== undefined) {
                    var sVal = (typeof v === 'object' && v !== null && v.value !== undefined) ? String(v.value).trim() : String(v).trim();
                    if (sVal.length > 0 && sVal.length < 80) catSpecsMap[featKey].add(sVal);
                  }
                });
              } else if (featVal !== null && featVal !== undefined) {
                var sVal = (typeof featVal === 'object' && featVal !== null && featVal.value !== undefined) ? String(featVal.value).trim() : String(featVal).trim();
                if (sVal.length > 0 && sVal.length < 80) catSpecsMap[featKey].add(sVal);
              }
            }
          });

          // 3. Preserve open/collapsed accordion states
          var openStateMap = {};
          var filterAcc = document.getElementById('filterAccordion');
          if (filterAcc) {
            filterAcc.querySelectorAll('.filter-item').forEach(function (itemEl) {
              var headSpan = itemEl.querySelector('.filter-head span');
              if (headSpan) {
                openStateMap[headSpan.textContent] = itemEl.classList.contains('open');
              }
            });
          }

          // 4. Build filters list in the order defined by filterable_attributes (Airtable Order column)
          var filters = [];
          var addedKeys = new Set();

          if (typeof AZOOGI_PRODUCTS !== 'undefined' && Array.isArray(AZOOGI_PRODUCTS.filterable_attributes)) {
            AZOOGI_PRODUCTS.filterable_attributes.forEach(function (specName) {
              var targetLower = String(specName).trim().toLowerCase();
              var matchedKey = Object.keys(catSpecsMap).find(function (k) {
                return k.trim().toLowerCase() === targetLower;
              });
              if (matchedKey && !addedKeys.has(matchedKey.toLowerCase())) {
                addedKeys.add(matchedKey.toLowerCase());
                var pVals = sortAttributeValues(matchedKey, Array.from(catSpecsMap[matchedKey]));
                if (pVals.length >= 1) {
                  filters.push({
                    name: matchedKey,
                    options: pVals,
                    open: openStateMap.hasOwnProperty(matchedKey) ? openStateMap[matchedKey] : false
                  });
                }
              }
            });
          }

          // Add any remaining keys that were not explicitly listed in filterable_attributes
          for (var specKey in catSpecsMap) {
            if (!catSpecsMap.hasOwnProperty(specKey)) continue;
            if (addedKeys.has(specKey.toLowerCase())) continue;
            var vals = sortAttributeValues(specKey, Array.from(catSpecsMap[specKey]));
            if (vals.length >= 1) {
              filters.push({
                name: specKey,
                options: vals,
                open: openStateMap.hasOwnProperty(specKey) ? openStateMap[specKey] : false
              });
            }
          }

          // Clean activeFilters.specs if any spec keys no longer exist in the selected category
          for (var activeGroup in activeFilters.specs) {
            if (!catSpecsMap.hasOwnProperty(activeGroup) || (allowedFilterKeys !== null && !allowedFilterKeys.has(activeGroup.trim().toLowerCase()))) {
              delete activeFilters.specs[activeGroup];
            }
          }

          // 5. Render accordion HTML & attach events
          if (filterAcc) {
            filterAcc.innerHTML = '';
            if (filters.length === 0) {
              filterAcc.innerHTML = '<div style="padding:12px 0; font-size:var(--fs-kicker); color:var(--muted);">No attributes available for this category.</div>';
              return;
            }

            filters.forEach(function (f) {
              var div = document.createElement('div');
              div.className = 'filter-item' + (f.open ? ' open' : '');
              var opts = f.options.map(function (o) {
                var isChecked = activeFilters.specs[f.name] && activeFilters.specs[f.name].includes(o);
                return '<label class="opt-row"><input type="checkbox" name="' + f.name + '" value="' + o + '"' + (isChecked ? ' checked' : '') + '> ' + o + '</label>';
              }).join('');

              div.innerHTML = '<div class="filter-head"><span>' + f.name + '</span><span class="filter-toggle">' + (f.open ? '&ndash;' : '+') + '</span></div><div class="filter-body">' + opts + '</div>';

              div.querySelector('.filter-head').addEventListener('click', function () {
                div.classList.toggle('open');
                div.querySelector('.filter-toggle').innerHTML = div.classList.contains('open') ? '&ndash;' : '+';
              });

              div.querySelectorAll('.opt-row input').forEach(function (input) {
                input.addEventListener('change', function () {
                  var group = this.name;
                  var val = this.value;
                  if (!activeFilters.specs[group]) activeFilters.specs[group] = [];
                  if (this.checked) {
                    if (!activeFilters.specs[group].includes(val)) activeFilters.specs[group].push(val);
                  } else {
                    activeFilters.specs[group] = activeFilters.specs[group].filter(function (x) { return x !== val; });
                    if (activeFilters.specs[group].length === 0) delete activeFilters.specs[group];
                  }
                  updateActiveTags();
                });
              });

              filterAcc.appendChild(div);
            });
          }
        }

        function removeFilter(type, val, group) {
          var searchInputEl = document.getElementById('prodSearchInput');
          if (type === 'search') {
            activeFilters.search = "";
            if (searchInputEl) searchInputEl.value = "";
          } else if (type === 'cat') {
            activeFilters.categories = activeFilters.categories.filter(function (x) { return x !== val; });
            updateActiveCategoryClasses();
            renderFilterAccordion();
          } else if (type === 'spec') {
            activeFilters.specs[group] = activeFilters.specs[group].filter(function (x) { return x !== val; });
            if (activeFilters.specs[group].length === 0) delete activeFilters.specs[group];
            document.querySelectorAll('#filterAccordion .opt-row input').forEach(function (input) {
              if (input.name === group && input.value === val) input.checked = false;
            });
          }
          updateActiveTags();
        }

        function clearFilters() {
          var searchInputEl = document.getElementById('prodSearchInput');
          activeFilters.search = "";
          if (searchInputEl) searchInputEl.value = "";
          activeFilters.categories = [];
          activeFilters.specs = {};
          updateActiveCategoryClasses();
          renderFilterAccordion();
          updateActiveTags();
        }

        function updateActiveTags() {
          currentPage = 1; // Reset to page 1 on filter changes
          var tagHtml = '';

          if (activeFilters.search.trim()) {
            tagHtml += '<div class="p-tag">Search: "' + activeFilters.search.trim() + '"<span class="remove-tag" onclick="removeFilter(\'search\')">&times;</span></div>';
          }

          activeFilters.categories.forEach(function (c) {
            tagHtml += '<div class="p-tag">' + c + '<span class="remove-tag" onclick="removeFilter(\'cat\', \'' + c + '\')">&times;</span></div>';
          });
          for (var group in activeFilters.specs) {
            activeFilters.specs[group].forEach(function (val) {
              tagHtml += '<div class="p-tag">' + val + '<span class="remove-tag" onclick="removeFilter(\'spec\', \'' + val + '\', \'' + group + '\')">&times;</span></div>';
            });
          }

          if (tagHtml !== '') {
            tagHtml += '<span class="clear-filters" onclick="clearFilters()" style="margin-left:8px; font-size:var(--fs-kicker); color:var(--muted); cursor:pointer; align-self:center;">Clear all filters</span>';
          }

          var activeTagsEl = document.getElementById('activeTags');
          if (activeTagsEl) activeTagsEl.innerHTML = tagHtml;
          renderGrid();
        }

        function renderGrid() {
          var countEl = document.getElementById('resultCount');
          var grid = document.getElementById('productGrid');
          var searchLower = activeFilters.search.trim().toLowerCase();

          var filtered = products.filter(function (p) {
            if (searchLower !== "") {
              var matchSearch = p.name.toLowerCase().indexOf(searchLower) !== -1 ||
                p.modelName.toLowerCase().indexOf(searchLower) !== -1 ||
                p.sub.toLowerCase().indexOf(searchLower) !== -1 ||
                p.cat.toLowerCase().indexOf(searchLower) !== -1 ||
                (p.sku && p.sku.toLowerCase().indexOf(searchLower) !== -1) ||
                (p.category_path && p.category_path.some(function (cp) {
                  return cp.toLowerCase().indexOf(searchLower) !== -1;
                }));

              if (!matchSearch && p.specs) {
                for (var skey in p.specs) {
                  if (!p.specs.hasOwnProperty(skey)) continue;
                  var sval = p.specs[skey];
                  var valStr = Array.isArray(sval)
                    ? sval.map(function (item) { return (typeof item === 'object' && item !== null && item.value !== undefined) ? item.value : item; }).join(' ')
                    : ((typeof sval === 'object' && sval !== null && sval.value !== undefined) ? sval.value : String(sval));
                  if (valStr.toLowerCase().indexOf(searchLower) !== -1) {
                    matchSearch = true;
                    break;
                  }
                }
              }
              if (!matchSearch) return false;
            }

            if (activeFilters.categories.length > 0) {
              var matchCat = activeFilters.categories.some(function (selectedCat) {
                return productMatchesCategory(p, selectedCat);
              });
              if (!matchCat) return false;
            }
            for (var group in activeFilters.specs) {
              var selectedVals = activeFilters.specs[group];
              if (selectedVals.length > 0) {
                var productSpec = p.specs[group];
                if (!productSpec) return false;

                var matchesAny = selectedVals.some(function (selVal) {
                  var targetSel = String(selVal || '').trim().toLowerCase();
                  if (Array.isArray(productSpec)) {
                    return productSpec.some(function (psVal) {
                      var rawVal = (typeof psVal === 'object' && psVal !== null && psVal.value !== undefined) ? psVal.value : psVal;
                      return String(rawVal || '').trim().toLowerCase() === targetSel;
                    });
                  } else {
                    var rawVal = (typeof productSpec === 'object' && productSpec !== null && productSpec.value !== undefined) ? productSpec.value : productSpec;
                    return String(rawVal || '').trim().toLowerCase() === targetSel;
                  }
                });

                if (!matchesAny) return false;
              }
            }
            return true;
          });

          // Sort filtered products
          if (activeFilters.sort === 'name_asc') {
            filtered.sort(function (a, b) { return a.name.localeCompare(b.name); });
          } else if (activeFilters.sort === 'name_desc') {
            filtered.sort(function (a, b) { return b.name.localeCompare(a.name); });
          } else if (activeFilters.sort === 'cat_asc') {
            filtered.sort(function (a, b) { return a.sub.localeCompare(b.sub) || a.name.localeCompare(b.name); });
          }

          if (countEl) countEl.textContent = filtered.length;

          if (!grid) return;

          if (filtered.length === 0) {
            grid.innerHTML = '<div style="padding: 40px; text-align: center; color: var(--muted); grid-column: 1 / -1;">No products found matching the selected filters.</div>';
            renderPagination(0);
            return;
          }

          var totalPages = Math.ceil(filtered.length / pageSize);
          if (currentPage > totalPages) currentPage = Math.max(1, totalPages);

          var pagedItems = filtered.slice((currentPage - 1) * pageSize, currentPage * pageSize);

          grid.innerHTML = pagedItems.map(function (p) {
            var detailUrl = p.slug ? ('/products/' + encodeURIComponent(p.slug)) : (p.id ? ('/products/' + encodeURIComponent(p.id)) : (p.filePath ? ('/product-detail?file=' + encodeURIComponent(p.filePath)) : ('/product-detail?product=' + encodeURIComponent(p.name))));

            var isFallback = !p.img || p.img === '/assets/bg_default.png' || p.img === '/assets/logo_dark.png';
            var imgHtml = '<img src="' + (p.img || '/assets/bg_default.png') + '" alt="' + (p.name || 'Product') + '" class="prod-swatch' + (isFallback ? ' is-fallback' : '') + '" loading="lazy" onerror="this.onerror=null; this.src=\'/assets/bg_default.png\'; this.classList.add(\'is-fallback\');"' + (isFallback ? ' style="filter: grayscale(100%); opacity: 0.7;"' : '') + '>';

            var displayName = p.name;

            return '<div class="prod-card" data-href="' + detailUrl + '" role="link" tabindex="0">' +
              '<div class="prod-card-img">' + imgHtml + '</div>' +
              '<div class="prod-card-title">' +
              '<div class="prod-card-title-text"><span class="cat-label">' + p.sub + '</span>' + displayName + '</div>' +
              '<button class="add-quote-btn" aria-label="Add to quote" data-quote-id="' + escapeHtml(p.id || p.sku || p.name) + '" data-quote-name="' + escapeHtml(p.name) + '" data-quote-sku="' + escapeHtml(primaryProductCode(p.sku)) + '" data-quote-image="' + escapeHtml(p.img || '') + '" data-quote-url="' + escapeHtml(detailUrl) + '" onclick="event.stopPropagation();">+</button>' +
              '</div>' +
              '</div>';
          }).join('');

          if (window.AzoogiQuote && typeof window.AzoogiQuote.refresh === 'function') {
            window.AzoogiQuote.refresh();
          }

          renderPagination(totalPages);
        }

        function renderPagination(totalPages) {
          var paginationEl = document.getElementById('paginationContainer');
          var grid = document.getElementById('productGrid');
          if (!paginationEl) return;

          if (totalPages <= 1) {
            paginationEl.style.display = 'none';
            paginationEl.innerHTML = '';
            return;
          }

          paginationEl.style.display = 'flex';
          var html = '';

          // Prev Button
          if (currentPage > 1) {
            html += '<div class="pg-btn" data-page="' + (currentPage - 1) + '">&lsaquo;</div>';
          } else {
            html += '<div class="pg-btn disabled" style="opacity:0.3; cursor:not-allowed;">&lsaquo;</div>';
          }

          var startPage = Math.max(1, currentPage - 2);
          var endPage = Math.min(totalPages, startPage + 4);
          if (endPage - startPage < 4) {
            startPage = Math.max(1, endPage - 4);
          }

          if (startPage > 1) {
            html += '<div class="pg-btn" data-page="1">1</div>';
            if (startPage > 2) html += '<span style="color:var(--muted); align-self:center; margin:0 4px;">...</span>';
          }

          for (var i = startPage; i <= endPage; i++) {
            var activeClass = (i === currentPage) ? ' active' : '';
            html += '<div class="pg-btn' + activeClass + '" data-page="' + i + '">' + i + '</div>';
          }

          if (endPage < totalPages) {
            if (endPage < totalPages - 1) html += '<span style="color:var(--muted); align-self:center; margin:0 4px;">...</span>';
            html += '<div class="pg-btn" data-page="' + totalPages + '">' + totalPages + '</div>';
          }

          // Next Button
          if (currentPage < totalPages) {
            html += '<div class="pg-btn" data-page="' + (currentPage + 1) + '">&rsaquo;</div>';
          } else {
            html += '<div class="pg-btn disabled" style="opacity:0.3; cursor:not-allowed;">&rsaquo;</div>';
          }

          paginationEl.innerHTML = html;

          paginationEl.querySelectorAll('.pg-btn[data-page]').forEach(function (btn) {
            btn.addEventListener('click', function () {
              var page = parseInt(this.getAttribute('data-page'), 10);
              if (page && page !== currentPage) {
                currentPage = page;
                renderGrid();
                if (grid) grid.scrollIntoView({ behavior: 'smooth', block: 'start' });
              }
            });
          });
        }

        /* ===== Initialize Products Page on DOMContentLoaded ===== */
        function initProductsPage() {
          if (typeof AZOOGI_PRODUCTS === 'undefined') {
            console.warn('AZOOGI_PRODUCTS is not loaded yet.');
            return;
          }

          // 1. Index products by ID
          rawProductsById = {};
          if (AZOOGI_PRODUCTS.products && Array.isArray(AZOOGI_PRODUCTS.products)) {
            AZOOGI_PRODUCTS.products.forEach(function (p) {
              if (p && p.id) rawProductsById[p.id] = p;
            });
          }

          // 2. Extract products from products array and tree
          productsMap = {};
          products = [];
          extractedSpecsMap = {};

          if (AZOOGI_PRODUCTS.products) {
            if (Array.isArray(AZOOGI_PRODUCTS.products)) {
              AZOOGI_PRODUCTS.products.forEach(function (prod) {
                if (prod.status && String(prod.status).toLowerCase().trim() !== 'publish') return;
                var pName = prod.product_name || prod.name || "Product";
                var images = prod.product_images || [];
                var imgUrl = images.length > 0 ? images[0] : '/assets/bg_default.png';
                var feats = prod.product_features || {};

                extractSpecsFromFeatures(feats);

                var cats = (prod.categories && Array.isArray(prod.categories)) ? prod.categories.slice() : [prod.category || "General"];
                var catPaths = (prod.category_paths && Array.isArray(prod.category_paths)) ? prod.category_paths.slice() : (prod.category_path ? [prod.category_path] : [[prod.category || "General"]]);

                var item = {
                  id: prod.id,
                  name: pName,
                  modelName: pName,
                  sub: prod.category || "General",
                  cat: prod.category || "General",
                  categories: cats,
                  category_path: prod.category_path || [prod.category || "General"],
                  category_paths: catPaths,
                  filePath: '',
                  img: getLocalImg(imgUrl, ''),
                  specs: feats,
                  sku: extractProductCode(prod),
                  rawProd: prod
                };
                var itemKey = prod.id || pName;
                if (!productsMap[itemKey]) {
                  productsMap[itemKey] = true;
                  products.push(item);
                }
              });
            } else {
              for (var pKey in AZOOGI_PRODUCTS.products) {
                if (!AZOOGI_PRODUCTS.products.hasOwnProperty(pKey)) continue;
                var pRow = AZOOGI_PRODUCTS.products[pKey];
                var variants = pRow.variants || {};
                var catPath = pRow.category_path || [];
                for (var vName in variants) {
                  if (!variants.hasOwnProperty(vName)) continue;
                  addProductToCatalog(vName, pRow.name || pKey, catPath, variants[vName]);
                }
              }
            }
          }

          if (AZOOGI_PRODUCTS.tree) {
            collectProductsFromTree(AZOOGI_PRODUCTS.tree, []);
          }

          // 3. Render categories in sidebar
          var catListEl = document.getElementById('catList');
          if (catListEl) {
            catListEl.innerHTML = '';
            if (AZOOGI_PRODUCTS.tree && AZOOGI_PRODUCTS.tree.length > 0) {
              renderCategoryTree(AZOOGI_PRODUCTS.tree, catListEl, 0);
            } else if (AZOOGI_PRODUCTS.categories && Array.isArray(AZOOGI_PRODUCTS.categories)) {
              AZOOGI_PRODUCTS.categories.forEach(function (catName) {
                var li = document.createElement('li');
                li.className = 'cat-node level-0';
                var link = document.createElement('a');
                link.href = '#';
                link.className = 'cat-sub-link level-0';
                link.setAttribute('data-sub', catName);
                link.textContent = catName;
                link.addEventListener('click', function (e) {
                  e.preventDefault();
                  toggleCategoryFilter(catName);
                });
                li.appendChild(link);
                catListEl.appendChild(li);
              });
            }
          }

          // 4. Attach search & sort input handlers
          var searchInputEl = document.getElementById('prodSearchInput');
          if (searchInputEl) {
            searchInputEl.value = activeFilters.search || "";
            searchInputEl.addEventListener('input', function () {
              activeFilters.search = this.value;
              updateActiveTags();
            });
          }

          var sortSelectEl = document.getElementById('sortSelect');
          if (sortSelectEl) {
            sortSelectEl.value = activeFilters.sort || "relevance";
            sortSelectEl.addEventListener('change', function () {
              activeFilters.sort = this.value;
              currentPage = 1;
              renderGrid();
            });
          }

          // 5. Initial category filter from URL parameter
          var urlParams = new URLSearchParams(window.location.search);
          var catParam = urlParams.get('category');
          if (catParam) {
            catParam = decodeURIComponent(catParam);
            if (!activeFilters.categories.includes(catParam)) {
              activeFilters.categories.push(catParam);
            }
            updateActiveCategoryClasses();
            document.querySelectorAll('.cat-label-text, .cat-sub-link').forEach(function (link) {
              var cName = link.getAttribute('data-cat') || link.getAttribute('data-sub');
              if (cName === catParam) {
                link.classList.add('active');
                var li = link.closest('li');
                if (li) {
                  li.classList.add('open');
                  var toggle = li.querySelector('.cat-toggle');
                  if (toggle) toggle.innerHTML = '&ndash;';
                  var topLi = li.closest('.cat-list > li');
                  if (topLi) {
                    topLi.classList.add('open');
                    var topToggle = topLi.querySelector('.cat-toggle');
                    if (topToggle) topToggle.innerHTML = '&ndash;';
                  }
                }
              }
            });
          }

          // 6. Initial render
          renderFilterAccordion();
          updateActiveTags();
        }

        if (document.readyState === 'loading') {
          document.addEventListener('DOMContentLoaded', initProductsPage);
        } else {
          initProductsPage();
        }
      </script>
    @endverbatim
  @endpush
@endif