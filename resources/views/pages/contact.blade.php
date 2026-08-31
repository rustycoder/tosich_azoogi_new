@extends('layouts.site')

@section('title')
Contact Us — Azoogi
@endsection

@section('description')
Get in touch with Azoogi. Office hours, address, and contact form for lighting projects across Australia.
@endsection

@section('bodyClass', 'contact-page')

@section('chrome', 'full')

@section('topbarClass', 'solid')
@section('logo', 'logo_dark.png')

@section('content')
<main class="contact-main">
  <div class="wrap contact-wrap">
    <div class="contact-grid">

      <aside class="contact-info-panel">
        <div class="info-block">
          <div class="info-label">Office Hours</div>
          <p>08:00<span>AM</span> – 04:00<span>PM</span><br>Monday To Friday</p>
        </div>
        <div class="info-block">
          <div class="info-label">Address</div>
          <p>
            <a href="https://www.google.com/maps/place/Azoogi+LED+Lighting/@-33.9654395,151.2254676,17z" target="_blank" rel="noopener noreferrer">
              Unit 47/10-12 Girawah Pl<br>Matraville NSW 2036
            </a>
          </p>
        </div>
        <div class="info-block">
          <div class="info-label">Office Number</div>
          <p><a href="tel:1300641261">1300 641 261</a></p>
        </div>
        <div class="info-block">
          <div class="info-label">ABN</div>
          <p>72 600 241 209</p>
        </div>
        <div class="info-block">
          <div class="info-label">ACN</div>
          <p>600 241 209</p>
        </div>

        <div class="contact-international">
          <p><strong class="green-title">Inquiring from outside Australia?</strong></p>
          <p>We regularly partner with architects, designers, developers, and trade contractors across the Asia-Pacific, Indian Ocean, and beyond. Our team is fully experienced in managing international logistics, cross-border time zones, and ensuring all products comply with local electrical, safety, and governance standards.</p>
          <p>For international project inquiries, connect with our export team at <a href="mailto:exports@azoogi.com">exports@azoogi.com</a> or call <a href="tel:+61279123524">+61 2 7912 3524</a> (or <a href="tel:1300641261">1300 641 261</a> within Australia).</p>
        </div>
      </aside>

      <div class="contact-grid-gap" aria-hidden="true"></div>

      <div class="contact-form-panel">
        <div class="kicker">Contact</div>
        <h1 class="h2 contact-title">We’d love to <span>hear</span> from you!</h1>
        <p class="contact-lead">Use the form below.</p>

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
