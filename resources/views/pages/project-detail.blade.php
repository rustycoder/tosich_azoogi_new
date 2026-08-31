@extends('layouts.site')

@section('title')
Project — Azoogi
@endsection

@section('description')
Azoogi project case study — LED lighting solutions for hospitality, residential, medical and commercial spaces.
@endsection

@section('bodyClass', 'projects-page')

@section('bodyAttributes')
data-page="project-detail"
@endsection

@section('chrome', 'full')

@section('topbarClass', 'solid')
@section('logo', 'logo_dark.png')

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/css/projects.css') }}?v={{ config('app.asset_version') }}">
@endpush

@section('content')
<main id="projectDetail">
  <div class="projects-loading">Loading project…</div>
</main>
@endsection

@push('scripts')
<script src="{{ asset('assets/js/projects.js') }}?v={{ config('app.asset_version') }}"></script>
@endpush
