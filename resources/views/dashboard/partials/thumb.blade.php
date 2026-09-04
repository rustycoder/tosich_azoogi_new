@php
    $src = trim((string) ($src ?? ''));
    $alt = (string) ($alt ?? '');
@endphp
@if ($src !== '')
    <img class="dash-list-thumb" src="{{ $src }}" alt="{{ $alt }}" loading="lazy">
@else
    <span class="dash-list-thumb is-empty" aria-hidden="true"></span>
@endif
