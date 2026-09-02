@php
    use App\PageMeta\FieldType;
    use App\PageMeta\Typography;
@endphp
<div class="dash-field{{ ! empty($wide) ? ' is-wide' : '' }}">
    @if (empty($hideLabel))
        <label>{{ $field->label }}</label>
    @endif
    @if ($field->type === FieldType::Textarea || $field->type === FieldType::Html)
        <textarea
            name="meta[{{ $row->id }}][value]"
            rows="{{ $field->type === FieldType::Html ? 8 : 3 }}"
            @if ($field->type === FieldType::Html)
                id="ckeditor-{{ $row->id }}"
                class="is-html"
                data-ckeditor
            @endif
        >{{ old('meta.'.$row->id.'.value', $row->value) }}</textarea>
    @elseif ($field->type === FieldType::Select)
        <select name="meta[{{ $row->id }}][value]">
            @foreach ($field->options as $value => $label)
                <option value="{{ $value }}" @selected(old('meta.'.$row->id.'.value', $row->value) === $value)>{{ $label }}</option>
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
        <input type="file" name="meta[{{ $row->id }}][file]">
    @else
        <input type="text" name="meta[{{ $row->id }}][value]" value="{{ old('meta.'.$row->id.'.value', $row->value) }}">
    @endif
    @if ($field->isTypographic() && request()->routeIs('dashboard.pages.*'))
        <div class="dash-type-row">
            <div class="dash-field">
                <label>Font size</label>
                <select name="meta[{{ $row->id }}][font_size]">
                    <option value="" @selected(old('meta.'.$row->id.'.font_size', $row->font_size) === null || old('meta.'.$row->id.'.font_size', $row->font_size) === '')>Default</option>
                    @foreach (Typography::sizes() as $value => $label)
                        <option value="{{ $value }}" @selected(old('meta.'.$row->id.'.font_size', $row->font_size) === $value)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="dash-field">
                <label>Alignment</label>
                <select name="meta[{{ $row->id }}][text_align]">
                    <option value="" @selected(old('meta.'.$row->id.'.text_align', $row->text_align) === null || old('meta.'.$row->id.'.text_align', $row->text_align) === '')>Default</option>
                    @foreach (Typography::alignments() as $value => $label)
                        <option value="{{ $value }}" @selected(old('meta.'.$row->id.'.text_align', $row->text_align) === $value)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
        </div>
        <small>Default keeps the page design. Change these only if you need an override.</small>
    @endif
</div>
