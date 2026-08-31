<button
    type="button"
    class="dash-pill dash-toggle {{ $on ? $onClass : $offClass }}"
    data-dash-toggle="{{ $url }}"
    data-dash-on-class="{{ $onClass }}"
    data-dash-off-class="{{ $offClass }}"
    aria-pressed="{{ $on ? 'true' : 'false' }}"
>{{ $label }}</button>
