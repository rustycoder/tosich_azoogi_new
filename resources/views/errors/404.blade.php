@extends('layouts.site')

@section('title', 'Page Not Found — Azoogi')

@section('description', 'The page you requested could not be found.')

@section('bodyClass', 'error-page error-404-page')

@section('chrome', 'full')

@section('topbarClass', 'solid')
@section('logo', 'logo_white.png')

@section('content')
<main class="error-main">
  <div class="wrap error-wrap">
    <div class="error-card reveal in">
      <span class="error-code">404</span>
      <h1 class="error-title">Page Not Found</h1>
      <p class="error-lead">
        The page you are looking for doesn't exist, has been removed, or is temporarily unavailable.
      </p>
      <div class="error-actions">
        <a href="{{ url('/') }}" class="btn primary">Back to Home</a>
        <a href="{{ route('products') }}" class="btn">Browse Products</a>
        <a href="{{ route('contact') }}" class="btn">Contact Us</a>
      </div>
    </div>
  </div>
</main>
@endsection
