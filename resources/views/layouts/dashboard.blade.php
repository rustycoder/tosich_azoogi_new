<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<meta name="csrf-token" content="{{ csrf_token() }}">
<title>@yield('title', 'Dashboard') — Azoogi</title>
<link rel="icon" href="{{ asset('assets/favicon.png') }}">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="{{ versioned_asset('assets/css/style_demo.css') }}">
<link rel="stylesheet" href="{{ versioned_asset('assets/css/dashboard.css') }}">
</head>
<body>
<div class="dash {{ request()->routeIs('dashboard.pages.edit') ? 'is-visual' : '' }}">
    <header class="dash-topbar">
        <button type="button" class="dash-menu" data-dash-menu aria-expanded="false" aria-controls="dash-side">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="M4 7h16M4 12h16M4 17h16"/></svg>
            <span class="visually-hidden">Open menu</span>
        </button>
        <a href="{{ route('dashboard.home') }}" class="dash-topbar-brand">
            <img src="{{ asset('assets/logo_dark.png') }}" alt="Azoogi">
        </a>
    </header>
    <div class="dash-side-backdrop" data-dash-menu-close hidden></div>
    <aside class="dash-side" id="dash-side">
        <div class="dash-side-head">
            <a href="{{ route('dashboard.home') }}" class="dash-brand"><img src="{{ asset('assets/logo_dark.png') }}" alt="Azoogi"></a>
            <button type="button" class="dash-user-btn dash-side-close" data-dash-menu-close aria-label="Close menu">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="M6 6l12 12M18 6 6 18"/></svg>
            </button>
        </div>
        <nav class="dash-nav">
            <a href="{{ route('dashboard.home') }}" class="{{ request()->routeIs('dashboard.home') ? 'is-active' : '' }}">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="M4 10.5 12 4l8 6.5V20a1 1 0 0 1-1 1h-5v-6H10v6H5a1 1 0 0 1-1-1z"/></svg>
                Dashboard
            </a>
            @if ($canManageQuoteEnquiries || $canManageProductEnquiries || $canManageContactEnquiries)
                <div class="dash-group">Enquiries</div>
                @if ($canManageQuoteEnquiries)
                    <a href="{{ route('dashboard.enquiries.index', ['type' => 'quote']) }}" class="{{ request()->routeIs('dashboard.enquiries.*') && (request()->route('type') === 'quote' || request()->route('type') === null) ? 'is-active' : '' }}">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><rect x="6" y="5" width="12" height="15" rx="2"/><path d="M9 5V4h6v1M9 11h6M9 15h4"/></svg>
                        Quote
                    </a>
                @endif
                @if ($canManageProductEnquiries)
                    <a href="{{ route('dashboard.enquiries.index', ['type' => 'products']) }}" class="{{ request()->routeIs('dashboard.enquiries.*') && request()->route('type') === 'products' ? 'is-active' : '' }}">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="M3 8.5 12 4l9 4.5-9 4.5L3 8.5z"/><path d="M3 8.5v7L12 20l9-4.5v-7M12 13v7"/></svg>
                        Product
                    </a>
                @endif
                @if ($canManageContactEnquiries)
                    <a href="{{ route('dashboard.enquiries.index', ['type' => 'contacts']) }}" class="{{ request()->routeIs('dashboard.enquiries.*') && request()->route('type') === 'contacts' ? 'is-active' : '' }}">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><rect x="3" y="6" width="18" height="13" rx="2"/><path d="m3 8 9 6 9-6"/></svg>
                        Contact
                    </a>
                @endif
            @endif
            @if ($canManageProjects || $canManageProducts || $canManagePages || $canManageSections)
                <div class="dash-group">Content Management</div>
                @if ($canManageProjects)
                    <a href="{{ route('dashboard.projects.index') }}" class="{{ request()->routeIs('dashboard.projects.*') ? 'is-active' : '' }}">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><rect x="3" y="4" width="18" height="14" rx="2"/><path d="M3 15l5-4 4 3 4-5 5 6"/></svg>
                        Projects
                    </a>
                @endif
                @if ($canManageProducts)
                    <a href="{{ route('dashboard.products.index') }}" class="{{ request()->routeIs('dashboard.products.*') ? 'is-active' : '' }}">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="M3 8.5 12 4l9 4.5-9 4.5L3 8.5z"/><path d="M3 8.5v7L12 20l9-4.5v-7M12 13v7"/></svg>
                    Products
                    </a>
                @endif
                @if ($canManagePages)
                    <a href="{{ route('dashboard.pages.index') }}" class="{{ request()->routeIs('dashboard.pages.*') ? 'is-active' : '' }}">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="M7 3h7l5 5v13a1 1 0 0 1-1 1H7a1 1 0 0 1-1-1V4a1 1 0 0 1 1-1z"/><path d="M14 3v6h6"/></svg>
                        Pages
                    </a>
                @endif
                @if ($canManageSections)
                    <a href="{{ route('dashboard.sections.index') }}" class="{{ request()->routeIs('dashboard.sections.*') ? 'is-active' : '' }}">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="M4 6h16M4 12h16M4 18h10"/></svg>
                        Sections
                    </a>
                @endif
            @endif
            @if ($isAdmin)
                <div class="dash-group">Administration</div>
                <a href="{{ route('dashboard.staff.index') }}" class="{{ request()->routeIs('dashboard.staff.*') ? 'is-active' : '' }}">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><circle cx="9" cy="8" r="3"/><path d="M4 19a5 5 0 0 1 10 0"/><circle cx="17" cy="9" r="2.4"/><path d="M16 19a4.2 4.2 0 0 1 4-3"/></svg>
                    Staff
                </a>
            @endif
        </nav>
        <div class="dash-user">
            <div class="dash-user-name">{{ auth()->user()->name }}</div>
            <div class="dash-user-meta">
                <span>{{ auth()->user()->user_type->label() }}</span>
                <div class="dash-user-actions">
                    <a
                        href="{{ route('dashboard.profile.edit') }}"
                        class="dash-user-btn {{ request()->routeIs('dashboard.profile.*') ? 'is-active' : '' }}"
                        title="Settings"
                        aria-label="Settings"
                    >
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                            <circle cx="12" cy="12" r="3"/>
                            <path d="M19.4 15a1.7 1.7 0 0 0 .3 1.8l.1.1a2 2 0 1 1-2.8 2.8l-.1-.1a1.7 1.7 0 0 0-1.8-.3 1.7 1.7 0 0 0-1 1.5V21a2 2 0 1 1-4 0v-.1a1.7 1.7 0 0 0-1.1-1.5 1.7 1.7 0 0 0-1.8.3l-.1.1a2 2 0 1 1-2.8-2.8l.1-.1a1.7 1.7 0 0 0 .3-1.8 1.7 1.7 0 0 0-1.5-1H3a2 2 0 1 1 0-4h.1a1.7 1.7 0 0 0 1.5-1.1 1.7 1.7 0 0 0-.3-1.8l-.1-.1a2 2 0 1 1 2.8-2.8l.1.1a1.7 1.7 0 0 0 1.8.3H9a1.7 1.7 0 0 0 1-1.5V3a2 2 0 1 1 4 0v.1a1.7 1.7 0 0 0 1 1.5 1.7 1.7 0 0 0 1.8-.3l.1-.1a2 2 0 1 1 2.8 2.8l-.1.1a1.7 1.7 0 0 0-.3 1.8V9a1.7 1.7 0 0 0 1.5 1H21a2 2 0 1 1 0 4h-.1a1.7 1.7 0 0 0-1.5 1z"/>
                        </svg>
                    </a>
                    <form method="post" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="dash-user-btn" title="Log out" aria-label="Log out">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                                <path d="M10 7V5a2 2 0 0 1 2-2h7a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-7a2 2 0 0 1-2-2v-2"/>
                                <path d="M15 12H3m0 0 3-3m-3 3 3 3"/>
                            </svg>
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </aside>
    <main class="dash-main">
        @yield('content')
    </main>
</div>
<div id="dash-toasts" class="dash-toasts" aria-live="polite" @if (session('status')) data-flash="{{ session('status') }}" @endif></div>
<script src="{{ versioned_asset('assets/js/dashboard.js') }}"></script>
@stack('scripts')
</body>
</html>
