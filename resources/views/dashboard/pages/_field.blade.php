@php
    use App\PageMeta\FieldType;
@endphp
<div class="dash-field{{ !empty($wide) ? ' is-wide' : '' }}">
    @if (empty($hideLabel))
        <label>
            {{ $field->label }}
            @if ($field->hasRecommendation())
                <small class="dash-field-rec">{{ $field->recommendationLabel() }}</small>
            @endif
        </label>
    @endif
    @if ($field->type === FieldType::Textarea || $field->type === FieldType::Html)
        <textarea name="meta[{{ $row->id }}][value]" rows="{{ $field->type === FieldType::Html ? 8 : 3 }}"
            @if ($field->type === FieldType::Html) id="ckeditor-{{ $row->id }}"
                class="is-html"
                data-ckeditor @endif
            @if ($field->hasRecommendation())
                data-counter="{{ $field->counterType }}"
                data-min="{{ $field->minCount }}"
                data-max="{{ $field->maxCount }}"
            @endif>{{ old('meta.' . $row->id . '.value', $row->value) }}</textarea>
    @elseif ($field->type === FieldType::Select)
        <select name="meta[{{ $row->id }}][value]">
            @foreach ($field->options as $value => $label)
                <option value="{{ $value }}" @selected(old('meta.' . $row->id . '.value', $row->value) === $value)>{{ $label }}</option>
            @endforeach
        </select>
    @elseif ($field->type === FieldType::Image || $field->type === FieldType::Video)
        @if ($row->value)
            @if ($field->type === FieldType::Image)
                <img class="dash-preview" src="{{ media_url($row->value) }}" alt="">
            @else
                <p>{{ $row->value }}</p>
            @endif
        @endif
        <input type="hidden" name="meta[{{ $row->id }}][value]" value="{{ $row->value }}">
        <input type="file" name="meta[{{ $row->id }}][file]" @if ($field->type === FieldType::Image) accept="image/*" @else accept="video/*" @endif>
        @include('dashboard.partials.upload-progress')
        @if ($field->type === FieldType::Image && $field->hint !== '')
            <small>{{ $field->hint }}</small>
        @endif
    @else
        <input type="text" name="meta[{{ $row->id }}][value]"
            value="{{ old('meta.' . $row->id . '.value', $row->value) }}"
            @if ($field->hasRecommendation())
                data-counter="{{ $field->counterType }}"
                data-min="{{ $field->minCount }}"
                data-max="{{ $field->maxCount }}"
            @endif>
    @endif
</div>
