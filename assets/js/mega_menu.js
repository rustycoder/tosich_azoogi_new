// Dynamic 2-Sidebar & Accordion Mega Menu Renderer for Azoogi
(function() {
  let container = null;
  let innerWrapper = null;

  function initMegaMenu() {
    container = document.getElementById('dynamic-mega-menu');
    if (!container) return;

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

    renderLevel(dummyRoot, 0);
  }

  function renderLevel(node, columnIndex) {
    // 1. Remove all columns index > columnIndex
    const columns = innerWrapper.querySelectorAll('.mega-menu-column');
    columns.forEach(col => {
      const idx = parseInt(col.getAttribute('data-column-index'), 10);
      if (idx > columnIndex) {
        col.remove();
      }
    });

    // 2. Max 2 Sidebars rule:
    if (columnIndex >= 2 || node.type === 'product_row') {
      renderProductsView(node, columnIndex);
      return;
    }

    if (!node.children || node.children.length === 0) return;

    const firstChild = node.children[0];

    // If children are categories, render a sidebar column
    if (firstChild.type === 'category') {
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

        const switchAction = () => {
          sidebarCol.querySelectorAll('.mega-sidebar-btn').forEach(b => b.classList.remove('active'));
          btn.classList.add('active');
          renderLevel(child, columnIndex + 1);
        };

        btn.addEventListener('click', (e) => {
          e.preventDefault();
          switchAction();
        });

        sidebarCol.appendChild(btn);
      });

      innerWrapper.appendChild(sidebarCol);

      // Auto-trigger selection on first item
      const firstBtn = sidebarCol.querySelector('.mega-sidebar-btn');
      if (firstBtn) {
        firstBtn.classList.add('active');
        renderLevel(node.children[0], columnIndex + 1);
      }
    } else {
      renderProductsView(node, columnIndex);
    }
  }

  function renderProductsView(node, columnIndex) {
    const productsView = document.createElement('div');
    productsView.className = 'mega-menu-column mega-products-view active';
    productsView.setAttribute('data-column-index', columnIndex + 1);

    const viewHeader = document.createElement('div');
    viewHeader.className = 'mega-panel-header';
    viewHeader.innerHTML = `
      <h3 class="mega-panel-title">${node.name}</h3>
    `;
    productsView.appendChild(viewHeader);

    let folderNodes = [];
    let directRowNodes = [];
    
    if (node.type === 'product_row') {
      directRowNodes = [node];
    } else {
      folderNodes = (node.children || []).filter(c => c.type === 'category');
      directRowNodes = (node.children || []).filter(c => c.type === 'product_row');
    }

    if (folderNodes.length === 0 && directRowNodes.length === 0) {
      productsView.innerHTML += `<div style="padding:20px;color:var(--muted);">No products found in this category.</div>`;
      innerWrapper.appendChild(productsView);
      return;
    }

    // Apply Max 3 limiter on folders/rows inside products view
    const visibleFolders = folderNodes.slice(0, 3);
    const visibleDirectRows = directRowNodes.slice(0, 3);

    // 1. Render subfolders as collapsible accordions
    if (visibleFolders.length > 0) {
      const accordionsContainer = document.createElement('div');
      accordionsContainer.className = 'mega-accordions-container';

      visibleFolders.forEach((folderNode, gIdx) => {
        const group = document.createElement('div');
        group.className = `mega-accordion-group${gIdx === 0 ? ' open' : ''}`;

        const header = document.createElement('div');
        header.className = 'mega-accordion-header';
        header.innerHTML = `
          <span class="mega-accordion-title">${folderNode.name}</span>
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" width="14" height="14" class="mega-accordion-chevron">
            <polyline points="6 9 12 15 18 9"></polyline>
          </svg>
        `;

        const content = document.createElement('div');
        content.className = 'mega-accordion-content';

        renderFolderContent(folderNode, content);

        group.appendChild(header);
        group.appendChild(content);

        header.addEventListener('click', () => {
          group.classList.toggle('open');
        });

        accordionsContainer.appendChild(group);
      });

      productsView.appendChild(accordionsContainer);
    }

    // 2. Render direct product rows below the accordions (e.g. GIANT)
    if (visibleDirectRows.length > 0) {
      const directContainer = document.createElement('div');
      directContainer.className = 'mega-direct-products-container';
      if (visibleFolders.length > 0) {
        directContainer.style.marginTop = '0';
      }
      visibleDirectRows.forEach(rowNode => {
        renderProductRowElement(rowNode, directContainer);
      });
      productsView.appendChild(directContainer);
    }

    // 3. Range buttons if total folders or rows exceed 3
    if (folderNodes.length > 3 || directRowNodes.length > 3) {
      const targetNode = folderNodes[3] || directRowNodes[3];
      const viewAllRangeBtn = document.createElement('a');
      viewAllRangeBtn.className = 'view-all-range-btn';
      viewAllRangeBtn.href = `product-detail.html?product=${targetNode.name}`;
      viewAllRangeBtn.innerHTML = `View all ${folderNodes.length + directRowNodes.length} items in range &rarr;`;
      viewAllRangeBtn.addEventListener('click', (e) => {
        e.preventDefault();
        window.location.href = viewAllRangeBtn.href;
      });
      productsView.appendChild(viewAllRangeBtn);
    }

    innerWrapper.appendChild(productsView);
  }

  // Recursive folder structure renderer
  function renderFolderContent(currentNode, parentContainer) {
    if (currentNode.type === 'product_row') {
      renderProductRowElement(currentNode, parentContainer);
    } else if (currentNode.children) {
      const childRows = currentNode.children.filter(c => c.type === 'product_row');
      const childCats = currentNode.children.filter(c => c.type === 'category');
      
      // Limit to 3 child rows
      const visibleRows = childRows.slice(0, 3);
      visibleRows.forEach(row => {
        renderProductRowElement(row, parentContainer);
      });
      
      // Limit to 3 child subfolders
      const visibleCats = childCats.slice(0, 3);
      visibleCats.forEach(cat => {
        const subHeader = document.createElement('div');
        subHeader.className = 'mega-product-subfolder-title';
        subHeader.textContent = cat.name;
        parentContainer.appendChild(subHeader);
        
        const subContainer = document.createElement('div');
        subContainer.className = 'mega-product-subfolder-container';
        parentContainer.appendChild(subContainer);
        
        renderFolderContent(cat, subContainer);
      });

      // Range button if subfolder items exceed 3
      if (childRows.length > 3 || childCats.length > 3) {
        const viewAllRangeBtn = document.createElement('a');
        viewAllRangeBtn.className = 'view-all-range-btn';
        viewAllRangeBtn.style.marginTop = '12px';
        viewAllRangeBtn.href = `product-detail.html?product=${currentNode.name}`;
        viewAllRangeBtn.innerHTML = `View all ${childRows.length + childCats.length} items &rarr;`;
        viewAllRangeBtn.addEventListener('click', (e) => {
          e.preventDefault();
          window.location.href = viewAllRangeBtn.href;
        });
        parentContainer.appendChild(viewAllRangeBtn);
      }
    }
  }

  // Product Row & Cards generator (shows max 3 variants)
  function renderProductRowElement(rowNode, parentContainer) {
    const row = document.createElement('div');
    row.className = 'mega-product-row';
    row.innerHTML = `
      <div class="mega-product-row-title">${rowNode.name}</div>
    `;

    const grid = document.createElement('div');
    grid.className = 'mega-variant-grid';

    const variants = rowNode.variants || {};
    const vnames = Object.keys(variants);
    const visibleVnames = vnames.slice(0, 3);

    visibleVnames.forEach(vname => {
      const vdata = variants[vname];

      const imgSrc = (vdata && vdata.product_images && vdata.product_images.length > 0)
        ? vdata.product_images[0]
        : 'assets/logo_dark.png'; // Azoogi dark logo placeholder!

      const card = document.createElement('a');
      card.href = `product-detail.html?file=${encodeURIComponent(vdata.file_path || '')}`;
      card.className = 'mega-variant-card';

      const imgContainer = document.createElement('div');
      imgContainer.className = 'mega-variant-img-container loading';

      const img = document.createElement('img');
      img.className = 'mega-variant-img';
      if (imgSrc === 'assets/logo_dark.png') {
        img.className += ' placeholder-logo';
      }
      img.setAttribute('loading', 'lazy');
      img.alt = vname;

      img.onload = () => {
        imgContainer.classList.remove('loading');
      };
      img.onerror = () => {
        imgContainer.classList.remove('loading');
        img.classList.add('placeholder-logo');
        img.src = 'assets/logo_dark.png'; // Azoogi dark logo fallback!
      };
      img.src = imgSrc;

      const info = document.createElement('div');
      info.className = 'mega-variant-info';
      info.innerHTML = `
        <div class="mega-variant-name" title="${vname}">${vname}</div>
      `;

      imgContainer.appendChild(img);
      card.appendChild(imgContainer);
      card.appendChild(info);

      card.addEventListener('click', (e) => {
        e.preventDefault();
        window.location.href = card.href;
      });

      grid.appendChild(card);
    });

    // Append View All Variants card at the end of the grid if items > 3
    if (vnames.length > 3) {
      const viewAllCard = document.createElement('a');
      viewAllCard.className = 'mega-variant-card view-all-card';
      viewAllCard.href = `product-detail.html?product=${rowNode.name}`;
      viewAllCard.innerHTML = `
        <div class="view-all-card-inner">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" width="20" height="20" style="color: var(--accent);">
            <line x1="5" y1="12" x2="19" y2="12"></line>
            <polyline points="12 5 19 12 12 19"></polyline>
          </svg>
          <span style="font-size: 11px; font-weight: 600; color: var(--accent);">View all ${vnames.length}</span>
        </div>
      `;
      viewAllCard.addEventListener('click', (e) => {
        e.preventDefault();
        window.location.href = viewAllCard.href;
      });
      grid.appendChild(viewAllCard);
    }

    row.appendChild(grid);
    parentContainer.appendChild(row);
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
