<div class="dash-field">
    <label>Name</label>
    <input name="name" value="{{ old('name', $staff->name ?? '') }}" required>
    @error('name')<p class="login-error">{{ $message }}</p>@enderror
</div>

<div class="dash-field">
    <label>Email</label>
    <input name="email" type="email" value="{{ old('email', $staff->email ?? '') }}" required>
    @error('email')<p class="login-error">{{ $message }}</p>@enderror
</div>

<div class="dash-field">
    <label for="password">Password @if($staff)<small>(leave blank to keep)</small>@endif</label>
    <input id="password" name="password" type="password" autocomplete="new-password" @unless($staff) required @endunless>
    @error('password')<p class="login-error">{{ $message }}</p>@enderror
</div>

<div class="dash-field">
    <label for="password_confirmation">Confirm password @if($staff)<small>(required if changing password)</small>@endif</label>
    <input id="password_confirmation" name="password_confirmation" type="password" autocomplete="new-password" @unless($staff) required @endunless>
    @error('password_confirmation')<p class="login-error">{{ $message }}</p>@enderror
</div>

<div class="dash-field">
    <label>Content access</label>
    <div class="dash-checks">
        @foreach ($resources as $resource)
            <label class="dash-check">
                <input type="checkbox" name="resources[]" value="{{ $resource->value }}" @checked(in_array($resource->value, $assigned, true))>
                {{ $resource->label() }}
            </label>
        @endforeach
    </div>
</div>
