@php
    $topbarClass = trim($__env->yieldContent('topbarClass', 'solid'));
    $logoFile = trim($__env->yieldContent('logo', 'logo_dark.png'));
@endphp
<header class="topbar {{ $topbarClass }}" id="topbar">
    <div class="util">
        <div class="util-inner">
            <div class="util-rotate" aria-live="polite">Australian-Owned B2B Trade Wholesaler - Custom Lighting &amp; Smart Control Solutions</div>
            <div style="display:flex;gap:24px">
                <a href="tel:1300641261">1300 641 261</a>
                <a href="mailto:sales@azoogi.com">sales@azoogi.com</a>
                <div style="display:flex; align-items:center;"><a href="{{ url('/trade-login') }}">Trade Login</a></div>
            </div>
        </div>
    </div>
    <nav class="nav">
        <a href="{{ url('/') }}" class="logo"><img src="{{ asset('assets/'.$logoFile) }}" width="80" alt="Azoogi"></a>
        <div class="menu">
            <div class="has-dropdown">
                <a href="{{ url('/products') }}">Products <span class="caret">&#9662;</span></a>
                <div class="mega-menu" id="dynamic-mega-menu"></div>
            </div>
            <a href="{{ url('/projects') }}">Projects</a>
            <a href="{{ url('/about') }}">About Us</a>
            <a href="{{ url('/solutions') }}">Solutions</a>
            <a href="{{ url('/contact') }}">Contact</a>
            <a href="{{ url('/ai-lighting') }}">AI Lighting</a>
        </div>
        <a href="{{ url('/led-strip-calculator') }}" class="cta">LED Calculator</a>
        <div class="burger"><span></span><span></span><span></span></div>
    </nav>
</header>
