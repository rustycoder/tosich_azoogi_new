<div class="dash-icon-actions">
    <a class="dash-icon-btn" href="{{ $edit }}" title="Edit" aria-label="Edit">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="M4 20h4l11-11-4-4L4 16z"/><path d="M13 5l4 4"/></svg>
    </a>
    @isset($view)
        <a class="dash-icon-btn" href="{{ $view }}" title="View" aria-label="View" target="_blank" rel="noopener">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="M2 12s4-7 10-7 10 7 10 7-4 7-10 7S2 12 2 12z"/><circle cx="12" cy="12" r="3"/></svg>
        </a>
    @endisset
</div>
