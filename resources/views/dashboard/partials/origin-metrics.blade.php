@php
    /** @var array{country: list<array{label: string, percent: int, color: string}>, device: list<array{label: string, percent: int, color: string}>} $metrics */
    /** @var string $title */
    /** @var string $icon */
@endphp
<article class="dash-metric-card dash-share-card" data-metric>
    <header class="dash-metric-head">
        <div class="dash-metric-title">
            @include('dashboard.partials.share-mark', ['icon' => $icon])
            <h2>{{ $title }}</h2>
        </div>
        <div class="dash-metric-switch" role="tablist" aria-label="{{ $title }} breakdown">
            <button type="button" role="tab" data-metric-tab="country" aria-selected="true">Country</button>
            <button type="button" role="tab" data-metric-tab="device" aria-selected="false">Device</button>
        </div>
    </header>
    @foreach (['country' => $metrics['country'], 'device' => $metrics['device']] as $view => $rows)
        <div class="dash-metric-body" data-metric-panel="{{ $view }}" role="tabpanel" @if ($view !== 'country') hidden @endif>
            @include('dashboard.partials.metric-bars', ['rows' => $rows])
        </div>
    @endforeach
</article>
