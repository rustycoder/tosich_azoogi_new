<div class="dash-card">
    <h2>Details</h2>
    <div class="dash-form-grid">
        <div class="dash-field">
            <label for="title">Title <small class="dash-field-rec">(30–60 chars recommended)</small></label>
            <input id="title" name="title" value="{{ old('title', $project->title ?? '') }}" data-counter="chars" data-min="30" data-max="60" required>
            @error('title')<p class="login-error">{{ $message }}</p>@enderror
        </div>

        <div class="dash-field">
            <label for="slug">Slug</label>
            <input id="slug" name="slug" value="{{ old('slug', $project->slug ?? '') }}">
        </div>

        <div class="dash-field">
            <label for="tag">Tag</label>
            <input id="tag" name="tag" value="{{ old('tag', $project->tag ?? '') }}">
        </div>

        <div class="dash-field">
            <label for="location">Location</label>
            <input id="location" name="location" value="{{ old('location', $project->location ?? '') }}">
        </div>

        <div class="dash-field">
            <label for="type">Type</label>
            <input id="type" name="type" value="{{ old('type', $project->type ?? '') }}">
        </div>

        <div class="dash-field">
            <label for="completed">Completed</label>
            <input id="completed" name="completed" value="{{ old('completed', $project->completed ?? '') }}">
        </div>

        <div class="dash-field is-wide">
            <label for="summary">Summary <small class="dash-field-rec">(15–30 words recommended)</small></label>
            <textarea id="summary" name="summary" rows="3" data-counter="words" data-min="15" data-max="30">{{ old('summary', $project->summary ?? '') }}</textarea>
        </div>

        <div class="dash-field is-wide">
            <label for="description">Description <small class="dash-field-rec">(80–250 words recommended)</small></label>
            <textarea id="description" name="description" rows="5" data-counter="words" data-min="80" data-max="250">{{ old('description', $project->description ?? '') }}</textarea>
        </div>

        <div class="dash-field is-wide">
            <label for="cover_remote">Cover remote URL</label>
            <input id="cover_remote" name="cover_remote" value="{{ old('cover_remote', $project->cover_remote ?? '') }}">
        </div>

        <div class="dash-field is-wide">
            <label for="cover_file">Cover image</label>
            <div class="dash-media">
                @if ($project?->cover)
                    <img class="dash-preview" src="{{ media_url($project->cover) }}" alt="">
                @endif
                <input id="cover_file" type="file" name="cover_file" accept="image/*">
            </div>
            @include('dashboard.partials.upload-progress')
            <small>{{ \App\PageMeta\ImageSize::Cover }}</small>
            @error('cover_file')<p class="login-error">{{ $message }}</p>@enderror
        </div>
    </div>
</div>

<div class="dash-card">
    <h2>Gallery</h2>
    <input type="hidden" name="gallery_sync" value="1">
    @if (! empty($project?->gallery))
        <div class="dash-gallery">
            @foreach ($project->gallery as $index => $image)
                @if (is_string($image) && $image !== '')
                    <div class="dash-gallery-item" data-gallery-item>
                        <img src="{{ media_url($image) }}" alt="">
                        <input type="hidden" name="keep_gallery[]" value="{{ $index }}">
                        <button type="button" class="dash-gallery-remove" data-remove-gallery>Remove</button>
                    </div>
                @endif
            @endforeach
        </div>
    @endif
    <div class="dash-field">
        <label for="gallery_files">Add images</label>
        <input id="gallery_files" type="file" name="gallery_files[]" accept="image/*" multiple>
        @include('dashboard.partials.upload-progress')
        <small>{{ \App\PageMeta\ImageSize::Gallery }}</small>
        @error('gallery_files')<p class="login-error">{{ $message }}</p>@enderror
        @error('gallery_files.*')<p class="login-error">{{ $message }}</p>@enderror
    </div>
</div>
