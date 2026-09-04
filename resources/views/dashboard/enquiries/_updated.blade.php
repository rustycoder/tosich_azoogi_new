<span class="dash-kanban-when">
    <strong data-updater-name>{{ $enquiry->updater?->name ?? '—' }}</strong>
    @if ($updatedAt)
        <span class="dash-updated-sep" aria-hidden="true">·</span>
        <time data-updated-at datetime="{{ $enquiry->updated_at->toIso8601String() }}">{{ $updatedAt->format('j M Y, g:i A') }}</time>
    @endif
</span>
