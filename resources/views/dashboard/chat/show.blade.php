@extends('layouts.dashboard')

@section('title', 'Chat Session Transcript')

@section('content')
<div class="dash-head">
    <div class="dash-head-title">
        <h1>Conversation Transcript</h1>
        <div class="dash-head-actions">
            <a href="{{ route('dashboard.chat-sessions.index') }}" class="dash-btn dash-btn-outline" style="text-decoration: none; padding: 8px 14px; font-size: 13px;">
                &larr; Back to All Chats
            </a>
            @if (! $session->enquiry_id)
                <form method="POST" action="{{ route('dashboard.chat-sessions.convert-enquiry', $session) }}" style="display: inline;">
                    @csrf
                    <button type="submit" class="dash-btn dash-btn-primary" style="padding: 8px 14px; font-size: 13px;" onclick="return confirm('Convert this chat into an official Quote Enquiry?')">
                        + Convert to Official Enquiry
                    </button>
                </form>
            @endif
        </div>
    </div>
    <p class="dash-lead">Review turn-by-turn questions asked, AI recommendations, executed tool queries, and visitor actions.</p>
</div>

@if (session('success'))
    <div class="dash-callout" style="padding: 12px 16px; background: rgba(16, 185, 129, 0.15); border: 1px solid #10b981; border-radius: 8px; color: #10b981; margin-bottom: 20px;">
        {{ session('success') }}
    </div>
@endif

<div style="display: grid; grid-template-columns: 2fr 1fr; gap: 24px; align-items: start;">
    <!-- Left: Conversation Transcript -->
    <div class="dash-card" style="padding: 24px;">
        <div style="font-size: 14px; font-weight: 600; color: var(--dash-ink); margin-bottom: 20px; display: flex; align-items: center; justify-content: space-between;">
            <span>Transcript ({{ $messages->count() }} messages)</span>
            <span style="font-size: 12px; color: var(--dash-muted); font-weight: normal;">Session UUID: {{ $session->uuid }}</span>
        </div>

        <div style="display: flex; flex-direction: column; gap: 20px;">
            @forelse ($messages as $msg)
                <div style="display: flex; flex-direction: column; {{ $msg->isUser() ? 'align-items: flex-end;' : 'align-items: flex-start;' }}">
                    <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 4px;">
                        <span style="font-size: 11px; font-weight: 600; color: {{ $msg->isUser() ? '#38bdf8' : '#67d04e' }}; text-transform: uppercase;">
                            {{ $msg->isUser() ? 'Visitor' : 'Azoogi AI' }}
                        </span>
                        <span style="font-size: 11px; color: var(--dash-muted);">
                            {{ $msg->created_at->timezone(config('app.timezone'))->format('j M Y, g:i A') }}
                        </span>
                    </div>

                    <!-- Message Bubble -->
                    <div style="max-width: 85%; padding: 14px 18px; border-radius: 14px; font-size: 13.5px; line-height: 1.55; {{ $msg->isUser() ? 'background: rgba(56, 189, 248, 0.12); color: var(--dash-ink); border: 1px solid rgba(56, 189, 248, 0.25);' : 'background: var(--dash-card-bg); color: var(--dash-ink); border: 1px solid var(--dash-border);' }}">
                        {!! nl2br(e($msg->content)) !!}
                    </div>

                    <!-- Tool Calls Inspector (if any executed) -->
                    @if ($msg->tool_calls)
                        <div style="margin-top: 8px; width: 85%;">
                            <details style="background: rgba(0, 0, 0, 0.35); border: 1px solid var(--dash-border); border-radius: 8px; padding: 8px 12px; font-size: 12px; font-family: monospace;">
                                <summary style="cursor: pointer; color: var(--dash-accent, #67d04e); font-weight: 600;">
                                    ⚙️ Executed {{ count($msg->tool_calls) }} Tool(s) &amp; Database Queries
                                </summary>
                                <div style="margin-top: 8px; color: var(--dash-muted); white-space: pre-wrap; word-break: break-all;">
                                    <strong>Tool Calls:</strong>
                                    {{ json_encode($msg->tool_calls, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) }}

                                    @if ($msg->tool_results)
                                        <div style="margin-top: 8px;">
                                            <strong>Tool Results:</strong>
                                            {{ json_encode($msg->tool_results, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) }}
                                        </div>
                                    @endif
                                </div>
                            </details>
                        </div>
                    @endif
                </div>
            @empty
                <div style="padding: 40px; text-align: center; color: var(--dash-muted);">
                    No messages recorded in this conversation yet.
                </div>
            @endforelse
        </div>
    </div>

    <!-- Right: Visitor Profile Card -->
    <div style="display: flex; flex-direction: column; gap: 20px;">
        <div class="dash-card" style="padding: 20px;">
            <div style="font-size: 14px; font-weight: 600; color: var(--dash-ink); margin-bottom: 14px; border-bottom: 1px solid var(--dash-border); padding-bottom: 10px;">
                Visitor Profile &amp; Lead Info
            </div>

            <dl style="display: flex; flex-direction: column; gap: 12px; font-size: 13px; margin: 0;">
                <div>
                    <dt style="color: var(--dash-muted); font-size: 11px; text-transform: uppercase;">Lead Full Name</dt>
                    <dd style="margin: 2px 0 0; font-weight: 600; color: {{ $session->lead_name ? '#10b981' : 'var(--dash-ink)' }};">
                        {{ $session->lead_name ?: 'Not Provided' }}
                    </dd>
                </div>
                <div>
                    <dt style="color: var(--dash-muted); font-size: 11px; text-transform: uppercase;">Email Address</dt>
                    <dd style="margin: 2px 0 0; color: {{ $session->lead_email ? '#10b981' : 'var(--dash-ink)' }};">
                        @if ($session->lead_email)
                            <a href="mailto:{{ $session->lead_email }}" style="color: #10b981; text-decoration: underline;">{{ $session->lead_email }}</a>
                        @else
                            Not Provided
                        @endif
                    </dd>
                </div>
                <div>
                    <dt style="color: var(--dash-muted); font-size: 11px; text-transform: uppercase;">Phone Number</dt>
                    <dd style="margin: 2px 0 0; color: var(--dash-ink);">{{ $session->lead_phone ?: 'Not Provided' }}</dd>
                </div>
                <div>
                    <dt style="color: var(--dash-muted); font-size: 11px; text-transform: uppercase;">Company / Firm</dt>
                    <dd style="margin: 2px 0 0; color: var(--dash-ink);">{{ $session->lead_company ?: 'Not Provided' }}</dd>
                </div>
                @if ($session->enquiry)
                    <div style="padding: 10px; background: rgba(16, 185, 129, 0.12); border: 1px solid #10b981; border-radius: 6px;">
                        <dt style="color: #10b981; font-size: 11px; font-weight: 600; text-transform: uppercase;">Linked Official Lead</dt>
                        <dd style="margin: 4px 0 0; font-weight: 600;">
                            <a href="{{ route('dashboard.enquiries.index', ['type' => $session->enquiry->type->menuSlug()]) }}" style="color: #10b981; text-decoration: underline;">
                                View Enquiry #{{ $session->enquiry->id }} &rarr;
                            </a>
                        </dd>
                    </div>
                @endif
                <div style="border-top: 1px solid var(--dash-border); padding-top: 10px;">
                    <dt style="color: var(--dash-muted); font-size: 11px; text-transform: uppercase;">IP Address &amp; Location</dt>
                    <dd style="margin: 2px 0 0; font-family: monospace; color: var(--dash-ink);">
                        {{ $session->ip_address }} ({{ $session->country ?: 'Australia' }})
                    </dd>
                </div>
                <div>
                    <dt style="color: var(--dash-muted); font-size: 11px; text-transform: uppercase;">Referrer Landing Page</dt>
                    <dd style="margin: 2px 0 0; color: var(--dash-ink); word-break: break-all;">
                        {{ $session->referrer_url ?: 'Direct Visit' }}
                    </dd>
                </div>
                <div>
                    <dt style="color: var(--dash-muted); font-size: 11px; text-transform: uppercase;">User Agent</dt>
                    <dd style="margin: 2px 0 0; color: var(--dash-muted); font-size: 11.5px; word-break: break-all;">
                        {{ $session->user_agent }}
                    </dd>
                </div>
            </dl>
        </div>
    </div>
</div>
@endsection
