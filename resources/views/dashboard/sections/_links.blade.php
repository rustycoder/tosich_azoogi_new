@php
    $rows = old('items.'.$alias, $links);
    if (! is_array($rows)) {
        $rows = $links;
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
        <div class="dash-link-head" aria-hidden="true">
            <div class="dash-link-fields">
                <span>Label</span>
                <span>URL</span>
                <span>Target</span>
            </div>
            <span class="dash-link-head-spacer"></span>
        </div>
        <div class="dash-link-rows" data-repeat-rows>
            @foreach ($rows as $index => $link)
                <div class="dash-link-item" data-repeat-item>
                    <div class="dash-link-row">
                        <div class="dash-link-fields">
                            <div class="dash-field">
                                <label>Label</label>
                                <input type="text" name="items[{{ $alias }}][{{ $index }}][label]" value="{{ $link['label'] ?? '' }}" aria-label="Label">
                            </div>
                            <div class="dash-field">
                                <label>URL</label>
                                <input type="text" name="items[{{ $alias }}][{{ $index }}][href]" value="{{ $link['href'] ?? '' }}" aria-label="URL">
                            </div>
                            <div class="dash-field dash-field-target">
                                <label>Target</label>
                                @php $target = ($link['target'] ?? '_self') === '_blank' ? '_blank' : '_self'; @endphp
                                <select name="items[{{ $alias }}][{{ $index }}][target]" aria-label="Target">
                                    <option value="_self" @selected($target === '_self')>Same tab</option>
                                    <option value="_blank" @selected($target === '_blank')>New tab</option>
                                </select>
                            </div>
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
                <div class="dash-link-fields">
                    <div class="dash-field">
                        <label>Label</label>
                        <input type="text" name="items[{{ $alias }}][__INDEX__][label]" value="" aria-label="Label">
                    </div>
                    <div class="dash-field">
                        <label>URL</label>
                        <input type="text" name="items[{{ $alias }}][__INDEX__][href]" value="" aria-label="URL">
                    </div>
                    <div class="dash-field dash-field-target">
                        <label>Target</label>
                        <select name="items[{{ $alias }}][__INDEX__][target]" aria-label="Target">
                            <option value="_self" selected>Same tab</option>
                            <option value="_blank">New tab</option>
                        </select>
                    </div>
                </div>
                <button type="button" class="dash-link-remove" data-remove-item aria-label="Remove">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
        </div>
    </template>
    <button type="button" class="btn primary dash-link-add" data-add-item>Add link</button>
</div>
