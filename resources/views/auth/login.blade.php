@extends('layouts.site')

@section('title')
Login — Azoogi
@endsection

@section('description')
Sign in to your Azoogi account.
@endsection

@section('bodyClass', 'login-page')
@section('chrome', 'none')

@push('styles')
@verbatim
<style>
html, body {
    height: 100%;
    overflow-x: hidden;
    overflow-y: auto;
}

.login-screen {
    min-height: 100vh;
    min-height: 100dvh;
    display: grid;
    place-items: center;
    padding: 28px;
    position: relative;
    overflow: hidden;
    background: var(--bg);
}

.login-screen::before {
    content: "";
    position: absolute;
    inset: 0;
    background-image: url('/assets/hero02.jpg');
    background-size: cover;
    background-position: center;
    opacity: .08;
    pointer-events: none;
}

.login-inner {
    position: relative;
    z-index: 1;
    width: min(440px, 100%);
    display: flex;
    flex-direction: column;
    align-items: center;
}

.login-inner .brand {
    margin-bottom: 36px;
}

.login-inner .brand img {
    width: clamp(160px, 36vw, 240px);
    height: auto;
    display: block;
}

.login-card {
    width: 100%;
    background: #fff;
    border: 1px solid var(--line);
    padding: 36px 32px 32px;
}

.login-card .kicker {
    margin-bottom: 10px;
}

.login-card .h2 {
    font-size: clamp(28px, 4vw, 36px);
    margin: 0 0 28px;
}

.login-form {
    display: grid;
    gap: 16px;
}

.login-form .form-group {
    display: grid;
    gap: 8px;
}

.login-form label {
    font-size: 12px;
    letter-spacing: .08em;
    text-transform: uppercase;
    font-weight: 500;
    color: var(--ink);
}

.login-form input[type="email"],
.login-form input[type="password"] {
    width: 100%;
    border: 1px solid var(--line);
    background: var(--bg);
    color: var(--ink);
    padding: 14px 16px;
    font: inherit;
    font-size: 15px;
    border-radius: 4px;
    outline: none;
    transition: border-color .25s ease, box-shadow .25s ease;
}

.login-form input:focus {
    border-color: var(--accent);
    box-shadow: 0 0 0 3px rgba(140, 198, 63, 0.2);
}

.login-form .remember {
    display: flex;
    align-items: center;
    gap: 10px;
    font-size: 14px;
    color: var(--muted);
    text-transform: none;
    letter-spacing: 0;
}

.login-form .remember input {
    accent-color: var(--accent);
}

.login-form .btn.primary {
    width: 100%;
    margin-top: 8px;
    justify-content: center;
}

.login-error {
    font-size: 13px;
    color: #c0392b;
    margin: 0;
}

@media (max-width: 640px) {
    .login-screen {
        padding: 20px;
    }

    .login-card {
        padding: 28px 22px 24px;
    }
}
</style>
@endverbatim
@endpush

@section('content')
<section class="login-screen">
    <div class="login-inner">
        <div class="brand" aria-label="Azoogi">
            <img src="{{ asset('assets/logo_dark.png') }}" alt="Azoogi">
        </div>

        <div class="login-card">
            <div class="kicker">Account</div>
            <h1 class="h2">Sign in</h1>

            <form class="login-form" method="post" action="{{ route('login.store') }}" novalidate>
                @csrf
                <div class="form-group">
                    <label for="email">Email</label>
                    <input id="email" name="email" type="email" value="{{ old('email') }}" required autocomplete="username" autofocus>
                    @error('email')
                        <p class="login-error">{{ $message }}</p>
                    @enderror
                </div>
                <div class="form-group">
                    <label for="password">Password</label>
                    <input id="password" name="password" type="password" required autocomplete="current-password">
                    @error('password')
                        <p class="login-error">{{ $message }}</p>
                    @enderror
                </div>
                <label class="remember">
                    <input type="checkbox" name="remember">
                    Remember me
                </label>
                <button type="submit" class="btn primary">Sign in</button>
            </form>
        </div>
    </div>
</section>
@endsection
