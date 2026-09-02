<form class="dash-search" method="get" action="{{ $action }}" role="search">
    <label class="visually-hidden" for="dash-search-q">{{ $placeholder ?? 'Search by title' }}</label>
    <div class="dash-search-field">
        <svg class="dash-search-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
            <circle cx="11" cy="11" r="6.5"/>
            <path d="M16.5 16.5 21 21"/>
        </svg>
        <input
            id="dash-search-q"
            type="search"
            name="q"
            value="{{ $search }}"
            placeholder="{{ $placeholder ?? 'Search by title' }}"
            maxlength="80"
            autocomplete="off"
        >
        @if ($search !== '')
            <a class="dash-search-clear" href="{{ $action }}" title="Clear search" aria-label="Clear search">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="M6 6l12 12M18 6 6 18"/></svg>
            </a>
        @endif
        <button type="submit" class="dash-search-submit">Search</button>
    </div>
</form>
