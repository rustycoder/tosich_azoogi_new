<!doctype html>
<html lang="en" data-theme="light" {!! trim($__env->yieldContent('htmlAttributes')) !!}>
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>{{ trim($__env->yieldContent('title', 'Azoogi')) }}</title>
<meta name="description" content="{{ trim($__env->yieldContent('description', 'Azoogi designs and supplies premium LED lighting for projects that demand more.')) }}">
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
