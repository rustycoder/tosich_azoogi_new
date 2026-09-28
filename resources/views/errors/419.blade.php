@php
  $pageModel = $page ?? \App\Models\Page::query()->where('slug', '419')->with('meta')->first();
  $pageMeta = (isset($meta) && $meta instanceof \App\Support\PageMetaBag)
      ? $meta
      : ($pageModel ? \App\Support\PageMetaBag::for($pageModel) : \App\Support\PageMetaBag::empty());
  $pageTitle = $page?->title ?? $pageModel?->title ?? 'Page Expired — Azoogi';
  $pageDescription = $page?->meta_description ?? $pageModel?->meta_description ?? 'Your session has expired.';
@endphp

@extends('layouts.site')

@section('title', $pageTitle)

@section('description', $pageDescription)

@section('bodyClass', 'error-page error-419-page')

@section('chrome', 'full')

@section('topbarClass', 'solid')
@section('logo', 'logo_white.png')

@section('content')
<main class="error-main">
  <div class="wrap error-wrap" {!! cms_section_attr('error') !!}>
    <div class="error-card reveal in">
      <span class="error-code"{!! cms_style($pageMeta, 'error.code') !!}>{{ $pageMeta->get('error.code', 0, '419') }}</span>
      <h1 class="error-title"{!! cms_style($pageMeta, 'error.title') !!}>{{ $pageMeta->get('error.title', 0, 'Page Expired') }}</h1>
      <p class="error-lead"{!! cms_style($pageMeta, 'error.lead') !!}>
        {{ $pageMeta->get('error.lead', 0, 'Your session has expired due to inactivity. Please refresh the page and try again.') }}
      </p>
      <div class="error-actions">
        @if ($pageMeta->get('cta.home.label', 0, 'Back to Home') !== '')
          <a href="{{ url($pageMeta->get('cta.home.href', 0, '/')) }}" class="btn primary"{!! cms_style($pageMeta, 'cta.home.label') !!}>
            {{ $pageMeta->get('cta.home.label', 0, 'Back to Home') }}
          </a>
        @endif
        @if ($pageMeta->get('cta.login.label', 0, 'Go to Login') !== '')
          <a href="{{ url($pageMeta->get('cta.login.href', 0, '/login')) }}" class="btn"{!! cms_style($pageMeta, 'cta.login.label') !!}>
            {{ $pageMeta->get('cta.login.label', 0, 'Go to Login') }}
          </a>
        @endif
      </div>
    </div>
  </div>
</main>
@endsection
