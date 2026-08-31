(() => {
  const DATA_URL = '/assets/data/audiences.json';

  function escapeHtml(str) {
    return String(str || '')
      .replace(/&/g, '&amp;')
      .replace(/</g, '&lt;')
      .replace(/>/g, '&gt;')
      .replace(/"/g, '&quot;');
  }

  function linkifyEmails(text) {
    return escapeHtml(text).replace(
      /([a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,})/g,
      '<a href="mailto:$1">$1</a>'
    );
  }

  function accentHeading(heading, accent) {
    const h = String(heading || '');
    const a = String(accent || '');
    if (!a) return escapeHtml(h);
    const i = h.indexOf(a);
    if (i === -1) return escapeHtml(h);
    return (
      escapeHtml(h.slice(0, i)) +
      '<span>' + escapeHtml(a) + '</span>' +
      escapeHtml(h.slice(i + a.length))
    );
  }

  function accentTitle(title, accent) {
    return accentHeading(title, accent);
  }

  async function loadData() {
    const res = await fetch(DATA_URL);
    if (!res.ok) throw new Error('Failed to load audiences');
    return res.json();
  }

  function renderCard(card, index) {
    const body = (card.body || [])
      .map((p) => `<p>${linkifyEmails(p)}</p>`)
      .join('');
    const cta = card.cta
      ? `<div class="card__cta"><a href="${escapeHtml(card.cta.href)}" class="btn">${escapeHtml(card.cta.label)}</a></div>`
      : '';

    return `
      <li class="card-main" id="card_${index + 1}" style="--index: ${index + 1}">
        <div class="card__content">
          <div class="card__body">
            <h2>${accentHeading(card.heading, card.headingAccent)}</h2>
            <div class="card__copy">${body}</div>
            ${cta}
          </div>
          <figure>
            <img src="${escapeHtml(card.image)}" alt="${escapeHtml(card.heading)}" loading="lazy">
          </figure>
        </div>
      </li>
    `;
  }

  function renderAudience(audience) {
    const root = document.getElementById('audienceRoot');
    if (!root) return;

    document.title = `${audience.navLabel || audience.title} — Azoogi`;
    const meta = document.querySelector('meta[name="description"]');
    if (meta && audience.lead && audience.lead[0]) {
      meta.setAttribute('content', audience.lead[0]);
    }

    const cards = audience.cards || [];
    const leadHtml = (audience.lead || [])
      .map((p) => `<p>${linkifyEmails(p)}</p>`)
      .join('');

    root.innerHTML = `
      <section class="audience-hero">
        <div class="wrap">
          ${audience.eyebrow ? `<div class="kicker">${escapeHtml(audience.eyebrow)}</div>` : ''}
          <h1 class="h2">${accentTitle(audience.title, audience.titleAccent)}</h1>
          ${leadHtml ? `<div class="audience-lead">${leadHtml}</div>` : ''}
        </div>
      </section>

      <section class="audience-cards-wrap card-in">
        <div class="wrap-sm">
          <ul id="cards" class="audience-cards" style="--numcards: ${cards.length}">
            ${cards.map(renderCard).join('')}
          </ul>
        </div>
      </section>
    `;
  }

  async function init() {
    const root = document.getElementById('audienceRoot');
    if (!root) return;

    const params = new URLSearchParams(window.location.search);
    const slug = params.get('slug') || 'home-owner';

    try {
      const data = await loadData();
      const audience = (data.audiences || []).find((a) => a.slug === slug);
      if (!audience) {
        root.innerHTML = `
          <div class="audience-error wrap">
            <p>Audience page not found.</p>
            <a class="btn" href="/">Back to Home</a>
          </div>`;
        return;
      }
      renderAudience(audience);
    } catch (err) {
      console.error(err);
      root.innerHTML = `
        <div class="audience-error wrap">
          <p>Unable to load this page right now.</p>
          <a class="btn" href="/">Back to Home</a>
        </div>`;
    }
  }

  document.getElementById('topbar')?.classList.add('solid');
  init();
})();
