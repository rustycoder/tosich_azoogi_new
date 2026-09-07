@php
    /** @var array{
     *     year: int,
     *     plot_left: float,
     *     plot_right: float,
     *     months: list<array{label: string, x: float}>,
     *     ticks: list<array{label: int, y: float}>,
     *     show_enquiries: bool,
     *     show_datasheets: bool,
     *     enquiries: list<int>,
     *     datasheets: list<int>,
     *     enquiry_bars: list<array{x: float, y: float, width: float, height: float, value: int}>,
     *     datasheet_bars: list<array{x: float, y: float, width: float, height: float, value: int}>
     * } $metrics
     */
@endphp
<article class="dash-metric-card is-wide">
    <header class="dash-metric-head">
        <div class="dash-metric-title">
            @include('dashboard.partials.share-mark', ['icon' => 'chart'])
            <h2>Audience Engagement Metrics</h2>
            <span class="dash-metric-year">{{ $metrics['year'] }}</span>
        </div>
        <ul class="dash-metric-key">
            @if ($metrics['show_enquiries'])
                <li class="is-enquiries">
                    <span class="dash-metric-swatch is-enquiries"></span>
                    Enquiries
                </li>
            @endif
            @if ($metrics['show_datasheets'])
                <li class="is-datasheets">
                    <span class="dash-metric-swatch is-datasheets"></span>
                    Datasheet
                </li>
            @endif
        </ul>
    </header>
    <div class="dash-metric-body">
    <svg
        class="dash-metric-chart"
        viewBox="0 0 760 168"
        role="img"
        aria-label="Monthly enquiries and datasheet exports for {{ $metrics['year'] }}"
        @if ($metrics['show_enquiries']) data-enquiries="{{ implode(',', $metrics['enquiries']) }}" @endif
        @if ($metrics['show_datasheets']) data-datasheets="{{ implode(',', $metrics['datasheets']) }}" @endif
    >
        @foreach ($metrics['ticks'] as $tick)
            <line
                class="dash-metric-grid{{ $tick['label'] === 0 ? ' is-base' : '' }}"
                x1="{{ $metrics['plot_left'] }}"
                y1="{{ $tick['y'] }}"
                x2="{{ $metrics['plot_right'] }}"
                y2="{{ $tick['y'] }}"
            />
            <text class="dash-metric-axis is-y" x="{{ $metrics['plot_left'] - 10 }}" y="{{ $tick['y'] }}" text-anchor="end" dominant-baseline="middle">{{ $tick['label'] }}</text>
        @endforeach
        @if ($metrics['show_enquiries'])
            @foreach ($metrics['enquiry_bars'] as $bar)
                @if ($bar['height'] > 0)
                    <rect
                        class="dash-metric-col is-enquiries"
                        data-series="enquiries"
                        x="{{ $bar['x'] }}"
                        y="{{ $bar['y'] }}"
                        width="{{ $bar['width'] }}"
                        height="{{ $bar['height'] }}"
                        rx="1.8"
                    />
                @endif
            @endforeach
        @endif
        @if ($metrics['show_datasheets'])
            @foreach ($metrics['datasheet_bars'] as $bar)
                @if ($bar['height'] > 0)
                    <rect
                        class="dash-metric-col is-datasheets"
                        data-series="datasheets"
                        x="{{ $bar['x'] }}"
                        y="{{ $bar['y'] }}"
                        width="{{ $bar['width'] }}"
                        height="{{ $bar['height'] }}"
                        rx="1.8"
                    />
                @endif
            @endforeach
        @endif
        @foreach ($metrics['months'] as $month)
            <text class="dash-metric-axis is-x" x="{{ $month['x'] }}" y="160" text-anchor="middle">{{ $month['label'] }}</text>
        @endforeach
    </svg>
    </div>
</article>
