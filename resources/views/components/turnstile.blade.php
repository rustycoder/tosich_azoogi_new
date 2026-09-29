@props([
    'action' => null,
    'theme' => null,
    'siteKey' => config('services.turnstile.site_key'),
    'id' => null,
])

@if ($siteKey)
  <div class="turnstile-wrapper">
    <div
      class="cf-turnstile"
      data-sitekey="{{ $siteKey }}"
      @if($theme) data-theme="{{ $theme }}" @endif
      @if($action) data-action="{{ $action }}" @endif
      @if($id) id="{{ $id }}" @endif
    ></div>
    @error('cf-turnstile-response')
      <p class="form-status is-error" style="margin-top: 6px;">{{ $message }}</p>
    @enderror
  </div>
@endif
