@php
    $topbarClass = trim($__env->yieldContent('topbarClass', 'solid'));
    $logoFile = trim($__env->yieldContent('logo', 'logo_white.png'));
    $description = $headerMeta->get('header.description', 0, 'Australian-Owned B2B Trade Wholesaler - Custom Lighting & Smart Control Solutions');
    $phone = $headerMeta->get('header.phone', 0, '1300 641 261');
    $email = $headerMeta->get('header.email', 0, 'sales@azoogi.com');
    $words = collect($headerMeta->group('header.word'))
        ->pluck('text')
        ->map(fn(?string $word): string => trim((string) $word))
        ->filter()
        ->values()
        ->all();
    $nav = collect($headerMeta->group('header.nav'))
        ->filter(fn(array $item): bool => trim($item['label'] ?? '') !== '')
        ->values();

    if ($nav->isEmpty()) {
        $nav = collect(\App\PageMeta\Definitions\HeaderDefinition::defaultNav());
    }
@endphp
<header class="topbar {{ $topbarClass }}" id="topbar">
    <div class="util">
        <div class="util-inner">
            <div class="util-rotate" data-words='@json($words)' aria-live="polite">{{ $description }}</div>
            <div class="util-end">
                <div class="top-con">
                    <a href="{{ tel_href($phone) }}">{{ $phone }}</a>
                    <a href="mailto:{{ $email }}">{{ $email }}</a>
                    <a href="{{ url('/trade-login') }}">Trade Login</a>
                </div>
                @include('partials.theme-toggle')
            </div>
        </div>
    </div>
    <nav class="nav">
        <a href="{{ url('/') }}" class="logo"><img src="{{ asset('assets/' . $logoFile) }}" width="80" alt="Azoogi"></a>
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
            <button type="button" class="search-trigger" id="search-trigger"
                aria-label="Search products" aria-expanded="false" aria-controls="site-search">
                <svg class="search-trigger-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"
                    stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <circle cx="11" cy="11" r="6.5" />
                    <path d="M16.5 16.5 21 21" />
                </svg>
            </button>
            <button type="button" class="quote-trigger" id="quote-trigger"
                aria-label="{{ $quoteMeta->get('drawer.trigger_label', 0, 'Quote List') }}" aria-expanded="false"
                aria-controls="quote-drawer">
                <svg class="quote-trigger-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"
                    stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <rect x="7" y="3.5" width="10" height="3" rx="1.2" />
                    <path
                        d="M8 5H6.5A1.5 1.5 0 0 0 5 6.5v13A1.5 1.5 0 0 0 6.5 21h11a1.5 1.5 0 0 0 1.5-1.5v-13A1.5 1.5 0 0 0 17.5 5H16" />
                    <path d="M9 11h6M9 15h4" />
                </svg>
                <span class="count" data-quote-count hidden>0</span>
            </button>
        </div>
        <div class="burger"><span></span><span></span><span></span></div>
    </nav>
    <div class="site-search" id="site-search" aria-hidden="true" inert>
        <div class="site-search-inner">
            <form class="site-search-form" id="site-search-form" role="search">
                <label class="visually-hidden" for="site-search-q">Search products</label>
                <div class="site-search-field">
                    <svg class="site-search-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"
                        stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <circle cx="11" cy="11" r="6.5" />
                        <path d="M16.5 16.5 21 21" />
                    </svg>
                    <input id="site-search-q" type="search" name="q" placeholder="Search products..."
                        autocomplete="off" maxlength="80">
                    <button type="button" class="site-search-clear" id="site-search-clear" aria-label="Clear search" hidden>&times;</button>
                </div>
            </form>
            <div class="site-search-results" id="site-search-results" hidden></div>
        </div>
    </div>
</header>