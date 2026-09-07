@php
    /** @var array{
     *     year: int,
     *     months: list<array{label: string, x: float}>,
     *     ticks: list<array{label: int, y: float}>,
     *     show_enquiries: bool,
     *     show_datasheets: bool,
     *     enquiries: list<int>,
     *     datasheets: list<int>,
     *     enquiry_line: string,
     *     enquiry_area: string,
     *     enquiry_points: list<array{x: float, y: float, value: int}>,
     *     datasheet_line: string,
     *     datasheet_area: string,
     *     datasheet_points: list<array{x: float, y: float, value: int}>
     * } $metrics
     */
@endphp
<article class="dash-metric-card is-wide">
    <header class="dash-metric-head">
        <div class="dash-metric-title">
            <h2>Engagement</h2>
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
    <svg
        class="dash-metric-area"
        viewBox="0 0 760 248"
        role="img"
        aria-label="Monthly enquiries and datasheet exports for {{ $metrics['year'] }}"
        @if ($metrics['show_enquiries']) data-enquiries="{{ implode(',', $metrics['enquiries']) }}" @endif
        @if ($metrics['show_datasheets']) data-datasheets="{{ implode(',', $metrics['datasheets']) }}" @endif
    >
        <defs>
            <linearGradient id="dash-metric-fill-enquiries" x1="0" y1="0" x2="0" y2="1">
                <stop offset="0%" stop-color="#8ed89a" stop-opacity="0.5"/>
                <stop offset="100%" stop-color="#8ed89a" stop-opacity="0"/>
            </linearGradient>
            <linearGradient id="dash-metric-fill-datasheets" x1="0" y1="0" x2="0" y2="1">
                <stop offset="0%" stop-color="#f0a3a0" stop-opacity="0.5"/>
                <stop offset="100%" stop-color="#f0a3a0" stop-opacity="0"/>
            </linearGradient>
        </defs>
        @if ($metrics['show_enquiries'])
            <path class="dash-metric-area-fill is-enquiries" data-series="enquiries" d="{{ $metrics['enquiry_area'] }}"/>
        @endif
        @if ($metrics['show_datasheets'])
            <path class="dash-metric-area-fill is-datasheets" data-series="datasheets" d="{{ $metrics['datasheet_area'] }}"/>
        @endif
        @if ($metrics['show_enquiries'])
            <path class="dash-metric-area-line is-enquiries" d="{{ $metrics['enquiry_line'] }}" fill="none"/>
            @foreach ($metrics['enquiry_points'] as $point)
                <circle class="dash-metric-dot is-enquiries" cx="{{ $point['x'] }}" cy="{{ $point['y'] }}" r="2.6"/>
                @if ($point['value'] > 0)
                    @include('dashboard.partials.engagement-badge', ['point' => $point, 'tone' => 'is-enquiries', 'above' => true])
                @endif
            @endforeach
        @endif
        @if ($metrics['show_datasheets'])
            <path class="dash-metric-area-line is-datasheets" d="{{ $metrics['datasheet_line'] }}" fill="none"/>
            @foreach ($metrics['datasheet_points'] as $point)
                <circle class="dash-metric-dot is-datasheets" cx="{{ $point['x'] }}" cy="{{ $point['y'] }}" r="2.6"/>
                @if ($point['value'] > 0)
                    @include('dashboard.partials.engagement-badge', ['point' => $point, 'tone' => 'is-datasheets', 'above' => false])
                @endif
            @endforeach
        @endif
        <line class="dash-metric-baseline" x1="16" y1="210" x2="744" y2="210"/>
        @foreach ($metrics['months'] as $month)
            <text class="dash-metric-axis is-x" x="{{ $month['x'] }}" y="238" text-anchor="middle">{{ $month['label'] }}</text>
        @endforeach
    </svg>
</article>
