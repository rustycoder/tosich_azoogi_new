(() => {
  const RESULT_LIMIT = 8;
  const FALLBACK_IMG = '/assets/bg_default.png';

  let trigger = null;
  let panel = null;
  let form = null;
  let input = null;
  let results = null;
  let closeBtn = null;
  let activeIndex = -1;
  let currentMatches = [];
  let swappedLogo = '';

  function publishedProducts() {
    if (typeof AZOOGI_PRODUCTS === 'undefined' || !Array.isArray(AZOOGI_PRODUCTS.products)) {
      return [];
    }

    return AZOOGI_PRODUCTS.products.filter((product) => {
      if (!product || typeof product !== 'object') {
        return false;
      }

      if (!product.status) {
        return true;
      }

      return String(product.status).toLowerCase().trim() === 'publish';
    });
  }

  function productCode(product) {
    const raw = product.product_code || product.productCode || '';
    if (Array.isArray(raw)) {
      return raw.map((value) => (value && typeof value === 'object' && value.value !== undefined ? value.value : value)).filter(Boolean).join(', ');
    }
    if (raw && typeof raw === 'object' && raw.value !== undefined) {
      return String(raw.value);
    }

    return String(raw || '').trim();
  }

  function skuValues(product) {
    const mappings = product.sku_mappings;
    if (!mappings || typeof mappings !== 'object') {
      return [];
    }

    return Object.values(mappings).map((value) => String(value || '').trim()).filter(Boolean);
  }

  function haystack(product) {
    const parts = [
      product.product_name,
      product.name,
      productCode(product),
      product.category,
      product.product_short_description,
      ...(Array.isArray(product.categories) ? product.categories : []),
      ...skuValues(product),
    ];

    return parts.join(' ').toLowerCase();
  }

  function productImage(product) {
    if (product.product_images && product.product_images.length > 0) {
      const src = String(product.product_images[0] || '');
      if (src !== '') {
        return src.startsWith('/') || src.startsWith('http') ? src : '/' + src;
      }
    }

    return FALLBACK_IMG;
  }

  function productUrl(product) {
    if (product.id) {
      return '/product-detail?id=' + encodeURIComponent(product.id);
    }

    const name = product.product_name || product.name || '';
    return '/product-detail?product=' + encodeURIComponent(name);
  }

  function escapeHtml(value) {
    return String(value || '')
      .replace(/&/g, '&amp;')
      .replace(/"/g, '&quot;')
      .replace(/</g, '&lt;')
      .replace(/>/g, '&gt;');
  }

  function isOpen() {
    return document.body.classList.contains('search-open');
  }

  function closeNav() {
    document.body.classList.remove('nav-open');
    const burger = document.querySelector('.burger');
    if (burger) {
      burger.setAttribute('aria-expanded', 'false');
    }
  }

  function closeQuote() {
    if (window.AzoogiQuote && typeof window.AzoogiQuote.close === 'function') {
      window.AzoogiQuote.close();
    }
  }

  function headerLogo() {
    return document.querySelector('#topbar .logo img');
  }

  function useDarkHeaderLogo() {
    const logo = headerLogo();
    if (!logo) {
      return;
    }

    const src = logo.getAttribute('src') || '';
    if (src.indexOf('logo_white') === -1) {
      return;
    }

    swappedLogo = src;
    logo.src = src.replace('logo_white.png', 'logo_dark.png');
  }

  function restoreHeaderLogo() {
    const logo = headerLogo();
    if (!logo || swappedLogo === '') {
      swappedLogo = '';
      return;
    }

    if (window.scrollY > 40) {
      logo.src = swappedLogo.replace('logo_white.png', 'logo_dark.png');
    } else {
      logo.src = swappedLogo;
    }

    swappedLogo = '';
  }

  function openSearch() {
    closeNav();
    closeQuote();
    useDarkHeaderLogo();
    document.body.classList.add('search-open');
    if (trigger) {
      trigger.setAttribute('aria-expanded', 'true');
    }
    if (panel) {
      panel.setAttribute('aria-hidden', 'false');
      panel.inert = false;
    }
    syncClearButton();
    window.setTimeout(() => {
      if (input) {
        input.focus();
        input.select();
      }
    }, 40);
  }

  function closeSearch() {
    document.body.classList.remove('search-open');
    restoreHeaderLogo();
    if (trigger) {
      trigger.setAttribute('aria-expanded', 'false');
    }
    if (panel) {
      panel.setAttribute('aria-hidden', 'true');
      panel.inert = true;
    }
    activeIndex = -1;
  }

  function toggleSearch() {
    if (isOpen()) {
      closeSearch();
      return;
    }
    openSearch();
  }

  function renderResults(query) {
    if (!results) {
      return;
    }

    const term = query.trim().toLowerCase();
    activeIndex = -1;
    currentMatches = [];

    if (term === '') {
      results.hidden = true;
      results.innerHTML = '';
      return;
    }

    currentMatches = publishedProducts().filter((product) => haystack(product).indexOf(term) !== -1).slice(0, RESULT_LIMIT);

    if (currentMatches.length === 0) {
      results.hidden = false;
      results.innerHTML = '<p class="site-search-empty">No products match that search.</p>';
      return;
    }

    results.hidden = false;
    results.innerHTML = currentMatches.map((product, index) => {
      const name = product.product_name || product.name || 'Product';
      const code = productCode(product);
      const category = Array.isArray(product.categories) && product.categories[0]
        ? product.categories[0]
        : (product.category || '');
      const meta = [code, category].filter(Boolean).join(' · ');

      return (
        '<a class="site-search-item" href="' + escapeHtml(productUrl(product)) + '" data-search-index="' + index + '">' +
          '<img class="site-search-item-img" src="' + escapeHtml(productImage(product)) + '" alt="" loading="lazy">' +
          '<span class="site-search-item-copy">' +
            '<span class="site-search-item-name">' + escapeHtml(name) + '</span>' +
            (meta !== '' ? '<span class="site-search-item-meta">' + escapeHtml(meta) + '</span>' : '') +
          '</span>' +
        '</a>'
      );
    }).join('');
  }

  function items() {
    return results ? Array.from(results.querySelectorAll('.site-search-item')) : [];
  }

  function setActive(index) {
    const nodes = items();
    if (nodes.length === 0) {
      activeIndex = -1;
      return;
    }

    if (index < 0) {
      index = nodes.length - 1;
    }
    if (index >= nodes.length) {
      index = 0;
    }

    activeIndex = index;
    nodes.forEach((node, i) => {
      node.classList.toggle('is-active', i === activeIndex);
    });
    nodes[activeIndex].scrollIntoView({ block: 'nearest' });
  }

  function goToActiveOrFirst() {
    const nodes = items();
    if (nodes.length === 0) {
      return;
    }

    const target = nodes[activeIndex] || nodes[0];
    window.location.href = target.getAttribute('href');
  }

  function syncClearButton() {
    if (!closeBtn || !input) {
      return;
    }

    closeBtn.hidden = String(input.value || '').trim() === '';
  }

  function clearQuery() {
    if (!input) {
      return;
    }

    input.value = '';
    renderResults('');
    syncClearButton();
    input.focus();
  }

  function bind() {
    trigger = document.getElementById('search-trigger');
    panel = document.getElementById('site-search');
    form = document.getElementById('site-search-form');
    input = document.getElementById('site-search-q');
    results = document.getElementById('site-search-results');
    closeBtn = document.getElementById('site-search-clear');

    if (!trigger || !panel || !input) {
      return;
    }

    trigger.addEventListener('click', (event) => {
      event.preventDefault();
      event.stopPropagation();
      toggleSearch();
    });

    if (closeBtn) {
      closeBtn.addEventListener('click', (event) => {
        event.preventDefault();
        event.stopPropagation();
        clearQuery();
      });
    }

    if (form) {
      form.addEventListener('submit', (event) => {
        event.preventDefault();
        goToActiveOrFirst();
      });
    }

    input.addEventListener('input', () => {
      renderResults(input.value);
      syncClearButton();
    });

    input.addEventListener('keydown', (event) => {
      if (event.key === 'ArrowDown') {
        event.preventDefault();
        setActive(activeIndex + 1);
      } else if (event.key === 'ArrowUp') {
        event.preventDefault();
        setActive(activeIndex - 1);
      } else if (event.key === 'Escape') {
        event.preventDefault();
        closeSearch();
        trigger.focus();
      }
    });

    document.addEventListener('keydown', (event) => {
      if (event.key === 'Escape' && isOpen()) {
        closeSearch();
      }
    });

    document.addEventListener('click', (event) => {
      if (!isOpen()) {
        return;
      }
      if (event.target.closest('#site-search, #search-trigger')) {
        return;
      }
      closeSearch();
    });

    window.addEventListener('quote:open', closeSearch);
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', bind);
  } else {
    bind();
  }
})();
