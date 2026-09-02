<div class="dash-updated">
    <span class="dash-list-label">{{ $label ?? 'Last updated' }}</span>
    <span class="dash-updated-value">
        <strong>{{ $record->updater?->name ?? '—' }}</strong>
        @if ($record->updated_at)
            <span class="dash-updated-sep" aria-hidden="true">·</span>
            <time datetime="{{ $record->updated_at->toIso8601String() }}">{{ $record->updated_at->format('j M Y, g:i A') }}</time>
        @endif
    </span>
</div>
