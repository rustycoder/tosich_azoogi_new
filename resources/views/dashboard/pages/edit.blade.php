@extends('layouts.dashboard')

@section('title', $definition->navLabel())

@section('content')
<div class="dash-visual" data-preview-url="{{ $previewUrl }}" data-open-section="{{ request('section') }}">
    <header class="dash-visual-bar">
        <a class="dash-visual-back" href="{{ route('dashboard.pages.index') }}">← Pages</a>
        <div class="dash-visual-title">
            <strong>{{ $definition->navLabel() }}</strong>
            <span>Click Edit on the page to change that section.</span>
        </div>
        <div class="dash-visual-tools">
            <button type="button" class="btn" data-open-section="meta">Page Meta</button>
            <a class="btn" href="{{ $page->publicPath() }}" target="_blank" rel="noopener">Live preview</a>
        </div>
    </header>

    <div class="dash-visual-body">
        <iframe
            id="dash-visual-frame"
            class="dash-visual-frame"
            title="Page preview"
            src="{{ $previewUrl }}"
        ></iframe>
    </div>
</div>

<div class="dash-drawer-backdrop" id="dash-drawer-backdrop" hidden></div>

<aside class="dash-drawer" id="dash-drawer-meta" hidden>
    <form class="dash-form" method="post" action="{{ route('dashboard.pages.update', $page) }}" enctype="multipart/form-data">
        @csrf
        @method('put')
        <input type="hidden" name="editor_section" value="meta">
        <input type="hidden" name="status" value="{{ $page->status->value }}">
        <div class="dash-drawer-head">
            <h2>Page Meta & SEO</h2>
            <button type="button" class="dash-drawer-close" data-close-drawer aria-label="Close">&times;</button>
        </div>
        <div class="dash-drawer-body">
            <div class="dash-field">
                <label for="title">SEO title <small class="dash-field-rec">(50–60 chars recommended)</small></label>
                <input id="title" name="title" value="{{ old('title', $page->title) }}" data-counter="chars" data-min="50" data-max="60" required>
                @error('title')<p class="login-error">{{ $message }}</p>@enderror
            </div>
            <div class="dash-field">
                <label for="meta_description">Meta description <small class="dash-field-rec">(140–160 chars recommended)</small></label>
                <textarea id="meta_description" name="meta_description" rows="4" data-counter="chars" data-min="140" data-max="160">{{ old('meta_description', $page->meta_description) }}</textarea>
                @error('meta_description')<p class="login-error">{{ $message }}</p>@enderror
            </div>
            <div class="dash-field">
                <label for="og_image_file">Social Share Image (OG Image) <small class="dash-field-rec">(1200×630px recommended)</small></label>
                <div class="dash-dropzone" data-image-dropzone>
                    @if (!empty($page->og_image))
                        @php
                            $ogInfo = media_file_info($page->og_image);
                        @endphp
                        <div class="dash-dropzone-previews">
                            <div class="dash-preview-card">
                                <div class="dash-preview-thumb-wrap" style="width: 100px; height: 56px;">
                                    <img class="dash-preview-thumb" src="{{ media_url($page->og_image) }}" alt="OG Preview">
                                </div>
                                <div class="dash-preview-info">
                                    <div class="dash-preview-filename" title="{{ basename($page->og_image) }}">{{ basename($page->og_image) }}</div>
                                    <div class="dash-preview-badges">
                                        <span class="dash-preview-badge is-format">{{ $ogInfo['format'] ?? 'IMAGE' }}</span>
                                        @if (!empty($ogInfo['size']))
                                            <span class="dash-preview-badge">{{ $ogInfo['size'] }}</span>
                                        @endif
                                        @if (!empty($ogInfo['dimensions']))
                                            <span class="dash-preview-badge is-dimensions">{{ $ogInfo['dimensions'] }}</span>
                                        @endif
                                        @if (!empty($ogInfo['aspect_ratio']))
                                            <span class="dash-preview-badge is-ratio">{{ $ogInfo['aspect_ratio'] }}</span>
                                        @endif
                                        <span class="dash-preview-badge">Current file</span>
                                    </div>
                                    <label style="font-size: 11.5px; display: inline-flex; align-items: center; gap: 5px; cursor: pointer; color: #c4453c; margin-top: 2px;">
                                        <input type="checkbox" name="remove_og_image" value="1">
                                        Remove on save
                                    </label>
                                </div>
                            </div>
                        </div>
                    @endif
                    <div class="dash-dropzone-box">
                        <input id="og_image_file" class="dash-dropzone-input" name="og_image_file" type="file" accept="image/png,image/jpeg,image/webp">
                        <div class="dash-dropzone-icon">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8.5" cy="8.5" r="1.5"/><polyline points="21 15 16 10 5 21"/></svg>
                        </div>
                        <div class="dash-dropzone-text"><strong>Choose an OG image</strong> or drag and drop</div>
                        <span class="dash-dropzone-sub">WEBP, JPG, PNG (1200×630px optimal)</span>
                    </div>
                    <div class="dash-dropzone-previews" hidden></div>
                </div>
                @error('og_image_file')<p class="login-error">{{ $message }}</p>@enderror
            </div>
        </div>
        <div class="dash-drawer-foot">
            <button type="button" class="btn" data-close-drawer>Close</button>
            <button type="submit" class="btn primary">Save meta</button>
        </div>
    </form>
</aside>

@foreach ($sections as $section)
    @php
        $hasHtml = collect($section['fields'])->contains(
            fn ($field) => $field->type === \App\PageMeta\FieldType::Html,
        );
    @endphp
    <aside class="dash-drawer{{ $hasHtml ? ' is-wide' : '' }}" id="dash-drawer-{{ $section['key'] }}" hidden>
        <form class="dash-form" method="post" action="{{ route('dashboard.pages.update', $page) }}" enctype="multipart/form-data">
            @csrf
            @method('put')
            <input type="hidden" name="editor_section" value="{{ $section['key'] }}">
            <input type="hidden" name="title" value="{{ $page->title }}">
            <input type="hidden" name="meta_description" value="{{ $page->meta_description }}">
            <input type="hidden" name="status" value="{{ $page->status->value }}">
            <div class="dash-drawer-head">
                <h2>{{ $section['label'] }}</h2>
                <button type="button" class="dash-drawer-close" data-close-drawer aria-label="Close">&times;</button>
            </div>
            <div class="dash-drawer-body">
                @include('dashboard.pages._section-fields', ['section' => $section, 'metaByKey' => $metaByKey])
            </div>
            <div class="dash-drawer-foot">
                <button type="button" class="btn" data-close-drawer>Close</button>
                <button type="submit" class="btn primary">Save section</button>
            </div>
        </form>
    </aside>
@endforeach
@endsection

@push('scripts')
<script>
    window.dashCkeditor = {
        contentsCss: [
            @json(versioned_asset('assets/css/style_demo.css')),
            @json(versioned_asset('assets/css/legal.css')),
            @json(versioned_asset('assets/css/ckeditor-contents.css')),
        ],
    };
</script>
<script src="https://cdn.ckeditor.com/4.22.1/standard/ckeditor.js"></script>
<script src="{{ versioned_asset('assets/js/dashboard-visual.js') }}"></script>
@endpush
