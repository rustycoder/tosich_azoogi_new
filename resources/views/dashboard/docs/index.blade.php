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
                                    @case('alt-text')
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M4 7V4h16v3M9 20h6M12 4v16"/><path d="M2 19h20M2 5h20" stroke-dasharray="2 2"/><circle cx="18" cy="15" r="3"/><path d="m20.5 17.5-1.5-1.5"/></svg>
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
                                    @case('mcp')
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><rect x="4" y="4" width="16" height="16" rx="2"/><rect x="9" y="9" width="6" height="6"/><line x1="9" y1="1" x2="9" y2="4"/><line x1="15" y1="1" x2="15" y2="4"/><line x1="9" y1="20" x2="9" y2="23"/><line x1="15" y1="20" x2="15" y2="23"/><line x1="20" y1="9" x2="23" y2="9"/><line x1="20" y1="14" x2="23" y2="14"/><line x1="1" y1="9" x2="4" y2="9"/><line x1="1" y1="14" x2="4" y2="14"/></svg>
                                        @break
                                    @case('ai-models')
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M12 2v4M12 18v4M4.93 4.93l2.83 2.83M16.24 16.24l2.83 2.83M2 12h4M18 12h4M4.93 19.07l2.83-2.83M16.24 7.76l2.83-2.83"/></svg>
                                        @break
                                    @case('ai-rates')
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg>
                                        @break
                                    @case('ai-widget')
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><rect x="3" y="3" width="18" height="18" rx="4"/><path d="M9 3v18M15 9h6M15 15h6"/></svg>
                                        @break
                                    @case('ai-rules')
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><polyline points="9 11 12 14 22 4"/><path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"/></svg>
                                        @break
                                    @case('ai-context')
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>
                                        @break
                                    @case('ai-faqs')
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="12" cy="12" r="10"/><path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
                                        @break
                                    @case('ai-prompt')
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/><polyline points="10 9 9 9 8 9"/></svg>
                                        @break
                                    @case('ai-chat-logs')
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M21 11.5a8.38 8.38 0 0 1-.9 3.8 8.5 8.5 0 0 1-7.6 4.7 8.38 8.38 0 0 1-3.8-.9L3 21l1.9-5.7a8.38 8.38 0 0 1-.9-3.8 8.5 8.5 0 0 1 4.7-7.6 8.38 8.38 0 0 1 3.8-.9h.5a8.48 8.48 0 0 1 8 8v.5z"/></svg>
                                        @break
                                    @case('ai-testing')
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M14.5 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V7.5L14.5 2z"/><polyline points="14 2 14 8 20 8"/><path d="m9 15 2 2 4-4"/></svg>
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

                <hr style="border: 0; border-top: 1px solid var(--dash-border); margin: 28px 0;">

                <h3>AI & Automation Suite</h3>
                <p>Azoogi features a full suite of AI capabilities designed to empower customer support, assist architectural lighting specifications, and convert visitors into qualified project leads:</p>

                <div class="dash-doc-grid-cards">
                    <div class="dash-doc-feature-card">
                        <div class="dash-doc-icon-wrap">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M12 2v4M12 18v4M4.93 4.93l2.83 2.83M16.24 16.24l2.83 2.83M2 12h4M18 12h4M4.93 19.07l2.83-2.83M16.24 7.76l2.83-2.83"/></svg>
                        </div>
                        <h3>AI Models & Providers</h3>
                        <p>Configure Gemini, Claude, OpenRouter, OpenAI, and custom GPU providers with live connection testing.</p>
                        <a href="{{ route('dashboard.docs.index', ['topic' => 'ai-models']) }}" class="dash-doc-inline-link">Read Models Guide &rarr;</a>
                    </div>

                    <div class="dash-doc-feature-card">
                        <div class="dash-doc-icon-wrap">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg>
                        </div>
                        <h3>Rates & Cost Engine</h3>
                        <p>Track real-time input/output token usage, budget limits, and cost-per-conversation metrics.</p>
                        <a href="{{ route('dashboard.docs.index', ['topic' => 'ai-rates']) }}" class="dash-doc-inline-link">Read Rates Guide &rarr;</a>
                    </div>

                    <div class="dash-doc-feature-card">
                        <div class="dash-doc-icon-wrap">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><rect x="3" y="3" width="18" height="18" rx="4"/><path d="M9 3v18M15 9h6M15 15h6"/></svg>
                        </div>
                        <h3>Widget & Branding</h3>
                        <p>Customize assistant name, avatar image, startup greeting, lead intake fields, and prompt chips.</p>
                        <a href="{{ route('dashboard.docs.index', ['topic' => 'ai-widget']) }}" class="dash-doc-inline-link">Read Widget Guide &rarr;</a>
                    </div>

                    <div class="dash-doc-feature-card">
                        <div class="dash-doc-icon-wrap">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><polyline points="9 11 12 14 22 4"/><path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"/></svg>
                        </div>
                        <h3>System Rules & Guardrails</h3>
                        <p>Define strict behavioral directives, electrical safety disclosures, and brand voice guidelines.</p>
                        <a href="{{ route('dashboard.docs.index', ['topic' => 'ai-rules']) }}" class="dash-doc-inline-link">Read Rules Guide &rarr;</a>
                    </div>

                    <div class="dash-doc-feature-card">
                        <div class="dash-doc-icon-wrap">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>
                        </div>
                        <h3>Company Info & Logistics</h3>
                        <p>Ground the AI in Sydney warehouse lead times, custom extrusion cutting, and warranty terms.</p>
                        <a href="{{ route('dashboard.docs.index', ['topic' => 'ai-context']) }}" class="dash-doc-inline-link">Read Company Info Guide &rarr;</a>
                    </div>

                    <div class="dash-doc-feature-card">
                        <div class="dash-doc-icon-wrap">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="12" cy="12" r="10"/><path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
                        </div>
                        <h3>FAQ Knowledge Base</h3>
                        <p>Manage verified Q&A pairs covering Casambi, DALI-2, Neon Flex, and technical specs.</p>
                        <a href="{{ route('dashboard.docs.index', ['topic' => 'ai-faqs']) }}" class="dash-doc-inline-link">Read FAQs Guide &rarr;</a>
                    </div>

                    <div class="dash-doc-feature-card">
                        <div class="dash-doc-icon-wrap">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/></svg>
                        </div>
                        <h3>Live System Prompt Inspector</h3>
                        <p>Inspect the live compiled master prompt, estimated token counts, and assembly layers.</p>
                        <a href="{{ route('dashboard.docs.index', ['topic' => 'ai-prompt']) }}" class="dash-doc-inline-link">Read Prompt Guide &rarr;</a>
                    </div>

                    <div class="dash-doc-feature-card">
                        <div class="dash-doc-icon-wrap">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M21 11.5a8.38 8.38 0 0 1-.9 3.8 8.5 8.5 0 0 1-7.6 4.7 8.38 8.38 0 0 1-3.8-.9L3 21l1.9-5.7a8.38 8.38 0 0 1-.9-3.8 8.5 8.5 0 0 1 4.7-7.6 8.38 8.38 0 0 1 3.8-.9h.5a8.48 8.48 0 0 1 8 8v.5z"/></svg>
                        </div>
                        <h3>Chat Logs & Lead Conversions</h3>
                        <p>Review real-time visitor transcripts, token spend, and convert chat sessions into sales quotes.</p>
                        <a href="{{ route('dashboard.docs.index', ['topic' => 'ai-chat-logs']) }}" class="dash-doc-inline-link">Read Chat Logs Guide &rarr;</a>
                    </div>

                    <div class="dash-doc-feature-card">
                        <div class="dash-doc-icon-wrap">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M14.5 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V7.5L14.5 2z"/><polyline points="14 2 14 8 20 8"/><path d="m9 15 2 2 4-4"/></svg>
                        </div>
                        <h3>AI Test Suite & Benchmarks</h3>
                        <p>1-click copy test prompts covering all 6 function tools, token benchmarking, and model comparisons.</p>
                        <a href="{{ route('dashboard.docs.index', ['topic' => 'ai-testing']) }}" class="dash-doc-inline-link">Read AI Test Suite &rarr;</a>
                    </div>
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
                        <p>Words inside <code>{...}</code> in hero slide titles (<code>hero.title</code>) are rendered in the signature <strong>outlined stroke font</strong> on the website.</p>
                        
                        <div style="margin-top: 12px; background: #0b0b0b; border: 1px solid rgba(255,255,255,0.15); border-radius: 8px; padding: 14px 18px;">
                            <div style="font-size: 11px; text-transform: uppercase; letter-spacing: 0.08em; color: #888888; margin-bottom: 6px;">Live Hero Banner Output:</div>
                            <div style="font-size: 24px; font-weight: 800; line-height: 1.15; color: #ffffff; letter-spacing: -0.01em;">
                                Get in <span style="color: transparent; -webkit-text-stroke: 1.35px #67d04e; filter: drop-shadow(0 0 0.12em rgba(103, 208, 78, 0.45)); letter-spacing: 0.02em;">Touch</span>
                            </div>
                        </div>

                        <div style="margin-top: 10px; font-size: 13px; background: var(--dash-fill); padding: 8px 12px; border-radius: 6px; border: 1px solid var(--dash-border);">
                            <code>Get in {Touch}</code> &rarr; <strong>Get in</strong> <span style="color: transparent; -webkit-text-stroke: 1.35px var(--dash-green-dark, #27771e); font-weight: 800;">Touch</span>
                        </div>
                    </div>

                    <div class="dash-doc-feature-card">
                        <div class="dash-doc-icon-wrap" style="color: var(--dash-green-dark);">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="m12 3-1.912 5.813a2 2 0 0 1-1.275 1.275L3 12l5.813 1.912a2 2 0 0 1 1.275 1.275L12 21l1.912-5.813a2 2 0 0 1 1.275-1.275L21 12l-5.813-1.912a2 2 0 0 1-1.275-1.275L12 3Z"/></svg>
                        </div>
                        <h4>Section Titles, Paragraphs & Cards (Brand Accent Color)</h4>
                        <p>Words inside <code>{...}</code> in section titles, paragraphs, subtitles, and cards are highlighted in the vibrant <strong>brand accent color</strong>.</p>
                        
                        <div style="margin-top: 12px; background: #0b0b0b; border: 1px solid rgba(255,255,255,0.15); border-radius: 8px; padding: 14px 18px;">
                            <div style="font-size: 11px; text-transform: uppercase; letter-spacing: 0.08em; color: #888888; margin-bottom: 6px;">Live Section Heading Output:</div>
                            <div style="font-size: 22px; font-weight: 700; line-height: 1.15; color: #ffffff;">
                                Why Choose <span style="color: #67d04e; font-weight: 700;">Azoogi</span>
                            </div>
                        </div>

                        <div style="margin-top: 10px; font-size: 13px; background: var(--dash-fill); padding: 8px 12px; border-radius: 6px; border: 1px solid var(--dash-border);">
                            <code>Why Choose {Azoogi}</code> &rarr; <strong>Why Choose</strong> <span style="color: var(--dash-green-dark, #27771e); font-weight: 700;">Azoogi</span>
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

                <div class="dash-doc-callout info">
                    <strong>Cover & Gallery Alt Text:</strong> You can edit cover and gallery alt text directly in the Project form with live card previews. Read the <a href="{{ route('dashboard.docs.index', ['topic' => 'alt-text']) }}" class="dash-doc-inline-link"><strong>Image Alt Text & Accessibility Guide &rarr;</strong></a> for writing tips and SEO benefits.
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
                    <h2>Product Catalog & Airtable Guide</h2>
                    <span class="dash-pill-active">Airtable Architecture & SOP</span>
                </div>
                <p>Product specifications, variants, category hierarchies, images, and datasheets are managed in Airtable and synced to the website database. Ordering across the catalog is strictly governed by a mathematical hierarchy between the <strong>Categories</strong> and <strong>Products</strong> tables.</p>

                <div class="dash-doc-callout info">
                    <strong>Two Cooperating Order Fields:</strong> The <strong>Categories</strong> table sets the display order of categories, and the <strong>Products</strong> table sets the order of products within them. Following this system guarantees clean category grouping and deterministic product sorting site-wide.
                </div>

                <hr style="border: 0; border-top: 1px solid var(--dash-border); margin: 28px 0;">

                <h3>1. Category Order & Number Blocks</h3>
                <p>Each top-level category owns a block of numbers in <strong>steps of 100</strong>. Its subcategories use the numbers directly after it:</p>

                <div class="dash-table-wrap">
                    <table class="dash-doc-table">
                        <thead>
                            <tr>
                                <th style="width: 100px;">Block</th>
                                <th style="width: 240px;">Parent Category</th>
                                <th>Subcategories & Ranges</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td><code>100</code></td>
                                <td><strong>Landscape Lighting</strong></td>
                                <td><code>101</code> Garden Light, <code>102</code> Pool Light, <code>103</code> Handrail</td>
                            </tr>
                            <tr>
                                <td><code>200</code></td>
                                <td><strong>Neon Flex</strong></td>
                                <td><code>201</code> Mini Neon, <code>202</code> Standard Neon, <code>203</code> 3D Neon … <code>213</code> Long Run Neon</td>
                            </tr>
                            <tr>
                                <td><code>300</code></td>
                                <td><strong>COB Strips / SMD Strips</strong></td>
                                <td><code>301</code> COB Strips, <code>302–304</code> COB types, <code>305</code> SMD Strips, <code>306–307</code> SMD types, <code>308</code> Flex Panel Sheets</td>
                            </tr>
                            <tr>
                                <td><code>400</code></td>
                                <td><strong>Profiles</strong></td>
                                <td><code>401</code> Trimless … <code>414</code> Wall Washer <em>(the 403 and 407 parents hold the Surfaced and Recessed sub-groups)</em></td>
                            </tr>
                            <tr>
                                <td><code>500</code></td>
                                <td><strong>Drivers</strong></td>
                                <td><code>501–504</code> Driver types</td>
                            </tr>
                            <tr>
                                <td><code>600</code></td>
                                <td><strong>Accessories</strong></td>
                                <td><code>601</code> Neon, <code>602</code> LED Strip, <code>603</code> Remotes (<code>604</code> RF Remotes, <code>605</code> Wall Panels), <code>606</code> Downlight Accessories</td>
                            </tr>
                            <tr>
                                <td><code>700</code></td>
                                <td><strong>Controllers</strong></td>
                                <td><code>701</code> DALI, <code>702</code> Tuya, <code>703</code> Casambi Controllers (empty), <code>704</code> Waterproof, <code>705</code> MADRIX Pixel</td>
                            </tr>
                            <tr>
                                <td><code>750</code></td>
                                <td><strong>Smart Controls</strong></td>
                                <td><code>751</code> Casambi Controls, <code>752</code> Smart Switches</td>
                            </tr>
                            <tr>
                                <td><code>800</code></td>
                                <td><strong>48V Track Systems</strong></td>
                                <td><code>801</code> Azoogi TR11, <code>802</code> Luminaires, <code>803</code> Tracks, <code>804</code> Track Accessories, <code>805</code> Audio, <code>806</code> Ventilation</td>
                            </tr>
                            <tr>
                                <td><code>900</code></td>
                                <td><strong>Downlights</strong></td>
                                <td><code>901</code> Recessed, <code>902</code> Surface Mounted, <code>903</code> Pendant, <code>904</code> Wall Lights</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div class="dash-doc-callout tip" style="margin-top: 14px;">
                    <strong>Category Rules:</strong>
                    <ul style="margin: 8px 0 0 18px; padding: 0;">
                        <li><strong>Unique numbers:</strong> Every category has its own three-digit number. A subcategory's number is always higher than its parent's and lower than the next parent's block.</li>
                        <li><strong>Adding a subcategory:</strong> Give it the next free number inside its parent's block. For example, a new Neon type would be <code>214</code>.</li>
                        <li><strong>Adding a top-level category:</strong> Give it a new empty block, such as <code>1000</code>.</li>
                        <li><strong>Moving a category:</strong> You can change its number, but its products won't follow automatically. Each product's Order must also have its first three digits updated (see Section 2).</li>
                    </ul>
                </div>

                <hr style="border: 0; border-top: 1px solid var(--dash-border); margin: 28px 0;">

                <h3>2. Product Order Formula</h3>
                <p>Every product's number combines its category number with its position in that category:</p>

                <div style="margin: 16px 0; background: var(--dash-fill); border: 1px solid var(--dash-border); border-radius: 8px; padding: 16px 20px;">
                    <div style="font-size: 11px; text-transform: uppercase; letter-spacing: 0.08em; color: var(--dash-muted); margin-bottom: 6px;">Order Generation Formula:</div>
                    <div style="font-size: 18px; font-weight: 700; color: var(--dash-green-dark, #27771e); font-family: monospace;">
                        Product Order = Category Order × 1000 + position (001–999)
                    </div>
                </div>

                <div class="dash-table-wrap">
                    <table class="dash-doc-table">
                        <thead>
                            <tr>
                                <th>Product</th>
                                <th>Category (Order)</th>
                                <th>Position</th>
                                <th>Calculated Product Order</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td><strong>First Mini Neon product</strong></td>
                                <td>Mini Neon (<code>201</code>)</td>
                                <td><code>1</code></td>
                                <td><code style="font-weight: 700; color: var(--dash-green-dark, #27771e);">201001</code></td>
                            </tr>
                            <tr>
                                <td><strong>Fourth Mini Neon product</strong></td>
                                <td>Mini Neon (<code>201</code>)</td>
                                <td><code>4</code></td>
                                <td><code style="font-weight: 700; color: var(--dash-green-dark, #27771e);">201004</code></td>
                            </tr>
                            <tr>
                                <td><strong>First Trimless profile</strong></td>
                                <td>Trimless Profiles (<code>401</code>)</td>
                                <td><code>1</code></td>
                                <td><code style="font-weight: 700; color: var(--dash-green-dark, #27771e);">401001</code></td>
                            </tr>
                            <tr>
                                <td><strong>41st Recessed downlight</strong></td>
                                <td>Recessed (<code>901</code>)</td>
                                <td><code>41</code></td>
                                <td><code style="font-weight: 700; color: var(--dash-green-dark, #27771e);">901041</code></td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <p style="margin-top: 12px;">To read any product number, the <strong>first three digits are the category</strong> and the <strong>last three are the position</strong>. For example, <code>901041</code> is category <strong>901</strong> (Recessed), product <strong>041</strong>.</p>

                <div class="dash-doc-callout tip" style="margin-top: 14px;">
                    <strong>Product Rules:</strong>
                    <ul style="margin: 8px 0 0 18px; padding: 0;">
                        <li><strong>Unique numbers:</strong> No two products share an <code>Order</code> value.</li>
                        <li><strong>Category first:</strong> Sorting the whole table by <code>Order</code> lists products by category, following the category order above.</li>
                        <li><strong>Multi-category products:</strong> A product in more than one category is numbered under the <em>first category</em> in its Categories field. For example, PR126 is listed under Suspended Profiles (<code>411</code>), so its Order is <code>411xxx</code>.</li>
                        <li><strong>Capacity:</strong> Each category holds up to 999 products (001–999). The largest now is Neon Accessories with 46.</li>
                    </ul>
                </div>

                <hr style="border: 0; border-top: 1px solid var(--dash-border); margin: 28px 0;">

                <h3>3. How the Current Numbers Were Set</h3>
                <ul class="dash-doc-list">
                    <li><strong>Categories</strong> were placed in the sequence of the Categories table.</li>
                    <li><strong>Within each category</strong>, products that already had an order kept their relative sequence.</li>
                    <li><strong>Products with no order</strong> were placed at the end of their category, alphabetically by name.</li>
                    <li><strong>All numbers were converted</strong> from the earlier ×100 format to ×1000 (e.g. <code>20101</code> became <code>201001</code>). Categories and positions stayed the same.</li>
                </ul>

                <hr style="border: 0; border-top: 1px solid var(--dash-border); margin: 28px 0;">

                <h3>4. Day-to-Day Maintenance</h3>
                <div class="dash-doc-steps">
                    <div class="dash-doc-step">
                        <div class="dash-doc-step-num">1</div>
                        <div class="dash-doc-step-content">
                            <h4>New Product</h4>
                            <p>Find the highest number in its category and add 1. If the last Mini Neon is <code>201004</code>, the new one is <code>201005</code>.</p>
                        </div>
                    </div>
                    <div class="dash-doc-step">
                        <div class="dash-doc-step-num">2</div>
                        <div class="dash-doc-step-content">
                            <h4>Reordering Within a Category</h4>
                            <p>Renumber only that category's products, and keep the same first three digits.</p>
                        </div>
                    </div>
                    <div class="dash-doc-step">
                        <div class="dash-doc-step-num">3</div>
                        <div class="dash-doc-step-content">
                            <h4>Inserting in the Middle</h4>
                            <p>Positions are consecutive, so there's no gap. Renumber the products after the insert point in that category (can be done in bulk).</p>
                        </div>
                    </div>
                    <div class="dash-doc-step">
                        <div class="dash-doc-step-num">4</div>
                        <div class="dash-doc-step-content">
                            <h4>Changing a Product's Category</h4>
                            <p>Give it the next free number in the new category's range.</p>
                        </div>
                    </div>
                    <div class="dash-doc-step">
                        <div class="dash-doc-step-num">5</div>
                        <div class="dash-doc-step-content">
                            <h4>New Subcategory</h4>
                            <p>Its products start at <code>[category number]001</code>, for example <code>214001</code>.</p>
                        </div>
                    </div>
                    <div class="dash-doc-step">
                        <div class="dash-doc-step-num">6</div>
                        <div class="dash-doc-step-content">
                            <h4>Checking the Table</h4>
                            <p>Sort by <code>Order</code> in Airtable. If a product appears in the wrong group, or its first three digits don't match its category, it needs renumbering.</p>
                        </div>
                    </div>
                </div>

                <hr style="border: 0; border-top: 1px solid var(--dash-border); margin: 28px 0;">

                <h3>5. Synchronization Pipeline & Execution</h3>
                <p>When you trigger a sync from <a href="{{ route('dashboard.products.index') }}"><strong>Products</strong></a> or via CLI, the backend connects securely to Airtable, fetches all active product records, updates technical specifications, downloads new media assets to local storage, and rebuilds the product cache.</p>

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

                <h3 style="margin-top: 20px;">Troubleshooting Sync Issues</h3>
                <ul class="dash-doc-list">
                    <li><strong>Missing Product Images:</strong> Ensure the image field in Airtable contains valid attachments and that <code>php artisan storage:link</code> has been generated on the server.</li>
                    <li><strong>Duplicate Slugs:</strong> Ensure product codes and names in Airtable are unique to avoid URL routing collisions.</li>
                    <li><strong>Out-of-Order Products:</strong> If products appear in the wrong order, verify the 5-digit <code>Order</code> value in Airtable and re-run sync.</li>
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

                <div class="dash-doc-callout info">
                    <strong>Setting Alt Text & Accessibility:</strong> For instructions on configuring custom alt text, live preview badges, and search ranking benefits, read our dedicated <a href="{{ route('dashboard.docs.index', ['topic' => 'alt-text']) }}" class="dash-doc-inline-link"><strong>Image Alt Text & Accessibility Guide &rarr;</strong></a>
                </div>
            </article>
        @endif

        {{-- 8b. IMAGE ALT TEXT & ACCESSIBILITY --}}
        @if ($activeTopic === 'alt-text')
            <article class="dash-card dash-doc-section" data-doc-block>
                <div class="dash-doc-header">
                    <h2>Image Alt Text & Accessibility (WCAG 2.1 & SEO)</h2>
                    <span class="dash-pill-active">Accessibility & SEO Standard</span>
                </div>
                <p>Alternative text (alt text) is a concise textual description embedded in image HTML tags (<code>&lt;img alt="..."&gt;</code>). It serves as the primary bridge between visual imagery and non-visual user agents&mdash;including search engine crawlers, assistive screen readers, and AI recommendation engines.</p>

                <h3>Why Alt Text is Crucial for the Website</h3>
                <div class="dash-doc-grid-cards">
                    <div class="dash-doc-feature-card">
                        <div class="dash-doc-icon-wrap">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="12" cy="12" r="10"/><line x1="2" y1="12" x2="22" y2="12"/><path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"/></svg>
                        </div>
                        <h4>1. Search Engine Optimization (SEO) & Google Images</h4>
                        <p>Search bots (Googlebot, Bingbot) cannot directly "see" photographic lighting installations. Alt text provides explicit keyword context that indexes project installations in Google Image search and rich result carousels.</p>
                    </div>

                    <div class="dash-doc-feature-card">
                        <div class="dash-doc-icon-wrap">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.7 1.7 0 0 0 .3 1.8l.1.1a2 2 0 1 1-2.8 2.8l-.1-.1a1.7 1.7 0 0 0-1.8-.3 1.7 1.7 0 0 0-1 1.5V21a2 2 0 1 1-4 0v-.1a1.7 1.7 0 0 0-1.1-1.5 1.7 1.7 0 0 0-1.8.3l-.1.1a2 2 0 1 1-2.8-2.8l.1-.1a1.7 1.7 0 0 0 .3-1.8 1.7 1.7 0 0 0-1.5-1H3a2 2 0 1 1 0-4h.1a1.7 1.7 0 0 0 1.5-1.1 1.7 1.7 0 0 0-.3-1.8l-.1-.1a2 2 0 1 1 2.8-2.8l.1.1a1.7 1.7 0 0 0 1.8.3H9a1.7 1.7 0 0 0 1-1.5V3a2 2 0 1 1 4 0v.1a1.7 1.7 0 0 0 1 1.5 1.7 1.7 0 0 0 1.8-.3l.1-.1a2 2 0 1 1 2.8 2.8l-.1.1a1.7 1.7 0 0 0-.3 1.8V9a1.7 1.7 0 0 0 1.5 1H21a2 2 0 1 1 0 4h-.1a1.7 1.7 0 0 0-1.5 1z"/></svg>
                        </div>
                        <h4>2. Web Accessibility (WCAG 2.1 AA Compliance)</h4>
                        <p>Visually impaired architects, lighting designers, and clients using screen readers (VoiceOver, NVDA, JAWS) rely on alt text read aloud to understand diagrams, installation photos, and lighting fixtures.</p>
                    </div>

                    <div class="dash-doc-feature-card">
                        <div class="dash-doc-icon-wrap">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M12 3l1.912 5.813a2 2 0 0 0 1.275 1.275L21 12l-5.813 1.912a2 2 0 0 0-1.275 1.275L12 21l-1.912-5.813a2 2 0 0 0-1.275-1.275L3 12l5.813-1.912a2 2 0 0 0 1.275-1.275L12 3z"/></svg>
                        </div>
                        <h4>3. Generative AI Search (LLMs & GEO)</h4>
                        <p>Modern AI search engines (ChatGPT, Perplexity, Google Gemini, Claude) crawl image alt attributes to synthesize answers about Azoogi's architectural lighting projects, citing them in AI-generated responses.</p>
                    </div>

                    <div class="dash-doc-feature-card">
                        <div class="dash-doc-icon-wrap">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8.5" cy="8.5" r="1.5"/><polyline points="21 15 16 10 5 21"/></svg>
                        </div>
                        <h4>4. Graceful Degradation on Slow Networks</h4>
                        <p>If a visitor is on a low-bandwidth cellular network and high-resolution photography fails to download immediately, the browser renders the alt text in place of the image, keeping page context intact.</p>
                    </div>
                </div>

                <h3>How to Set Alt Text in the Backend CMS</h3>
                <div class="dash-doc-steps">
                    <div class="dash-doc-step">
                        <div class="dash-doc-step-num">1</div>
                        <div class="dash-doc-step-content">
                            <h4>Project Cover Image Alt Text</h4>
                            <p>In the Project Editor (<a href="{{ route('dashboard.projects.index') }}"><strong>Content &rarr; Projects</strong></a>), locate the <strong>Cover image alt text</strong> field directly below the cover upload dropzone. The preview card above updates in real time to show the active alt text badge.</p>
                        </div>
                    </div>
                    <div class="dash-doc-step">
                        <div class="dash-doc-step-num">2</div>
                        <div class="dash-doc-step-content">
                            <h4>Project Gallery Photo Alt Text</h4>
                            <p>Every photo in the Gallery grid displays an image preview card with metadata badges (format, file size, dimensions, and aspect ratio). Click the <strong>Edit Alt</strong> pencil icon (<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" style="width: 13px; height: 13px; vertical-align: -2px; display: inline-block;"><path d="M12 20h9"/><path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4Z"/></svg>) to reveal the inline custom alt text field. Type your description, and the preview label reflects your input instantly.</p>
                        </div>
                    </div>
                    <div class="dash-doc-step">
                        <div class="dash-doc-step-num">3</div>
                        <div class="dash-doc-step-content">
                            <h4>Automatic Intelligent Fallbacks</h4>
                            <p>If you leave the alt text blank, the Azoogi platform automatically generates a clean, descriptive fallback (e.g. <code>[Project Title]</code> for covers and <code>[Project Title] photo [Index]</code> for gallery photos) to guarantee 100% WCAG 2.1 compliance with zero empty alt tags across the public website.</p>
                        </div>
                    </div>
                    <div class="dash-doc-step">
                        <div class="dash-doc-step-num">4</div>
                        <div class="dash-doc-step-content">
                            <h4>Page Feature Diagrams & Schematics</h4>
                            <p>Structured page sections (such as system architecture diagrams, software integration workflows, and Casambi mesh diagrams) include dedicated alt fields (e.g. <code>feature.image_alt</code>) ensuring technical schematics remain accessible.</p>
                        </div>
                    </div>
                </div>

                <h3>Best Practice Writing Guidelines</h3>
                <div class="dash-table-wrap">
                    <table class="dash-doc-table">
                        <thead>
                            <tr>
                                <th>Category</th>
                                <th>Poor Alt Text (Avoid)</th>
                                <th>Optimized Alt Text (Recommended)</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td><strong>Project Cover Photo</strong></td>
                                <td><code>IMG_8492.jpg</code> or <code>Photo</code></td>
                                <td><code>Crown Sydney Grand Atrium illuminated by custom curved RGBW linear profiles</code></td>
                            </tr>
                            <tr>
                                <td><strong>Gallery Installation Shot</strong></td>
                                <td><code>Image of boardroom lights</code></td>
                                <td><code>Recessed micro-downlights and acoustic lighting suspended above executive boardroom table</code></td>
                            </tr>
                            <tr>
                                <td><strong>Exterior Facade Lighting</strong></td>
                                <td><code>Azoogi project 2</code></td>
                                <td><code>IP67 exterior wall-washers highlighting heritage sandstone facade in warm 2700K</code></td>
                            </tr>
                            <tr>
                                <td><strong>Technical Control Diagram</strong></td>
                                <td><code>Diagram</code></td>
                                <td><code>Casambi wireless BLE mesh network topology diagram linking sensors, switches, and drivers</code></td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div class="dash-doc-callout info">
                    <strong>Golden Rules for Writing Alt Text:</strong>
                    <ul>
                        <li><strong>Be Specific & Descriptive:</strong> State the architectural setting, fixture type, and lighting effect.</li>
                        <li><strong>Skip Redundant Prefixes:</strong> Do NOT start with <em>"Image of..."</em> or <em>"Picture of..."</em>&mdash;screen readers already announce that the element is an image.</li>
                        <li><strong>Keep it Under 125 Characters:</strong> Most popular screen readers pause or truncate excessively verbose strings.</li>
                        <li><strong>Include Natural Keywords:</strong> Mention relevant architectural lighting terminology naturally without keyword stuffing.</li>
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
                    <li><strong>Image Alt Text</strong>: Always include descriptive alt text for product and project imagery for screen readers and Google Image search ranking. See our <a href="{{ route('dashboard.docs.index', ['topic' => 'alt-text']) }}" class="dash-doc-inline-link"><strong>Image Alt Text & Accessibility Guide &rarr;</strong></a></li>
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

                <div class="dash-doc-callout info" style="margin-top: 14px;">
                    <strong>Custom Feed Editor:</strong> You can view, customize, and edit the live Markdown files served to LLMs directly from <a href="{{ route('dashboard.llms.index') }}" class="dash-doc-inline-link"><strong>AI & LLM Feeds &rarr;</strong></a> in the dashboard.
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

        {{-- AI & AUTOMATION DOCUMENTATION SECTIONS --}}

        {{-- AI.1: AI MODELS & PROVIDERS --}}
        @if ($activeTopic === 'ai-models')
            <article class="dash-card dash-doc-section" data-doc-block>
                <div class="dash-doc-header">
                    <h2>AI Models, API Keys & Custom Providers</h2>
                    <span class="dash-pill-active">AI Core Configuration</span>
                </div>
                <p>The <strong>AI Models</strong> module serves as the primary control plane for configuring the Large Language Models (LLMs) that power the Azoogi conversational assistant. Administrators can seamlessly switch between world-class cloud providers (Google Gemini, Anthropic Claude, OpenRouter, and OpenAI) or connect private, on-premise AI providers (such as Ollama, vLLM, OpenWebUI, or LM Studio).</p>

                <div class="dash-doc-grid-cards">
                    <div class="dash-doc-feature-card">
                        <div class="dash-doc-icon-wrap">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M12 2v4M12 18v4M4.93 4.93l2.83 2.83M16.24 16.24l2.83 2.83M2 12h4M18 12h4M4.93 19.07l2.83-2.83M16.24 7.76l2.83-2.83"/></svg>
                        </div>
                        <h3>Multi-Provider Hub</h3>
                        <p>Configure official APIs for Gemini, Anthropic Claude, OpenRouter, and OpenAI with 1-click active driver switching.</p>
                        <a href="{{ route('dashboard.ai.models') }}" class="dash-doc-inline-link">Go to AI Models &rarr;</a>
                    </div>

                    <div class="dash-doc-feature-card">
                        <div class="dash-doc-icon-wrap">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><polyline points="22 12 18 12 15 21 9 3 6 12 2 12"/></svg>
                        </div>
                        <h3>Live Diagnostic Testing</h3>
                        <p>Test API keys, model latency, and handshake responses in real time before saving changes to production.</p>
                    </div>

                    <div class="dash-doc-feature-card">
                        <div class="dash-doc-icon-wrap">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><rect x="2" y="2" width="20" height="8" rx="2"/><rect x="2" y="14" width="20" height="8" rx="2"/><line x1="6" y1="6" x2="6.01" y2="6"/><line x1="6" y1="18" x2="6.01" y2="18"/></svg>
                        </div>
                        <h3>Custom Provider Engine</h3>
                        <p>Integrate private GPU servers, Ollama endpoints, OpenWebUI proxies, or vLLM deployments with custom JSON bodies.</p>
                    </div>

                    <div class="dash-doc-feature-card">
                        <div class="dash-doc-icon-wrap">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                        </div>
                        <h3>Secure Encrypted Storage</h3>
                        <p>API keys are securely stored in the database, masked in dashboard UI fields, and protected by role permissions.</p>
                    </div>
                </div>

                <div class="dash-doc-callout info">
                    <strong>Zero Downtime Provider Switching:</strong> When you change the active AI provider in the dashboard, the system updates the live configuration immediately without requiring server restarts or cache clearing.
                </div>

                <hr style="border: 0; border-top: 1px solid var(--dash-border); margin: 28px 0;">

                <h3>1. Supported AI Providers & Model Matrix</h3>
                <p>Azoogi natively supports the following industry-leading LLM engines:</p>

                <div class="dash-table-wrap">
                    <table class="dash-doc-table">
                        <thead>
                            <tr>
                                <th style="width: 140px;">Provider</th>
                                <th style="width: 220px;">Default / Popular Models</th>
                                <th>Strengths & Target Use Case</th>
                                <th style="width: 120px;">Speed & Latency</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td><strong>Google Gemini</strong></td>
                                <td><code>gemini-2.5-flash</code> (Default)<br><code>gemini-2.5-flash-lite</code><br><code>gemini-1.5-pro</code></td>
                                <td>Ultra-fast response times, massive context window (1M+ tokens), exceptional cost efficiency, and native multimodal reasoning. Ideal for high-traffic visitor chat.</td>
                                <td><span class="dash-pill-active" style="background: #e6f4ea; color: #137333;">Fastest (&lt;800ms)</span></td>
                            </tr>
                            <tr>
                                <td><strong>Anthropic Claude</strong></td>
                                <td><code>claude-3-5-sonnet-20241022</code><br><code>claude-3-5-haiku-20241022</code><br><code>claude-3-opus-20240229</code></td>
                                <td>Best-in-class nuanced reasoning, exceptional adherence to complex system guardrails, natural conversational tone, and flawless technical lighting guidance.</td>
                                <td><span class="dash-pill-active" style="background: #fef7e0; color: #b06000;">Fast (1.2s)</span></td>
                            </tr>
                            <tr>
                                <td><strong>OpenRouter</strong></td>
                                <td><code>anthropic/claude-3.5-sonnet</code><br><code>deepseek/deepseek-chat</code><br><code>meta-llama/llama-3.3-70b-instruct</code></td>
                                <td>Universal aggregation gateway allowing access to hundreds of open-source and proprietary models under a single unified billing account and API key.</td>
                                <td><span class="dash-pill-active" style="background: #e8f0fe; color: #1a73e8;">Variable</span></td>
                            </tr>
                            <tr>
                                <td><strong>OpenAI</strong></td>
                                <td><code>gpt-4o-mini</code><br><code>gpt-4o</code><br><code>o3-mini</code></td>
                                <td>Reliable enterprise-grade generation, strong structured JSON extraction, and high compatibility.</td>
                                <td><span class="dash-pill-active" style="background: #e6f4ea; color: #137333;">Fast (900ms)</span></td>
                            </tr>
                            <tr>
                                <td><strong>Custom Providers</strong></td>
                                <td>Ollama (e.g. <code>llama3.2:3b</code>)<br>OpenWebUI<br>vLLM / LM Studio</td>
                                <td>Self-hosted local privacy, zero API costs, on-premise data compliance, and custom hardware acceleration.</td>
                                <td><span class="dash-pill-active" style="background: #f3e8fd; color: #7b1fa2;">Hardware Dependent</span></td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <hr style="border: 0; border-top: 1px solid var(--dash-border); margin: 28px 0;">

                <h3>2. Step-by-Step Configuration Guide</h3>

                <div class="dash-doc-steps">
                    <div class="dash-doc-step">
                        <div class="dash-doc-step-num">1</div>
                        <div class="dash-doc-step-content">
                            <h4>Select the Active Provider</h4>
                            <p>Navigate to <a href="{{ route('dashboard.ai.models') }}" class="dash-doc-inline-link"><strong>AI &rarr; Models</strong></a>. Click the radio selector next to the provider you wish to activate (Gemini, Anthropic, OpenRouter, OpenAI, or a Custom Provider).</p>
                        </div>
                    </div>
                    <div class="dash-doc-step">
                        <div class="dash-doc-step-num">2</div>
                        <div class="dash-doc-step-content">
                            <h4>Enter Your API Key</h4>
                            <p>Paste your API secret key into the corresponding provider card (e.g. <code>AIzaSy...</code> for Gemini or <code>sk-ant-...</code> for Anthropic). Keys are masked automatically for security.</p>
                        </div>
                    </div>
                    <div class="dash-doc-step">
                        <div class="dash-doc-step-num">3</div>
                        <div class="dash-doc-step-content">
                            <h4>Select or Enter Target Model Identifier</h4>
                            <p>Choose your preferred model from the dropdown list, or select <em>Custom Model Identifier</em> to specify an exact model string (e.g. <code>gemini-2.5-flash</code> or <code>claude-3-5-sonnet-20241022</code>).</p>
                        </div>
                    </div>
                    <div class="dash-doc-step">
                        <div class="dash-doc-step-num">4</div>
                        <div class="dash-doc-step-content">
                            <h4>Run Live Diagnostic Connection Test</h4>
                            <p>Click the <strong>Test Connection</strong> button on the provider card. The backend will perform an instant live handshake with the AI API, displaying the status, round-trip latency, model identification, and sample output.</p>
                        </div>
                    </div>
                    <div class="dash-doc-step">
                        <div class="dash-doc-step-num">5</div>
                        <div class="dash-doc-step-content">
                            <h4>Save Configuration</h4>
                            <p>Click <strong>Save Model Settings</strong> in the top-right corner. The new model will immediately start servicing live visitor chat sessions.</p>
                        </div>
                    </div>
                </div>

                <hr style="border: 0; border-top: 1px solid var(--dash-border); margin: 28px 0;">

                <h3>3. Adding Custom AI Providers (Ollama, vLLM, OpenWebUI)</h3>
                <p>To connect a self-hosted or third-party AI proxy, scroll down to <strong>Add Custom AI Provider</strong> in <a href="{{ route('dashboard.ai.models') }}" class="dash-doc-inline-link"><strong>AI &rarr; Models</strong></a> and configure the following parameters:</p>

                <ul>
                    <li><strong>Provider Name:</strong> A friendly display name (e.g., <em>Local Ollama RTX 4090</em> or <em>Internal OpenWebUI Gateway</em>).</li>
                    <li><strong>Provider Type:</strong> Select the API protocol format: <code>openai</code> (OpenAI Compatible), <code>anthropic</code> (Anthropic Messages API), or <code>gemini</code>.</li>
                    <li><strong>Base URL:</strong> The full endpoint URL (e.g., <code>http://192.168.1.50:11434/v1</code> for Ollama or <code>https://ai.company.com/api/v1</code>).</li>
                    <li><strong>API Key:</strong> Secret bearer token (or <code>ollama</code> / dummy key if local authentication is disabled).</li>
                    <li><strong>Model Identifier:</strong> The exact model name as registered in the host engine (e.g., <code>llama3.2:3b</code>, <code>qwen2.5-coder:7b</code>, or <code>mistral-small</code>). Supports comma-separated model lists.</li>
                    <li><strong>OpenWebUI Compatibility Toggle:</strong> Enable this checkbox if routing through OpenWebUI; the orchestrator automatically injects required session bypass headers (<code>"chat_id": "api_bypass_fix"</code>).</li>
                    <li><strong>Extra Body JSON Parameters (Optional):</strong> Supply custom parameters such as <code>{"temperature": 0.3, "top_p": 0.9}</code>.</li>
                </ul>

                <div class="dash-doc-callout tip">
                    <strong>Testing Custom Providers:</strong> Always click <em>Test Connection</em> before setting a custom provider as active to verify firewall access, CORS headers, and model responsiveness.
                </div>
            </article>
        @endif

        {{-- AI.2: AI RATES & COST ESTIMATION --}}
        @if ($activeTopic === 'ai-rates')
            <article class="dash-card dash-doc-section" data-doc-block>
                <div class="dash-doc-header">
                    <h2>AI Pricing Rates, Token Budgets & Cost Engine</h2>
                    <span class="dash-pill-active">Cost & Financial Analytics</span>
                </div>
                <p>The <strong>AI Rates</strong> module provides financial transparency into your LLM operational expenditure. It features a built-in mathematical cost estimation engine that tracks input and output token consumption for every visitor conversation turn and computes accurate dollar costs down to fractions of a cent.</p>

                <div class="dash-doc-grid-cards">
                    <div class="dash-doc-feature-card">
                        <div class="dash-doc-icon-wrap">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg>
                        </div>
                        <h3>Real-Time Spend Tracking</h3>
                        <p>Live computation of total tokens consumed and total USD spent across all visitor chat sessions.</p>
                        <a href="{{ route('dashboard.ai.rates') }}" class="dash-doc-inline-link">Go to AI Rates &rarr;</a>
                    </div>

                    <div class="dash-doc-feature-card">
                        <div class="dash-doc-icon-wrap">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><rect x="4" y="4" width="16" height="16" rx="2"/><rect x="9" y="9" width="6" height="6"/><line x1="9" y1="1" x2="9" y2="4"/><line x1="15" y1="1" x2="15" y2="4"/><line x1="9" y1="20" x2="9" y2="23"/><line x1="15" y1="20" x2="15" y2="23"/></svg>
                        </div>
                        <h3>Granular Tokenomics</h3>
                        <p>Inspects prompt tokens (input) and completion tokens (output) separately using official pricing tiers.</p>
                    </div>

                    <div class="dash-doc-feature-card">
                        <div class="dash-doc-icon-wrap">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><polyline points="23 6 13.5 15.5 8.5 10.5 1 18"/><polyline points="17 6 23 6 23 12"/></svg>
                        </div>
                        <h3>Model Comparison Matrix</h3>
                        <p>Comprehensive rate card comparing cost per million tokens across Gemini, Claude, OpenAI, and DeepSeek.</p>
                    </div>

                    <div class="dash-doc-feature-card">
                        <div class="dash-doc-icon-wrap">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="12" cy="12" r="10"/><path d="M12 6v6l4 2"/></svg>
                        </div>
                        <h3>Average Cost Per Lead</h3>
                        <p>Calculates average token weight and monetary cost per customer interaction to optimize ROI.</p>
                    </div>
                </div>

                <hr style="border: 0; border-top: 1px solid var(--dash-border); margin: 28px 0;">

                <h3>1. Mathematical Cost Calculation Engine</h3>
                <p>Every time a visitor submits a message or the assistant streams a response, Azoogi's <code>AiCostCalculator</code> evaluates the exact token usage reported by the provider API:</p>

                <div class="dash-doc-code-block" style="margin-bottom: 16px;">
                    <div class="dash-doc-code-header"><span>Cost Calculation Formula</span></div>
                    <pre><code>Estimated Cost ($ USD) = ( (Prompt Tokens × Input Rate) + (Completion Tokens × Output Rate) ) / 1,000,000</code></pre>
                </div>

                <p>Where rates are specified in <strong>USD per 1 Million Tokens ($/MTok)</strong>. This guarantees transparent, auditable pricing down to four decimal places (e.g. <code>$0.0014 USD</code> per typical session).</p>

                <hr style="border: 0; border-top: 1px solid var(--dash-border); margin: 28px 0;">

                <h3>2. Model Rate Card Comparison</h3>
                <p>The pricing table below outlines official rate schedules across all supported cloud LLMs:</p>

                <div class="dash-table-wrap">
                    <table class="dash-doc-table">
                        <thead>
                            <tr>
                                <th>Model Name & Identifier</th>
                                <th>Provider</th>
                                <th style="width: 140px;">Input Rate ($/MTok)</th>
                                <th style="width: 140px;">Output Rate ($/MTok)</th>
                                <th>Cost Profile</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td><strong>Gemini 2.5 Flash</strong> (<code>gemini-2.5-flash</code>)</td>
                                <td>Google Gemini</td>
                                <td><code>$0.075</code></td>
                                <td><code>$0.300</code></td>
                                <td><span class="dash-pill-active" style="background: #e6f4ea; color: #137333;">Ultra Affordable</span></td>
                            </tr>
                            <tr>
                                <td><strong>Gemini 2.5 Flash-Lite</strong> (<code>gemini-2.5-flash-lite</code>)</td>
                                <td>Google Gemini</td>
                                <td><code>$0.0375</code></td>
                                <td><code>$0.150</code></td>
                                <td><span class="dash-pill-active" style="background: #e6f4ea; color: #137333;">Maximum Economy</span></td>
                            </tr>
                            <tr>
                                <td><strong>Gemini 1.5 Pro</strong> (<code>gemini-1.5-pro</code>)</td>
                                <td>Google Gemini</td>
                                <td><code>$1.250</code></td>
                                <td><code>$5.000</code></td>
                                <td><span class="dash-pill-active" style="background: #e8f0fe; color: #1a73e8;">Advanced Reasoning</span></td>
                            </tr>
                            <tr>
                                <td><strong>Claude 3.5 Sonnet</strong> (<code>claude-3-5-sonnet-20241022</code>)</td>
                                <td>Anthropic</td>
                                <td><code>$3.000</code></td>
                                <td><code>$15.000</code></td>
                                <td><span class="dash-pill-active" style="background: #fef7e0; color: #b06000;">Flagship Quality</span></td>
                            </tr>
                            <tr>
                                <td><strong>Claude 3.5 Haiku</strong> (<code>claude-3-5-haiku-20241022</code>)</td>
                                <td>Anthropic</td>
                                <td><code>$0.800</code></td>
                                <td><code>$4.000</code></td>
                                <td><span class="dash-pill-active" style="background: #e6f4ea; color: #137333;">Balanced High Speed</span></td>
                            </tr>
                            <tr>
                                <td><strong>Claude 3 Opus</strong> (<code>claude-3-opus-20240229</code>)</td>
                                <td>Anthropic</td>
                                <td><code>$15.000</code></td>
                                <td><code>$75.000</code></td>
                                <td><span class="dash-pill-active" style="background: #fce8e6; color: #c5221f;">Heavy Compute</span></td>
                            </tr>
                            <tr>
                                <td><strong>GPT-4o Mini</strong> (<code>gpt-4o-mini</code>)</td>
                                <td>OpenAI</td>
                                <td><code>$0.150</code></td>
                                <td><code>$0.600</code></td>
                                <td><span class="dash-pill-active" style="background: #e6f4ea; color: #137333;">Cost Efficient</span></td>
                            </tr>
                            <tr>
                                <td><strong>GPT-4o</strong> (<code>gpt-4o</code>)</td>
                                <td>OpenAI</td>
                                <td><code>$2.500</code></td>
                                <td><code>$10.000</code></td>
                                <td><span class="dash-pill-active" style="background: #e8f0fe; color: #1a73e8;">General Purpose</span></td>
                            </tr>
                            <tr>
                                <td><strong>DeepSeek V3</strong> (<code>deepseek/deepseek-chat</code>)</td>
                                <td>OpenRouter</td>
                                <td><code>$0.140</code></td>
                                <td><code>$0.280</code></td>
                                <td><span class="dash-pill-active" style="background: #e6f4ea; color: #137333;">High Efficiency</span></td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <hr style="border: 0; border-top: 1px solid var(--dash-border); margin: 28px 0;">

                <h3>3. Practical Tips for Budget Optimization</h3>
                <ul>
                    <li><strong>Production Recommendation:</strong> <code>gemini-2.5-flash</code> provides the perfect sweet spot: sub-second generation, high factual precision with lighting catalogs, and costing less than <strong>$0.01 for every 15-20 conversations</strong>.</li>
                    <li><strong>System Prompt Compression:</strong> Keep system rules and company context concise and focused; since the system prompt is evaluated on every conversation turn, concise wording reduces input tokens exponentially.</li>
                    <li><strong>Auditing Outliers:</strong> Review the <a href="{{ route('dashboard.chat-sessions.index', ['sort' => 'highest_cost']) }}" class="dash-doc-inline-link"><strong>Chat Logs (Sorted by Highest Cost)</strong></a> to identify unusually long conversation sessions or automated scrapers.</li>
                </ul>
            </article>
        @endif

        {{-- AI.3: AI WIDGET & BRANDING --}}
        @if ($activeTopic === 'ai-widget')
            <article class="dash-card dash-doc-section" data-doc-block>
                <div class="dash-doc-header">
                    <h2>AI Chat Widget, Branding & Lead Capture</h2>
                    <span class="dash-pill-active">Front-End Experience & Lead Gen</span>
                </div>
                <p>The <strong>AI Widget</strong> module controls the visual appearance, identity, welcome speech, and lead capture behavior of the floating conversational assistant on the live Azoogi website. Administrators can brand the assistant, customize welcome greetings, set up interactive suggestion chips, and configure required sales intake fields.</p>

                <div class="dash-doc-grid-cards">
                    <div class="dash-doc-feature-card">
                        <div class="dash-doc-icon-wrap">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><rect x="3" y="3" width="18" height="18" rx="4"/><path d="M9 3v18M15 9h6M15 15h6"/></svg>
                        </div>
                        <h3>Assistant Identity & Avatar</h3>
                        <p>Customize the assistant's name, role subtitle, and upload a custom branded avatar image.</p>
                        <a href="{{ route('dashboard.ai.widget') }}" class="dash-doc-inline-link">Go to AI Widget &rarr;</a>
                    </div>

                    <div class="dash-doc-feature-card">
                        <div class="dash-doc-icon-wrap">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
                        </div>
                        <h3>Startup & Welcome Message</h3>
                        <p>Define the friendly greeting displayed when a visitor clicks to open the chat window.</p>
                    </div>

                    <div class="dash-doc-feature-card">
                        <div class="dash-doc-icon-wrap">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><rect x="3" y="4" width="18" height="18" rx="2"/><path d="M16 2v4M8 2v4M3 10h18M8 14h.01M12 14h.01M16 14h.01M8 18h.01M12 18h.01M16 18h.01"/></svg>
                        </div>
                        <h3>Lead Intake Form</h3>
                        <p>Capture customer name, email address, and architectural project scope before or during chat.</p>
                    </div>

                    <div class="dash-doc-feature-card">
                        <div class="dash-doc-icon-wrap">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M4 6h16M4 12h16M4 18h10"/></svg>
                        </div>
                        <h3>Starter Prompt Chips</h3>
                        <p>Provide 1-click suggested prompts (e.g. Casambi controls, IP68 Neon Flex) for instant engagement.</p>
                    </div>
                </div>

                <hr style="border: 0; border-top: 1px solid var(--dash-border); margin: 28px 0;">

                <h3>1. Visual Branding & Identity Settings</h3>
                <p>In <a href="{{ route('dashboard.ai.widget') }}" class="dash-doc-inline-link"><strong>AI &rarr; Widget</strong></a>, configure how your AI assistant introduces itself:</p>

                <ul>
                    <li><strong>AI Assistant Name:</strong> The primary display name shown in the chat window header (e.g. <em>Azoogi Lighting Consultant</em> or <em>Azoogi AI Assistant</em>).</li>
                    <li><strong>Assistant Subtitle / Role:</strong> A professional sub-heading (e.g. <em>Architectural Lighting & Smart Controls Specialist</em>).</li>
                    <li><strong>Custom Avatar Image:</strong> Upload a square PNG, JPG, WebP, or SVG image (max 2MB). The system automatically saves the file to public storage (<code>/storage/ai/avatars/...</code>) and links it immediately. Alternatively, provide an absolute image URL.</li>
                </ul>

                <hr style="border: 0; border-top: 1px solid var(--dash-border); margin: 28px 0;">

                <h3>2. Conversational Messages & Dynamic Templates</h3>

                <div class="dash-doc-steps">
                    <div class="dash-doc-step">
                        <div class="dash-doc-step-num">1</div>
                        <div class="dash-doc-step-content">
                            <h4>Startup Greeting Message</h4>
                            <p>This message appears automatically as the first chat bubble when a visitor opens the widget. Example:</p>
                            <div class="dash-doc-code-block" style="margin-top: 8px;">
                                <pre><code>Hi there! 👋 Welcome to Azoogi Architectural Lighting. How can I assist you with your linear LED, profiles, or Casambi / DALI controls project today?</code></pre>
                            </div>
                        </div>
                    </div>
                    <div class="dash-doc-step">
                        <div class="dash-doc-step-num">2</div>
                        <div class="dash-doc-step-content">
                            <h4>Lead Intake Greeting Template</h4>
                            <p>When a visitor fills out the lead intake form, the assistant can greet them personally using dynamic placeholders:</p>
                            <div class="dash-doc-code-block" style="margin-top: 8px;">
                                <pre><code>Thanks for sharing your details, {name}! I see you're working on "{project}". Let's find the exact fixtures and control specifications you need.</code></pre>
                            </div>
                            <p style="font-size: 13px; color: var(--muted); margin-top: 6px;">Supported placeholders: <code>{name}</code>, <code>{project}</code>, <code>{email}</code>.</p>
                        </div>
                    </div>
                </div>

                <hr style="border: 0; border-top: 1px solid var(--dash-border); margin: 28px 0;">

                <h3>3. Lead Intake Form & Field Requirements</h3>
                <p>The widget includes an interactive Lead Intake modal that captures verified contact information and routes qualified leads directly to the sales team:</p>

                <div class="dash-table-wrap">
                    <table class="dash-doc-table">
                        <thead>
                            <tr>
                                <th style="width: 220px;">Intake Setting</th>
                                <th style="width: 120px;">Default</th>
                                <th>Description & Operational Impact</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td><strong>Enable Lead Intake Form</strong></td>
                                <td><code>Enabled</code></td>
                                <td>Toggles the modal prompt asking users if they would like to share their project info for tailored technical advice.</td>
                            </tr>
                            <tr>
                                <td><strong>Require Customer Name</strong></td>
                                <td><code>Optional</code></td>
                                <td>When enabled, visitors must enter their full name before starting the conversation.</td>
                            </tr>
                            <tr>
                                <td><strong>Require Email Address</strong></td>
                                <td><code>Optional</code></td>
                                <td>When enabled, visitors must provide a valid email address, allowing automated quote follow-ups.</td>
                            </tr>
                            <tr>
                                <td><strong>Require Project Name / Scope</strong></td>
                                <td><code>Optional</code></td>
                                <td>When enabled, requires architects/contractors to enter their project name (e.g. <em>Sydney Harbour Residence</em>).</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <hr style="border: 0; border-top: 1px solid var(--dash-border); margin: 28px 0;">

                <h3>4. Starter Prompt Chips (Quick Questions)</h3>
                <p>Starter prompt chips appear below the welcome message as clickable pills. Clicking a chip instantly sends that prompt, guiding visitors directly to high-value solutions.</p>

                <div class="dash-doc-callout info">
                    <strong>Recommended Starter Chips for Lighting Projects:</strong>
                    <ul style="margin-top: 8px; margin-bottom: 0;">
                        <li><code>Recommend IP68 Neon Flex for outdoor facade lighting</code></li>
                        <li><code>How do I set up Casambi Bluetooth wireless control?</code></li>
                        <li><code>Compare DALI-2 drivers vs standard Phase dimming</code></li>
                        <li><code>Request architectural sample pack & product catalog</code></li>
                    </ul>
                </div>
            </article>
        @endif

        {{-- AI.4: AI RULES & GUARDRAILS --}}
        @if ($activeTopic === 'ai-rules')
            <article class="dash-card dash-doc-section" data-doc-block>
                <div class="dash-doc-header">
                    <h2>AI System Rules, Guardrails & Directives</h2>
                    <span class="dash-pill-active">Prompt Governance & Safety</span>
                </div>
                <p>The <strong>AI Rules</strong> module allows administrators to establish strict operational boundaries, brand voice guidelines, safety guardrails, and lighting domain policies. Every active rule is automatically compiled into the master system prompt fed to the LLM on each conversation turn.</p>

                <div class="dash-doc-grid-cards">
                    <div class="dash-doc-feature-card">
                        <div class="dash-doc-icon-wrap">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><polyline points="9 11 12 14 22 4"/><path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"/></svg>
                        </div>
                        <h3>Granular Rule Management</h3>
                        <p>Create, edit, and organize system directives with clear titles, categories, and detailed guidelines.</p>
                        <a href="{{ route('dashboard.ai.rules') }}" class="dash-doc-inline-link">Go to AI Rules &rarr;</a>
                    </div>

                    <div class="dash-doc-feature-card">
                        <div class="dash-doc-icon-wrap">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M18.36 6.64a9 9 0 1 1-12.73 0"/><line x1="12" y1="2" x2="12" y2="12"/></svg>
                        </div>
                        <h3>Live AJAX Active Toggle</h3>
                        <p>Instantly turn specific rules on or off without deleting them—perfect for seasonal promotions or A/B testing.</p>
                    </div>

                    <div class="dash-doc-feature-card">
                        <div class="dash-doc-icon-wrap">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
                        </div>
                        <h3>Safety & Compliance Guardrails</h3>
                        <p>Enforce strict guardrails against competitor mentions, off-topic discussions, or unverified electrical advice.</p>
                    </div>

                    <div class="dash-doc-feature-card">
                        <div class="dash-doc-icon-wrap">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
                        </div>
                        <h3>Dynamic Prompt Injection</h3>
                        <p>Active rules are automatically structured under the <code>[SYSTEM RULES & GUARDRAILS]</code> prompt block.</p>
                    </div>
                </div>

                <hr style="border: 0; border-top: 1px solid var(--dash-border); margin: 28px 0;">

                <h3>1. Rule Categories & Best Practices</h3>
                <p>Organize rules under descriptive categories to keep your system prompt structured and maintainable:</p>

                <div class="dash-table-wrap">
                    <table class="dash-doc-table">
                        <thead>
                            <tr>
                                <th style="width: 200px;">Rule Category</th>
                                <th>Purpose & Example Directives</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td><strong>Safety & Guardrails</strong></td>
                                <td>Enforce electrical safety disclosures. Example: <em>"Always advise clients that 240V mains wiring and DALI bus connections must be installed by a licensed electrical contractor."</em></td>
                            </tr>
                            <tr>
                                <td><strong>Tone & Brand Voice</strong></td>
                                <td>Maintain an authoritative, sophisticated architectural tone. Example: <em>"Respond with professional expertise, using precise lighting design terminology (CRI, CCT, lumens/watt, IP ratings)."</em></td>
                            </tr>
                            <tr>
                                <td><strong>Product Recommendations</strong></td>
                                <td>Guide how fixtures are suggested. Example: <em>"Always recommend matching aluminum extrusion profiles and appropriate 24V constant voltage drivers whenever suggesting COB or Neon Flex strips."</em></td>
                            </tr>
                            <tr>
                                <td><strong>Sales & Lead Capture</strong></td>
                                <td>Drive quote conversion. Example: <em>"When a visitor specifies dimensions or quantities exceeding 10 meters, proactively encourage them to submit an architectural quote inquiry."</em></td>
                            </tr>
                            <tr>
                                <td><strong>Australian Standards</strong></td>
                                <td>Ensure compliance with national standards. Example: <em>"Reference AS/NZS 1680 interior lighting guidelines and AS/NZS 3000 wiring standards when answering technical questions."</em></td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <hr style="border: 0; border-top: 1px solid var(--dash-border); margin: 28px 0;">

                <h3>2. How to Add or Modify a System Rule</h3>

                <div class="dash-doc-steps">
                    <div class="dash-doc-step">
                        <div class="dash-doc-step-num">1</div>
                        <div class="dash-doc-step-content">
                            <h4>Navigate to AI Rules</h4>
                            <p>Open <a href="{{ route('dashboard.ai.rules') }}" class="dash-doc-inline-link"><strong>AI &rarr; Rules</strong></a>.</p>
                        </div>
                    </div>
                    <div class="dash-doc-step">
                        <div class="dash-doc-step-num">2</div>
                        <div class="dash-doc-step-content">
                            <h4>Enter Rule Details</h4>
                            <p>In the <strong>Add New System Rule</strong> card, select or type a <strong>Category</strong>, specify a clear <strong>Rule Title</strong>, and write the <strong>Directive Content</strong> (up to 4,000 characters).</p>
                        </div>
                    </div>
                    <div class="dash-doc-step">
                        <div class="dash-doc-step-num">3</div>
                        <div class="dash-doc-step-content">
                            <h4>Save & Verify Active Status</h4>
                            <p>Click <strong>Save System Rule</strong>. Ensure the status badge displays <strong>Active</strong>. You can click the toggle button anytime to switch between Active and Inactive.</p>
                        </div>
                    </div>
                </div>
            </article>
        @endif

        {{-- AI.5: COMPANY INFO & CONTEXT --}}
        @if ($activeTopic === 'ai-context')
            <article class="dash-card dash-doc-section" data-doc-block>
                <div class="dash-doc-header">
                    <h2>Company Info, Logistics & Operational Context</h2>
                    <span class="dash-pill-active">Enterprise Grounding & Facts</span>
                </div>
                <p>The <strong>Company Info</strong> module manages foundational operational intelligence and domain knowledge about Azoogi. It grounds the AI assistant in verified facts regarding Azoogi's Sydney headquarters, custom extrusion cutting services, dispatch timelines, Australian Standards compliance, and trade support policies.</p>

                <div class="dash-doc-grid-cards">
                    <div class="dash-doc-feature-card">
                        <div class="dash-doc-icon-wrap">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>
                        </div>
                        <h3>Enterprise Knowledge</h3>
                        <p>Store verified facts about Azoogi's business model, Sydney warehouse, and manufacturing capacity.</p>
                        <a href="{{ route('dashboard.ai.context') }}" class="dash-doc-inline-link">Go to Company Info &rarr;</a>
                    </div>

                    <div class="dash-doc-feature-card">
                        <div class="dash-doc-icon-wrap">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><rect x="1" y="3" width="15" height="13"/><polygon points="16 8 20 8 23 11 23 16 16 16 16 8"/><circle cx="5.5" cy="18.5" r="2.5"/><circle cx="18.5" cy="18.5" r="2.5"/></svg>
                        </div>
                        <h3>Logistics & Lead Times</h3>
                        <p>Keep the AI updated with accurate dispatch times, courier cutoffs, and custom fabrication turnarounds.</p>
                    </div>

                    <div class="dash-doc-feature-card">
                        <div class="dash-doc-icon-wrap">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
                        </div>
                        <h3>Warranties & Standards</h3>
                        <p>Ground responses in official 5-year commercial warranty terms, RCM compliance, and IP test standards.</p>
                    </div>

                    <div class="dash-doc-feature-card">
                        <div class="dash-doc-icon-wrap">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                        </div>
                        <h3>Trade & Project Pricing</h3>
                        <p>Inform the assistant on how to handle wholesale trade discounts, contractor accounts, and sample requests.</p>
                    </div>
                </div>

                <hr style="border: 0; border-top: 1px solid var(--dash-border); margin: 28px 0;">

                <h3>1. Key Context Domains & Sample Data</h3>
                <p>Company info snippets are organized into logical domains to give the AI a complete, multi-faceted understanding of Azoogi's operations:</p>

                <div class="dash-table-wrap">
                    <table class="dash-doc-table">
                        <thead>
                            <tr>
                                <th style="width: 220px;">Context Domain</th>
                                <th>Recommended Content & Operational Details</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td><strong>Company Background</strong></td>
                                <td>Azoogi is an Australian architectural lighting supplier based in Sydney, NSW, specializing in linear LED systems, commercial extrusion profiles, and smart wireless lighting control systems (Casambi, Silvair, DALI-2, MADRIX).</td>
                            </tr>
                            <tr>
                                <td><strong>Warehouse & Logistics</strong></td>
                                <td>Orders for in-stock items placed before 1:00 PM AEST dispatch same-day from the Sydney distribution facility. Standard delivery across Australia takes 2-4 business days. Express courier options are available for urgent site deliveries.</td>
                            </tr>
                            <tr>
                                <td><strong>Custom Extrusion Cutting</strong></td>
                                <td>Azoogi offers in-house precision cutting, soldering, and assembly of linear profiles to millimeter accuracy. Standard turnaround for custom pre-cut linear profiles is 3-5 business days.</td>
                            </tr>
                            <tr>
                                <td><strong>Warranty & Guarantees</strong></td>
                                <td>Standard 5-year commercial replacement warranty on all architectural LED strips and commercial drivers. Products hold Australian RCM (Regulatory Compliance Mark) certifications and strict photobiological safety ratings.</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <hr style="border: 0; border-top: 1px solid var(--dash-border); margin: 28px 0;">

                <h3>2. Adding & Managing Context Items</h3>
                <ol>
                    <li>Go to <a href="{{ route('dashboard.ai.context') }}" class="dash-doc-inline-link"><strong>AI &rarr; Company Info</strong></a>.</li>
                    <li>Fill out <strong>Category</strong>, <strong>Title</strong>, and <strong>Context Content</strong>.</li>
                    <li>Toggle the item <strong>Active</strong>. The content will be included in the live compiled system prompt immediately.</li>
                    <li>Use the <strong>Active / Inactive Toggle</strong> to temporarily disable snippets (e.g. during end-of-year warehouse inventory closures).</li>
                </ol>
            </article>
        @endif

        {{-- AI.6: AI FAQS --}}
        @if ($activeTopic === 'ai-faqs')
            <article class="dash-card dash-doc-section" data-doc-block>
                <div class="dash-doc-header">
                    <h2>FAQ Knowledge Base & Curated Q&A</h2>
                    <span class="dash-pill-active">Domain Knowledge Base</span>
                </div>
                <p>The <strong>AI FAQs</strong> module provides a structured question-and-answer library for instant, high-precision retrieval of common technical questions, control protocol inquiries, fixture compatibility facts, and customer service details.</p>

                <div class="dash-doc-grid-cards">
                    <div class="dash-doc-feature-card">
                        <div class="dash-doc-icon-wrap">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="12" cy="12" r="10"/><path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
                        </div>
                        <h3>Curated Q&A Pairs</h3>
                        <p>Store verified answers to frequent customer questions regarding Casambi, DALI, Neon Flex, and drivers.</p>
                        <a href="{{ route('dashboard.ai.faqs') }}" class="dash-doc-inline-link">Go to AI FAQs &rarr;</a>
                    </div>

                    <div class="dash-doc-feature-card">
                        <div class="dash-doc-icon-wrap">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M4 6h16M4 10h16M4 14h16M4 18h16"/></svg>
                        </div>
                        <h3>Categorized Knowledge</h3>
                        <p>Group FAQs by product lines, smart controls, installation techniques, and ordering policies.</p>
                    </div>

                    <div class="dash-doc-feature-card">
                        <div class="dash-doc-icon-wrap">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M18.36 6.64a9 9 0 1 1-12.73 0"/><line x1="12" y1="2" x2="12" y2="12"/></svg>
                        </div>
                        <h3>Instant Active Toggle</h3>
                        <p>Enable or silence individual FAQ items in real-time with an AJAX switch without modifying code.</p>
                    </div>

                    <div class="dash-doc-feature-card">
                        <div class="dash-doc-icon-wrap">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>
                        </div>
                        <h3>Zero Hallucination Grounding</h3>
                        <p>All active FAQs are automatically indexed and presented to the LLM as authoritative references.</p>
                    </div>
                </div>

                <hr style="border: 0; border-top: 1px solid var(--dash-border); margin: 28px 0;">

                <h3>1. High-Impact Lighting FAQ Examples</h3>
                <div class="dash-table-wrap">
                    <table class="dash-doc-table">
                        <thead>
                            <tr>
                                <th style="width: 160px;">Category</th>
                                <th style="width: 280px;">Question</th>
                                <th>Verified Technical Answer</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td><strong>Smart Controls</strong></td>
                                <td><em>Can Casambi Bluetooth modules control standard 24V constant voltage LED strips?</em></td>
                                <td>Yes. Casambi CVM or PWM dimming units wire directly between the 24V DC driver output and the LED strip, providing smooth 0.1–100% flicker-free dimming and color tuning without extra control wiring.</td>
                            </tr>
                            <tr>
                                <td><strong>Extrusions</strong></td>
                                <td><em>What is the maximum single run length for Azoogi aluminum profiles?</em></td>
                                <td>Standard profiles are supplied in 2.0-meter and 3.0-meter stock lengths and can be seamlessly joined using alignment pins for continuous architectural runs. Custom factory pre-cuts are also available.</td>
                            </tr>
                            <tr>
                                <td><strong>Waterproofing</strong></td>
                                <td><em>What is the difference between IP65 and IP68 Neon Flex?</em></td>
                                <td>IP65 Neon Flex is weather-resistant against water jets for general outdoor architectural outlines. IP68 Neon Flex features factory-sealed injection-moulded end caps suitable for submerged swimming pool and water feature applications.</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <hr style="border: 0; border-top: 1px solid var(--dash-border); margin: 28px 0;">

                <h3>2. How to Add an FAQ Knowledge Item</h3>
                <div class="dash-doc-steps">
                    <div class="dash-doc-step">
                        <div class="dash-doc-step-num">1</div>
                        <div class="dash-doc-step-content">
                            <h4>Navigate to AI FAQs</h4>
                            <p>Open <a href="{{ route('dashboard.ai.faqs') }}" class="dash-doc-inline-link"><strong>AI &rarr; FAQs</strong></a>.</p>
                        </div>
                    </div>
                    <div class="dash-doc-step">
                        <div class="dash-doc-step-num">2</div>
                        <div class="dash-doc-step-content">
                            <h4>Enter FAQ Details</h4>
                            <p>Select a <strong>Category</strong>, input the <strong>Question</strong> (up to 300 characters), and enter the verified <strong>Answer</strong> (up to 1,500 characters).</p>
                        </div>
                    </div>
                    <div class="dash-doc-step">
                        <div class="dash-doc-step-num">3</div>
                        <div class="dash-doc-step-content">
                            <h4>Save & Verify</h4>
                            <p>Click <strong>Save FAQ Item</strong>. The AI will immediately utilize the Q&A entry in all new visitor conversations.</p>
                        </div>
                    </div>
                </div>
            </article>
        @endif

        {{-- AI.7: AI SYSTEM PROMPT INSPECTOR --}}
        @if ($activeTopic === 'ai-prompt')
            <article class="dash-card dash-doc-section" data-doc-block>
                <div class="dash-doc-header">
                    <h2>Live System Prompt Inspector & Token Metrics</h2>
                    <span class="dash-pill-active">System Prompt Diagnostics</span>
                </div>
                <p>The <strong>AI Prompt</strong> module provides complete architectural transparency into the exact, real-time system prompt generated by the backend and sent to the LLM provider API. It displays estimated token counts, component item breakdowns, and enables 1-click prompt copying for benchmarking in external developer tools.</p>

                <div class="dash-doc-grid-cards">
                    <div class="dash-doc-feature-card">
                        <div class="dash-doc-icon-wrap">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/></svg>
                        </div>
                        <h3>Live Prompt Rendering</h3>
                        <p>View the exact master prompt assembled by <code>ChatOrchestrator</code> in real time.</p>
                        <a href="{{ route('dashboard.ai.prompt') }}" class="dash-doc-inline-link">Go to AI Prompt &rarr;</a>
                    </div>

                    <div class="dash-doc-feature-card">
                        <div class="dash-doc-icon-wrap">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="12" cy="12" r="9"/><polyline points="12 7 12 12 15 15"/></svg>
                        </div>
                        <h3>Live Token Estimator</h3>
                        <p>Real-time calculation of prompt token weight to optimize response latency and context sizing.</p>
                    </div>

                    <div class="dash-doc-feature-card">
                        <div class="dash-doc-icon-wrap">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/></svg>
                        </div>
                        <h3>Assembly Component Badges</h3>
                        <p>Visual count badges showing active Rules, Company Context snippets, and FAQ items included in the prompt.</p>
                    </div>

                    <div class="dash-doc-feature-card">
                        <div class="dash-doc-icon-wrap">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><rect x="9" y="9" width="13" height="13" rx="2" ry="2"/><path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"/></svg>
                        </div>
                        <h3>1-Click Playground Copy</h3>
                        <p>Instantly copy the compiled system prompt to clipboard for testing in Google AI Studio or Claude Workbench.</p>
                    </div>
                </div>

                <hr style="border: 0; border-top: 1px solid var(--dash-border); margin: 28px 0;">

                <h3>1. Master System Prompt Assembly Hierarchy</h3>
                <p>When assembling the system prompt, <code>ChatOrchestrator::getSystemPrompt()</code> dynamically builds and formats the following layers in strict sequential hierarchy:</p>

                <div class="dash-doc-code-block" style="margin-bottom: 20px;">
                    <div class="dash-doc-code-header"><span>Prompt Assembly Architecture</span></div>
                    <pre><code>[1. CORE IDENTITY & WIDGET BRANDING]
   - Assistant Name, Role, Brand Tone & Architectural Demeanor

[2. SYSTEM RULES & GUARDRAILS]
   - Active Behavioral Directives, Safety Disclosures, Negative Constraints

[3. COMPANY CONTEXT & OPERATIONAL LOGISTICS]
   - Sydney Warehouse Location, Lead Times, Custom Cutting, Australian Standards

[4. FAQ KNOWLEDGE BASE & TECHNICAL FACTS]
   - Curated Q&A pairs (Casambi, DALI-2, Neon Flex, Profiles, Power Supplies)

[5. PRODUCT CATALOG SCHEMA & CATEGORY HIERARCHY]
   - Airtable Catalog Structure, Category Numbering Blocks (100–700), Specs

[6. LEAD CAPTURE & CRM CONVERSION INSTRUCTIONS]
   - Format Directives for capturing Project Scope, Name, Email, and Quote Requirements</code></pre>
                </div>

                <hr style="border: 0; border-top: 1px solid var(--dash-border); margin: 28px 0;">

                <h3>2. How to Inspect & Validate Prompt Updates</h3>
                <ol>
                    <li>After adding or updating any rule in <a href="{{ route('dashboard.ai.rules') }}" class="dash-doc-inline-link"><strong>AI Rules</strong></a>, company info in <a href="{{ route('dashboard.ai.context') }}" class="dash-doc-inline-link"><strong>Company Info</strong></a>, or FAQs in <a href="{{ route('dashboard.ai.faqs') }}" class="dash-doc-inline-link"><strong>AI FAQs</strong></a>, navigate to <a href="{{ route('dashboard.ai.prompt') }}" class="dash-doc-inline-link"><strong>AI &rarr; Prompt</strong></a>.</li>
                    <li>Inspect the <strong>Active Component Badges</strong> (e.g. <em>8 Active Rules</em>, <em>4 Active Context Items</em>, <em>6 Active FAQs</em>).</li>
                    <li>Check the <strong>Estimated Prompt Tokens</strong> meter (typically ~2,500 to ~4,500 tokens).</li>
                    <li>Scroll through the live prompt viewer to verify that your new instructions are formatted cleanly and unambiguously.</li>
                    <li>Click <strong>Copy Prompt to Clipboard</strong> if you want to benchmark responses against test user queries in external LLM sandboxes.</li>
                </ol>
            </article>
        @endif

        {{-- AI.8: AI CHAT LOGS & SESSIONS --}}
        @if ($activeTopic === 'ai-chat-logs')
            <article class="dash-card dash-doc-section" data-doc-block>
                <div class="dash-doc-header">
                    <h2>Chat Logs, Visitor Sessions & Lead Conversions</h2>
                    <span class="dash-pill-active">Conversational Intelligence & CRM</span>
                </div>
                <p>The <strong>Chat Logs</strong> module provides a real-time conversational intelligence dashboard and sales conversion engine. It logs every website visitor chat session with turn-by-turn transcripts, granular token counts, computed USD costs, visitor geo-location, and enables 1-click conversion of high-intent chat discussions into official sales quote enquiries.</p>

                <div class="dash-doc-grid-cards">
                    <div class="dash-doc-feature-card">
                        <div class="dash-doc-icon-wrap">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M21 11.5a8.38 8.38 0 0 1-.9 3.8 8.5 8.5 0 0 1-7.6 4.7 8.38 8.38 0 0 1-3.8-.9L3 21l1.9-5.7a8.38 8.38 0 0 1-.9-3.8 8.5 8.5 0 0 1 4.7-7.6 8.38 8.38 0 0 1 3.8-.9h.5a8.48 8.48 0 0 1 8 8v.5z"/></svg>
                        </div>
                        <h3>Live Session Monitor</h3>
                        <p>Browse full turn-by-turn transcripts of all visitor interactions with timestamps and status tags.</p>
                        <a href="{{ route('dashboard.chat-sessions.index') }}" class="dash-doc-inline-link">Go to Chat Logs &rarr;</a>
                    </div>

                    <div class="dash-doc-feature-card">
                        <div class="dash-doc-icon-wrap">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                        </div>
                        <h3>1-Click Lead Conversion</h3>
                        <p>Convert promising chat conversations directly into official CRM Quote Enquiries with pre-filled details.</p>
                    </div>

                    <div class="dash-doc-feature-card">
                        <div class="dash-doc-icon-wrap">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>
                        </div>
                        <h3>Starred Favorites & Read State</h3>
                        <p>Star important conversations for team review and track unread sessions with instant status indicators.</p>
                    </div>

                    <div class="dash-doc-feature-card">
                        <div class="dash-doc-icon-wrap">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><line x1="8" y1="6" x2="21" y2="6"/><line x1="8" y1="12" x2="21" y2="12"/><line x1="8" y1="18" x2="21" y2="18"/><line x1="3" y1="6" x2="3.01" y2="6"/><line x1="3" y1="12" x2="3.01" y2="12"/><line x1="3" y1="18" x2="3.01" y2="18"/></svg>
                        </div>
                        <h3>Advanced Search & Filtering</h3>
                        <p>Filter by date range, leads only, unread status, or search by keyword, visitor email, company, and IP.</p>
                    </div>
                </div>

                <hr style="border: 0; border-top: 1px solid var(--dash-border); margin: 28px 0;">

                <h3>1. Key Session Metrics & Analytics</h3>
                <p>The Chat Logs header displays real-time operational KPIs:</p>

                <ul>
                    <li><strong>Total Conversations:</strong> Lifetime count of all visitor sessions initiated on the website.</li>
                    <li><strong>Unread Sessions:</strong> Number of new visitor conversations that have not yet been reviewed by staff.</li>
                    <li><strong>Active Today:</strong> Count of visitor interactions recorded in the current calendar day.</li>
                    <li><strong>Starred / Favorites:</strong> High-priority architectural leads bookmarked by team members.</li>
                    <li><strong>Total & Average Tokens:</strong> Comprehensive breakdown of prompt (input) vs completion (output) tokens.</li>
                    <li><strong>Total Spend ($ USD):</strong> Aggregate dollar expenditure across all session transcripts.</li>
                </ul>

                <hr style="border: 0; border-top: 1px solid var(--dash-border); margin: 28px 0;">

                <h3>2. Inspecting Transcripts & Message Details</h3>
                <p>Clicking on any session row in <a href="{{ route('dashboard.chat-sessions.index') }}" class="dash-doc-inline-link"><strong>AI &rarr; Chat Logs</strong></a> opens the dedicated transcript viewer:</p>

                <div class="dash-table-wrap">
                    <table class="dash-doc-table">
                        <thead>
                            <tr>
                                <th style="width: 200px;">Transcript Element</th>
                                <th>Description & Insight</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td><strong>Lead Information Card</strong></td>
                                <td>Displays visitor Name, Email, Company, Project Name, IP Address, and Country origin captured by the Lead Intake Form.</td>
                            </tr>
                            <tr>
                                <td><strong>Message Bubbles</strong></td>
                                <td>Color-coded conversational view separating visitor queries (right) and assistant replies (left) with markdown rendering.</td>
                            </tr>
                            <tr>
                                <td><strong>Token & Cost Badges</strong></td>
                                <td>Each individual assistant response displays the exact model used, prompt tokens, completion tokens, and dollar cost for that turn.</td>
                            </tr>
                            <tr>
                                <td><strong>Quick Action Bar</strong></td>
                                <td>Buttons to <em>Convert to Enquiry</em>, <em>Toggle Star/Favorite</em>, <em>Mark as Read/Unread</em>, or <em>Delete Session</em>.</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <hr style="border: 0; border-top: 1px solid var(--dash-border); margin: 28px 0;">

                <h3>3. 1-Click Converting Chat to Sales Enquiry</h3>

                <div class="dash-doc-steps">
                    <div class="dash-doc-step">
                        <div class="dash-doc-step-num">1</div>
                        <div class="dash-doc-step-content">
                            <h4>Open the High-Intent Chat Session</h4>
                            <p>Locate the conversation in <a href="{{ route('dashboard.chat-sessions.index') }}" class="dash-doc-inline-link"><strong>AI &rarr; Chat Logs</strong></a> and click to view the transcript.</p>
                        </div>
                    </div>
                    <div class="dash-doc-step">
                        <div class="dash-doc-step-num">2</div>
                        <div class="dash-doc-step-content">
                            <h4>Click "Convert to Enquiry"</h4>
                            <p>Click the <strong>Convert to Quote Enquiry</strong> button in the top action toolbar.</p>
                        </div>
                    </div>
                    <div class="dash-doc-step">
                        <div class="dash-doc-step-num">3</div>
                        <div class="dash-doc-step-content">
                            <h4>Review Pre-Filled Quote Details</h4>
                            <p>The system automatically transfers the customer's full name, email, company, project scope, and full chat transcript into the Azoogi Enquiry CRM.</p>
                        </div>
                    </div>
                    <div class="dash-doc-step">
                        <div class="dash-doc-step-num">4</div>
                        <div class="dash-doc-step-content">
                            <h4>Follow Up in Enquiries Dashboard</h4>
                            <p>The enquiry is now tracked in <a href="{{ route('dashboard.enquiries.index', ['type' => 'quote']) }}" class="dash-doc-inline-link"><strong>Enquiries &rarr; Quote</strong></a> with direct links back to the original AI chat session.</p>
                        </div>
                    </div>
                </div>
            </article>
        @endif

        {{-- AI TEST SUITE & TOKEN BENCHMARKS --}}
        @if ($activeTopic === 'ai-testing')
            <article class="dash-card dash-doc-section" data-doc-block>
                <div class="dash-doc-header">
                    <h2>AI Model Testing, Tool Verification & Token Benchmarks</h2>
                    <span class="dash-pill-active">AI Test Suite</span>
                </div>
                <p>This comprehensive test suite enables administrators and engineers to systematically test every registered tool, measure token consumption, observe latency, and verify accurate cost estimation across all supported AI engines (<strong>Anthropic Claude</strong>, <strong>Google Gemini</strong>, <strong>OpenRouter Gateway</strong>, and <strong>Prisha AI / Custom Endpoints</strong>).</p>

                <div class="dash-doc-grid-cards">
                    <div class="dash-doc-feature-card">
                        <div class="dash-doc-icon-wrap">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M12 2v4M12 18v4M4.93 4.93l2.83 2.83M16.24 16.24l2.83 2.83M2 12h4M18 12h4M4.93 19.07l2.83-2.83M16.24 7.76l2.83-2.83"/></svg>
                        </div>
                        <h3>Provider Comparison</h3>
                        <p>Evaluate response fidelity, function calling accuracy, and output formats between Claude, Gemini, DeepSeek, and local Ollama models.</p>
                        <a href="{{ route('dashboard.ai.models') }}" class="dash-doc-inline-link">Switch AI Provider &rarr;</a>
                    </div>

                    <div class="dash-doc-feature-card">
                        <div class="dash-doc-icon-wrap">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                        </div>
                        <h3>Token & Latency Auditing</h3>
                        <p>Observe input prompt token overhead, tool definition schema sizing, completion token generation, and round-trip execution latency.</p>
                        <a href="{{ route('dashboard.chat-sessions.index') }}" class="dash-doc-inline-link">Inspect Chat Logs &rarr;</a>
                    </div>

                    <div class="dash-doc-feature-card">
                        <div class="dash-doc-icon-wrap">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg>
                        </div>
                        <h3>Cost Engine Verification</h3>
                        <p>Verify that real-time USD cost calculation matches official provider rate cards across simple and complex multi-turn sessions.</p>
                        <a href="{{ route('dashboard.ai.rates') }}" class="dash-doc-inline-link">View AI Rate Cards &rarr;</a>
                    </div>

                    <div class="dash-doc-feature-card">
                        <div class="dash-doc-icon-wrap">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><polyline points="9 11 12 14 22 4"/><path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"/></svg>
                        </div>
                        <h3>100% Tool Coverage</h3>
                        <p>Systematic test prompts covering all 6 registered function-calling tools: search, specs, datasheets, quotes, leads, and transcripts.</p>
                    </div>
                </div>

                <div class="dash-doc-callout info">
                    <strong>How to Benchmark Models:</strong> To benchmark a specific model, navigate to <a href="{{ route('dashboard.ai.models') }}" class="dash-doc-inline-link"><strong>AI &rarr; Models</strong></a>, select the target provider/model (e.g. <code>claude-opus-5-5</code>, <code>gemini-2.5-flash</code>, <code>deepseek/deepseek-chat</code>, or <code>qwen2.5-coder:3b</code>), and click <strong>Save All AI Settings</strong>. Then use the copy buttons below to paste each test prompt into the chat widget and check the resulting tokens in <a href="{{ route('dashboard.chat-sessions.index') }}" class="dash-doc-inline-link"><strong>Chat Logs</strong></a>.
                </div>

                <hr style="border: 0; border-top: 1px solid var(--dash-border); margin: 28px 0;">

                <h3>1. Baseline Conversational Prompts (No Tools Triggered)</h3>
                <p>Use these baseline questions to evaluate raw reasoning, response speed, and token cost without function execution overhead.</p>

                <div class="dash-doc-code-block" style="margin-bottom: 12px;">
                    <pre><code>Can you briefly introduce Azoogi and explain what type of lighting solutions you specialize in?</code></pre>
                    <button type="button" class="dash-doc-copy-btn" data-copy-text="Can you briefly introduce Azoogi and explain what type of lighting solutions you specialize in?">Copy Prompt</button>
                </div>

                <div class="dash-doc-code-block" style="margin-bottom: 24px;">
                    <pre><code>What is the difference between IP65 and IP68 waterproof ratings for architectural lighting?</code></pre>
                    <button type="button" class="dash-doc-copy-btn" data-copy-text="What is the difference between IP65 and IP68 waterproof ratings for architectural lighting?">Copy Prompt</button>
                </div>

                <p style="font-size: 13px; color: var(--dash-muted);"><strong>Expected Outcome:</strong> Immediate fluent response without tool invocations (~150–350 total tokens).</p>

                <hr style="border: 0; border-top: 1px solid var(--dash-border); margin: 28px 0;">

                <h3>2. Tool 1: Product Search & Filter (<code>public_search_and_filter_products</code>)</h3>
                <p>Tests whether the model parses visitor requirements (category, application, IP rating, dimensions) into structured database filter arguments.</p>

                <div class="dash-doc-code-block" style="margin-bottom: 12px;">
                    <pre><code>I am looking for outdoor garden lights. What options do you have available?</code></pre>
                    <button type="button" class="dash-doc-copy-btn" data-copy-text="I am looking for outdoor garden lights. What options do you have available?">Copy Prompt</button>
                </div>

                <div class="dash-doc-code-block" style="margin-bottom: 12px;">
                    <pre><code>Show me your waterproof pool lighting fixtures with high IP rating.</code></pre>
                    <button type="button" class="dash-doc-copy-btn" data-copy-text="Show me your waterproof pool lighting fixtures with high IP rating.">Copy Prompt</button>
                </div>

                <div class="dash-doc-code-block" style="margin-bottom: 24px;">
                    <pre><code>Do you have any trimless bendable aluminium profiles around 50mm to 60mm width?</code></pre>
                    <button type="button" class="dash-doc-copy-btn" data-copy-text="Do you have any trimless bendable aluminium profiles around 50mm to 60mm width?">Copy Prompt</button>
                </div>

                <p style="font-size: 13px; color: var(--dash-muted);"><strong>Expected Tool Call:</strong> <code>public_search_and_filter_products({"query": "garden light", "category": "Garden Light"})</code>. The model returns interactive product recommendation cards with thumbnails and links.</p>

                <hr style="border: 0; border-top: 1px solid var(--dash-border); margin: 28px 0;">

                <h3>3. Tool 2: Product Specifications & Downloads (<code>public_get_product_details_and_downloads</code>)</h3>
                <p>Tests whether the model resolves an entity slug/SKU and fetches deep technical parameters, cutouts, photometrics, and IES files.</p>

                <div class="dash-doc-code-block" style="margin-bottom: 12px;">
                    <pre><code>Can you give me the full technical specifications and download files for the 12W Garden Light?</code></pre>
                    <button type="button" class="dash-doc-copy-btn" data-copy-text="Can you give me the full technical specifications and download files for the 12W Garden Light?">Copy Prompt</button>
                </div>

                <div class="dash-doc-code-block" style="margin-bottom: 24px;">
                    <pre><code>What are the dimensions, cutouts, and available downloads for the Pool Light?</code></pre>
                    <button type="button" class="dash-doc-copy-btn" data-copy-text="What are the dimensions, cutouts, and available downloads for the Pool Light?">Copy Prompt</button>
                </div>

                <p style="font-size: 13px; color: var(--dash-muted);"><strong>Expected Tool Call:</strong> <code>public_get_product_details_and_downloads({"slug": "12w-garden-light"})</code>.</p>

                <hr style="border: 0; border-top: 1px solid var(--dash-border); margin: 28px 0;">

                <h3>4. Tool 3: Custom PDF Datasheet Generation (<code>public_generate_custom_datasheet</code>)</h3>
                <p>Tests automated on-demand PDF compilation for architectural specifiers and lighting designers.</p>

                <div class="dash-doc-code-block" style="margin-bottom: 12px;">
                    <pre><code>Could you generate a custom PDF datasheet for the 7W Garden Light so I can share it with my client?</code></pre>
                    <button type="button" class="dash-doc-copy-btn" data-copy-text="Could you generate a custom PDF datasheet for the 7W Garden Light so I can share it with my client?">Copy Prompt</button>
                </div>

                <div class="dash-doc-code-block" style="margin-bottom: 24px;">
                    <pre><code>Please create a spec sheet for the trimless-bendable-profile-526x137mm.</code></pre>
                    <button type="button" class="dash-doc-copy-btn" data-copy-text="Please create a spec sheet for the trimless-bendable-profile-526x137mm.">Copy Prompt</button>
                </div>

                <p style="font-size: 13px; color: var(--dash-muted);"><strong>Expected Tool Call:</strong> <code>public_generate_custom_datasheet({"slug": "7w-garden-light"})</code>. The model responds with a direct download button and PDF URL.</p>

                <hr style="border: 0; border-top: 1px solid var(--dash-border); margin: 28px 0;">

                <h3>5. Tool 4: Quote Cart Management (<code>public_manage_quote_cart</code>)</h3>
                <p>Tests visitor session quote cart manipulation (adding line items, viewing cart contents, and clearing items).</p>

                <div class="dash-doc-code-block" style="margin-bottom: 12px;">
                    <pre><code>Please add 15 units of the 12W Garden Light to my quote cart.</code></pre>
                    <button type="button" class="dash-doc-copy-btn" data-copy-text="Please add 15 units of the 12W Garden Light to my quote cart.">Copy Prompt (Add)</button>
                </div>

                <div class="dash-doc-code-block" style="margin-bottom: 12px;">
                    <pre><code>What items and quantities do I currently have in my quote cart?</code></pre>
                    <button type="button" class="dash-doc-copy-btn" data-copy-text="What items and quantities do I currently have in my quote cart?">Copy Prompt (View)</button>
                </div>

                <div class="dash-doc-code-block" style="margin-bottom: 24px;">
                    <pre><code>Please remove everything from my quote cart.</code></pre>
                    <button type="button" class="dash-doc-copy-btn" data-copy-text="Please remove everything from my quote cart.">Copy Prompt (Clear)</button>
                </div>

                <p style="font-size: 13px; color: var(--dash-muted);"><strong>Expected Tool Call:</strong> <code>public_manage_quote_cart({"action": "add", "items": [{"slug": "12w-garden-light", "quantity": 15}]})</code>.</p>

                <hr style="border: 0; border-top: 1px solid var(--dash-border); margin: 28px 0;">

                <h3>6. Tool 5: Lead Submission & CRM Intake (<code>public_submit_lead_enquiry</code>)</h3>
                <p>Tests structured multi-field extraction (name, email, phone number, project title, and requirements) to automatically generate database lead records.</p>

                <div class="dash-doc-code-block" style="margin-bottom: 24px;">
                    <pre><code>I want to submit an inquiry for our new commercial project. My name is Alex Turner, email is alex.turner@example.com, phone is 0412345678, project name is 'Riverside Residences', and we need custom strip lighting for 50 balconies.</code></pre>
                    <button type="button" class="dash-doc-copy-btn" data-copy-text="I want to submit an inquiry for our new commercial project. My name is Alex Turner, email is alex.turner@example.com, phone is 0412345678, project name is 'Riverside Residences', and we need custom strip lighting for 50 balconies.">Copy Prompt</button>
                </div>

                <p style="font-size: 13px; color: var(--dash-muted);"><strong>Expected Tool Call:</strong> <code>public_submit_lead_enquiry({"name": "Alex Turner", "email": "alex.turner@example.com", "phone": "0412345678", "project_name": "Riverside Residences", "message": "..."})</code>. Automatically creates an active enquiry in the CRM.</p>

                <hr style="border: 0; border-top: 1px solid var(--dash-border); margin: 28px 0;">

                <h3>7. Tool 6: Sales Transcript Handoff (<code>public_send_chat_transcript_to_sales</code>)</h3>
                <p>Tests compiling the entire visitor chat history and dispatching email notifications to sales engineers.</p>

                <div class="dash-doc-code-block" style="margin-bottom: 24px;">
                    <pre><code>Can you please email a copy of this entire conversation to sales@azoogi.com? My name is Alex.</code></pre>
                    <button type="button" class="dash-doc-copy-btn" data-copy-text="Can you please email a copy of this entire conversation to sales@azoogi.com? My name is Alex.">Copy Prompt</button>
                </div>

                <p style="font-size: 13px; color: var(--dash-muted);"><strong>Expected Tool Call:</strong> <code>public_send_chat_transcript_to_sales({"recipient_email": "sales@azoogi.com", "customer_name": "Alex"})</code>.</p>

                <hr style="border: 0; border-top: 1px solid var(--dash-border); margin: 28px 0;">

                <h3>8. Multi-Step Multi-Turn Workflow (Stress Benchmark)</h3>
                <p>Execute this 4-step sequence in one continuous chat session to test conversational memory retention, token accumulation across turns, and combined tool orchestration:</p>

                <div class="dash-doc-steps-list">
                    <div class="dash-doc-step">
                        <div class="dash-doc-step-num">1</div>
                        <div class="dash-doc-step-content">
                            <h4>Step 1: Product Discovery</h4>
                            <div class="dash-doc-code-block" style="margin-top: 6px;">
                                <pre><code>Find me 3 garden lights suitable for a villa driveway.</code></pre>
                                <button type="button" class="dash-doc-copy-btn" data-copy-text="Find me 3 garden lights suitable for a villa driveway.">Copy Step 1</button>
                            </div>
                        </div>
                    </div>
                    <div class="dash-doc-step">
                        <div class="dash-doc-step-num">2</div>
                        <div class="dash-doc-step-content">
                            <h4>Step 2: Technical Comparison</h4>
                            <div class="dash-doc-code-block" style="margin-top: 6px;">
                                <pre><code>Which one has higher wattage between the 7W and 12W models?</code></pre>
                                <button type="button" class="dash-doc-copy-btn" data-copy-text="Which one has higher wattage between the 7W and 12W models?">Copy Step 2</button>
                            </div>
                        </div>
                    </div>
                    <div class="dash-doc-step">
                        <div class="dash-doc-step-num">3</div>
                        <div class="dash-doc-step-content">
                            <h4>Step 3: Cart Modification</h4>
                            <div class="dash-doc-code-block" style="margin-top: 6px;">
                                <pre><code>Add 8 units of the 12W model to my quote.</code></pre>
                                <button type="button" class="dash-doc-copy-btn" data-copy-text="Add 8 units of the 12W model to my quote.">Copy Step 3</button>
                            </div>
                        </div>
                    </div>
                    <div class="dash-doc-step">
                        <div class="dash-doc-step-num">4</div>
                        <div class="dash-doc-step-content">
                            <h4>Step 4: Lead Submission</h4>
                            <div class="dash-doc-code-block" style="margin-top: 6px;">
                                <pre><code>My name is Sarah (sarah@example.com, 0498765432) for the 'Villa Royale' project. Please submit my quote.</code></pre>
                                <button type="button" class="dash-doc-copy-btn" data-copy-text="My name is Sarah (sarah@example.com, 0498765432) for the 'Villa Royale' project. Please submit my quote.">Copy Step 4</button>
                            </div>
                        </div>
                    </div>
                </div>

                <hr style="border: 0; border-top: 1px solid var(--dash-border); margin: 28px 0;">

                <h3>9. Model Comparison & Benchmark Reference</h3>
                <div class="dash-table-wrap">
                    <table class="dash-doc-table">
                        <thead>
                            <tr>
                                <th>AI Model</th>
                                <th>Provider / Route</th>
                                <th>Tool Calling Support</th>
                                <th>Avg Latency</th>
                                <th>Cost Profile (per 1M tokens)</th>
                                <th>Recommended Role</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td><strong>claude-opus-5-5</strong></td>
                                <td>Anthropic Direct</td>
                                <td><span class="dash-pill-active">Native Tools</span></td>
                                <td>~1.2s – 2.0s</td>
                                <td>$15.00 In / $75.00 Out</td>
                                <td>Highest reasoning quality & complex specification drafting.</td>
                            </tr>
                            <tr>
                                <td><strong>gemini-2.5-flash</strong></td>
                                <td>Google Gemini</td>
                                <td><span class="dash-pill-active">Native Tools</span></td>
                                <td>~0.4s – 0.8s</td>
                                <td>$0.15 In / $0.60 Out</td>
                                <td><strong>Recommended Default</strong>: Ultra-fast, highly accurate tool calls, lowest spend.</td>
                            </tr>
                            <tr>
                                <td><strong>gemini-2.5-pro</strong></td>
                                <td>Google Gemini</td>
                                <td><span class="dash-pill-active">Native Tools</span></td>
                                <td>~1.0s – 1.8s</td>
                                <td>$1.25 In / $5.00 Out</td>
                                <td>Advanced architectural lighting engineering.</td>
                            </tr>
                            <tr>
                                <td><strong>deepseek/deepseek-chat</strong></td>
                                <td>OpenRouter Gateway</td>
                                <td><span class="dash-pill-active">Native Tools</span></td>
                                <td>~0.6s – 1.2s</td>
                                <td>$0.14 In / $0.28 Out</td>
                                <td>Maximum economy with DeepSeek V3 open-weights flagship.</td>
                            </tr>
                            <tr>
                                <td><strong>openai/gpt-4o-mini</strong></td>
                                <td>OpenRouter Gateway</td>
                                <td><span class="dash-pill-active">Native Tools</span></td>
                                <td>~0.5s – 1.0s</td>
                                <td>$0.15 In / $0.60 Out</td>
                                <td>Reliable OpenAI tool execution at budget rates.</td>
                            </tr>
                            <tr>
                                <td><strong>qwen2.5-coder:3b</strong></td>
                                <td>Prisha AI / OpenWebUI</td>
                                <td><span class="dash-pill-active">Text / Prompt Injection</span></td>
                                <td>~0.8s – 2.2s</td>
                                <td>$0.00 (Self-hosted GPU)</td>
                                <td>Private, self-hosted on-premise execution.</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </article>
        @endif

        {{-- 17. MODEL CONTEXT PROTOCOL (MCP) & AI ASSISTANT --}}
        @if ($activeTopic === 'mcp')
            <article class="dash-card dash-doc-section" data-doc-block>
                <div class="dash-doc-header">
                    <h2>Model Context Protocol (MCP) & AI Integration</h2>
                    <span class="dash-pill-active">AI Engine</span>
                </div>
                <p>Azoogi features a <strong>native PHP implementation of the Model Context Protocol (MCP)</strong> conforming to the <code>2024-11-05</code> specification. MCP allows external Large Language Models (such as Anthropic Claude Desktop, Cursor AI, or IDE assistants) to securely inspect front-end views, read route definitions, explore assets, and execute validated database updates using natural language prompts.</p>

                <div class="dash-doc-callout info">
                    <strong>Zero Node.js Overhead:</strong> The MCP server runs 100% natively in PHP through the standard input/output stream (<code>stdio</code>) via an Artisan console command without external runtime dependencies.
                </div>

                <h3>1. Quick Start & Artisan Server</h3>
                <p>The MCP server runs as a background process or interactive stdio listener via Artisan:</p>
                <div class="dash-doc-code-block" style="margin-bottom: 16px;">
                    <pre><code># Start the MCP server exposing all tool suites (Frontend, Backend, and Public Chat)
php artisan mcp:serve --mode=all

# Expose only read-only frontend exploration tools
php artisan mcp:serve --mode=frontend

# Expose only backend mutations and CMS updating tools
php artisan mcp:serve --mode=backend

# Expose catalog search and quote builder tools
php artisan mcp:serve --mode=public_chat</code></pre>
                    <button type="button" class="dash-doc-copy-btn" data-copy-text="php artisan mcp:serve --mode=all">Copy Command</button>
                </div>

                <h3>2. Connecting MCP to Claude Desktop</h3>
                <p>To connect Claude Desktop to your local Azoogi backend, open your Claude configuration file:</p>
                <ul>
                    <li><strong>macOS:</strong> <code>~/Library/Application Support/Claude/claude_desktop_config.json</code></li>
                    <li><strong>Windows:</strong> <code>%APPDATA%\Claude\claude_desktop_config.json</code></li>
                </ul>
                <div class="dash-doc-code-block" style="margin-bottom: 16px;">
                    <pre><code>{
  "mcpServers": {
    "azoogi-backend": {
      "command": "php",
      "args": [
        "{{ base_path('artisan') }}",
        "mcp:serve",
        "--mode=all"
      ]
    }
  }
}</code></pre>
                    <button type="button" class="dash-doc-copy-btn" data-copy-text='{
  "mcpServers": {
    "azoogi-backend": {
      "command": "php",
      "args": [
        "{{ str_replace('\\', '/', base_path('artisan')) }}",
        "mcp:serve",
        "--mode=all"
      ]
    }
  }
}'>Copy JSON Config</button>
                </div>

                <h3>3. Connecting MCP to Cursor IDE</h3>
                <p>To connect Cursor to the Azoogi MCP server, create or edit <code>.cursor/mcp.json</code> in your project root:</p>
                <div class="dash-doc-code-block" style="margin-bottom: 16px;">
                    <pre><code>{
  "mcpServers": {
    "azoogi-app": {
      "command": "php",
      "args": ["artisan", "mcp:serve", "--mode=all"]
    }
  }
}</code></pre>
                    <button type="button" class="dash-doc-copy-btn" data-copy-text='{
  "mcpServers": {
    "azoogi-app": {
      "command": "php",
      "args": ["artisan", "mcp:serve", "--mode=all"]
    }
  }
}'>Copy JSON Config</button>
                </div>

                <h3>4. Example Natural Language Use Cases</h3>
                <div class="dash-doc-steps">
                    <div class="dash-doc-step">
                        <div class="dash-doc-step-num">A</div>
                        <div class="dash-doc-step-content">
                            <h4>Update Solution Page Hero Banner Copy</h4>
                            <p>Prompt in Claude or Cursor:</p>
                            <p><em>&ldquo;Update the solution page hero banner title to 'Innovative Commercial Lighting Solutions' and the lead text to 'High-efficiency luminaires and smart controls built for modern architecture'.&rdquo;</em></p>
                            <p><strong>Action Taken:</strong> The AI invokes <code>backend_update_page_content</code>, matches the <code>solutions</code> slug, and writes to MySQL <code>page_meta</code> inside an atomic database transaction.</p>
                        </div>
                    </div>
                    <div class="dash-doc-step">
                        <div class="dash-doc-step-num">B</div>
                        <div class="dash-doc-step-content">
                            <h4>Visitor Product Search & Quote Creation</h4>
                            <p>Prompt on website chat or AI assistant:</p>
                            <p><em>&ldquo;Search for our outdoor garden spike lights, add 6 units of the 50W model to my quote, and submit a quote request for info@archstudio.com.au.&rdquo;</em></p>
                            <p><strong>Action Taken:</strong> The AI queries <code>public_search_products</code>, updates session cart via <code>public_manage_quote_list</code>, and registers the lead via <code>public_submit_quote_enquiry</code>.</p>
                        </div>
                    </div>
                </div>

                <h3>5. Available MCP Tool Reference</h3>
                <div class="dash-doc-table-wrap">
                    <table class="dash-doc-table">
                        <thead>
                            <tr>
                                <th>Tool Identifier</th>
                                <th>Category</th>
                                <th>Description</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td><code>frontend_list_blade_views</code></td>
                                <td><span class="dash-pill-active" style="font-size: 11px;">Frontend</span></td>
                                <td>Recursively scans <code>resources/views</code> for templates, layouts, and components.</td>
                            </tr>
                            <tr>
                                <td><code>frontend_read_blade_view</code></td>
                                <td><span class="dash-pill-active" style="font-size: 11px;">Frontend</span></td>
                                <td>Safely reads Blade file contents with directory traversal protection.</td>
                            </tr>
                            <tr>
                                <td><code>frontend_list_routes</code></td>
                                <td><span class="dash-pill-active" style="font-size: 11px;">Frontend</span></td>
                                <td>Lists registered application routes with HTTP methods, URIs, and middleware.</td>
                            </tr>
                            <tr>
                                <td><code>frontend_search_assets</code></td>
                                <td><span class="dash-pill-active" style="font-size: 11px;">Frontend</span></td>
                                <td>Grep search across Blade views, CSS stylesheets, and JavaScript files.</td>
                            </tr>
                            <tr>
                                <td><code>backend_update_page_content</code></td>
                                <td><span class="dash-pill-active" style="font-size: 11px; background: rgba(58, 160, 40, 0.2); color: #67d04e;">Backend</span></td>
                                <td>Updates page metadata, hero titles, subtitles, banner texts, or custom section values.</td>
                            </tr>
                            <tr>
                                <td><code>backend_inspect_schema</code></td>
                                <td><span class="dash-pill-active" style="font-size: 11px; background: rgba(58, 160, 40, 0.2); color: #67d04e;">Backend</span></td>
                                <td>Inspects MySQL database table columns, types, and primary keys for models.</td>
                            </tr>
                            <tr>
                                <td><code>backend_query_records</code></td>
                                <td><span class="dash-pill-active" style="font-size: 11px; background: rgba(58, 160, 40, 0.2); color: #67d04e;">Backend</span></td>
                                <td>Queries records from allowlisted Eloquent models with safe filtering and pagination.</td>
                            </tr>
                            <tr>
                                <td><code>backend_mutate_model</code></td>
                                <td><span class="dash-pill-active" style="font-size: 11px; background: rgba(58, 160, 40, 0.2); color: #67d04e;">Backend</span></td>
                                <td>Safely executes validated create, update, or delete operations on allowlisted models.</td>
                            </tr>
                            <tr>
                                <td><code>public_search_products</code></td>
                                <td><span class="dash-pill-active" style="font-size: 11px; background: rgba(56, 189, 248, 0.2); color: #38bdf8;">Chat / Quotes</span></td>
                                <td>Searches active product catalog by application, category, or keyword.</td>
                            </tr>
                            <tr>
                                <td><code>public_manage_quote_list</code></td>
                                <td><span class="dash-pill-active" style="font-size: 11px; background: rgba(56, 189, 248, 0.2); color: #38bdf8;">Chat / Quotes</span></td>
                                <td>Adds, removes, or inspects items in visitor quote cart.</td>
                            </tr>
                            <tr>
                                <td><code>public_submit_quote_enquiry</code></td>
                                <td><span class="dash-pill-active" style="font-size: 11px; background: rgba(56, 189, 248, 0.2); color: #38bdf8;">Chat / Quotes</span></td>
                                <td>Creates quote enquiry records in MySQL with customer details and selected items.</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <h3>6. Security Controls & Guardrails</h3>
                <ul>
                    <li><strong>Strict Model Allowlisting:</strong> Only models explicitly registered in <code>MutationGuard</code> (Page, PageMeta, Product, Category, Project, Enquiry) can be manipulated. Sensitive models like <code>User</code> are strictly locked down.</li>
                    <li><strong>Forbidden Column Protection:</strong> Columns such as <code>id</code>, <code>password</code>, <code>is_admin</code>, <code>remember_token</code>, and <code>api_token</code> cannot be modified through MCP mutations.</li>
                    <li><strong>Atomic DB Transactions:</strong> All mutations are executed within <code>DB::transaction()</code> blocks to guarantee that failures or invalid attributes immediately rollback changes.</li>
                    <li><strong>Dry-Run Previews:</strong> Destructive or complex updates support <code>"dry_run": true</code>, returning an exact before-and-after attribute diff before committing changes to MySQL.</li>
                </ul>
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
