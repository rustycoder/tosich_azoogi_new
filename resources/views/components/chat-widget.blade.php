@php
    $branding = \App\Services\Chat\ChatOrchestrator::getWidgetBranding();
@endphp

<div class="azoogi-chat-widget" id="azoogi-chat-widget" data-branding="{{ json_encode($branding) }}">
    <!-- Floating Bottom-Right Launcher -->
    <button type="button" class="azoogi-chat-launcher" id="azoogi-chat-launcher" aria-label="Open {{ $branding['ai_name'] }}">
        <div class="azoogi-chat-launcher-icon">
            <span class="azoogi-chat-launcher-pulse"></span>
            <!-- Main Chat Sparkle/Message SVG -->
            <svg class="azoogi-chat-launcher-main-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="18" height="18">
                <path d="M21 11.5a8.38 8.38 0 0 1-.9 3.8 8.5 8.5 0 0 1-7.6 4.7 8.38 8.38 0 0 1-3.8-.9L3 21l1.9-5.7a8.38 8.38 0 0 1-.9-3.8 8.5 8.5 0 0 1 4.7-7.6 8.38 8.38 0 0 1 3.8-.9h.5a8.48 8.48 0 0 1 8 8v.5z"/>
            </svg>
        </div>
        <span class="azoogi-chat-launcher-text">Ask {{ $branding['ai_name'] }}</span>
    </button>

    <!-- Chat Container Window -->
    <div class="azoogi-chat-container" id="azoogi-chat-container" role="dialog" aria-modal="true" aria-label="{{ $branding['ai_name'] }}">
        <!-- Header -->
        <div class="azoogi-chat-header">

            <div class="azoogi-chat-header-brand">
                <div class="azoogi-chat-avatar js-chat-avatar">
                    @if (!empty($branding['ai_custom_avatar_url']))
                        <img src="{{ $branding['ai_custom_avatar_url'] }}" alt="{{ $branding['ai_name'] }}">
                    @else
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="20" height="20">
                            <path d="M21 11.5a8.38 8.38 0 0 1-.9 3.8 8.5 8.5 0 0 1-7.6 4.7 8.38 8.38 0 0 1-3.8-.9L3 21l1.9-5.7a8.38 8.38 0 0 1-.9-3.8 8.5 8.5 0 0 1 4.7-7.6 8.38 8.38 0 0 1 3.8-.9h.5a8.48 8.48 0 0 1 8 8v.5z"/>
                        </svg>
                    @endif
                    <span class="azoogi-chat-status-dot"></span>
                </div>
                <div class="azoogi-chat-title-wrap">
                    <h3 class="js-chat-header-title">{{ $branding['ai_name'] }}</h3>
                    <p class="js-chat-header-subtitle">{{ $branding['ai_subtitle'] }}</p>
                </div>
            </div>
            <div class="azoogi-chat-header-actions">
                <button type="button" class="azoogi-chat-header-btn" id="azoogi-chat-sound-toggle" title="Toggle audio chime" aria-label="Toggle sound">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" width="16" height="16">
                        <polygon points="11 5 6 9 2 9 2 15 6 15 11 19 11 5"/>
                        <path d="M15.54 8.46a5 5 0 0 1 0 7.07"/>
                    </svg>
                </button>
                <button type="button" class="azoogi-chat-header-btn" id="azoogi-chat-reset" title="Refresh conversation" aria-label="Refresh conversation">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" width="16" height="16">
                        <path d="M3 12a9 9 0 0 1 9-9 9.75 9.75 0 0 1 6.74 2.74L21 8"/>
                        <path d="M21 3v5h-5"/>
                        <path d="M21 12a9 9 0 0 1-9 9 9.75 9.75 0 0 1-6.74-2.74L3 16"/>
                        <path d="M3 21v-5h5"/>
                    </svg>
                </button>
                <button type="button" class="azoogi-chat-header-btn azoogi-chat-header-close-btn" id="azoogi-chat-close" title="Close chat" aria-label="Close chat">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="18" height="18">
                        <line x1="18" y1="6" x2="6" y2="18"/>
                        <line x1="6" y1="6" x2="18" y2="18"/>
                    </svg>
                </button>
            </div>
        </div>

        <!-- Chat Body Scroll Area -->
        <div class="azoogi-chat-body" id="azoogi-chat-body">
            <div class="azoogi-chat-welcome-container">
                <!-- AI Assistant Greeting Message -->
                <div class="azoogi-chat-welcome-msg">
                    <div class="azoogi-chat-welcome-avatar">
                        @if (!empty($branding['ai_custom_avatar_url']))
                            <img src="{{ $branding['ai_custom_avatar_url'] }}" alt="{{ $branding['ai_name'] }}">
                        @else
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16">
                                <path d="M21 11.5a8.38 8.38 0 0 1-.9 3.8 8.5 8.5 0 0 1-7.6 4.7 8.38 8.38 0 0 1-3.8-.9L3 21l1.9-5.7a8.38 8.38 0 0 1-.9-3.8 8.5 8.5 0 0 1 4.7-7.6 8.38 8.38 0 0 1 3.8-.9h.5a8.48 8.48 0 0 1 8 8v.5z"/>
                            </svg>
                        @endif
                    </div>
                    <div class="azoogi-chat-welcome-bubble">
                        <p>{{ $branding['startup_message'] }}</p>
                    </div>
                </div>

                <!-- Suggestion Chips -->
                <div class="azoogi-chat-chip-list">
                    @foreach ($branding['starter_chips'] as $idx => $chip)
                        @php
                            $chipText = is_array($chip) ? ($chip['prompt'] ?? $chip['label'] ?? '') : (string) $chip;
                        @endphp
                        @if (!empty($chipText))
                            <button type="button" class="azoogi-chat-chip js-chat-chip is-featured" data-prompt="{{ $chipText }}">
                                <svg viewBox="0 0 24 24" fill="currentColor" width="10" height="10" style="margin-right: 6px; flex-shrink: 0;"><polygon points="6 3 20 12 6 21 6 3"/></svg>
                                <span>{{ $chipText }}</span>
                            </button>
                        @endif
                    @endforeach
                </div>

                <!-- CTA Actions Block matching design -->
                @if (!empty($branding['show_consultation_btn']) || !empty($branding['show_report_issue']))
                    <div class="azoogi-chat-welcome-actions" id="azoogi-chat-welcome-actions">
                        @if (!empty($branding['show_consultation_btn']))
                            <button type="button" class="azoogi-chat-cta-btn" id="azoogi-chat-cta-btn">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16">
                                    <rect x="3" y="4" width="18" height="18" rx="2" ry="2"/>
                                    <line x1="16" y1="2" x2="16" y2="6"/>
                                    <line x1="8" y1="2" x2="8" y2="6"/>
                                    <line x1="3" y1="10" x2="21" y2="10"/>
                                </svg>
                                <span>{{ $branding['consultation_btn_text'] ?? 'Book a consultation' }}</span>
                            </button>
                        @endif
                        @if (!empty($branding['show_report_issue']))
                            <button type="button" class="azoogi-chat-report-link" id="azoogi-chat-report-link">{{ $branding['report_issue_text'] ?? 'Report an issue with this chat' }}</button>
                        @endif
                    </div>
                @endif
            </div>
        </div>

        <!-- Custom Pop-up Dialog: Refresh Conversation -->
        <div class="azoogi-chat-modal-overlay" id="azoogi-chat-refresh-modal" aria-hidden="true">
            <div class="azoogi-chat-modal-card" role="alertdialog" aria-labelledby="azoogi-refresh-modal-title" aria-describedby="azoogi-refresh-modal-desc">
                <div class="azoogi-chat-modal-icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="22" height="22">
                        <path d="M3 12a9 9 0 0 1 9-9 9.75 9.75 0 0 1 6.74 2.74L21 8"/>
                        <path d="M21 3v5h-5"/>
                        <path d="M21 12a9 9 0 0 1-9 9 9.75 9.75 0 0 1-6.74-2.74L3 16"/>
                        <path d="M3 21v-5h5"/>
                    </svg>
                </div>
                <h4 class="azoogi-chat-modal-title" id="azoogi-refresh-modal-title">Refresh Conversation?</h4>
                <p class="azoogi-chat-modal-desc" id="azoogi-refresh-modal-desc">This will clear your current chat history and start a fresh consultation.</p>
                <div class="azoogi-chat-modal-actions">
                    <button type="button" class="azoogi-chat-modal-btn is-cancel" id="azoogi-refresh-cancel-btn">Cancel</button>
                    <button type="button" class="azoogi-chat-modal-btn is-confirm" id="azoogi-refresh-confirm-btn">Start Fresh</button>
                </div>
            </div>
        </div>

        <!-- Custom Pop-up Dialog: Report an Issue -->
        <div class="azoogi-chat-modal-overlay" id="azoogi-chat-report-modal" aria-hidden="true">
            <div class="azoogi-chat-modal-card is-form" role="dialog" aria-labelledby="azoogi-report-modal-title">
                <div class="azoogi-chat-modal-head">
                    <div class="azoogi-chat-modal-head-title">
                        <span class="azoogi-chat-modal-icon is-sm">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16">
                                <circle cx="12" cy="12" r="10"/>
                                <line x1="12" y1="8" x2="12" y2="12"/>
                                <line x1="12" y1="16" x2="12.01" y2="16"/>
                            </svg>
                        </span>
                        <h4 class="azoogi-chat-modal-title" id="azoogi-report-modal-title">Report an Issue</h4>
                    </div>
                    <button type="button" class="azoogi-chat-modal-close" id="azoogi-report-close-btn" aria-label="Close dialog">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16">
                            <line x1="18" y1="6" x2="6" y2="18"/>
                            <line x1="6" y1="6" x2="18" y2="18"/>
                        </svg>
                    </button>
                </div>
                <p class="azoogi-chat-modal-desc" style="text-align: left; margin-bottom: 12px;">Let our team know what issue you faced with this chat and we will look into it right away.</p>
                <form id="azoogi-chat-report-form">
                    <div class="azoogi-chat-modal-field-row">
                        <div class="azoogi-chat-modal-field">
                            <label for="azoogi-report-name">Your Name (Optional)</label>
                            <input type="text" id="azoogi-report-name" class="azoogi-chat-intake-input" placeholder="Jane Doe" maxlength="120">
                        </div>
                        <div class="azoogi-chat-modal-field">
                            <label for="azoogi-report-email">Your Email (Optional)</label>
                            <input type="email" id="azoogi-report-email" class="azoogi-chat-intake-input" placeholder="jane@studio.com.au" maxlength="150">
                        </div>
                    </div>
                    <div class="azoogi-chat-modal-field">
                        <label for="azoogi-report-issue-input">What issue did you face? <span class="req">*</span></label>
                        <textarea id="azoogi-report-issue-input" class="azoogi-chat-intake-input azoogi-chat-modal-textarea" rows="4" placeholder="Describe the issue you experienced..." required maxlength="2000"></textarea>
                    </div>
                    <div id="azoogi-report-feedback" class="azoogi-chat-modal-feedback" style="display: none;"></div>
                    <div class="azoogi-chat-modal-actions" style="margin-top: 14px;">
                        <button type="button" class="azoogi-chat-modal-btn is-cancel" id="azoogi-report-cancel-btn">Cancel</button>
                        <button type="submit" class="azoogi-chat-modal-btn is-confirm" id="azoogi-report-submit-btn">Send Report</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Footer Input -->
        <div class="azoogi-chat-footer">
            <div class="azoogi-chat-input-bar">
                <div class="azoogi-chat-input-wrapper">
                    <input type="text" id="azoogi-chat-input" class="azoogi-chat-input" placeholder="Ask me anything..." autocomplete="off" maxlength="1000">
                </div>
                <button type="button" id="azoogi-chat-send" class="azoogi-chat-send-btn" aria-label="Send message">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" width="18" height="18">
                        <line x1="22" y1="2" x2="11" y2="13"/>
                        <polygon points="22 2 15 22 11 13 2 9 22 2"/>
                    </svg>
                </button>
            </div>
            <div class="azoogi-chat-disclaimer js-chat-disclaimer" id="azoogi-chat-disclaimer" style="{{ empty($branding['disclaimer_text']) ? 'display: none;' : '' }}">
                {{ $branding['disclaimer_text'] ?? 'Azoogi AI can make mistakes. Verify critical specs with our engineering team.' }}
            </div>
        </div>
    </div>
</div>
