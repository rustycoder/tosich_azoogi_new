(() => {
  const DATA_URL = 'assets/data/projects.json';

  function coverOf(p) {
    return p.cover || p.coverRemote || '';
  }

  function escapeHtml(str) {
    return String(str || '')
      .replace(/&/g, '&amp;')
      .replace(/</g, '&lt;')
      .replace(/>/g, '&gt;')
      .replace(/"/g, '&quot;');
  }

  async function loadProjects() {
    const res = await fetch(DATA_URL);
    if (!res.ok) throw new Error('Failed to load projects');
    return res.json();
  }

  function renderListing(data) {
    const introEl = document.getElementById('projectsIntro');
    const highlightsEl = document.getElementById('highlightsGrid');
    const gridEl = document.getElementById('projectsGrid');
    const countEl = document.getElementById('projectsCount');

    if (introEl && data.intro) {
      introEl.innerHTML = `
        <h1 class="h2"><span>${escapeHtml(data.intro.title)}</span></h1>
        <p class="projects-hero-lead">
          ${escapeHtml(data.intro.lead)}
          For a copy of our capability statement, contact us at
          <a href="mailto:${escapeHtml(data.intro.contactEmail)}">${escapeHtml(data.intro.contactEmail)}</a>.
        </p>
      `;
    }

    const projects = data.projects || [];
    const featured = projects
      .filter((p) => p.featured)
      .sort((a, b) => (a.featuredOrder || 99) - (b.featuredOrder || 99));

    if (highlightsEl) {
      highlightsEl.innerHTML = featured.map((p) => `
        <a class="highlight-card" href="project-detail.html?slug=${encodeURIComponent(p.slug)}">
          <img src="${escapeHtml(coverOf(p))}" alt="${escapeHtml(p.title)}" loading="lazy">
          <div class="cap">
            <small>${escapeHtml(p.tag || p.type || '')}${p.location ? ' — ' + escapeHtml(p.location) : ''}</small>
            <h3>${escapeHtml(p.title)}</h3>
          </div>
        </a>
      `).join('');
    }

    if (countEl) {
      countEl.textContent = `Showing ${projects.length} project${projects.length === 1 ? '' : 's'}`;
    }

    if (gridEl) {
      gridEl.innerHTML = projects.map((p) => `
        <a class="project-card" href="project-detail.html?slug=${encodeURIComponent(p.slug)}">
          <div class="project-card-media">
            <img src="${escapeHtml(coverOf(p))}" alt="${escapeHtml(p.title)}" loading="lazy">
          </div>
          <div class="project-card-body">
            <span class="project-tag">${escapeHtml(p.tag || p.type || 'Project')}</span>
            <h3>${escapeHtml(p.title)}</h3>
          </div>
        </a>
      `).join('');
    }
  }

  function renderDetail(data) {
    const params = new URLSearchParams(window.location.search);
    const slug = params.get('slug');
    const root = document.getElementById('projectDetail');
    if (!root) return;

    const project = (data.projects || []).find((p) => p.slug === slug || p.id === slug);
    if (!project) {
      root.innerHTML = `
        <div class="wrap">
          <div class="projects-error">
            <p>Project not found.</p>
            <a class="btn" href="projects.html">Back to Projects</a>
          </div>
        </div>`;
      return;
    }

    document.title = `${project.title} — Azoogi Projects`;

    const gallery = (project.gallery && project.gallery.length
      ? project.gallery
      : [coverOf(project)]).slice(0, 6);

    root.innerHTML = `
      <section class="project-detail-hero">
        <div class="wrap">
          <a class="project-back" href="projects.html">&larr; All Projects</a>
          <div class="meta-bar">
            <span class="project-tag">${escapeHtml(project.tag || project.type || '')}</span>
            ${project.location ? `<span class="meta-location">${escapeHtml(project.location)}</span>` : ''}
          </div>
          <h1>${escapeHtml(project.title)}</h1>
          <div class="cover" style="margin-top:24px">
            <img src="${escapeHtml(coverOf(project))}" alt="${escapeHtml(project.title)}">
          </div>
        </div>
      </section>

      <section class="project-info">
        <div class="wrap">
          <div class="project-info-grid">
            <div>
              <h2>Project Overview</h2>
              <div class="project-meta-rows">
                ${project.location ? `<div class="project-meta-row"><span class="label">Location:</span><span class="value">${escapeHtml(project.location)}</span></div>` : ''}
                ${project.type ? `<div class="project-meta-row"><span class="label">Type:</span><span class="value">${escapeHtml(project.type)}</span></div>` : ''}
                ${project.completed ? `<div class="project-meta-row"><span class="label">Completed:</span><span class="value">${escapeHtml(project.completed)}</span></div>` : ''}
              </div>
            </div>
            <div class="project-description">
              <p>${escapeHtml(project.description || project.summary || '')}</p>
            </div>
          </div>
          <div class="project-gallery">
            ${gallery.map((src) => `
              <div class="image">
                <img src="${escapeHtml(src)}" alt="${escapeHtml(project.title)}" loading="lazy">
              </div>
            `).join('')}
          </div>
        </div>
      </section>
    `;
  }

  async function init() {
    const page = document.body.dataset.page;
    try {
      const data = await loadProjects();
      if (page === 'projects-list') renderListing(data);
      if (page === 'project-detail') renderDetail(data);
    } catch (err) {
      console.error(err);
      const target = document.getElementById('projectsIntro')
        || document.getElementById('projectDetail')
        || document.body;
      const msg = document.createElement('div');
      msg.className = 'projects-error';
      msg.textContent = 'Unable to load projects right now.';
      target.appendChild(msg);
    }
  }

  document.getElementById('topbar')?.classList.add('solid');
  init();
})();
