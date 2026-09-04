// Dynamic 2-Sidebar & Accordion Mega Menu Renderer for Azoogi
(function() {
  let container = null;
  let innerWrapper = null;
  let productsById = {};

  function initMegaMenu() {
    container = document.getElementById('dynamic-mega-menu');
    if (!container) return;

    // Index products by ID for fast lookup
    productsById = {};
    if (typeof AZOOGI_PRODUCTS !== 'undefined' && AZOOGI_PRODUCTS.products && Array.isArray(AZOOGI_PRODUCTS.products)) {
      AZOOGI_PRODUCTS.products.forEach(p => {
        if (p && p.id && (!p.status || String(p.status).toLowerCase().trim() === 'publish')) {
          productsById[p.id] = p;
        }
      });
    }

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
    // 1. Remove all columns with data-column-index > columnIndex
    const columns = innerWrapper.querySelectorAll('.mega-menu-column');
    columns.forEach(col => {
      const idx = parseInt(col.getAttribute('data-column-index'), 10);
      if (idx > columnIndex) {
        col.remove();
      }
    });

    // 2. Max 2 Sidebars rule or if node is a product_row directly
    if (columnIndex >= 2 || node.type === 'product_row') {
      renderProductsView(node, columnIndex);
      return;
    }

    if (!node.children || node.children.length === 0) {
      renderProductsView(node, columnIndex);
      return;
    }

    const subCats = (node.children || []).filter(c => c.type === 'category');

    // If subcategories exist under this parent category, render Level 2 Sidebar
    if (subCats.length > 0) {
      const sidebarCol = document.createElement('div');
      sidebarCol.className = `mega-menu-column mega-sidebar level-${columnIndex + 1}`;
      sidebarCol.setAttribute('data-column-index', columnIndex + 1);

      subCats.forEach(child => {
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

      // Auto-trigger selection on first subcategory item
      const firstBtn = sidebarCol.querySelector('.mega-sidebar-btn');
      if (firstBtn) {
        firstBtn.classList.add('active');
        renderLevel(subCats[0], columnIndex + 1);
      }
    } else {
      // No subcategories exist: skip Level 2 Sidebar and render Main Panel directly
      renderProductsView(node, columnIndex);
    }
  }

  function extractProductCards(rowNodes) {
    const cards = [];
    rowNodes.forEach(rowNode => {
      const variants = rowNode.variants || {};
      const vnames = Object.keys(variants);
      vnames.forEach(vname => {
        let vdata = variants[vname];
        if (typeof vdata === 'string' && productsById[vdata]) {
          vdata = productsById[vdata];
        }

        if (vdata && vdata.status && String(vdata.status).toLowerCase().trim() !== 'publish') return;

        cards.push({
          vname: vname,
          vdata: vdata,
          rowName: rowNode.name
        });
      });
    });
    return cards;
  }

  function extractProductCode(source) {
    if (!source) return '';
    let raw = source.product_code || source.productCode || '';
    if (!raw && source.product_features) {
      const feats = source.product_features;
      raw = feats['Product Code'] || feats['Product code'] || '';
    }
    if (Array.isArray(raw)) {
      raw = raw.map(v => (v && typeof v === 'object' && v.value !== undefined) ? v.value : v).filter(Boolean).join(', ');
    } else if (raw && typeof raw === 'object' && raw.value !== undefined) {
      raw = raw.value;
    }
    return String(raw || '').trim();
  }

  function primaryProductCode(sku) {
    if (!sku) return '';
    return String(sku).split(',')[0].trim();
  }

  function createProductCard(cardData) {
    const { vname, vdata } = cardData;
    const rawImgSrc = (vdata && vdata.product_images && vdata.product_images.length > 0)
      ? vdata.product_images[0]
      : '/assets/bg_default.png';

    const imgSrc = getLocalImagePath(rawImgSrc, vdata ? vdata.file_path : '');
    const prodCode = primaryProductCode(extractProductCode(vdata));
    const prodCodeHtml = prodCode ? `<div class="mega-variant-code">${prodCode}</div>` : '';

    const card = document.createElement('a');
    const pId = (vdata && vdata.id) ? vdata.id : (vdata && vdata.product_name ? vdata.product_name : vname);
    card.href = `/product-detail?id=${encodeURIComponent(pId)}`;
    card.className = 'mega-variant-card';

    const imgContainer = document.createElement('div');
    imgContainer.className = 'mega-variant-img-container loading';

    const img = document.createElement('img');
    img.className = 'mega-variant-img';
    if (imgSrc === '/assets/bg_default.png' || imgSrc === '/assets/logo_dark.png') {
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
      img.src = '/assets/bg_default.png';
    };
    img.src = imgSrc;

    const info = document.createElement('div');
    info.className = 'mega-variant-info';
    info.innerHTML = `
      <div class="mega-variant-name" title="${vname}">${vname}</div>
      ${prodCodeHtml}
    `;

    imgContainer.appendChild(img);
    card.appendChild(imgContainer);
    card.appendChild(info);

    card.addEventListener('click', (e) => {
      e.preventDefault();
      window.location.href = card.href;
    });

    return card;
  }

  function renderProductsGrid(cardsList, categoryName, parentContainer, maxLimit = 12) {
    if (!cardsList || cardsList.length === 0) return;

    const grid = document.createElement('div');
    grid.className = 'mega-variant-grid';

    const visibleCards = cardsList.slice(0, maxLimit);
    visibleCards.forEach(c => {
      grid.appendChild(createProductCard(c));
    });

    parentContainer.appendChild(grid);
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

    const directCards = extractProductCards(directRowNodes);

    if (folderNodes.length === 0 && directCards.length === 0) {
      productsView.innerHTML += `<div style="padding:20px;color:var(--muted);">No products found in this category.</div>`;
      innerWrapper.appendChild(productsView);
      return;
    }

    // 1. Render subfolders as collapsible accordions
    if (folderNodes.length > 0) {
      const accordionsContainer = document.createElement('div');
      accordionsContainer.className = 'mega-accordions-container';

      folderNodes.forEach((folderNode, gIdx) => {
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

    // 2. Render direct products in a shared grid (max 12)
    if (directCards.length > 0) {
      const directContainer = document.createElement('div');
      directContainer.className = 'mega-direct-products-container';
      if (folderNodes.length > 0) {
        directContainer.style.marginTop = '16px';
      }
      renderProductsGrid(directCards, node.name, directContainer, 12);
      productsView.appendChild(directContainer);
    }

    // 3. View all range button if total items exceed 12
    const totalItemsCount = folderNodes.length + directCards.length;
    if (totalItemsCount > 12) {
      const viewAllRangeBtn = document.createElement('a');
      viewAllRangeBtn.className = 'view-all-range-btn';
      viewAllRangeBtn.href = `/products?category=${encodeURIComponent(node.name)}`;
      viewAllRangeBtn.innerHTML = `View all ${totalItemsCount} items in range &rarr;`;
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
      const cards = extractProductCards([currentNode]);
      renderProductsGrid(cards, currentNode.name, parentContainer, 12);
    } else if (currentNode.children) {
      const childRows = currentNode.children.filter(c => c.type === 'product_row');
      const childCats = currentNode.children.filter(c => c.type === 'category');
      
      const childCards = extractProductCards(childRows);
      if (childCards.length > 0) {
        renderProductsGrid(childCards, currentNode.name, parentContainer, 12);
      }
      
      childCats.forEach(cat => {
        const subHeader = document.createElement('div');
        subHeader.className = 'mega-product-subfolder-title';
        subHeader.textContent = cat.name;
        parentContainer.appendChild(subHeader);
        
        const subContainer = document.createElement('div');
        subContainer.className = 'mega-product-subfolder-container';
        parentContainer.appendChild(subContainer);
        
        renderFolderContent(cat, subContainer);
      });

      // Range button if subfolder items exceed 12
      const totalFolderCount = childCards.length + childCats.length;
      if (totalFolderCount > 12) {
        const viewAllRangeBtn = document.createElement('a');
        viewAllRangeBtn.className = 'view-all-range-btn';
        viewAllRangeBtn.style.marginTop = '12px';
        viewAllRangeBtn.href = `/products?category=${encodeURIComponent(currentNode.name)}`;
        viewAllRangeBtn.innerHTML = `View all ${totalFolderCount} items &rarr;`;
        viewAllRangeBtn.addEventListener('click', (e) => {
          e.preventDefault();
          window.location.href = viewAllRangeBtn.href;
        });
        parentContainer.appendChild(viewAllRangeBtn);
      }
    }
  }

  function getLocalImagePath(imgUrl, filePath) {
    const fallback = '/assets/bg_default.png';
    if (!imgUrl || typeof imgUrl !== 'string') return fallback;
    if (!imgUrl.startsWith('http')) {
      return imgUrl.startsWith('/') ? imgUrl : '/'+imgUrl;
    }
    const filename = imgUrl.split('/').pop().split('?')[0];
    if (!filename) return fallback;
    if (filePath) {
      const cleanFilePath = decodeURIComponent(filePath);
      const lastSlash = cleanFilePath.lastIndexOf('/');
      if (lastSlash !== -1) {
        const folderPath = cleanFilePath.substring(0, lastSlash);
        const local = `${folderPath}/${filename}`;
        return local.startsWith('/') ? local : '/'+local;
      }
    }
    return imgUrl;
  }
  window.getLocalImagePath = getLocalImagePath;

  // Run on DOM load
  document.addEventListener('DOMContentLoaded', () => {
    if (typeof AZOOGI_PRODUCTS !== 'undefined') {
      initMegaMenu();
    } else {
      console.warn("Azoogi products database is not loaded.");
    }
  });
})();
