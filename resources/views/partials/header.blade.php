@php
    $topbarClass = trim($__env->yieldContent('topbarClass', 'solid'));
    $logoFile = trim($__env->yieldContent('logo', 'logo_dark.png'));
    $description = $headerMeta->get('header.description', 0, 'Australian-Owned B2B Trade Wholesaler - Custom Lighting & Smart Control Solutions');
    $phone = $headerMeta->get('header.phone', 0, '1300 641 261');
    $email = $headerMeta->get('header.email', 0, 'sales@azoogi.com');
    $words = collect($headerMeta->group('header.word'))
        ->pluck('text')
        ->map(fn (?string $word): string => trim((string) $word))
        ->filter()
        ->values()
        ->all();
    $nav = collect($headerMeta->group('header.nav'))
        ->filter(fn (array $item): bool => trim($item['label'] ?? '') !== '')
        ->values();

    if ($nav->isEmpty()) {
        $nav = collect(\App\PageMeta\Definitions\HeaderDefinition::defaultNav());
    }
@endphp
<header class="topbar {{ $topbarClass }}" id="topbar">
    <div class="util">
        <div class="util-inner">
            <div class="util-rotate" data-words='@json($words)' aria-live="polite">{{ $description }}</div>
            <div style="display:flex;gap:24px">
                <a href="{{ tel_href($phone) }}">{{ $phone }}</a>
                <a href="mailto:{{ $email }}">{{ $email }}</a>
                <div style="display:flex; align-items:center;"><a href="{{ url('/trade-login') }}">Trade Login</a></div>
            </div>
        </div>
    </div>
    <nav class="nav">
        <a href="{{ url('/') }}" class="logo"><img src="{{ asset('assets/'.$logoFile) }}" width="80" alt="Azoogi"></a>
        <div class="menu">
            <div class="has-dropdown">
                <a href="{{ url('/products') }}">Products <span class="caret">&#9662;</span></a>
                <div class="mega-menu" id="dynamic-mega-menu"></div>
            </div>
            @foreach ($nav as $item)
                <a href="{{ chrome_url($item['href'] ?? '') }}"{!! chrome_target_attrs($item['target'] ?? null) !!}>{{ $item['label'] }}</a>
            @endforeach
        </div>
        <div class="nav-actions">
            <a href="{{ url('/led-strip-calculator') }}" class="cta">LED Calculator</a>
            <button type="button" class="quote-trigger" id="quote-trigger" aria-label="Quote list" aria-expanded="false" aria-controls="quote-drawer">
                <span class="quote-trigger-icon" aria-hidden="true"></span>
                <span class="count" data-quote-count hidden>0</span>
            </button>
        </div>
        <div class="burger"><span></span><span></span><span></span></div>
    </nav>
</header>
