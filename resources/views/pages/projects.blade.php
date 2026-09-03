@extends('layouts.site')

@section('title')
Projects — Azoogi
@endsection

@section('description')
Explore Azoogi LED lighting projects across hospitality, residential, medical, industrial and commercial spaces.
@endsection

@section('bodyClass', 'projects-page')

@section('chrome', 'full')

@section('topbarClass', 'solid')
@section('logo', 'logo_dark.png')

@push('styles')
<link rel="stylesheet" href="{{ versioned_asset('assets/css/projects.css') }}">
@endpush

@section('content')
<section class="projects-hero">
  <div class="wrap" id="projectsIntro">
    <h1 class="h2"><span>Projects Powered by Azoogi</span></h1>
    <p class="projects-hero-lead">
      From a new strip light in your kitchen to landmark Tier-1 developments — we deliver LED lighting solutions for projects of all sizes. Whether it’s a heritage restoration, boutique hospitality venue, residential upgrade, or a large-scale commercial build, our in-house engineering and assembly line ensure precision, speed, efficiency and quality — no matter the scale.
      For a copy of our capability statement, contact us at
      <a href="mailto:majorprojects@azoogi.com">majorprojects@azoogi.com</a>.
    </p>
  </div>
</section>

<section class="projects-highlights">
  <div class="wrap">
    <div class="section-head">
      <h2>Recent <span>Highlights</span></h2>
    </div>
    <div class="highlights-grid" id="highlightsGrid">
      @foreach ($highlights as $project)
        <a class="highlight-card" href="{{ route('project-detail', ['slug' => $project->slug]) }}">
          <img src="{{ $project->coverUrl() }}" alt="{{ $project->title }}" loading="lazy">
          <div class="cap">
            <small>{{ $project->tag ?: $project->type }}@if ($project->location) — {{ $project->location }}@endif</small>
            <h3>{{ $project->title }}</h3>
          </div>
        </a>
      @endforeach
    </div>
  </div>
</section>

<section class="projects-grid-section">
  <div class="wrap">
    <div class="projects-count" id="projectsCount">Showing {{ $projects->count() }} project{{ $projects->count() === 1 ? '' : 's' }}</div>
    <div class="projects-grid" id="projectsGrid">
      @foreach ($projects as $project)
        <a class="project-card" href="{{ route('project-detail', ['slug' => $project->slug]) }}">
          <div class="project-card-media">
            <img src="{{ $project->coverUrl() }}" alt="{{ $project->title }}" loading="lazy">
          </div>
          <div class="project-card-body">
            <span class="project-tag">{{ $project->tag ?: $project->type ?: 'Project' }}</span>
            <h3>{{ $project->title }}</h3>
          </div>
        </a>
      @endforeach
    </div>
  </div>
</section>
@endsection
