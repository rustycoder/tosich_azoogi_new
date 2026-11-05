@php
    $starterChips = \App\Services\Chat\AdminChatOrchestrator::getStarterChips();
@endphp

<div class="azoogi-chat-widget dash-copilot-widget" id="admin-copilot-widget"
    data-init-url="{{ route('dashboard.ai-copilot.init') }}"
    data-message-url="{{ route('dashboard.ai-copilot.message') }}"
    data-clear-url="{{ route('dashboard.ai-copilot.clear') }}">
    <!-- Floating Bottom-Right Launcher -->
    <button type="button" class="azoogi-chat-launcher" id="admin-copilot-launcher" aria-label="Open Admin Copilot">
        <div class="azoogi-chat-launcher-icon">
            <span class="azoogi-chat-launcher-pulse"></span>
            <svg class="azoogi-chat-launcher-main-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="18" height="18">
                <path d="M12 3l1.912 5.813a2 2 0 0 0 1.275 1.275L21 12l-5.813 1.912a2 2 0 0 0-1.275 1.275L12 21l-1.912-5.813a2 2 0 0 0-1.275-1.275L3 12l5.813-1.912a2 2 0 0 0 1.275-1.275L12 3z"/>
            </svg>
        </div>
        <span class="azoogi-chat-launcher-text">Admin Copilot</span>
    </button>

    <!-- Chat Container Window -->
    <div class="azoogi-chat-container" id="admin-copilot-container" role="dialog" aria-modal="true" aria-label="Azoogi Admin Copilot">
        <!-- Header -->
        <div class="azoogi-chat-header">
            <div class="azoogi-chat-header-brand">
                <div class="azoogi-chat-avatar">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="18" height="18">
                        <path d="M12 3l1.912 5.813a2 2 0 0 0 1.275 1.275L21 12l-5.813 1.912a2 2 0 0 0-1.275 1.275L12 21l-1.912-5.813a2 2 0 0 0-1.275-1.275L3 12l5.813-1.912a2 2 0 0 0 1.275-1.275L12 3z"/>
                    </svg>
                    <span class="azoogi-chat-status-dot"></span>
                </div>
                <div class="azoogi-chat-title-wrap">
                    <h3>Azoogi Admin Copilot</h3>
                    <p>MCP Backend Operations Assistant</p>
                </div>
            </div>
            <div class="azoogi-chat-header-actions">
                <button type="button" class="azoogi-chat-header-btn" id="admin-copilot-reset" title="Reset conversation" aria-label="Reset conversation">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" width="16" height="16">
                        <path d="M3 12a9 9 0 0 1 9-9 9.75 9.75 0 0 1 6.74 2.74L21 8"/>
                        <path d="M21 3v5h-5"/>
                        <path d="M21 12a9 9 0 0 1-9 9 9.75 9.75 0 0 1-6.74-2.74L3 16"/>
                        <path d="M3 21v-5h5"/>
                    </svg>
                </button>
                <button type="button" class="azoogi-chat-header-btn azoogi-chat-header-close-btn" id="admin-copilot-close" title="Close Copilot" aria-label="Close Copilot">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="18" height="18">
                        <line x1="18" y1="6" x2="6" y2="18"/>
                        <line x1="6" y1="6" x2="18" y2="18"/>
                    </svg>
                </button>
            </div>
        </div>

        <!-- Body -->
        <div class="azoogi-chat-body" id="admin-copilot-body">
            <div class="azoogi-chat-welcome-container" id="admin-copilot-welcome">
                <div class="azoogi-chat-welcome-msg">
                    <div class="azoogi-chat-welcome-avatar">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16">
                            <path d="M12 3l1.912 5.813a2 2 0 0 0 1.275 1.275L21 12l-5.813 1.912a2 2 0 0 0-1.275 1.275L12 21l-1.912-5.813a2 2 0 0 0-1.275-1.275L3 12l5.813-1.912a2 2 0 0 0 1.275-1.275L12 3z"/>
                        </svg>
                    </div>
                    <div class="azoogi-chat-welcome-bubble">
                        <p>Hi {{ auth()->user()?->name ?? 'Admin' }}! I can inspect database records, update page content, trigger Airtable syncs, manage AI rules &amp; FAQs, or summarize analytics using MCP tools.</p>
                    </div>
                </div>

                <div class="azoogi-chat-chip-list">
                    @foreach ($starterChips as $chip)
                        <button type="button" class="azoogi-chat-chip js-admin-copilot-chip" data-prompt="{{ $chip['prompt'] }}">
                            <span>{{ $chip['label'] }}</span>
                        </button>
                    @endforeach
                </div>
            </div>
        </div>

        <div class="azoogi-chat-footer">
            <div class="azoogi-chat-input-bar">
                <div class="azoogi-chat-input-wrapper">
                    <input type="text" id="admin-copilot-input" class="azoogi-chat-input" placeholder="Ask Copilot to inspect or update backend..." autocomplete="off" maxlength="2000">
                </div>
                <button type="button" id="admin-copilot-send" class="azoogi-chat-send-btn" aria-label="Send command">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" width="18" height="18">
                        <line x1="22" y1="2" x2="11" y2="13"/>
                        <polygon points="22 2 15 22 11 13 2 9 22 2"/>
                    </svg>
                </button>
            </div>
            <div class="azoogi-chat-disclaimer">
                Connected to Backend MCP Tools &bull; Role-scoped &amp; audit-logged.
            </div>
        </div>
    </div>
</div>

