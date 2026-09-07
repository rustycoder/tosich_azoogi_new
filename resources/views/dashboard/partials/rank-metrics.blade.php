@php
    /** @var list<array{label: string, percent: int, color: string}> $rows */
    /** @var string $title */
    /** @var string $icon */
@endphp
<article class="dash-metric-card dash-share-card">
    <header class="dash-metric-head">
        <div class="dash-metric-title">
            @include('dashboard.partials.share-mark', ['icon' => $icon])
            <h2>{{ $title }}</h2>
        </div>
    </header>
    <div class="dash-metric-body">
        @include('dashboard.partials.metric-bars', ['rows' => $rows])
    </div>
</article>
