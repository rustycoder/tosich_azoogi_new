@extends('layouts.site')

@section('title', $page->title)

@section('description', $page->meta_description)

@section('bodyClass', 'contact-page')

@section('chrome', 'full')

@section('topbarClass', 'solid')
@section('logo', 'logo_dark.png')

@section('content')
<main class="contact-main">
  <div class="wrap contact-wrap">
    <div class="contact-grid">

      <aside class="contact-info-panel">
        <div class="info-block" {!! cms_section_attr('hours') !!}>
          <div class="info-label"{!! cms_style($meta, 'hours.label') !!}>{{ $meta->get('hours.label') }}</div>
          <p{!! cms_style($meta, 'hours.value') !!}>{!! nl2br_html($meta->get('hours.value')) !!}</p>
        </div>
        <div class="info-block" {!! cms_section_attr('address') !!}>
          <div class="info-label"{!! cms_style($meta, 'address.label') !!}>{{ $meta->get('address.label') }}</div>
          <p>
            <a href="{{ $meta->get('address.maps_url') }}" target="_blank" rel="noopener noreferrer"{!! cms_style($meta, 'address.value') !!}>
              {!! nl2br_html($meta->get('address.value')) !!}
            </a>
          </p>
        </div>
        <div class="info-block" {!! cms_section_attr('phone') !!}>
          <div class="info-label"{!! cms_style($meta, 'phone.label') !!}>{{ $meta->get('phone.label') }}</div>
          <p{!! cms_style($meta, 'phone.value') !!}><a href="tel:{{ preg_replace('/\s+/', '', $meta->get('phone.value')) }}">{{ $meta->get('phone.value') }}</a></p>
        </div>
        <div class="info-block" {!! cms_section_attr('abn') !!}>
          <div class="info-label"{!! cms_style($meta, 'abn.label') !!}>{{ $meta->get('abn.label') }}</div>
          <p{!! cms_style($meta, 'abn.value') !!}>{{ $meta->get('abn.value') }}</p>
        </div>
        <div class="info-block" {!! cms_section_attr('acn') !!}>
          <div class="info-label"{!! cms_style($meta, 'acn.label') !!}>{{ $meta->get('acn.label') }}</div>
          <p{!! cms_style($meta, 'acn.value') !!}>{{ $meta->get('acn.value') }}</p>
        </div>

        <div class="contact-international" {!! cms_section_attr('intl') !!}>
          <p><strong class="green-title"{!! cms_style($meta, 'intl.heading') !!}>{{ $meta->get('intl.heading') }}</strong></p>
          <p{!! cms_style($meta, 'intl.body') !!}>{{ $meta->get('intl.body') }}</p>
          <p>For international project inquiries, connect with our export team at <a href="mailto:{{ $meta->get('intl.email') }}">{{ $meta->get('intl.email') }}</a> or call <a href="tel:{{ preg_replace('/\s+/', '', $meta->get('intl.phone')) }}">{{ $meta->get('intl.phone') }}</a> (or <a href="tel:{{ preg_replace('/\s+/', '', $meta->get('phone.value')) }}">{{ $meta->get('phone.value') }}</a> within Australia).</p>
        </div>
      </aside>

      <div class="contact-grid-gap" aria-hidden="true"></div>

      <div class="contact-form-panel" {!! cms_section_attr('form') !!}>
        <div class="kicker"{!! cms_style($meta, 'form.kicker') !!}>{{ $meta->get('form.kicker') }}</div>
        <h1 class="h2 contact-title"{!! cms_style($meta, 'form.title') !!}>{!! accent_html($meta->get('form.title'), 'hear') !!}</h1>
        <p class="contact-lead"{!! cms_style($meta, 'form.lead') !!}>{{ $meta->get('form.lead') }}</p>

        <form class="contact-form" id="contactForm" action="{{ route('contact.submit') }}" method="post" novalidate>
          @csrf
          <div class="form-group">
            <label for="your-name">Full Name*</label>
            <input id="your-name" name="your-name" type="text" maxlength="400" required autocomplete="name" value="{{ old('your-name') }}">
          </div>
          <div class="form-group">
            <label for="your-email">Email*</label>
            <input id="your-email" name="your-email" type="email" maxlength="400" required autocomplete="email" value="{{ old('your-email') }}">
          </div>
          <div class="form-group">
            <label for="your-company">Company Name*</label>
            <input id="your-company" name="your-company" type="text" maxlength="400" required autocomplete="organization" value="{{ old('your-company') }}">
          </div>
          <div class="form-group">
            <label for="your-message">Message*</label>
            <textarea id="your-message" name="your-message" rows="6" maxlength="2000" required placeholder="We’re here to help genuine customers and potential partners. Please, no unsolicited sales pitches.">{{ old('your-message') }}</textarea>
          </div>
          <div class="form-actions">
            <button type="submit" class="btn primary">Send Message</button>
          </div>
          @if ($errors->any())
            <p class="form-status is-error">{{ $errors->first() }}</p>
          @elseif (session('status'))
            <p class="form-status is-success">{{ session('status') }}</p>
          @endif
        </form>
      </div>

    </div>
  </div>
</main>
@endsection

@push('scripts')
<script>
  document.getElementById('topbar').classList.add('solid');
</script>
@endpush
