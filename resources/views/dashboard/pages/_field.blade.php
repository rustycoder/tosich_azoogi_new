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
            data-counter="{{ $field->counterType ?? 'chars' }}"
            @if ($field->hasRecommendation())
                data-min="{{ $field->minCount }}"
                data-max="{{ $field->maxCount }}"
            @endif>{{ old('meta.' . $row->id . '.value', $row->value) }}</textarea>
    @elseif ($field->type === FieldType::Select)
        <select name="meta[{{ $row->id }}][value]">
            @foreach ($field->options as $value => $label)
                <option value="{{ $value }}" @selected(old('meta.' . $row->id . '.value', $row->value) === $value)>{{ $label }}</option>
            @endforeach
        </select>
    @elseif ($field->type === FieldType::Image)
        @php
            $info = media_file_info($row->value);
        @endphp
        <div class="dash-dropzone" data-image-dropzone>
            @if ($row->value)
                <div class="dash-dropzone-previews">
                    <div class="dash-preview-card">
                        <div class="dash-preview-thumb-wrap">
                            <img class="dash-preview-thumb" src="{{ media_url($row->value) }}" alt="{{ basename($row->value) }}">
                        </div>
                        <div class="dash-preview-info">
                            <div class="dash-preview-filename" title="{{ basename($row->value) }}">{{ basename($row->value) }}</div>
                            <div class="dash-preview-badges">
                                <span class="dash-preview-badge is-format">{{ $info['format'] ?? 'IMAGE' }}</span>
                                @if (!empty($info['size']))
                                    <span class="dash-preview-badge">{{ $info['size'] }}</span>
                                @endif
                                @if (!empty($info['dimensions']))
                                    <span class="dash-preview-badge is-dimensions">{{ $info['dimensions'] }}</span>
                                @endif
                                @if (!empty($info['aspect_ratio']))
                                    <span class="dash-preview-badge is-ratio">{{ $info['aspect_ratio'] }}</span>
                                @endif
                                <span class="dash-preview-badge">Current file</span>
                            </div>
                        </div>
                    </div>
                </div>
            @endif
            <div class="dash-dropzone-box">
                <input class="dash-dropzone-input" type="file" name="meta[{{ $row->id }}][file]" accept="image/*">
                <div class="dash-dropzone-icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8.5" cy="8.5" r="1.5"/><polyline points="21 15 16 10 5 21"/></svg>
                </div>
                <div class="dash-dropzone-text"><strong>Choose an image</strong> or drag and drop</div>
                <span class="dash-dropzone-sub">WEBP, JPG, PNG up to 10MB</span>
            </div>
            <div class="dash-dropzone-previews" hidden></div>
        </div>
        <input type="hidden" name="meta[{{ $row->id }}][value]" value="{{ $row->value }}">
        @include('dashboard.partials.upload-progress')
        @if ($field->hint !== '')
            <small>{{ $field->hint }}</small>
        @endif
    @elseif ($field->type === FieldType::Video)
        @php
            $videoInfo = media_file_info($row->value);
        @endphp
        <div class="dash-dropzone" data-media-dropzone>
            @if ($row->value)
                <div class="dash-dropzone-previews">
                    <div class="dash-preview-card">
                        <div class="dash-preview-thumb-wrap">
                            <video class="dash-preview-thumb" src="{{ media_url($row->value) }}" muted playsinline preload="metadata"></video>
                        </div>
                        <div class="dash-preview-info">
                            <div class="dash-preview-filename" title="{{ basename($row->value) }}">{{ basename($row->value) }}</div>
                            <div class="dash-preview-badges">
                                <span class="dash-preview-badge is-format">{{ $videoInfo['format'] ?? 'VIDEO' }}</span>
                                @if (!empty($videoInfo['size']))
                                    <span class="dash-preview-badge">{{ $videoInfo['size'] }}</span>
                                @endif
                                @if (!empty($videoInfo['dimensions']))
                                    <span class="dash-preview-badge is-dimensions">{{ $videoInfo['dimensions'] }}</span>
                                @endif
                                @if (!empty($videoInfo['aspect_ratio']))
                                    <span class="dash-preview-badge is-ratio">{{ $videoInfo['aspect_ratio'] }}</span>
                                @endif
                                <span class="dash-preview-badge">Current video</span>
                            </div>
                        </div>
                    </div>
                </div>
            @endif
            <div class="dash-dropzone-box">
                <input class="dash-dropzone-input" type="file" name="meta[{{ $row->id }}][file]" accept="video/*">
                <div class="dash-dropzone-icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><rect x="2" y="4" width="20" height="16" rx="2"/><polygon points="10 8 16 12 10 16 10 8"/></svg>
                </div>
                <div class="dash-dropzone-text"><strong>Choose a video</strong> or drag and drop</div>
                <span class="dash-dropzone-sub">MP4, WebM (H.264 MP4 recommended, &lt; 5MB)</span>
            </div>
            <div class="dash-dropzone-previews" hidden></div>
        </div>
        <input type="hidden" name="meta[{{ $row->id }}][value]" value="{{ $row->value }}">
        @include('dashboard.partials.upload-progress')
    @else
        <input type="text" name="meta[{{ $row->id }}][value]"
            value="{{ old('meta.' . $row->id . '.value', $row->value) }}"
            data-counter="{{ $field->counterType ?? 'chars' }}"
            @if ($field->hasRecommendation())
                data-min="{{ $field->minCount }}"
                data-max="{{ $field->maxCount }}"
            @endif>
    @endif
</div>
