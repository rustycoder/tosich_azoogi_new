/**
 * Azoogi AI Floating Chat Widget Client
 * Handles state persistence across page navigation, interactive product cards, quote cart sync, and UI rendering.
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
    const STORAGE_OPEN_KEY = 'azoogi_chat_is_open';
    const STORAGE_MESSAGES_KEY = 'azoogi_chat_messages_cache';
    const STORAGE_LEAD_KEY = 'azoogi_chat_lead_info';

    let sessionUuid = localStorage.getItem(STORAGE_UUID_KEY);
    let soundEnabled = localStorage.getItem(STORAGE_SOUND_KEY) !== 'false';
    let isProcessing = false;

    const getStoredLead = () => {
        try {
            const raw = localStorage.getItem(STORAGE_LEAD_KEY);
            return raw ? JSON.parse(raw) : null;
        } catch (e) {
            return null;
        }
    };

    const saveStoredLead = (leadObj) => {
        try {
            localStorage.setItem(STORAGE_LEAD_KEY, JSON.stringify(leadObj));
        } catch (e) {}
    };

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

    // Toggle Chat Window & Persist Open State across pages
    const toggleChat = (forceState = null) => {
        const isOpen = forceState !== null ? forceState : !widget.classList.contains('is-open');
        if (isOpen) {
            widget.classList.add('is-open');
            launcher?.classList.remove('has-unread');
            localStorage.setItem(STORAGE_OPEN_KEY, 'true');
            setTimeout(() => {
                const intakeNameInput = document.getElementById('azoogi-intake-name');
                if (intakeNameInput) {
                    intakeNameInput.focus();
                } else {
                    input?.focus();
                }
            }, 200);
            scrollToBottom();
        } else {
            widget.classList.remove('is-open');
            localStorage.setItem(STORAGE_OPEN_KEY, 'false');
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
        localStorage.removeItem(STORAGE_MESSAGES_KEY);
        sessionUuid = null;
        renderInitialScreen();
    });

    const scrollToBottom = () => {
        if (body) {
            body.scrollTop = body.scrollHeight;
        }
    };

    const getCsrfToken = () => {
        return document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
    };

    // Message History Cache Helpers
    const getCachedMessages = () => {
        try {
            const raw = localStorage.getItem(STORAGE_MESSAGES_KEY);
            return raw ? JSON.parse(raw) : [];
        } catch (e) {
            return [];
        }
    };

    const saveMessageToCache = (msgObj) => {
        try {
            const msgs = getCachedMessages();
            msgs.push(msgObj);
            localStorage.setItem(STORAGE_MESSAGES_KEY, JSON.stringify(msgs));
        } catch (e) {}
    };

    // Render User Message
    const appendUserMessage = (text, timeStr = null, saveToCache = true) => {
        const time = timeStr || new Date().toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
        const el = document.createElement('div');
        el.className = 'azoogi-chat-msg is-user';
        el.innerHTML = `
            <div class="azoogi-chat-bubble">${escapeHtml(text)}</div>
            <div class="azoogi-chat-msg-time">${time}</div>
        `;
        body.appendChild(el);
        scrollToBottom();

        if (saveToCache) {
            saveMessageToCache({ sender: 'user', content: text, time });
        }
    };

    // Render Assistant Message with Cards
    const appendAssistantMessage = (text, cards = [], timeStr = null, saveToCache = true) => {
        const time = timeStr || new Date().toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
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

        if (saveToCache) {
            saveMessageToCache({ sender: 'assistant', content: text, cards, time });
            playChime();
        }
    };

    // Product Carousel Card Template (Matches Website Catalog .prod-card)
    const renderProductsCarousel = (products) => {
        if (!products.length) return '';
        const items = products.map(p => {
            const isFallback = !p.image_url || p.image_url.includes('placeholder') || p.image_url.includes('default');
            const imgHtml = `<img src="${p.image_url || '/assets/quote.webp'}" alt="${escapeHtml(p.name)}" class="prod-swatch${isFallback ? ' is-fallback' : ''}" loading="lazy" onerror="this.onerror=null; this.src='/assets/quote.webp';">`;
            const catLabel = p.category ? `<span class="cat-label">${escapeHtml(p.category)}</span>` : '';
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
        const liveItems = (window.AzoogiQuote && typeof window.AzoogiQuote.items === 'function')
            ? window.AzoogiQuote.items()
            : (items || []);

        const count = liveItems.length;
        const listHtml = liveItems.map(it => `
            <div style="display: flex; justify-content: space-between; align-items: center; font-size: 11.5px; padding: 5px 0; border-bottom: 1px solid var(--chat-border);">
                <span><strong>${it.qty || it.quantity || 1}x</strong> ${escapeHtml(it.name)}</span>
                <span style="color: var(--chat-accent); font-family: monospace; font-size: 10.5px;">${escapeHtml(it.sku || it.code || '')}</span>
            </div>
        `).join('');

        return `
            <div class="azoogi-chat-starters" style="margin-top: 8px;">
                <div class="azoogi-chat-starters-title">Project Quote Request List (${count})</div>
                <div style="margin: 6px 0;">${listHtml || '<p style="font-size: 11px; color: var(--chat-muted); margin: 4px 0;">Your quote list is currently empty.</p>'}</div>
                <div style="display: flex; gap: 6px; margin-top: 8px;">
                    <button type="button" class="azoogi-chat-btn-sm azoogi-chat-btn-outline" style="flex: 1; padding: 7px 10px; cursor: pointer;" onclick="if (window.AzoogiQuote && typeof window.AzoogiQuote.open === 'function') window.AzoogiQuote.open();">
                        Open Quote Drawer
                    </button>
                    <a href="${quoteUrl}" class="azoogi-chat-btn-sm azoogi-chat-btn-primary" style="flex: 1; text-decoration: none; padding: 7px 10px; text-align: center;">
                        Submit Quote &rarr;
                    </a>
                </div>
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
        const typeTitles = {
            'contact': 'Contact Message Sent!',
            'product': 'Product Enquiry Submitted!',
            'quote': 'Quote Request Submitted!'
        };
        const title = typeTitles[lead.enquiry_type] || `Enquiry #${lead.enquiry_id} Submitted!`;
        const sub = lead.enquiry_type === 'contact'
            ? `Thank you ${escapeHtml(lead.name || '')}! Our engineering team will reply to ${escapeHtml(lead.email || '')}.`
            : `Ref #${lead.enquiry_id} &bull; Confirmation sent to ${escapeHtml(lead.email || '')}`;

        return `
            <div class="azoogi-chat-download-card" style="border-color: #10b981;">
                <div class="azoogi-chat-download-icon" style="background: rgba(16, 185, 129, 0.15); color: #10b981;">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="20" height="20"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
                </div>
                <div class="azoogi-chat-download-meta">
                    <div class="azoogi-chat-download-title">${title}</div>
                    <div class="azoogi-chat-download-sub">${sub}</div>
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
                const sku = btn.getAttribute('data-sku') || '';
                const image = btn.getAttribute('data-image') || '';
                const url = btn.getAttribute('data-url') || '';
                btn.disabled = true;
                btn.textContent = '...';

                // Synchronize with website quote drawer / storage
                if (window.AzoogiQuote && typeof window.AzoogiQuote.add === 'function') {
                    window.AzoogiQuote.add({
                        id: id || sku || name,
                        name: name,
                        sku: sku,
                        image: image,
                        url: url
                    });
                }

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
                        if (window.siteToast && typeof window.siteToast === 'function') {
                            window.siteToast(`Added "${name}" to your quote request.`);
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

        appendUserMessage(text, null, true);
        showTypingIndicator();

        const clientQuoteItems = (window.AzoogiQuote && typeof window.AzoogiQuote.items === 'function')
            ? window.AzoogiQuote.items()
            : [];

        const leadInfo = getStoredLead() || {};

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
                    referrer_url: window.location.href,
                    name: leadInfo.name || null,
                    email: leadInfo.email || null,
                    project_name: leadInfo.project_name || null,
                    quote_items: clientQuoteItems
                })
            });

            const data = await response.json();
            removeTypingIndicator();

            if (data.status === 'success') {
                if (data.session_uuid) {
                    sessionUuid = data.session_uuid;
                    localStorage.setItem(STORAGE_UUID_KEY, sessionUuid);
                }
                if (data.quote_items && window.AzoogiQuote && typeof window.AzoogiQuote.sync === 'function') {
                    window.AzoogiQuote.sync(data.quote_items);
                }
                appendAssistantMessage(data.reply, data.cards, null, true);
            } else {
                appendAssistantMessage('Sorry, I encountered an error processing your request. Please try again.', [], null, true);
            }
        } catch (e) {
            removeTypingIndicator();
            appendAssistantMessage('Unable to reach the server. Please check your network connection.', [], null, true);
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

    // Render Pre-Chat Intake Form
    function renderIntakeForm() {
        const storedLead = getStoredLead() || {};
        body.innerHTML = `
            <div class="azoogi-chat-intake-card" id="azoogi-chat-intake-card">
                <div class="azoogi-chat-intake-badge">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" style="width: 14px; height: 14px;"><circle cx="12" cy="12" r="10"/><path d="m9 12 2 2 4-4"/></svg>
                    Project Consultation
                </div>
                <h4 class="azoogi-chat-intake-title">Welcome to Azoogi AI</h4>
                <p class="azoogi-chat-intake-desc">Please share a few details so our engineering team and AI can tailor fixture specs and lighting schedules for your project.</p>

                <form id="azoogi-chat-intake-form">
                    <div class="azoogi-chat-intake-field">
                        <label for="azoogi-intake-name">Your Full Name <span class="req">*</span></label>
                        <input type="text" id="azoogi-intake-name" class="azoogi-chat-intake-input" placeholder="e.g. Jane Doe" value="${escapeHtml(storedLead.name || '')}" required maxlength="100">
                    </div>

                    <div class="azoogi-chat-intake-field">
                        <label for="azoogi-intake-email">Work Email Address <span class="req">*</span></label>
                        <input type="email" id="azoogi-intake-email" class="azoogi-chat-intake-input" placeholder="e.g. jane@architecture.com.au" value="${escapeHtml(storedLead.email || '')}" required maxlength="120">
                    </div>

                    <div class="azoogi-chat-intake-field">
                        <label for="azoogi-intake-project">Project Name / Reference</label>
                        <input type="text" id="azoogi-intake-project" class="azoogi-chat-intake-input" placeholder="e.g. Bondi Beach Apartment Fitout" value="${escapeHtml(storedLead.project_name || '')}" maxlength="150">
                    </div>

                    <button type="submit" class="azoogi-chat-intake-submit" id="azoogi-intake-submit-btn">
                        <span>Start Consultation</span>
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" style="width: 15px; height: 15px;"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg>
                    </button>

                    <button type="button" class="azoogi-chat-intake-skip" id="azoogi-intake-skip-btn">
                        Skip &amp; ask question anonymously &rarr;
                    </button>
                </form>
            </div>
        `;

        const form = document.getElementById('azoogi-chat-intake-form');
        const skipBtn = document.getElementById('azoogi-intake-skip-btn');

        form?.addEventListener('submit', async (e) => {
            e.preventDefault();
            const name = document.getElementById('azoogi-intake-name')?.value.trim();
            const email = document.getElementById('azoogi-intake-email')?.value.trim();
            const projectName = document.getElementById('azoogi-intake-project')?.value.trim();

            if (!name || !email) {
                alert('Please enter your name and email address.');
                return;
            }

            const submitBtn = document.getElementById('azoogi-intake-submit-btn');
            if (submitBtn) {
                submitBtn.disabled = true;
                submitBtn.innerHTML = 'Connecting...';
            }

            const leadObj = { name, email, project_name: projectName };
            saveStoredLead(leadObj);

            try {
                const res = await fetch('/api/chat/init', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': getCsrfToken()
                    },
                    body: JSON.stringify({
                        session_uuid: sessionUuid,
                        name,
                        email,
                        project_name: projectName,
                        referrer_url: window.location.href
                    })
                });

                const data = await res.json();
                if (data.session_uuid) {
                    sessionUuid = data.session_uuid;
                    localStorage.setItem(STORAGE_UUID_KEY, sessionUuid);
                }

                // Render customized welcome screen
                renderWelcomeWithGreeting(data.greeting || `Hi ${name}! How can our team assist with ${projectName || 'your project'} today?`);
            } catch (err) {
                renderWelcomeWithGreeting(`Hi ${name}! Welcome to Azoogi. How can our team assist with your lighting project today?`);
            }
        });

        skipBtn?.addEventListener('click', () => {
            renderWelcomeWithGreeting(liveBranding.startup_message || 'Welcome to Azoogi Lighting! How can our engineering team assist with your project specifications today?');
        });
    }

    let liveBranding = {
        ai_name: 'Azoogi AI Assistant',
        ai_avatar: 'spark',
        ai_custom_avatar_url: null,
        ai_subtitle: 'Architectural & Smart Controls Specialist',
        startup_message: 'Welcome to Azoogi Lighting! How can our architectural engineering team assist with your project specifications, lighting schedules, or quotes today?',
        starter_chips: [
            { icon: '🌿', label: 'Garden Lights', prompt: 'Show me outdoor garden lights' },
            { icon: '💡', label: '80mm Downlights', prompt: 'I want to explore downlights with dimension Ø82mm x 80mm (H)' },
            { icon: '🏢', label: 'Linear & DALI Profiles', prompt: 'Show commercial linear profiles with DALI dimming' },
            { icon: '📝', label: 'How to Add to Quote?', prompt: 'How to add products to quote list?' },
            { icon: '📋', label: 'View Quote Items', prompt: 'Show my quote list' },
            { icon: '📄', label: 'Custom Datasheets', prompt: 'How do I generate a custom PDF datasheet?' }
        ]
    };

    function applyBranding(branding) {
        if (!branding) return;
        liveBranding = Object.assign({}, liveBranding, branding);

        const titleEl = document.querySelector('.js-chat-header-title');
        if (titleEl && liveBranding.ai_name) {
            titleEl.textContent = liveBranding.ai_name;
        }

        const subEl = document.querySelector('.js-chat-header-subtitle');
        if (subEl && liveBranding.ai_subtitle) {
            subEl.textContent = liveBranding.ai_subtitle;
        }

        const launcherText = document.querySelector('.azoogi-chat-launcher-text');
        if (launcherText && liveBranding.ai_name) {
            launcherText.textContent = `Ask ${liveBranding.ai_name}`;
        }

        const avatarEl = document.querySelector('.js-chat-avatar');
        if (avatarEl) {
            const avatarMap = { spark: '⚡', lightbulb: '💡', leaf: '🌿', building: '🏢', robot: '🤖' };
            if (liveBranding.ai_avatar === 'custom' && liveBranding.ai_custom_avatar_url) {
                avatarEl.innerHTML = `<img src="${liveBranding.ai_custom_avatar_url}" alt="${liveBranding.ai_name}" style="width: 100%; height: 100%; object-fit: cover; border-radius: 50%;"><span class="azoogi-chat-status-dot"></span>`;
            } else {
                const char = avatarMap[liveBranding.ai_avatar] || '⚡';
                avatarEl.innerHTML = `${char}<span class="azoogi-chat-status-dot"></span>`;
            }
        }
    }

    function renderWelcomeWithGreeting(greetingText) {
        const chipsHtml = (liveBranding.starter_chips || []).map(chip => {
            return `<button type="button" class="azoogi-chat-chip js-chat-chip" data-prompt="${escapeHtml(chip.prompt)}">${escapeHtml(chip.icon || '💡')} ${escapeHtml(chip.label)}</button>`;
        }).join('');

        body.innerHTML = `
            <div class="azoogi-chat-starters">
                <div class="azoogi-chat-starters-title">Welcome to Azoogi Lighting</div>
                <p style="font-size: 13px; color: var(--chat-text); margin: 0 0 12px 0; line-height: 1.5;">${escapeHtml(greetingText)}</p>
                <div class="azoogi-chat-chip-list">
                    ${chipsHtml}
                </div>
            </div>
        `;
        initChipListeners();
        input?.focus();
    }

    function renderInitialScreen() {
        const storedLead = getStoredLead();
        if (storedLead && (storedLead.name || storedLead.email)) {
            const greeting = (storedLead.name && storedLead.project_name)
                ? `Hi ${storedLead.name}! Welcome back. How can our team assist with ${storedLead.project_name} today?`
                : (storedLead.name ? `Hi ${storedLead.name}! Welcome back to Azoogi Lighting.` : liveBranding.startup_message);
            renderWelcomeWithGreeting(greeting);
        } else {
            renderIntakeForm();
        }
    }

    // Restore Session History on Page Load (Persist across navigation)
    const restoreSessionHistory = async () => {
        // 1. First, render instantly from local cache if available (zero flicker)
        const cached = getCachedMessages();
        if (cached && cached.length > 0) {
            body.innerHTML = '';
            cached.forEach(m => {
                if (m.sender === 'user') {
                    appendUserMessage(m.content, m.time, false);
                } else {
                    appendAssistantMessage(m.content, m.cards || [], m.time, false);
                }
            });
        } else {
            renderInitialScreen();
        }

        // 2. Fetch fresh session & branding from server if UUID exists
        if (sessionUuid) {
            try {
                const res = await fetch(`/api/chat/session/${sessionUuid}`);
                const data = await res.json();
                if (data.branding) {
                    applyBranding(data.branding);
                }
                if (data.status === 'success' && Array.isArray(data.messages) && data.messages.length > 0) {
                    body.innerHTML = '';
                    const updatedCache = [];

                    data.messages.forEach(m => {
                        const time = m.created_at ? new Date(m.created_at).toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' }) : '';
                        if (m.sender === 'user') {
                            appendUserMessage(m.content, time, false);
                            updatedCache.push({ sender: 'user', content: m.content, time });
                        } else {
                            appendAssistantMessage(m.content, m.cards || [], time, false);
                            updatedCache.push({ sender: 'assistant', content: m.content, cards: m.cards, time });
                        }
                    });

                    localStorage.setItem(STORAGE_MESSAGES_KEY, JSON.stringify(updatedCache));

                    if (quoteBadge && typeof data.quote_count === 'number') {
                        quoteBadge.textContent = data.quote_count;
                        quoteBadge.style.display = data.quote_count > 0 ? 'block' : 'none';
                    }
                }
            } catch (err) {
                // Fallback to cache already rendered
            }
        }

        // 3. Check if chat was open prior to navigating
        const wasOpen = localStorage.getItem(STORAGE_OPEN_KEY) === 'true';
        if (wasOpen) {
            toggleChat(true);
        }
    };

    // Initialize session restoration on page load
    restoreSessionHistory();

    // Welcome Template
    function getWelcomeTemplate() {
        const chipsHtml = (liveBranding.starter_chips || []).map(chip => {
            return `<button type="button" class="azoogi-chat-chip js-chat-chip" data-prompt="${escapeHtml(chip.prompt)}">${escapeHtml(chip.icon || '💡')} ${escapeHtml(chip.label)}</button>`;
        }).join('');

        return `
            <div class="azoogi-chat-starters">
                <div class="azoogi-chat-starters-title">Welcome to Azoogi Lighting</div>
                <p style="font-size: 13px; color: var(--chat-text); margin: 0;">${escapeHtml(liveBranding.startup_message)}</p>
                <div class="azoogi-chat-chip-list" style="margin-top: 8px;">
                    ${chipsHtml}
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

        // 1. Normalize line endings
        let str = text.replace(/\r\n/g, '\n').trim();

        // 2. Escape raw HTML entities to prevent XSS
        str = str.replace(/&/g, '&amp;')
                 .replace(/</g, '&lt;')
                 .replace(/>/g, '&gt;');

        // 3. Code blocks (```code```)
        str = str.replace(/```([\s\S]*?)```/g, (match, p1) => {
            return `<pre class="azoogi-chat-codeblock"><code>${p1.trim()}</code></pre>`;
        });

        // 4. Inline code (`code`)
        str = str.replace(/`([^`]+)`/g, '<code class="azoogi-chat-inline-code">$1</code>');

        // 5. Headings (### Level 3, ## Level 2, # Level 1)
        str = str.replace(/^###[ \t]+(.*)$/gm, '<h4 class="azoogi-chat-heading">$1</h4>');
        str = str.replace(/^##[ \t]+(.*)$/gm, '<h3 class="azoogi-chat-heading">$1</h3>');
        str = str.replace(/^#[ \t]+(.*)$/gm, '<h2 class="azoogi-chat-heading">$1</h2>');

        // 6. Bold & Strong (**text** or __text__)
        str = str.replace(/\*\*([^*]+)\*\*/g, '<strong class="azoogi-chat-bold">$1</strong>');
        str = str.replace(/__([^_]+)__/g, '<strong class="azoogi-chat-bold">$1</strong>');

        // 7. Italics (*text* or _text_)
        str = str.replace(/(?<!\*)\*([^*]+)\*(?!\*)/g, '<em>$1</em>');
        str = str.replace(/(?<!_)_([^_]+)_(?!_)/g, '<em>$1</em>');

        // 8. Markdown Links [text](url)
        str = str.replace(/\[([^\]]+)\]\((https?:\/\/[^\s)]+)\)/g, '<a href="$2" target="_blank" rel="noopener noreferrer" class="azoogi-chat-link">$1</a>');

        // 9. Process line by line for structured lists & paragraphs
        const lines = str.split('\n');
        let html = '';
        let inUl = false;
        let inOl = false;

        for (let i = 0; i < lines.length; i++) {
            const line = lines[i].trim();

            if (!line) {
                if (inUl) { html += '</ul>'; inUl = false; }
                if (inOl) { html += '</ol>'; inOl = false; }
                continue;
            }

            // Bullet list item: - item or * item or • item
            const ulMatch = line.match(/^[-*•]\s+(.*)$/);
            // Numbered list item: 1. item
            const olMatch = line.match(/^(\d+)\.\s+(.*)$/);

            if (ulMatch) {
                if (inOl) { html += '</ol>'; inOl = false; }
                if (!inUl) { html += '<ul class="azoogi-chat-list">'; inUl = true; }
                html += `<li>${ulMatch[1]}</li>`;
            } else if (olMatch) {
                if (inUl) { html += '</ul>'; inUl = false; }
                if (!inOl) { html += '<ol class="azoogi-chat-list">'; inOl = true; }
                html += `<li>${olMatch[2]}</li>`;
            } else {
                if (inUl) { html += '</ul>'; inUl = false; }
                if (inOl) { html += '</ol>'; inOl = false; }

                if (line.startsWith('<h') || line.startsWith('<pre')) {
                    html += line;
                } else {
                    html += `<p class="azoogi-chat-p">${line}</p>`;
                }
            }
        }

        if (inUl) html += '</ul>';
        if (inOl) html += '</ol>';

        return html;
    }
});
