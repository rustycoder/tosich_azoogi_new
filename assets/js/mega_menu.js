// Dynamic Recursive Multi-Sidebar Mega Menu Renderer for Azoogi
(function() {
  let container = null;
  let innerWrapper = null;

  function initMegaMenu() {
    container = document.getElementById('dynamic-mega-menu');
    if (!container) return;

    // Reset container
    container.innerHTML = '';

    innerWrapper = document.createElement('div');
    innerWrapper.className = 'mega-menu-inner';
    container.appendChild(innerWrapper);

    const tree = AZOOGI_PRODUCTS.tree;
    if (!tree || tree.length === 0) return;

    // Wrap tree in a dummy root category to start recursion
    const dummyRoot = {
      type: 'category',
      name: 'Root',
      children: tree
    };

    // Render first level
    renderLevel(dummyRoot, 0);
  }

  function renderLevel(node, columnIndex) {
    // 1. Remove all column elements with index > columnIndex
    const columns = innerWrapper.querySelectorAll('.mega-menu-column');
    columns.forEach(col => {
      const idx = parseInt(col.getAttribute('data-column-index'), 10);
      if (idx > columnIndex) {
        col.remove();
      }
    });

    if (!node.children || node.children.length === 0) return;

    const firstChild = node.children[0];

    if (firstChild.type === 'category') {
      // Create a Sidebar column
      const sidebarCol = document.createElement('div');
      sidebarCol.className = `mega-menu-column mega-sidebar level-${columnIndex + 1}`;
      sidebarCol.setAttribute('data-column-index', columnIndex + 1);

      node.children.forEach(child => {
        const btn = document.createElement('button');
        btn.className = 'mega-sidebar-btn';
        btn.innerHTML = `
          <span>${child.name}</span>
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" width="12" height="12" style="opacity: 0.5;">
            <polyline points="9 18 15 12 9 6"></polyline>
          </svg>
        `;

        // Bind interactive switches
        const switchAction = () => {
          sidebarCol.querySelectorAll('.mega-sidebar-btn').forEach(b => b.classList.remove('active'));
          btn.classList.add('active');
          renderLevel(child, columnIndex + 1);
        };

        btn.addEventListener('mouseenter', () => {
          if (window.innerWidth > 960) switchAction();
        });
        btn.addEventListener('click', (e) => {
          e.preventDefault();
          switchAction();
        });

        sidebarCol.appendChild(btn);
      });

      innerWrapper.appendChild(sidebarCol);

      // Auto-trigger hover/select on first item
      const firstBtn = sidebarCol.querySelector('.mega-sidebar-btn');
      if (firstBtn) {
        firstBtn.classList.add('active');
        renderLevel(node.children[0], columnIndex + 1);
      }
    } else if (firstChild.type === 'product_row') {
      // Create rightmost products view pane
      const productsView = document.createElement('div');
      productsView.className = 'mega-menu-column mega-products-view active';
      productsView.setAttribute('data-column-index', columnIndex + 1);

      // Add Panel Title (based on parent category name)
      const viewHeader = document.createElement('div');
      viewHeader.className = 'mega-panel-header';
      viewHeader.innerHTML = `
        <h3 class="mega-panel-title">${node.name}</h3>
      `;
      productsView.appendChild(viewHeader);

      // Iterate child product rows
      node.children.forEach(rowNode => {
        const row = document.createElement('div');
        row.className = 'mega-product-row';
        row.innerHTML = `
          <div class="mega-product-row-title">${rowNode.name}</div>
        `;

        const grid = document.createElement('div');
        grid.className = 'mega-variant-grid';

        const variants = rowNode.variants || {};
        for (const vname in variants) {
          if (!variants.hasOwnProperty(vname)) continue;
          const vdata = variants[vname];

          // Resolve thumbnail image
          const imgSrc = (vdata && vdata.product_images && vdata.product_images.length > 0)
            ? vdata.product_images[0]
            : 'assets/img/prod-1.jpg';

          // Compile specs summary
          let specString = "";
          if (vdata && vdata.product_features) {
            const volt = vdata.product_features.Voltage || vdata.product_features["Input Voltage"] || "";
            const watt = vdata.product_features.Wattage || "";
            const voltStr = Array.isArray(volt) ? volt.join(' ') : volt;
            const wattStr = Array.isArray(watt) ? watt.join(' ') : watt;
            specString = [voltStr, wattStr].filter(Boolean).join(' | ');
          }

          const card = document.createElement('a');
          card.href = `product-detail.html?product=${rowNode.name}&variant=${vname}`;
          card.className = 'mega-variant-card';
          card.innerHTML = `
            <div class="mega-variant-img" style="background-image:url(${imgSrc})"></div>
            <div class="mega-variant-info">
              <div class="mega-variant-name" title="${vname}">${vname}</div>
              <div class="mega-variant-specs">${specString}</div>
            </div>
          `;
          grid.appendChild(card);
        }

        row.appendChild(grid);
        productsView.appendChild(row);
      });

      innerWrapper.appendChild(productsView);
    }
  }

  // Run on DOM load
  document.addEventListener('DOMContentLoaded', () => {
    if (typeof AZOOGI_PRODUCTS !== 'undefined') {
      initMegaMenu();
    } else {
      console.warn("Azoogi products database is not loaded.");
    }
  });
})();
