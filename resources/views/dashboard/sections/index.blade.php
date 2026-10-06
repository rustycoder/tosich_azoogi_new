@extends('layouts.dashboard')

@section('title', 'Sections')

@section('content')
<div class="dash-head">
    <div>
        <h1>Sections</h1>
        <p class="dash-lead">Edit header and footer copy shown on every public page.</p>
    </div>
</div>

@include('dashboard.partials.search', [
    'action' => route('dashboard.sections.index'),
    'search' => $search,
])

<div class="dash-list">
    @forelse ($pages as $page)
        @php
            $bag = \App\Support\PageMetaBag::for($page);
        @endphp
        <article class="dash-list-card dash-section-card">
            <div class="dash-list-card-header-row">
                <div class="dash-list-card-copy">
                    @include('dashboard.partials.title-link', [
                        'href' => route('dashboard.sections.edit', $page),
                        'label' => \App\PageMeta\Catalog::for($page->slug)->navLabel(),
                    ])
                    @if ($description = \App\PageMeta\Catalog::sectionDescription($page->slug))
                        <p class="dash-list-sub">{{ $description }}</p>
                    @endif
                </div>
                <div class="dash-list-card-meta is-end">
                    @include('dashboard.partials.updated', ['record' => $page])
                </div>
            </div>

            <!-- Live Section Content & Layout Preview -->
            <div class="dash-section-preview-wrap">
                @if ($page->slug === 'header')
                    @php
                        $headerDesc = $bag->get('header.description', 0, 'Australian-Owned B2B Trade Wholesaler - Custom Lighting & Smart Control Solutions');
                        $headerPhone = $bag->get('header.phone', 0, '1300 641 261');
                        $headerEmail = $bag->get('header.email', 0, 'sales@azoogi.com');
                        $headerWords = collect($bag->group('header.word'))->pluck('text')->filter()->values();
                        $headerNav = collect($bag->group('header.nav'))->filter(fn($i) => trim($i['label'] ?? '') !== '')->values();
                        if ($headerNav->isEmpty()) {
                            $headerNav = collect(\App\PageMeta\Definitions\HeaderDefinition::defaultNav());
                        }
                    @endphp
                    <div class="dash-preview-frame">
                        <!-- Top Utility Bar -->
                        <div class="dash-preview-topbar-util">
                            <div class="dash-preview-topbar-rotate">
                                <span class="dash-preview-dot"></span>
                                <span>{{ $headerDesc }}</span>
                            </div>
                            <div class="dash-preview-topbar-contact">
                                @if ($headerPhone)
                                    <span>
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"/></svg>
                                        {{ $headerPhone }}
                                    </span>
                                @endif
                                @if ($headerEmail)
                                    <span>
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg>
                                        {{ $headerEmail }}
                                    </span>
                                @endif
                                <span class="dash-preview-trade-pill">Trade Login</span>
                            </div>
                        </div>

                        <!-- Main Navigation Bar -->
                        <div class="dash-preview-topbar-nav">
                            <div class="dash-preview-brand">
                                <strong>AZOOGI</strong>
                            </div>
                            <div class="dash-preview-nav-links">
                                <span class="dash-preview-link is-active">Products ▾</span>
                                @foreach ($headerNav as $navItem)
                                    <span class="dash-preview-link">{{ $navItem['label'] }}</span>
                                @endforeach
                            </div>
                            <div class="dash-preview-nav-cta">
                                <span class="dash-preview-cta-btn">LED Calculator</span>
                            </div>
                        </div>

                        @if ($headerWords->isNotEmpty())
                            <div class="dash-preview-words-row">
                                <span class="dash-preview-words-title">Rotating Keywords:</span>
                                @foreach ($headerWords as $word)
                                    <span class="dash-tag is-primary" style="font-size: 11px; padding: 2px 8px;">{{ $word }}</span>
                                @endforeach
                            </div>
                        @endif
                    </div>

                @elseif ($page->slug === 'footer')
                    @php
                        $footerDesc = $bag->get('footer.description', 0, 'We design, engineer, manufacture, assemble, and test our products in-house, offering custom powder coating to deliver fully tailored lighting solutions');
                        $footerPhone = $bag->get('footer.phone', 0, '1300 641 261');
                        $footerEmail = $bag->get('footer.email', 0, 'sales@azoogi.com');
                        $footerMsg = $bag->get('footer.message', 0, 'Azoogi Pty Ltd. All rights reserved.');
                        $prodHead = $bag->get('footer.products.heading', 0, 'Products');
                        $compHead = $bag->get('footer.company.heading', 0, 'Company');
                        $contHead = $bag->get('footer.contact.heading', 0, 'Contact');
                        $prodLinks = collect($bag->group('footer.products.link'))->filter(fn($i) => trim($i['label'] ?? '') !== '');
                        $compLinks = collect($bag->group('footer.company.link'))->filter(fn($i) => trim($i['label'] ?? '') !== '');
                        $contLinks = collect($bag->group('footer.contact.link'))->filter(fn($i) => trim($i['label'] ?? '') !== '');
                    @endphp
                    <div class="dash-preview-frame">
                        <div class="dash-preview-foot-columns">
                            <!-- Brand & Mission Column -->
                            <div class="dash-preview-foot-brand">
                                <div class="dash-preview-brand" style="margin-bottom: 6px;">
                                    <strong>AZOOGI</strong>
                                </div>
                                <p class="dash-preview-foot-bio">{{ $footerDesc }}</p>
                            </div>

                            <!-- Menu 1 -->
                            <div class="dash-preview-foot-col">
                                <h4 class="dash-preview-foot-h">{{ $prodHead }}</h4>
                                <ul class="dash-preview-foot-list">
                                    @forelse ($prodLinks->take(5) as $l)
                                        <li>{{ $l['label'] }}</li>
                                    @empty
                                        <li style="color: var(--dash-muted);">—</li>
                                    @endforelse
                                </ul>
                            </div>

                            <!-- Menu 2 -->
                            <div class="dash-preview-foot-col">
                                <h4 class="dash-preview-foot-h">{{ $compHead }}</h4>
                                <ul class="dash-preview-foot-list">
                                    @forelse ($compLinks->take(5) as $l)
                                        <li>{{ $l['label'] }}</li>
                                    @empty
                                        <li style="color: var(--dash-muted);">—</li>
                                    @endforelse
                                </ul>
                            </div>

                            <!-- Menu 3 & Direct Contact -->
                            <div class="dash-preview-foot-col">
                                <h4 class="dash-preview-foot-h">{{ $contHead }}</h4>
                                <ul class="dash-preview-foot-list">
                                    @if ($footerPhone) <li style="font-weight: 600; color: var(--dash-ink);">{{ $footerPhone }}</li> @endif
                                    @if ($footerEmail) <li style="color: var(--dash-green);">{{ $footerEmail }}</li> @endif
                                    @foreach ($contLinks->take(3) as $l)
                                        <li>{{ $l['label'] }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        </div>

                        <!-- Copyright Bottom Bar -->
                        <div class="dash-preview-foot-bottom">
                            <span>&copy; {{ date('Y') }} {{ $footerMsg }}</span>
                            <div class="dash-preview-foot-legal">
                                <span>Privacy</span>
                                <span class="sep">·</span>
                                <span>Terms</span>
                                <span class="sep">·</span>
                                <span>Warranty</span>
                                <span class="sep">·</span>
                                <span>Modern Slavery</span>
                            </div>
                        </div>
                    </div>
                @endif
            </div>
        </article>
    @empty
        <div class="dash-card dash-empty">{{ $search === '' ? 'No sections assigned to this account.' : 'No titles match that search.' }}</div>
    @endforelse
</div>
@endsection
