@extends('layouts.site')

@section('title', $page->title)

@section('description', $page->meta_description)

@section('bodyClass', 'contact-page')

@section('chrome', 'full')

@section('topbarClass', '')
@section('logo', 'logo_white.png')

@section('content')
<main class="contact-main">

  <section class="contact-hero" {!! cms_section_attr('hero') !!}>
    <div class="contact-hero-media" aria-hidden="true">
      @if (filled($meta->get('hero.video')))
        <video class="contact-hero-video" autoplay muted loop playsinline preload="auto" poster="{{ media_url($meta->get('hero.poster', 0, '/assets/imgcontact.jpeg')) }}">
          <source src="{{ media_url($meta->get('hero.video')) }}" type="{{ video_mime_type($meta->get('hero.video')) }}">
        </video>
      @elseif (filled($meta->get('hero.poster')))
        <img src="{{ media_url($meta->get('hero.poster', 0, '/assets/imgcontact.jpeg')) }}" alt="" loading="eager" decoding="async">
      @else
        <img src="{{ media_url('/assets/imgcontact.jpeg') }}" alt="" loading="eager" decoding="async">
      @endif
    </div>
    <div class="contact-hero-copy">
      <h1{!! cms_style($meta, 'hero.title') !!}>
        {!! accent_html($meta->get('hero.title', 0, 'Get in {Touch}')) !!}</h1>
      @php
        $contactLead = $meta->get('hero.lead', 0, 'Have a project in mind, need custom LED engineering, or looking for trade support? We’re here to help.');
      @endphp
      @if ($contactLead !== '')
        <p class="contact-hero-lead"{!! cms_style($meta, 'hero.lead') !!}>{!! accent_html($contactLead) !!}</p>
      @endif
    </div>
  </section>

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
        <h2 class="h2 contact-title"{!! cms_style($meta, 'form.title') !!}>{!! accent_html($meta->get('form.title')) !!}</h2>
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
          <x-turnstile action="contact" />
          <div class="form-actions">
            <button type="submit" class="btn primary">Send Message</button>
          </div>
          @if ($errors->any())
            <p class="form-status is-error">{{ $errors->first() }}</p>
          @endif
        </form>
      </div>

    </div>
  </div>
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
  document.querySelectorAll('.reveal, .contact-hero').forEach(el => io.observe(el));
</script>
@endverbatim
@endpush
