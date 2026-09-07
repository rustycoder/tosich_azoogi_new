@php
    /** @var list<array{label: string, percent: int, color: string}> $rows */
@endphp
@if ($rows === [])
    <p class="dash-metric-empty">No records yet.</p>
@else
    <ul class="dash-metric-bars">
        @foreach ($rows as $row)
            <li style="--n: {{ $row['percent'] }}%; --bar: {{ $row['color'] }}">
                <div class="dash-metric-bar-meta">
                    <span class="dash-metric-bar-label" title="{{ $row['label'] }}">{{ $row['label'] }}</span>
                    <span class="dash-metric-bar-value">{{ $row['percent'] }}%</span>
                </div>
                <span class="dash-metric-bar-track">
                    <span class="dash-metric-bar-fill"></span>
                </span>
            </li>
        @endforeach
    </ul>
@endif
