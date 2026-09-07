@php
    /** @var list<array{label: string, percent: int, color: string}> $rows */
@endphp
<article class="dash-metric-card">
    <header class="dash-metric-head">
        <div class="dash-metric-title">
            <h2>{{ $title }}</h2>
        </div>
    </header>
    <div class="dash-metric-body">
        @include('dashboard.partials.metric-bars', ['rows' => $rows])
    </div>
</article>
