(() => {
    document.querySelectorAll('[data-repeat-list]').forEach((list) => {
        const rows = list.querySelector('[data-repeat-rows]');
        const template = list.querySelector('template');
        const add = list.querySelector('[data-add-item]');

        if (!rows || !template) {
            return;
        }

        add?.addEventListener('click', () => {
            const index = Number(list.dataset.next || rows.children.length);
            list.dataset.next = String(index + 1);
            const wrap = document.createElement('div');
            wrap.innerHTML = template.innerHTML.replaceAll('__INDEX__', String(index)).trim();
            const row = wrap.firstElementChild;
            if (row) {
                rows.appendChild(row);
            }
        });

        list.addEventListener('click', (event) => {
            const button = event.target.closest('[data-remove-item]');
            if (!button || !list.contains(button)) {
                return;
            }
            button.closest('[data-repeat-item]')?.remove();
        });
    });
})();
