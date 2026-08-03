(() => {
  const INTRO = 'Aus Owned B2B Trade Wholesaler - Custom Lighting & Smart Control Solutions';
  const WORDS = ['DESIGN', 'ENGINEER', 'CUSTOMISE', 'SUPPLY', 'CONTROL', 'COMMISSION'];
  const INTERVAL_MS = 6000; // match hero DUR in index.html
  const MOBILE_MQ = '(max-width: 960px)';

  let el = null;

  function swap(nextText, asWords) {
    if (!el) return;
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

  // Listen early so the first hero show(0) is caught
  window.addEventListener('hero:slide', (e) => {
    const index = e.detail && typeof e.detail.index === 'number' ? e.detail.index : 0;
    if (index === 0) {
      swap(INTRO, false);
      return;
    }
    swap(WORDS[(index - 1) % WORDS.length], true);
  });

  function initUtilRotate() {
    el = document.querySelector('.util-rotate');
    if (!el) return;

    el.textContent = INTRO;
    el.classList.add('is-in');

    // Non-homepage: timed cycle matching hero duration
    if (!document.getElementById('hero')) {
      let wordIndex = 0;
      setInterval(() => {
        swap(WORDS[wordIndex % WORDS.length], true);
        wordIndex += 1;
      }, INTERVAL_MS);
    }
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

  function ensureMobileUtil(menu) {
    if (!menu || menu.querySelector('.mobile-util')) return;
    const util = document.querySelector('.util');
    if (!util) return;

    const box = document.createElement('div');
    box.className = 'mobile-util';
    util.querySelectorAll('a').forEach((a) => {
      const clone = a.cloneNode(true);
      box.appendChild(clone);
    });
    if (box.childNodes.length) menu.prepend(box);
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

    ensureMobileUtil(menu);
    const overlay = ensureOverlay();

    burger.setAttribute('role', 'button');
    burger.setAttribute('aria-label', 'Open menu');
    burger.setAttribute('aria-expanded', 'false');
    if (!burger.hasAttribute('tabindex')) burger.setAttribute('tabindex', '0');

    burger.addEventListener('click', (e) => {
      e.preventDefault();
      toggleNav();
    });

    burger.addEventListener('keydown', (e) => {
      if (e.key === 'Enter' || e.key === ' ') {
        e.preventDefault();
        toggleNav();
      }
    });

    overlay.addEventListener('click', closeNav);

    // Product dropdown accordion on mobile
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

    // Close drawer when navigating (except product accordion toggle)
    menu.addEventListener('click', (e) => {
      const link = e.target.closest('a');
      if (!link) return;
      if (link.closest('.has-dropdown') && link === dropdown?.querySelector(':scope > a')) return;
      if (link.closest('.mega-menu')) {
        // allow navigation; close drawer
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
