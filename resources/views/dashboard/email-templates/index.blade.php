@extends('layouts.dashboard')

@section('title', 'Email Notifications')

@section('content')
<div class="dash-head">
    <div class="dash-head-title">
        <h1>Email Notifications</h1>
        <div class="dash-head-actions">
            <span class="dash-pill is-active">{{ $templates->count() }} Templates</span>
        </div>
    </div>
    <p class="dash-lead">Configure dynamic notification recipients, subject lines, and branded HTML templates for website inquiries and datasheet downloads.</p>
</div>

@if (session('status'))
    <div class="dash-status" style="background:#e6f4ea;color:#137333;padding:12px 16px;border-radius:6px;margin-bottom:20px;font-size:14px;border:1px solid #ceead6;">
        {{ session('status') }}
    </div>
@endif

<!-- Email Templates Airtable Grid -->
<div class="dash-airtable-wrap">
    <table class="dash-airtable-table">
        <thead>
            <tr>
                <th scope="col" class="dash-sticky-col" style="min-width: 240px;">Notification Template</th>
                <th scope="col" style="width: 140px; text-align: center;">Identifier</th>
                <th scope="col" style="width: 110px; text-align: center;">Status</th>
                <th scope="col" style="min-width: 320px;">Description</th>
                <th scope="col" style="min-width: 170px;">Updated</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($templates as $template)
                <tr>
                    <!-- 1. Template Name (Sticky Column) -->
                    <td class="dash-sticky-col">
                        @include('dashboard.partials.title-link', [
                            'href' => route('dashboard.email-templates.edit', $template),
                            'label' => $template->name,
                        ])
                    </td>

                    <!-- 2. Identifier Key -->
                    <td style="text-align: center;">
                        <span class="dash-code-badge" style="font-size: 11px; font-weight: 600;">{{ $template->key }}</span>
                    </td>

                    <!-- 3. Status Toggle -->
                    <td style="text-align: center;">
                        @include('dashboard.partials.toggle', [
                            'url' => route('dashboard.email-templates.toggle-status', $template),
                            'on' => $template->is_active,
                            'label' => $template->is_active ? 'Active' : 'Disabled',
                            'onClass' => 'is-active',
                            'offClass' => 'is-inactive',
                        ])
                    </td>

                    <!-- 4. Description -->
                    <td>
                        <p style="margin: 0; font-size: 12px; color: var(--dash-muted); line-height: 1.4;">
                            {{ $template->description }}
                        </p>
                    </td>

                    <!-- 5. Updated -->
                    <td>
                        <div class="dash-updated" style="gap: 2px;">
                            <span class="dash-updated-value" style="font-size: 11.5px;">
                                @if ($template->updated_at)
                                    <time datetime="{{ $template->updated_at->toIso8601String() }}">{{ $template->updated_at->timezone(config('app.timezone'))->format('j M Y, g:i A') }}</time>
                                @else
                                    <span style="color: var(--dash-muted);">—</span>
                                @endif
                            </span>
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="5">
                        <div class="dash-card dash-empty">No email templates found.</div>
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection
