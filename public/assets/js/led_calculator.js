(() => {
  const STEP_COUNT = 9;
  const CATALOG = window.AZOOGI_LED_CALC || { lights: [], drivers: [], controllers: [] };
  const LIGHTS = Array.isArray(CATALOG.lights) ? CATALOG.lights : [];
  const DRIVERS = Array.isArray(CATALOG.drivers) ? CATALOG.drivers : [];
  const CONTROLLERS = Array.isArray(CATALOG.controllers) ? CATALOG.controllers : [];

  const CHIP_COPY = {
    csp: {
      title: 'CSP (Chip Scale Package)',
      body: 'Ultra-compact chips for high density and smooth light. Ideal for tight profiles and premium architectural installs.'
    },
    cob: {
      title: 'COB (Chip on Board)',
      body: 'Continuous, dotted-free glow with high CRI. Excellent for cove lighting, kitchens and feature lines.'
    },
    smd: {
      title: 'SMD (Surface Mounted Diode)',
      body: 'A flexible, budget-friendly option suited to general lighting or decorative use across many brightness levels.'
    },
    neon: {
      title: 'NEON Integrated',
      body: 'Flexible encapsulated linear neon for indoor and outdoor architectural contours.'
    }
  };

  const IP_LABELS = {
    IP20: ['Indoor and Dry', 'IP20'],
    IP54: ['Semi-outdoor', 'IP54'],
    IP65: ['Indoor/Outdoor Shelter', 'IP65'],
    IP67: ['Outdoor', 'IP67'],
    IP68: ['Underwater', 'IP68'],
    IP69: ['Extreme / Washdown', 'IP69']
  };

  const GROUP_LABELS = {
    'strip-single': 'LED Strips (Single Colour)',
    'strip-multi': 'LED Strips (Multi Colour)',
    'strip-nano-single': 'Nano-coated (Single Colour)',
    'strip-nano-multi': 'Nano-coated (Multi Colour)',
    'neon-side': 'Side Bend Neon',
    'neon-top': 'Top Bend Neon',
    'neon-360': '360° / Dual Bend Neon'
  };

  const COLOR_HINTS = {
    '1600K': 'Ultra Warm',
    '2200K': 'Very Warm',
    '2400K': 'Warm Glow',
    '2700K': 'Warm White',
    '3000K': 'Soft White',
    '4000K': 'Natural White',
    '5000K': 'Cool White',
    '5300K': 'Cool White',
    '5700K': 'Daylight',
    '6000K': 'Cool White',
    RGB: 'Multi Colour',
    RGBW: 'Multi Colour + White',
    CCT: 'Tunable White',
    'RGB+CCT': 'RGB + Tunable White'
  };

  const DRIVER_LABELS = {
    'non-dimmable': 'Non-Dimmable',
    dimmable: 'Dimmable (5-in-1)',
    'dali-2': 'DALI-2'
  };

  const CHIP_LABELS = {
    csp: 'CSP',
    cob: 'COB',
    smd: 'SMD',
    neon: 'NEON Integrated'
  };

  const state = {
    step: 1,
    ip: null,
    group: null,
    chip: null,
    color: null,
    voltage: null,
    power: null,
    width: null,
    driver_type: null,
    controller: 'none'
  };

  const stepList = document.getElementById('calcStepList');
  const steps = [...document.querySelectorAll('.step')];
  const nextBtn = document.getElementById('calcNext');
  const backBtn = document.getElementById('calcBack');
  const hint = document.getElementById('calcNavHint');
  const selectorSection = document.getElementById('led-selector');
  const resultsSection = document.getElementById('led-selector-results');
  const resultsGrid = document.getElementById('resultsGrid');
  const chipInfo = document.getElementById('chipInfoDynamic');

  function unique(values) {
    return [...new Set(values.filter(Boolean))];
  }

  function ipCodes(light) {
    return (light.ips || []).map((item) => item.code);
  }

  function groupsFor(light, ip) {
    const entry = (light.ips || []).find((item) => item.code === ip);
    if (!entry) {
      return [];
    }

    if (light.family === 'neon') {
      return [light.neon_type || 'neon-side'];
    }

    const prefix = entry.nano ? 'strip-nano-' : 'strip-';
    const groups = [];
    if (light.has_single) {
      groups.push(prefix + 'single');
    }
    if (light.has_multi) {
      groups.push(prefix + 'multi');
    }
    return groups;
  }

  function isMultiGroup(group) {
    return String(group || '').endsWith('-multi');
  }

  function matchingLights(upToStep) {
    return LIGHTS.filter((light) => {
      if (upToStep >= 1 && state.ip && !ipCodes(light).includes(state.ip)) {
        return false;
      }
      if (upToStep >= 2 && state.group && !groupsFor(light, state.ip).includes(state.group)) {
        return false;
      }
      if (upToStep >= 3 && state.chip && light.chip !== state.chip) {
        return false;
      }
      if (upToStep >= 4 && state.color) {
        const colors = isMultiGroup(state.group) ? (light.color_types || []) : (light.ccts || []).concat(light.family === 'neon' ? (light.color_types || []) : []);
        if (light.family === 'neon') {
          if (!(light.ccts || []).includes(state.color) && !(light.color_types || []).includes(state.color)) {
            return false;
          }
        } else if (!colors.includes(state.color)) {
          return false;
        }
      }
      if (upToStep >= 5 && state.voltage && !(light.voltages || []).includes(state.voltage)) {
        return false;
      }
      if (upToStep >= 6 && state.power && !(light.powers || []).includes(state.power)) {
        return false;
      }
      if (upToStep >= 7 && state.width && (light.widths || []).length > 0 && !(light.widths || []).includes(state.width)) {
        return false;
      }
      return true;
    });
  }

  function optionsForStep(step) {
    const pool = matchingLights(step - 1);

    if (step === 1) {
      return unique(LIGHTS.flatMap(ipCodes)).sort().map((code) => {
        const label = IP_LABELS[code] || [code, code];
        return { value: code, html: `${label[0]}<br><small>${label[1]}</small>` };
      });
    }

    if (step === 2) {
      return unique(pool.flatMap((light) => groupsFor(light, state.ip)))
        .sort()
        .map((value) => ({ value, html: GROUP_LABELS[value] || value }));
    }

    if (step === 3) {
      return unique(pool.map((light) => light.chip)).map((value) => ({
        value,
        html: CHIP_LABELS[value] || value.toUpperCase()
      }));
    }

    if (step === 4) {
      const values = unique(pool.flatMap((light) => {
        if (light.family === 'neon') {
          return (light.ccts || []).concat(light.color_types || []);
        }
        return isMultiGroup(state.group) ? (light.color_types || []) : (light.ccts || []);
      }));
      return values.map((value) => {
        const hintText = COLOR_HINTS[value];
        return { value, html: hintText ? `${value}<br><small>${hintText}</small>` : value };
      });
    }

    if (step === 5) {
      return unique(pool.flatMap((light) => light.voltages || [])).sort().map((value) => ({ value, html: value }));
    }

    if (step === 6) {
      return unique(pool.flatMap((light) => light.powers || []))
        .sort((a, b) => parseFloat(a) - parseFloat(b))
        .map((value) => ({ value, html: value }));
    }

    if (step === 7) {
      const widths = unique(pool.flatMap((light) => light.widths || []));
      if (widths.length === 0) {
        return [{ value: 'standard', html: 'Standard size' }];
      }
      return widths.map((value) => ({ value, html: value.replace('x', ' × ') }));
    }

    if (step === 8) {
      const types = unique(DRIVERS.map((driver) => driver.type));
      const fallback = ['non-dimmable', 'dimmable', 'dali-2'];
      return (types.length ? types : fallback).map((value) => ({
        value,
        html: DRIVER_LABELS[value] || value
      }));
    }

    const extras = CONTROLLERS.map((item) => ({
      value: item.id,
      html: item.name
    }));
    return [{ value: 'none', html: 'None' }].concat(extras);
  }

  function currentKeyForStep(step) {
    return {
      1: 'ip',
      2: 'group',
      3: 'chip',
      4: 'color',
      5: 'voltage',
      6: 'power',
      7: 'width',
      8: 'driver_type',
      9: 'controller'
    }[step];
  }

  function clearFromStep(fromStep) {
    const keys = ['ip', 'group', 'chip', 'color', 'voltage', 'power', 'width', 'driver_type', 'controller'];
    keys.slice(fromStep - 1).forEach((key) => {
      state[key] = key === 'controller' ? 'none' : null;
    });
  }

  function fillOptions(step) {
    const host = document.getElementById('step' + step + 'Options');
    if (!host) {
      return;
    }

    const key = currentKeyForStep(step);
    const options = optionsForStep(step);
    const columns = options.length >= 4 ? 'options-4' : options.length === 3 ? 'options-3' : options.length === 2 ? 'options-2' : 'options-row';
    host.className = 'options ' + columns;
    host.dataset.key = key;
    host.innerHTML = options.map((option) => {
      const selected = state[key] === option.value ? ' is-selected' : '';
      return `<button type="button" data-value="${option.value}" class="${selected.trim()}">${option.html}</button>`;
    }).join('');

    if (step === 3 && chipInfo) {
      const info = CHIP_COPY[state.chip];
      chipInfo.innerHTML = info ? `<strong>${info.title}</strong><p>${info.body}</p>` : '';
    }

    if (step === 4) {
      const title = document.getElementById('step4Title');
      if (title) {
        title.textContent = isMultiGroup(state.group) ? 'Select colour type' : 'Select colour temperature';
      }
    }
  }

  function renderStep() {
    steps.forEach((stepEl) => {
      const n = Number(stepEl.dataset.step);
      const active = n === state.step;
      stepEl.hidden = !active;
      stepEl.classList.toggle('is-current', active);
    });

    stepList.querySelectorAll('li').forEach((li) => {
      const n = Number(li.dataset.stepLabel);
      li.classList.toggle('is-active', n === state.step);
      li.classList.toggle('is-done', n < state.step);
      li.setAttribute('aria-current', n === state.step ? 'step' : 'false');
    });

    fillOptions(state.step);

    const options = optionsForStep(state.step);
    if (options.length === 1 && !state[currentKeyForStep(state.step)]) {
      state[currentKeyForStep(state.step)] = options[0].value;
      fillOptions(state.step);
    }

    const hasSelection = Boolean(state[currentKeyForStep(state.step)]) || state.step === 9;
    nextBtn.disabled = !hasSelection || (state.step < 8 && matchingLights(state.step).length === 0 && state.step !== 8);
    nextBtn.textContent = state.step === STEP_COUNT ? 'See Results' : 'Next';
    backBtn.hidden = state.step === 1;
    hint.textContent = LIGHTS.length === 0
      ? 'No catalogue products are available yet. Run a product sync.'
      : hasSelection
        ? (state.step === STEP_COUNT ? 'Ready to view your setup' : 'Selection saved — continue')
        : 'Select an option to continue';

    resultsSection.hidden = true;
  }

  function selectOption(button) {
    const group = button.closest('.options');
    if (!group || group.closest('.step')?.dataset.step != state.step) {
      return;
    }
    const key = group.dataset.key;
    const value = button.dataset.value;
    if (!key || !value) {
      return;
    }

    clearFromStep(state.step);
    state[key] = value;
    renderStep();
  }

  function pickLight() {
    return matchingLights(7)[0] || null;
  }

  function pickDriver(light) {
    const voltage = state.voltage || (light && light.voltages[0]) || '24V';
    const stripWatts = parseFloat(state.power || '0') || 0;
    const typed = DRIVERS.filter((driver) => driver.type === state.driver_type);
    const pool = (typed.length ? typed : DRIVERS).filter((driver) => {
      return !driver.voltages || driver.voltages.length === 0 || driver.voltages.includes(voltage);
    });
    const ranked = pool.slice().sort((a, b) => {
      const aFit = (a.watts || []).some((watt) => watt >= stripWatts) ? 0 : 1;
      const bFit = (b.watts || []).some((watt) => watt >= stripWatts) ? 0 : 1;
      return aFit - bFit;
    });
    return ranked[0] || null;
  }

  function pickController() {
    if (!state.controller || state.controller === 'none') {
      return null;
    }
    return CONTROLLERS.find((item) => item.id === state.controller) || null;
  }

  function productCard(label, product, extraName) {
    if (!product) {
      return '';
    }
    const name = extraName || product.name;
    const sku = product.sku ? `SKU: ${product.sku}` : '';
    return `
      <div class="ls-product-card">
        <div class="ls-card-image"><img src="${product.image}" alt="${name}"></div>
        <div class="ls-card-info">
          <div class="ls-card-label">${label}</div>
          <div class="ls-card-name">${name}</div>
          <div class="ls-card-sku">${sku}</div>
        </div>
      </div>`;
  }

  function buildResults() {
    const light = pickLight();
    const driver = pickDriver(light);
    const controller = pickController();

    if (!light) {
      resultsGrid.innerHTML = '<p class="step-hint">No catalogue product matches this combination. Go back and try another option.</p>';
      return;
    }

    const color = state.color || '—';
    const voltage = state.voltage || (light.voltages[0] || '24V');
    const power = state.power || (light.powers[0] || '—');
    const width = state.width && state.width !== 'standard' ? state.width.replace('x', ' × ') : (light.widths[0] || '—');
    const lightName = [light.name, power, voltage, state.ip, color].filter(Boolean).join(' | ');
    const driverWatts = driver && driver.watts && driver.watts.length
      ? driver.watts.find((watt) => watt >= (parseFloat(power) || 0)) || driver.watts[0]
      : null;
    const driverName = driver
      ? [driver.name, driverWatts ? driverWatts + 'W' : null, voltage, driver.ip, DRIVER_LABELS[driver.type]]
        .filter(Boolean).join(' | ')
      : '';

    resultsGrid.innerHTML = `
      <div class="ls-section ls-section-1">
        ${productCard(light.family === 'neon' ? 'NEON LIGHT' : 'STRIP LIGHT', light, lightName)}
      </div>
      <div class="ls-section ls-section-2">
        ${driver ? productCard('DRIVER', driver, driverName) : '<p class="step-hint">No matching driver in the catalogue.</p>'}
        ${controller ? productCard('STRIP CONTROLLER', controller) : ''}
      </div>
      <div class="ls-section ls-section-3">
        <div class="ls-specs-list">
          <div class="ls-spec-row"><span class="ls-spec-label">Product</span><span class="ls-spec-value">${light.name}</span></div>
          <div class="ls-spec-row"><span class="ls-spec-label">LED Strip Input (V)</span><span class="ls-spec-value">${voltage}</span></div>
          <div class="ls-spec-row"><span class="ls-spec-label">Cutting Interval / Width</span><span class="ls-spec-value">${width}</span></div>
          <div class="ls-spec-row"><span class="ls-spec-label">Strip Watts</span><span class="ls-spec-value">${power}</span></div>
          <div class="ls-spec-row"><span class="ls-spec-label">Colour / CCT</span><span class="ls-spec-value">${color}</span></div>
          <div class="ls-spec-row"><span class="ls-spec-label">Chip</span><span class="ls-spec-value">${(CHIP_LABELS[light.chip] || light.chip).toUpperCase()}</span></div>
          <div class="ls-spec-row"><span class="ls-spec-label">IP Rating</span><span class="ls-spec-value">${state.ip}${state.group && state.group.includes('nano') ? ' Nano' : ''}</span></div>
          <div class="ls-spec-row"><span class="ls-spec-label">CRI</span><span class="ls-spec-value">${light.cri || '>90'}</span></div>
          <div class="ls-spec-row"><span class="ls-spec-label">Warranty</span><span class="ls-spec-value">${light.warranty || '5 Years'}</span></div>
        </div>
        <div class="ls-results-actions">
          <button type="button" class="btn-add-to-cart" id="btnAddEnquiry">Add to Enquiry Cart</button>
          <a class="btn-edit" id="btnViewProduct" href="${light.url}">View Product</a>
          <button type="button" class="btn-edit" id="btnEdit">Make Edits</button>
        </div>
      </div>
    `;

    document.getElementById('btnEdit').addEventListener('click', () => {
      resultsSection.hidden = true;
      selectorSection.hidden = false;
      selectorSection.scrollIntoView({ behavior: 'smooth', block: 'start' });
    });

    document.getElementById('btnAddEnquiry').addEventListener('click', () => {
      const add = window.AzoogiQuote && window.AzoogiQuote.add;
      if (typeof add === 'function') {
        add({ id: light.id, name: light.name, sku: light.sku, image: light.image, url: light.url });
        if (driver) {
          add({ id: driver.id, name: driver.name, sku: driver.sku, image: driver.image, url: driver.url });
        }
        if (controller) {
          add({ id: controller.id, name: controller.name, sku: controller.sku, image: controller.image, url: controller.url });
        }
      }
      if (typeof window.siteToast === 'function') {
        window.siteToast('Added to enquiry cart');
      } else {
        showToast('Added to enquiry cart');
      }
    });
  }

  function showToast(message) {
    let toast = document.querySelector('.ls-toast');
    if (!toast) {
      toast = document.createElement('div');
      toast.className = 'ls-toast';
      document.body.appendChild(toast);
    }
    toast.textContent = message;
    toast.classList.add('is-show');
    clearTimeout(showToast._t);
    showToast._t = setTimeout(() => toast.classList.remove('is-show'), 2400);
  }

  function goNext() {
    if (!state[currentKeyForStep(state.step)] && state.step !== 9) {
      return;
    }
    if (state.step === STEP_COUNT) {
      buildResults();
      selectorSection.hidden = true;
      resultsSection.hidden = false;
      resultsSection.scrollIntoView({ behavior: 'smooth', block: 'start' });
      return;
    }
    state.step += 1;
    renderStep();
  }

  function goBack() {
    if (state.step <= 1) {
      return;
    }
    state.step -= 1;
    renderStep();
  }

  document.getElementById('stepsContainer').addEventListener('click', (e) => {
    const btn = e.target.closest('.options button');
    if (btn) {
      selectOption(btn);
    }
  });

  nextBtn.addEventListener('click', goNext);
  backBtn.addEventListener('click', goBack);

  document.getElementById('topbar')?.classList.add('solid');
  renderStep();
})();
