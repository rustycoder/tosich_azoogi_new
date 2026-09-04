@extends('layouts.site')

@section('title')
Request a Quote — Azoogi
@endsection

@section('description')
Review the products in your quote list and send a request to the Azoogi trade team.
@endsection

@section('bodyClass', 'quote-request-page')

@section('chrome', 'full')

@section('topbarClass', 'solid')
@section('logo', 'logo_dark.png')

@section('content')
<main class="quote-page">
  <div class="wrap quote-page-wrap">
    <div class="quote-page-intro">
      <div class="quote-page-kicker">Trade quote</div>
      <h1>Get A Quote For Your Project</h1>
      <p>Looking for tailored lighting solutions for your next project? Whether you're an architect, builder, designer or wholesaler, our team is here to help. Simply tell us what you need — and we'll provide a fast, accurate quote with expert support every step of the way.</p>
    </div>

    <div class="quote-page-grid">
      <section class="quote-page-list" aria-labelledby="quote-products-title">
        <h2 id="quote-products-title">Products in this quote</h2>
        <div data-quote-list="page"></div>
      </section>

      <section class="quote-page-form" aria-labelledby="quote-form-title">
        <h2 id="quote-form-title">Request details</h2>
        @include('partials.quote-request-form')
      </section>
    </div>
  </div>
</main>
@endsection
