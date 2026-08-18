/**
 * Azoogi Test Configuration Studio Engine
 * Product Details Page Style Configurator with Combined JSON Input Field (Options & Constraints).
 */

document.addEventListener('DOMContentLoaded', () => {
  // DOM Elements
  const jsonInputTextarea = document.getElementById('combined-json-input');
  const jsonStatus = document.getElementById('json-status');
  const jsonBadge = document.getElementById('json-badge');
  const alertContainer = document.getElementById('alert-container');
  
  const formatJsonBtn = document.getElementById('format-json-btn');
  const compileBtn = document.getElementById('compile-btn');
  const autoCompileToggle = document.getElementById('auto-compile-toggle');
  
  const configuratorContainer = document.getElementById('dynamic-configurator');
  const summaryListContainer = document.getElementById('selected-summary-list');

  // Application State
  let currentOptions = {};
  let currentConstraints = {};
  let selectedOptions = {}; // { [categoryKey]: selectedOptionId }

  // Default Combined JSON
  const DEFAULT_JSON = {
    "Options": {
      "Channel Configuration": [
        { "name": "Double-Sided", "id": "101" },
        { "name": "Bendable Single-Sided", "id": "102" },
        { "name": "Bendable Double-Sided", "id": "103" },
        { "name": "Single-Sided", "id": "104" },
        { "name": "Sigle-Side & Dual LED", "id": "105" }
      ],
      "Dimension": [
        { "name": "53mm (W) x 14mm (H)", "id": "106" },
        { "name": "34.4mm (W) x 14mm (H)", "id": "107" },
        { "name": "51mm (W) x 13.1mm (H)", "id": "108" },
        { "name": "52.6mm (W) x 13.7mm (H)", "id": "109" },
        { "name": "60mm (W) x 12.9mm (H)", "id": "110" },
        { "name": "69mm (W) x 13.5mm (H)", "id": "111" },
        { "name": "53mm (W) x 14.5mm (H)", "id": "112" },
        { "name": "36.19mm (W) x 14mm (H)", "id": "113" },
        { "name": "62mm (W) x 13mm (H)", "id": "114" },
        { "name": "53mm (W) x 16mm (H)", "id": "115" },
        { "name": "116.1mm (W) x 36.5mm (H)", "id": "116" },
        { "name": "60mm (W) x 14.7mm (H)", "id": "117" }
      ],
      "Finish": [
        { "name": "Silver", "id": "118" }
      ]
    },
    "Constraints": {
      "101": ["107", "108", "109", "110", "111", "112", "113", "114", "115", "116", "117"],
      "102": ["106", "108", "109", "110", "111", "112", "113", "114", "115", "116", "117"],
      "103": ["106", "107", "112", "113", "114", "115", "116"],
      "104": ["106", "107", "108", "109", "110", "111", "115", "116", "117"],
      "105": ["106", "107", "108", "109", "110", "111", "112", "113", "114", "115", "117"]
    }
  };

  // Helper Alert
  function showAlert(msg, type = 'info') {
    if (!alertContainer) return;
    alertContainer.innerHTML = `
      <div class="alert-banner ${type}">
        <span>${type === 'error' ? '❌' : type === 'success' ? '✅' : 'ℹ️'}</span>
        <div>${msg}</div>
      </div>
    `;
  }

  function clearAlert() {
    if (alertContainer) alertContainer.innerHTML = '';
  }

  // Format JSON
  function formatJSON() {
    try {
      const parsed = JSON.parse(jsonInputTextarea.value);
      jsonInputTextarea.value = JSON.stringify(parsed, null, 2);
      showAlert('JSON formatted successfully.', 'success');
      return true;
    } catch (e) {
      showAlert(`JSON Syntax Error: ${e.message}`, 'error');
      return false;
    }
  }

  // Parse Combined JSON input
  function parseJSONInput() {
    const raw = jsonInputTextarea.value.trim();
    if (!raw) {
      jsonStatus.className = 'status-indicator invalid';
      jsonStatus.innerHTML = '⚠️ Empty JSON input';
      jsonBadge.textContent = 'Empty';
      return false;
    }
    try {
      const parsed = JSON.parse(raw);
      currentOptions = parsed.Options || parsed.options || {};
      currentConstraints = parsed.Constraints || parsed.constraints || {};

      jsonStatus.className = 'status-indicator valid';
      jsonStatus.innerHTML = '✓ Valid JSON Syntax';
      
      const optCount = Object.keys(currentOptions).length;
      const constCount = Object.keys(currentConstraints).length;
      jsonBadge.textContent = `${optCount} Categories | ${constCount} Rules`;
      return true;
    } catch (err) {
      jsonStatus.className = 'status-indicator invalid';
      jsonStatus.innerHTML = `❌ Syntax Error: ${err.message}`;
      jsonBadge.textContent = 'Syntax Error';
      return false;
    }
  }

  // Initialize Default Selections (pick first valid option for each category)
  function initDefaultSelections() {
    selectedOptions = {};
    for (const optKey in currentOptions) {
      if (currentOptions.hasOwnProperty(optKey) && currentOptions[optKey].length > 0) {
        selectedOptions[optKey] = String(currentOptions[optKey][0].id);
      }
    }
  }

  // Render Product Detail Style Configurator Workspace
  function renderConfigurator() {
    if (!configuratorContainer) return;
    clearAlert();

    const isValid = parseJSONInput();
    if (!isValid || Object.keys(currentOptions).length === 0) {
      configuratorContainer.innerHTML = `
        <div style="text-align: center; padding: 40px; color: var(--muted);">
          No valid options found in JSON. Please verify your JSON format and click <strong>Generate Configuration</strong>.
        </div>
      `;
      renderSummary();
      return;
    }

    // Build Header in Product Details Style
    configuratorContainer.innerHTML = `
      <div class="reset-selection">
        <h3 style="font-family: var(--font-serif); font-size: 26px; margin: 0;">Build Your Own</h3>
        <button type="button" id="clear-selection-btn" class="btn sm" style="display:inline-flex; align-items:center; gap:6px; background:var(--card-bg); border:1px solid var(--line); color:var(--ink); padding:6px 12px; border-radius:4px; cursor:pointer; font-size:12px;">
          <svg xmlns="http://www.w3.org/2000/svg" height="14px" viewBox="0 -960 960 960" fill="currentColor">
            <path d="m336-280 144-144 144 144 56-56-144-144 144-144-56-56-144 144-144-144-56 56 144 144-144 144 56 56ZM480-80q-83 0-156-31.5T197-197q-54-54-85.5-127T80-480q0-83 31.5-156T197-763q54-54 127-85.5T480-880q83 0 156 31.5T763-763q54 54 85.5 127T880-480q0 83-31.5 156T763-197q-54 54-127 85.5T480-80Z"/>
          </svg> Clear Selection
        </button>
      </div>
    `;

    document.getElementById('clear-selection-btn').addEventListener('click', () => {
      selectedOptions = {};
      checkConstraints();
      renderSummary();
    });

    // Render Option Category Groups in Product Details Style
    for (const optKey in currentOptions) {
      if (!currentOptions.hasOwnProperty(optKey)) continue;

      const optVals = currentOptions[optKey];
      const group = document.createElement('div');
      group.className = 'config-group';
      
      const flexId = `options-flex-${optKey.replace(/[^a-zA-Z0-9]/g, '-')}`;
      group.innerHTML = `
        <div class="config-group-title">
          <span>${optKey}</span>
        </div>
        <div class="config-options-flex" id="${flexId}"></div>
      `;
      configuratorContainer.appendChild(group);

      const flex = group.querySelector(`#${flexId}`);

      optVals.forEach(val => {
        const itemObj = typeof val === 'object' ? val : { name: String(val), id: String(val) };
        const btn = document.createElement('button');
        btn.type = 'button';
        btn.className = 'config-btn';
        btn.setAttribute('data-opt-key', optKey);
        btn.setAttribute('data-opt-id', itemObj.id);
        btn.setAttribute('data-opt-name', itemObj.name);
        btn.textContent = itemObj.name;

        btn.addEventListener('click', () => {
          if (selectedOptions[optKey] === String(itemObj.id)) {
            delete selectedOptions[optKey]; // Toggle off
          } else {
            selectedOptions[optKey] = String(itemObj.id);
          }
          checkConstraints();
          renderSummary();
        });

        flex.appendChild(btn);
      });
    }

    // Apply constraint evaluation
    checkConstraints();
    renderSummary();
  }

  // Constraints Checker Engine (Matches product-detail.html exact algorithm)
  function checkConstraints() {
    const prohibitedIds = new Set();

    // Collect forbidden IDs based on current active selected IDs
    const activeIds = Object.values(selectedOptions);
    activeIds.forEach(id => {
      if (currentConstraints && currentConstraints[id]) {
        const forbiddenList = Array.isArray(currentConstraints[id]) ? currentConstraints[id] : [currentConstraints[id]];
        forbiddenList.forEach(forbiddenId => prohibitedIds.add(String(forbiddenId)));
      }
    });

    // Evaluate each category and update button states
    for (const optKey in currentOptions) {
      if (!currentOptions.hasOwnProperty(optKey)) continue;

      const optVals = currentOptions[optKey];
      const flexId = `options-flex-${optKey.replace(/[^a-zA-Z0-9]/g, '-')}`;
      const flex = document.getElementById(flexId);
      if (!flex) continue;

      let hasActiveValid = false;
      const currentSelectedId = selectedOptions[optKey];

      const btns = flex.querySelectorAll('.config-btn');
      btns.forEach(btn => {
        const optId = btn.getAttribute('data-opt-id');

        if (prohibitedIds.has(String(optId))) {
          btn.disabled = true;
          btn.classList.add('disabled');
          btn.classList.remove('active');
        } else {
          btn.disabled = false;
          btn.classList.remove('disabled');

          if (currentSelectedId !== undefined && String(optId) === String(currentSelectedId)) {
            btn.classList.add('active');
            hasActiveValid = true;
          } else {
            btn.classList.remove('active');
          }
        }
      });

      // Fallback resolution: Only if category had a selected option that became forbidden
      if (currentSelectedId !== undefined && !hasActiveValid && optVals.length > 0) {
        let fallbackVal = null;
        for (let i = 0; i < optVals.length; i++) {
          const itemObj = typeof optVals[i] === 'object' ? optVals[i] : { name: String(optVals[i]), id: String(optVals[i]) };
          if (!prohibitedIds.has(String(itemObj.id))) {
            fallbackVal = itemObj;
            break;
          }
        }
        if (fallbackVal) {
          selectedOptions[optKey] = String(fallbackVal.id);
          btns.forEach(btn => {
            const optId = btn.getAttribute('data-opt-id');
            if (String(optId) === String(fallbackVal.id)) {
              btn.classList.add('active');
            }
          });
        } else {
          delete selectedOptions[optKey];
        }
      }
    }
  }

  // Render Selected Configuration Summary List
  function renderSummary() {
    if (!summaryListContainer) return;
    summaryListContainer.innerHTML = '';

    if (Object.keys(selectedOptions).length === 0) {
      summaryListContainer.innerHTML = '<div style="color:var(--muted); font-style:italic;">No selections active</div>';
      return;
    }

    for (const catKey in selectedOptions) {
      const selectedId = selectedOptions[catKey];
      const categoryItems = currentOptions[catKey] || [];
      const foundObj = categoryItems.find(item => String(item.id || item) === String(selectedId));
      const valName = foundObj ? (foundObj.name || foundObj) : selectedId;

      const itemDiv = document.createElement('div');
      itemDiv.style.cssText = 'display:flex; justify-content:space-between; align-items:center; border-bottom:1px solid var(--rgba-line); padding-bottom:6px;';
      itemDiv.innerHTML = `
        <span style="font-weight:600; color:var(--ink);">${catKey}:</span>
        <span style="color:var(--accent); font-weight:500;">${valName} <small style="opacity:0.6; color:var(--muted);">(#${selectedId})</small></span>
      `;
      summaryListContainer.appendChild(itemDiv);
    }
  }

  // Set default JSON content
  jsonInputTextarea.value = JSON.stringify(DEFAULT_JSON, null, 2);

  // Event Listeners
  compileBtn.addEventListener('click', () => {
    initDefaultSelections();
    renderConfigurator();
    showAlert('Configuration successfully generated!', 'success');
  });

  formatJsonBtn.addEventListener('click', formatJSON);

  // Auto compile on typing if toggle is on
  jsonInputTextarea.addEventListener('input', () => {
    if (autoCompileToggle.checked) {
      renderConfigurator();
    }
  });

  // Initial render
  initDefaultSelections();
  renderConfigurator();
});
