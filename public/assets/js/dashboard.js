(() => {
    const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') ?? '';
    const stack = document.getElementById('dash-toasts');

    const toast = (message, type = 'success') => {
        if (!stack || !message) {
            return;
        }

        const item = document.createElement('div');
        item.className = `dash-toast${type === 'error' ? ' is-error' : ''}`;
        item.textContent = message;
        stack.append(item);

        window.setTimeout(() => {
            item.classList.add('is-out');
            window.setTimeout(() => item.remove(), 220);
        }, 3200);
    };

    window.dashToast = toast;

    const flash = stack?.dataset.flash;
    if (flash) {
        toast(flash);
        delete stack.dataset.flash;
    }

    const shell = document.querySelector('.dash');
    const menuButton = document.querySelector('[data-dash-menu]');

    const isMobileNav = () => window.matchMedia('(max-width: 1024px)').matches;

    const setNavOpen = (open) => {
        if (!shell || !isMobileNav()) {
            return;
        }

        shell.classList.toggle('is-nav-open', open);
        document.querySelector('.dash-side-backdrop')?.toggleAttribute('hidden', !open);
        menuButton?.setAttribute('aria-expanded', open ? 'true' : 'false');
    };

    menuButton?.addEventListener('click', () => {
        if (!isMobileNav()) {
            return;
        }

        setNavOpen(!shell?.classList.contains('is-nav-open'));
    });

    document.querySelectorAll('[data-dash-menu-close]').forEach((el) => {
        el.addEventListener('click', () => setNavOpen(false));
    });

    document.querySelectorAll('.dash-nav a').forEach((link) => {
        link.addEventListener('click', () => {
            if (isMobileNav()) {
                setNavOpen(false);
            }
        });
    });

    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape') {
            setNavOpen(false);
        }
    });

    window.addEventListener('resize', () => {
        if (isMobileNav()) {
            return;
        }

        shell?.classList.remove('is-nav-open', 'is-nav-collapsed');
        document.querySelector('.dash-side-backdrop')?.setAttribute('hidden', '');
        menuButton?.setAttribute('aria-expanded', 'false');
    });

    document.querySelectorAll('[data-dash-toggle]').forEach((button) => {
        button.addEventListener('click', async () => {
            if (button.disabled) {
                return;
            }

            button.disabled = true;

            try {
                const response = await fetch(button.dataset.dashToggle, {
                    method: 'PATCH',
                    headers: {
                        Accept: 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': csrf,
                    },
                });

                if (!response.ok) {
                    throw new Error('toggle-failed');
                }

                const data = await response.json();
                const onClass = button.dataset.dashOnClass;
                const offClass = button.dataset.dashOffClass;

                button.classList.remove(onClass, offClass);
                button.classList.add(data.on ? onClass : offClass);
                button.textContent = data.label;
                button.setAttribute('aria-pressed', data.on ? 'true' : 'false');
                toast(data.message);
            } catch {
                toast('Could not update. Try again.', 'error');
            } finally {
                button.disabled = false;
            }
        });
    });

    const sortBody = document.querySelector('[data-dash-sort]');

    if (sortBody) {
        let dragging = null;
        let startOrder = [];

        const rowOrder = () => [...sortBody.querySelectorAll('[data-id]')].map((row) => Number(row.dataset.id));

        const rowFromPoint = (y) => {
            const rows = [...sortBody.querySelectorAll('[data-id]:not(.is-dragging)')];

            return rows.reduce((closest, row) => {
                const box = row.getBoundingClientRect();
                const offset = y - box.top - box.height / 2;

                if (offset < 0 && offset > closest.offset) {
                    return { offset, element: row };
                }

                return closest;
            }, { offset: Number.NEGATIVE_INFINITY, element: null }).element;
        };

        const persistOrder = async () => {
            const order = rowOrder();

            if (order.join() === startOrder.join()) {
                return;
            }

            try {
                const response = await fetch(sortBody.dataset.dashSort, {
                    method: 'PATCH',
                    headers: {
                        Accept: 'application/json',
                        'Content-Type': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': csrf,
                    },
                    body: JSON.stringify({ order }),
                });

                if (!response.ok) {
                    throw new Error('reorder-failed');
                }

                const data = await response.json();
                startOrder = order;
                toast(data.message || 'Featured order updated.');
            } catch {
                toast('Could not update order. Try again.', 'error');
                window.location.reload();
            }
        };

        startOrder = rowOrder();

        sortBody.querySelectorAll('[data-id]').forEach((row) => {
            const handle = row.querySelector('.dash-drag-handle');

            handle?.addEventListener('pointerdown', () => {
                row.setAttribute('draggable', 'true');
            });

            handle?.addEventListener('pointerup', () => {
                if (!row.classList.contains('is-dragging')) {
                    row.removeAttribute('draggable');
                }
            });

            row.addEventListener('dragstart', (event) => {
                if (!row.getAttribute('draggable')) {
                    event.preventDefault();
                    return;
                }

                dragging = row;
                startOrder = rowOrder();
                row.classList.add('is-dragging');
                event.dataTransfer.effectAllowed = 'move';
                event.dataTransfer.setData('text/plain', row.dataset.id);
            });

            row.addEventListener('dragend', () => {
                row.classList.remove('is-dragging');
                row.removeAttribute('draggable');
                dragging = null;
                persistOrder();
            });
        });

        sortBody.addEventListener('dragover', (event) => {
            event.preventDefault();

            if (!dragging) {
                return;
            }

            const after = rowFromPoint(event.clientY);

            if (after) {
                sortBody.insertBefore(dragging, after);
            } else {
                sortBody.append(dragging);
            }
        });
    }

    const dialog = document.querySelector('[data-enquiry-dialog]');
    const dialogTitle = dialog?.querySelector('[data-enquiry-dialog-title]');
    const dialogMeta = dialog?.querySelector('[data-enquiry-dialog-meta]');
    const dialogBody = dialog?.querySelector('[data-enquiry-dialog-body]');
    let openCard = null;
    let statusLabels = {};

    const refreshCounts = (root) => {
        root.querySelectorAll('[data-kanban-col]').forEach((column) => {
            const count = column.querySelectorAll('[data-enquiry-id]').length;
            const badge = column.querySelector('[data-kanban-count]');

            if (badge) {
                badge.textContent = String(count);
            }
        });
    };

    const cardFromPoint = (column, y) => {
        const cards = [...column.querySelectorAll('[data-enquiry-id]:not(.is-dragging)')];

        return cards.reduce((closest, card) => {
            const box = card.getBoundingClientRect();
            const offset = y - box.top - box.height / 2;

            if (offset < 0 && offset > closest.offset) {
                return { offset, element: card };
            }

            return closest;
        }, { offset: Number.NEGATIVE_INFINITY, element: null }).element;
    };

    const setStatusPill = (pill, status, label) => {
        if (!pill || !status) {
            return;
        }

        pill.className = `dash-pill is-${status}`;
        pill.textContent = label || statusLabels[status] || status;
    };

    const cardStatus = (card) => card.closest('[data-status]')?.dataset.status ?? '';

    const syncCardStatus = (card, status, label) => {
        const template = card.querySelector('[data-enquiry-detail]');
        setStatusPill(template?.content?.querySelector('[data-enquiry-status]'), status, label);
    };

    const persistStatus = async (card, status) => {
        try {
            const response = await fetch(card.dataset.url, {
                method: 'PATCH',
                headers: {
                    Accept: 'application/json',
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': csrf,
                },
                body: JSON.stringify({ status }),
            });

            if (!response.ok) {
                throw new Error('status-failed');
            }

            const data = await response.json();
            syncCardStatus(card, data.status, data.label);
            toast(data.message);
        } catch {
            toast('Could not update. Try again.', 'error');
            window.location.reload();
        }
    };

    const clearDialog = () => {
        openCard = null;
        dialogMeta?.replaceChildren();
        dialogBody?.replaceChildren();
    };

    const closeDialog = () => {
        clearDialog();

        if (dialog?.open) {
            dialog.close();
        }
    };

    const openDialog = (card) => {
        const detail = card.querySelector('[data-enquiry-detail]');

        if (!dialog || !dialogTitle || !dialogBody || !detail) {
            return;
        }

        const clone = detail.content.cloneNode(true);
        const meta = clone.querySelector('[data-enquiry-meta]');

        openCard = card;
        dialogTitle.textContent = card.querySelector('strong')?.textContent ?? 'Enquiry';
        setStatusPill(clone.querySelector('[data-enquiry-status]'), cardStatus(card));

        if (dialogMeta && meta) {
            dialogMeta.replaceChildren(...meta.childNodes);
            meta.remove();
        }

        dialogBody.replaceChildren(clone);
        dialog.showModal();
    };

    const deleteEnquiry = async (card) => {
        if (!window.confirm('Delete this enquiry?')) {
            return;
        }

        try {
            const response = await fetch(card.dataset.deleteUrl, {
                method: 'DELETE',
                headers: {
                    Accept: 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': csrf,
                },
            });

            if (!response.ok) {
                throw new Error('delete-failed');
            }

            const data = await response.json();
            const kanban = card.closest('[data-enquiry-kanban]');
            closeDialog();
            card.remove();

            if (kanban) {
                refreshCounts(kanban);
            }

            toast(data.message);
        } catch {
            toast('Could not delete. Try again.', 'error');
        }
    };

    dialog?.addEventListener('click', (event) => {
        if (event.target === dialog) {
            closeDialog();
        }
    });

    dialog?.addEventListener('close', () => {
        clearDialog();
    });

    dialog?.querySelectorAll('[data-enquiry-dialog-close]').forEach((button) => {
        button.addEventListener('click', () => closeDialog());
    });

    dialog?.querySelector('[data-enquiry-delete]')?.addEventListener('click', () => {
        if (openCard) {
            deleteEnquiry(openCard);
        }
    });

    document.querySelectorAll('[data-enquiry-kanban]').forEach((kanban) => {
        const canMove = !kanban.hasAttribute('data-pending-only');
        let dragging = null;
        let originStatus = '';

        try {
            statusLabels = { ...statusLabels, ...JSON.parse(kanban.dataset.statusLabels || '{}') };
        } catch {
            // Keep labels already parsed from other boards.
        }

        kanban.querySelectorAll('[data-enquiry-id]').forEach((card) => {
            card.querySelector('[data-enquiry-open]')?.addEventListener('click', () => {
                openDialog(card);
            });

            if (!canMove) {
                return;
            }

            const handle = card.querySelector('.dash-drag-handle');

            handle?.addEventListener('pointerdown', () => {
                card.setAttribute('draggable', 'true');
            });

            handle?.addEventListener('pointerup', () => {
                if (!card.classList.contains('is-dragging')) {
                    card.removeAttribute('draggable');
                }
            });

            card.addEventListener('dragstart', (event) => {
                if (!card.getAttribute('draggable')) {
                    event.preventDefault();
                    return;
                }

                dragging = card;
                originStatus = card.closest('[data-kanban-drop]')?.dataset.status ?? '';
                card.classList.add('is-dragging');
                event.dataTransfer.effectAllowed = 'move';
                event.dataTransfer.setData('text/plain', card.dataset.enquiryId);
            });

            card.addEventListener('dragend', () => {
                const nextStatus = card.closest('[data-kanban-drop]')?.dataset.status ?? '';

                card.classList.remove('is-dragging');
                card.removeAttribute('draggable');
                refreshCounts(kanban);

                if (dragging === card && nextStatus !== '' && nextStatus !== originStatus) {
                    syncCardStatus(card, nextStatus);
                    persistStatus(card, nextStatus);
                }

                dragging = null;
                originStatus = '';
            });
        });

        if (!canMove) {
            return;
        }

        kanban.querySelectorAll('[data-kanban-drop]').forEach((column) => {
            column.addEventListener('dragover', (event) => {
                event.preventDefault();

                if (!dragging) {
                    return;
                }

                const empty = column.querySelector('.dash-kanban-empty');
                const after = cardFromPoint(column, event.clientY);

                if (after) {
                    column.insertBefore(dragging, after);
                } else if (empty) {
                    column.insertBefore(dragging, empty);
                } else {
                    column.append(dragging);
                }
            });
        });
    });

    document.querySelectorAll('[data-access-section]').forEach((section) => {
        const button = section.querySelector('[data-access-toggle]');
        const boxes = [...section.querySelectorAll('input[type="checkbox"]')];

        if (!button || boxes.length === 0) {
            return;
        }

        const syncToggle = () => {
            button.textContent = boxes.every((box) => box.checked) ? 'Clear' : 'Select all';
        };

        button.addEventListener('click', () => {
            const next = !boxes.every((box) => box.checked);
            boxes.forEach((box) => {
                box.checked = next;
            });
            syncToggle();
        });

        boxes.forEach((box) => {
            box.addEventListener('change', syncToggle);
        });

        syncToggle();
    });
})();
