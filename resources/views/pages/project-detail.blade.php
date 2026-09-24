@extends('layouts.site')

@section('title', $project->title.' — Azoogi Projects')

@section('description', $project->summary ?: $project->description)

@section('bodyClass', 'projects-page')

@section('chrome', 'full')

@section('topbarClass', 'solid')
@section('logo', 'logo_white.png')

@push('styles')
<link rel="stylesheet" href="{{ versioned_asset('assets/css/projects.css') }}">
@endpush

@section('content')
@php
    $cover = $project->cover ?: $project->cover_remote;
    $gallery = array_values(array_filter(
        $project->gallery ?: [],
        fn ($image) => $image !== $cover && $image !== $project->cover_remote,
    ));
    $gallery = array_slice($gallery, 0, 6);
@endphp
<main id="projectDetail">
  <section class="project-detail-hero">
    <div class="project-detail-hero-media" aria-hidden="true">
      <img src="{{ $project->coverUrl() }}" alt="">
    </div>
    <div class="project-detail-hero-copy">
      <a class="project-back" href="{{ route('projects') }}"{!! cms_style($meta, 'detail.back') !!}>
        <svg class="project-back-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M15 6 9 12l6 6"/></svg>
        {{ $meta->get('detail.back') }}
      </a>
      <div class="project-detail-hero-tags">
        @if ($project->location)
          <span class="project-tag">
            <svg class="project-tag-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 21s7-5.2 7-11a7 7 0 1 0-14 0c0 5.8 7 11 7 11z"/><circle cx="12" cy="10" r="2.2"/></svg>
            {{ $project->location }}
          </span>
        @endif
        @if ($project->type ?: $project->tag)
          <span class="project-tag">
            <svg class="project-tag-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M4 21V8l8-4 8 4v13"/><path d="M9 21v-6h6v6"/><path d="M9 10h.01M15 10h.01M9 14h.01M15 14h.01"/></svg>
            {{ $project->type ?: $project->tag }}
          </span>
        @endif
        @if ($project->completed)
          <span class="project-tag">
            <svg class="project-tag-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="3" y="5" width="18" height="16" rx="2"/><path d="M3 10h18M8 3v4M16 3v4"/></svg>
            {{ $project->completed }}
          </span>
        @endif
      </div>
      <h1>{!! accent_html($project->title) !!}</h1>
    </div>
  </section>

  <section class="project-info{{ $gallery === [] ? ' project-info--last' : '' }}" {!! cms_section_attr('detail') !!}>
    <div class="wrap">
      <h2{!! cms_style($meta, 'detail.overview') !!}>{{ $meta->get('detail.overview') }}</h2>
      <div class="project-description">
        <p>{{ $project->description ?: $project->summary }}</p>
      </div>
    </div>
  </section>

  @if ($gallery !== [])
    <section class="project-gallery-section">
      <div class="wrap">
        <div class="project-gallery">
          @foreach ($gallery as $image)
            <div class="image">
              <img src="{{ media_url($image) }}" alt="{{ $project->title }}" loading="lazy">
            </div>
          @endforeach
        </div>
      </div>
    </section>
  @endif
</main>
@endsection
