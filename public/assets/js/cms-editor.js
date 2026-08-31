(() => {
    if (window.self === window.top) {
        return;
    }

    document.documentElement.classList.add('cms-editing');

    const layer = document.createElement('div');
    layer.className = 'cms-edit-layer';
    document.body.append(layer);

    const header = document.getElementById('topbar');
    const sections = [...document.querySelectorAll('[data-cms-section]')];

    const headerBottom = () => (header ? header.getBoundingClientRect().bottom : 0);

    const openSection = (section) => {
        window.parent.postMessage({
            source: 'azoogi-cms',
            section,
        }, window.location.origin);
    };

    const sync = () => {
        const floor = headerBottom() + 8;
        const seen = new Set();

        sections.forEach((section) => {
            const key = section.getAttribute('data-cms-section');
            const rect = section.getBoundingClientRect();
            const visible = rect.bottom > floor + 24 && rect.top < window.innerHeight - 12;

            let button = layer.querySelector(`[data-cms-for="${CSS.escape(key)}"]`);

            if (!visible || seen.has(key)) {
                button?.remove();
                return;
            }

            seen.add(key);

            if (!button) {
                button = document.createElement('button');
                button.type = 'button';
                button.className = 'cms-edit-btn';
                button.dataset.cmsFor = key;
                button.textContent = 'Edit';
                button.addEventListener('click', (event) => {
                    event.preventDefault();
                    event.stopPropagation();
                    section.scrollIntoView({ behavior: 'smooth', block: 'center' });
                    openSection(key);
                });
                layer.append(button);
            }

            const top = Math.max(rect.top, floor) + 8;
            const left = Math.min(rect.right - 78, window.innerWidth - 90);
            button.style.transform = `translate(${Math.max(12, left)}px, ${top}px)`;
        });
    };

    let frame = 0;
    const requestSync = () => {
        if (frame) {
            return;
        }
        frame = window.requestAnimationFrame(() => {
            frame = 0;
            sync();
        });
    };

    window.addEventListener('scroll', requestSync, { passive: true });
    window.addEventListener('resize', requestSync);
    document.addEventListener('click', (event) => {
        const link = event.target.closest('a');
        if (link && !link.classList.contains('cms-edit-btn')) {
            event.preventDefault();
        }
    });

    sync();
})();
