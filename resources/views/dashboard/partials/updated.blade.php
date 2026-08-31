<div class="dash-updated">
    <strong>{{ $record->updater?->name ?? '—' }}</strong>
    @if ($record->updated_at)
        <time datetime="{{ $record->updated_at->toIso8601String() }}">{{ $record->updated_at->format('j M Y, g:i A') }}</time>
    @endif
</div>
