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
    <p class="dash-lead">Comprehensive instructions, best practices, technical standards, and workflows for managing the Azoogi website and backend.</p>
</div>

<div class="dash-doc-layout">
    <!-- Topic Navigation Sidebar / Tabs -->
    <aside class="dash-doc-sidebar">
        <div class="dash-doc-search-wrap">
            <input type="search" id="doc-search" class="dash-doc-search-input" placeholder="Search guides & topics..." aria-label="Search documentation">
            <svg class="dash-doc-search-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                <circle cx="11" cy="11" r="7"/>
                <line x1="21" y1="21" x2="16.65" y2="16.65"/>
            </svg>
        </div>

        <nav class="dash-doc-menu" id="doc-menu">
            @foreach ($topics as $key => $title)
                <a href="{{ route('dashboard.docs.index', ['topic' => $key]) }}" class="dash-doc-menu-item {{ $activeTopic === $key ? 'is-active' : '' }}" data-topic="{{ $key }}">
                    @switch($key)
                        @case('overview')
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M4 10.5 12 4l8 6.5V20a1 1 0 0 1-1 1h-5v-6H10v6H5a1 1 0 0 1-1-1z"/></svg>
                            @break
                        @case('media')
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><rect x="2" y="2" width="20" height="20" rx="2.18"/><polygon points="10 8 16 12 10 16 10 8"/></svg>
                            @break
                        @case('content')
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                            @break
                        @case('products')
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M3 8.5 12 4l9 4.5-9 4.5L3 8.5z"/><path d="M3 8.5v7L12 20l9-4.5v-7M12 13v7"/></svg>
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
                        @case('maintenance')
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.7 1.7 0 0 0 .3 1.8l.1.1a2 2 0 1 1-2.8 2.8l-.1-.1a1.7 1.7 0 0 0-1.8-.3 1.7 1.7 0 0 0-1 1.5V21a2 2 0 1 1-4 0v-.1a1.7 1.7 0 0 0-1.1-1.5 1.7 1.7 0 0 0-1.8.3l-.1.1a2 2 0 1 1-2.8-2.8l.1-.1a1.7 1.7 0 0 0 .3-1.8 1.7 1.7 0 0 0-1.5-1H3a2 2 0 1 1 0-4h.1a1.7 1.7 0 0 0 1.5-1.1 1.7 1.7 0 0 0-.3-1.8l-.1-.1a2 2 0 1 1 2.8-2.8l.1.1a1.7 1.7 0 0 0 1.8.3H9a1.7 1.7 0 0 0 1-1.5V3a2 2 0 1 1 4 0v.1a1.7 1.7 0 0 0 1 1.5 1.7 1.7 0 0 0 1.8-.3l.1-.1a2 2 0 1 1 2.8 2.8l-.1.1a1.7 1.7 0 0 0-.3 1.8V9a1.7 1.7 0 0 0 1.5 1H21a2 2 0 1 1 0 4h-.1a1.7 1.7 0 0 0-1.5 1z"/></svg>
                            @break
                    @endswitch
                    <span>{{ $title }}</span>
                </a>
            @endforeach
        </nav>
    </aside>

    <!-- Main Content Body -->
    <div class="dash-doc-body" id="doc-content-body">

        {{-- 1. OVERVIEW --}}
        @if ($activeTopic === 'overview')
            <article class="dash-card dash-doc-section" data-doc-block>
                <div class="dash-doc-header">
                    <h2>Welcome to Azoogi Administration</h2>
                    <span class="dash-pill-active">Quick Start</span>
                </div>
                <p>This backend management system provides direct control over website content, project showcases, product synchronization with Airtable, sales enquiries, and customer notification workflows.</p>

                <div class="dash-doc-grid-cards">
                    <div class="dash-doc-feature-card">
                        <div class="dash-doc-icon-wrap">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><rect x="3" y="4" width="18" height="14" rx="2"/><path d="M3 15l5-4 4 3 4-5 5 6"/></svg>
                        </div>
                        <h3>Content & Projects</h3>
                        <p>Manage pages, visual section copy, hero sliders, and project case studies.</p>
                        <a href="{{ route('dashboard.pages.index') }}" class="dash-doc-inline-link">Go to Pages &rarr;</a>
                    </div>

                    <div class="dash-doc-feature-card">
                        <div class="dash-doc-icon-wrap">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M3 8.5 12 4l9 4.5-9 4.5L3 8.5z"/><path d="M3 8.5v7L12 20l9-4.5v-7M12 13v7"/></svg>
                        </div>
                        <h3>Products & Airtable</h3>
                        <p>Sync architectural lighting catalog, technical specs, categories, and images directly from Airtable.</p>
                        <a href="{{ route('dashboard.products.index') }}" class="dash-doc-inline-link">Go to Products &rarr;</a>
                    </div>

                    <div class="dash-doc-feature-card">
                        <div class="dash-doc-icon-wrap">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><rect x="6" y="5" width="12" height="15" rx="2"/><path d="M9 5V4h6v1M9 11h6M9 15h4"/></svg>
                        </div>
                        <h3>Enquiries & Quotes</h3>
                        <p>Review customer quote requests, product inquiries, and contact submissions with instant status tracking.</p>
                        <a href="{{ route('dashboard.enquiries.index') }}" class="dash-doc-inline-link">Go to Enquiries &rarr;</a>
                    </div>
                </div>

                <div class="dash-doc-callout info">
                    <strong>Tip for Editors:</strong> Always optimize images and background videos to the recommended web standards before uploading to keep page speed and Google Core Web Vitals optimal.
                </div>
            </article>
        @endif

        {{-- 2. MEDIA GUIDELINES --}}
        @if ($activeTopic === 'media')
            <article class="dash-card dash-doc-section" data-doc-block>
                <div class="dash-doc-header">
                    <h2>Video Guidelines (Hero & Background Videos)</h2>
                    <span class="dash-pill-active">Performance Standard</span>
                </div>
                <p>Background videos create immersive visuals but must be properly encoded to guarantee instant autoplay without delaying page interactivity.</p>

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
                <p>Run this command on your terminal to optimize any raw video file:</p>
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

            <article class="dash-card dash-doc-section" data-doc-block>
                <div class="dash-doc-header">
                    <h2>Image Dimensions & Formats</h2>
                    <span class="dash-pill-active">Image Standard</span>
                </div>

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

        {{-- 3. CONTENT & PAGE EDITOR --}}
        @if ($activeTopic === 'content')
            <article class="dash-card dash-doc-section" data-doc-block>
                <div class="dash-doc-header">
                    <h2>Page Content & Visual Editor</h2>
                    <span class="dash-pill-active">Content Management</span>
                </div>
                <p>The Pages section enables in-context visual editing and structured content management for all primary website landing pages.</p>

                <div class="dash-doc-steps">
                    <div class="dash-doc-step">
                        <div class="dash-doc-step-num">1</div>
                        <div class="dash-doc-step-content">
                            <h4>Select Page to Edit</h4>
                            <p>Navigate to <a href="{{ route('dashboard.pages.index') }}"><strong>Content &rarr; Pages</strong></a> and click on any page (e.g., <em>Home</em>, <em>Solutions</em>, <em>Casambi</em>, <em>About</em>).</p>
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
                            <h4>Configure SEO & Social Share Image</h4>
                            <p>Click the <strong>Page Meta</strong> button in the top toolbar to set the SEO Title, Meta Description, and upload a custom <code>1200×630px</code> Social Sharing (Open Graph) preview image.</p>
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

                <h3>Managing Featured Projects</h3>
                <p>Located in <a href="{{ route('dashboard.projects.index') }}"><strong>Content &rarr; Projects</strong></a>:</p>
                <ul class="dash-doc-list">
                    <li><strong>Reordering</strong>: Drag and drop project rows or use the position controls to determine the exact display order on the public <code>/projects</code> page.</li>
                    <li><strong>Featured Toggle</strong>: Toggle the star icon to highlight flagship case studies on the home page.</li>
                    <li><strong>Product Linking</strong>: Associate specific Azoogi lighting fixtures used in the project so visitors can click through directly to product details.</li>
                </ul>
            </article>
        @endif

        {{-- 4. PRODUCTS & AIRTABLE SYNC --}}
        @if ($activeTopic === 'products')
            <article class="dash-card dash-doc-section" data-doc-block>
                <div class="dash-doc-header">
                    <h2>Product Catalog & Airtable Sync</h2>
                    <span class="dash-pill-active">Automated Pipeline</span>
                </div>
                <p>Product specifications, variants, category relationships, images, and datasheets are managed in Airtable and synced seamlessly to the website database.</p>

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

        {{-- 5. ENQUIRIES & QUOTES --}}
        @if ($activeTopic === 'enquiries')
            <article class="dash-card dash-doc-section" data-doc-block>
                <div class="dash-doc-header">
                    <h2>Enquiries & Lead Management</h2>
                    <span class="dash-pill-active">Sales Workflow</span>
                </div>
                <p>Customer submissions from the website are captured in real-time under <a href="{{ route('dashboard.enquiries.index') }}"><strong>Enquiries</strong></a>.</p>

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

        {{-- 6. DATASHEETS --}}
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

        {{-- 7. EMAIL TEMPLATES --}}
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

        {{-- 8. STAFF & PERMISSIONS --}}
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

        {{-- 9. SEO & MAINTENANCE --}}
        @if ($activeTopic === 'maintenance')
            <article class="dash-card dash-doc-section" data-doc-block>
                <div class="dash-doc-header">
                    <h2>SEO, Asset Versioning & Deployment</h2>
                    <span class="dash-pill-active">Engineering & SEO</span>
                </div>

                <h3>Search Engine Optimization (SEO) Checklist</h3>
                <ul class="dash-doc-list">
                    <li><strong>Page Titles</strong>: Keep between 50 &ndash; 60 characters with brand suffix (e.g. <em>Smart Architectural Lighting Solutions — Azoogi</em>). Configured via the <em>Page Meta</em> drawer.</li>
                    <li><strong>Meta Descriptions</strong>: Keep between 140 &ndash; 160 characters summarizing the core value proposition with an action-oriented CTA.</li>
                    <li><strong>Social Share Images (OG Images)</strong>: Upload high-res <code>1200×630px</code> WebP/JPG images per page. These automatically appear when links are shared on LinkedIn, WhatsApp, Slack, iMessage, and X/Twitter.</li>
                    <li><strong>Canonical URLs</strong>: Automatically generated for each page to prevent duplicate content indexing.</li>
                    <li><strong>Structured Data (Schema.org)</strong>: Automated <code>Organization</code> and <code>Product</code> JSON-LD schemas power Google rich snippets and search knowledge graphs.</li>
                    <li><strong>Image Alt Text</strong>: Always include descriptive alt text for product and project imagery for screen readers and Google Image search ranking.</li>
                </ul>

                <h3>CSS & JavaScript Asset Versioning</h3>
                <p>When deploying updates to front-end styles, bump the asset cache version using the project utility script:</p>
                <div class="dash-doc-code-block">
                    <pre><code>python update_version.py bump</code></pre>
                    <button type="button" class="dash-doc-copy-btn" data-copy-text="python update_version.py bump">Copy Command</button>
                </div>

                <h3>Server Storage Symlink</h3>
                <p>If uploaded media or product attachments show 404 broken links on a new environment, verify the public storage symlink:</p>
                <div class="dash-doc-code-block">
                    <pre><code>php artisan storage:link</code></pre>
                    <button type="button" class="dash-doc-copy-btn" data-copy-text="php artisan storage:link">Copy Command</button>
                </div>
            </article>
        @endif

    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    // Quick Search filter
    const searchInput = document.getElementById('doc-search');
    if (searchInput) {
        searchInput.addEventListener('input', (e) => {
            const term = e.target.value.toLowerCase().trim();
            const blocks = document.querySelectorAll('[data-doc-block]');
            blocks.forEach(block => {
                const text = block.textContent.toLowerCase();
                block.style.display = term === '' || text.includes(term) ? '' : 'none';
            });
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
