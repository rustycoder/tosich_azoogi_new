@extends('layouts.dashboard')

@section('title', 'Settings')

@section('content')
<div class="dash-head">
    <div class="dash-head-title">
        <h1>Settings</h1>
        <div class="dash-head-actions">
            <button class="btn primary" type="submit" form="dash-profile-form">Save settings</button>
        </div>
    </div>
    <p class="dash-lead">Update your profile details and password.</p>
</div>

<form id="dash-profile-form" class="dash-form" method="post" action="{{ route('dashboard.profile.update') }}">
    @csrf
    @method('put')

    <div class="dash-card">
        <h2>Profile</h2>
        <div class="dash-field">
            <label for="name">Name</label>
            <input id="name" name="name" value="{{ old('name', auth()->user()->name) }}" required autocomplete="name">
            @error('name')<p class="login-error">{{ $message }}</p>@enderror
        </div>

        <div class="dash-field">
            <label for="email">Email</label>
            <input id="email" name="email" type="email" value="{{ old('email', auth()->user()->email) }}" required autocomplete="email">
            @error('email')<p class="login-error">{{ $message }}</p>@enderror
        </div>
    </div>

    <div class="dash-card">
        <h2>Password</h2>
        <div class="dash-field">
            <label for="current_password">Current password <small>(required to change password)</small></label>
            <input id="current_password" name="current_password" type="password" autocomplete="current-password">
            @error('current_password')<p class="login-error">{{ $message }}</p>@enderror
        </div>

        <div class="dash-field">
            <label for="password">New password <small>(leave blank to keep)</small></label>
            <input id="password" name="password" type="password" autocomplete="new-password">
            @error('password')<p class="login-error">{{ $message }}</p>@enderror
        </div>

        <div class="dash-field">
            <label for="password_confirmation">Confirm password</label>
            <input id="password_confirmation" name="password_confirmation" type="password" autocomplete="new-password">
            @error('password_confirmation')<p class="login-error">{{ $message }}</p>@enderror
        </div>
    </div>
</form>
@endsection
