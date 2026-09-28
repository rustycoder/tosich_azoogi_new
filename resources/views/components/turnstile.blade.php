@props([
    'action' => null,
    'theme' => 'auto',
    'siteKey' => config('services.turnstile.site_key'),
    'id' => null,
])

@if ($siteKey)
  <div class="turnstile-wrapper" style="margin: 12px 0 18px;">
    <div
      class="cf-turnstile"
      data-sitekey="{{ $siteKey }}"
      data-theme="{{ $theme }}"
      @if($action) data-action="{{ $action }}" @endif
      @if($id) id="{{ $id }}" @endif
    ></div>
    @error('cf-turnstile-response')
      <p class="form-status is-error" style="margin-top: 6px;">{{ $message }}</p>
    @enderror
  </div>
@endif
