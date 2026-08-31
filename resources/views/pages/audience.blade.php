@extends('layouts.site')

@section('title')
Azoogi — Solutions
@endsection

@section('description')
Azoogi LED lighting solutions for architects, designers, electricians, builders, wholesalers and homeowners.
@endsection

@section('bodyClass', 'audience-page')

@section('chrome', 'full')

@section('topbarClass', 'solid')
@section('logo', 'logo_dark.png')

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/css/audience.css') }}?v={{ config('app.asset_version') }}">
@endpush

@section('content')
<main class="audience-main" id="audienceRoot">
  <div class="audience-loading">Loading…</div>
</main>
@endsection

@push('scripts')
<script src="{{ asset('assets/js/audience.js') }}?v={{ config('app.asset_version') }}"></script>
@endpush
