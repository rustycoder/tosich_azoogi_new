<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>{{ $sheet['title'] }} — Azoogi datasheet</title>
<link rel="icon" href="{{ asset('assets/favicon.png') }}">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Open+Sans:wght@400;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="{{ versioned_asset('assets/css/datasheet.css') }}">
</head>
<body>
<button type="button" class="ds-print" onclick="window.print()" title="Print / Save PDF" aria-label="Print / Save PDF">
    <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M19 8H5a3 3 0 0 0-3 3v6h4v4h12v-4h4v-6a3 3 0 0 0-3-3zm-3 11H8v-5h8zm3-7a1 1 0 1 1 1-1 1 1 0 0 1-1 1zM18 3H6v4h12z"/></svg>
</button>
<section class="page">
    <header class="az-header">
        <img class="logo-left" src="{{ asset('assets/img/datasheet-logo.png') }}" alt="Azoogi Lighting Solutions">
        <img class="logo-right" src="{{ asset('assets/img/lighting-council.png') }}" alt="Lighting Council Australia">
    </header>

    <div class="title-block">
        @if ($sheet['title'] !== '')
            <h1>{{ $sheet['title'] }}</h1>
        @endif
        @if ($sheet['name'] !== '')
            <h2>{{ $sheet['name'] }}</h2>
        @endif
    </div>
    <hr class="gradient-line">

    <div class="body-wrap">
        <div class="content">
            <div>
                @if ($sheet['description'] !== '')
                    <p class="desc">{{ $sheet['description'] }}</p>
                @endif
                @if ($sheet['specifications'] !== [])
                    <p class="params-title">SPECIFICATIONS</p>
                    <table class="params">
                        @foreach ($sheet['specifications'] as $row)
                            <tr>
                                <td>{{ $row['label'] }}</td>
                                <td>{{ $row['value'] }}</td>
                            </tr>
                        @endforeach
                    </table>
                @endif
            </div>

            <div style="display: flex; flex-direction: column;">
                @if ($sheet['product_image'] !== '')
                    <div class="product-img-container">
                        <img class="product-img" src="{{ $sheet['product_image'] }}" alt="{{ $sheet['name'] !== '' ? $sheet['name'] : $sheet['title'] }}">
                    </div>
                @endif
                @if ($sheet['dimension_image'] !== '')
                    <div class="dim-img-container">
                        <div class="dim-title">DIMENSIONS</div>
                        <img class="dim-img" src="{{ $sheet['dimension_image'] }}" alt="Dimensions">
                    </div>
                @endif
                <div class="note-img-container">
                    <main class="sheet">
                        <section class="row head">
                            <div>
                                <h1>Lighting Technical Review</h1>
                                <div class="project"><b>Project:</b> {{ $sheet['project_name'] }}</div>
                            </div>
                            <div class="logo">
                                <img src="{{ asset('assets/logo_dark.png') }}" alt="Azoogi Lighting Solutions logo">
                            </div>
                        </section>
                        <section class="row checks">
                            <div class="check">
                                <div class="check-label">Matched to Specifcation:</div>
                                <div class="box" role="checkbox" aria-checked="false" aria-label="Matched to specification"></div>
                            </div>
                            <div class="check">
                                <div class="check-label">Minor Variations:</div>
                                <div class="box" role="checkbox" aria-checked="false" aria-label="Minor variations"></div>
                            </div>
                        </section>
                        <section class="row line">Checked By: <span>{{ $sheet['person_name'] }}</span></section>
                        <section class="row line">Date: <span>{{ $sheet['reviewed_on'] }}</span></section>
                        <section class="row notes">
                            <ul>
                                <li>This technical review indicates general conformity with the project design intent only.</li>
                                <li>Review does not constitute approval of dimensions, quantities, installation methods, site conditions, or regulatory compliance.</li>
                                <li>The Client and Electrical Subcontractor remain responsible for verification and compliance with all project documentation and applicable standards. Refer to the latest issued Azoogi working file and Luminaire Schedule for current product selections and revisions.</li>
                            </ul>
                        </section>
                    </main>
                </div>
            </div>
        </div>
    </div>

    <footer class="az-footer">
        <span class="f-item">
            <svg viewBox="0 0 24 24"><path d="M20 4H4a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2V6a2 2 0 0 0-2-2zm0 4-8 5-8-5V6l8 5 8-5z"/></svg>
            {{ $sheet['email'] }}
        </span>
        <span class="f-item">
            <svg viewBox="0 0 24 24"><path d="M6.6 10.8a15.9 15.9 0 0 0 6.6 6.6l2.2-2.2a1 1 0 0 1 1-.25 11.4 11.4 0 0 0 3.6.58 1 1 0 0 1 1 1V20a1 1 0 0 1-1 1A17 17 0 0 1 3 4a1 1 0 0 1 1-1h3.5a1 1 0 0 1 1 1 11.4 11.4 0 0 0 .58 3.6 1 1 0 0 1-.25 1z"/></svg>
            {{ $sheet['phone'] }}
        </span>
        <span class="f-item">
            <svg viewBox="0 0 24 24"><path d="M12 2a10 10 0 1 0 10 10A10 10 0 0 0 12 2zm7.9 9h-3.95a15.7 15.7 0 0 0-1.3-5.7A8 8 0 0 1 19.9 11zM12 4c.97 1.4 1.8 3.7 1.95 7H10.05C10.2 7.7 11.03 5.4 12 4zM4.1 13h3.95a15.7 15.7 0 0 0 1.3 5.7A8 8 0 0 1 4.1 13zm0-2A8 8 0 0 1 9.35 5.3 15.7 15.7 0 0 0 8.05 11zm7.9 9c-.97-1.4-1.8-3.7-1.95-7h3.9c-.15 3.3-.98 5.6-1.95 7zm2.65-.3a15.7 15.7 0 0 0 1.3-5.7h3.95a8 8 0 0 1-5.25 5.7z"/></svg>
            {{ $sheet['website'] }}
        </span>
        <span class="f-item">
            <svg viewBox="0 0 24 24"><path d="M12 2a7 7 0 0 0-7 7c0 5.25 7 13 7 13s7-7.75 7-13a7 7 0 0 0-7-7zm0 9.5A2.5 2.5 0 1 1 14.5 9 2.5 2.5 0 0 1 12 11.5z"/></svg>
            {{ $sheet['address'] }}
        </span>
    </footer>
</section>
@if ($autoPrint)
    <script>
        window.addEventListener('load', function () {
            window.setTimeout(function () { window.print(); }, 250);
        });
    </script>
@endif
</body>
</html>
