<!doctype html>
<html lang="en" {!! trim($__env->yieldContent('htmlAttributes')) !!}>
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>{{ trim($__env->yieldContent('title', 'Azoogi')) }}</title>
<meta name="description" content="{{ trim($__env->yieldContent('description', 'Azoogi designs and supplies premium LED lighting for projects that demand more.')) }}">
<link rel="icon" href="{{ asset('assets/favicon.png') }}">
@if (request()->routeIs('dashboard.pages.preview'))
<base href="{{ rtrim(url('/'), '/') }}/">
@endif
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Google+Sans+Flex:opsz,wght@6..144,1..1000&display=swap" rel="stylesheet">
<link rel="stylesheet" href="{{ versioned_asset('assets/css/style_demo.css') }}">
<link rel="stylesheet" href="{{ versioned_asset('assets/css/quote.css') }}">
@stack('styles')
@if (trim($__env->yieldContent('chrome', 'full')) !== 'none')
<script>const AZOOGI_PRODUCTS = @json($productCatalog, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);</script>
<script defer src="{{ asset('assets/js/mega_menu.js') }}?v={{ config('app.asset_version') }}"></script>
<script defer src="{{ asset('assets/js/site_header.js') }}?v={{ config('app.asset_version') }}"></script>
<script defer src="{{ versioned_asset('assets/js/quote.js') }}"></script>
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

@stack('scripts')
@if (request()->routeIs('dashboard.pages.preview'))
<script src="{{ asset('assets/js/cms-editor.js') }}?v={{ config('app.asset_version') }}"></script>
@endif
</body>
</html>
