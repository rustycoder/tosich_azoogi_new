(() => {
  function currentTheme() {
    return document.documentElement.getAttribute('data-theme') === 'light' ? 'light' : 'dark';
  }

  function updateLogos(theme) {
    const mode = theme === 'light' || theme === 'dark' ? theme : currentTheme();
    const topbar = document.getElementById('topbar');
    const solid = !!(topbar && (topbar.classList.contains('solid') || document.body.classList.contains('search-open')));

    document.querySelectorAll('.logo img, .brand img').forEach((img) => {
      if (img.closest('.topbar')) {
        img.src = (mode === 'light' && solid) ? '/assets/logo_dark.png' : '/assets/logo_white.png';
      } else {
        img.src = mode === 'light' ? '/assets/logo_dark.png' : '/assets/logo_white.png';
      }
    });
  }

  function syncToggle(theme) {
    const btn = document.getElementById('theme-toggle');
    if (!btn) {
      return;
    }

    btn.setAttribute('aria-checked', theme === 'dark' ? 'true' : 'false');
  }

  function renderTurnstiles(targetTheme) {
    if (!window.turnstile || typeof window.turnstile.render !== 'function') {
      return;
    }
    const mode = (targetTheme === 'light' || targetTheme === 'dark') ? targetTheme : currentTheme();
    document.querySelectorAll('.cf-turnstile').forEach((el) => {
      const explicitTheme = el.getAttribute('data-theme');
      const effectiveTheme = (explicitTheme && explicitTheme !== 'auto') ? explicitTheme : mode;
      const existingId = el.getAttribute('data-turnstile-widget-id');
      if (existingId) {
        try {
          window.turnstile.remove(existingId);
        } catch (e) {}
      }
      const sitekey = el.getAttribute('data-sitekey');
      const action = el.getAttribute('data-action');
      if (sitekey) {
        try {
          const widgetId = window.turnstile.render(el, {
            sitekey: sitekey,
            theme: effectiveTheme,
            action: action || undefined,
          });
          if (widgetId !== undefined) {
            el.setAttribute('data-turnstile-widget-id', widgetId);
          }
        } catch (e) {}
      }
    });
  }

  function toggleTheme() {
    const newTheme = currentTheme() === 'light' ? 'dark' : 'light';
    document.documentElement.setAttribute('data-theme', newTheme);
    try {
      localStorage.setItem('theme', newTheme);
    } catch {
      // Ignore quota / private-mode failures.
    }
    syncToggle(newTheme);
    updateLogos(newTheme);
    renderTurnstiles(newTheme);
  }

  function initTheme() {
    const btn = document.getElementById('theme-toggle');
    if (btn && btn.dataset.themeBound !== '1') {
      btn.dataset.themeBound = '1';
      btn.addEventListener('click', (event) => {
        event.preventDefault();
        toggleTheme();
      });
    }
    syncToggle(currentTheme());
    updateLogos();
    renderTurnstiles(currentTheme());
  }

  window.toggleTheme = toggleTheme;
  window.updateLogos = updateLogos;
  window.renderTurnstiles = renderTurnstiles;
  window.onloadTurnstileCallback = function () {
    renderTurnstiles();
  };

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initTheme);
  } else {
    initTheme();
  }
})();
