@php
    /** @var list<array{label: string, sku: string, icon: string, url: string}> $rows */
@endphp
<article class="dash-metric-card is-wide dash-top-products">
    <header class="dash-metric-head">
        <div class="dash-metric-title">
            @include('dashboard.partials.share-mark', ['icon' => 'products'])
            <h2>Top Products</h2>
        </div>
    </header>
    <div class="dash-metric-body">
        @if ($rows === [])
            <p class="dash-metric-empty">No records yet.</p>
        @else
            <ol class="dash-top-products-grid">
                @foreach ($rows as $index => $row)
                    <li class="{{ $index === 0 ? 'is-lead' : '' }}">
                        <span class="dash-top-products-rank" aria-hidden="true">{{ $index + 1 }}</span>
                        @include('dashboard.partials.thumb', [
                            'src' => $row['icon'],
                            'alt' => $row['label'],
                        ])
                        <div class="dash-top-products-copy">
                            <span class="dash-metric-bar-label" title="{{ $row['label'] }}">{{ $row['label'] }}</span>
                            @if ($row['sku'] !== '')
                                <span class="dash-top-products-sku">{{ $row['sku'] }}</span>
                            @endif
                        </div>
                        @if ($row['url'] !== '')
                            <a
                                class="dash-row-link-icon"
                                href="{{ $row['url'] }}"
                                title="Preview"
                                aria-label="Preview {{ $row['label'] }}"
                                target="_blank"
                                rel="noopener"
                            >
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M2 12s4-7 10-7 10 7 10 7-4 7-10 7S2 12 2 12z"/><circle cx="12" cy="12" r="3"/></svg>
                            </a>
                        @endif
                    </li>
                @endforeach
            </ol>
        @endif
    </div>
</article>
