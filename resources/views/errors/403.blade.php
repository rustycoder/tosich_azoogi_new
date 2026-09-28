@php
  $pageModel = $page ?? \App\Models\Page::query()->where('slug', '403')->with('meta')->first();
  $pageMeta = (isset($meta) && $meta instanceof \App\Support\PageMetaBag)
      ? $meta
      : ($pageModel ? \App\Support\PageMetaBag::for($pageModel) : \App\Support\PageMetaBag::empty());
  $pageTitle = $page?->title ?? $pageModel?->title ?? 'Access Forbidden — Azoogi';
  $pageDescription = $page?->meta_description ?? $pageModel?->meta_description ?? 'Access to this resource is forbidden.';
@endphp

@extends('layouts.site')

@section('title', $pageTitle)

@section('description', $pageDescription)

@section('bodyClass', 'error-page error-403-page')

@section('chrome', 'full')

@section('topbarClass', 'solid')
@section('logo', 'logo_white.png')

@section('content')
<main class="error-main">
  <div class="wrap error-wrap" {!! cms_section_attr('error') !!}>
    <div class="error-card reveal in">
      <span class="error-code"{!! cms_style($pageMeta, 'error.code') !!}>{{ $pageMeta->get('error.code', 0, '403') }}</span>
      <h1 class="error-title"{!! cms_style($pageMeta, 'error.title') !!}>{{ $pageMeta->get('error.title', 0, 'Access Forbidden') }}</h1>
      <p class="error-lead"{!! cms_style($pageMeta, 'error.lead') !!}>
        {{ $pageMeta->get('error.lead', 0, 'You do not have permission to access this page or resource.') }}
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
