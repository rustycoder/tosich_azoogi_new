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

  function toggleTheme() {
    const newTheme = currentTheme() === 'light' ? 'dark' : 'light';
    document.documentElement.setAttribute('data-theme', newTheme);
    try {
      localStorage.setItem('theme', newTheme);
    } catch {
      // Ignore quota / private-mode failures.
    }
    updateLogos(newTheme);
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
    updateLogos();
  }

  window.toggleTheme = toggleTheme;
  window.updateLogos = updateLogos;

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initTheme);
  } else {
    initTheme();
  }
})();
