@extends('layouts.dashboard')

@section('title', 'AI Chat Conversations')

@section('content')
<div class="dash-head">
    <div class="dash-head-title">
        <h1>AI Chat Conversations</h1>
        <div class="dash-head-actions">
            <span class="dash-doc-badge">Live Assistant Logs</span>
        </div>
    </div>
    <p class="dash-lead">Inspect real-time visitor inquiries, questions asked, AI recommendations, and leads generated through the website chat assistant.</p>
</div>

<!-- Metrics Overview -->
<div class="dash-stats-grid" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 16px; margin-bottom: 24px;">
    <div class="dash-card" style="padding: 18px;">
        <div style="font-size: 11px; text-transform: uppercase; color: var(--dash-muted); font-weight: 600; letter-spacing: 0.05em;">Total Conversations</div>
        <div style="font-size: 28px; font-weight: 700; color: var(--dash-ink); margin-top: 6px;">{{ number_format($metrics['total_conversations']) }}</div>
    </div>
    <div class="dash-card" style="padding: 18px;">
        <div style="font-size: 11px; text-transform: uppercase; color: var(--dash-muted); font-weight: 600; letter-spacing: 0.05em;">Leads Captured</div>
        <div style="font-size: 28px; font-weight: 700; color: #10b981; margin-top: 6px;">{{ number_format($metrics['leads_captured']) }}</div>
    </div>
    <div class="dash-card" style="padding: 18px;">
        <div style="font-size: 11px; text-transform: uppercase; color: var(--dash-muted); font-weight: 600; letter-spacing: 0.05em;">Active Today</div>
        <div style="font-size: 28px; font-weight: 700; color: var(--dash-accent, #67d04e); margin-top: 6px;">{{ number_format($metrics['active_today']) }}</div>
    </div>
    <div class="dash-card" style="padding: 18px;">
        <div style="font-size: 11px; text-transform: uppercase; color: var(--dash-muted); font-weight: 600; letter-spacing: 0.05em;">Total Messages Exchanged</div>
        <div style="font-size: 28px; font-weight: 700; color: var(--dash-ink); margin-top: 6px;">{{ number_format($metrics['total_messages']) }}</div>
    </div>
</div>

<!-- Search and Filter Bar -->
<div class="dash-card" style="padding: 16px; margin-bottom: 24px;">
    <form method="GET" action="{{ route('dashboard.chat-sessions.index') }}" style="display: flex; flex-wrap: wrap; gap: 12px; align-items: center;">
        <div style="flex: 1; min-width: 240px; position: relative;">
            <input type="search" name="q" value="{{ $searchQuery }}" placeholder="Search by lead name, email, IP, country, or keyword..." class="dash-input" style="width: 100%; padding: 10px 14px; border-radius: 8px; border: 1px solid var(--dash-border); background: var(--dash-card-bg); color: var(--dash-ink);">
        </div>
        <div>
            <select name="status" class="dash-select" style="padding: 10px 14px; border-radius: 8px; border: 1px solid var(--dash-border); background: var(--dash-card-bg); color: var(--dash-ink);" onchange="this.form.submit()">
                <option value="">All Statuses</option>
                <option value="active" {{ $currentStatus === 'active' ? 'selected' : '' }}>Active</option>
                <option value="completed" {{ $currentStatus === 'completed' ? 'selected' : '' }}>Completed / Lead</option>
                <option value="abandoned" {{ $currentStatus === 'abandoned' ? 'selected' : '' }}>Abandoned</option>
            </select>
        </div>
        <div style="display: flex; align-items: center; gap: 8px;">
            <label style="font-size: 13px; color: var(--dash-ink); cursor: pointer; display: flex; align-items: center; gap: 6px;">
                <input type="checkbox" name="has_lead" value="1" {{ $hasLead ? 'checked' : '' }} onchange="this.form.submit()">
                With Captured Lead
            </label>
        </div>
        <button type="submit" class="dash-btn dash-btn-primary" style="padding: 10px 18px;">Filter</button>
        @if ($searchQuery || $currentStatus || $hasLead)
            <a href="{{ route('dashboard.chat-sessions.index') }}" class="dash-btn dash-btn-outline" style="padding: 10px 14px; text-decoration: none;">Reset</a>
        @endif
    </form>
</div>

<!-- Sessions Table -->
<div class="dash-card" style="overflow: hidden;">
    <div style="overflow-x: auto;">
        <table class="dash-table" style="width: 100%; border-collapse: collapse; text-align: left; font-size: 13.5px;">
            <thead>
                <tr style="border-bottom: 1px solid var(--dash-border); background: rgba(255, 255, 255, 0.02);">
                    <th style="padding: 14px 18px; color: var(--dash-muted); font-weight: 600;">Visitor / Lead</th>
                    <th style="padding: 14px 18px; color: var(--dash-muted); font-weight: 600;">Location & IP</th>
                    <th style="padding: 14px 18px; color: var(--dash-muted); font-weight: 600;">Conversation Summary</th>
                    <th style="padding: 14px 18px; color: var(--dash-muted); font-weight: 600;">Messages</th>
                    <th style="padding: 14px 18px; color: var(--dash-muted); font-weight: 600;">Status</th>
                    <th style="padding: 14px 18px; color: var(--dash-muted); font-weight: 600;">Last Activity</th>
                    <th style="padding: 14px 18px; color: var(--dash-muted); font-weight: 600; text-align: right;">Action</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($sessions as $session)
                    <tr style="border-bottom: 1px solid var(--dash-border); transition: background 0.15s ease;">
                        <td style="padding: 14px 18px;">
                            @if ($session->lead_name || $session->lead_email)
                                <div style="font-weight: 600; color: #10b981;">{{ $session->lead_name ?: 'Identified Lead' }}</div>
                                <div style="font-size: 11.5px; color: var(--dash-muted);">{{ $session->lead_email }}</div>
                                @if ($session->lead_phone)
                                    <div style="font-size: 11px; color: var(--dash-muted);">{{ $session->lead_phone }}</div>
                                @endif
                            @else
                                <div style="font-weight: 500; color: var(--dash-ink);">Anonymous Visitor</div>
                                <div style="font-size: 11px; color: var(--dash-muted); font-family: monospace;">{{ substr($session->uuid, 0, 8) }}...</div>
                            @endif
                        </td>
                        <td style="padding: 14px 18px;">
                            <div>{{ $session->country ?: 'Australia' }}</div>
                            <div style="font-size: 11.5px; color: var(--dash-muted); font-family: monospace;">{{ $session->ip_address }}</div>
                        </td>
                        <td style="padding: 14px 18px; max-width: 320px;">
                            <div style="white-space: nowrap; overflow: hidden; text-overflow: ellipsis; color: var(--dash-ink);">
                                {{ $session->summary ?: 'Initial greeting & inquiry' }}
                            </div>
                            @if ($session->referrer_url)
                                <div style="font-size: 11px; color: var(--dash-muted); white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                                    Via: {{ parse_url($session->referrer_url, PHP_URL_PATH) ?: $session->referrer_url }}
                                </div>
                            @endif
                        </td>
                        <td style="padding: 14px 18px;">
                            <span class="dash-pill-active" style="font-size: 11px;">{{ $session->messages_count }} msgs</span>
                        </td>
                        <td style="padding: 14px 18px;">
                            @if ($session->status === 'completed' || $session->enquiry_id)
                                <span style="display: inline-block; padding: 3px 8px; border-radius: 4px; font-size: 11px; font-weight: 600; background: rgba(16, 185, 129, 0.15); color: #10b981;">
                                    Quote Lead
                                </span>
                            @elseif ($session->status === 'active')
                                <span style="display: inline-block; padding: 3px 8px; border-radius: 4px; font-size: 11px; font-weight: 600; background: rgba(103, 208, 78, 0.15); color: #67d04e;">
                                    Active
                                </span>
                            @else
                                <span style="display: inline-block; padding: 3px 8px; border-radius: 4px; font-size: 11px; font-weight: 600; background: rgba(255, 255, 255, 0.08); color: var(--dash-muted);">
                                    {{ ucfirst($session->status) }}
                                </span>
                            @endif
                        </td>
                        <td style="padding: 14px 18px; color: var(--dash-muted); font-size: 12.5px;">
                            {{ $session->updated_at->diffForHumans() }}
                        </td>
                        <td style="padding: 14px 18px; text-align: right;">
                            <a href="{{ route('dashboard.chat-sessions.show', $session) }}" class="dash-btn dash-btn-sm dash-btn-outline" style="text-decoration: none; padding: 6px 12px; font-size: 12px;">
                                View Chat &rarr;
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" style="padding: 36px; text-align: center; color: var(--dash-muted);">
                            No chat conversations found matching your filters.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if ($sessions->hasPages())
        <div style="padding: 16px; border-top: 1px solid var(--dash-border);">
            {{ $sessions->links() }}
        </div>
    @endif
</div>
@endsection
