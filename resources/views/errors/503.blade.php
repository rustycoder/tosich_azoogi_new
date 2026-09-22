@php
  $pageModel = $page ?? rescue(fn () => \App\Models\Page::query()->where('slug', '503')->with('meta')->first(), null, false);
  $pageMeta = (isset($meta) && $meta instanceof \App\Support\PageMetaBag)
      ? $meta
      : ($pageModel ? \App\Support\PageMetaBag::for($pageModel) : \App\Support\PageMetaBag::empty());
  $pageTitle = $page?->title ?? $pageModel?->title ?? 'Service Unavailable — Azoogi';
  $pageDescription = $page?->meta_description ?? $pageModel?->meta_description ?? 'We are currently performing scheduled maintenance.';
@endphp

@extends('layouts.site')

@section('title', $pageTitle)

@section('description', $pageDescription)

@section('bodyClass', 'error-page error-503-page')

@section('chrome', 'full')

@section('topbarClass', 'solid')
@section('logo', 'logo_white.png')

@section('content')
<main class="error-main">
  <div class="wrap error-wrap" {!! cms_section_attr('error') !!}>
    <div class="error-card reveal in">
      <span class="error-code"{!! cms_style($pageMeta, 'error.code') !!}>{{ $pageMeta->get('error.code', 0, '503') }}</span>
      <h1 class="error-title"{!! cms_style($pageMeta, 'error.title') !!}>{{ $pageMeta->get('error.title', 0, 'Under Maintenance') }}</h1>
      <p class="error-lead"{!! cms_style($pageMeta, 'error.lead') !!}>
        {{ $pageMeta->get('error.lead', 0, 'We are currently performing scheduled maintenance to improve your experience. We will be back online shortly.') }}
      </p>
      <div class="error-actions">
        @if ($pageMeta->get('cta.home.label', 0, 'Back to Home') !== '')
          <a href="{{ url($pageMeta->get('cta.home.href', 0, '/')) }}" class="btn primary"{!! cms_style($pageMeta, 'cta.home.label') !!}>
            {{ $pageMeta->get('cta.home.label', 0, 'Back to Home') }}
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
