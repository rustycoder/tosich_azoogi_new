@extends('layouts.dashboard')

@section('title', 'Chat Session Transcript & Cost Audit')

@section('content')
<div class="dash-head">
    <div class="dash-crumb">
        <a href="{{ route('dashboard.home') }}">Dashboard</a>
        <span>/</span>
        <a href="{{ route('dashboard.chat-sessions.index') }}">AI Chat Logs</a>
        <span>/</span>
        <span>Transcript #{{ substr($session->uuid, 0, 8) }}</span>
    </div>
    <div class="dash-head-title">
        <div style="display: flex; align-items: center; gap: 12px; flex-wrap: wrap;">
            <h1>Conversation Transcript &amp; Cost Audit</h1>
            @if ($session->status === 'completed' || $session->enquiry_id)
                <span class="dash-pill is-active" style="background: rgba(16, 185, 129, 0.15); color: #10b981; border: 1px solid rgba(16, 185, 129, 0.25);">
                    Quote Lead
                </span>
            @elseif ($session->status === 'active')
                <span class="dash-pill is-active">Active</span>
            @else
                <span class="dash-pill is-inactive">{{ ucfirst($session->status) }}</span>
            @endif
        </div>
        <div class="dash-head-actions">
            <a href="{{ route('dashboard.chat-sessions.index') }}" class="btn" style="text-decoration: none; display: inline-flex; align-items: center; gap: 6px;" title="Back to All Chats">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width: 14px; height: 14px;"><polyline points="15 18 9 12 15 6"/></svg>
                <span>Back</span>
            </a>
            <!-- Favorite Toggle Button -->
            <button type="button" class="btn" id="btnShowFavorite" onclick="toggleShowFavorite({{ $session->id }}, this)" style="display: inline-flex; align-items: center; gap: 6px; {{ $session->is_favorite ? 'color: #f59e0b; border-color: rgba(245,158,11,0.35); background: rgba(245,158,11,0.1);' : '' }}" title="Favorite Conversation">
                <svg id="showFavStar" viewBox="0 0 24 24" fill="{{ $session->is_favorite ? '#f59e0b' : 'none' }}" stroke="currentColor" stroke-width="2" style="width: 14px; height: 14px;"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>
                <span id="btnShowFavoriteText">{{ $session->is_favorite ? 'Favorited' : 'Favorite' }}</span>
            </button>
            <!-- Read/Unread Toggle Button -->
            <button type="button" class="btn" id="btnShowRead" onclick="toggleShowRead({{ $session->id }}, this)" style="display: inline-flex; align-items: center; gap: 6px; {{ $session->is_read ? '' : 'color: #38bdf8; border-color: rgba(56,189,248,0.35); background: rgba(56,189,248,0.1);' }}" title="Toggle Read/Unread">
                <svg id="showReadIcon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" style="width: 14px; height: 14px;"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg>
                <span id="btnShowReadText">{{ $session->is_read ? 'Unread' : 'Read' }}</span>
            </button>
            <button type="button" class="btn" id="btnCopyTranscript" onclick="copyFullTranscript()" style="display: inline-flex; align-items: center; gap: 6px;" title="Copy Full Transcript">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width: 14px; height: 14px;"><rect x="9" y="9" width="13" height="13" rx="2" ry="2"/><path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"/></svg>
                <span id="btnCopyTranscriptText">Copy</span>
            </button>
            @if (! $session->enquiry_id)
                <form method="POST" action="{{ route('dashboard.chat-sessions.convert-enquiry', $session) }}" style="display: inline;">
                    @csrf
                    <button type="submit" class="btn primary" style="display: inline-flex; align-items: center; gap: 6px;" onclick="return confirm('Convert this chat into an official Quote Enquiry?')" title="Convert to Official Enquiry">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width: 15px; height: 15px;"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><line x1="19" y1="8" x2="19" y2="14"/><line x1="22" y1="11" x2="16" y2="11"/></svg>
                        <span>Convert</span>
                    </button>
                </form>
            @endif
        </div>
    </div>
    <p class="dash-lead">Inspect turn-by-turn user queries, AI responses, executed product database tools, token usage breakdowns, and computed compute costs.</p>
</div>

@if (session('success'))
    <div class="dash-card" style="padding: 14px 18px; background: rgba(16, 185, 129, 0.1); border: 1px solid rgba(16, 185, 129, 0.25); color: #10b981; margin-bottom: 24px; display: flex; align-items: center; gap: 10px;">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width: 18px; height: 18px; flex-shrink: 0;"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
        <span>{{ session('success') }}</span>
    </div>
@endif

@if (session('info'))
    <div class="dash-card" style="padding: 14px 18px; background: rgba(56, 189, 248, 0.1); border: 1px solid rgba(56, 189, 248, 0.25); color: #38bdf8; margin-bottom: 24px; display: flex; align-items: center; gap: 10px;">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width: 18px; height: 18px; flex-shrink: 0;"><circle cx="12" cy="12" r="10"/><line x1="12" y1="16" x2="12" y2="12"/><line x1="12" y1="8" x2="12.01" y2="8"/></svg>
        <span>{{ session('info') }}</span>
    </div>
@endif

@php
    $sessionPromptTokens = $messages->sum('prompt_tokens');
    $sessionCompletionTokens = $messages->sum('completion_tokens');
    $sessionTotalTokens = $session->total_tokens ?: $messages->sum('tokens_used');
    $sessionTotalCost = $session->total_cost ?: (float) $messages->sum('estimated_cost');
    $primaryModelName = $session->primary_model ?: $messages->whereNotNull('model')->first()?->model ?: 'claude-3-5-sonnet';
    $messageCount = $messages->count();

    $rawTranscriptText = "--- AZOOGI AI CONVERSATION TRANSCRIPT ---\n";
    $rawTranscriptText .= "Session UUID: " . $session->uuid . "\n";
    $rawTranscriptText .= "Visitor: " . ($session->lead_name ?: 'Anonymous') . " (" . $session->ip_address . ", " . ($session->country ?: 'Australia') . ")\n";
    $rawTranscriptText .= "Total Tokens: " . number_format($sessionTotalTokens) . " | Total Cost: $" . number_format($sessionTotalCost, 6) . " USD\n\n";

    foreach ($messages as $m) {
        $senderLabel = $m->isUser() ? ($session->lead_name ?: 'Visitor') : 'Azoogi AI Assistant';
        $timeLabel = $m->created_at ? $m->created_at->timezone(config('app.timezone'))->format('Y-m-d H:i:s') : '';
        $rawTranscriptText .= "[{$timeLabel}] {$senderLabel}:\n{$m->content}\n\n";
    }
@endphp

<!-- Top Metadata & KPI Ribbon -->
<div class="dash-grid" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 16px; margin-bottom: 24px;">
    <!-- Card 1: Session Identifier -->
    <div class="dash-card" style="padding: 18px;">
        <div style="font-size: 11px; text-transform: uppercase; color: var(--dash-muted, var(--muted)); font-weight: 600; letter-spacing: 0.05em;">Session ID &amp; Location</div>
        <div style="display: flex; align-items: center; gap: 8px; margin-top: 6px;">
            <span class="dash-code-badge" style="font-size: 13px; font-weight: 700; font-family: monospace;">
                #{{ substr($session->uuid, 0, 8) }}
            </span>
            <span class="dash-tag is-primary" style="font-size: 10.5px;">
                {{ $session->country ?: 'Australia' }}
            </span>
        </div>
        <div style="font-size: 11.5px; color: var(--dash-muted, var(--muted)); margin-top: 4px; font-family: monospace;">
            {{ $session->ip_address ?: '127.0.0.1' }}
        </div>
    </div>

    <!-- Card 2: Total Compute Spend -->
    <div class="dash-card" style="padding: 18px;">
        <div style="font-size: 11px; text-transform: uppercase; color: var(--dash-muted, var(--muted)); font-weight: 600; letter-spacing: 0.05em;">Total Estimated Spend</div>
        <div style="font-size: 24px; font-weight: 700; color: #10b981; font-family: monospace; margin-top: 4px;">
            ${{ number_format($sessionTotalCost, 6) }} <span style="font-size: 12px; font-weight: normal; color: var(--dash-muted, var(--muted));">USD</span>
        </div>
        <div style="font-size: 11px; color: var(--dash-muted, var(--muted)); margin-top: 4px;">
            {{ $messageCount > 0 ? '~$' . number_format($sessionTotalCost / max(1, (int) ceil($messageCount / 2)), 6) . ' / turn' : 'No queries' }}
        </div>
    </div>

    <!-- Card 3: Total Tokens Consumed -->
    <div class="dash-card" style="padding: 18px;">
        <div style="font-size: 11px; text-transform: uppercase; color: var(--dash-muted, var(--muted)); font-weight: 600; letter-spacing: 0.05em;">Tokens Consumed</div>
        <div style="font-size: 24px; font-weight: 700; color: var(--accent); font-family: monospace; margin-top: 4px;">
            {{ number_format($sessionTotalTokens) }}
        </div>
        <div style="font-size: 11px; color: var(--dash-muted, var(--muted)); margin-top: 4px; font-family: monospace;">
            {{ number_format($sessionPromptTokens) }} in / {{ number_format($sessionCompletionTokens) }} out
        </div>
    </div>

    <!-- Card 4: AI Model & Driver -->
    <div class="dash-card" style="padding: 18px;">
        <div style="font-size: 11px; text-transform: uppercase; color: var(--dash-muted, var(--muted)); font-weight: 600; letter-spacing: 0.05em;">AI Model Engine</div>
        <div style="margin-top: 6px;">
            <span class="dash-code-badge" style="font-size: 12px; font-weight: 600; max-width: 100%; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; display: inline-block;" title="{{ $primaryModelName }}">
                {{ $primaryModelName }}
            </span>
        </div>
        <div style="font-size: 11px; color: var(--dash-muted, var(--muted)); margin-top: 4px;">
            {{ $messageCount }} {{ \Illuminate\Support\Str::plural('message', $messageCount) }} recorded
        </div>
    </div>
</div>

<!-- Main Two-Column Layout -->
<div style="display: grid; grid-template-columns: minmax(0, 2fr) minmax(320px, 1fr); gap: 24px; align-items: start;">
    <!-- Left Column: Conversation Transcript Stream -->
    <div class="dash-card" style="padding: 0; overflow: hidden; min-width: 0;">
        <!-- Transcript Header Bar -->
        <div style="padding: 18px 24px; border-bottom: 1px solid var(--line); display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 12px; background: rgba(255, 255, 255, 0.02);">
            <div style="display: flex; align-items: center; gap: 10px;">
                <div style="width: 10px; height: 10px; border-radius: 50%; background: #10b981; box-shadow: 0 0 8px rgba(16, 185, 129, 0.6);"></div>
                <h3 style="font-size: 15px; font-weight: 700; color: var(--dash-ink, var(--ink)); margin: 0;">
                    Conversation Timeline
                </h3>
                <span class="dash-pill is-active" style="font-size: 11px;">{{ $messageCount }} {{ \Illuminate\Support\Str::plural('Turn', $messageCount) }}</span>
            </div>
            <div style="font-size: 11.5px; color: var(--dash-muted, var(--muted)); font-family: monospace;">
                UUID: {{ $session->uuid }}
            </div>
        </div>

        <!-- Chat Stream Body -->
        <div style="padding: 24px; display: flex; flex-direction: column; gap: 24px; min-width: 0; width: 100%; box-sizing: border-box;" id="transcript-stream-container">
            @forelse ($messages as $index => $msg)
                @php
                    $isUser = $msg->isUser();
                @endphp
                <div style="display: flex; flex-direction: column; {{ $isUser ? 'align-items: flex-end;' : 'align-items: flex-start;' }}; gap: 6px; width: 100%; min-width: 0; box-sizing: border-box;">
                    <!-- Message Sender Meta Header -->
                    <div style="display: flex; align-items: center; gap: 8px; font-size: 11.5px; color: var(--dash-muted, var(--muted)); padding: 0 4px; max-width: 100%; flex-wrap: wrap;">
                        @if ($isUser)
                            <span style="font-weight: 700; color: #38bdf8; text-transform: uppercase; letter-spacing: 0.04em;">
                                {{ $session->lead_name ?: 'Visitor' }}
                            </span>
                            <span>•</span>
                            <time datetime="{{ $msg->created_at->toIso8601String() }}">
                                {{ $msg->created_at->timezone(config('app.timezone'))->format('g:i A') }}
                            </time>
                        @else
                            <div style="width: 18px; height: 18px; border-radius: 4px; background: var(--accent); color: #0b0b0b; display: inline-flex; align-items: center; justify-content: center; font-weight: 800; font-size: 10.5px; flex-shrink: 0;">
                                A
                            </div>
                            <span style="font-weight: 700; color: var(--accent); text-transform: uppercase; letter-spacing: 0.04em;">
                                Azoogi AI Assistant
                            </span>
                            @if ($msg->model)
                                <span class="dash-code-badge" style="font-size: 10px; padding: 1px 5px;">{{ $msg->model }}</span>
                            @endif
                            <span>•</span>
                            <time datetime="{{ $msg->created_at->toIso8601String() }}">
                                {{ $msg->created_at->timezone(config('app.timezone'))->format('g:i A') }}
                            </time>
                        @endif
                    </div>

                    <!-- Message Bubble -->
                    <div style="max-width: 88%; width: fit-content; min-width: 0; box-sizing: border-box; padding: 16px 20px; border-radius: {{ $isUser ? '14px 14px 2px 14px' : '14px 14px 14px 2px' }}; font-size: 13.5px; line-height: 1.6; word-break: break-word; overflow-wrap: anywhere; {{ $isUser ? 'background: rgba(56, 189, 248, 0.1); color: var(--dash-ink, var(--ink)); border: 1px solid rgba(56, 189, 248, 0.22);' : 'background: var(--dash-bg-2, var(--bg-2)); color: var(--dash-ink, var(--ink)); border: 1px solid var(--line); box-shadow: 0 2px 6px rgba(0, 0, 0, 0.08);' }}">
                        <div class="dash-transcript-content" style="font-family: inherit; word-break: break-word; overflow-wrap: anywhere;">
                            {!! \Illuminate\Support\Str::markdown($msg->content) !!}
                        </div>
                    </div>

                    <!-- Token Usage & Cost Audit Pill for Assistant Turns -->
                    @if (! $isUser && ($msg->tokens_used > 0 || $msg->estimated_cost > 0 || $msg->prompt_tokens > 0))
                        <div style="display: inline-flex; align-items: center; gap: 8px; padding: 4px 10px; border-radius: 6px; background: var(--dash-fill, rgba(255, 255, 255, 0.04)); border: 1px solid var(--dash-line, var(--line)); font-size: 11px; font-family: monospace; color: var(--dash-muted, var(--muted)); margin-top: 2px; max-width: 100%; flex-wrap: wrap; box-sizing: border-box;">
                            <span style="color: var(--dash-green, var(--accent)); font-weight: 600;">⚡ {{ number_format($msg->tokens_used) }} tokens</span>
                            <span style="color: var(--dash-line, var(--line));">|</span>
                            <span>{{ number_format($msg->prompt_tokens) }} in / {{ number_format($msg->completion_tokens) }} out</span>
                            <span style="color: var(--dash-line, var(--line));">|</span>
                            <span style="color: #10b981; font-weight: 700;">${{ number_format((float) $msg->estimated_cost, 6) }} USD</span>
                        </div>
                    @endif

                    <!-- Tool Calls & Database Queries Inspector (Collapsible Accordion) -->
                    @if ($msg->tool_calls && count($msg->tool_calls) > 0)
                        <div style="max-width: 88%; width: 100%; min-width: 0; box-sizing: border-box; margin-top: 4px;">
                            <details style="box-sizing: border-box; width: 100%; max-width: 100%; background: var(--dash-fill, rgba(255, 255, 255, 0.03)); border: 1px solid var(--dash-line, var(--line)); border-radius: 8px; padding: 10px 14px; font-size: 11.5px; font-family: monospace; overflow: hidden; box-shadow: 0 1px 3px rgba(0, 0, 0, 0.03);">
                                <summary style="cursor: pointer; color: var(--dash-green, var(--accent)); font-weight: 600; display: flex; align-items: center; gap: 6px; user-select: none; max-width: 100%; word-break: break-word;">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width: 14px; height: 14px; flex-shrink: 0; color: var(--dash-green, var(--accent));"><circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1 0 2.83 2 2 0 0 1-2.83 0l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-2 2 2 2 0 0 1-2-2v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83 0 2 2 0 0 1 0-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1-2-2 2 2 0 0 1 2-2h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 0-2.83 2 2 0 0 1 2.83 0l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 2-2 2 2 0 0 1 2 2v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 0 2 2 0 0 1 0 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 2 2 2 2 0 0 1-2 2h-.09a1.65 1.65 0 0 0-1.51 1z"/></svg>
                                    <span>Executed {{ count($msg->tool_calls) }} Database / Product Tool {{ \Illuminate\Support\Str::plural('Query', count($msg->tool_calls)) }}</span>
                                </summary>
                                <div style="margin-top: 10px; color: var(--dash-muted, var(--muted)); max-height: 250px; overflow-y: auto; overflow-x: auto; width: 100%; box-sizing: border-box;">
                                    <div style="font-weight: 600; color: var(--dash-ink, var(--ink)); margin-bottom: 4px;">Tool Arguments:</div>
                                    <pre style="margin: 0; background: var(--dash-fill-strong, rgba(0,0,0,0.06)); border: 1px solid var(--dash-line, var(--line)); color: var(--dash-ink, var(--ink)); padding: 8px 10px; border-radius: 6px; font-size: 11px; overflow-x: auto; max-width: 100%; box-sizing: border-box; white-space: pre-wrap; word-break: break-all;">{{ json_encode($msg->tool_calls, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) }}</pre>

                                    @if ($msg->tool_results)
                                        <div style="font-weight: 600; color: var(--dash-ink, var(--ink)); margin-top: 8px; margin-bottom: 4px;">Tool Response Payload:</div>
                                        <pre style="margin: 0; background: var(--dash-fill-strong, rgba(0,0,0,0.06)); border: 1px solid var(--dash-line, var(--line)); color: var(--dash-ink, var(--ink)); padding: 8px 10px; border-radius: 6px; font-size: 11px; overflow-x: auto; max-width: 100%; box-sizing: border-box; white-space: pre-wrap; word-break: break-all;">{{ json_encode($msg->tool_results, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) }}</pre>
                                    @endif
                                </div>
                            </details>
                        </div>
                    @endif
                </div>
            @empty
                <div style="padding: 48px; text-align: center; color: var(--dash-muted, var(--muted));">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" style="width: 36px; height: 36px; margin: 0 auto 12px; opacity: 0.5;"><path d="M21 11.5a8.38 8.38 0 0 1-.9 3.8 8.5 8.5 0 0 1-7.6 4.7 8.38 8.38 0 0 1-3.8-.9L3 21l1.9-5.7a8.38 8.38 0 0 1-.9-3.8 8.5 8.5 0 0 1 4.7-7.6 8.38 8.38 0 0 1 3.8-.9h.5a8.48 8.48 0 0 1 8 8v.5z"/></svg>
                    <p style="margin: 0; font-size: 14px;">No messages recorded in this conversation yet.</p>
                </div>
            @endforelse
        </div>
    </div>

    <!-- Right Column: Audit & Visitor Sidebar -->
    <div style="display: flex; flex-direction: column; gap: 20px;">
        <!-- Card 1: Token & Compute Cost Audit -->
        <div class="dash-card" style="padding: 22px;">
            <div style="font-size: 14px; font-weight: 700; color: var(--dash-ink, var(--ink)); margin-bottom: 16px; border-bottom: 1px solid var(--line); padding-bottom: 12px; display: flex; align-items: center; gap: 8px;">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width: 17px; height: 17px; color: var(--accent);"><polyline points="22 12 18 12 15 21 9 3 6 12 2 12"/></svg>
                <span>Token &amp; Compute Cost Audit</span>
            </div>

            <div style="margin-bottom: 16px;">
                <div style="font-size: 11px; text-transform: uppercase; color: var(--dash-muted, var(--muted)); font-weight: 600; letter-spacing: 0.05em;">Total Estimated Cost</div>
                <div style="font-size: 26px; font-weight: 800; color: #10b981; font-family: monospace; margin-top: 4px;">
                    ${{ number_format($sessionTotalCost, 6) }} <span style="font-size: 12px; font-weight: normal; color: var(--dash-muted, var(--muted));">USD</span>
                </div>
            </div>

            <div style="margin-bottom: 16px;">
                <div style="display: flex; justify-content: space-between; align-items: center; font-size: 11px; text-transform: uppercase; color: var(--dash-muted, var(--muted)); font-weight: 600;">
                    <span>Total Tokens Consumed</span>
                    <span style="font-family: monospace; font-size: 13px; color: var(--dash-ink, var(--ink));">{{ number_format($sessionTotalTokens) }}</span>
                </div>

                <!-- Input vs Output Token Visual Split Bar -->
                @php
                    $promptPct = $sessionTotalTokens > 0 ? (int) round(($sessionPromptTokens / $sessionTotalTokens) * 100) : 50;
                    $completionPct = 100 - $promptPct;
                @endphp
                <div style="width: 100%; height: 8px; border-radius: 4px; background: rgba(255, 255, 255, 0.06); display: flex; overflow: hidden; margin-top: 8px;">
                    <div style="width: {{ $promptPct }}%; background: #38bdf8;" title="Input / Prompt Tokens: {{ $promptPct }}%"></div>
                    <div style="width: {{ $completionPct }}%; background: var(--accent);" title="Output / Reply Tokens: {{ $completionPct }}%"></div>
                </div>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px; background: var(--dash-bg, var(--bg)); padding: 12px; border-radius: 8px; border: 1px solid var(--line); font-family: monospace; font-size: 11.5px; margin-bottom: 16px;">
                <div>
                    <div style="color: #38bdf8; font-size: 10.5px; font-weight: 600;">● Prompt / Input:</div>
                    <div style="font-weight: 700; color: var(--dash-ink, var(--ink)); font-size: 14px; margin-top: 2px;">{{ number_format($sessionPromptTokens) }}</div>
                </div>
                <div>
                    <div style="color: var(--accent); font-size: 10.5px; font-weight: 600;">● Output / Reply:</div>
                    <div style="font-weight: 700; color: var(--dash-ink, var(--ink)); font-size: 14px; margin-top: 2px;">{{ number_format($sessionCompletionTokens) }}</div>
                </div>
            </div>

            <div style="display: flex; flex-direction: column; gap: 10px; font-size: 12px;">
                <div style="display: flex; justify-content: space-between;">
                    <span style="color: var(--dash-muted, var(--muted));">Blended Rate:</span>
                    <span style="font-family: monospace; font-weight: 600; color: var(--dash-ink, var(--ink));">
                        ${{ $sessionTotalTokens > 0 ? number_format(($sessionTotalCost / $sessionTotalTokens) * 1000, 4) : '0.0000' }} / 1k tokens
                    </span>
                </div>
                <div style="display: flex; justify-content: space-between;">
                    <span style="color: var(--dash-muted, var(--muted));">Model Engine:</span>
                    <span class="dash-code-badge" style="font-size: 10.5px; max-width: 170px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;" title="{{ $primaryModelName }}">{{ $primaryModelName }}</span>
                </div>
            </div>
        </div>

        <!-- Card 2: Visitor Profile & Lead Information -->
        <div class="dash-card" style="padding: 22px;">
            <div style="font-size: 14px; font-weight: 700; color: var(--dash-ink, var(--ink)); margin-bottom: 16px; border-bottom: 1px solid var(--line); padding-bottom: 12px; display: flex; align-items: center; gap: 8px;">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width: 17px; height: 17px;"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                <span>Visitor &amp; Lead Profile</span>
            </div>

            <div style="display: flex; flex-direction: column; gap: 14px; font-size: 12.5px;">
                <div>
                    <div style="color: var(--dash-muted, var(--muted)); font-size: 10.5px; text-transform: uppercase; font-weight: 600;">Full Name</div>
                    <div style="margin-top: 3px; font-weight: 600; color: {{ $session->lead_name ? '#10b981' : 'var(--dash-ink, var(--ink))' }}; font-size: 13.5px;">
                        {{ $session->lead_name ?: 'Anonymous Visitor' }}
                    </div>
                </div>

                <div>
                    <div style="color: var(--dash-muted, var(--muted)); font-size: 10.5px; text-transform: uppercase; font-weight: 600;">Email Address</div>
                    <div style="margin-top: 3px;">
                        @if ($session->lead_email)
                            <a href="mailto:{{ $session->lead_email }}" style="color: #10b981; font-weight: 600; text-decoration: underline;">{{ $session->lead_email }}</a>
                        @else
                            <span style="color: var(--dash-muted, var(--muted));">Not provided</span>
                        @endif
                    </div>
                </div>

                <div>
                    <div style="color: var(--dash-muted, var(--muted)); font-size: 10.5px; text-transform: uppercase; font-weight: 600;">Project Reference / Name</div>
                    <div style="margin-top: 3px; font-weight: 600; color: {{ $session->project_name ? '#38bdf8' : 'var(--dash-ink, var(--ink))' }}; font-size: 13px;">
                        @if ($session->project_name)
                            📁 {{ $session->project_name }}
                        @else
                            <span style="color: var(--dash-muted, var(--muted)); font-weight: normal;">Not specified</span>
                        @endif
                    </div>
                </div>

                <div>
                    <div style="color: var(--dash-muted, var(--muted)); font-size: 10.5px; text-transform: uppercase; font-weight: 600;">Phone Number</div>
                    <div style="margin-top: 3px;">
                        @if ($session->lead_phone)
                            <a href="tel:{{ $session->lead_phone }}" style="color: var(--dash-ink, var(--ink)); text-decoration: none;">{{ $session->lead_phone }}</a>
                        @else
                            <span style="color: var(--dash-muted, var(--muted));">Not provided</span>
                        @endif
                    </div>
                </div>

                <div>
                    <div style="color: var(--dash-muted, var(--muted)); font-size: 10.5px; text-transform: uppercase; font-weight: 600;">Company / Firm</div>
                    <div style="margin-top: 3px; color: var(--dash-ink, var(--ink));">
                        {{ $session->lead_company ?: 'Not provided' }}
                    </div>
                </div>

                @if ($session->enquiry)
                    <div style="padding: 12px; background: rgba(16, 185, 129, 0.12); border: 1px solid rgba(16, 185, 129, 0.3); border-radius: 6px; margin-top: 4px;">
                        <div style="color: #10b981; font-size: 11px; font-weight: 700; text-transform: uppercase;">Official Lead Linked</div>
                        <div style="margin-top: 4px;">
                            <a href="{{ route('dashboard.enquiries.index', ['type' => $session->enquiry->type->menuSlug()]) }}" style="color: #10b981; font-weight: 600; text-decoration: underline; display: inline-flex; align-items: center; gap: 4px;">
                                <span>Enquiry #{{ $session->enquiry->id }} ({{ ucfirst($session->enquiry->type->value) }})</span>
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width: 12px; height: 12px;"><line x1="7" y1="17" x2="17" y2="7"/><polyline points="7 7 17 7 17 17"/></svg>
                            </a>
                        </div>
                    </div>
                @endif

                <div style="border-top: 1px solid var(--line); padding-top: 12px; display: flex; flex-direction: column; gap: 10px;">
                    <div>
                        <div style="color: var(--dash-muted, var(--muted)); font-size: 10.5px; text-transform: uppercase; font-weight: 600;">Session Started (Created)</div>
                        <div style="margin-top: 3px; color: var(--dash-ink, var(--ink)); font-size: 12px;">
                            {{ $session->created_at ? $session->created_at->timezone(config('app.timezone'))->format('j M Y, g:i A') : 'N/A' }}
                            <span style="color: var(--dash-muted, var(--muted)); font-size: 11px;">({{ $session->created_at ? $session->created_at->diffForHumans() : '' }})</span>
                        </div>
                    </div>

                    <div>
                        <div style="color: var(--dash-muted, var(--muted)); font-size: 10.5px; text-transform: uppercase; font-weight: 600;">IP &amp; Geolocation</div>
                        <div style="margin-top: 3px; font-family: monospace; color: var(--dash-ink, var(--ink));">
                            {{ $session->ip_address }} ({{ $session->country ?: 'Australia' }})
                        </div>
                    </div>

                    <div>
                        <div style="color: var(--dash-muted, var(--muted)); font-size: 10.5px; text-transform: uppercase; font-weight: 600;">Referrer Source</div>
                        <div style="margin-top: 3px; color: var(--dash-muted, var(--muted)); word-break: break-all; font-size: 11.5px;">
                            {{ $session->referrer_url ?: 'Direct Navigation / Homepage' }}
                        </div>
                    </div>

                    <div>
                        <div style="color: var(--dash-muted, var(--muted)); font-size: 10.5px; text-transform: uppercase; font-weight: 600;">Device / Browser</div>
                        <div style="margin-top: 3px; color: var(--dash-muted, var(--muted)); font-size: 11px; word-break: break-all; line-height: 1.4;">
                            {{ $session->user_agent ?: 'Unknown User Agent' }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Hidden pre-rendered transcript for copy button -->
<textarea id="raw-transcript-data" style="display: none;">{{ $rawTranscriptText }}</textarea>

@push('styles')
<style>
.dash-transcript-content strong,
.dash-transcript-content b {
    color: var(--accent);
    font-weight: 700;
}
.dash-transcript-content p {
    margin: 0 0 8px 0;
}
.dash-transcript-content p:last-child {
    margin-bottom: 0;
}
.dash-transcript-content ul,
.dash-transcript-content ol {
    margin: 6px 0 8px 0;
    padding-left: 20px;
    display: flex;
    flex-direction: column;
    gap: 4px;
}
.dash-transcript-content li::marker {
    color: var(--accent);
    font-weight: 700;
}
.dash-transcript-content h2,
.dash-transcript-content h3,
.dash-transcript-content h4 {
    color: var(--accent);
    margin: 12px 0 6px 0;
    font-weight: 700;
}
.dash-transcript-content h2 { font-size: 15px; color: var(--dash-ink, var(--ink)); }
.dash-transcript-content h3 { font-size: 14px; }
.dash-transcript-content h4 { font-size: 13.5px; }
.dash-transcript-content code {
    background: var(--dash-fill-strong, rgba(0, 0, 0, 0.05));
    color: var(--dash-green, var(--accent));
    padding: 2px 6px;
    border-radius: 4px;
    font-size: 12px;
    font-family: monospace;
    border: 1px solid var(--dash-line, var(--line));
}
.dash-transcript-content pre {
    background: var(--dash-fill-strong, rgba(0, 0, 0, 0.05));
    border: 1px solid var(--dash-line, var(--line));
    border-radius: 6px;
    padding: 10px;
    overflow-x: auto;
    font-size: 12px;
    margin: 8px 0;
    color: var(--dash-ink, var(--ink));
}
.dash-transcript-content a {
    color: var(--dash-green, var(--accent));
    text-decoration: underline;
}
</style>
@endpush

@push('scripts')
<script>
async function toggleShowFavorite(sessionId, btn) {
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
            const textEl = document.getElementById('btnShowFavoriteText');
            const star = document.getElementById('showFavStar');
            if (data.is_favorite) {
                btn.style.color = '#f59e0b';
                btn.style.borderColor = 'rgba(245,158,11,0.35)';
                btn.style.background = 'rgba(245,158,11,0.1)';
                if (textEl) textEl.textContent = 'Favorited';
                if (star) star.setAttribute('fill', '#f59e0b');
            } else {
                btn.style.color = '';
                btn.style.borderColor = '';
                btn.style.background = '';
                if (textEl) textEl.textContent = 'Favorite';
                if (star) star.setAttribute('fill', 'none');
            }
        }
    } catch (e) {
        console.error('Error toggling favorite:', e);
    } finally {
        btn.disabled = false;
    }
}

async function toggleShowRead(sessionId, btn) {
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
            const textEl = document.getElementById('btnShowReadText');
            if (data.is_read) {
                btn.style.color = '';
                btn.style.borderColor = '';
                btn.style.background = '';
                if (textEl) textEl.textContent = 'Unread';
            } else {
                btn.style.color = '#38bdf8';
                btn.style.borderColor = 'rgba(56,189,248,0.35)';
                btn.style.background = 'rgba(56,189,248,0.1)';
                if (textEl) textEl.textContent = 'Read';
            }
        }
    } catch (e) {
        console.error('Error toggling read status:', e);
    } finally {
        btn.disabled = false;
    }
}

function copyFullTranscript() {
    const rawEl = document.getElementById('raw-transcript-data');
    if (!rawEl) return;
    const text = rawEl.value;

    if (navigator.clipboard && window.isSecureContext) {
        navigator.clipboard.writeText(text).then(() => {
            const btn = document.getElementById('btnCopyTranscriptText');
            if (btn) {
                const originalText = btn.textContent;
                btn.textContent = 'Copied!';
                setTimeout(() => { btn.textContent = originalText; }, 2000);
            }
        });
    } else {
        rawEl.style.display = 'block';
        rawEl.select();
        document.execCommand('copy');
        rawEl.style.display = 'none';
        const btn = document.getElementById('btnCopyTranscriptText');
        if (btn) {
            const originalText = btn.textContent;
            btn.textContent = 'Copied!';
            setTimeout(() => { btn.textContent = originalText; }, 2000);
        }
    }
}
</script>
@endpush
@endsection
