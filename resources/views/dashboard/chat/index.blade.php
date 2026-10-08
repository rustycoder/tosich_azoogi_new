@extends('layouts.dashboard')

@section('title', 'AI Chat Logs')

@section('content')
<div class="dash-head">
    <div class="dash-crumb">
        <a href="{{ route('dashboard.home') }}">Dashboard</a>
        <span>/</span>
        <span>AI</span>
        <span>/</span>
        <span>Chat Logs</span>
    </div>
    <div class="dash-head-title">
        <h1>AI Chat Logs</h1>
        <div class="dash-head-actions">
            <span class="dash-pill is-active">{{ $sessions->total() }} Conversations</span>
            <a href="{{ route('dashboard.ai.config') }}" class="btn" style="display: inline-flex; align-items: center; gap: 6px;">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width: 15px; height: 15px;"><circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1 0 2.83 2 2 0 0 1-2.83 0l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-2 2 2 2 0 0 1-2-2v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83 0 2 2 0 0 1 0-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1-2-2 2 2 0 0 1 2-2h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 0-2.83 2 2 0 0 1 2.83 0l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 2-2 2 2 0 0 1 2 2v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 0 2 2 0 0 1 0 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 2 2 2 2 0 0 1-2 2h-.09a1.65 1.65 0 0 0-1.51 1z"/></svg>
                AI Model Settings
            </a>
        </div>
    </div>
    <p class="dash-lead">Inspect real-time visitor inquiries, questions asked, AI recommendations, token usage, and live computed LLM costs per chat session.</p>
</div>

<!-- Metrics Overview -->
<div class="dash-grid" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 16px; margin-bottom: 24px;">
    <div class="dash-card" style="padding: 18px;">
        <div style="font-size: 11px; text-transform: uppercase; color: var(--dash-muted, var(--muted)); font-weight: 600; letter-spacing: 0.05em;">Total Conversations</div>
        <div style="font-size: 26px; font-weight: 700; color: var(--dash-ink, var(--ink)); margin-top: 6px;">{{ number_format($metrics['total_conversations']) }}</div>
        <div style="font-size: 11px; color: var(--dash-muted, var(--muted)); margin-top: 4px;">
            <span style="color: #38bdf8; font-weight: 600;">{{ number_format($metrics['unread_count']) }} unread</span> · 
            <span style="color: #f59e0b; font-weight: 600;">{{ number_format($metrics['favorites_count']) }} starred</span> · 
            {{ number_format($metrics['active_today']) }} active today
        </div>
    </div>
    <div class="dash-card" style="padding: 18px;">
        <div style="font-size: 11px; text-transform: uppercase; color: var(--dash-muted, var(--muted)); font-weight: 600; letter-spacing: 0.05em;">Total Tokens Consumed</div>
        <div style="font-size: 26px; font-weight: 700; color: var(--accent); margin-top: 6px;">{{ number_format($metrics['total_tokens']) }}</div>
        <div style="font-size: 11px; color: var(--dash-muted, var(--muted)); margin-top: 4px;">~{{ number_format($metrics['avg_tokens']) }} / conversation</div>
    </div>
    <div class="dash-card" style="padding: 18px;">
        <div style="font-size: 11px; text-transform: uppercase; color: #38bdf8; font-weight: 600; letter-spacing: 0.05em;">Prompt Tokens (Input)</div>
        <div style="font-size: 26px; font-weight: 700; color: #38bdf8; margin-top: 6px;">{{ number_format($metrics['total_prompt_tokens']) }}</div>
        <div style="font-size: 11px; color: var(--dash-muted, var(--muted)); margin-top: 4px;">Avg: {{ number_format($metrics['avg_prompt_tokens']) }} / conversation</div>
    </div>
    <div class="dash-card" style="padding: 18px;">
        <div style="font-size: 11px; text-transform: uppercase; color: #a78bfa; font-weight: 600; letter-spacing: 0.05em;">Completion Tokens (Output)</div>
        <div style="font-size: 26px; font-weight: 700; color: #a78bfa; margin-top: 6px;">{{ number_format($metrics['total_completion_tokens']) }}</div>
        <div style="font-size: 11px; color: var(--dash-muted, var(--muted)); margin-top: 4px;">Avg: {{ number_format($metrics['avg_completion_tokens']) }} / conversation</div>
    </div>
    <div class="dash-card" style="padding: 18px;">
        <div style="font-size: 11px; text-transform: uppercase; color: #10b981; font-weight: 600; letter-spacing: 0.05em;">Total Estimated Spend</div>
        <div style="font-size: 26px; font-weight: 700; color: #10b981; margin-top: 6px;">${{ number_format($metrics['total_cost'], 4) }} <span style="font-size: 13px; font-weight: normal; color: var(--dash-muted, var(--muted));">USD</span></div>
        <div style="font-size: 11px; color: var(--dash-muted, var(--muted)); margin-top: 4px;">~${{ number_format($metrics['avg_cost'], 4) }} / conversation</div>
    </div>
    <div class="dash-card" style="padding: 18px;">
        <div style="font-size: 11px; text-transform: uppercase; color: var(--dash-muted, var(--muted)); font-weight: 600; letter-spacing: 0.05em;">Leads Captured</div>
        <div style="font-size: 26px; font-weight: 700; color: #10b981; margin-top: 6px;">{{ number_format($metrics['leads_captured']) }}</div>
        <div style="font-size: 11px; color: var(--dash-muted, var(--muted)); margin-top: 4px;">Quote requests &amp; inquiries</div>
    </div>
</div>

<!-- Search & Filter Controls Toolbar -->
<form class="dash-toolbar-row" method="get" action="{{ route('dashboard.chat-sessions.index') }}" role="search" id="chatFilterForm">
    <!-- Search Input Field -->
    <div class="dash-search">
        <label class="visually-hidden" for="dash-search-q">Search chat sessions</label>
        <div class="dash-search-field">
            <svg class="dash-search-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                <circle cx="11" cy="11" r="6.5"/>
                <path d="M16.5 16.5 21 21"/>
            </svg>
            <input
                id="dash-search-q"
                type="search"
                name="q"
                value="{{ $searchQuery }}"
                placeholder="Search by lead name, email, IP, country, or keyword..."
                maxlength="100"
                autocomplete="off"
            >
            @if ($searchQuery !== '')
                <a class="dash-search-clear" href="{{ route('dashboard.chat-sessions.index', array_filter(['status' => $currentStatus, 'read_status' => $currentReadStatus, 'favorite_only' => $favoriteOnly ? 1 : null, 'sort' => $currentSort !== 'latest' ? $currentSort : null, 'has_lead' => $hasLead ? 1 : null, 'start_date' => $startDate, 'end_date' => $endDate])) }}" title="Clear search" aria-label="Clear search">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="M6 6l12 12M18 6 6 18"/></svg>
                </a>
            @endif
            <button type="submit" class="dash-search-submit">Search</button>
        </div>
    </div>

    <!-- Date Range Filter -->
    <div class="dash-date-range-wrap" style="display: inline-flex; align-items: center; gap: 6px; background: var(--dash-card); border: 1px solid var(--dash-line); border-radius: 8px; padding: 0 10px; min-height: 40px;">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width: 14px; height: 14px; color: var(--dash-muted, var(--muted)); flex-shrink: 0;" aria-hidden="true"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
        <span style="font-size: 11px; text-transform: uppercase; letter-spacing: 0.04em; color: var(--dash-muted, var(--muted)); font-weight: 600;">Date:</span>
        <input
            type="date"
            name="start_date"
            value="{{ $startDate ?? '' }}"
            class="dash-date-input"
            style="background: transparent; border: none; color: var(--dash-ink, var(--ink)); font-size: 12.5px; font-family: inherit; outline: none; padding: 4px 2px; cursor: pointer;"
            onchange="document.getElementById('chatFilterForm').submit()"
            title="Start Date (From)"
            aria-label="Start Date"
        >
        <span style="color: var(--dash-muted, var(--muted)); font-size: 12px;">→</span>
        <input
            type="date"
            name="end_date"
            value="{{ $endDate ?? '' }}"
            class="dash-date-input"
            style="background: transparent; border: none; color: var(--dash-ink, var(--ink)); font-size: 12.5px; font-family: inherit; outline: none; padding: 4px 2px; cursor: pointer;"
            onchange="document.getElementById('chatFilterForm').submit()"
            title="End Date (To)"
            aria-label="End Date"
        >
    </div>

    <!-- Read Status Filter Dropdown -->
    <div class="dash-select-wrap">
        <select name="read_status" class="dash-select" onchange="document.getElementById('chatFilterForm').submit()" aria-label="Filter by read status">
            <option value="">All Read / Unread</option>
            <option value="unread" {{ ($currentReadStatus ?? '') === 'unread' ? 'selected' : '' }}>🔵 Unread ({{ $metrics['unread_count'] ?? 0 }})</option>
            <option value="read" {{ ($currentReadStatus ?? '') === 'read' ? 'selected' : '' }}>✓ Read</option>
        </select>
        <svg class="dash-select-chevron" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><polyline points="6 9 12 15 18 9"/></svg>
    </div>

    <!-- Status Filter Dropdown -->
    <div class="dash-select-wrap">
        <select name="status" class="dash-select" onchange="document.getElementById('chatFilterForm').submit()" aria-label="Filter by status">
            <option value="">All Statuses</option>
            <option value="active" {{ $currentStatus === 'active' ? 'selected' : '' }}>Active</option>
            <option value="completed" {{ $currentStatus === 'completed' ? 'selected' : '' }}>Completed / Lead</option>
            <option value="abandoned" {{ $currentStatus === 'abandoned' ? 'selected' : '' }}>Abandoned</option>
        </select>
        <svg class="dash-select-chevron" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><polyline points="6 9 12 15 18 9"/></svg>
    </div>

    <!-- Sort Dropdown -->
    <div class="dash-select-wrap">
        <select name="sort" class="dash-select" onchange="document.getElementById('chatFilterForm').submit()" aria-label="Sort chat conversations">
            <option value="latest" {{ ($currentSort ?? '') === 'latest' ? 'selected' : '' }}>Sort: Latest Activity</option>
            <option value="highest_cost" {{ ($currentSort ?? '') === 'highest_cost' ? 'selected' : '' }}>Sort: Highest Cost ($)</option>
            <option value="highest_tokens" {{ ($currentSort ?? '') === 'highest_tokens' ? 'selected' : '' }}>Sort: Most Tokens</option>
            <option value="most_messages" {{ ($currentSort ?? '') === 'most_messages' ? 'selected' : '' }}>Sort: Most Messages</option>
        </select>
        <svg class="dash-select-chevron" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><polyline points="6 9 12 15 18 9"/></svg>
    </div>

    <!-- Favorites Only Checkbox Filter -->
    <label style="display: inline-flex; align-items: center; gap: 6px; font-size: 13px; color: var(--dash-ink, var(--ink)); cursor: pointer; padding: 0 4px; user-select: none;">
        <input type="checkbox" name="favorite_only" value="1" {{ !empty($favoriteOnly) ? 'checked' : '' }} onchange="document.getElementById('chatFilterForm').submit()" style="accent-color: #f59e0b; width: 16px; height: 16px;">
        <span style="display: inline-flex; align-items: center; gap: 4px;">
            <svg viewBox="0 0 24 24" fill="{{ !empty($favoriteOnly) ? '#f59e0b' : 'none' }}" stroke="#f59e0b" stroke-width="1.8" style="width: 14px; height: 14px;"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>
            Favorites Only
        </span>
    </label>

    <!-- Has Lead Checkbox Filter -->
    <label style="display: inline-flex; align-items: center; gap: 8px; font-size: 13px; color: var(--dash-ink, var(--ink)); cursor: pointer; padding: 0 4px; user-select: none;">
        <input type="checkbox" name="has_lead" value="1" {{ $hasLead ? 'checked' : '' }} onchange="document.getElementById('chatFilterForm').submit()" style="accent-color: var(--accent); width: 16px; height: 16px;">
        <span>Captured Leads Only</span>
    </label>

    @if ($searchQuery || $currentStatus || $currentReadStatus || $favoriteOnly || $hasLead || ($currentSort && $currentSort !== 'latest') || $startDate || $endDate)
        <a href="{{ route('dashboard.chat-sessions.index') }}" class="btn" style="padding: 7px 12px; font-size: 12.5px; text-decoration: none;">Reset Filters</a>
    @endif
</form>

<!-- Airtable-Style Chat Sessions Table -->
<div class="dash-airtable-wrap">
    <table class="dash-airtable-table">
        <thead>
            <!-- Group Header Tier 1 -->
            <tr class="dash-group-header-row">
                <th scope="colgroup" colspan="7" class="dash-group-th is-primary" style="text-align: center;">
                    <span class="dash-group-badge is-primary">1. Session &amp; Visitor</span>
                </th>
                <th scope="colgroup" colspan="2" class="dash-group-th is-specs" style="text-align: center;">
                    <span class="dash-group-badge is-specs">2. Conversation Context</span>
                </th>
                <th scope="colgroup" colspan="2" class="dash-group-th is-media" style="text-align: center;">
                    <span class="dash-group-badge is-media">3. AI Model &amp; Compute</span>
                </th>
                <th scope="colgroup" colspan="1" class="dash-group-th is-supplier" style="text-align: center;">
                    <span class="dash-group-badge is-supplier">4. Token Spend</span>
                </th>
                <th scope="colgroup" colspan="1" class="dash-group-th is-docs" style="text-align: center;">
                    <span class="dash-group-badge is-docs">5. Conversion</span>
                </th>
                <th scope="colgroup" colspan="2" class="dash-group-th is-audit" style="text-align: center;">
                    <span class="dash-group-badge is-audit">6. Audit &amp; Actions</span>
                </th>
            </tr>
            <!-- Column Header Tier 2 -->
            <tr>
                <!-- Group 1: Session & Visitor -->
                <th scope="col" class="dash-col-th is-primary" style="width: 44px; text-align: center;" title="Favorite">
                    <svg viewBox="0 0 24 24" fill="#f59e0b" stroke="#f59e0b" stroke-width="1.8" style="width: 13px; height: 13px; vertical-align: middle;"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>
                </th>
                <th scope="col" class="dash-col-th is-primary" style="min-width: 170px;">Visitor / Lead</th>
                <th scope="col" class="dash-col-th is-primary" style="min-width: 150px;">Project Name</th>
                <th scope="col" class="dash-col-th is-primary" style="min-width: 170px;">Email</th>
                <th scope="col" class="dash-col-th is-primary" style="min-width: 110px;">Location</th>
                <th scope="col" class="dash-col-th is-primary" style="min-width: 120px;">IP Address</th>
                <th scope="col" class="dash-col-th is-primary" style="width: 100px; text-align: center;">Session ID</th>

                <!-- Group 2: Conversation Context -->
                <th scope="col" class="dash-col-th is-specs" style="min-width: 240px;">Conversation Summary</th>
                <th scope="col" class="dash-col-th is-specs" style="width: 90px; text-align: center;">Messages</th>

                <!-- Group 3: AI Model & Compute -->
                <th scope="col" class="dash-col-th is-media" style="min-width: 160px;">AI Model &amp; Provider</th>
                <th scope="col" class="dash-col-th is-media" style="min-width: 160px;">Token Usage (Total / In / Out)</th>

                <!-- Group 4: Token Spend -->
                <th scope="col" class="dash-col-th is-supplier" style="min-width: 120px;">Estimated Cost</th>

                <!-- Group 5: Conversion -->
                <th scope="col" class="dash-col-th is-docs" style="width: 105px; text-align: center;">Status</th>

                <!-- Group 6: Audit & Actions -->
                <th scope="col" class="dash-col-th is-audit" style="min-width: 130px;">Created Date</th>
                <th scope="col" class="dash-col-th is-audit" style="width: 110px; text-align: center;">Actions</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($sessions as $session)
                @php
                    $promptTokens = (int) ($session->total_prompt_tokens ?? 0);
                    $completionTokens = (int) ($session->total_completion_tokens ?? 0);
                    $tokens = (int) ($session->total_tokens ?: ($promptTokens + $completionTokens));
                    if ($promptTokens === 0 && $completionTokens === 0 && $tokens > 0) {
                        $completionTokens = max(1, (int) round($tokens * 0.15));
                        $promptTokens = max(1, $tokens - $completionTokens);
                    }
                    $cost = (float) ($session->total_cost ?: $session->messages->sum('estimated_cost'));
                    $isLead = filled($session->lead_name) || filled($session->lead_email) || filled($session->enquiry_id);
                @endphp
                <tr id="session-row-{{ $session->id }}" style="{{ ! $session->is_read ? 'background: rgba(56, 189, 248, 0.02);' : '' }}">
                    <!-- Favorite Column -->
                    <td style="text-align: center; width: 44px; padding: 6px 4px;">
                        <button
                            type="button"
                            class="btn"
                            id="btn-fav-{{ $session->id }}"
                            onclick="toggleChatFavorite({{ $session->id }}, this)"
                            style="padding: 0; width: 28px; height: 28px; border-radius: 6px; display: inline-flex; align-items: center; justify-content: center; {{ $session->is_favorite ? 'color: #f59e0b; border-color: rgba(245,158,11,0.35); background: rgba(245,158,11,0.1);' : 'color: var(--dash-muted); border-color: transparent; background: transparent;' }}"
                            title="{{ $session->is_favorite ? 'Remove from favorites' : 'Mark as favorite' }}"
                            aria-label="{{ $session->is_favorite ? 'Remove from favorites' : 'Mark as favorite' }}"
                        >
                            <svg viewBox="0 0 24 24" fill="{{ $session->is_favorite ? '#f59e0b' : 'none' }}" stroke="currentColor" stroke-width="1.8" style="width: 15px; height: 15px;"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>
                        </button>
                    </td>
                    <!-- 1. Visitor / Lead Name -->
                    <td>
                        <div class="dash-product-cell">
                            @if ($session->lead_name)
                                <a href="{{ route('dashboard.chat-sessions.show', $session) }}" class="dash-product-title" style="color: #10b981; font-weight: 600;">
                                    {{ $session->lead_name }}
                                </a>
                                @if (filled($session->lead_phone))
                                    <span style="font-size: 10.5px; color: var(--dash-muted, var(--muted)); display: block;">
                                        {{ $session->lead_phone }}
                                    </span>
                                @endif
                            @else
                                <a href="{{ route('dashboard.chat-sessions.show', $session) }}" class="dash-product-title" style="color: var(--dash-ink, var(--ink)); font-weight: 600;">
                                    Anonymous Visitor
                                </a>
                                <span style="font-size: 11px; color: var(--dash-muted, var(--muted));">Website Inquiry</span>
                            @endif
                        </div>
                    </td>

                    <!-- Project Name -->
                    <td>
                        @if (filled($session->project_name))
                            <span class="dash-tag" style="font-size: 11px; width: fit-content; background: rgba(56, 189, 248, 0.12); color: #38bdf8; border: 1px solid rgba(56, 189, 248, 0.25);" title="Project: {{ $session->project_name }}">
                                📁 {{ \Illuminate\Support\Str::limit($session->project_name, 22) }}
                            </span>
                        @endif
                    </td>

                    <!-- Email -->
                    <td>
                        @if (filled($session->lead_email))
                            <a href="mailto:{{ $session->lead_email }}" style="color: var(--dash-ink, var(--ink)); font-size: 12px; font-family: inherit; font-weight: 500; text-decoration: none;" title="{{ $session->lead_email }}">
                                {{ $session->lead_email }}
                            </a>
                        @endif
                    </td>

                    <!-- Location -->
                    <td>
                        @if (filled($session->country))
                            <span class="dash-tag is-primary" style="font-size: 10.5px; width: fit-content;">
                                {{ $session->country }}
                            </span>
                        @endif
                    </td>

                    <!-- IP Address -->
                    <td>
                        @if (filled($session->ip_address))
                            <span class="dash-code-badge" style="font-size: 10.5px; font-family: monospace;">
                                {{ $session->ip_address }}
                            </span>
                        @endif
                    </td>

                    <!-- Session ID -->
                    <td style="text-align: center;">
                        <span class="dash-code-badge" style="font-size: 11px; font-family: monospace;" title="Session UUID: {{ $session->uuid }}">
                            {{ substr($session->uuid, 0, 8) }}
                        </span>
                    </td>

                    <!-- 2. Conversation Context -->
                    <!-- Summary / Topic -->
                    <td>
                        <div class="dash-text-snippet" title="{{ $session->summary ?: 'Initial greeting & inquiry' }}" style="max-width: 280px;">
                            <span style="color: var(--dash-ink, var(--ink)); font-weight: 500;">
                                {{ \Illuminate\Support\Str::limit($session->summary ?: 'Initial greeting & product inquiry', 85) }}
                            </span>
                        </div>
                    </td>

                    <!-- Messages Count -->
                    <td style="text-align: center;">
                        <span class="dash-tag is-specs-tag" style="font-weight: 600; font-size: 11px;">
                            {{ $session->messages_count }} {{ \Illuminate\Support\Str::plural('msg', $session->messages_count) }}
                        </span>
                    </td>

                    <!-- 3. AI Model & Compute -->
                    <!-- AI Model & Provider -->
                    <td>
                        @if ($session->primary_model)
                            <div style="display: flex; flex-direction: column; gap: 2px;">
                                <span class="dash-code-badge" style="font-size: 11px; font-weight: 600; max-width: 180px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;" title="{{ $session->primary_model }}">
                                    {{ $session->primary_model }}
                                </span>
                            </div>
                        @else
                            <span style="color: var(--dash-muted, var(--muted)); font-size: 11px;">Auto / Default</span>
                        @endif
                    </td>

                    <!-- Token Usage -->
                    <td>
                        <div style="display: flex; flex-direction: column; gap: 3px;">
                            <span class="dash-tag" style="font-family: monospace; font-size: 11.5px; font-weight: 600; color: var(--dash-ink, var(--ink)); width: fit-content;">
                                ⚡ {{ number_format($tokens) }} <span style="font-weight: normal; font-size: 10px; color: var(--dash-muted, var(--muted));">total</span>
                            </span>
                            <div style="display: flex; align-items: center; gap: 5px; font-size: 10.5px; font-family: monospace; margin-top: 1px;">
                                <span style="color: #38bdf8; font-weight: 600;" title="Prompt Tokens (Input)">In: {{ number_format($promptTokens) }}</span>
                                <span style="color: var(--dash-line, rgba(255,255,255,0.2));">/</span>
                                <span style="color: #a78bfa; font-weight: 600;" title="Completion Tokens (Output)">Out: {{ number_format($completionTokens) }}</span>
                            </div>
                        </div>
                    </td>

                    <!-- 4. Token Spend / Cost -->
                    <td>
                        <div style="display: flex; flex-direction: column; gap: 2px;">
                            <span style="font-size: 12.5px; font-weight: 700; color: #10b981; font-family: monospace;">
                                ${{ number_format($cost, 4) }} <span style="font-size: 10px; font-weight: normal; color: var(--dash-muted, var(--muted));">USD</span>
                            </span>
                        </div>
                    </td>

                    <!-- 5. Conversion & Status -->
                    <td style="text-align: center;">
                        @if ($session->status === 'completed' || $session->enquiry_id)
                            <span class="dash-pill is-active" style="font-size: 10.5px; background: rgba(16, 185, 129, 0.15); color: #10b981; border: 1px solid rgba(16, 185, 129, 0.25);">
                                Quote Lead
                            </span>
                        @elseif ($session->status === 'active')
                            <span class="dash-pill is-active" style="font-size: 10.5px;">
                                Active
                            </span>
                        @else
                            <span class="dash-pill is-inactive" style="font-size: 10.5px;">
                                {{ ucfirst($session->status) }}
                            </span>
                        @endif
                    </td>

                    <!-- 6. Audit & Actions -->
                    <!-- Created Date -->
                    <td>
                        <div class="dash-updated" style="gap: 2px;">
                            <span class="dash-updated-value" style="font-size: 11.5px;">
                                <strong>{{ $session->created_at->diffForHumans() }}</strong>
                                @if ($session->created_at)
                                    <span class="dash-updated-sep" aria-hidden="true">·</span>
                                    <time datetime="{{ $session->created_at->toIso8601String() }}">{{ $session->created_at->timezone(config('app.timezone'))->format('j M, g:ia') }}</time>
                                @endif
                            </span>
                        </div>
                    </td>

                    <!-- Action Icons (Mark Read/Unread, Transcribe Icon) -->
                    <td style="text-align: center;">
                        <div style="display: inline-flex; align-items: center; gap: 5px; justify-content: center;">
                            <!-- Mark Read / Unread Button -->
                            <button
                                type="button"
                                class="btn"
                                id="btn-read-{{ $session->id }}"
                                onclick="toggleChatRead({{ $session->id }}, this)"
                                style="padding: 0; width: 30px; height: 30px; display: inline-flex; align-items: center; justify-content: center; {{ ! $session->is_read ? 'color: #38bdf8; border-color: rgba(56,189,248,0.35); background: rgba(56,189,248,0.1);' : 'color: var(--dash-muted);' }}"
                                title="{{ $session->is_read ? 'Mark as unread' : 'Mark as read' }}"
                                aria-label="{{ $session->is_read ? 'Mark as unread' : 'Mark as read' }}"
                            >
                                @if (! $session->is_read)
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" style="width: 14px; height: 14px;"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/><circle cx="19" cy="5" r="3" fill="#38bdf8" stroke="none"/></svg>
                                @else
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" style="width: 14px; height: 14px;"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg>
                                @endif
                            </button>

                            <!-- Transcribe / Transcript Icon Button -->
                            <a
                                href="{{ route('dashboard.chat-sessions.show', $session) }}"
                                class="btn"
                                style="padding: 0; width: 30px; height: 30px; display: inline-flex; align-items: center; justify-content: center; text-decoration: none; color: var(--accent); border-color: rgba(103, 208, 78, 0.35); background: rgba(103, 208, 78, 0.08);"
                                title="View Conversation Transcript"
                                aria-label="View Conversation Transcript"
                            >
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" style="width: 14px; height: 14px;"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/><polyline points="10 9 9 9 8 9"/></svg>
                            </a>
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="15">
                        <div class="dash-card dash-empty">
                            {{ $searchQuery === '' ? 'No chat conversations recorded yet.' : 'No chat conversations match "' . $searchQuery . '".' }}
                        </div>
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

<div style="margin-top: 18px;">
    {{ $sessions->links('dashboard.partials.pagination') }}
</div>

@push('scripts')
<script>
async function toggleChatFavorite(sessionId, btn) {
    btn.disabled = true;
    try {
        const response = await fetch(`/dashboard/chat-sessions/${sessionId}/toggle-favorite`, {
            method: 'PATCH',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json',
                'Content-Type': 'application/json'
            }
        });
        const data = await response.json();
        if (data.success) {
            const svg = btn.querySelector('svg');
            if (data.is_favorite) {
                btn.style.color = '#f59e0b';
                btn.style.borderColor = 'rgba(245,158,11,0.35)';
                btn.style.background = 'rgba(245,158,11,0.1)';
                btn.title = 'Remove from favorites';
                btn.setAttribute('aria-label', 'Remove from favorites');
                if (svg) svg.setAttribute('fill', '#f59e0b');
            } else {
                btn.style.color = 'var(--dash-muted)';
                btn.style.borderColor = '';
                btn.style.background = 'transparent';
                btn.title = 'Mark as favorite';
                btn.setAttribute('aria-label', 'Mark as favorite');
                if (svg) svg.setAttribute('fill', 'none');
            }
        }
    } catch (e) {
        console.error('Error toggling favorite:', e);
    } finally {
        btn.disabled = false;
    }
}

async function toggleChatRead(sessionId, btn) {
    btn.disabled = true;
    try {
        const response = await fetch(`/dashboard/chat-sessions/${sessionId}/toggle-read`, {
            method: 'PATCH',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json',
                'Content-Type': 'application/json'
            }
        });
        const data = await response.json();
        if (data.success) {
            const row = document.getElementById(`session-row-${sessionId}`);
            const badge = document.getElementById(`unread-badge-${sessionId}`);
            if (data.is_read) {
                btn.style.color = 'var(--dash-muted)';
                btn.style.borderColor = '';
                btn.style.background = 'transparent';
                btn.title = 'Mark as unread';
                btn.setAttribute('aria-label', 'Mark as unread');
                btn.innerHTML = `<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" style="width: 14px; height: 14px;"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg>`;
                if (row) row.style.background = '';
                if (badge) badge.style.display = 'none';
            } else {
                btn.style.color = '#38bdf8';
                btn.style.borderColor = 'rgba(56,189,248,0.35)';
                btn.style.background = 'rgba(56,189,248,0.1)';
                btn.title = 'Mark as read';
                btn.setAttribute('aria-label', 'Mark as read');
                btn.innerHTML = `<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" style="width: 14px; height: 14px;"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/><circle cx="19" cy="5" r="3" fill="#38bdf8" stroke="none"/></svg>`;
                if (row) row.style.background = 'rgba(56, 189, 248, 0.02)';
                if (badge) badge.style.display = '';
            }
        }
    } catch (e) {
        console.error('Error toggling read status:', e);
    } finally {
        btn.disabled = false;
    }
}
</script>
@endpush
@endsection
