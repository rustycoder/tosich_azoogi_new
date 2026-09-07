@php
    $width = 11 + (strlen((string) $point['value']) * 5);
    $height = 11;
    $rectX = $point['x'] - ($width / 2);
    $rectY = $above ? max(1, $point['y'] - 16) : min(214, $point['y'] + 5);
@endphp
<g class="dash-metric-badge {{ $tone }}">
    <rect x="{{ $rectX }}" y="{{ $rectY }}" width="{{ $width }}" height="{{ $height }}" rx="5.5"/>
    <text class="dash-metric-count {{ $tone }}" x="{{ $point['x'] }}" y="{{ $rectY + ($height / 2) }}" text-anchor="middle" dominant-baseline="middle">{{ $point['value'] }}</text>
</g>
