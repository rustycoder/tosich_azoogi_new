(() => {
  const STORAGE_KEY = 'azoogi_quote_items';
  const FALLBACK_IMAGE = '/assets/bg_default.png';

  function readItems() {
    try {
      const raw = localStorage.getItem(STORAGE_KEY);
      const items = raw ? JSON.parse(raw) : [];
      if (!Array.isArray(items)) {
        return [];
      }

      return items.map((item) => {
        const sku = primarySku(item.sku);
        return {
          ...item,
          sku,
          id: sku || item.id,
        };
      });
    } catch {
      return [];
    }
  }

  function writeItems(items) {
    localStorage.setItem(STORAGE_KEY, JSON.stringify(items));
    window.dispatchEvent(new CustomEvent('quote:changed'));
  }

  function normalizeSku(value) {
    return String(value || '').replace(/^(MODEL|SKU|PRODUCT CODE):\s*/i, '').trim();
  }

  function primarySku(value) {
    const sku = normalizeSku(value);
    if (!sku) {
      return '';
    }

    return sku.split(',')[0].trim();
  }

  function itemKey(item) {
    return primarySku(item.sku) || String(item.id || item.name || '').trim();
  }

  function countItems(items) {
    return items.reduce((sum, item) => sum + (Number(item.qty) || 1), 0);
  }

  function upsertItem(next) {
    const name = String(next.name || '').trim();
    if (!name) {
      return;
    }

    const items = readItems();
    const incoming = {
      id: primarySku(next.sku) || String(next.id || name),
      name,
      sku: primarySku(next.sku),
      image: String(next.image || '').trim() || FALLBACK_IMAGE,
      url: String(next.url || '').trim(),
      qty: 1,
    };
    const key = itemKey(incoming);
    const existing = items.find((item) => itemKey(item) === key);

    if (existing) {
      existing.qty = (Number(existing.qty) || 1) + 1;
      existing.image = incoming.image || existing.image;
      existing.sku = incoming.sku || existing.sku;
      existing.url = incoming.url || existing.url;
    } else {
      items.push(incoming);
    }

    writeItems(items);
  }

  function setQty(key, qty) {
    const nextQty = Math.max(0, Number(qty) || 0);
    const items = readItems()
      .map((item) => (itemKey(item) === key ? { ...item, qty: nextQty } : item))
      .filter((item) => (Number(item.qty) || 0) > 0);
    writeItems(items);
  }

  function removeItem(key) {
    writeItems(readItems().filter((item) => itemKey(item) !== key));
  }

  function backgroundUrl(url) {
    const safe = String(url || FALLBACK_IMAGE).replace(/"/g, '');
    return 'url("' + safe + '")';
  }

  function extractFromCard(button) {
    if (button.dataset.quoteName) {
      return {
        id: button.dataset.quoteId || button.dataset.quoteSku || button.dataset.quoteName,
        name: button.dataset.quoteName,
        sku: primarySku(button.dataset.quoteSku),
        image: button.dataset.quoteImage || '',
        url: button.dataset.quoteUrl || '',
      };
    }

    const card = button.closest('.prod-card, [data-href]');
    if (!card) {
      return null;
    }

    const title = card.querySelector('.prod-card-title-text');
    const cat = title ? title.querySelector('.cat-label') : null;
    const code = title ? title.querySelector('.prod-card-code') : null;
    let name = title ? title.textContent : '';
    if (cat) {
      name = name.replace(cat.textContent, '');
    }
    if (code) {
      name = name.replace(code.textContent, '');
    }

    const swatch = card.querySelector('.prod-swatch');
    const rawBg = swatch ? swatch.style.backgroundImage : '';
    const image = rawBg.replace(/^url\(["']?/, '').replace(/["']?\)$/, '');
    const onclick = card.getAttribute('onclick') || '';
    const fromOnclick = onclick.match(/['"](\/[^'"]+)['"]/);

    return {
      id: (code ? code.textContent.replace(/[()]/g, '').trim() : '') || name.trim(),
      name: name.replace(/\s+/g, ' ').trim(),
      sku: primarySku(code ? code.textContent.replace(/[()]/g, '') : ''),
      image,
      url: card.dataset.href || (fromOnclick ? fromOnclick[1] : ''),
    };
  }

  function extractFromProductDetail() {
    const nameEl = document.getElementById('product-name');
    const specBtn = document.getElementById('add-to-spec-btn');
    const codeEl = document.getElementById('product-code-label');
    const imgEl = document.getElementById('gallery-main-img');
    const name = nameEl ? nameEl.textContent.trim() : '';
    const sku = primarySku(
      (specBtn && specBtn.dataset.quoteSku) || (codeEl ? codeEl.textContent : ''),
    );

    return {
      id: sku || name,
      name,
      sku,
      image: imgEl ? (imgEl.getAttribute('src') || '') : '',
      url: window.location.pathname + window.location.search,
    };
  }

  function renderItemRow(item, variant) {
    const row = document.createElement('div');
    row.className = variant === 'page' ? 'quote-page-item' : 'quote-item';
    row.dataset.quoteKey = itemKey(item);

    const img = document.createElement('div');
    img.className = variant === 'page' ? 'quote-page-item-img' : 'quote-item-img';
    img.style.backgroundImage = backgroundUrl(item.image);

    const copy = document.createElement('div');
    copy.className = variant === 'page' ? 'quote-page-item-copy' : 'quote-item-copy';

    const name = document.createElement(item.url ? 'a' : 'span');
    name.className = variant === 'page' ? 'quote-page-item-name' : 'quote-item-name';
    name.textContent = item.name;
    if (item.url) {
      name.href = item.url;
    }
    copy.appendChild(name);

    if (item.sku) {
      const sku = document.createElement('span');
      sku.className = variant === 'page' ? 'quote-page-item-sku' : 'quote-item-sku';
      sku.textContent = 'SKU: ' + item.sku;
      copy.appendChild(sku);
    }

    const actions = document.createElement('div');
    actions.className = 'quote-item-actions';

    const stepper = document.createElement('div');
    stepper.className = 'quote-qty';

    const minus = document.createElement('button');
    minus.type = 'button';
    minus.dataset.quoteQty = '-1';
    minus.setAttribute('aria-label', 'Decrease quantity');
    minus.textContent = '−';

    const qty = document.createElement('span');
    qty.textContent = String(item.qty || 1);

    const plus = document.createElement('button');
    plus.type = 'button';
    plus.dataset.quoteQty = '1';
    plus.setAttribute('aria-label', 'Increase quantity');
    plus.textContent = '+';

    stepper.append(minus, qty, plus);

    const remove = document.createElement('button');
    remove.type = 'button';
    remove.className = 'quote-item-remove';
    remove.dataset.quoteRemove = '1';
    remove.setAttribute('aria-label', 'Remove');
    remove.innerHTML = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M4 7h16"/><path d="M9 7V5.8A1.8 1.8 0 0 1 10.8 4h2.4A1.8 1.8 0 0 1 15 5.8V7"/><path d="M6.5 7l.8 12.2A1.8 1.8 0 0 0 9.1 21h5.8a1.8 1.8 0 0 0 1.8-1.8L17.5 7"/><path d="M10 11v6M14 11v6"/></svg>';

    actions.append(stepper, remove);
    row.append(img, copy, actions);
    return row;
  }

  function renderLists() {
    const items = readItems();
    const count = countItems(items);
    const countEls = document.querySelectorAll('[data-quote-count]');
    const drawers = document.querySelectorAll('[data-quote-list="drawer"]');
    const pages = document.querySelectorAll('[data-quote-list="page"]');
    const submitLinks = document.querySelectorAll('[data-quote-submit]');
    const payload = document.getElementById('your-products');

    countEls.forEach((el) => {
      el.textContent = String(count);
      el.hidden = count === 0;
    });

    drawers.forEach((list) => {
      list.replaceChildren();
      if (items.length === 0) {
        const empty = document.createElement('p');
        empty.className = 'quote-empty';
        empty.textContent = 'No products in your quote list yet.';
        list.appendChild(empty);
        return;
      }
      items.forEach((item) => list.appendChild(renderItemRow(item, 'drawer')));
    });

    pages.forEach((list) => {
      list.replaceChildren();
      if (items.length === 0) {
        const empty = document.createElement('p');
        empty.className = 'quote-empty';
        empty.textContent = 'Your quote list is empty. Add products from a product page.';
        list.appendChild(empty);
        return;
      }
      items.forEach((item) => list.appendChild(renderItemRow(item, 'page')));
    });

    submitLinks.forEach((link) => {
      link.classList.toggle('is-disabled', items.length === 0);
      if (items.length === 0) {
        link.setAttribute('aria-disabled', 'true');
      } else {
        link.removeAttribute('aria-disabled');
      }
    });

    const pageSubmit = document.querySelector('#quote-request-form button[type="submit"]');
    if (pageSubmit) {
      pageSubmit.disabled = items.length === 0;
      pageSubmit.classList.toggle('is-disabled', items.length === 0);
      pageSubmit.setAttribute('aria-disabled', items.length === 0 ? 'true' : 'false');
    }

    if (payload) {
      payload.value = items.map((item) => (item.sku ? item.name + ' (' + item.sku + ') x' + (item.qty || 1) : item.name + ' x' + (item.qty || 1))).join('\n');
    }

    markAddedButtons(items);
  }

  function markAddedButtons(items) {
    const keys = new Set(items.map(itemKey));
    document.querySelectorAll('.add-quote-btn').forEach((button) => {
      const extracted = extractFromCard(button);
      const key = extracted ? itemKey(extracted) : '';
      const added = key !== '' && keys.has(key);
      button.classList.toggle('added', added);
      button.textContent = added ? '−' : '+';
      button.setAttribute('aria-label', added ? 'Remove from quote' : 'Add to quote');
    });
  }

  function openDrawer() {
    document.body.classList.add('quote-open');
    const trigger = document.getElementById('quote-trigger');
    const drawer = document.getElementById('quote-drawer');
    if (trigger) {
      trigger.setAttribute('aria-expanded', 'true');
    }
    if (drawer) {
      drawer.setAttribute('aria-hidden', 'false');
    }
  }

  function closeDrawer() {
    document.body.classList.remove('quote-open');
    const trigger = document.getElementById('quote-trigger');
    const drawer = document.getElementById('quote-drawer');
    if (trigger) {
      trigger.setAttribute('aria-expanded', 'false');
    }
    if (drawer) {
      drawer.setAttribute('aria-hidden', 'true');
    }
  }

  function toggleDrawer() {
    if (document.body.classList.contains('quote-open')) {
      closeDrawer();
      return;
    }
    openDrawer();
  }

  function bind() {
    const trigger = document.getElementById('quote-trigger');
    if (trigger) {
      trigger.addEventListener('click', (event) => {
        event.preventDefault();
        toggleDrawer();
      });
    }

    document.addEventListener('click', (event) => {
      if (event.target.closest('[data-quote-close]')) {
        event.preventDefault();
        closeDrawer();
        return;
      }

      const row = event.target.closest('[data-quote-key]');
      if (row) {
        const key = row.dataset.quoteKey;
        if (event.target.closest('[data-quote-remove]')) {
          removeItem(key);
          return;
        }
        const qtyBtn = event.target.closest('[data-quote-qty]');
        if (qtyBtn) {
          const item = readItems().find((entry) => itemKey(entry) === key);
          const current = item ? (Number(item.qty) || 1) : 1;
          setQty(key, current + Number(qtyBtn.dataset.quoteQty));
          return;
        }
      }

      const specBtn = event.target.closest('#add-to-spec-btn, #add-to-spec-btn-summary');
      if (specBtn) {
        event.preventDefault();
        upsertItem(extractFromProductDetail());
        openDrawer();
        return;
      }

      const cardBtn = event.target.closest('.add-quote-btn');
      if (cardBtn) {
        const item = extractFromCard(cardBtn);
        if (!item) {
          return;
        }
        const key = itemKey(item);
        const exists = readItems().some((entry) => itemKey(entry) === key);
        if (exists) {
          removeItem(key);
        } else {
          upsertItem(item);
        }
      }
    }, true);

    document.addEventListener('keydown', (event) => {
      if (event.key === 'Escape') {
        closeDrawer();
      }
    });

    const form = document.getElementById('quote-request-form');
    if (form) {
      form.addEventListener('submit', (event) => {
        if (readItems().length === 0) {
          event.preventDefault();
          return;
        }

        const products = form.querySelector('#your-products');
        if (products && !String(products.value || '').trim()) {
          products.value = readItems().map((item) => {
            const qty = Number(item.qty) || 1;
            const sku = item.sku ? ` (${item.sku})` : '';
            return `${qty}x ${item.name}${sku}`;
          }).join('\n');
        }
      });
    }

    if (document.getElementById('site-toasts')?.hasAttribute('data-clear-quote')) {
      writeItems([]);
    }

    window.addEventListener('quote:changed', renderLists);
    window.addEventListener('storage', (event) => {
      if (event.key === STORAGE_KEY) {
        renderLists();
      }
    });

    renderLists();
  }

  window.AzoogiQuote = {
    add: upsertItem,
    open: openDrawer,
    close: closeDrawer,
    items: readItems,
    clear: function () {
      writeItems([]);
    },
    refresh: function () {
      markAddedButtons(readItems());
    },
  };

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', bind);
  } else {
    bind();
  }
})();

(() => {
  const stack = document.getElementById('site-toasts');

  if (!stack) {
    return;
  }

  const toast = (message) => {
    if (!message) {
      return;
    }

    const item = document.createElement('div');
    item.className = 'site-toast';
    item.textContent = message;
    stack.append(item);

    window.setTimeout(() => {
      item.classList.add('is-out');
      window.setTimeout(() => item.remove(), 220);
    }, 4200);
  };

  const flash = stack.dataset.flash;

  if (flash) {
    toast(flash);
    delete stack.dataset.flash;
  }

  window.siteToast = toast;
})();
