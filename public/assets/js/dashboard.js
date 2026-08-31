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

        const rowOrder = () => [...sortBody.querySelectorAll('tr[data-id]')].map((row) => Number(row.dataset.id));

        const rowFromPoint = (y) => {
            const rows = [...sortBody.querySelectorAll('tr[data-id]:not(.is-dragging)')];

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

        sortBody.querySelectorAll('tr[data-id]').forEach((row) => {
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
})();
