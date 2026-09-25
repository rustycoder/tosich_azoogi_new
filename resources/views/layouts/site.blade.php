<!doctype html>
<html lang="en" data-theme="light" {!! trim($__env->yieldContent('htmlAttributes')) !!}>
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>{{ trim($__env->yieldContent('title', 'Azoogi — Architectural LED Lighting Solutions')) }}</title>
<meta name="description" content="{{ trim($__env->yieldContent('description', 'Azoogi designs and supplies premium LED lighting for projects that demand more.')) }}">
<link rel="canonical" href="{{ trim($__env->yieldContent('canonical', url()->current())) }}">
<meta name="robots" content="{{ trim($__env->yieldContent('robots', request()->routeIs('dashboard.pages.preview') || request()->routeIs('trade-login') ? 'noindex, nofollow' : 'index, follow, max-image-preview:large, max-snippet:-1, max-video-preview:-1')) }}">

<!-- Open Graph / Social Sharing -->
<meta property="og:type" content="{{ trim($__env->yieldContent('ogType', 'website')) }}">
<meta property="og:site_name" content="Azoogi">
<meta property="og:url" content="{{ trim($__env->yieldContent('canonical', url()->current())) }}">
<meta property="og:title" content="{{ trim($__env->yieldContent('ogTitle', $__env->yieldContent('title', 'Azoogi — Architectural LED Lighting Solutions'))) }}">
<meta property="og:description" content="{{ trim($__env->yieldContent('ogDescription', $__env->yieldContent('description', 'Azoogi designs and supplies premium LED lighting for projects that demand more.'))) }}">
<meta property="og:image" content="{{ trim($__env->yieldContent('ogImage', !empty($page?->og_image) ? media_url($page->og_image) : asset('assets/logo_dark.png'))) }}">
<meta property="og:locale" content="en_AU">

<!-- Twitter / X Card -->
<meta name="twitter:card" content="{{ trim($__env->yieldContent('twitterCard', 'summary_large_image')) }}">
<meta name="twitter:title" content="{{ trim($__env->yieldContent('ogTitle', $__env->yieldContent('title', 'Azoogi — Architectural LED Lighting Solutions'))) }}">
<meta name="twitter:description" content="{{ trim($__env->yieldContent('ogDescription', $__env->yieldContent('description', 'Azoogi designs and supplies premium LED lighting for projects that demand more.'))) }}">
<meta name="twitter:image" content="{{ trim($__env->yieldContent('ogImage', !empty($page?->og_image) ? media_url($page->og_image) : asset('assets/logo_dark.png'))) }}">

<!-- Structured Data (JSON-LD) -->
@if (trim($__env->yieldContent('schema')) !== '')
@yield('schema')
@else
<script type="application/ld+json">
{
  "@@context": "https://schema.org",
  "@@type": "Organization",
  "name": "Azoogi",
  "url": "{{ url('/') }}",
  "logo": "{{ asset('assets/logo_dark.png') }}",
  "description": "Azoogi designs and supplies premium architectural LED lighting and smart control systems."
}
</script>
@endif

<link rel="icon" href="{{ asset('assets/favicon.png') }}">
<script>
(function () {
  try {
    if (localStorage.getItem('theme') === 'dark') {
      document.documentElement.setAttribute('data-theme', 'dark');
    }
  } catch (e) {}
})();
</script>
@if (request()->routeIs('dashboard.pages.preview'))
<base href="{{ rtrim(url('/'), '/') }}/">
@endif
<link rel="stylesheet" href="{{ versioned_asset('assets/css/style_demo.css') }}">
<link rel="stylesheet" href="{{ versioned_asset('assets/css/quote.css') }}">
<link rel="stylesheet" href="{{ versioned_asset('assets/css/site-search.css') }}">
@stack('styles')
<script defer src="{{ versioned_asset('assets/js/site-theme.js') }}"></script>
@if (trim($__env->yieldContent('chrome', 'full')) !== 'none')
<script>const AZOOGI_PRODUCTS = @json($productCatalog, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);</script>
<script>window.AZOOGI_QUOTE = { productsUrl: @json(route('quote.products')) };</script>
<script defer src="{{ asset('assets/js/mega_menu.js') }}?v={{ config('app.asset_version') }}"></script>
<script defer src="{{ versioned_asset('assets/js/site_header.js') }}"></script>
<script defer src="{{ versioned_asset('assets/js/quote.js') }}"></script>
<script defer src="{{ versioned_asset('assets/js/site-search.js') }}"></script>
@endif
@if (config('services.turnstile.site_key'))
<script src="https://challenges.cloudflare.com/turnstile/v0/api.js" async defer></script>
@endif
@stack('head')
@if (request()->routeIs('dashboard.pages.preview'))
<link rel="stylesheet" href="{{ versioned_asset('assets/css/cms-editor.css') }}">
@endif
</head>
<body class="@yield('bodyClass')" {!! trim($__env->yieldContent('bodyAttributes')) !!}>
@if (trim($__env->yieldContent('chrome', 'full')) !== 'none')
    @include('partials.header')
    @include('partials.quote-drawer')
@endif

@yield('content')

@if (trim($__env->yieldContent('chrome', 'full')) !== 'none')
    @include('partials.footer')
@endif

<div id="site-toasts" class="site-toasts" aria-live="polite" @if (session('status')) data-flash="{{ session('status') }}" @endif @if (session('clear_quote')) data-clear-quote @endif></div>

@stack('scripts')
@if (request()->routeIs('dashboard.pages.preview'))
<script src="{{ asset('assets/js/cms-editor.js') }}?v={{ config('app.asset_version') }}"></script>
@endif
</body>
</html>
