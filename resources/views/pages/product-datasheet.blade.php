<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>{{ $sheet['title'] }} — Azoogi datasheet</title>
<link rel="icon" href="{{ asset('assets/favicon.png') }}">
<link rel="stylesheet" href="{{ versioned_asset('assets/css/datasheet.css') }}">
</head>
<body class="ds-root">
<div class="ds-toolbar">
    <button type="button" onclick="window.print()">Print / Save PDF</button>
</div>
<article class="ds-sheet">
    <header class="ds-header">
        <img class="ds-header-logo" src="{{ asset('assets/logo_white.png') }}" alt="Azoogi Lighting Solutions">
        <img class="ds-header-badge" src="{{ asset('assets/img/lighting-council-accredited.svg') }}" alt="Lighting Council Australia Accredited">
    </header>

    <div class="ds-body">
        <h1 class="ds-title">{{ $sheet['title'] }}</h1>
        @if ($sheet['category'] !== '')
            <p class="ds-category">{{ $sheet['category'] }}</p>
        @endif
        @if ($sheet['description'] !== '')
            <p class="ds-desc">{{ $sheet['description'] }}</p>
        @endif
        <div class="ds-rule"></div>

        <div class="ds-grid">
            <section>
                <h2 class="ds-specs-title">SPECIFICATIONS</h2>
                <table class="ds-specs">
                    <tbody>
                        @foreach ($sheet['specifications'] as $row)
                            <tr>
                                <th>{{ $row['label'] }}</th>
                                <td>{{ $row['value'] }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </section>

            <aside class="ds-visual">
                <div class="ds-photo-wrap">
                    @if ($sheet['product_image'] !== '')
                        <img class="ds-photo" src="{{ $sheet['product_image'] }}" alt="{{ $sheet['title'] }}">
                    @endif
                </div>
                @if ($sheet['dimension_image'] !== '')
                    <div class="ds-drawing-wrap">
                        <h2 class="ds-specs-title">DIMENSIONS</h2>
                        <img class="ds-drawing" src="{{ $sheet['dimension_image'] }}" alt="{{ $sheet['title'] }} dimensions">
                    </div>
                @endif
                <div class="ds-review">
                    <div class="ds-review-head">
                        <h2 class="ds-review-title">Lighting Technical Review</h2>
                        <img class="ds-review-logo" src="{{ asset('assets/logo_dark.png') }}" alt="Azoogi">
                    </div>
                    <div class="ds-review-row">
                        <span class="ds-review-label">PROJECT:</span>
                        <span class="ds-review-value">{{ $sheet['project_name'] }}</span>
                    </div>
                    <div class="ds-review-row">
                        <span class="ds-review-label">MATCHED TO SPECIFICATION</span>
                        <span class="ds-check" aria-hidden="true"></span>
                    </div>
                    <div class="ds-review-row">
                        <span class="ds-review-label">MINOR VARIATIONS</span>
                        <span class="ds-check" aria-hidden="true"></span>
                    </div>
                    <div class="ds-review-row">
                        <span class="ds-review-label">CHECKED BY:</span>
                        <span class="ds-review-value">{{ $sheet['person_name'] }}</span>
                    </div>
                    <div class="ds-review-row">
                        <span class="ds-review-label">DATE:</span>
                        <span class="ds-review-value">{{ $sheet['reviewed_on'] }}</span>
                    </div>
                    <p class="ds-disclaimer">This Lighting Technical Review is a selection guide only. It is not a certification, warranty, or approval of the installation. The specifier, consultant, and installer remain responsible for ensuring the product is fit for purpose and complies with applicable Australian Standards and regulations.</p>
                </div>
            </aside>
        </div>
    </div>

    <footer class="ds-footer">
        <div class="ds-footer-item">
            <svg class="ds-footer-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><rect x="3" y="5" width="18" height="14" rx="2"/><path d="m3 7 9 7 9-7"/></svg>
            <span>{{ $sheet['email'] }}</span>
        </div>
        <div class="ds-footer-item">
            <svg class="ds-footer-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M6.5 4.5h3l1.5 4-2 1.5a12 12 0 0 0 5 5l1.5-2 4 1.5v3a2 2 0 0 1-2.2 2A16 16 0 0 1 4.5 6.7 2 2 0 0 1 6.5 4.5z"/></svg>
            <span>{{ $sheet['phone'] }}</span>
        </div>
        <div class="ds-footer-item">
            <svg class="ds-footer-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><circle cx="12" cy="12" r="9"/><path d="M3 12h18M12 3a14 14 0 0 1 0 18M12 3a14 14 0 0 0 0 18"/></svg>
            <span>{{ $sheet['website'] }}</span>
        </div>
        <div class="ds-footer-item">
            <svg class="ds-footer-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M12 21s7-5.4 7-11a7 7 0 1 0-14 0c0 5.6 7 11 7 11z"/><circle cx="12" cy="10" r="2.4"/></svg>
            <span>{{ $sheet['address'] }}</span>
        </div>
    </footer>
</article>
@if ($autoPrint)
    <script>
        window.addEventListener('load', function () {
            window.setTimeout(function () { window.print(); }, 250);
        });
    </script>
@endif
</body>
</html>
