/**
 * Azoogi Dashboard Admin Copilot Widget (MCP-Powered)
 */
document.addEventListener('DOMContentLoaded', () => {
    const widget = document.getElementById('admin-copilot-widget');
    if (!widget) return;

    const launcher = document.getElementById('admin-copilot-launcher');
    const closeBtn = document.getElementById('admin-copilot-close');
    const resetBtn = document.getElementById('admin-copilot-reset');
    const body = document.getElementById('admin-copilot-body');
    const input = document.getElementById('admin-copilot-input');
    const sendBtn = document.getElementById('admin-copilot-send');

    const initUrl = widget.dataset.initUrl || '/dashboard/ai-copilot/init';
    const messageUrl = widget.dataset.messageUrl || '/dashboard/ai-copilot/message';
    const clearUrl = widget.dataset.clearUrl || '/dashboard/ai-copilot/clear';

    const STORAGE_OPEN_KEY = 'azoogi_admin_copilot_is_open';
    let isProcessing = false;
    let isInitialized = false;

    const getCsrfToken = () => {
        return document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
    };

    const scrollToBottom = () => {
        if (body) {
            body.scrollTop = body.scrollHeight;
        }
    };

    const escapeHtml = (str) => {
        const div = document.createElement('div');
        div.textContent = str ?? '';
        return div.innerHTML;
    };

    const formatMarkdown = (text) => {
        let safe = escapeHtml(text || '');
        safe = safe.replace(/\*\*(.+?)\*\*/g, '<strong>$1</strong>');
        safe = safe.replace(/`([^`]+)`/g, '<code>$1</code>');
        safe = safe.replace(/\n/g, '<br>');
        return safe;
    };

    const renderMcpCard = (card) => {
        if (!card || card.type !== 'mcp_tool_execution') return '';
        const statusClass = card.is_error ? 'is-error' : 'is-success';
        const toolName = escapeHtml(card.tool || 'mcp_tool');
        const summary = escapeHtml(card.summary || 'Executed MCP Tool');
        const payloadJson = escapeHtml(JSON.stringify(card.payload || {}, null, 2));

        return `
            <div class="dash-copilot-mcp-card ${statusClass}">
                <div class="dash-copilot-mcp-head">
                    <span class="dash-copilot-mcp-badge">${toolName}</span>
                    <span class="dash-copilot-mcp-summary">${summary}</span>
                </div>
                <details class="dash-copilot-mcp-details">
                    <summary>Inspect MCP Payload</summary>
                    <pre>${payloadJson}</pre>
                </details>
            </div>
        `;
    };

    const appendMessage = (sender, content, cards = []) => {
        const welcome = document.getElementById('admin-copilot-welcome');
        if (welcome && sender === 'user') {
            welcome.style.display = 'none';
        }

        const msgWrap = document.createElement('div');
        msgWrap.className = `azoogi-chat-msg azoogi-chat-msg-${sender === 'user' ? 'user' : 'assistant'}`;

        let cardsHtml = '';
        if (Array.isArray(cards) && cards.length > 0) {
            cardsHtml = cards.map(renderMcpCard).join('');
        }

        msgWrap.innerHTML = `
            <div class="azoogi-chat-bubble">
                <div class="azoogi-chat-text">${formatMarkdown(content)}</div>
                ${cardsHtml}
            </div>
        `;

        body.appendChild(msgWrap);
        scrollToBottom();
    };

    const showTyping = () => {
        const el = document.createElement('div');
        el.id = 'admin-copilot-typing';
        el.className = 'azoogi-chat-msg azoogi-chat-msg-assistant';
        el.innerHTML = `
            <div class="azoogi-chat-bubble">
                <div class="azoogi-chat-text"><em>Running MCP tool...</em></div>
            </div>
        `;
        body.appendChild(el);
        scrollToBottom();
    };

    const removeTyping = () => {
        document.getElementById('admin-copilot-typing')?.remove();
    };

    const hydrateHistory = async () => {
        if (isInitialized) return;
        isInitialized = true;
        try {
            const res = await fetch(initUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': getCsrfToken(),
                },
                body: JSON.stringify({}),
            });
            const data = await res.json();
            if (data.status === 'success' && Array.isArray(data.messages) && data.messages.length > 0) {
                const welcome = document.getElementById('admin-copilot-welcome');
                if (welcome) welcome.style.display = 'none';
                data.messages.forEach((m) => {
                    appendMessage(m.sender, m.content, m.cards || []);
                });
            }
        } catch (e) {}
    };

    const toggleCopilot = (forceState = null) => {
        const isOpen = forceState !== null ? forceState : !widget.classList.contains('is-open');
        if (isOpen) {
            widget.classList.add('is-open');
            localStorage.setItem(STORAGE_OPEN_KEY, 'true');
            hydrateHistory();
            setTimeout(() => input?.focus(), 150);
            scrollToBottom();
        } else {
            widget.classList.remove('is-open');
            localStorage.setItem(STORAGE_OPEN_KEY, 'false');
        }
    };

    const sendMessage = async (textOverride = null) => {
        if (isProcessing) return;
        const text = (textOverride !== null ? textOverride : (input?.value || '')).trim();
        if (!text) return;

        if (textOverride === null && input) {
            input.value = '';
        }

        isProcessing = true;
        appendMessage('user', text);
        showTyping();

        try {
            const res = await fetch(messageUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': getCsrfToken(),
                },
                body: JSON.stringify({
                    message: text,
                    current_url: window.location.href,
                }),
            });
            const data = await res.json();
            removeTyping();

            if (data.status === 'success') {
                appendMessage('assistant', data.reply, data.cards || []);
            } else {
                appendMessage('assistant', data.message || 'Unable to complete MCP request.');
            }
        } catch (err) {
            removeTyping();
            appendMessage('assistant', 'Network error while communicating with Admin Copilot.');
        } finally {
            isProcessing = false;
        }
    };

    launcher?.addEventListener('click', () => toggleCopilot());
    closeBtn?.addEventListener('click', () => toggleCopilot(false));

    document.querySelectorAll('.js-open-admin-copilot').forEach((link) => {
        link.addEventListener('click', (e) => {
            e.preventDefault();
            toggleCopilot(true);
        });
    });

    sendBtn?.addEventListener('click', () => sendMessage());
    input?.addEventListener('keydown', (e) => {
        if (e.key === 'Enter' && !e.shiftKey) {
            e.preventDefault();
            sendMessage();
        }
    });

    document.querySelectorAll('.js-admin-copilot-chip').forEach((chip) => {
        chip.addEventListener('click', () => {
            const promptText = chip.getAttribute('data-prompt');
            if (promptText) {
                sendMessage(promptText);
            }
        });
    });

    resetBtn?.addEventListener('click', async () => {
        if (!confirm('Clear Admin Copilot conversation history?')) return;
        try {
            await fetch(clearUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': getCsrfToken(),
                },
            });
        } catch (e) {}

        body.querySelectorAll('.azoogi-chat-msg').forEach((el) => el.remove());
        const welcome = document.getElementById('admin-copilot-welcome');
        if (welcome) welcome.style.display = '';
    });

    if (localStorage.getItem(STORAGE_OPEN_KEY) === 'true') {
        toggleCopilot(true);
    }
});

