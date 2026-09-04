@extends('layouts.site')

@section('title', $page->title)

@section('description', $page->meta_description)

@section('bodyClass', 'quote-request-page')

@section('chrome', 'full')

@section('topbarClass', 'solid')
@section('logo', 'logo_dark.png')

@section('content')
<main class="quote-page">
  <div class="wrap quote-page-wrap">
    <div class="quote-page-intro" {!! cms_section_attr('intro') !!}>
      <div class="quote-page-kicker"{!! cms_style($meta, 'intro.kicker') !!}>{{ $meta->get('intro.kicker') }}</div>
      <h1{!! cms_style($meta, 'intro.title') !!}>{{ $meta->get('intro.title') }}</h1>
      <p{!! cms_style($meta, 'intro.body') !!}>{{ $meta->get('intro.body') }}</p>
    </div>

    <div class="quote-page-grid">
      <section class="quote-page-form" aria-labelledby="quote-form-title" {!! cms_section_attr('form') !!}>
        <h2 id="quote-form-title"{!! cms_style($meta, 'form.title') !!}>{{ $meta->get('form.title') }}</h2>
        @include('partials.quote-request-form')
      </section>

      <section class="quote-page-list" aria-labelledby="quote-products-title" {!! cms_section_attr('list') !!}>
        <h2 id="quote-products-title"{!! cms_style($meta, 'list.title') !!}>{{ $meta->get('list.title') }}</h2>
        <div data-quote-list="page"></div>
      </section>
    </div>
  </div>
</main>
@endsection
