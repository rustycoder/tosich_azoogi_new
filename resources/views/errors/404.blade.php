@php
  $pageModel = $page ?? \App\Models\Page::query()->where('slug', '404')->with('meta')->first();
  $pageMeta = (isset($meta) && $meta instanceof \App\Support\PageMetaBag)
      ? $meta
      : ($pageModel ? \App\Support\PageMetaBag::for($pageModel) : \App\Support\PageMetaBag::empty());
  $pageTitle = $page?->title ?? $pageModel?->title ?? 'Page Not Found — Azoogi';
  $pageDescription = $page?->meta_description ?? $pageModel?->meta_description ?? 'The page you requested could not be found.';
@endphp

@extends('layouts.site')

@section('title', $pageTitle)

@section('description', $pageDescription)

@section('bodyClass', 'error-page error-404-page')

@section('chrome', 'full')

@section('topbarClass', 'solid')
@section('logo', 'logo_white.png')

@section('content')
<main class="error-main">
  <div class="wrap error-wrap" {!! cms_section_attr('error') !!}>
    <div class="error-card reveal in">
      <span class="error-code"{!! cms_style($pageMeta, 'error.code') !!}>{{ $pageMeta->get('error.code', 0, '404') }}</span>
      <h1 class="error-title"{!! cms_style($pageMeta, 'error.title') !!}>{{ $pageMeta->get('error.title', 0, 'Page Not Found') }}</h1>
      <p class="error-lead"{!! cms_style($pageMeta, 'error.lead') !!}>
        {{ $pageMeta->get('error.lead', 0, "The page you are looking for doesn't exist, has been removed, or is temporarily unavailable.") }}
      </p>
      <div class="error-actions">
        @if ($pageMeta->get('cta.home.label', 0, 'Back to Home') !== '')
          <a href="{{ url($pageMeta->get('cta.home.href', 0, '/')) }}" class="btn primary"{!! cms_style($pageMeta, 'cta.home.label') !!}>
            {{ $pageMeta->get('cta.home.label', 0, 'Back to Home') }}
          </a>
        @endif
        @if ($pageMeta->get('cta.products.label', 0, 'Browse Products') !== '')
          <a href="{{ url($pageMeta->get('cta.products.href', 0, '/products')) }}" class="btn"{!! cms_style($pageMeta, 'cta.products.label') !!}>
            {{ $pageMeta->get('cta.products.label', 0, 'Browse Products') }}
          </a>
        @endif
        @if ($pageMeta->get('cta.contact.label', 0, 'Contact Us') !== '')
          <a href="{{ url($pageMeta->get('cta.contact.href', 0, '/contact')) }}" class="btn"{!! cms_style($pageMeta, 'cta.contact.label') !!}>
            {{ $pageMeta->get('cta.contact.label', 0, 'Contact Us') }}
          </a>
        @endif
      </div>
    </div>
  </div>
</main>
@endsection
