<div class="dash-card">
    <h2>Details</h2>
    <div class="dash-form-grid">
        <div class="dash-field">
            <label for="title">Title <small class="dash-field-rec">(30–60 chars recommended)</small></label>
            <input id="title" name="title" value="{{ old('title', $project->title ?? '') }}" data-counter="chars" data-min="30" data-max="60" required>
            @error('title')<p class="login-error">{{ $message }}</p>@enderror
        </div>

        <div class="dash-field">
            <label for="slug">Slug <small class="dash-field-rec">(20–50 chars recommended)</small></label>
            <input id="slug" name="slug" value="{{ old('slug', $project->slug ?? '') }}" data-counter="chars" data-min="20" data-max="50" placeholder="e.g. eve-hotel-sydney">
            @error('slug')<p class="login-error">{{ $message }}</p>@enderror
        </div>

        <div class="dash-field">
            <label for="tag">Tags <small class="dash-field-rec">(comma-separated for multiple)</small></label>
            <input id="tag" name="tag" value="{{ old('tag', $project->tag ?? '') }}" placeholder="e.g. Hospitality, Commercial, Architectural">
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
            <label for="cover_file">Cover image</label>
            <div class="dash-dropzone" data-image-dropzone>
                @if (!empty($project?->cover))
                    @php
                        $coverInfo = media_file_info($project->cover);
                        $coverAltVal = $project->cover_alt ?? '';
                        $coverFallbackAlt = $project->title ?? 'Project';
                    @endphp
                    <div class="dash-dropzone-previews">
                        <div class="dash-preview-card">
                            <div class="dash-preview-thumb-wrap">
                                <img class="dash-preview-thumb" src="{{ media_url($project->cover) }}" alt="{{ basename($project->cover) }}">
                            </div>
                            <div class="dash-preview-info">
                                <div class="dash-preview-filename" title="{{ basename($project->cover) }}">{{ basename($project->cover) }}</div>
                                <div class="dash-preview-alt-text" title="Alt text for screen readers and search engines">
                                    <span class="dash-preview-alt-label">Alt:</span>
                                    <span class="dash-preview-alt-val" data-cover-alt-val data-default-alt="{{ $coverFallbackAlt }}">{{ $coverAltVal !== '' ? $coverAltVal : 'Auto (' . $coverFallbackAlt . ')' }}</span>
                                </div>
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

        <div class="dash-field is-wide">
            <label for="cover_alt">Cover image alt text</label>
            <input id="cover_alt" name="cover_alt" value="{{ old('cover_alt', $project->cover_alt ?? '') }}" placeholder="e.g. {{ $project->title ?? 'Project' }} architectural lighting installation">
            <small>Descriptive alt text for accessibility and search engines. Defaults to project title if left blank.</small>
            @error('cover_alt')<p class="login-error">{{ $message }}</p>@enderror
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
                    @php
                        $imageInfo = media_file_info($image);
                        $altVal = $project->gallery_alts[$index] ?? ($project->gallery_alts[$image] ?? '');
                        $fallbackAlt = ($project->title ?? 'Project') . ' photo ' . ($index + 1);
                    @endphp
                    <div class="dash-preview-card is-gallery-card" data-gallery-item>
                        <input type="hidden" name="keep_gallery[]" value="{{ $index }}">
                        <div class="dash-preview-card-header">
                            <div class="dash-preview-thumb-wrap">
                                <img class="dash-preview-thumb" src="{{ media_url($image) }}" alt="{{ basename($image) }}">
                            </div>
                            <div class="dash-preview-info">
                                <div class="dash-preview-filename" title="{{ basename($image) }}">{{ basename($image) }}</div>
                                <div class="dash-preview-alt-text" title="Alt text for screen readers and search engines">
                                    <span class="dash-preview-alt-label">Alt:</span>
                                    <span class="dash-preview-alt-val" data-gallery-alt-val data-default-alt="{{ $fallbackAlt }}">{{ $altVal !== '' ? $altVal : 'Auto (' . $fallbackAlt . ')' }}</span>
                                </div>
                                <div class="dash-preview-badges">
                                    <span class="dash-preview-badge is-format">{{ $imageInfo['format'] ?? 'IMAGE' }}</span>
                                    @if (!empty($imageInfo['size']))
                                        <span class="dash-preview-badge">{{ $imageInfo['size'] }}</span>
                                    @endif
                                    @if (!empty($imageInfo['dimensions']))
                                        <span class="dash-preview-badge is-dimensions">{{ $imageInfo['dimensions'] }}</span>
                                    @endif
                                    @if (!empty($imageInfo['aspect_ratio']))
                                        <span class="dash-preview-badge is-ratio">{{ $imageInfo['aspect_ratio'] }}</span>
                                    @endif
                                </div>
                            </div>
                            <div class="dash-preview-actions">
                                <button type="button" class="dash-preview-action" data-toggle-alt-edit title="Edit alt text">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 20h9"/><path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4Z"/></svg>
                                </button>
                                <button type="button" class="dash-preview-remove" data-remove-gallery title="Remove image">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M3 6h18M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2M10 11v6M14 11v6"/></svg>
                                </button>
                            </div>
                        </div>
                        <div class="dash-preview-alt-edit" data-gallery-alt-edit hidden>
                            <input type="text" name="gallery_alts[{{ $index }}]" value="{{ old("gallery_alts.{$index}", $altVal) }}" placeholder="Enter custom alt text..." data-gallery-alt-input>
                        </div>
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
