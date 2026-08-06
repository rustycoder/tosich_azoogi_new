(() => {
  const INTRO = 'Australian-Owned B2B Trade Wholesaler - Custom Lighting & Smart Control Solutions';
  const WORDS = ['DESIGN', 'ENGINEER', 'CUSTOMISE', 'SUPPLY', 'CONTROL', 'COMMISSION'];
  const MOBILE_MQ = '(max-width: 960px)';
  const AI_HREF = 'ai-lighting.html';
  const AI_LABEL = 'AI Lighting';

  // Client default: clean white sheet + green accent only
  const MOBILE_NAV_STYLE = 'a';

  let el = null;
  let isHome = false;

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
      swap(INTRO, false);
      return;
    }
    swap(WORDS[(index - 1) % WORDS.length], true);
  });

  function initUtilRotate() {
    el = document.querySelector('.util-rotate');
    isHome = !!document.getElementById('hero');
    if (!el) return;
    el.textContent = INTRO;
    el.classList.remove('is-words', 'is-out');
    el.classList.add('is-in');
  }

  function ensureAiLightingLink(menu) {
    if (!menu) return;
    const existing = Array.from(menu.querySelectorAll(':scope > a')).find((a) => {
      const href = (a.getAttribute('href') || '').toLowerCase();
      return href.includes('ai-lighting');
    });
    if (existing) return;

    const link = document.createElement('a');
    link.href = AI_HREF;
    link.textContent = AI_LABEL;

    const contact = Array.from(menu.querySelectorAll(':scope > a')).find((a) => {
      const href = (a.getAttribute('href') || '').toLowerCase();
      return href.includes('contact');
    });
    if (contact) contact.after(link);
    else menu.appendChild(link);
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
      ensureAiLightingLink(menu);
      ensureMobileChrome(menu);
      return;
    }
    burger.dataset.navBound = '1';

    ensureAiLightingLink(menu);
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

    const dropdown = menu.querySelector('.has-dropdown');
    if (dropdown) {
      const trigger = dropdown.querySelector(':scope > a');
      if (trigger) {
        trigger.addEventListener('click', (e) => {
          if (!window.matchMedia(MOBILE_MQ).matches) return;
          e.preventDefault();
          dropdown.classList.toggle('open');
        });
      }
    }

    menu.addEventListener('click', (e) => {
      const link = e.target.closest('a');
      if (!link) return;
      if (link.closest('.mobile-util')) {
        closeNav();
        return;
      }
      if (link.closest('.has-dropdown') && link === dropdown?.querySelector(':scope > a')) return;
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
