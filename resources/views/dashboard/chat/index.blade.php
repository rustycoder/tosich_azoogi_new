@extends('layouts.dashboard')

@section('title', 'AI Chat Conversations & Cost Tracking')

@section('content')
<div class="dash-head">
    <div class="dash-crumb">
        <a href="{{ route('dashboard.home') }}">Dashboard</a>
        <span>/</span>
        <span>AI</span>
        <span>/</span>
        <span>AI Chat Logs</span>
    </div>
    <div class="dash-head-title">
        <h1>AI Chat Conversations &amp; Cost Tracking</h1>
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
<div class="dash-grid" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 16px; margin-bottom: 24px;">
    <div class="dash-card" style="padding: 18px;">
        <div style="font-size: 11px; text-transform: uppercase; color: var(--dash-muted, var(--muted)); font-weight: 600; letter-spacing: 0.05em;">Total Conversations</div>
        <div style="font-size: 26px; font-weight: 700; color: var(--dash-ink, var(--ink)); margin-top: 6px;">{{ number_format($metrics['total_conversations']) }}</div>
        <div style="font-size: 11px; color: var(--dash-muted, var(--muted)); margin-top: 4px;">{{ number_format($metrics['active_today']) }} active today</div>
    </div>
    <div class="dash-card" style="padding: 18px;">
        <div style="font-size: 11px; text-transform: uppercase; color: var(--dash-muted, var(--muted)); font-weight: 600; letter-spacing: 0.05em;">Total Tokens Consumed</div>
        <div style="font-size: 26px; font-weight: 700; color: var(--accent); margin-top: 6px;">{{ number_format($metrics['total_tokens']) }}</div>
        <div style="font-size: 11px; color: var(--dash-muted, var(--muted)); margin-top: 4px;">Prompt &amp; completion tokens</div>
    </div>
    <div class="dash-card" style="padding: 18px;">
        <div style="font-size: 11px; text-transform: uppercase; color: var(--dash-muted, var(--muted)); font-weight: 600; letter-spacing: 0.05em;">Total Estimated Spend</div>
        <div style="font-size: 26px; font-weight: 700; color: #10b981; margin-top: 6px;">${{ number_format($metrics['total_cost'], 4) }} <span style="font-size: 13px; font-weight: normal; color: var(--dash-muted, var(--muted));">USD</span></div>
        <div style="font-size: 11px; color: var(--dash-muted, var(--muted)); margin-top: 4px;">~${{ number_format($metrics['avg_cost'], 4) }} / session</div>
    </div>
    <div class="dash-card" style="padding: 18px;">
        <div style="font-size: 11px; text-transform: uppercase; color: var(--dash-muted, var(--muted)); font-weight: 600; letter-spacing: 0.05em;">Leads Captured</div>
        <div style="font-size: 26px; font-weight: 700; color: #10b981; margin-top: 6px;">{{ number_format($metrics['leads_captured']) }}</div>
        <div style="font-size: 11px; color: var(--dash-muted, var(--muted)); margin-top: 4px;">{{ number_format($metrics['total_messages']) }} total messages</div>
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
                <a class="dash-search-clear" href="{{ route('dashboard.chat-sessions.index', array_filter(['status' => $currentStatus, 'sort' => $currentSort !== 'latest' ? $currentSort : null, 'has_lead' => $hasLead ? 1 : null])) }}" title="Clear search" aria-label="Clear search">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="M6 6l12 12M18 6 6 18"/></svg>
                </a>
            @endif
            <button type="submit" class="dash-search-submit">Search</button>
        </div>
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

    <!-- Has Lead Checkbox Filter -->
    <label style="display: inline-flex; align-items: center; gap: 8px; font-size: 13px; color: var(--dash-ink, var(--ink)); cursor: pointer; padding: 0 4px; user-select: none;">
        <input type="checkbox" name="has_lead" value="1" {{ $hasLead ? 'checked' : '' }} onchange="document.getElementById('chatFilterForm').submit()" style="accent-color: var(--accent); width: 16px; height: 16px;">
        <span>Captured Leads Only</span>
    </label>

    @if ($searchQuery || $currentStatus || $hasLead || ($currentSort && $currentSort !== 'latest'))
        <a href="{{ route('dashboard.chat-sessions.index') }}" class="btn" style="padding: 7px 12px; font-size: 12.5px; text-decoration: none;">Reset Filters</a>
    @endif
</form>

<!-- Airtable-Style Chat Sessions Table -->
<div class="dash-airtable-wrap">
    <table class="dash-airtable-table">
        <thead>
            <!-- Group Header Tier 1 -->
            <tr class="dash-group-header-row">
                <th scope="colgroup" colspan="3" class="dash-group-th is-primary" style="text-align: center;">
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
                <th scope="col" class="dash-col-th is-primary" style="min-width: 200px;">Visitor / Lead</th>
                <th scope="col" class="dash-col-th is-primary" style="min-width: 140px;">Location &amp; IP</th>
                <th scope="col" class="dash-col-th is-primary" style="width: 110px; text-align: center;">Session ID</th>

                <!-- Group 2: Conversation Context -->
                <th scope="col" class="dash-col-th is-specs" style="min-width: 250px;">Conversation Summary</th>
                <th scope="col" class="dash-col-th is-specs" style="width: 90px; text-align: center;">Messages</th>

                <!-- Group 3: AI Model & Compute -->
                <th scope="col" class="dash-col-th is-media" style="min-width: 170px;">AI Model &amp; Provider</th>
                <th scope="col" class="dash-col-th is-media" style="min-width: 150px;">Token Usage</th>

                <!-- Group 4: Token Spend -->
                <th scope="col" class="dash-col-th is-supplier" style="min-width: 130px;">Estimated Cost</th>

                <!-- Group 5: Conversion -->
                <th scope="col" class="dash-col-th is-docs" style="width: 110px; text-align: center;">Status</th>

                <!-- Group 6: Audit & Actions -->
                <th scope="col" class="dash-col-th is-audit" style="min-width: 140px;">Last Activity</th>
                <th scope="col" class="dash-col-th is-audit" style="width: 130px; text-align: center;">Action</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($sessions as $session)
                @php
                    $tokens = (int) ($session->total_tokens ?: $session->messages->sum('tokens_used'));
                    $cost = (float) ($session->total_cost ?: $session->messages->sum('estimated_cost'));
                    $isLead = filled($session->lead_name) || filled($session->lead_email) || filled($session->enquiry_id);
                @endphp
                <tr>
                    <!-- 1. Visitor / Lead -->
                    <td>
                        <div class="dash-product-cell">
                            @if ($session->lead_name || $session->lead_email || $session->project_name)
                                <a href="{{ route('dashboard.chat-sessions.show', $session) }}" class="dash-product-title" style="color: #10b981; font-weight: 600;">
                                    {{ $session->lead_name ?: ($session->project_name ?: 'Identified Lead') }}
                                </a>
                                @if (filled($session->lead_email))
                                    <span style="font-size: 11.5px; color: var(--dash-muted, var(--muted)); display: block; line-height: 1.3;">
                                        {{ $session->lead_email }}
                                    </span>
                                @endif
                                @if (filled($session->project_name))
                                    <span class="dash-tag" style="font-size: 10px; width: fit-content; margin-top: 3px; background: rgba(56, 189, 248, 0.12); color: #38bdf8; border: 1px solid rgba(56, 189, 248, 0.25);" title="Project: {{ $session->project_name }}">
                                        📁 {{ \Illuminate\Support\Str::limit($session->project_name, 26) }}
                                    </span>
                                @endif
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

                    <!-- Location & IP -->
                    <td>
                        <div style="display: flex; flex-direction: column; gap: 3px;">
                            @if (filled($session->country))
                                <span class="dash-tag is-primary" style="font-size: 10.5px; width: fit-content;">
                                    {{ $session->country }}
                                </span>
                            @else
                                <span class="dash-tag" style="font-size: 10.5px; width: fit-content; color: var(--dash-muted, var(--muted));">
                                    Australia
                                </span>
                            @endif
                            @if (filled($session->ip_address))
                                <span class="dash-code-badge" style="font-size: 10.5px; font-family: monospace;">
                                    {{ $session->ip_address }}
                                </span>
                            @endif
                        </div>
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
                        <div style="display: flex; flex-direction: column; gap: 2px;">
                            <span class="dash-tag" style="font-family: monospace; font-size: 11.5px; font-weight: 600; color: var(--dash-ink, var(--ink));">
                                ⚡ {{ number_format($tokens) }} <span style="font-weight: normal; font-size: 10px; color: var(--dash-muted, var(--muted));">tokens</span>
                            </span>
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
                    <!-- Last Activity -->
                    <td>
                        <div class="dash-updated" style="gap: 2px;">
                            <span class="dash-updated-value" style="font-size: 11.5px;">
                                <strong>{{ $session->updated_at->diffForHumans() }}</strong>
                                @if ($session->updated_at)
                                    <span class="dash-updated-sep" aria-hidden="true">·</span>
                                    <time datetime="{{ $session->updated_at->toIso8601String() }}">{{ $session->updated_at->timezone(config('app.timezone'))->format('j M, g:ia') }}</time>
                                @endif
                            </span>
                        </div>
                    </td>

                    <!-- Action Link -->
                    <td style="text-align: center;">
                        <a href="{{ route('dashboard.chat-sessions.show', $session) }}" class="btn" style="padding: 5px 11px; font-size: 12px; font-weight: 600; text-decoration: none; display: inline-flex; align-items: center; gap: 4px; white-space: nowrap;">
                            <span>Transcript</span>
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width: 12px; height: 12px;"><polyline points="9 18 15 12 9 6"/></svg>
                        </a>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="11">
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
@endsection
