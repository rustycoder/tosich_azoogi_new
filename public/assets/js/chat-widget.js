/**
 * Azoogi AI Floating Chat Widget Client
 * Handles state persistence, interactive product cards, quote cart sync, and UI rendering.
 */
document.addEventListener('DOMContentLoaded', () => {
    const widget = document.getElementById('azoogi-chat-widget');
    if (!widget) return;

    const launcher = document.getElementById('azoogi-chat-launcher');
    const container = document.getElementById('azoogi-chat-container');
    const closeBtn = document.getElementById('azoogi-chat-close');
    const resetBtn = document.getElementById('azoogi-chat-reset');
    const soundToggle = document.getElementById('azoogi-chat-sound-toggle');
    const body = document.getElementById('azoogi-chat-body');
    const input = document.getElementById('azoogi-chat-input');
    const sendBtn = document.getElementById('azoogi-chat-send');
    const quoteBadge = document.getElementById('azoogi-chat-quote-badge');

    const STORAGE_UUID_KEY = 'azoogi_chat_session_uuid';
    const STORAGE_SOUND_KEY = 'azoogi_chat_sound_enabled';

    let sessionUuid = localStorage.getItem(STORAGE_UUID_KEY);
    let soundEnabled = localStorage.getItem(STORAGE_SOUND_KEY) !== 'false';
    let isProcessing = false;

    // Web Audio Chime synthesizer
    const playChime = () => {
        if (!soundEnabled) return;
        try {
            const ctx = new (window.AudioContext || window.webkitAudioContext)();
            const osc = ctx.createOscillator();
            const gain = ctx.createGain();
            osc.connect(gain);
            gain.connect(ctx.destination);
            osc.type = 'sine';
            osc.frequency.setValueAtTime(587.33, ctx.currentTime); // D5
            osc.frequency.exponentialRampToValueAtTime(880, ctx.currentTime + 0.12); // A5
            gain.gain.setValueAtTime(0.08, ctx.currentTime);
            gain.gain.exponentialRampToValueAtTime(0.001, ctx.currentTime + 0.35);
            osc.start();
            osc.stop(ctx.currentTime + 0.36);
        } catch (e) {
            // AudioContext not allowed before user gesture
        }
    };

    // Toggle Chat Window
    const toggleChat = (forceState = null) => {
        const isOpen = forceState !== null ? forceState : !widget.classList.contains('is-open');
        if (isOpen) {
            widget.classList.add('is-open');
            launcher.classList.remove('has-unread');
            setTimeout(() => input?.focus(), 200);
            scrollToBottom();
        } else {
            widget.classList.remove('is-open');
        }
    };

    launcher?.addEventListener('click', () => toggleChat());
    closeBtn?.addEventListener('click', () => toggleChat(false));

    // Sound toggle
    soundToggle?.addEventListener('click', () => {
        soundEnabled = !soundEnabled;
        localStorage.setItem(STORAGE_SOUND_KEY, soundEnabled);
        soundToggle.style.opacity = soundEnabled ? '1' : '0.4';
    });

    // Reset Chat
    resetBtn?.addEventListener('click', async () => {
        if (!confirm('Start a fresh conversation?')) return;
        if (sessionUuid) {
            try {
                await fetch('/api/chat/clear', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': getCsrfToken() },
                    body: JSON.stringify({ session_uuid: sessionUuid })
                });
            } catch (e) {}
        }
        localStorage.removeItem(STORAGE_UUID_KEY);
        sessionUuid = null;
        body.innerHTML = getWelcomeTemplate();
        initChipListeners();
    });

    const scrollToBottom = () => {
        if (body) {
            body.scrollTop = body.scrollHeight;
        }
    };

    const getCsrfToken = () => {
        return document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
    };

    // Render User Message
    const appendUserMessage = (text) => {
        const time = new Date().toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
        const el = document.createElement('div');
        el.className = 'azoogi-chat-msg is-user';
        el.innerHTML = `
            <div class="azoogi-chat-bubble">${escapeHtml(text)}</div>
            <div class="azoogi-chat-msg-time">${time}</div>
        `;
        body.appendChild(el);
        scrollToBottom();
    };

    // Render Assistant Message with Cards
    const appendAssistantMessage = (text, cards = []) => {
        const time = new Date().toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
        const el = document.createElement('div');
        el.className = 'azoogi-chat-msg is-assistant';

        let cardsHtml = '';
        if (Array.isArray(cards)) {
            cards.forEach(card => {
                if (card.type === 'products_carousel' && Array.isArray(card.items)) {
                    cardsHtml += renderProductsCarousel(card.items);
                } else if (card.type === 'datasheet_download_card') {
                    cardsHtml += renderDatasheetCard(card.data);
                } else if (card.type === 'quote_cart_card') {
                    cardsHtml += renderQuoteCard(card.items, card.quote_url);
                } else if (card.type === 'product_detail_card') {
                    cardsHtml += renderProductDetailCard(card.data);
                } else if (card.type === 'lead_confirmation_card') {
                    cardsHtml += renderLeadConfirmationCard(card);
                }
            });
        }

        el.innerHTML = `
            <div class="azoogi-chat-bubble">${formatMarkdown(text)}</div>
            ${cardsHtml}
            <div class="azoogi-chat-msg-time">Azoogi AI &bull; ${time}</div>
        `;

        body.appendChild(el);
        attachCardListeners(el);
        scrollToBottom();
        playChime();
    };

    // Product Carousel Card Template (Matches Website Catalog .prod-card)
    const renderProductsCarousel = (products) => {
        if (!products.length) return '';
        const items = products.map(p => {
            const isFallback = !p.image_url || p.image_url.includes('placeholder') || p.image_url.includes('default');
            const imgHtml = `<img src="${p.image_url || '/assets/quote.webp'}" alt="${escapeHtml(p.name)}" class="prod-swatch${isFallback ? ' is-fallback' : ''}" loading="lazy" onerror="this.onerror=null; this.src='/assets/quote.webp';">`;
            const catLabel = p.category ? `<span class="cat-label">${escapeHtml(p.category)}</span>` : '';
            const codeLabel = p.code ? `<span class="prod-card-code">${escapeHtml(p.code)}</span>` : '';
            const detailUrl = p.url || `/products/${encodeURIComponent(p.slug || p.id)}`;

            return `
                <div class="prod-card" data-href="${detailUrl}" role="link" tabindex="0">
                    <div class="prod-card-img">
                        ${imgHtml}
                    </div>
                    <div class="prod-card-title">
                        <div class="prod-card-title-text">
                            ${catLabel}
                            <span class="prod-card-name">${escapeHtml(p.name)}</span>
                            ${codeLabel}
                        </div>
                        <button class="add-quote-btn js-add-quote" type="button" aria-label="Add to quote" data-id="${p.id}" data-name="${escapeHtml(p.name)}" data-sku="${escapeHtml(p.code || '')}" data-image="${escapeHtml(p.image_url || '')}" data-url="${escapeHtml(detailUrl)}" onclick="event.stopPropagation();">+</button>
                    </div>
                </div>
            `;
        }).join('');

        return `<div class="azoogi-chat-products-carousel">${items}</div>`;
    };

    // Custom Datasheet Download Card Template
    const renderDatasheetCard = (data) => {
        return `
            <div class="azoogi-chat-download-card">
                <div class="azoogi-chat-download-icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="20" height="20"><path d="M14 3H7a1 1 0 0 0-1 1v16a1 1 0 0 0 1 1h10a1 1 0 0 0 1-1V8z"/><polyline points="14,3 14,8 19,8"/></svg>
                </div>
                <div class="azoogi-chat-download-meta">
                    <div class="azoogi-chat-download-title">${data.product_name}</div>
                    <div class="azoogi-chat-download-sub">Custom PDF Specification Sheet</div>
                </div>
                <a href="${data.download_url}" target="_blank" class="azoogi-chat-btn-sm azoogi-chat-btn-primary" style="padding: 8px 12px; text-decoration: none;">
                    Download PDF
                </a>
            </div>
        `;
    };

    // Quote Cart Card Template
    const renderQuoteCard = (items, quoteUrl) => {
        const listHtml = items.map(it => `
            <div style="display: flex; justify-content: space-between; font-size: 11.5px; padding: 4px 0; border-bottom: 1px solid var(--chat-border);">
                <span><strong>${it.quantity}x</strong> ${it.name}</span>
                <span style="color: var(--chat-accent); font-family: monospace;">${it.code || ''}</span>
            </div>
        `).join('');

        return `
            <div class="azoogi-chat-starters" style="margin-top: 8px;">
                <div class="azoogi-chat-starters-title">Active Quote Items (${items.length})</div>
                <div style="margin: 6px 0;">${listHtml || '<p style="font-size: 11px; color: var(--chat-muted);">Your quote cart is empty.</p>'}</div>
                <a href="${quoteUrl}" class="azoogi-chat-btn-sm azoogi-chat-btn-primary" style="text-decoration: none; padding: 8px 12px; margin-top: 6px; display: block; text-align: center;">
                    Proceed to Official Quote Request &rarr;
                </a>
            </div>
        `;
    };

    // Product Detail Card Template
    const renderProductDetailCard = (p) => {
        const downloadsHtml = (p.downloads || []).map(d => `
            <a href="${d.url}" target="_blank" class="azoogi-chat-btn-sm azoogi-chat-btn-outline" style="text-decoration: none; margin-top: 4px; display: inline-flex; align-items: center; gap: 4px;">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="13" height="13"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
                ${d.type}
            </a>
        `).join(' ');

        return `
            <div class="azoogi-chat-starters" style="margin-top: 8px;">
                <div style="font-weight: 600; font-size: 13px; color: var(--chat-text);">${p.name} (${p.code || ''})</div>
                <p style="font-size: 12px; color: var(--chat-muted); margin: 6px 0;">${p.description || ''}</p>
                <div style="font-size: 11.5px; color: var(--chat-accent); margin-bottom: 6px;"><strong>Control:</strong> ${p.dimming}</div>
                <div style="display: flex; flex-wrap: wrap; gap: 6px;">
                    ${downloadsHtml}
                </div>
            </div>
        `;
    };

    // Lead Confirmation Card Template
    const renderLeadConfirmationCard = (lead) => {
        return `
            <div class="azoogi-chat-download-card" style="border-color: #10b981;">
                <div class="azoogi-chat-download-icon" style="background: rgba(16, 185, 129, 0.15); color: #10b981;">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="20" height="20"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
                </div>
                <div class="azoogi-chat-download-meta">
                    <div class="azoogi-chat-download-title">Quote Inquiry #${lead.enquiry_id} Submitted!</div>
                    <div class="azoogi-chat-download-sub">Confirmation sent to ${lead.email}</div>
                </div>
            </div>
        `;
    };

    // Card Action Listeners (Clicking card navigates to product, clicking '+' adds to quote)
    const attachCardListeners = (containerEl) => {
        containerEl.querySelectorAll('.prod-card[data-href]').forEach(card => {
            card.addEventListener('click', (e) => {
                if (e.target.closest('.js-add-quote') || e.target.closest('.add-quote-btn')) return;
                const href = card.getAttribute('data-href');
                if (href) {
                    window.open(href, '_blank');
                }
            });
        });

        containerEl.querySelectorAll('.js-add-quote').forEach(btn => {
            btn.addEventListener('click', async (e) => {
                e.stopPropagation();
                const id = btn.getAttribute('data-id');
                const name = btn.getAttribute('data-name');
                btn.disabled = true;
                btn.textContent = '...';

                try {
                    const res = await fetch('/api/chat/quote/add', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': getCsrfToken() },
                        body: JSON.stringify({ product_id: id, quantity: 1 })
                    });
                    const data = await res.json();
                    if (data.status === 'success') {
                        btn.textContent = '✓';
                        btn.classList.add('added');
                        if (quoteBadge) {
                            quoteBadge.textContent = data.quote_count;
                            quoteBadge.style.display = data.quote_count > 0 ? 'block' : 'none';
                        }
                        if (window.AzoogiQuote && typeof window.AzoogiQuote.refresh === 'function') {
                            window.AzoogiQuote.refresh();
                        }
                    } else {
                        btn.textContent = '+';
                        btn.disabled = false;
                    }
                } catch (err) {
                    btn.textContent = '+';
                    btn.disabled = false;
                }
            });
        });
    };

    // Typing indicator
    const showTypingIndicator = () => {
        const el = document.createElement('div');
        el.className = 'azoogi-chat-typing';
        el.id = 'azoogi-chat-typing';
        el.innerHTML = `
            <div class="azoogi-chat-typing-dot"></div>
            <div class="azoogi-chat-typing-dot"></div>
            <div class="azoogi-chat-typing-dot"></div>
        `;
        body.appendChild(el);
        scrollToBottom();
    };

    const removeTypingIndicator = () => {
        document.getElementById('azoogi-chat-typing')?.remove();
    };

    // Send Message Form
    const sendMessage = async (customText = null) => {
        const text = customText || input.value.trim();
        if (!text || isProcessing) return;

        if (!customText) input.value = '';
        isProcessing = true;
        sendBtn.disabled = true;

        appendUserMessage(text);
        showTypingIndicator();

        try {
            const response = await fetch('/api/chat/message', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': getCsrfToken()
                },
                body: JSON.stringify({
                    message: text,
                    session_uuid: sessionUuid,
                    referrer_url: window.location.href
                })
            });

            const data = await response.json();
            removeTypingIndicator();

            if (data.status === 'success') {
                if (data.session_uuid) {
                    sessionUuid = data.session_uuid;
                    localStorage.setItem(STORAGE_UUID_KEY, sessionUuid);
                }
                if (quoteBadge && typeof data.quote_count === 'number') {
                    quoteBadge.textContent = data.quote_count;
                    quoteBadge.style.display = data.quote_count > 0 ? 'block' : 'none';
                }
                appendAssistantMessage(data.reply, data.cards);
            } else {
                appendAssistantMessage('Sorry, I encountered an error processing your request. Please try again.');
            }
        } catch (e) {
            removeTypingIndicator();
            appendAssistantMessage('Unable to reach the server. Please check your network connection.');
        } finally {
            isProcessing = false;
            sendBtn.disabled = false;
            input.focus();
        }
    };

    sendBtn?.addEventListener('click', () => sendMessage());
    input?.addEventListener('keydown', (e) => {
        if (e.key === 'Enter' && !e.shiftKey) {
            e.preventDefault();
            sendMessage();
        }
    });

    // Quick chip starter listeners
    const initChipListeners = () => {
        document.querySelectorAll('.js-chat-chip').forEach(chip => {
            chip.addEventListener('click', () => {
                const prompt = chip.getAttribute('data-prompt') || chip.textContent.trim();
                sendMessage(prompt);
            });
        });
    };

    initChipListeners();

    // Welcome Template
    function getWelcomeTemplate() {
        return `
            <div class="azoogi-chat-starters">
                <div class="azoogi-chat-starters-title">Welcome to Azoogi Lighting</div>
                <p style="font-size: 13px; color: var(--chat-text); margin: 0;">How can our engineering team assist with your project today?</p>
                <div class="azoogi-chat-chip-list" style="margin-top: 8px;">
                    <button type="button" class="azoogi-chat-chip js-chat-chip" data-prompt="Show me outdoor IP66 lighting fixtures">🌿 Outdoor IP66 Lighting</button>
                    <button type="button" class="azoogi-chat-chip js-chat-chip" data-prompt="Show commercial linear profiles with DALI dimming">🏢 Commercial Linear & DALI</button>
                    <button type="button" class="azoogi-chat-chip js-chat-chip" data-prompt="How do I generate a custom PDF datasheet?">📄 Custom Datasheets</button>
                    <button type="button" class="azoogi-chat-chip js-chat-chip" data-prompt="I want to request a project quote">📋 Build a Quote Request</button>
                </div>
            </div>
        `;
    }

    function escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    function formatMarkdown(text) {
        if (!text) return '';
        return text
            .replace(/\*\*(.*?)\*\*/g, '<strong>$1</strong>')
            .replace(/\*(.*?)\*/g, '<em>$1</em>')
            .replace(/`([^`]+)`/g, '<code>$1</code>')
            .replace(/\n/g, '<br>');
    }
});
