(() => {
    const root = document.querySelector('.dash-visual');
    if (!root) {
        return;
    }

    const backdrop = document.getElementById('dash-drawer-backdrop');
    const contentsCss = Array.isArray(window.dashCkeditor?.contentsCss)
        ? window.dashCkeditor.contentsCss
        : [];

    const editorConfig = (drawer) => {
        const wide = drawer.classList.contains('is-wide');

        return {
            startupMode: 'wysiwyg',
            height: wide ? 520 : 280,
            allowedContent: true,
            versionCheck: false,
            contentsCss,
            removePlugins: 'elementspath,sourcearea',
            toolbar: [
                { name: 'styles', items: ['Format'] },
                { name: 'basicstyles', items: ['Bold', 'Italic', 'Underline', 'Strike', '-', 'RemoveFormat'] },
                { name: 'paragraph', items: ['NumberedList', 'BulletedList', '-', 'Outdent', 'Indent'] },
                { name: 'links', items: ['Link', 'Unlink'] },
            ],
            on: {
                instanceReady(event) {
                    const html = event.editor.document.getDocumentElement().$;
                    const theme = document.documentElement.getAttribute('data-theme') || 'light';
                    html.setAttribute('data-theme', theme);
                    event.editor.resetDirty();
                },
            },
        };
    };

    const mountEditors = (drawer) => {
        if (typeof CKEDITOR === 'undefined') {
            return;
        }

        const config = editorConfig(drawer);

        drawer.querySelectorAll('textarea[data-ckeditor]').forEach((textarea) => {
            if (!textarea.id) {
                textarea.id = `ckeditor-${Math.random().toString(36).slice(2, 10)}`;
            }

            const existing = CKEDITOR.instances[textarea.id];

            if (existing) {
                existing.resize('100%', config.height);
                return;
            }

            const editor = CKEDITOR.replace(textarea, config);

            textarea.closest('form')?.addEventListener('submit', () => {
                editor.updateElement();
            });
        });
    };

    const closeDrawers = () => {
        document.querySelectorAll('.dash-drawer').forEach((drawer) => {
            drawer.hidden = true;
        });
        if (backdrop) {
            backdrop.hidden = true;
        }
    };

    const openSection = (section) => {
        closeDrawers();
        const drawer = document.getElementById(`dash-drawer-${section}`);
        if (!drawer) {
            return;
        }
        drawer.hidden = false;
        if (backdrop) {
            backdrop.hidden = false;
        }
        window.setTimeout(() => mountEditors(drawer), 50);
    };

    document.querySelectorAll('[data-open-section]').forEach((button) => {
        if (button.tagName === 'DIV') {
            return;
        }
        button.addEventListener('click', () => openSection(button.dataset.openSection));
    });

    document.querySelectorAll('[data-close-drawer]').forEach((button) => {
        button.addEventListener('click', closeDrawers);
    });

    backdrop?.addEventListener('click', closeDrawers);

    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape') {
            closeDrawers();
        }
    });

    window.addEventListener('message', (event) => {
        if (event.origin !== window.location.origin || event.data?.source !== 'azoogi-cms') {
            return;
        }
        if (event.data.section) {
            openSection(event.data.section);
        }
    });

    const initial = root.dataset.openSection;
    if (initial) {
        openSection(initial);
    }
})();
