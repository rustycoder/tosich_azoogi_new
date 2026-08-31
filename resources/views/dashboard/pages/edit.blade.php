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
            <button type="button" class="btn" data-open-section="settings">Page settings</button>
            <a class="btn" href="{{ $page->publicPath() }}" target="_blank" rel="noopener">View live</a>
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

<aside class="dash-drawer" id="dash-drawer-settings" hidden>
    <form class="dash-form" method="post" action="{{ route('dashboard.pages.update', $page) }}">
        @csrf
        @method('put')
            <input type="hidden" name="editor_section" value="settings">
        <div class="dash-drawer-head">
            <h2>Page settings</h2>
            <button type="button" class="dash-drawer-close" data-close-drawer aria-label="Close">&times;</button>
        </div>
        <div class="dash-drawer-body">
            <div class="dash-field">
                <label for="title">SEO title</label>
                <input id="title" name="title" value="{{ old('title', $page->title) }}" required>
                @error('title')<p class="login-error">{{ $message }}</p>@enderror
            </div>

            <div class="dash-field">
                <label for="meta_description">Meta description</label>
                <textarea id="meta_description" name="meta_description" rows="3">{{ old('meta_description', $page->meta_description) }}</textarea>
            </div>

            <div class="dash-field">
                <label for="status">Status</label>
                <select id="status" name="status">
                    <option value="active" @selected(old('status', $page->status->value) === 'active')>Active</option>
                    <option value="inactive" @selected(old('status', $page->status->value) === 'inactive')>Inactive</option>
                </select>
            </div>
        </div>
        <div class="dash-drawer-foot">
            <button type="submit" class="btn primary">Save settings</button>
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
                <button type="submit" class="btn primary">Save section</button>
            </div>
        </form>
    </aside>
@endforeach
@endsection

@push('scripts')
<script src="https://cdn.ckeditor.com/4.22.1/standard/ckeditor.js"></script>
<script src="{{ asset('assets/js/dashboard-visual.js') }}?v={{ config('app.asset_version') }}"></script>
@endpush
