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
    <label>Access</label>
    <p class="dash-access-hint">Choose which enquiry boards and pages this person can manage.</p>
    <div class="dash-access-groups">
        @foreach ($resources as $group)
            @php
                $hasLabeledSections = collect($group['sections'])->contains(fn (array $section): bool => $section['label'] !== '');
            @endphp
            <section class="dash-access-group">
                @if ($hasLabeledSections)
                    <p class="dash-access-group-title">{{ $group['label'] }}</p>
                @endif
                @foreach ($group['sections'] as $section)
                    <div class="dash-access-section" data-access-section>
                        <div class="dash-access-section-head">
                            @if ($section['label'] !== '')
                                <p class="dash-access-section-title">{{ $section['label'] }}</p>
                            @else
                                <p class="dash-access-group-title">{{ $group['label'] }}</p>
                            @endif
                            <button type="button" class="dash-access-toggle" data-access-toggle>Select all</button>
                        </div>
                        <div class="dash-checks">
                            @foreach ($section['resources'] as $resource)
                                <label class="dash-check">
                                    <input type="checkbox" name="resources[]" value="{{ $resource->value }}" @checked(in_array($resource->value, $assigned, true))>
                                    <span>{{ $resource->label() }}</span>
                                </label>
                            @endforeach
                        </div>
                    </div>
                @endforeach
            </section>
        @endforeach
    </div>
</div>
