@extends('layouts.site')

@section('title')
Projects — Azoogi
@endsection

@section('description')
Explore Azoogi LED lighting projects across hospitality, residential, medical, industrial and commercial spaces.
@endsection

@section('bodyClass', 'projects-page')

@section('bodyAttributes')
data-page="projects-list"
@endsection

@section('chrome', 'full')

@section('topbarClass', 'solid')
@section('logo', 'logo_dark.png')

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/css/projects.css') }}?v={{ config('app.asset_version') }}">
@endpush

@section('content')
<section class="projects-hero">
  <div class="wrap" id="projectsIntro">
    <div class="projects-loading">Loading projects…</div>
  </div>
</section>

<section class="projects-highlights">
  <div class="wrap">
    <div class="section-head">
      <h2>Recent <span>Highlights</span></h2>
    </div>
    <div class="highlights-grid" id="highlightsGrid"></div>
  </div>
</section>

<section class="projects-grid-section">
  <div class="wrap">
    <div class="projects-count" id="projectsCount"></div>
    <div class="projects-grid" id="projectsGrid"></div>
  </div>
</section>
@endsection

@push('scripts')
<script src="{{ asset('assets/js/projects.js') }}?v={{ config('app.asset_version') }}"></script>
@endpush
