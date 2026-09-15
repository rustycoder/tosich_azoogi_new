(() => {
  const STORAGE_KEY = 'azoogi_quote_items';
  const FALLBACK_IMAGE = '/assets/bg_default.png';

  function cleanStr(s) {
    return String(s || '').replace(/\s+/g, ' ').trim().toLowerCase();
  }

  function readItems() {
    try {
      const raw = localStorage.getItem(STORAGE_KEY);
      const items = raw ? JSON.parse(raw) : [];
      if (!Array.isArray(items)) {
        return [];
      }

      let mutated = false;
      const result = items.map((item) => {
        const sku = primarySku(item.sku);
        const resolvedImg = resolveItemImage(item);
        let curImg = String(item.image || '').trim();
        if ((!curImg || curImg === FALLBACK_IMAGE || curImg.includes('bg_default.png') || curImg.includes('logo_dark.png')) && resolvedImg && resolvedImg !== FALLBACK_IMAGE) {
          curImg = resolvedImg;
          mutated = true;
        }
        return {
          ...item,
          sku,
          id: sku || item.id,
          image: curImg || resolvedImg || FALLBACK_IMAGE,
        };
      });

      if (mutated) {
        try {
          localStorage.setItem(STORAGE_KEY, JSON.stringify(result));
        } catch {}
      }

      return result;
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

  function extractProductImage(p) {
    if (!p) return '';
    const images = p.product_images || p.images || [];
    let raw = '';
    if (Array.isArray(images) && images.length > 0) {
      raw = images[0];
    } else if (p.cover) {
      raw = p.cover;
    } else if (p.img) {
      raw = p.img;
    } else if (p.image) {
      raw = p.image;
    } else if (Array.isArray(p.product_dimension) && p.product_dimension.length > 0) {
      raw = p.product_dimension[0];
    }

    if (typeof raw === 'object' && raw !== null && raw.url) {
      raw = raw.url;
    }
    if (typeof raw === 'string') {
      const clean = raw.trim();
      if (clean && clean !== FALLBACK_IMAGE && !clean.includes('bg_default.png') && !clean.includes('logo_dark.png')) {
        return (clean.startsWith('http://') || clean.startsWith('https://') || clean.startsWith('/')) ? clean : ('/' + clean);
      }
    }
    return '';
  }

  function searchTreeForVariant(nodes, target) {
    if (!nodes || !Array.isArray(nodes) || !target) return null;
    for (const node of nodes) {
      if (!node) continue;
      if (node.variants && typeof node.variants === 'object') {
        for (const vName in node.variants) {
          if (cleanStr(vName) === target || cleanStr(node.name) === target) {
            const vData = node.variants[vName];
            if (typeof vData === 'string') return vData;
            if (typeof vData === 'object' && vData !== null && vData.id) return vData.id;
          }
        }
      }
      if (node.children && Array.isArray(node.children)) {
        const res = searchTreeForVariant(node.children, target);
        if (res) return res;
      }
    }
    return null;
  }

  function findProductInCatalog(item) {
    if (!item || typeof AZOOGI_PRODUCTS === 'undefined' || !AZOOGI_PRODUCTS.products) {
      return null;
    }

    const list = Array.isArray(AZOOGI_PRODUCTS.products)
      ? AZOOGI_PRODUCTS.products
      : Object.values(AZOOGI_PRODUCTS.products);

    const targetId = cleanStr(item.id);
    const targetSku = cleanStr(primarySku(item.sku));
    const targetName = cleanStr(item.name);

    let urlSlug = '';
    let urlId = '';
    if (item.url) {
      const u = String(item.url);
      const mSlug = u.match(/\/products\/([^?#]+)/);
      if (mSlug) {
        urlSlug = cleanStr(decodeURIComponent(mSlug[1]));
      }
      const mId = u.match(/[?&]id=([^&#]+)/);
      if (mId) {
        urlId = cleanStr(decodeURIComponent(mId[1]));
      }
    }

    // 1. Exact ID or Slug match
    for (const p of list) {
      if (!p) continue;
      const pId = cleanStr(p.id || p.airtable_id);
      const pSlug = cleanStr(p.slug);
      if (targetId && (pId === targetId || pSlug === targetId)) return p;
      if (urlId && pId === urlId) return p;
      if (urlSlug && pSlug === urlSlug) return p;
    }

    // 2. SKU match (code, mapped SKUs)
    if (targetSku) {
      for (const p of list) {
        if (!p) continue;
        const pSku = cleanStr(primarySku(p.product_code || p.sku));
        if (pSku && pSku === targetSku) return p;
        if (p.sku_mappings && typeof p.sku_mappings === 'object') {
          for (const k in p.sku_mappings) {
            const mappedSku = cleanStr(primarySku(p.sku_mappings[k]));
            if (mappedSku && mappedSku === targetSku) return p;
          }
        }
      }
    }

    // 3. Exact Name match
    if (targetName) {
      for (const p of list) {
        if (!p) continue;
        const pName = cleanStr(p.product_name || p.name);
        if (pName && pName === targetName) return p;
      }
    }

    // 4. Tree variants match
    if (AZOOGI_PRODUCTS.tree && Array.isArray(AZOOGI_PRODUCTS.tree)) {
      const variantProdId = searchTreeForVariant(AZOOGI_PRODUCTS.tree, targetName || targetId);
      if (variantProdId) {
        const found = list.find((p) => cleanStr(p.id || p.airtable_id) === cleanStr(variantProdId));
        if (found) return found;
      }
    }

    // 5. Fuzzy / contains Name match
    if (targetName && targetName.length > 3) {
      for (const p of list) {
        if (!p) continue;
        const pName = cleanStr(p.product_name || p.name);
        if (pName && (pName.includes(targetName) || targetName.includes(pName))) return p;
      }
    }

    return null;
  }

  function resolveItemImage(item) {
    if (!item) return FALLBACK_IMAGE;

    let img = String(item.image || '').trim();
    if (img && img !== FALLBACK_IMAGE && img !== '/assets/logo_dark.png' && !img.includes('bg_default.png')) {
      if (img.startsWith('http://') || img.startsWith('https://') || img.startsWith('/')) {
        return img;
      }
      return '/' + img;
    }

    if (typeof AZOOGI_PRODUCTS !== 'undefined' && AZOOGI_PRODUCTS) {
      const prod = findProductInCatalog(item);
      if (prod) {
        const foundImg = extractProductImage(prod);
        if (foundImg) {
          return foundImg;
        }
      }
    }

    return FALLBACK_IMAGE;
  }

  function backgroundUrl(url) {
    const safe = String(url || FALLBACK_IMAGE).replace(/"/g, '');
    return 'url("' + safe + '")';
  }

  function upsertItem(next) {
    const name = String(next.name || '').trim();
    if (!name) {
      return;
    }

    const items = readItems();
    let img = String(next.image || '').trim();
    if (!img || img === FALLBACK_IMAGE || img.includes('bg_default.png') || img.includes('logo_dark.png')) {
      img = resolveItemImage(next);
    }

    const incoming = {
      id: primarySku(next.sku) || String(next.id || name),
      name,
      sku: primarySku(next.sku),
      image: img || FALLBACK_IMAGE,
      url: String(next.url || '').trim(),
      qty: 1,
    };
    const key = itemKey(incoming);
    const existing = items.find((item) => itemKey(item) === key);

    if (existing) {
      existing.qty = (Number(existing.qty) || 1) + 1;
      if (incoming.image && incoming.image !== FALLBACK_IMAGE && !incoming.image.includes('bg_default.png')) {
        existing.image = incoming.image;
      }
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

    const card = button.closest('.prod-card, [data-href], .prod-grid');
    if (!card) {
      return null;
    }

    const title = card.querySelector('.prod-card-title-text, .prod-card-title, h3, h4');
    const cat = title ? title.querySelector('.cat-label') : null;
    const code = title ? title.querySelector('.prod-card-code') : null;
    let name = title ? title.textContent : '';
    if (cat) {
      name = name.replace(cat.textContent, '');
    }
    if (code) {
      name = name.replace(code.textContent, '');
    }

    const swatch = card.querySelector('.prod-swatch, .prod-card-img img, img');
    const rawBg = swatch ? (swatch.getAttribute('src') || swatch.src || swatch.style.backgroundImage || '') : '';
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
    const name = (specBtn && specBtn.dataset.quoteName) || (nameEl ? nameEl.textContent.trim() : '');
    const sku = primarySku(
      (specBtn && specBtn.dataset.quoteSku) || (codeEl ? codeEl.textContent : ''),
    );
    let image = (specBtn && specBtn.dataset.quoteImage) || (imgEl ? (imgEl.currentSrc || imgEl.src || imgEl.getAttribute('src') || '') : '');
    if (image && image.includes(window.location.origin)) {
      image = image.replace(window.location.origin, '');
    }

    return {
      id: (specBtn && specBtn.dataset.quoteId) || sku || name,
      name,
      sku,
      image,
      url: (specBtn && specBtn.dataset.quoteUrl) || (window.location.pathname + window.location.search),
    };
  }

  function resolveItemSku(item) {
    let sku = primarySku(item && item.sku ? item.sku : '');
    if (sku) {
      return sku;
    }

    if (typeof AZOOGI_PRODUCTS !== 'undefined' && AZOOGI_PRODUCTS) {
      const found = findProductInCatalog(item);
      if (found) {
        return primarySku(found.product_code || found.sku || (found.product_features && (found.product_features['Product Code'] || found.product_features['Product code'])) || '');
      }
    }

    return '';
  }

  function renderItemRow(item, variant) {
    const row = document.createElement('div');
    row.className = variant === 'page' ? 'quote-page-item' : 'quote-item';
    row.dataset.quoteKey = itemKey(item);

    const imgSrc = resolveItemImage(item);
    const img = document.createElement('div');
    img.className = variant === 'page' ? 'quote-page-item-img' : 'quote-item-img';
    img.style.backgroundImage = backgroundUrl(imgSrc);

    const imgEl = document.createElement('img');
    imgEl.src = imgSrc;
    imgEl.alt = item.name || 'Product Image';
    imgEl.loading = 'lazy';
    imgEl.onerror = function () {
      this.onerror = null;
      this.src = FALLBACK_IMAGE;
      this.classList.add('is-fallback');
    };
    if (imgSrc === FALLBACK_IMAGE || imgSrc.includes('bg_default.png') || imgSrc.includes('logo_dark.png')) {
      imgEl.classList.add('is-fallback');
    }
    img.appendChild(imgEl);

    const copy = document.createElement('div');
    copy.className = variant === 'page' ? 'quote-page-item-copy' : 'quote-item-copy';

    const name = document.createElement(item.url ? 'a' : 'span');
    name.className = variant === 'page' ? 'quote-page-item-name' : 'quote-item-name';
    name.textContent = item.name;
    if (item.url) {
      name.href = item.url;
    }
    copy.appendChild(name);

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

  function isProductInQuote(items, product) {
    const sku = primarySku(product.sku);
    const name = String(product.name || '').trim();

    return items.some((item) => {
      const key = itemKey(item);
      return (sku !== '' && key === sku) || (name !== '' && (key === name || item.name === name));
    });
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

    const product = extractFromProductDetail();
    const productAdded = isProductInQuote(items, product);
    document.querySelectorAll('#add-to-spec-btn, #add-to-spec-btn-summary').forEach((button) => {
      button.classList.toggle('is-added', productAdded);
      const label = button.querySelector('[data-quote-label]');
      if (label) {
        label.textContent = productAdded ? 'Added to Quote List' : 'Add to Quote List';
      }
      const icon = button.querySelector('svg path');
      if (icon) {
        icon.setAttribute(
          'd',
          productAdded
            ? 'M382-240 154-468l57-57 171 171 367-367 57 57-424 424Z'
            : 'M440-440H200v-80h240v-240h80v240h240v80H520v240h-80v-240Z',
        );
      }
      button.setAttribute('aria-label', productAdded ? 'Open quote list' : 'Add to quote list');
    });
  }

  function openDrawer() {
    document.body.classList.add('quote-open');
    window.dispatchEvent(new CustomEvent('quote:open'));
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
        const item = extractFromProductDetail();
        const exists = isProductInQuote(readItems(), item);
        if (exists) {
          openDrawer();
        } else {
          upsertItem(item);
        }
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
