@extends('layouts.dashboard')

@section('title', 'Documentation & Guides')

@section('content')
<div class="dash-head">
    <div class="dash-head-title">
        <h1>Documentation & Guides</h1>
        <div class="dash-head-actions">
            <span class="dash-doc-badge">Azoogi Operational Handbook</span>
        </div>
    </div>
    <p class="dash-lead">Comprehensive instructions, technical standards, best practices, and operational workflows for managing the Azoogi platform.</p>
</div>

<div class="dash-doc-layout">
    <!-- Topic Navigation Sidebar -->
    <aside class="dash-doc-sidebar">
        <div class="dash-doc-search-wrap">
            <input type="search" id="doc-search" class="dash-doc-search-input" placeholder="Search guides & topics..." aria-label="Search documentation">
            <svg class="dash-doc-search-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                <circle cx="11" cy="11" r="7"/>
                <line x1="21" y1="21" x2="16.65" y2="16.65"/>
            </svg>
        </div>

        <nav class="dash-doc-menu" id="doc-menu">
            @if (isset($sections) && is_array($sections))
                @foreach ($sections as $sectionHeading => $groupTopics)
                    <div class="dash-doc-menu-group" data-doc-group>
                        <div class="dash-doc-group-title">{{ $sectionHeading }}</div>
                        @foreach ($groupTopics as $key => $topicData)
                            @php
                                $label = $topicData['label'] ?? ($topicData['title'] ?? $key);
                                $fullTitle = $topicData['title'] ?? $label;
                                $keywords = $topicData['keywords'] ?? '';
                            @endphp
                            <a href="{{ route('dashboard.docs.index', ['topic' => $key]) }}" 
                               class="dash-doc-menu-item {{ $activeTopic === $key ? 'is-active' : '' }}" 
                               data-topic="{{ $key }}"
                               data-title="{{ strtolower($fullTitle . ' ' . $label) }}"
                               data-keywords="{{ strtolower($keywords) }}">
                                @switch($key)
                                    @case('overview')
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M4 10.5 12 4l8 6.5V20a1 1 0 0 1-1 1h-5v-6H10v6H5a1 1 0 0 1-1-1z"/></svg>
                                        @break
                                    @case('pages')
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                                        @break
                                    @case('formatting')
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M4 7V4h16v3M9 20h6M12 4v16"/></svg>
                                        @break
                                    @case('counters')
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="12" cy="12" r="9"/><polyline points="12 7 12 12 15 15"/></svg>
                                        @break
                                    @case('projects')
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>
                                        @break
                                    @case('products')
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M3 8.5 12 4l9 4.5-9 4.5L3 8.5z"/><path d="M3 8.5v7L12 20l9-4.5v-7M12 13v7"/></svg>
                                        @break
                                    @case('videos')
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><rect x="2" y="2" width="20" height="20" rx="2.18"/><polygon points="10 8 16 12 10 16 10 8"/></svg>
                                        @break
                                    @case('images')
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8.5" cy="8.5" r="1.5"/><polyline points="21 15 16 10 5 21"/></svg>
                                        @break
                                    @case('seo')
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="12" cy="12" r="10"/><line x1="2" y1="12" x2="22" y2="12"/><path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"/></svg>
                                        @break
                                    @case('sitemap')
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><rect x="9" y="3" width="6" height="4" rx="1"/><rect x="3" y="17" width="6" height="4" rx="1"/><rect x="15" y="17" width="6" height="4" rx="1"/><path d="M12 7v5M6 17v-3a2 2 0 0 1 2-2h8a2 2 0 0 1 2 2v3"/></svg>
                                        @break
                                    @case('geo')
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M12 3l1.912 5.813a2 2 0 0 0 1.275 1.275L21 12l-5.813 1.912a2 2 0 0 0-1.275 1.275L12 21l-1.912-5.813a2 2 0 0 0-1.275-1.275L3 12l5.813-1.912a2 2 0 0 0 1.275-1.275L12 3z"/></svg>
                                        @break
                                    @case('enquiries')
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><rect x="6" y="5" width="12" height="15" rx="2"/><path d="M9 5V4h6v1M9 11h6M9 15h4"/></svg>
                                        @break
                                    @case('datasheets')
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M14 3H7a1 1 0 0 0-1 1v16a1 1 0 0 0 1 1h10a1 1 0 0 0 1-1V8z"/><path d="M14 3v5h5M9 13h6M9 17h4"/></svg>
                                        @break
                                    @case('emails')
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg>
                                        @break
                                    @case('staff')
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="9" cy="8" r="3"/><path d="M4 19a5 5 0 0 1 10 0"/><circle cx="17" cy="9" r="2.4"/><path d="M16 19a4.2 4.2 0 0 1 4-3"/></svg>
                                        @break
                                    @case('deployment')
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.7 1.7 0 0 0 .3 1.8l.1.1a2 2 0 1 1-2.8 2.8l-.1-.1a1.7 1.7 0 0 0-1.8-.3 1.7 1.7 0 0 0-1 1.5V21a2 2 0 1 1-4 0v-.1a1.7 1.7 0 0 0-1.1-1.5 1.7 1.7 0 0 0-1.8.3l-.1.1a2 2 0 1 1-2.8-2.8l.1-.1a1.7 1.7 0 0 0 .3-1.8 1.7 1.7 0 0 0-1.5-1H3a2 2 0 1 1 0-4h.1a1.7 1.7 0 0 0 1.5-1.1 1.7 1.7 0 0 0-.3-1.8l-.1-.1a2 2 0 1 1 2.8-2.8l.1.1a1.7 1.7 0 0 0 1.8.3H9a1.7 1.7 0 0 0 1-1.5V3a2 2 0 1 1 4 0v.1a1.7 1.7 0 0 0 1 1.5 1.7 1.7 0 0 0 1.8-.3l.1-.1a2 2 0 1 1 2.8 2.8l-.1.1a1.7 1.7 0 0 0-.3 1.8V9a1.7 1.7 0 0 0 1.5 1H21a2 2 0 1 1 0 4h-.1a1.7 1.7 0 0 0-1.5 1z"/></svg>
                                        @break
                                @endswitch
                                <span>{{ $label }}</span>
                            </a>
                        @endforeach
                    </div>
                @endforeach
            @endif
            <div id="doc-menu-no-results" class="dash-doc-no-results" style="display: none;">
                No matching topics found
            </div>
        </nav>
    </aside>

    <!-- Main Content Body -->
    <div class="dash-doc-body" id="doc-content-body">

        {{-- 1. OVERVIEW & QUICK START --}}
        @if ($activeTopic === 'overview')
            <article class="dash-card dash-doc-section" data-doc-block>
                <div class="dash-doc-header">
                    <h2>Welcome to Azoogi Administration</h2>
                    <span class="dash-pill-active">Quick Start</span>
                </div>
                <p>This backend management system gives administrators and editors direct control over website landing pages, project case studies, Airtable product catalog synchronisation, sales quote enquiries, and customer notification workflows.</p>

                <div class="dash-doc-grid-cards">
                    <div class="dash-doc-feature-card">
                        <div class="dash-doc-icon-wrap">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><rect x="3" y="4" width="18" height="14" rx="2"/><path d="M3 15l5-4 4 3 4-5 5 6"/></svg>
                        </div>
                        <h3>Pages & Sections</h3>
                        <p>Manage in-context copy, hero sliders, feature cards, and brand typography.</p>
                        <a href="{{ route('dashboard.pages.index') }}" class="dash-doc-inline-link">Go to Pages &rarr;</a>
                    </div>

                    <div class="dash-doc-feature-card">
                        <div class="dash-doc-icon-wrap">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>
                        </div>
                        <h3>Featured Projects</h3>
                        <p>Curate architectural lighting case studies, client galleries, and linked fixtures.</p>
                        <a href="{{ route('dashboard.projects.index') }}" class="dash-doc-inline-link">Go to Projects &rarr;</a>
                    </div>

                    <div class="dash-doc-feature-card">
                        <div class="dash-doc-icon-wrap">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M3 8.5 12 4l9 4.5-9 4.5L3 8.5z"/><path d="M3 8.5v7L12 20l9-4.5v-7M12 13v7"/></svg>
                        </div>
                        <h3>Products & Airtable</h3>
                        <p>Sync architectural lighting catalog, technical specs, categories, and images.</p>
                        <a href="{{ route('dashboard.products.index') }}" class="dash-doc-inline-link">Go to Products &rarr;</a>
                    </div>

                    <div class="dash-doc-feature-card">
                        <div class="dash-doc-icon-wrap">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><rect x="6" y="5" width="12" height="15" rx="2"/><path d="M9 5V4h6v1M9 11h6M9 15h4"/></svg>
                        </div>
                        <h3>Enquiries & Quotes</h3>
                        <p>Review customer quote requests, product inquiries, and contact leads in real time.</p>
                        <a href="{{ route('dashboard.enquiries.index') }}" class="dash-doc-inline-link">Go to Enquiries &rarr;</a>
                    </div>
                </div>

                <div class="dash-doc-callout info">
                    <strong>Tip for Editors:</strong> Always optimize images and background videos to the recommended web standards before uploading to keep page speed and Core Web Vitals at peak performance.
                </div>
            </article>
        @endif

        {{-- 2. PAGES & VISUAL EDITOR --}}
        @if ($activeTopic === 'pages')
            <article class="dash-card dash-doc-section" data-doc-block>
                <div class="dash-doc-header">
                    <h2>Page Content & Visual Editor</h2>
                    <span class="dash-pill-active">Content Management</span>
                </div>
                <p>The Pages section enables in-context visual editing and structured content management for all primary website landing pages (Home, Solutions, Casambi, Silvair, Madrix, DALI Centre, AI Lighting, Data Centre, Audience Portals, About, and Contact).</p>

                <div class="dash-doc-steps">
                    <div class="dash-doc-step">
                        <div class="dash-doc-step-num">1</div>
                        <div class="dash-doc-step-content">
                            <h4>Select Page to Edit</h4>
                            <p>Navigate to <a href="{{ route('dashboard.pages.index') }}"><strong>Content &rarr; Pages</strong></a> and click on any page row to open the editor.</p>
                        </div>
                    </div>
                    <div class="dash-doc-step">
                        <div class="dash-doc-step-num">2</div>
                        <div class="dash-doc-step-content">
                            <h4>Update Structured Fields</h4>
                            <p>Modify Hero slide captions, video/poster links, statistics counters, solutions cards, and interactive callouts.</p>
                        </div>
                    </div>
                    <div class="dash-doc-step">
                        <div class="dash-doc-step-num">3</div>
                        <div class="dash-doc-step-content">
                            <h4>Configure Page Metadata & Social Share</h4>
                            <p>Click the <strong>Page Meta</strong> button in the top toolbar to set the SEO Title, Meta Description, and upload a custom <code>1200×630px</code> Open Graph social preview image.</p>
                        </div>
                    </div>
                    <div class="dash-doc-step">
                        <div class="dash-doc-step-num">4</div>
                        <div class="dash-doc-step-content">
                            <h4>Live Preview & Save</h4>
                            <p>Use the <strong>Live preview</strong> link to inspect changes in a real browser tab before publishing.</p>
                        </div>
                    </div>
                </div>

                <div class="dash-doc-callout tip">
                    <strong>Section Toggles:</strong> Individual page sections can be temporarily enabled or disabled using the section visibility toggle switch without deleting content.
                </div>
            </article>
        @endif

        {{-- 3. FORMATTING (CURLY BRACES {} SYNTAX) --}}
        @if ($activeTopic === 'formatting')
            <article class="dash-card dash-doc-section" data-doc-block>
                <div class="dash-doc-header">
                    <h2>Accent & Outlined Text Formatting (Curly Braces <code>{}</code> Syntax)</h2>
                    <span class="dash-pill-active">Styling Standard</span>
                </div>
                <p>Wrap any word or phrase in curly braces <code>{...}</code> within CMS text fields to apply signature visual typography automatically across the site.</p>

                <div class="dash-doc-grid-cards">
                    <div class="dash-doc-feature-card">
                        <div class="dash-doc-icon-wrap" style="color: var(--dash-green-dark);">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M4 7V4h16v3M9 20h6M12 4v16"/></svg>
                        </div>
                        <h4>Hero Slide Titles (Outlined Typography)</h4>
                        <p>Words inside <code>{...}</code> in hero titles (<code>hero.title</code> / slide titles) are rendered in the signature <strong>outlined stroke font</strong>.</p>
                        <div style="margin-top: 10px; font-size: 13px; background: var(--dash-fill); padding: 10px 14px; border-radius: 6px; border: 1px solid var(--dash-border);">
                            <code>Get in {Touch}</code> &rarr; <span style="font-size: 15px;"><strong>Get in</strong> <span style="font-family: serif; -webkit-text-stroke: 1px currentColor; color: transparent; font-weight: 700; letter-spacing: .02em;">Touch</span></span>
                        </div>
                    </div>

                    <div class="dash-doc-feature-card">
                        <div class="dash-doc-icon-wrap" style="color: var(--dash-green-dark);">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="m12 3-1.912 5.813a2 2 0 0 1-1.275 1.275L3 12l5.813 1.912a2 2 0 0 1 1.275 1.275L12 21l1.912-5.813a2 2 0 0 1 1.275-1.275L21 12l-5.813-1.912a2 2 0 0 1-1.275-1.275L12 3Z"/></svg>
                        </div>
                        <h4>Section Titles, Paragraphs & Cards (Brand Accent Color)</h4>
                        <p>Words inside <code>{...}</code> in section titles, paragraphs, subtitles, and cards are highlighted in the vibrant <strong>brand accent color</strong>.</p>
                        <div style="margin-top: 10px; font-size: 13px; background: var(--dash-fill); padding: 10px 14px; border-radius: 6px; border: 1px solid var(--dash-border);">
                            <code>Why Choose {Azoogi}</code> &rarr; <span style="font-size: 15px;"><strong>Why Choose</strong> <span style="color: var(--dash-green-dark); font-weight: 700;">Azoogi</span></span>
                        </div>
                    </div>
                </div>

                <div class="dash-doc-callout info">
                    <strong>Formatting Rules:</strong>
                    <ul>
                        <li>Use single curly braces: <code>{keyword}</code> (not double <code>&#123;&#123;...&#125;&#125;</code>).</li>
                        <li>You can wrap multiple words: <code>{Architectural Lighting}</code>.</li>
                        <li>Braces are automatically stripped from plain-text search results, OpenGraph tags, and sitemaps so search engines see clean text.</li>
                    </ul>
                </div>
            </article>
        @endif

        {{-- 4. COUNTERS --}}
        @if ($activeTopic === 'counters')
            <article class="dash-card dash-doc-section" data-doc-block>
                <div class="dash-doc-header">
                    <h2>Live Character & Word Counters</h2>
                    <span class="dash-pill-active">UX & SEO Assistant</span>
                </div>
                <p>CMS input fields, textareas, and SEO metadata forms feature real-time character and word count meters to ensure content fits responsive typography containers and aligns with search engine standards.</p>

                <div class="dash-doc-grid-cards">
                    <div class="dash-doc-feature-card">
                        <h4>Recommendation Badges</h4>
                        <p>Target guidelines (e.g. <em>(50–60 chars recommended)</em> for SEO Titles or <em>(15–30 words recommended)</em> for Section Subtitles) clarify ideal text density at a glance.</p>
                    </div>

                    <div class="dash-doc-feature-card">
                        <h4>Dynamic Status Indicators</h4>
                        <p>Indicators update on every keystroke: turning <span style="color: var(--dash-green-dark); font-weight: 600;">green (Optimal)</span> when within the target zone, showing remaining counts when under, or alerting <span style="color: #c4453c; font-weight: 600;">(+X over limit)</span> when exceeding maximums.</p>
                    </div>
                </div>

                <div class="dash-table-wrap" style="margin-top: 14px;">
                    <table class="dash-doc-table">
                        <thead>
                            <tr>
                                <th>Field Type</th>
                                <th>Recommended Length</th>
                                <th>Reasoning</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td><strong>Page Title (SEO)</strong></td>
                                <td><code>50 – 60 characters</code></td>
                                <td>Prevents Google search snippet truncation.</td>
                            </tr>
                            <tr>
                                <td><strong>Meta Description</strong></td>
                                <td><code>140 – 160 characters</code></td>
                                <td>Optimizes search result click-through rates across mobile and desktop.</td>
                            </tr>
                            <tr>
                                <td><strong>Hero Slide Title</strong></td>
                                <td><code>3 – 7 words</code></td>
                                <td>Maintains visual impact on widescreen viewports and mobile devices.</td>
                            </tr>
                            <tr>
                                <td><strong>Section Lead / Subtitle</strong></td>
                                <td><code>15 – 30 words</code></td>
                                <td>Ensures scannability without overwhelming visitors.</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </article>
        @endif

        {{-- 5. PROJECTS & CASE STUDIES --}}
        @if ($activeTopic === 'projects')
            <article class="dash-card dash-doc-section" data-doc-block>
                <div class="dash-doc-header">
                    <h2>Project Showcase & Case Studies</h2>
                    <span class="dash-pill-active">Portfolio Curation</span>
                </div>
                <p>Curate architectural lighting project case studies under <a href="{{ route('dashboard.projects.index') }}"><strong>Content &rarr; Projects</strong></a>.</p>

                <div class="dash-doc-specs-grid">
                    <div class="dash-doc-spec-item">
                        <span class="spec-label">Reordering</span>
                        <span class="spec-value">Drag and drop project rows or update position indices to control the exact sorting order on the public <code>/projects</code> page.</span>
                    </div>
                    <div class="dash-doc-spec-item">
                        <span class="spec-label">Featured Star</span>
                        <span class="spec-value">Toggle the star icon to feature flagship architectural installations directly on the home page showcase grid.</span>
                    </div>
                    <div class="dash-doc-spec-item">
                        <span class="spec-label">Fixture Linking</span>
                        <span class="spec-value">Associate specific Azoogi products with project case studies so visitors can click through to product datasheets.</span>
                    </div>
                    <div class="dash-doc-spec-item">
                        <span class="spec-label">Image Format</span>
                        <span class="spec-value"><code>1200 x 800 px</code> (3:2) WebP images for optimal photography sharpness.</span>
                    </div>
                </div>

                <div class="dash-doc-callout tip">
                    <strong>Auto-Sitemap Invalidation:</strong> Whenever a project is created, updated, or deleted, the public XML sitemap (<code>/sitemap.xml</code>) and AI feeds (<code>/llms.txt</code>) are automatically invalidated and refreshed.
                </div>
            </article>
        @endif

        {{-- 6. PRODUCT CATALOG & AIRTABLE --}}
        @if ($activeTopic === 'products')
            <article class="dash-card dash-doc-section" data-doc-block>
                <div class="dash-doc-header">
                    <h2>Product Catalog & Airtable Sync</h2>
                    <span class="dash-pill-active">Automated Pipeline</span>
                </div>
                <p>Product specifications, variants, category hierarchies, images, and datasheets are managed in Airtable and synced seamlessly to the website database.</p>

                <div class="dash-doc-callout info">
                    <strong>How Synchronization Works:</strong> When you trigger a sync from <a href="{{ route('dashboard.products.index') }}"><strong>Products</strong></a>, the backend connects securely to Airtable, fetches all active product records, updates technical specifications, downloads new media assets to local storage, and rebuilds the product cache.
                </div>

                <h3>Sync Modes</h3>
                <div class="dash-doc-grid-cards">
                    <div class="dash-doc-feature-card">
                        <h4>Live Stream Sync</h4>
                        <p>Displays real-time progress messages (fetched count, processed items, downloaded images) in an active terminal box.</p>
                    </div>
                    <div class="dash-doc-feature-card">
                        <h4>Background Sync</h4>
                        <p>Processes product updates asynchronously via background jobs, allowing you to navigate away freely.</p>
                    </div>
                </div>

                <h3>Troubleshooting Sync Issues</h3>
                <ul class="dash-doc-list">
                    <li><strong>Missing Product Images</strong>: Ensure the image field in Airtable contains valid attachments and that <code>php artisan storage:link</code> has been generated on the server.</li>
                    <li><strong>Duplicate Slugs</strong>: Ensure product codes and names in Airtable are unique to avoid URL routing collisions.</li>
                </ul>
            </article>
        @endif

        {{-- 7. VIDEOS & ENCODING --}}
        @if ($activeTopic === 'videos')
            <article class="dash-card dash-doc-section" data-doc-block>
                <div class="dash-doc-header">
                    <h2>Video Guidelines & Encoding Standards</h2>
                    <span class="dash-pill-active">Performance Standard</span>
                </div>
                <p>Background videos create immersive visuals but must be properly encoded to guarantee instant autoplay without delaying page interactivity or consuming excessive mobile bandwidth.</p>

                <div class="dash-doc-specs-grid">
                    <div class="dash-doc-spec-item">
                        <span class="spec-label">Container Format</span>
                        <span class="spec-value"><code>.mp4</code> (Mandatory for Safari & iOS support)</span>
                    </div>
                    <div class="dash-doc-spec-item">
                        <span class="spec-label">Video Codec</span>
                        <span class="spec-value"><strong>H.264</strong> (Baseline or Main Profile)</span>
                    </div>
                    <div class="dash-doc-spec-item">
                        <span class="spec-label">Target File Size</span>
                        <span class="spec-value"><strong>2 MB – 5 MB</strong> (Never exceed 8 MB)</span>
                    </div>
                    <div class="dash-doc-spec-item">
                        <span class="spec-label">Audio Track</span>
                        <span class="spec-value"><strong>Strip Audio Completely</strong> (Required for mobile autoplay)</span>
                    </div>
                    <div class="dash-doc-spec-item">
                        <span class="spec-label">Resolution</span>
                        <span class="spec-value"><code>1920x1080</code> (1080p) or <code>1280x720</code> (720p)</span>
                    </div>
                    <div class="dash-doc-spec-item">
                        <span class="spec-label">Streaming Flag</span>
                        <span class="spec-value"><code>+faststart</code> (Enables progressive streaming)</span>
                    </div>
                </div>

                <div class="dash-doc-callout alert">
                    <strong>Mandatory Poster Image:</strong> Every hero video upload must have a corresponding <code>.webp</code> Poster Image. This prevents black or empty boxes while the video stream is buffering.
                </div>

                <h3>Quick ffmpeg Optimization Command</h3>
                <p>Run this command in your terminal to optimize any raw video file for web delivery:</p>
                <div class="dash-doc-code-block">
                    <pre><code>ffmpeg -i input_video.mp4 \
  -vcodec libx264 \
  -crf 26 \
  -preset slow \
  -an \
  -movflags +faststart \
  -vf "scale=1920:1080:force_original_aspect_ratio=decrease,pad=1920:1080:(ow-iw)/2:(oh-ih)/2" \
  output_optimized.mp4</code></pre>
                    <button type="button" class="dash-doc-copy-btn" data-copy-text='ffmpeg -i input_video.mp4 -vcodec libx264 -crf 26 -preset slow -an -movflags +faststart -vf "scale=1920:1080:force_original_aspect_ratio=decrease,pad=1920:1080:(ow-iw)/2:(oh-ih)/2" output_optimized.mp4'>Copy Command</button>
                </div>
            </article>
        @endif

        {{-- 8. IMAGES & WEBP --}}
        @if ($activeTopic === 'images')
            <article class="dash-card dash-doc-section" data-doc-block>
                <div class="dash-doc-header">
                    <h2>Image Dimensions & WebP Optimization</h2>
                    <span class="dash-pill-active">Image Standard</span>
                </div>
                <p>Follow these resolution and file size limits before uploading assets to ensure fast page loads and sharp retina rendering.</p>

                <div class="dash-table-wrap">
                    <table class="dash-doc-table">
                        <thead>
                            <tr>
                                <th>Placement</th>
                                <th>Recommended Dimensions</th>
                                <th>Max Target File Size</th>
                                <th>Preferred Format</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td><strong>Hero Banners & Posters</strong></td>
                                <td><code>1920 x 1080 px</code> (16:9)</td>
                                <td>&lt; 200 KB</td>
                                <td><span class="dash-pill-active">.webp</span></td>
                            </tr>
                            <tr>
                                <td><strong>Project Showcase Photos</strong></td>
                                <td><code>1200 x 800 px</code> (3:2)</td>
                                <td>&lt; 120 KB</td>
                                <td><span class="dash-pill-active">.webp</span> / .jpg</td>
                            </tr>
                            <tr>
                                <td><strong>Product Thumbnails</strong></td>
                                <td><code>600 x 600 px</code> (1:1)</td>
                                <td>&lt; 60 KB</td>
                                <td><span class="dash-pill-active">.webp</span></td>
                            </tr>
                            <tr>
                                <td><strong>Social Share (OG Images)</strong></td>
                                <td><code>1200 x 630 px</code> (1.91:1)</td>
                                <td>&lt; 150 KB</td>
                                <td><span class="dash-pill-active">.webp</span> / .jpg</td>
                            </tr>
                            <tr>
                                <td><strong>Brand Logos & Badges</strong></td>
                                <td>Vector or <code>400 x 120 px</code></td>
                                <td>&lt; 30 KB</td>
                                <td><span class="dash-pill-active">.svg</span> / .png</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div class="dash-doc-callout tip">
                    <strong>Recommended Free Optimization Tools:</strong>
                    <ul>
                        <li><a href="https://squoosh.app/" target="_blank" rel="noopener noreferrer">Squoosh.app</a> &mdash; Best for converting photos to high-efficiency WebP.</li>
                        <li><a href="https://tinypng.com/" target="_blank" rel="noopener noreferrer">TinyPNG</a> &mdash; Fast bulk compression.</li>
                        <li><a href="https://handbrake.fr/" target="_blank" rel="noopener noreferrer">HandBrake</a> &mdash; Free desktop app for video web optimization.</li>
                    </ul>
                </div>
            </article>
        @endif

        {{-- 9. SEO & SOCIAL SHARE (OG) --}}
        @if ($activeTopic === 'seo')
            <article class="dash-card dash-doc-section" data-doc-block>
                <div class="dash-doc-header">
                    <h2>SEO & Social Share (OG Metadata)</h2>
                    <span class="dash-pill-active">Search Engine Optimization</span>
                </div>
                <p>Azoogi incorporates modern technical SEO architecture to rank prominently on Google, Bing, and social sharing platforms.</p>

                <h3>SEO Checklist & Best Practices</h3>
                <ul class="dash-doc-list">
                    <li><strong>Page Titles</strong>: Keep between 50 &ndash; 60 characters with brand suffix (e.g. <em>Smart Architectural Lighting Solutions — Azoogi</em>). Configured via the <em>Page Meta</em> drawer.</li>
                    <li><strong>Meta Descriptions</strong>: Keep between 140 &ndash; 160 characters summarizing the core value proposition with an action-oriented CTA.</li>
                    <li><strong>Social Share Images (OG Images)</strong>: Upload high-res <code>1200×630px</code> WebP/JPG images per page. These automatically appear when links are shared on LinkedIn, WhatsApp, Slack, iMessage, and X/Twitter.</li>
                    <li><strong>Canonical URLs</strong>: Automatically generated for each page to prevent duplicate content indexing.</li>
                    <li><strong>Structured Data (Schema.org)</strong>: Automated <code>Organization</code> and <code>Product</code> JSON-LD schemas power Google rich snippets and search knowledge graphs.</li>
                    <li><strong>Image Alt Text</strong>: Always include descriptive alt text for product and project imagery for screen readers and Google Image search ranking.</li>
                </ul>

                <div class="dash-doc-callout info">
                    <strong>Social Preview Verification:</strong> Test social share image rendering using the official <a href="https://www.linkedin.com/post-inspector/" target="_blank" rel="noopener noreferrer">LinkedIn Post Inspector</a> or <a href="https://cards-dev.twitter.com/validator" target="_blank" rel="noopener noreferrer">Twitter Card Validator</a>.
                </div>
            </article>
        @endif

        {{-- 10. XML SITEMAP --}}
        @if ($activeTopic === 'sitemap')
            <article class="dash-card dash-doc-section" data-doc-block>
                <div class="dash-doc-header">
                    <h2>XML Sitemap (<code>/sitemap.xml</code>)</h2>
                    <span class="dash-pill-active">Search Engine Indexing</span>
                </div>
                <p>The platform automatically compiles and serves a standards-compliant XML sitemap at <a href="{{ url('/sitemap.xml') }}" target="_blank" class="dash-doc-inline-link"><strong>/sitemap.xml</strong></a> for search engines (Googlebot, Bingbot, Yandex).</p>

                <div class="dash-doc-specs-grid">
                    <div class="dash-doc-spec-item">
                        <span class="spec-label">Sitemap URL</span>
                        <span class="spec-value"><code>{{ url('/sitemap.xml') }}</code></span>
                    </div>
                    <div class="dash-doc-spec-item">
                        <span class="spec-label">Standard</span>
                        <span class="spec-value"><strong>sitemaps.org 0.9</strong> Schema</span>
                    </div>
                    <div class="dash-doc-spec-item">
                        <span class="spec-label">Caching Policy</span>
                        <span class="spec-value"><strong>24 Hours (Sub-5ms response)</strong></span>
                    </div>
                    <div class="dash-doc-spec-item">
                        <span class="spec-label">Content Type</span>
                        <span class="spec-value"><code>application/xml; charset=utf-8</code></span>
                    </div>
                </div>

                <div class="dash-table-wrap" style="margin-top: 14px;">
                    <table class="dash-doc-table">
                        <thead>
                            <tr>
                                <th>Section / URL Type</th>
                                <th>Included URLs</th>
                                <th>Priority</th>
                                <th>Changefreq</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td><strong>Homepage</strong></td>
                                <td><code>/</code></td>
                                <td><span class="dash-pill-active">1.0</span></td>
                                <td>daily</td>
                            </tr>
                            <tr>
                                <td><strong>Catalog & Solutions</strong></td>
                                <td><code>/products</code>, <code>/solutions</code></td>
                                <td><span class="dash-pill-active">0.9</span></td>
                                <td>weekly</td>
                            </tr>
                            <tr>
                                <td><strong>Tech Pages, Categories & Products</strong></td>
                                <td><code>/casambi</code>, <code>/silvair</code>, <code>/madrix</code>, <code>/dali-centre</code>, <code>/ai-lighting</code>, <code>/data-centre</code>, <code>/products?category=...</code>, <code>/products/{slug}</code></td>
                                <td><span class="dash-pill-active">0.8</span></td>
                                <td>weekly</td>
                            </tr>
                            <tr>
                                <td><strong>Projects & Audience Pages</strong></td>
                                <td><code>/projects</code>, <code>/project-detail?slug=...</code>, <code>/architect-designer</code>, <code>/electrician-builder</code>, <code>/home-owner</code>, <code>/wholesaler</code>, <code>/about</code>, <code>/contact</code></td>
                                <td><span class="dash-pill-active">0.7</span></td>
                                <td>monthly</td>
                            </tr>
                            <tr>
                                <td><strong>Legal & Policies</strong></td>
                                <td><code>/privacy</code>, <code>/terms</code>, <code>/warranty-returns</code>, <code>/modern-slavery</code></td>
                                <td><span class="dash-pill-active">0.3</span></td>
                                <td>yearly</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div class="dash-doc-callout tip">
                    <strong>Automated Real-Time Cache Invalidation:</strong> The sitemap cache is automatically purged and refreshed whenever:
                    <ul>
                        <li>A page or section is updated or toggled in <strong>Content &rarr; Pages / Sections</strong>.</li>
                        <li>An Airtable product catalog sync finishes successfully in <strong>Products</strong>.</li>
                        <li>A showcase project is created, edited, reordered, or deleted in <strong>Projects</strong>.</li>
                    </ul>
                </div>

                <h4>Sitemap CLI Commands</h4>
                <p>You can pre-warm or manually clear the XML sitemap cache via Artisan terminal commands:</p>
                <div class="dash-doc-code-block" style="margin-bottom: 12px;">
                    <pre><code># Pre-warm & regenerate sitemap cache
php artisan sitemap:generate</code></pre>
                    <button type="button" class="dash-doc-copy-btn" data-copy-text="php artisan sitemap:generate">Copy Command</button>
                </div>
                <div class="dash-doc-code-block">
                    <pre><code># Clear sitemap cache immediately
php artisan sitemap:generate --clear</code></pre>
                    <button type="button" class="dash-doc-copy-btn" data-copy-text="php artisan sitemap:generate --clear">Copy Command</button>
                </div>
            </article>
        @endif

        {{-- 11. GENERATIVE ENGINE OPTIMIZATION (GEO) --}}
        @if ($activeTopic === 'geo')
            <article class="dash-card dash-doc-section" data-doc-block>
                <div class="dash-doc-header">
                    <h2>Generative Engine Optimization (GEO & <code>/llms.txt</code>)</h2>
                    <span class="dash-pill-active">AI Search Feeds</span>
                </div>
                <p>To maximize brand citations, product discovery, and authoritative source inclusion in AI search engines (<strong>ChatGPT Search, Perplexity AI, Claude, Google Gemini / AI Overviews, Microsoft Copilot</strong>), the platform serves token-optimized, machine-readable Markdown feeds.</p>

                <div class="dash-doc-specs-grid">
                    <div class="dash-doc-spec-item">
                        <span class="spec-label">LLM Summary Feed</span>
                        <span class="spec-value"><a href="{{ url('/llms.txt') }}" target="_blank" class="dash-doc-inline-link"><code>{{ url('/llms.txt') }}</code></a></span>
                    </div>
                    <div class="dash-doc-spec-item">
                        <span class="spec-label">Full Technical Feed</span>
                        <span class="spec-value"><a href="{{ url('/llms-full.txt') }}" target="_blank" class="dash-doc-inline-link"><code>{{ url('/llms-full.txt') }}</code></a></span>
                    </div>
                    <div class="dash-doc-spec-item">
                        <span class="spec-label">AI Crawlers Allowed</span>
                        <span class="spec-value"><strong>GPTBot, PerplexityBot, ClaudeBot, Google-Extended, Bingbot</strong></span>
                    </div>
                    <div class="dash-doc-spec-item">
                        <span class="spec-label">Knowledge Graph</span>
                        <span class="spec-value"><strong>Schema.org Multi-Entity Graph (Org + WebSite)</strong></span>
                    </div>
                </div>

                <div class="dash-doc-callout tip" style="margin-top: 14px;">
                    <strong>Unified GEO & Sitemap Command:</strong> Pre-warm or refresh all search engine and AI feeds simultaneously:
                </div>

                <div class="dash-doc-code-block">
                    <pre><code># Pre-warm /llms.txt, /llms-full.txt and /sitemap.xml
php artisan geo:generate

# Clear all GEO & sitemap caches
php artisan geo:generate --clear</code></pre>
                    <button type="button" class="dash-doc-copy-btn" data-copy-text="php artisan geo:generate">Copy Command</button>
                </div>
            </article>
        @endif

        {{-- 12. ENQUIRIES & QUOTE WORKFLOW --}}
        @if ($activeTopic === 'enquiries')
            <article class="dash-card dash-doc-section" data-doc-block>
                <div class="dash-doc-header">
                    <h2>Enquiries & Lead Management</h2>
                    <span class="dash-pill-active">Sales Workflow</span>
                </div>
                <p>Customer submissions from the website are captured in real time under <a href="{{ route('dashboard.enquiries.index') }}"><strong>Enquiries</strong></a>.</p>

                <div class="dash-doc-specs-grid">
                    <div class="dash-doc-spec-item">
                        <span class="spec-label">Quote Enquiries</span>
                        <span class="spec-value">Contains selected product fixtures, quantities, project requirements, and uploaded architectural plans.</span>
                    </div>
                    <div class="dash-doc-spec-item">
                        <span class="spec-label">Product Inquiries</span>
                        <span class="spec-value">Direct technical or commercial questions submitted from individual product specification pages.</span>
                    </div>
                    <div class="dash-doc-spec-item">
                        <span class="spec-label">Contact Submissions</span>
                        <span class="spec-value">General inquiries from the contact page.</span>
                    </div>
                </div>

                <h3>Status Lifecycle</h3>
                <div class="dash-table-wrap">
                    <table class="dash-doc-table">
                        <thead>
                            <tr>
                                <th>Status</th>
                                <th>Meaning & Recommended Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td><span class="dash-pill-pending">Pending / New</span></td>
                                <td>Recently submitted lead awaiting assignment or initial review.</td>
                            </tr>
                            <tr>
                                <td><span class="dash-pill-active">In Progress</span></td>
                                <td>Sales engineer is actively drafting lighting layout or pricing.</td>
                            </tr>
                            <tr>
                                <td><span class="dash-pill-yes">Resolved / Quoted</span></td>
                                <td>Formal quote or response has been delivered to client.</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </article>
        @endif

        {{-- 13. DATASHEETS --}}
        @if ($activeTopic === 'datasheets')
            <article class="dash-card dash-doc-section" data-doc-block>
                <div class="dash-doc-header">
                    <h2>Datasheet Generation & Export Logs</h2>
                    <span class="dash-pill-active">Technical Spec Sheets</span>
                </div>
                <p>The Azoogi platform dynamically generates downloadable PDF spec sheets for architects, interior designers, and electrical contractors.</p>

                <div class="dash-doc-callout tip">
                    <strong>Export Monitoring:</strong> In <a href="{{ route('dashboard.datasheets.exports') }}"><strong>Datasheets &rarr; Exports</strong></a>, you can inspect which lighting products are being downloaded most frequently and review recent generation timestamps.
                </div>
            </article>
        @endif

        {{-- 14. EMAIL TEMPLATES --}}
        @if ($activeTopic === 'emails')
            <article class="dash-card dash-doc-section" data-doc-block>
                <div class="dash-doc-header">
                    <h2>Email Templates & Customer Alerts</h2>
                    <span class="dash-pill-active">Notification Engine</span>
                </div>
                <p>All automated transaction emails (Quote confirmations, Contact receipts, Staff notifications) are manageable in <a href="{{ route('dashboard.email-templates.index') }}"><strong>Notification &rarr; Email</strong></a>.</p>

                <h3>Best Practices for Editing Templates</h3>
                <ul class="dash-doc-list">
                    <li><strong>Use Dynamic Variables</strong>: Incorporate placeholders like <code>&#123;&#123; name &#125;&#125;</code>, <code>&#123;&#123; email &#125;&#125;</code>, <code>&#123;&#123; enquiry_id &#125;&#125;</code> to personalize notifications.</li>
                    <li><strong>Send a Test Email First</strong>: Use the <em>Send Test Email</em> button to verify rendering in your inbox before publishing template edits.</li>
                    <li><strong>Reset to System Default</strong>: If formatting breaks, use the <em>Reset</em> action to restore the tested default template.</li>
                </ul>
            </article>
        @endif

        {{-- 15. STAFF & PERMISSIONS --}}
        @if ($activeTopic === 'staff')
            <article class="dash-card dash-doc-section" data-doc-block>
                <div class="dash-doc-header">
                    <h2>Staff Administration & Role Permissions</h2>
                    <span class="dash-pill-active">Access Control</span>
                </div>
                <p>Administrators can manage internal team members under <a href="{{ route('dashboard.staff.index') }}"><strong>Administration &rarr; Staff</strong></a>.</p>

                <div class="dash-table-wrap">
                    <table class="dash-doc-table">
                        <thead>
                            <tr>
                                <th>Role / Permission</th>
                                <th>Capabilities</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td><strong>Administrator</strong></td>
                                <td>Full system control: Staff accounts, email templates, system settings, plus all content & enquiries.</td>
                            </tr>
                            <tr>
                                <td><strong>Staff (Custom Permissions)</strong></td>
                                <td>Granular access controlled by checkboxes:
                                    <ul>
                                        <li><code>can.manage:products</code> &mdash; Airtable sync & product catalog</li>
                                        <li><code>can.manage:projects</code> &mdash; Featured project showcase</li>
                                        <li><code>can.manage:enquiries</code> &mdash; Quotes, product & contact leads</li>
                                        <li><code>can.manage:datasheet</code> &mdash; Datasheet export logs</li>
                                    </ul>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </article>
        @endif

        {{-- 16. SERVER SETUP & DEPLOYMENT --}}
        @if ($activeTopic === 'deployment')
            <article class="dash-card dash-doc-section" data-doc-block>
                <div class="dash-doc-header">
                    <h2>Server Setup, Environment & Deployment</h2>
                    <span class="dash-pill-active">Engineering & Ops</span>
                </div>
                <p>Comprehensive environment configuration standards, external API integrations, database connectivity, and deployment protocols for staging and production hosting.</p>

                <h3>1. Database Configuration (<code>DB_*</code>)</h3>
                <p>The platform supports <strong>SQLite</strong> (recommended for lightweight local development) as well as <strong>MySQL / MariaDB</strong> and <strong>PostgreSQL</strong> for high-concurrency production deployments.</p>

                <div class="dash-doc-grid-cards" style="margin-bottom: 12px;">
                    <div class="dash-doc-feature-card">
                        <h4>MySQL / MariaDB (Production)</h4>
                        <div class="dash-doc-code-block" style="margin-top: 8px;">
                            <pre><code>DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=azoogi_production
DB_USERNAME=azoogi_dbuser
DB_PASSWORD=your_secure_db_password</code></pre>
                        </div>
                    </div>
                    <div class="dash-doc-feature-card">
                        <h4>SQLite (Local Dev / Staging)</h4>
                        <div class="dash-doc-code-block" style="margin-top: 8px;">
                            <pre><code>DB_CONNECTION=sqlite
# Database file location:
# database/database.sqlite</code></pre>
                        </div>
                    </div>
                </div>

                <p>Run migrations on newly deployed instances:</p>
                <div class="dash-doc-code-block" style="margin-bottom: 16px;">
                    <pre><code># Run all database schema migrations
php artisan migrate --force</code></pre>
                    <button type="button" class="dash-doc-copy-btn" data-copy-text="php artisan migrate --force">Copy Command</button>
                </div>

                <h3>2. Airtable API Configuration (<code>AIRTABLE_*</code>)</h3>
                <p>Product catalog data, categories, and technical specification attributes synchronize with Airtable through personal access tokens (PAT).</p>

                <div class="dash-doc-code-block" style="margin-bottom: 12px;">
                    <pre><code># Airtable Credentials
AIRTABLE_API_KEY=pat_your_personal_access_token_here
AIRTABLE_BASE_ID=app_your_airtable_base_id_here

# Table Names (Matches Airtable Table Names Exactly)
AIRTABLE_PRODUCTS_TABLE="Products"
AIRTABLE_CATEGORIES_TABLE="Categories"
AIRTABLE_ATTRIBUTES_TABLE="Product attributes"</code></pre>
                    <button type="button" class="dash-doc-copy-btn" data-copy-text='AIRTABLE_API_KEY=your_token
AIRTABLE_BASE_ID=your_base_id
AIRTABLE_PRODUCTS_TABLE="Products"
AIRTABLE_CATEGORIES_TABLE="Categories"
AIRTABLE_ATTRIBUTES_TABLE="Product attributes"'>Copy Template</button>
                </div>

                <div class="dash-doc-callout info">
                    <strong>Airtable Token Permissions:</strong> Ensure your Airtable Personal Access Token has the <code>data.records:read</code> and <code>schema.bases:read</code> scopes assigned for the target Base.
                </div>

                <p>You can run manual product synchronization via the CLI or use the Dashboard UI under <a href="{{ route('dashboard.products.index') }}" class="dash-doc-inline-link"><strong>Products</strong></a>:</p>
                <div class="dash-doc-code-block" style="margin-bottom: 16px;">
                    <pre><code># Trigger CLI product catalog sync
php artisan products:sync</code></pre>
                    <button type="button" class="dash-doc-copy-btn" data-copy-text="php artisan products:sync">Copy Command</button>
                </div>

                <h3>3. Email & Transactional Notification Setup (<code>MAIL_*</code>)</h3>
                <p>Automated customer quote receipts, enquiry dispatch, and staff notification emails can be delivered via SMTP, Amazon SES, Resend, or Postmark.</p>

                <div class="dash-doc-grid-cards" style="margin-bottom: 12px;">
                    <div class="dash-doc-feature-card">
                        <h4>Standard SMTP Configuration</h4>
                        <div class="dash-doc-code-block" style="margin-top: 8px;">
                            <pre><code>MAIL_MAILER=smtp
MAIL_HOST=smtp.mailgun.org
MAIL_PORT=587
MAIL_ENCRYPTION=tls
MAIL_USERNAME=your_smtp_username
MAIL_PASSWORD=your_smtp_password
MAIL_FROM_ADDRESS="sales@azoogi.com"
MAIL_FROM_NAME="Azoogi"</code></pre>
                        </div>
                    </div>
                    <div class="dash-doc-feature-card">
                        <h4>Amazon SES / API Providers</h4>
                        <div class="dash-doc-code-block" style="margin-top: 8px;">
                            <pre><code>MAIL_MAILER=ses
AWS_ACCESS_KEY_ID=your_aws_key
AWS_SECRET_ACCESS_KEY=your_aws_secret
AWS_DEFAULT_REGION=ap-southeast-2
MAIL_FROM_ADDRESS="sales@azoogi.com"
MAIL_FROM_NAME="Azoogi"</code></pre>
                        </div>
                    </div>
                </div>

                <div class="dash-doc-callout tip">
                    <strong>Testing Email Delivery:</strong> Navigate to <a href="{{ route('dashboard.email-templates.index') }}" class="dash-doc-inline-link"><strong>Notifications &rarr; Email</strong></a> and click <em>Send Test Email</em> to verify outgoing deliverability.
                </div>

                <h3>4. Cloudflare Turnstile CAPTCHA (<code>TURNSTILE_*</code>)</h3>
                <p>Spam protection for public quote requests and contact forms uses Cloudflare Turnstile:</p>
                <div class="dash-doc-code-block" style="margin-bottom: 16px;">
                    <pre><code>TURNSTILE_SITE_KEY=your_turnstile_site_key
TURNSTILE_SECRET_KEY=your_turnstile_secret_key
TURNSTILE_ENABLED=true</code></pre>
                </div>

                <h3>5. Public Storage Symlink</h3>
                <p>If uploaded media, hero videos, or product attachments show broken links on a new environment, verify the public storage symlink:</p>
                <div class="dash-doc-code-block" style="margin-bottom: 16px;">
                    <pre><code>php artisan storage:link</code></pre>
                    <button type="button" class="dash-doc-copy-btn" data-copy-text="php artisan storage:link">Copy Command</button>
                </div>

                <h3>6. CSS & JavaScript Asset Versioning</h3>
                <p>When deploying updates to front-end styles or scripts, bump the asset cache version using the project utility script:</p>
                <div class="dash-doc-code-block" style="margin-bottom: 16px;">
                    <pre><code>python update_version.py bump</code></pre>
                    <button type="button" class="dash-doc-copy-btn" data-copy-text="python update_version.py bump">Copy Command</button>
                </div>

                <h3>7. Production Deployment Cache Checklist</h3>
                <p>After pushing code updates to production servers, optimize Laravel configuration, routing, and search caches:</p>
                <div class="dash-doc-code-block">
                    <pre><code>php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan geo:generate</code></pre>
                    <button type="button" class="dash-doc-copy-btn" data-copy-text="php artisan config:cache && php artisan route:cache && php artisan view:cache && php artisan geo:generate">Copy Command</button>
                </div>
            </article>
        @endif

    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    // Quick Search filter
    const searchInput = document.getElementById('doc-search');
    const menuItems = document.querySelectorAll('.dash-doc-menu-item');
    const noResults = document.getElementById('doc-menu-no-results');
    const contentBlocks = document.querySelectorAll('[data-doc-block]');

    if (searchInput) {
        const performSearch = () => {
            const query = searchInput.value.toLowerCase().trim();
            const terms = query.split(/\s+/).filter(Boolean);
            let visibleCount = 0;

            // 1. Filter sidebar navigation topics
            menuItems.forEach(item => {
                if (terms.length === 0) {
                    item.style.display = '';
                    visibleCount++;
                    return;
                }

                const title = item.getAttribute('data-title') || '';
                const keywords = item.getAttribute('data-keywords') || '';
                const topic = item.getAttribute('data-topic') || '';
                const itemText = (title + ' ' + keywords + ' ' + topic).toLowerCase();

                const isMatch = terms.every(term => itemText.includes(term));
                if (isMatch) {
                    item.style.display = '';
                    visibleCount++;
                } else {
                    item.style.display = 'none';
                }
            });

            // 2. Hide group headings if all items in that group are filtered out
            document.querySelectorAll('[data-doc-group]').forEach(group => {
                const groupItems = group.querySelectorAll('.dash-doc-menu-item');
                const hasVisible = Array.from(groupItems).some(item => item.style.display !== 'none');
                group.style.display = (terms.length === 0 || hasVisible) ? '' : 'none';
            });

            if (noResults) {
                noResults.style.display = (terms.length > 0 && visibleCount === 0) ? 'block' : 'none';
            }

            // 3. Filter sub-sections and cards inside the currently active document
            contentBlocks.forEach(block => {
                if (terms.length === 0) {
                    block.style.display = '';
                    return;
                }
                const blockText = block.textContent.toLowerCase();
                const matchesBlock = terms.some(term => blockText.includes(term));
                block.style.display = matchesBlock || visibleCount > 0 ? '' : 'none';
            });
        };

        searchInput.addEventListener('input', performSearch);

        // Press Enter to open the first matching topic in sidebar
        searchInput.addEventListener('keydown', (e) => {
            if (e.key === 'Enter') {
                e.preventDefault();
                const firstVisible = Array.from(menuItems).find(item => item.style.display !== 'none');
                if (firstVisible) {
                    window.location.href = firstVisible.getAttribute('href');
                }
            } else if (e.key === 'Escape') {
                searchInput.value = '';
                performSearch();
            }
        });
    }

    // Copy to clipboard buttons
    document.querySelectorAll('.dash-doc-copy-btn').forEach(btn => {
        btn.addEventListener('click', () => {
            const text = btn.getAttribute('data-copy-text');
            if (text && navigator.clipboard) {
                navigator.clipboard.writeText(text).then(() => {
                    const original = btn.textContent;
                    btn.textContent = 'Copied!';
                    btn.classList.add('is-copied');
                    setTimeout(() => {
                        btn.textContent = original;
                        btn.classList.remove('is-copied');
                    }, 2000);
                });
            }
        });
    });
});
</script>
@endsection
