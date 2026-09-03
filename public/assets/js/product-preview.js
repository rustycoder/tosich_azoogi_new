(() => {
  const TAP_SLOP = 12;
  const cache = new Set();

  function hoverFine() {
    return window.matchMedia('(hover: hover) and (pointer: fine)').matches;
  }

  function preload(src) {
    if (!src || cache.has(src)) {
      return;
    }

    cache.add(src);
    const img = new Image();
    img.src = src;
  }

  function bind(preview) {
    const image = preview.querySelector('img');
    const name = preview.getAttribute('data-product-preview');

    if (!image || !name) {
      return;
    }

    const products = Array.from(document.querySelectorAll('.' + name + '[data-preview]'));

    if (products.length === 0) {
      return;
    }

    let visible = false;
    let active = null;
    let mouseX = 0;
    let mouseY = 0;
    let frame = 0;
    let pointer = null;
    let ignoreDismiss = false;

    function clampPlace(left, top) {
      const width = preview.offsetWidth;
      const height = preview.offsetHeight;
      const maxX = window.innerWidth - width - 16;
      const maxY = window.innerHeight - height - 16;
      preview.style.transform = 'translate('
        + Math.max(16, Math.min(left, maxX)) + 'px, '
        + Math.max(16, Math.min(top, maxY)) + 'px)';
      frame = 0;
    }

    function placeAtCursor() {
      clampPlace(mouseX + 20, mouseY + 20);
    }

    function placeNear(el) {
      const rect = el.getBoundingClientRect();
      const width = preview.offsetWidth || 220;
      const height = preview.offsetHeight || 160;
      let left = rect.left + (rect.width / 2) - (width / 2);
      let top = rect.bottom + 12;

      if (top + height > window.innerHeight - 16) {
        top = rect.top - height - 12;
      }

      clampPlace(left, top);
    }

    function mark(product) {
      products.forEach((item) => {
        item.classList.toggle('is-previewing', item === product);
      });
    }

    function show(src, alt, product, fromTouch) {
      if (!src) {
        return;
      }

      preload(src);

      if (image.getAttribute('src') !== src) {
        image.src = src;
      }

      image.alt = alt;
      preview.hidden = false;
      preview.classList.toggle('is-anchored', Boolean(fromTouch));
      visible = true;
      active = product;
      mark(product);

      if (fromTouch) {
        placeNear(product);
      } else {
        placeAtCursor();
      }
    }

    function hide() {
      visible = false;
      active = null;
      mark(null);
      preview.hidden = true;
      preview.classList.remove('is-anchored');
      image.removeAttribute('src');
      image.alt = '';
    }

    function toggleFromTap(product) {
      const src = product.getAttribute('data-preview') || '';
      const alt = product.textContent.trim();

      if (visible && active === product) {
        hide();
        return;
      }

      ignoreDismiss = true;
      show(src, alt, product, true);
      window.setTimeout(() => {
        ignoreDismiss = false;
      }, 0);
    }

    products.forEach((product) => {
      const src = () => product.getAttribute('data-preview') || '';
      const alt = () => product.textContent.trim();

      product.addEventListener('mouseenter', (event) => {
        if (!hoverFine()) {
          return;
        }

        mouseX = event.clientX;
        mouseY = event.clientY;
        show(src(), alt(), product, false);
      });

      product.addEventListener('mousemove', (event) => {
        if (!hoverFine() || !visible) {
          return;
        }

        mouseX = event.clientX;
        mouseY = event.clientY;

        if (!frame) {
          frame = window.requestAnimationFrame(placeAtCursor);
        }
      });

      product.addEventListener('mouseleave', () => {
        if (hoverFine()) {
          hide();
        }
      });

      product.addEventListener('pointerdown', (event) => {
        preload(src());
        pointer = { x: event.clientX, y: event.clientY };
      });

      product.addEventListener('pointerup', (event) => {
        if (!pointer || hoverFine()) {
          pointer = null;
          return;
        }

        const dx = event.clientX - pointer.x;
        const dy = event.clientY - pointer.y;
        pointer = null;

        if (Math.hypot(dx, dy) > TAP_SLOP) {
          return;
        }

        toggleFromTap(product);
      });

      product.addEventListener('keydown', (event) => {
        if (event.key !== 'Enter' && event.key !== ' ') {
          return;
        }

        event.preventDefault();
        toggleFromTap(product);
      });
    });

    document.addEventListener('pointerdown', (event) => {
      if (hoverFine() || ignoreDismiss || preview.hidden) {
        return;
      }

      if (event.target.closest('.' + name + '[data-preview]')) {
        return;
      }

      hide();
    });
  }

  document.querySelectorAll('[data-product-preview]').forEach(bind);
})();
