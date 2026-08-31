@extends('layouts.site')

@section('title', $page->title)

@section('description', $leads[0] ?? $page->meta_description)

@section('bodyClass', 'audience-page')

@section('chrome', 'full')

@section('topbarClass', 'solid')
@section('logo', 'logo_dark.png')

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/css/audience.css') }}?v={{ config('app.asset_version') }}">
@endpush

@section('content')
<main class="audience-main" id="audienceRoot">
  <section class="audience-hero" {!! cms_section_attr('hero') !!}>
    <div class="wrap">
      @if ($meta->get('hero.eyebrow'))
        <div class="kicker">{{ $meta->get('hero.eyebrow') }}</div>
      @endif
      <h1 class="h2">{!! accent_html($meta->get('hero.title'), $meta->get('hero.title_accent')) !!}</h1>
      @if ($leads)
        <div class="audience-lead">
          @foreach ($leads as $paragraph)
            <p>{!! linkify_emails($paragraph) !!}</p>
          @endforeach
        </div>
      @endif
    </div>
  </section>

  <section class="audience-cards-wrap card-in" {!! cms_section_attr('card') !!}>
    <div class="wrap-sm">
      <ul id="cards" class="audience-cards" style="--numcards: {{ count($cards) }}">
        @foreach ($cards as $card)
          <li class="card-main" id="card_{{ $loop->iteration }}" style="--index: {{ $loop->iteration }}">
            <div class="card__content">
              <div class="card__body">
                <h2>{!! accent_html($card['heading'] ?? '', $card['heading_accent'] ?? '') !!}</h2>
                <div class="card__copy">
                  @foreach (preg_split("/\n\n+/", $card['body'] ?? '') as $paragraph)
                    @if (trim($paragraph) !== '')
                      <p>{!! linkify_emails($paragraph) !!}</p>
                    @endif
                  @endforeach
                </div>
                @if (! empty($card['cta.label']))
                  <div class="card__cta"><a href="{{ $card['cta.href'] ?? '#' }}" class="btn">{{ $card['cta.label'] }}</a></div>
                @endif
              </div>
              <figure>
                <img src="{{ media_url($card['image'] ?? '') }}" alt="{{ $card['heading'] ?? '' }}" loading="lazy">
              </figure>
            </div>
          </li>
        @endforeach
      </ul>
    </div>
  </section>
</main>
@endsection
