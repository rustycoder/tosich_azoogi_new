<div class="dash-row-link">
    <span class="dash-row-link-text">{{ $label }}</span>
    <span class="dash-row-link-actions">
        <a class="dash-row-link-icon" href="{{ $href }}" title="Edit" aria-label="Edit">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 20h9"/><path d="M16.5 3.5a2.1 2.1 0 0 1 3 3L7 19l-4 1 1-4Z"/></svg>
        </a>
        @isset($view)
            <a class="dash-row-link-icon" href="{{ $view }}" title="Preview" aria-label="Preview" target="_blank" rel="noopener">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M2 12s4-7 10-7 10 7 10 7-4 7-10 7S2 12 2 12z"/><circle cx="12" cy="12" r="3"/></svg>
            </a>
        @endisset
    </span>
</div>
