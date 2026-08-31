(() => {
  const STEP_COUNT = 9;
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
    }
  };

  const state = {
    step: 1,
    pa_ip_rating: null,
    led_category: null,
    led_subcategory: null,
    neon_type: null,
    path: null, // multi-colour | single-colour | neon
    chip_type: null,
    color_type: null,
    cct: null,
    neon_color: null,
    voltage: null,
    power: null,
    width: null,
    driver_type: null,
    controller: null
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
  const widthOptions = document.getElementById('widthOptions');

  function currentKeyForStep(step) {
    const map = {
      1: 'pa_ip_rating',
      2: state.pa_ip_rating === 'IP20' ? 'led_category'
        : state.pa_ip_rating === 'IP65' ? 'led_subcategory'
          : 'neon_type',
      3: 'chip_type',
      4: state.path === 'multi-colour' ? 'color_type'
        : state.path === 'single-colour' ? 'cct'
          : 'neon_color',
      5: 'voltage',
      6: 'power',
      7: 'width',
      8: 'driver_type',
      9: 'controller'
    };
    return map[step];
  }

  function selectionForStep(step) {
    return state[currentKeyForStep(step)];
  }

  function derivePath() {
    if (state.pa_ip_rating === 'IP20') {
      state.path = state.led_category;
    } else if (state.pa_ip_rating === 'IP65') {
      if (state.led_subcategory === 'ip65-nano-single') state.path = 'single-colour';
      else state.path = 'multi-colour';
    } else if (state.pa_ip_rating === 'IP67' || state.pa_ip_rating === 'IP68') {
      state.path = 'neon';
    } else {
      state.path = null;
    }
  }

  function clearFromStep(fromStep) {
    const keysByStep = {
      1: ['pa_ip_rating', 'led_category', 'led_subcategory', 'neon_type', 'path', 'chip_type', 'color_type', 'cct', 'neon_color', 'voltage', 'power', 'width', 'driver_type', 'controller'],
      2: ['led_category', 'led_subcategory', 'neon_type', 'path', 'chip_type', 'color_type', 'cct', 'neon_color', 'voltage', 'power', 'width', 'driver_type', 'controller'],
      3: ['chip_type', 'color_type', 'cct', 'neon_color', 'voltage', 'power', 'width', 'driver_type', 'controller'],
      4: ['color_type', 'cct', 'neon_color', 'voltage', 'power', 'width', 'driver_type', 'controller'],
      5: ['voltage', 'power', 'width', 'driver_type', 'controller'],
      6: ['power', 'width', 'driver_type', 'controller'],
      7: ['width', 'driver_type', 'controller'],
      8: ['driver_type', 'controller'],
      9: ['controller']
    };
    (keysByStep[fromStep] || []).forEach((k) => {
      if (k === 'path') state.path = null;
      else state[k] = null;
    });

    document.querySelectorAll('.options button.is-selected').forEach((btn) => {
      const key = btn.closest('.options')?.dataset.key;
      if (key && state[key] == null) btn.classList.remove('is-selected');
    });
  }

  function updateConditionals() {
    document.querySelectorAll('.conditional').forEach((el) => {
      const when = el.dataset.showWhen;
      let show = false;
      if (['IP20', 'IP65', 'IP67', 'IP68'].includes(when)) {
        show = state.pa_ip_rating === when;
      } else if (['multi-colour', 'single-colour', 'neon'].includes(when)) {
        show = state.path === when;
      }
      el.hidden = !show;
    });

    // Width filter
    if (widthOptions) {
      const isNeon = state.path === 'neon';
      widthOptions.querySelectorAll('button').forEach((btn) => {
        const kind = btn.dataset.for;
        btn.hidden = isNeon ? kind !== 'neon' : kind !== 'strip';
      });
    }

    // Chip info
    if (chipInfo && state.path === 'single-colour' && state.chip_type) {
      const info = CHIP_COPY[state.chip_type];
      chipInfo.innerHTML = info
        ? `<strong>${info.title}</strong><p>${info.body}</p>`
        : '';
    } else if (chipInfo) {
      chipInfo.innerHTML = '';
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

    updateConditionals();

    const hasSelection = Boolean(selectionForStep(state.step));
    nextBtn.disabled = !hasSelection;
    nextBtn.textContent = state.step === STEP_COUNT ? 'See Results' : 'Next';
    backBtn.hidden = state.step === 1;
    hint.textContent = hasSelection
      ? (state.step === STEP_COUNT ? 'Ready to view your setup' : 'Selection saved — continue')
      : 'Select an option to continue';

    resultsSection.hidden = true;
  }

  function selectOption(button) {
    const group = button.closest('.options');
    if (!group || group.closest('.step')?.dataset.step != state.step) return;
    const key = group.dataset.key;
    const value = button.dataset.value;
    if (!key || !value) return;

    clearFromStep(state.step);
    state[key] = value;
    if (state.step === 1 || state.step === 2) derivePath();

    group.querySelectorAll('button').forEach((b) => b.classList.remove('is-selected'));
    button.classList.add('is-selected');
    renderStep();
  }

  function buildResults() {
    const color =
      state.color_type || state.cct || state.neon_color || '—';
    const chip = (state.chip_type || 'smd').toUpperCase();
    const power = state.power || '15W/m';
    const voltage = state.voltage || '24V';
    const width = state.width || '8mm';
    const ip = state.pa_ip_rating || 'IP20';
    const leds = state.path === 'neon' ? '120 LEDs/m' : chip === 'COB' ? '544 LEDs/m' : '120 LEDs/m';
    const stripName = `${power} | ${voltage} | ${leds} | ${ip} | ${color} | ${width}`;
    const stripSku = state.path === 'neon'
      ? 'NEON-' + (state.neon_type || 'TB').slice(0, 2).toUpperCase() + '01'
      : chip === 'COB' ? 'COB019' : chip === 'CSP' ? 'CSP012' : 'SMD024';

    const driverLabel = {
      'non-dimmable': 'Non-Dimmable',
      'dimmable': '5-in-1 Dimmable',
      'dali-2': 'DALI-2'
    }[state.driver_type] || '5-in-1 Dimmable';

    const driverWatts = String(power).includes('29') ? '150W'
      : String(power).includes('22') || String(power).includes('19') ? '100W'
        : '60W';
    const driverName = `${driverWatts} | ${voltage} | IP67 | ${driverLabel}`;
    const driverSku = state.driver_type === 'dali-2' ? 'ADR012D'
      : state.driver_type === 'non-dimmable' ? 'ADR003' : 'ADR008D';

    const controllerMap = {
      none: null,
      remote: { name: 'Remote Controller | RF', sku: 'AREC003', img: 'https://azoogi.com.au/wp-content/uploads/2025/07/AREC011.jpg' },
      'wifi-rf': { name: 'Controller | CCT | RGB | RGBW | RGB+CCT | WiFi + RF', sku: 'AREC011', img: 'https://azoogi.com.au/wp-content/uploads/2025/07/AREC011.jpg' },
      'wall-panel': { name: 'Wall Panel Controller | Touch', sku: 'AREC021', img: 'https://azoogi.com.au/wp-content/uploads/2025/07/AREC011.jpg' }
    };
    const controller = controllerMap[state.controller];

    const stripImg = state.path === 'neon'
      ? 'https://azoogi.com.au/wp-content/uploads/2025/12/image434.png'
      : 'https://azoogi.com.au/wp-content/uploads/2025/12/image434.png';
    const driverImg = 'https://azoogi.com.au/wp-content/uploads/2025/07/ADR003D.jpg';

    const accessories = `
      <div class="ls-section ls-section-2">
        <div class="ls-product-card">
          <div class="ls-card-image"><img src="${driverImg}" alt="${driverName}"></div>
          <div class="ls-card-info">
            <div class="ls-card-label">DRIVER</div>
            <div class="ls-card-name">${driverName}</div>
            <div class="ls-card-sku">SKU: ${driverSku}</div>
          </div>
        </div>
        ${controller ? `
        <div class="ls-product-card">
          <div class="ls-card-image"><img src="${controller.img}" alt="${controller.name}"></div>
          <div class="ls-card-info">
            <div class="ls-card-label">STRIP CONTROLLER</div>
            <div class="ls-card-name">${controller.name}</div>
            <div class="ls-card-sku">SKU: ${controller.sku}</div>
          </div>
        </div>` : ''}
      </div>`;

    resultsGrid.innerHTML = `
      <div class="ls-section ls-section-1">
        <div class="ls-product-card">
          <div class="ls-card-image"><img src="${stripImg}" alt="${stripName}"></div>
          <div class="ls-card-info">
            <div class="ls-card-label">${state.path === 'neon' ? 'NEON LIGHT' : 'STRIP LIGHT'}</div>
            <div class="ls-card-name">${stripName}</div>
            <div class="ls-card-sku">SKU: ${stripSku}</div>
          </div>
        </div>
      </div>
      ${accessories}
      <div class="ls-section ls-section-3">
        <div class="ls-specs-list">
          <div class="ls-spec-row"><span class="ls-spec-label">LED Strip Input (V)</span><span class="ls-spec-value">${voltage}</span></div>
          <div class="ls-spec-row"><span class="ls-spec-label">Cutting Interval / Width</span><span class="ls-spec-value">${width}</span></div>
          <div class="ls-spec-row"><span class="ls-spec-label">Strip Watts</span><span class="ls-spec-value">${power}</span></div>
          <div class="ls-spec-row"><span class="ls-spec-label">LEDs/m</span><span class="ls-spec-value">${leds}</span></div>
          <div class="ls-spec-row"><span class="ls-spec-label">Colour / CCT</span><span class="ls-spec-value">${color}</span></div>
          <div class="ls-spec-row"><span class="ls-spec-label">CRI</span><span class="ls-spec-value">&gt;90</span></div>
          <div class="ls-spec-row"><span class="ls-spec-label">IP Rating</span><span class="ls-spec-value">${ip}</span></div>
          <div class="ls-spec-row"><span class="ls-spec-label">Warranty</span><span class="ls-spec-value">5 Years</span></div>
        </div>
        <div class="ls-results-actions">
          <button type="button" class="btn-add-to-cart" id="btnAddEnquiry">Add to Enquiry Cart</button>
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
      showToast('Added to enquiry cart (demo)');
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
    if (!selectionForStep(state.step)) return;
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
    if (state.step <= 1) return;
    state.step -= 1;
    renderStep();
  }

  document.getElementById('stepsContainer').addEventListener('click', (e) => {
    const btn = e.target.closest('.options button');
    if (btn) selectOption(btn);
  });

  nextBtn.addEventListener('click', goNext);
  backBtn.addEventListener('click', goBack);

  document.getElementById('topbar')?.classList.add('solid');
  renderStep();
})();
