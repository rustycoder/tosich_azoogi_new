@extends('layouts.site')

@section('title', $page->title)

@section('description', $page->meta_description)

@section('bodyClass', 'cb-page')

@section('chrome', 'full')

@section('topbarClass', '')
@section('logo', 'logo_white.png')

@push('styles')
<link rel="stylesheet" href="{{ versioned_asset('assets/css/casambi.css') }}">
@endpush

@section('content')
    @php
        $whyItems = $meta->group('why.item');
        $hardwareRows = $meta->group('hardware.row');
        $supportItems = $meta->group('support.item');
        $casambiLogo = media_url($meta->get('hero.logo'));
        $casambiUsesStockLockup = in_array($casambiLogo, [
            '/assets/img/casambi/logo.svg',
            '/assets/img/casambi/logo-dark.svg',
        ], true);
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
            <div class="cb-hero-media" aria-hidden="true">
                <img src="{{ media_url($meta->get('hero.image')) }}" alt="" loading="eager">
            </div>
            <div class="cb-hero-copy">
                @if ($casambiLogo !== '')
                    <div class="cb-lockup">
                        @if ($casambiUsesStockLockup)
                            @include('partials.themed-lockup', [
                                'path' => 'assets/img/casambi/logo-dark.svg',
                                'class' => 'cb-lockup-casambi',
                                'label' => 'Casambi',
                                'fill' => '#111',
                            ])
                        @else
                            <img class="cb-lockup-casambi" src="{{ $casambiLogo }}" alt="Casambi">
                        @endif
                    </div>
                @endif
                <h1 class="cb-title"{!! cms_style($meta, 'hero.title') !!}>{!! accent_html($meta->get('hero.title', 0, 'Casambi Wireless {Smart Ecosystems}')) !!}</h1>
                @php
                  $cbLead = $meta->get('hero.lead', 0, 'Specification-grade Bluetooth Low Energy mesh lighting controls for seamless commercial and high-end residential integration.');
                  $cbIntro = $meta->get('intro.body', 0, 'Casambi provides a robust, scalable wireless solution without the complexity of traditional control wiring.');
                @endphp
                @if (trim($cbLead) !== '')
                  <p class="cb-lead"{!! cms_style($meta, 'hero.lead') !!}>{!! accent_html($cbLead) !!}</p>
                @endif
                @if (trim($cbIntro) !== '')
                  <p class="cb-intro" {!! cms_section_attr('intro') !!}{!! cms_style($meta, 'intro.body') !!}>{!! accent_html($cbIntro) !!}</p>
                @endif
            </div>
        </section>

        <section class="cb-band cb-band--alt" {!! cms_section_attr('why') !!}>
            <div class="wrap">
                <div class="cb-section-head reveal">
                    <h2{!! cms_style($meta, 'why.heading') !!}>{{ $meta->get('why.heading') }}</h2>
                </div>
                <ol class="cb-caps">
                    @foreach ($whyItems as $item)
                        <li class="reveal" style="transition-delay: {{ $loop->iteration * 0.08 }}s">
                            <span class="cb-num">{{ str_pad((string) $loop->iteration, 2, '0', STR_PAD_LEFT) }}</span>
                            <h3{!! cms_style($meta, 'why.item.title', $loop->index) !!}>{{ $item['title'] ?? '' }}</h3>
                                <p{!! cms_style($meta, 'why.item.body', $loop->index) !!}>{{ $item['body'] ?? '' }}</p>
                        </li>
                    @endforeach
                </ol>
            </div>
        </section>

        <section class="cb-band" {!! cms_section_attr('lineup') !!}>
            <div class="wrap">
                <div class="cb-section-head reveal">
                    <h2{!! cms_style($meta, 'lineup.heading') !!}>{{ $meta->get('lineup.heading') }}</h2>
                </div>

                <div class="cb-feature reveal" {!! cms_section_attr('software') !!}>
                    <div class="cb-feature-copy">
                        <h3 class="cb-lineup-heading"{!! cms_style($meta, 'software.heading') !!}>{{ $meta->get('software.heading') }}</h3>
                        <div class="kicker"{!! cms_style($meta, 'software.title') !!}>{{ $meta->get('software.title') }}</div>
                        <p{!! cms_style($meta, 'software.body') !!}>{{ $meta->get('software.body') }}</p>
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
                    <h3 class="cb-lineup-heading"{!! cms_style($meta, 'hardware.heading') !!}>{{ $meta->get('hardware.heading') }}</h3>
                    <div class="cb-table-wrap">
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
                                                <span class="cb-product"
                                                    data-preview="{{ $preview }}" tabindex="0"{!! cms_style($meta, 'hardware.row.product', $loop->index) !!}>{{ $row['product'] ?? '' }}</span>
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

        <section class="cb-band cb-band--alt" {!! cms_section_attr('support') !!}>
            <div class="wrap">
                <div class="cb-section-head reveal">
                    <h2{!! cms_style($meta, 'support.heading') !!}>{{ $meta->get('support.heading') }}</h2>
                        @if (trim($meta->get('support.lead')) !== '')
                            <p{!! cms_style($meta, 'support.lead') !!}>{{ $meta->get('support.lead') }}</p>
                        @endif
                </div>
                <ul class="cb-support">
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
            <section class="cb-band" {!! cms_section_attr('video') !!}>
                <div class="wrap">
                    <div class="cb-video reveal">
                        <iframe src="https://www.youtube-nocookie.com/embed/{{ $videoId }}?rel=0&modestbranding=1"
                            title="Casambi wireless lighting control"
                            allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share"
                            allowfullscreen loading="lazy"></iframe>
                    </div>
                </div>
            </section>
        @endif

        <div class="wrap reveal" {!! cms_section_attr('cta') !!}>
                <div class="cb-cta">
                    <h2{!! cms_style($meta, 'cta.heading') !!}>{{ $meta->get('cta.heading') }}</h2>
                        <p{!! cms_style($meta, 'cta.body') !!}>{{ $meta->get('cta.body') }}</p>
                            <a class="btn primary"
                                href="{{ chrome_url($meta->get('cta.href', 0, '/contact')) }}"{!! cms_style($meta, 'cta.label') !!}>{{ $meta->get('cta.label') }}</a>
                </div>
        </div>

        <div class="cb-cursor-preview" data-product-preview="cb-product" hidden aria-hidden="true">
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

            (function() {
                const io = new IntersectionObserver(function(entries) {
                    entries.forEach(function(entry) {
                        if (entry.isIntersecting) {
                            entry.target.classList.add('in');
                            io.unobserve(entry.target);
                        }
                    });
                }, {
                    threshold: 0.12
                });
                document.querySelectorAll('.reveal').forEach(function(el) {
                    io.observe(el);
                });
            })
            ();
        </script>
    @endverbatim
@endpush
