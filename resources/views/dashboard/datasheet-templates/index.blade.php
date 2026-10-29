@extends('layouts.dashboard')

@section('title', 'Datasheet Templates')

@section('content')
<div class="dash-head">
    <div class="dash-head-title">
        <h1>Datasheet Templates</h1>
        <div class="dash-head-actions">
            @if ($activeTemplate)
                <span class="dash-pill is-active" style="display: inline-flex; align-items: center; gap: 5px; font-weight: 600;" title="Current active template used for client datasheet exports">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" style="width: 12px; height: 12px;"><polyline points="20 6 9 17 4 12"/></svg>
                    Active: {{ $activeTemplate->name }}
                </span>
            @endif
            <span class="dash-pill">{{ $templates->total() }} {{ \Illuminate\Support\Str::plural('Template', $templates->total()) }}</span>
            <a class="btn primary" href="{{ route('dashboard.datasheet-templates.create') }}">Add Template</a>
        </div>
    </div>
    <p class="dash-lead">Design custom HTML &amp; CSS datasheet layouts, configure dynamic tokens, and select the active template used for client datasheet generation.</p>
</div>

@if (session('status'))
    <div class="dash-status" style="background:#e6f4ea;color:#137333;padding:12px 16px;border-radius:6px;margin-bottom:20px;font-size:14px;border:1px solid #ceead6;display:flex;align-items:center;gap:8px;">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:18px;height:18px;flex-shrink:0;"><polyline points="20 6 9 17 4 12"/></svg>
        <span>{{ session('status') }}</span>
    </div>
@endif

@if ($errors->any())
    <div class="dash-status" style="background:#fce8e6;color:#c5221f;padding:12px 16px;border-radius:6px;margin-bottom:20px;font-size:14px;border:1px solid #fad2cf;">
        {{ $errors->first() }}
    </div>
@endif

@include('dashboard.partials.search', [
    'action' => route('dashboard.datasheet-templates.index'),
    'search' => $search,
    'placeholder' => 'Search by template name or description',
])

<!-- Datasheet Templates Airtable Grid -->
<div class="dash-airtable-wrap">
    <table class="dash-airtable-table">
        <thead>
            <tr>
                <th scope="col" class="dash-sticky-col" style="min-width: 250px;">Datasheet Template</th>
                <th scope="col" style="width: 140px; text-align: center;">Status</th>
                <th scope="col" style="min-width: 160px;">Slug / Key</th>
                <th scope="col" style="min-width: 280px;">Description</th>
                <th scope="col" style="min-width: 170px;">Last Updated</th>
                <th scope="col" style="width: 130px; text-align: right;">Actions</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($templates as $template)
                <tr style="{{ $template->is_active ? 'background: rgba(103,208,78,0.03);' : '' }}">
                    <!-- 1. Template Name (Sticky Column) -->
                    <td class="dash-sticky-col">
                        <div style="display: flex; align-items: center; gap: 8px;">
                            <a href="{{ route('dashboard.datasheet-templates.edit', $template) }}" style="font-weight: 600; color: var(--dash-ink); text-decoration: none;">
                                {{ $template->name }}
                            </a>
                            @if ($template->is_active)
                                <span style="font-size: 10px; background: rgba(103,208,78,0.18); color: var(--accent-2); border: 1px solid var(--accent); padding: 1px 6px; border-radius: 4px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px;">Live</span>
                            @endif
                        </div>
                    </td>

                    <!-- 2. Active Status -->
                    <td style="text-align: center;">
                        @if ($template->is_active)
                            <span class="dash-pill is-active" style="display: inline-flex; align-items: center; gap: 4px; font-size: 11px; padding: 4px 10px; font-weight: 700;">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" style="width: 11px; height: 11px;"><polyline points="20 6 9 17 4 12"/></svg>
                                Active
                            </span>
                        @else
                            <form method="post" action="{{ route('dashboard.datasheet-templates.activate', $template) }}" style="display: inline-block;">
                                @csrf
                                @method('patch')
                                <button type="submit" class="btn" style="padding: 3px 8px; font-size: 11px; min-height: 24px; line-height: 1.2;" title="Make this the live datasheet template">
                                    Set Active
                                </button>
                            </form>
                        @endif
                    </td>

                    <!-- 3. Slug / Key -->
                    <td>
                        <span class="dash-code-badge" style="font-size: 11px; font-weight: 600;">{{ $template->slug }}</span>
                    </td>

                    <!-- 4. Description -->
                    <td>
                        <p style="margin: 0; font-size: 12px; color: var(--dash-muted); line-height: 1.4;">
                            {{ $template->description ?: '—' }}
                        </p>
                    </td>

                    <!-- 5. Last Updated -->
                    <td>
                        @include('dashboard.partials.updated', ['record' => $template])
                    </td>

                    <!-- 6. Actions (Preview, Duplicate, Delete) -->
                    <td style="text-align: right;">
                        <div style="display: inline-flex; align-items: center; gap: 4px; justify-content: flex-end;">
                            <a href="{{ route('dashboard.datasheet-templates.preview', $template) }}" target="_blank" rel="noopener" class="btn" style="padding: 4px 6px; min-height: unset; line-height: 1;" title="Live A4 preview in new tab">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" style="width: 13px; height: 13px;"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                            </a>
                            <form method="post" action="{{ route('dashboard.datasheet-templates.duplicate', $template) }}" style="display: inline;">
                                @csrf
                                <button type="submit" class="btn" style="padding: 4px 6px; min-height: unset; line-height: 1;" title="Duplicate template">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" style="width: 13px; height: 13px;"><rect x="9" y="9" width="13" height="13" rx="2" ry="2"/><path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"/></svg>
                                </button>
                            </form>
                            @if (! $template->is_active)
                                <form method="post" action="{{ route('dashboard.datasheet-templates.destroy', $template) }}" style="display: inline;" onsubmit="return confirm('Are you sure you want to delete this template?');">
                                    @csrf
                                    @method('delete')
                                    <button type="submit" class="btn danger" style="padding: 4px 6px; min-height: unset; line-height: 1;" title="Delete template">
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" style="width: 13px; height: 13px;"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg>
                                    </button>
                                </form>
                            @endif
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="6">
                        <div class="dash-card dash-empty">{{ $search === '' ? 'No datasheet templates created yet.' : 'No templates match that search.' }}</div>
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

@if ($templates->hasPages())
    <div style="margin-top: 18px;">
        {{ $templates->links('dashboard.partials.pagination') }}
    </div>
@endif
@endsection
