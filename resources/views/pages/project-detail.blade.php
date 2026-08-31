@extends('layouts.site')

@section('title', $project->title.' — Azoogi Projects')

@section('description', $project->summary ?: $project->description)

@section('bodyClass', 'projects-page')

@section('chrome', 'full')

@section('topbarClass', 'solid')
@section('logo', 'logo_dark.png')

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/css/projects.css') }}?v={{ config('app.asset_version') }}">
@endpush

@section('content')
@php
    $gallery = $project->gallery ?: [];
    if ($gallery === []) {
        $cover = $project->cover ?: $project->cover_remote;
        if ($cover) {
            $gallery = [$cover];
        }
    }
    $gallery = array_slice($gallery, 0, 6);
@endphp
<main id="projectDetail">
  <section class="project-detail-hero">
    <div class="wrap">
      <a class="project-back" href="{{ route('projects') }}">&larr; All Projects</a>
      <div class="meta-bar">
        <span class="project-tag">{{ $project->tag ?: $project->type }}</span>
        @if ($project->location)
          <span class="meta-location">{{ $project->location }}</span>
        @endif
      </div>
      <h1>{{ $project->title }}</h1>
      <div class="cover" style="margin-top:24px">
        <img src="{{ $project->coverUrl() }}" alt="{{ $project->title }}">
      </div>
    </div>
  </section>

  <section class="project-info">
    <div class="wrap">
      <div class="project-info-grid">
        <div>
          <h2>Project Overview</h2>
          <div class="project-meta-rows">
            @if ($project->location)
              <div class="project-meta-row"><span class="meta-label">Location:</span><span class="meta-value">{{ $project->location }}</span></div>
            @endif
            @if ($project->type)
              <div class="project-meta-row"><span class="meta-label">Type:</span><span class="meta-value">{{ $project->type }}</span></div>
            @endif
            @if ($project->completed)
              <div class="project-meta-row"><span class="meta-label">Completed:</span><span class="meta-value">{{ $project->completed }}</span></div>
            @endif
          </div>
        </div>
        <div class="project-description">
          <p>{{ $project->description ?: $project->summary }}</p>
        </div>
      </div>
      <div class="project-gallery">
        @foreach ($gallery as $image)
          <div class="image">
            <img src="{{ media_url($image) }}" alt="{{ $project->title }}" loading="lazy">
          </div>
        @endforeach
      </div>
    </div>
  </section>
</main>
@endsection
