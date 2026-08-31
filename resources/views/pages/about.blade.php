@extends('layouts.site')

@section('title')
About Us — Engineered Lighting | Azoogi
@endsection

@section('description')
Azoogi designs, assembles, and optimizes architectural, commercial, and industrial lighting for projects of every scale.
@endsection

@section('bodyClass', 'about-page')

@section('chrome', 'full')

@section('topbarClass', '')
@section('logo', 'logo_white.png')

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/css/about.v-1.0.css') }}?v={{ config('app.asset_version') }}">
@endpush

@section('content')
<main class="about-main">

  <section class="about-hero">
    <div class="about-hero-media" aria-hidden="true">
      <img src="/assets/img/ai-lighting/hero.jpg" alt="" loading="eager">
    </div>
    <div class="about-hero-copy">
      <div class="kicker">About Us</div>
      <h1>Engineered Lighting.<br>Infinite Scale.<br><span>Zero Compromise.</span></h1>
    </div>
  </section>

  <section class="about-band">
    <div class="wrap about-intro reveal">
      <p>We design, assemble, and optimize architectural, commercial, and industrial lighting for projects of every scale - from bespoke residential projects to Tier 1 developments.</p>
      <div class="about-intro-action">
        <button type="button" class="btn primary" id="capabilityBtn">Request Capability Statement</button>
      </div>
    </div>
  </section>

  <section class="about-band about-band--alt" id="why">
    <div class="wrap">
      <div class="about-split-copy about-why-head reveal">
        <div class="kicker">Why Azoogi</div>
        <h2>Why Choose <span>Azoogi</span></h2>
      </div>

      <div class="about-why">
        <div class="about-why-sticky">
          <div class="about-why-visual" id="aboutWhyVisual">
            <img class="is-active" src="/assets/img/leds.webp" alt="" data-panel="0">
            <img src="/assets/img/img-1.jpg" alt="" data-panel="1" loading="lazy">
            <img src="/assets/img/drivers.webp" alt="" data-panel="2" loading="lazy">
            <img src="/assets/img/datacenter1.webp" alt="" data-panel="3" loading="lazy">
            <img src="/assets/img/acdm.jpg" alt="" data-panel="4" loading="lazy">
            <img src="/assets/img/prod-3.jpg" alt="" data-panel="5" loading="lazy">
            <div class="about-why-meta">
              <span class="about-why-count"><b id="aboutWhyCount">01</b> / 06</span>
              <span class="about-why-track"><i class="about-why-bar" id="aboutWhyBar"></i></span>
            </div>
          </div>

          <ol class="about-why-rail" id="aboutWhyRail">
            <li><button type="button" class="is-active">01</button></li>
            <li><button type="button">02</button></li>
            <li><button type="button">03</button></li>
            <li><button type="button">04</button></li>
            <li><button type="button">05</button></li>
            <li><button type="button">06</button></li>
          </ol>
        </div>

        <ol class="about-why-steps" id="aboutWhySteps">
          <li class="about-why-step">
            <span class="about-why-ghost" aria-hidden="true">01</span>
            <h3>Bespoke Custom Engineering</h3>
            <p>As an active manufacturer equipped with specialized equipment and production lines - we deliver rapid turnarounds where speed counts.</p>
          </li>
          <li class="about-why-step">
            <span class="about-why-ghost" aria-hidden="true">02</span>
            <h3>Design Integrity &amp; Schedule Optimization</h3>
            <p>Quality doesn't have to mean cost overruns. We collaborate with designers, builders and electrical contractors to optimize lighting schedules - delivering spec-grade fixtures that respect design intent, photometric standards, and commercial targets.</p>
          </li>
          <li class="about-why-step">
            <span class="about-why-ghost" aria-hidden="true">03</span>
            <h3>Global Sourcing, Local Assembly &amp; QA</h3>
            <p>We source the highest-grade raw materials and components from leading global partners, bringing them together under our own strict in-house assembly and Quality Assurance processes. Every fitting is thoroughly tested before it lands on your site.</p>
          </li>
          <li class="about-why-step">
            <span class="about-why-ghost" aria-hidden="true">04</span>
            <h3>Direct Technical &amp; On-Site Support</h3>
            <p>We don't just ship boxes - we partner with your team on the ground. From compliance reporting and photometric modeling to rapid on-site technical assistance, we ensure complete project continuity</p>
          </li>
          <li class="about-why-step">
            <span class="about-why-ghost" aria-hidden="true">05</span>
            <h3>Verified Ethical &amp; Transparent Operations</h3>
            <p>Tier 1 and civil projects demand total supply chain accountability. As a certified Sedex Plus Member, our supply chain and manufacturing operations are independently verified against strict global standards for fair labor, health and safety, business ethics, and environmental responsibility.</p>
          </li>
          <li class="about-why-step">
            <span class="about-why-ghost" aria-hidden="true">06</span>
            <h3>100% Wholesale Channel Protected</h3>
            <p>We proudly support our trade distributor network. Every project inquiry, custom schedule, and commercial order is routed and fulfilled strictly through your nominated local electrical wholesaler.</p>
          </li>
        </ol>
      </div>
    </div>
  </section>

  <section class="about-reach">
    <div class="about-reach-media" aria-hidden="true">
      <img src="/assets/img/sydney-night.jpg" alt="" loading="lazy">
    </div>
    <div class="wrap about-reach-inner reveal">
      <div class="kicker">Worldwide</div>
      <h2>International Project <span>Reach</span></h2>
      <p>For over two decades, our engineering footprint has extended far beyond Australia - delivering technical lighting packages for major developments and luxury resorts across Fiji, Vanuatu, Bali, the Maldives. With extensive export expertise, multi-currency processing, and deep experience navigating international compliance standards, we ensure seamless project delivery anywhere in the world.</p>
    </div>
  </section>

  <section class="about-band">
    <div class="wrap about-path-section">
      <div class="about-split-copy reveal">
        <div class="kicker">Audiences</div>
        <h2>Select Your<br><span>Path</span></h2>
      </div>
      <div class="about-path-list">
        <a class="about-path-row reveal" href="/audience?slug=architect-designer">
          <figure>
            <img src="/assets/img/img-1.jpg" alt="" loading="lazy">
          </figure>
          <div>
            <h3>For Architects &amp; Specifiers</h3>
            <p>Protect your design intent. Partner early with us - custom modifications, photometric testing, and spec-grade fixtures that match your vision. We can also assist smart control integration, and detailed Casambi schematics for example  - acting as your seamless backend engineering partner.</p>
          </div>
        </a>
        <a class="about-path-row reveal" href="/audience?slug=electrician-builder" style="transition-delay: 0.08s">
          <figure>
            <img src="/assets/img/datacenter2.webp" alt="" loading="lazy">
          </figure>
          <div>
            <h3>For Builders &amp; Contractors</h3>
            <p>On time and on budget. We catch potential site issues before your installer ever opens a box. Enjoy seamless communication, zero lead-time friction, and dedicated on-site technical support across all major cities (with regional options available). You get exclusive access to your own Live Project Portal to track real-time schedule progress, manage site inquiries, and monitor order status - all managed directly by your dedicated project management and in-house logistics team.</p>
          </div>
        </a>
        <a class="about-path-row reveal" href="/audience?slug=wholesaler" style="transition-delay: 0.16s">
          <figure>
            <img src="/assets/img/prod-4.jpg" alt="" loading="lazy">
          </figure>
          <div>
            <h3>For Electrical Wholesalers</h3>
            <p>100% channel protected. Guaranteed trade margins, fast quotes, and reliable local stock support.</p>
          </div>
        </a>
      </div>
    </div>
  </section>

</main>
@endsection

@push('scripts')
@verbatim
<script>
const topbar = document.getElementById('topbar');
  let lastScrolled = null;

  function updateLogos() {
    const isScrolled = window.scrollY > 40;
    document.querySelectorAll('.logo img').forEach(img => {
      if (img.closest('.topbar')) {
        img.src = isScrolled ? '/assets/logo_dark.png' : '/assets/logo_white.png';
      } else {
        img.src = '/assets/logo_dark.png';
      }
    });
  }

  const onScroll = () => {
    const isScrolled = window.scrollY > 40;
    if (isScrolled !== lastScrolled) {
      topbar.classList.toggle('solid', isScrolled);
      lastScrolled = isScrolled;
      updateLogos();
    }
  };
  window.addEventListener('scroll', onScroll, { passive: true });
  onScroll();

  const io = new IntersectionObserver((es) => {
    es.forEach(e => { if (e.isIntersecting) { e.target.classList.add('in'); io.unobserve(e.target); } });
  }, { threshold: .12 });
  document.querySelectorAll('.reveal').forEach(el => io.observe(el));

  (function () {
    const list = document.getElementById('aboutWhySteps');
    if (!list) return;

    const sticky = document.querySelector('.about-why-sticky');
    const steps = Array.from(list.querySelectorAll('.about-why-step'));
    const visuals = Array.from(document.querySelectorAll('#aboutWhyVisual img'));
    const rail = Array.from(document.querySelectorAll('#aboutWhyRail button'));
    const bar = document.getElementById('aboutWhyBar');
    const count = document.getElementById('aboutWhyCount');
    if (!sticky || !steps.length) return;

    /* Dimming only applies once JS is driving the section, so the copy stays
       fully legible if this never runs. */
    list.classList.add('is-live');

    let current = -1;

    function setActive(index) {
      if (index < 0 || index === current) return;
      current = index;
      steps.forEach(function (el, i) { el.classList.toggle('is-active', i === index); });
      visuals.forEach(function (img, i) { img.classList.toggle('is-active', i === index); });
      rail.forEach(function (btn, i) { btn.classList.toggle('is-active', i === index); });
      if (bar) bar.style.width = ((index + 1) / steps.length * 100) + '%';
      if (count) count.textContent = ('0' + (index + 1)).slice(-2);
    }

    /* Where the step being read actually sits. Side by side the whole viewport
       is readable, but once the layout stacks the pinned visual covers the top
       of the screen, so the reading area starts below it. */
    function readingCentre() {
      const viewport = window.innerHeight;
      const stickyBox = sticky.getBoundingClientRect();
      const listBox = list.getBoundingClientRect();
      const sideBySide = stickyBox.right <= listBox.left + 2 ||
        listBox.right <= stickyBox.left + 2;
      if (sideBySide) return viewport / 2;
      const top = Math.min(Math.max(stickyBox.bottom, 0), viewport * 0.6);
      return (top + viewport) / 2;
    }

    /* Measure which step is nearest that point rather than trusting observer
       entry order, so a jumped or fast scroll cannot leave a stale step
       active. */
    function sync() {
      const listBox = list.getBoundingClientRect();
      if (listBox.bottom < 0 || listBox.top > window.innerHeight) return;
      const centre = readingCentre();
      let best = 0;
      let bestDistance = Infinity;
      steps.forEach(function (el, i) {
        const box = el.getBoundingClientRect();
        const distance = Math.abs(box.top + box.height / 2 - centre);
        if (distance < bestDistance) {
          bestDistance = distance;
          best = i;
        }
      });
      setActive(best);
    }

    rail.forEach(function (btn, i) {
      btn.addEventListener('click', function () {
        const box = steps[i].getBoundingClientRect();
        window.scrollTo({
          top: box.top + window.scrollY + box.height / 2 - readingCentre(),
          behavior: 'smooth'
        });
      });
    });

    window.addEventListener('scroll', sync, { passive: true });
    window.addEventListener('resize', sync);
    window.addEventListener('orientationchange', sync);
    sync();
  })();

  (function () {
    const modal = document.getElementById('capabilityModal');
    const openBtn = document.getElementById('capabilityBtn');
    if (!modal || !openBtn) return;

    function openModal() {
      modal.hidden = false;
      document.body.style.overflow = 'hidden';
    }

    function closeModal() {
      modal.hidden = true;
      document.body.style.overflow = '';
    }

    openBtn.addEventListener('click', openModal);
    modal.querySelectorAll('[data-close-modal]').forEach((el) => {
      el.addEventListener('click', closeModal);
    });
    document.addEventListener('keydown', (e) => {
      if (e.key === 'Escape' && !modal.hidden) closeModal();
    });
  })();
</script>
@endverbatim
@endpush
