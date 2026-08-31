(() => {
  const MOBILE_MQ = '(max-width: 960px)';

  // Client default: clean white sheet + green accent only
  const MOBILE_NAV_STYLE = 'a';

  let el = null;
  let isHome = false;
  let intro = 'Australian-Owned B2B Trade Wholesaler - Custom Lighting & Smart Control Solutions';
  let words = ['DESIGN', 'ENGINEER', 'CUSTOMISE', 'SUPPLY', 'CONTROL', 'COMMISSION'];

  function swap(nextText, asWords) {
    if (!el || !isHome) return;
    if (el.textContent === nextText && el.classList.contains('is-words') === asWords) return;
    el.classList.remove('is-in');
    el.classList.add('is-out');
    window.setTimeout(() => {
      el.textContent = nextText;
      el.classList.toggle('is-words', asWords);
      el.classList.remove('is-out');
      el.classList.add('is-in');
    }, 320);
  }

  window.addEventListener('hero:slide', (e) => {
    if (!isHome) return;
    const index = e.detail && typeof e.detail.index === 'number' ? e.detail.index : 0;
    if (index === 0) {
      swap(intro, false);
      return;
    }
    swap(words[(index - 1) % words.length], true);
  });

  function readWords(node) {
    const raw = node.getAttribute('data-words');
    if (!raw) {
      return words;
    }

    try {
      const parsed = JSON.parse(raw);
      if (!Array.isArray(parsed)) {
        return words;
      }

      const cleaned = parsed.map((word) => String(word).trim()).filter(Boolean);

      return cleaned.length ? cleaned : words;
    } catch {
      return words;
    }
  }

  function initUtilRotate() {
    el = document.querySelector('.util-rotate');
    isHome = !!document.getElementById('hero');
    if (!el) return;
    intro = el.textContent.trim() || intro;
    words = readWords(el);
    el.classList.remove('is-words', 'is-out');
    el.classList.add('is-in');
  }

  function ensureOverlay() {
    let overlay = document.querySelector('.nav-overlay');
    if (!overlay) {
      overlay = document.createElement('div');
      overlay.className = 'nav-overlay';
      overlay.setAttribute('aria-hidden', 'true');
      document.body.appendChild(overlay);
    }
    return overlay;
  }

  function ensureMobileChrome(menu) {
    if (!menu) return;

    menu.dataset.navStyle = MOBILE_NAV_STYLE;
    menu.classList.add('mobile-drawer');

    if (!menu.querySelector('.mobile-nav-head')) {
      const head = document.createElement('div');
      head.className = 'mobile-nav-head';
      head.innerHTML = `
        <div class="mobile-nav-brand">
          <span class="mobile-nav-kicker">Azoogi</span>
          <span class="mobile-nav-title">Menu</span>
        </div>
        <button type="button" class="mobile-nav-close" aria-label="Close menu">
          <span aria-hidden="true"></span>
          <span aria-hidden="true"></span>
        </button>
      `;
      menu.insertBefore(head, menu.firstChild);
    }

    let utilBox = menu.querySelector('.mobile-util');
    if (!utilBox) {
      const util = document.querySelector('.util');
      if (util) {
        utilBox = document.createElement('div');
        utilBox.className = 'mobile-util';

        const links = util.querySelectorAll('a');
        links.forEach((a) => {
          const href = (a.getAttribute('href') || '').toLowerCase();
          const clone = a.cloneNode(true);
          clone.classList.add('mobile-util-link');

          if (href.startsWith('tel:')) clone.dataset.kind = 'phone';
          else if (href.startsWith('mailto:')) clone.dataset.kind = 'email';
          else clone.dataset.kind = 'trade';

          const label = document.createElement('span');
          label.className = 'mobile-util-label';
          if (clone.dataset.kind === 'phone') label.textContent = 'Call';
          else if (clone.dataset.kind === 'email') label.textContent = 'Email';
          else label.textContent = 'Trade';

          const value = document.createElement('span');
          value.className = 'mobile-util-value';
          value.textContent = clone.textContent.trim();
          clone.textContent = '';
          clone.appendChild(label);
          clone.appendChild(value);
          utilBox.appendChild(clone);
        });

        if (utilBox.childNodes.length) menu.appendChild(utilBox);
      }
    } else if (utilBox !== menu.lastElementChild) {
      menu.appendChild(utilBox);
    }
  }

  function closeNav() {
    document.body.classList.remove('nav-open');
    const burger = document.querySelector('.burger');
    if (burger) burger.setAttribute('aria-expanded', 'false');
    document.querySelectorAll('.has-dropdown.open').forEach((d) => d.classList.remove('open'));
  }

  function openNav() {
    document.body.classList.add('nav-open');
    const burger = document.querySelector('.burger');
    if (burger) burger.setAttribute('aria-expanded', 'true');
  }

  function toggleNav() {
    if (document.body.classList.contains('nav-open')) closeNav();
    else openNav();
  }

  function initMobileNav() {
    const burger = document.querySelector('.burger');
    const menu = document.querySelector('.nav .menu');
    if (!burger || !menu) return;

    if (burger.dataset.navBound === '1') {
      ensureMobileChrome(menu);
      return;
    }
    burger.dataset.navBound = '1';

    ensureMobileChrome(menu);
    const overlay = ensureOverlay();

    burger.setAttribute('role', 'button');
    burger.setAttribute('aria-label', 'Open menu');
    burger.setAttribute('aria-expanded', 'false');
    if (!burger.hasAttribute('tabindex')) burger.setAttribute('tabindex', '0');

    burger.addEventListener('click', (e) => {
      e.preventDefault();
      e.stopPropagation();
      toggleNav();
    });

    burger.addEventListener('keydown', (e) => {
      if (e.key === 'Enter' || e.key === ' ') {
        e.preventDefault();
        toggleNav();
      }
    });

    overlay.addEventListener('click', closeNav);

    menu.addEventListener('click', (e) => {
      if (e.target.closest('.mobile-nav-close')) {
        e.preventDefault();
        closeNav();
      }
    });

    // Mobile: Products is a normal link (no accordion / mega menu)
    const dropdown = menu.querySelector('.has-dropdown');
    if (dropdown) {
      dropdown.classList.add('mobile-plain-link');
      const trigger = dropdown.querySelector(':scope > a');
      if (trigger) {
        const caret = trigger.querySelector('.caret');
        if (caret) caret.hidden = true;
      }
    }

    menu.addEventListener('click', (e) => {
      const link = e.target.closest('a');
      if (!link) return;
      if (link.closest('.mobile-util')) {
        closeNav();
        return;
      }
      if (link.closest('.mega-menu')) {
        closeNav();
        return;
      }
      if (link.getAttribute('href') && link.getAttribute('href') !== '#') {
        closeNav();
      }
    });

    document.addEventListener('keydown', (e) => {
      if (e.key === 'Escape') closeNav();
    });

    window.addEventListener('resize', () => {
      if (!window.matchMedia(MOBILE_MQ).matches) closeNav();
    });
  }

  function init() {
    initUtilRotate();
    initMobileNav();
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init);
  } else {
    init();
  }
})();
