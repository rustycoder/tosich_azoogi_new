@php
    $rows = old('items.'.$alias, $words);
    if (! is_array($rows)) {
        $rows = $words;
    }
    $rows = array_values(array_filter(
        $rows,
        fn ($row, $key): bool => $key !== '_sync' && is_array($row),
        ARRAY_FILTER_USE_BOTH,
    ));
@endphp
<div class="dash-link-list" data-repeat-list data-next="{{ count($rows) }}">
    <input type="hidden" name="items[{{ $alias }}][_sync]" value="1">
    <div class="dash-link-panel">
        <div class="dash-link-rows" data-repeat-rows>
            @foreach ($rows as $index => $word)
                <div class="dash-link-item" data-repeat-item>
                    <div class="dash-link-row">
                        <div class="dash-field">
                            <label>Text</label>
                            <input type="text" name="items[{{ $alias }}][{{ $index }}][text]" value="{{ $word['text'] ?? '' }}" aria-label="Rotating text">
                        </div>
                        <button type="button" class="dash-link-remove" data-remove-item aria-label="Remove">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
    <template>
        <div class="dash-link-item" data-repeat-item>
            <div class="dash-link-row">
                <div class="dash-field">
                    <label>Text</label>
                    <input type="text" name="items[{{ $alias }}][__INDEX__][text]" value="" aria-label="Rotating text">
                </div>
                <button type="button" class="dash-link-remove" data-remove-item aria-label="Remove">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
        </div>
    </template>
    <button type="button" class="btn primary dash-link-add" data-add-item>Add text</button>
</div>
