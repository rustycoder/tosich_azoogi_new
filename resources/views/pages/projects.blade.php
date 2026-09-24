@extends('layouts.site')

@section('title', $page->title)

@section('description', $page->meta_description)

@section('bodyClass', 'projects-page')

@section('chrome', 'full')

@section('topbarClass', '')
@section('logo', 'logo_white.png')

@push('styles')
<link rel="stylesheet" href="{{ versioned_asset('assets/css/projects.css') }}">
@endpush

@section('content')
<main class="projects-main">
  <section class="projects-hero" {!! cms_section_attr('hero') !!}>
    <div class="projects-hero-media" aria-hidden="true">
      <img src="{{ media_url($meta->get('hero.image')) }}" alt="" loading="eager">
    </div>
    <div class="projects-hero-copy">
      <h1{!! cms_style($meta, 'hero.title') !!}>{!! accent_html($meta->get('hero.title')) !!}</h1>
      <p class="projects-hero-lead"{!! cms_style($meta, 'hero.body') !!}>
        {!! linkify_emails(accent_html($meta->get('hero.body'))) !!}
      </p>
    </div>
  </section>

  <section class="projects-grid-section" {!! cms_section_attr('list') !!}>
    <div class="wrap">
      <div class="projects-count" id="projectsCount"{!! cms_style($meta, 'list.showing') !!}>{{ $meta->get('list.showing') }} {{ $projects->count() }} {{ $projects->count() === 1 ? $meta->get('list.singular') : $meta->get('list.plural') }}</div>
      <div class="projects-grid" id="projectsGrid">
        @foreach ($projects as $project)
          <a class="project-card" href="{{ route('project-detail', ['slug' => $project->slug]) }}">
            <span class="project-card-media">
              <img src="{{ $project->coverUrl() }}" alt="{{ $project->title }}" loading="lazy">
            </span>
            <span class="project-card-cap">
              <small class="project-tag">{{ $project->tag ?: $project->type ?: $meta->get('list.fallback_tag') }}</small>
              <h3>{{ $project->title }}</h3>
            </span>
          </a>
        @endforeach
      </div>
    </div>
  </section>
</main>
@endsection

@push('scripts')
@verbatim
<script>
  const topbar = document.getElementById('topbar');
  let lastScrolled = null;

  const onScroll = () => {
    const isScrolled = window.scrollY > 40;
    if (isScrolled !== lastScrolled) {
      topbar?.classList.toggle('solid', isScrolled);
      lastScrolled = isScrolled;
      if (typeof updateLogos === 'function') updateLogos();
    }
  };
  window.addEventListener('scroll', onScroll, { passive: true });
  onScroll();

  const io = new IntersectionObserver((entries) => {
    entries.forEach(entry => {
      if (entry.isIntersecting) {
        entry.target.classList.add('in');
        io.unobserve(entry.target);
      }
    });
  }, { threshold: 0.12 });
  document.querySelectorAll('.reveal, .projects-hero').forEach(el => io.observe(el));
</script>
@endverbatim
@endpush
