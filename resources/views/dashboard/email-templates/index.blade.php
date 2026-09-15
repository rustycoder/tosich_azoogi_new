@extends('layouts.dashboard')

@section('title', 'Email Templates')

@section('content')
<div class="dash-head">
    <div class="dash-head-title">
        <h1>Email Notifications & Templates</h1>
    </div>
    <p class="dash-lead">Configure dynamic notification recipients, subject lines, and branded HTML templates for website inquiries and datasheet downloads.</p>
</div>

@if (session('status'))
    <div class="dash-status" style="background:#e6f4ea;color:#137333;padding:12px 16px;border-radius:6px;margin-bottom:20px;font-size:14px;border:1px solid #ceead6;">
        {{ session('status') }}
    </div>
@endif

<div class="dash-list">
    @forelse ($templates as $template)
        <article class="dash-list-card">
            <div class="dash-list-card-copy">
                @include('dashboard.partials.title-link', [
                    'href' => route('dashboard.email-templates.edit', $template),
                    'label' => $template->name,
                ])
                <p class="dash-list-sub" style="margin-top:4px;color:var(--muted);">
                    {{ $template->description }}
                </p>
                <div style="margin-top:8px;font-size:13px;display:flex;align-items:center;gap:16px;flex-wrap:wrap;">
                    <span><strong>Recipients:</strong> <code style="background:var(--rgba-hover);padding:2px 6px;border-radius:4px;font-size:12px;">{{ $template->recipient_emails }}</code></span>
                    <span><strong>Subject:</strong> <span style="color:var(--ink);opacity:0.85;">{{ $template->subject }}</span></span>
                </div>
            </div>
            <div class="dash-list-card-meta">
                <div class="dash-updated">
                    <span class="dash-list-label">Status</span>
                    @include('dashboard.partials.toggle', [
                        'url' => route('dashboard.email-templates.toggle-status', $template),
                        'on' => $template->is_active,
                        'label' => $template->is_active ? 'Active' : 'Disabled',
                        'onClass' => 'is-active',
                        'offClass' => 'is-inactive',
                    ])
                </div>
                @include('dashboard.partials.updated', ['record' => $template])
            </div>
        </article>
    @empty
        <div class="dash-card dash-empty">No email templates found.</div>
    @endforelse
</div>
@endsection
