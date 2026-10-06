<div class="azoogi-chat-widget" id="azoogi-chat-widget">
    <!-- Floating Bottom-Right Launcher -->
    <button type="button" class="azoogi-chat-launcher" id="azoogi-chat-launcher" aria-label="Open Azoogi AI Lighting Assistant">
        <div class="azoogi-chat-launcher-icon">
            <span class="azoogi-chat-launcher-pulse"></span>
            <!-- Main Chat Sparkle/Message SVG -->
            <svg class="azoogi-chat-launcher-main-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="18" height="18">
                <path d="M21 11.5a8.38 8.38 0 0 1-.9 3.8 8.5 8.5 0 0 1-7.6 4.7 8.38 8.38 0 0 1-3.8-.9L3 21l1.9-5.7a8.38 8.38 0 0 1-.9-3.8 8.5 8.5 0 0 1 4.7-7.6 8.38 8.38 0 0 1 3.8-.9h.5a8.48 8.48 0 0 1 8 8v.5z"/>
            </svg>
            <!-- Close SVG when open -->
            <svg class="azoogi-chat-launcher-close-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" width="16" height="16">
                <line x1="18" y1="6" x2="6" y2="18"/>
                <line x1="6" y1="6" x2="18" y2="18"/>
            </svg>
        </div>
        <span class="azoogi-chat-launcher-text">Ask Azoogi AI</span>
    </button>

    <!-- Chat Container Window -->
    <div class="azoogi-chat-container" id="azoogi-chat-container" role="dialog" aria-modal="true" aria-label="Azoogi AI Assistant">
        <!-- Header -->
        <div class="azoogi-chat-header">
            <div class="azoogi-chat-header-brand">
                <div class="azoogi-chat-avatar">
                    A
                    <span class="azoogi-chat-status-dot"></span>
                </div>
                <div class="azoogi-chat-title-wrap">
                    <h3>Azoogi Lighting AI</h3>
                    <p>Architectural Specs, Products & Quotes</p>
                </div>
            </div>
            <div class="azoogi-chat-header-actions">
                <button type="button" class="azoogi-chat-header-btn" id="azoogi-chat-sound-toggle" title="Toggle audio chime" aria-label="Toggle sound">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" width="16" height="16">
                        <polygon points="11 5 6 9 2 9 2 15 6 15 11 19 11 5"/>
                        <path d="M15.54 8.46a5 5 0 0 1 0 7.07"/>
                    </svg>
                </button>
                <button type="button" class="azoogi-chat-header-btn" id="azoogi-chat-reset" title="New conversation" aria-label="Reset conversation">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" width="15" height="15">
                        <path d="M3 12a9 9 0 0 1 9-9 9.75 9.75 0 0 1 6.74 2.74L21 8"/>
                        <path d="M21 3v5h-5"/>
                        <path d="M21 12a9 9 0 0 1-9 9 9.75 9.75 0 0 1-6.74-2.74L3 16"/>
                        <path d="M3 21v-5h5"/>
                    </svg>
                </button>
                <button type="button" class="azoogi-chat-header-btn" id="azoogi-chat-close" title="Minimize chat" aria-label="Close chat">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16">
                        <line x1="18" y1="6" x2="6" y2="18"/>
                        <line x1="6" y1="6" x2="18" y2="18"/>
                    </svg>
                </button>
            </div>
        </div>

        <!-- Chat Body Scroll Area -->
        <div class="azoogi-chat-body" id="azoogi-chat-body">
            <div class="azoogi-chat-starters">
                <div class="azoogi-chat-starters-title">Welcome to Azoogi Lighting</div>
                <p style="font-size: 13px; color: var(--chat-text); margin: 0;">How can our engineering team assist with your project today?</p>
                <div class="azoogi-chat-chip-list" style="margin-top: 8px;">
                    <button type="button" class="azoogi-chat-chip js-chat-chip" data-prompt="Show me garden lights">🌿 Garden Lights</button>
                    <button type="button" class="azoogi-chat-chip js-chat-chip" data-prompt="I want to explore downlights with dimension Ø82mm x 80mm (H)">💡 80mm Downlights</button>
                    <button type="button" class="azoogi-chat-chip js-chat-chip" data-prompt="Show commercial linear profiles with DALI dimming">🏢 Linear & DALI Profiles</button>
                    <button type="button" class="azoogi-chat-chip js-chat-chip" data-prompt="How to add products to quote list?">📝 How to Add to Quote?</button>
                    <button type="button" class="azoogi-chat-chip js-chat-chip" data-prompt="Show my quote list">📋 View Quote Items</button>
                    <button type="button" class="azoogi-chat-chip js-chat-chip" data-prompt="How do I generate a custom PDF datasheet?">📄 Custom Datasheets</button>
                </div>
            </div>
        </div>

        <!-- Footer Input -->
        <div class="azoogi-chat-footer">
            <input type="text" id="azoogi-chat-input" class="azoogi-chat-input" placeholder="Ask about products, specs, or quotes..." autocomplete="off" maxlength="1000">
            <button type="button" id="azoogi-chat-send" class="azoogi-chat-send-btn" aria-label="Send message">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" width="16" height="16">
                    <line x1="22" y1="2" x2="11" y2="13"/>
                    <polygon points="22 2 15 22 11 13 2 9 22 2"/>
                </svg>
            </button>
        </div>
    </div>
</div>
