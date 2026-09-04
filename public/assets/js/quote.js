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
        const sku = normalizeSku(item.sku);
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
    return String(value || '').replace(/^(MODEL|SKU):\s*/i, '').trim();
  }

  function itemKey(item) {
    return normalizeSku(item.sku) || String(item.id || item.name || '').trim();
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
      id: normalizeSku(next.sku) || String(next.id || name),
      name,
      sku: normalizeSku(next.sku),
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
        sku: normalizeSku(button.dataset.quoteSku),
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
      sku: normalizeSku(code ? code.textContent.replace(/[()]/g, '') : ''),
      image,
      url: card.dataset.href || (fromOnclick ? fromOnclick[1] : ''),
    };
  }

  function extractFromProductDetail() {
    const nameEl = document.getElementById('product-name');
    const codeEl = document.getElementById('product-code-label');
    const imgEl = document.getElementById('gallery-main-img');
    const name = nameEl ? nameEl.textContent.trim() : '';
    const sku = codeEl ? normalizeSku(codeEl.textContent) : '';

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
      sku.textContent = item.sku;
      copy.appendChild(sku);
    }

    if (variant === 'page') {
      const qty = document.createElement('span');
      qty.className = 'quote-page-item-qty';
      qty.textContent = 'Qty ' + (item.qty || 1);
      copy.appendChild(qty);
      row.append(img, copy);
      return row;
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
    remove.textContent = 'Remove';

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
      form.addEventListener('submit', () => {
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
