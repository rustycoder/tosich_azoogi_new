@extends('layouts.site')

@section('title')
Trade Login — Coming Soon | Azoogi
@endsection

@section('description')
The Azoogi trade portal is coming soon. Exclusive pricing, project tools and account access for trade partners.
@endsection

@section('chrome', 'none')

@push('styles')
@verbatim
<style>
html, body {
    height: 100%;
    overflow-x: hidden;
    overflow-y: auto;
  }

  .coming-soon {
    min-height: 100vh;
    min-height: 100dvh;
    display: grid;
    place-items: center;
    padding: 28px;
    position: relative;
    overflow: hidden;
    background: var(--bg);
  }

  .coming-soon::before {
    content: "";
    position: absolute;
    inset: 0;
    background-image: url('/assets/hero02.jpg');
    background-size: cover;
    background-position: center;
    opacity: .08;
    pointer-events: none;
  }

  .coming-soon-inner {
    position: relative;
    z-index: 1;
    width: min(720px, 100%);
    text-align: center;
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 0;
  }

  .coming-soon .brand {
    display: block;
    margin-bottom: 48px;
    opacity: 0;
    transform: translateY(24px);
    animation: cs-in .9s ease forwards;
  }

  .coming-soon .brand img {
    width: clamp(220px, 42vw, 420px);
    height: auto;
    display: block;
  }

  .coming-soon .kicker {
    margin-bottom: 16px;
    opacity: 0;
    transform: translateY(24px);
    animation: cs-in .9s ease .12s forwards;
  }

  .coming-soon .h2 {
    font-size: clamp(42px, 7vw, 72px);
    margin: 0 0 18px;
    opacity: 0;
    transform: translateY(24px);
    animation: cs-in .9s ease .22s forwards;
  }

  .coming-soon .lead {
    margin: 0 auto 36px;
    max-width: 480px;
    font-size: 16px;
    line-height: 1.65;
    opacity: 0;
    transform: translateY(24px);
    animation: cs-in .9s ease .32s forwards;
  }

  .coming-soon .actions {
    display: flex;
    flex-wrap: wrap;
    justify-content: center;
    gap: 14px;
    opacity: 0;
    transform: translateY(24px);
    animation: cs-in .9s ease .42s forwards;
  }

  .coming-soon .meta {
    margin-top: 56px;
    font-size: 12px;
    letter-spacing: .12em;
    text-transform: uppercase;
    color: var(--muted);
    opacity: 0;
    animation: cs-in .9s ease .55s forwards;
  }

  .coming-soon .meta a {
    color: var(--ink);
    border-bottom: 1px solid transparent;
    transition: color .3s, border-color .3s;
  }

  .coming-soon .meta a:hover {
    color: var(--accent);
    border-bottom-color: var(--accent);
  }

  .coming-soon .back {
    position: absolute;
    top: 28px;
    left: 28px;
    z-index: 2;
    font-size: 12px;
    letter-spacing: .18em;
    text-transform: uppercase;
    color: var(--muted);
    display: inline-flex;
    align-items: center;
    gap: 8px;
    transition: color .3s, gap .3s;
  }

  .coming-soon .back:hover {
    color: var(--accent);
    gap: 12px;
  }

  @keyframes cs-in {
    to {
      opacity: 1;
      transform: none;
    }
  }

  @media (max-width: 640px) {
    .coming-soon {
      padding: 20px;
    }

    .coming-soon .back {
      top: 20px;
      left: 20px;
    }

    .coming-soon .brand {
      margin-bottom: 36px;
    }

    .coming-soon .meta {
      margin-top: 40px;
    }
  }
</style>
@endverbatim
@endpush

@section('content')
<section class="coming-soon">
  <a class="back" href="/">&larr; Back to home</a>

  <div class="coming-soon-inner">
    <a href="/" class="brand" aria-label="Azoogi home">
      <img src="/assets/logo_dark.png" alt="Azoogi">
    </a>

    <div class="kicker">Trade Portal</div>
    <h1 class="h2">Coming Soon</h1>
    <p class="lead">Exclusive trade pricing, project tools and account access are on the way. We’re finishing the last details for our partners.</p>

    <div class="actions">
      <a class="btn primary" href="/contact">Talk to a Specialist</a>
      <a class="btn" href="/">Explore Azoogi</a>
    </div>

    <div class="meta">
      Need help now? <a href="tel:1300641261">1300 641 261</a>
      &nbsp;&middot;&nbsp;
      <a href="mailto:sales@azoogi.com">sales@azoogi.com</a>
      &nbsp;&middot;&nbsp;
      <a href="/policies#privacy">Privacy</a>
      &nbsp;&middot;&nbsp;
      <a href="/policies#terms">Terms</a>
      &nbsp;&middot;&nbsp;
      <a href="/policies#warranty">Warranty</a>
      &nbsp;&middot;&nbsp;
      <a href="/policies#modern-slavery">Modern Slavery</a>
    </div>
  </div>
</section>
@endsection
