@php
    /** @var array{country: list<array{label: string, percent: int, color: string}>, device: list<array{label: string, percent: int, color: string}>} $metrics */
@endphp
<article class="dash-metric-card" data-metric>
    <header class="dash-metric-head">
        <div class="dash-metric-title">
            <h2>{{ $title }}</h2>
        </div>
        <div class="dash-metric-switch" role="tablist" aria-label="{{ $title }} breakdown">
            <button type="button" role="tab" data-metric-tab="country" aria-selected="true">Country</button>
            <button type="button" role="tab" data-metric-tab="device" aria-selected="false">Device</button>
        </div>
    </header>
    @foreach (['country' => $metrics['country'], 'device' => $metrics['device']] as $view => $rows)
        <div class="dash-metric-body" data-metric-panel="{{ $view }}" role="tabpanel" @if ($view !== 'country') hidden @endif>
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
        </div>
    @endforeach
</article>
