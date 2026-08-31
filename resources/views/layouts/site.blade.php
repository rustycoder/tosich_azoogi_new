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
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=Cormorant+Garamond:wght@300;400;500;600&display=swap" rel="stylesheet">
<link rel="stylesheet" href="{{ asset('assets/css/style_demo.v-1.5.css') }}?v={{ config('app.asset_version') }}">
@stack('styles')
@if (trim($__env->yieldContent('chrome', 'full')) !== 'none')
<script src="{{ asset('assets/js/products_data.js') }}?v={{ config('app.asset_version') }}"></script>
<script src="{{ asset('assets/js/mega_menu.js') }}?v={{ config('app.asset_version') }}"></script>
<script src="{{ asset('assets/js/site_header.js') }}?v={{ config('app.asset_version') }}"></script>
@endif
@stack('head')
@if (request()->routeIs('dashboard.pages.preview'))
<link rel="stylesheet" href="{{ asset('assets/css/cms-editor.css') }}?v={{ config('app.asset_version') }}">
@endif
</head>
<body class="@yield('bodyClass')" {!! trim($__env->yieldContent('bodyAttributes')) !!}>
@if (trim($__env->yieldContent('chrome', 'full')) !== 'none')
    @include('partials.header')
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
