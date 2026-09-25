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
            <div class="dash-dropzone" data-image-dropzone>
                @if ($project?->cover)
                    @php
                        $coverInfo = media_file_info($project->cover);
                    @endphp
                    <div class="dash-dropzone-previews">
                        <div class="dash-preview-card">
                            <div class="dash-preview-thumb-wrap">
                                <img class="dash-preview-thumb" src="{{ media_url($project->cover) }}" alt="{{ basename($project->cover) }}">
                            </div>
                            <div class="dash-preview-info">
                                <div class="dash-preview-filename" title="{{ basename($project->cover) }}">{{ basename($project->cover) }}</div>
                                <div class="dash-preview-badges">
                                    <span class="dash-preview-badge is-format">{{ $coverInfo['format'] ?? 'IMAGE' }}</span>
                                    @if (!empty($coverInfo['size']))
                                        <span class="dash-preview-badge">{{ $coverInfo['size'] }}</span>
                                    @endif
                                    @if (!empty($coverInfo['dimensions']))
                                        <span class="dash-preview-badge is-dimensions">{{ $coverInfo['dimensions'] }}</span>
                                    @endif
                                    @if (!empty($coverInfo['aspect_ratio']))
                                        <span class="dash-preview-badge is-ratio">{{ $coverInfo['aspect_ratio'] }}</span>
                                    @endif
                                    <span class="dash-preview-badge">Current cover</span>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif
                <div class="dash-dropzone-box">
                    <input id="cover_file" class="dash-dropzone-input" type="file" name="cover_file" accept="image/*">
                    <div class="dash-dropzone-icon">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8.5" cy="8.5" r="1.5"/><polyline points="21 15 16 10 5 21"/></svg>
                    </div>
                    <div class="dash-dropzone-text"><strong>Choose a cover image</strong> or drag and drop</div>
                    <span class="dash-dropzone-sub">WEBP, JPG, PNG up to 10MB</span>
                </div>
                <div class="dash-dropzone-previews" hidden></div>
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
        <div class="dash-dropzone" data-image-dropzone>
            <div class="dash-dropzone-box">
                <input id="gallery_files" class="dash-dropzone-input" type="file" name="gallery_files[]" accept="image/*" multiple>
                <div class="dash-dropzone-icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8.5" cy="8.5" r="1.5"/><polyline points="21 15 16 10 5 21"/></svg>
                </div>
                <div class="dash-dropzone-text"><strong>Choose one or more gallery images</strong> or drag and drop</div>
                <span class="dash-dropzone-sub">Upload multiple high-res photos</span>
            </div>
            <div class="dash-dropzone-previews is-grid" hidden></div>
        </div>
        @include('dashboard.partials.upload-progress')
        <small>{{ \App\PageMeta\ImageSize::Gallery }}</small>
        @error('gallery_files')<p class="login-error">{{ $message }}</p>@enderror
        @error('gallery_files.*')<p class="login-error">{{ $message }}</p>@enderror
    </div>
</div>
