@extends('layouts.site')

@section('title', $page->title)

@section('description', $page->meta_description)

@section('bodyClass', 'projects-page')

@section('chrome', 'full')

@section('topbarClass', 'solid')
@section('logo', 'logo_dark.png')

@push('styles')
<link rel="stylesheet" href="{{ versioned_asset('assets/css/projects.css') }}">
@endpush

@section('content')
<section class="projects-hero" {!! cms_section_attr('hero') !!}>
  <div class="wrap" id="projectsIntro">
    <h1 class="h2"{!! cms_style($meta, 'hero.title') !!}><span>{{ $meta->get('hero.title') }}</span></h1>
    <p class="projects-hero-lead"{!! cms_style($meta, 'hero.body') !!}>
      {!! nl2br(linkify_emails($meta->get('hero.body')), false) !!}
    </p>
  </div>
</section>

<section class="projects-highlights" {!! cms_section_attr('highlights') !!}>
  <div class="wrap">
    <div class="section-head">
      <h2{!! cms_style($meta, 'highlights.heading') !!}>{!! accent_html($meta->get('highlights.heading'), $meta->get('highlights.heading_accent')) !!}</h2>
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

<section class="projects-grid-section" {!! cms_section_attr('list') !!}>
  <div class="wrap">
    <div class="projects-count" id="projectsCount"{!! cms_style($meta, 'list.showing') !!}>{{ $meta->get('list.showing') }} {{ $projects->count() }} {{ $projects->count() === 1 ? $meta->get('list.singular') : $meta->get('list.plural') }}</div>
    <div class="projects-grid" id="projectsGrid">
      @foreach ($projects as $project)
        <a class="project-card" href="{{ route('project-detail', ['slug' => $project->slug]) }}">
          <div class="project-card-media">
            <img src="{{ $project->coverUrl() }}" alt="{{ $project->title }}" loading="lazy">
          </div>
          <div class="project-card-body">
            <span class="project-tag">{{ $project->tag ?: $project->type ?: $meta->get('list.fallback_tag') }}</span>
            <h3>{{ $project->title }}</h3>
          </div>
        </a>
      @endforeach
    </div>
  </div>
</section>
@endsection
