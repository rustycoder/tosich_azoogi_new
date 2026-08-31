@extends('layouts.site')

@section('title', $page->title)

@section('description', $page->meta_description)

@section('bodyClass', 'legal-page')

@section('chrome', 'full')

@section('topbarClass', 'solid')
@section('logo', 'logo_dark.png')

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/css/legal.v-1.2.css') }}?v={{ config('app.asset_version') }}">
@endpush

@section('content')
<main class="legal-main">
    <div class="wrap legal-page-wrap" {!! cms_section_attr('legal') !!}>
    <div class="legal-hero">
      <div class="kicker legal-kicker">{{ $meta->get('legal.kicker') }}</div>
      <h1 class="h2 legal-title">{{ $meta->get('legal.title') }}</h1>
      <p class="legal-intro">{{ $meta->get('legal.lead') }}</p>
    </div>

    <div class="legal-shell">
      <nav class="legal-nav" aria-label="Policy pages">
        <a href="{{ route('privacy') }}" class="{{ $page->slug === 'privacy' ? 'is-active' : '' }}">Privacy</a>
        <a href="{{ route('terms') }}" class="{{ $page->slug === 'terms' ? 'is-active' : '' }}">Terms</a>
        <a href="{{ route('warranty-returns') }}" class="{{ $page->slug === 'warranty-returns' ? 'is-active' : '' }}">Warranty &amp; Returns</a>
        <a href="{{ route('modern-slavery') }}" class="{{ $page->slug === 'modern-slavery' ? 'is-active' : '' }}">Modern Slavery</a>
      </nav>

      <div class="legal-wrap">
        <article class="legal-block" id="{{ $page->slug }}">
          <h2 class="legal-block-title">{{ $meta->get('legal.title') }}</h2>
          <p class="legal-block-lead">{{ $meta->get('legal.lead') }}</p>
          <div class="legal-body">
            {!! $meta->get('legal.html') !!}
          </div>
        </article>
      </div>
    </div>
  </div>
</main>
@endsection

@push('scripts')
<script>
  document.getElementById('topbar')?.classList.add('solid');
</script>
@endpush
