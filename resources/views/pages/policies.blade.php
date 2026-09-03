@extends('layouts.site')

@section('title')
Policies — Azoogi Pty Ltd
@endsection

@section('description')
Azoogi Pty Ltd Privacy Policy, Terms & Conditions, Warranty & Returns Policy, and Anti-Modern Slavery Policy Statement.
@endsection

@section('bodyClass', 'legal-page')

@section('chrome', 'full')

@section('topbarClass', 'solid')
@section('logo', 'logo_dark.png')

@push('styles')
<link rel="stylesheet" href="{{ versioned_asset('assets/css/legal.css') }}">
@endpush

@section('content')
<main class="legal-main">
  <div class="wrap legal-page-wrap">
    <div class="legal-hero">
      <div class="kicker legal-kicker">Legal</div>
      <h1 class="h2 legal-title">Policies &amp; <span>Terms</span></h1>
      <p class="legal-intro">Privacy, website terms, warranty information, and modern slavery policy for Azoogi Pty Ltd trade clients across Australia.</p>
    </div>

    <div class="legal-shell">
      <nav class="legal-nav" aria-label="Policy sections">
        <a href="#privacy">Privacy</a>
        <a href="#terms">Terms</a>
        <a href="#warranty">Warranty &amp; Returns</a>
        <a href="#modern-slavery">Modern Slavery</a>
      </nav>

      <div class="legal-wrap">
    <!-- Privacy -->
    <article class="legal-block" id="privacy">
      <h2 class="legal-block-title">Privacy Policy</h2>
      <p class="legal-block-lead">At Azoogi Pty Ltd, we keep things simple, transparent, and hassle-free. We value your trust and are committed to protecting your privacy while providing high-quality commercial and architectural lighting solutions to our trade clients across Australia. This Privacy Policy explains how we handle your personal and business information when you visit our website, use our trade portal, or deal with our team.</p>

      <section class="legal-section">
        <h3>1. Who We Are &amp; How Our Site Works</h3>
        <p>Azoogi is a trade-only wholesale supplier. While our website displays our product catalog for general browsing, online pricing and stock availability are strictly restricted to approved commercial and wholesale trade account holders. We do not operate an online retail e-commerce store—all orders and quotes are placed directly via email or through your dedicated account representative.</p>
      </section>

      <section class="legal-section">
        <h3>2. Information We Collect</h3>
        <p>We only collect information that is necessary to verify your business, manage your trade account, and support your commercial lighting needs. This includes:</p>
        <ul>
          <li><strong>Trade Account Information:</strong> Company name, ABN, contact person name, email address, phone number, and physical/delivery address when you apply for or use a trade login.</li>
          <li><strong>Communications:</strong> Details shared when you contact us via email, phone, or website contact forms regarding product inquiries, stock, or technical support.</li>
          <li><strong>Website &amp; Analytics Data:</strong> Technical information automatically collected when you visit our site, including IP address, browser type, pages viewed, and general location data.</li>
        </ul>
      </section>

      <section class="legal-section">
        <h3>3. How We Use Your Information &amp; Trade Mailing List</h3>
        <p>We use your information exclusively for legitimate business purposes, including:</p>
        <ul>
          <li>Verifying and setting up approved trade account logins to view pricing and live stock levels.</li>
          <li>Processing and fulfilling orders placed with our representatives or via email.</li>
          <li>Providing technical guidance, datasheets, warranty support, and product updates.</li>
          <li><strong>Trade Mailing List:</strong> When you send us an inquiry, request a quote, or register for a trade account, your contact email will be included on our trade communication list. We use this to share important updates regarding new stock, product launches, technical updates, and promotional offers relevant to your business. You can easily opt out at any time by clicking the “Unsubscribe” link in any email or by contacting <a href="mailto:support@azoogi.com">support@azoogi.com</a>.</li>
          <li>Improving our website performance and trade portal usability.</li>
          <li>Complying with legal, tax, and reporting obligations under Australian law.</li>
        </ul>
        <p>We will never sell, rent, or trade your personal or business information to third parties.</p>
      </section>

      <section class="legal-section">
        <h3>4. Cookies &amp; Web Analytics</h3>
        <p>Our website uses cookies and analytical tools (such as Google Analytics) to help us understand how trade clients navigate our product catalog. These tools use standard web identifiers to collect anonymous data about site usage.</p>
        <p>You can choose to disable cookies in your web browser settings at any time, though some features of the trade login portal may rely on cookies to remain signed in securely.</p>
      </section>

      <section class="legal-section">
        <h3>5. Data Security &amp; Storage</h3>
        <p>We take sensible, industry-standard security measures to safeguard your electronic data and account credentials against unauthorized access, loss, or misuse. Access to your trade portal account is secured by your login credentials, which should be kept confidential by your authorized team members.</p>
      </section>

      <section class="legal-section">
        <h3>6. Accessing &amp; Updating Your Information</h3>
        <p>You have the right to access, update, or request the deletion of the business contact information we hold for you. If you need to update account details, unsubscribe from communications, or remove former staff members from your trade account access, simply send an email to <a href="mailto:support@azoogi.com">support@azoogi.com</a>.</p>
      </section>

      <section class="legal-section">
        <h3>7. Contact Us</h3>
        <p>If you have any questions, concerns, or requests regarding this Privacy Policy or how your data is managed, please reach out to our team:</p>
        <div class="legal-contact">
          <p><strong>Azoogi Pty Ltd</strong></p>
          <p>Unit 47, 10-12 Girwah Place<br>Matraville NSW 2036, Australia</p>
          <p>Email: <a href="mailto:support@azoogi.com">support@azoogi.com</a></p>
        </div>
      </section>
    </article>

    <!-- Terms -->
    <article class="legal-block" id="terms">
      <h2 class="legal-block-title">Terms &amp; Conditions</h2>
      <p class="legal-block-lead">Welcome to Azoogi Pty Ltd. We aim to be straightforward, friendly, and easy to deal with while delivering top-quality commercial and architectural lighting solutions to our Australian trade partners. These Terms &amp; Conditions outline how our website, trade portal, and supply services operate.</p>

      <section class="legal-section">
        <h3>1. Trade Portal &amp; Account Access</h3>
        <p>Azoogi is a wholesale trade supplier. While our website displays our catalog publicly, pricing and stock visibility are strictly reserved for approved commercial and wholesale trade account holders.</p>
        <ul>
          <li>Trade portal credentials are issued exclusively to approved business clients.</li>
          <li>Account holders are responsible for keeping their login credentials secure and restricting access to authorized personnel.</li>
          <li>Azoogi reserves the right to suspend or terminate portal access if account details are misused or shared outside your organization.</li>
        </ul>
      </section>

      <section class="legal-section">
        <h3>2. Ordering &amp; Pricing</h3>
        <p>Our website and portal function as a live product catalog and resource hub—not an online e-commerce checkout.</p>
        <ul>
          <li><strong>Order Placement:</strong> Orders and formal price inquiries are placed directly via email with our sales team (<a href="mailto:sales@azoogi.com">sales@azoogi.com</a>) or through your designated Azoogi account representative.</li>
          <li><strong>Pricing &amp; Stock:</strong> Pricing and stock availability displayed on the trade portal are provided in good faith but remain subject to final confirmation at the time an official purchase order is submitted and processed.</li>
        </ul>
      </section>

      <section class="legal-section">
        <h3>3. Delivery, Shortages &amp; Transit Discrepancies</h3>
        <p>We work hard to ensure every shipment arrives complete and undamaged. When receiving goods, please inspect your delivery promptly.</p>
        <ul>
          <li><strong>14-Day Shortage Notification:</strong> Any claims regarding missing items, delivery shortages, incorrect items received, or transit damage must be submitted in writing to <a href="mailto:support@azoogi.com">support@azoogi.com</a> within 14 days of delivery.</li>
          <li>Claims made outside this 14-day window cannot be accepted, and shipment contents will be deemed accepted as delivered.</li>
        </ul>
      </section>

      <section class="legal-section">
        <h3>4. Change of Mind &amp; Non-Faulty Returns</h3>
        <p>We understand that project requirements can change. If you need to return standard stocked items due to an ordering error or change of mind:</p>
        <ul>
          <li><strong>Timeframe:</strong> Written return requests must be submitted within 30 days of invoice date.</li>
          <li><strong>Restocking Fee:</strong> Approved non-faulty returns are subject to a 25% restocking fee to cover processing, testing, and restocking.</li>
          <li><strong>Condition:</strong> Returned goods must be in brand-new, saleable condition, in their original undamaged packaging, and returned at the purchaser’s expense.</li>
          <li><strong>Exceptions:</strong> Custom-made, special build, or clearance products are non-returnable unless faulty.</li>
        </ul>
      </section>

      <section class="legal-section">
        <h3>5. Major Projects &amp; Custom Supply Agreements</h3>
        <p>For major commercial developments, custom lighting builds, or specialized supply contracts, specific contractual terms agreed upon in writing between Azoogi Pty Ltd and the client will supersede these general website terms where any conflict arises.</p>
      </section>

      <section class="legal-section">
        <h3>6. Intellectual Property</h3>
        <p>All content on this website—including product images, technical datasheets, CAD/IES files, logos, and written text—remains the property of Azoogi Pty Ltd. Approved trade clients may download and use these resources solely for the purpose of specifying, installing, or reselling Azoogi products.</p>
      </section>

      <section class="legal-section">
        <h3>7. Contact Information</h3>
        <p>If you have any questions regarding these terms, your account, or an order, please contact our team:</p>
        <div class="legal-contact">
          <p><strong>Azoogi Pty Ltd</strong></p>
          <p>Unit 47, 10-12 Girwah Place<br>Matraville NSW 2036, Australia</p>
          <p>Email: <a href="mailto:support@azoogi.com">support@azoogi.com</a> | Sales: <a href="mailto:sales@azoogi.com">sales@azoogi.com</a></p>
        </div>
      </section>
    </article>

    <!-- Warranty -->
    <article class="legal-block" id="warranty">
      <h2 class="legal-block-title">Warranty &amp; Returns Policy</h2>
      <p class="legal-block-lead">At Azoogi Pty Ltd, we stand behind the quality and reliability of our lighting products. We strive to offer a fair, hassle-free warranty process to keep your projects running smoothly.</p>

      <section class="legal-section">
        <h3>1. Standard Warranty Coverage</h3>
        <p>Azoogi products come with structural and operational manufacturer warranties to protect against material defects and component failures under normal operating conditions.</p>
        <p><strong>Standard Warranty Period:</strong> Most Azoogi products carry a standard 5-Year Warranty from the date of invoice. Select ranges carry a 3-Year Warranty. Please refer to the specific product datasheet or technical specification sheet for exact warranty periods applicable to each product model.</p>
        <p><strong>Licensed Electrician Requirement:</strong> To maintain warranty validity, all electrical products must be installed by a qualified, licensed electrician in accordance with Australian Wiring Rules (AS/NZS 3000) and manufacturer specifications.</p>
      </section>

      <section class="legal-section">
        <h3>2. Making a Warranty Claim &amp; Faulty Returns</h3>
        <p>If you experience an issue with an Azoogi product, our team is here to assist you quickly.</p>
        <p><strong>Return of Faulty Units:</strong> Faulty units must be returned to Azoogi for technical evaluation and testing prior to a replacement or credit being issued.</p>
        <p><strong>Claim Process:</strong> Contact our team at <a href="mailto:support@azoogi.com">support@azoogi.com</a> with your original invoice number, installation details, and a description of the fault.</p>
        <p><strong>Site Inspections &amp; Fee Conditions:</strong> If a faulty unit cannot be uninstalled or returned, Azoogi may agree to send a technical representative to perform an on-site inspection. If the product is determined to be non-faulty, or if the failure is caused by improper installation, external wiring issues, line spikes, or environmental misuse, the site visit and inspection will be fully chargeable to the client.</p>
      </section>

      <section class="legal-section">
        <h3>3. Non-Faulty Returns &amp; Ordering Errors</h3>
        <p>If you ordered the wrong item or need to return unused stock due to project design changes, we accept returns under the following conditions:</p>
        <ul>
          <li><strong>30-Day Window:</strong> Return requests must be lodged within 30 days of the invoice date.</li>
          <li><strong>Restocking Fee:</strong> Approved returns are subject to a 25% restocking fee to cover handling, inspection, testing, and repacking costs.</li>
          <li><strong>Product Condition:</strong> Returned items must be unused, in pristine original condition, and in original undamaged packaging. Freight costs for non-faulty returns are the responsibility of the purchaser.</li>
        </ul>
      </section>

      <section class="legal-section">
        <h3>4. Delivery Shortages &amp; Damage Claims</h3>
        <p>Upon receiving shipments from Azoogi, please inspect all packages immediately.</p>
        <p><strong>Strict 14-Day Shortage Window:</strong> Any shipping errors, missing items, or transit damages must be reported in writing to <a href="mailto:support@azoogi.com">support@azoogi.com</a> within 14 days of delivery. Claims lodged outside this period will not be accepted.</p>
      </section>

      <section class="legal-section">
        <h3>5. Australian Consumer Law</h3>
        <p>Our goods come with guarantees that cannot be excluded under the Australian Consumer Law. You are entitled to a replacement or refund for a major failure and compensation for any other reasonably foreseeable loss or damage. You are also entitled to have the goods repaired or replaced if the goods fail to be of acceptable quality and the failure does not amount to a major failure.</p>
      </section>

      <section class="legal-section">
        <h3>6. Contact Us for Warranty Support</h3>
        <p>For all warranty inquiries, return authorizations, or technical assistance, please reach out to our support team:</p>
        <div class="legal-contact">
          <p><strong>Azoogi Pty Ltd</strong></p>
          <p>Unit 47, 10-12 Girwah Place<br>Matraville NSW 2036, Australia</p>
          <p>Email: <a href="mailto:support@azoogi.com">support@azoogi.com</a></p>
        </div>
      </section>
    </article>

    <!-- Modern Slavery -->
    <article class="legal-block" id="modern-slavery">
      <h2 class="legal-block-title">Azoogi Pty Ltd — Anti-Modern Slavery Policy Statement</h2>

      <section class="legal-section">
        <h3>1. Policy Overview &amp; Commitment</h3>
        <p>Azoogi Pty Ltd is committed to maintaining the highest standards of ethical conduct, corporate integrity, and social responsibility across all aspects of our business. In accordance with the Modern Slavery Act 2018 (Cth), Azoogi maintains a strict zero-tolerance stance on all forms of modern slavery, human trafficking, forced labor, debt bondage, child labor, and deceptive recruiting practices within our internal operations and global supply chains.</p>
      </section>

      <section class="legal-section">
        <h3>2. Business &amp; Supply Chain Operations</h3>
        <p>Azoogi is an Australian-owned trade wholesaler specializing in custom linear LED packages, architectural profiles, power electronics, and intelligent lighting control systems across Australia and New Zealand. Our operational supply chain includes:</p>
        <ul>
          <li><strong>Local Customization &amp; Assembly:</strong> Custom linear profile cutting, thermal matching, specialized PCB board assembly, custom powder coating, and local technical testing.</li>
          <li><strong>Global Component Sourcing:</strong> Procurement of electronic components, raw aluminum extrusions, printed circuit boards (PCBs), LED chips, electronic drivers, and control hardware from international original equipment manufacturers (OEMs).</li>
          <li><strong>Distribution &amp; Freight:</strong> Local and international warehousing, freight, and logistical management partners.</li>
        </ul>
      </section>

      <section class="legal-section">
        <h3>3. Ethical Sourcing &amp; Sedex Supplier Plus Membership</h3>
        <p>Recognizing that offshore electronics manufacturing and raw material extraction carry inherent supply chain risks, Azoogi actively manages supply chain transparency through a multi-tiered due diligence framework:</p>
        <ul>
          <li><strong>Sedex Supplier Plus Accreditation:</strong> Azoogi is an active Sedex Supplier Plus Member. Through the global Sedex platform, we actively monitor, evaluate, and share independent ethical audit data (SMETA), operational risk assessments, and labor standard compliance across our global manufacturing footprint—providing our commercial partners, Tier-1 contractors, and procurement teams with complete transparency into our ESG and human rights performance.</li>
          <li><strong>Supplier Vetting &amp; Code of Conduct:</strong> All primary OEMs, component vendors, and assembly partners are thoroughly evaluated and required to adhere to strict ethical sourcing guidelines that prohibit forced, bonded, or underaged labor.</li>
          <li><strong>Traceability &amp; Continuous Auditing:</strong> We conduct ongoing performance reviews of our key supply chain partners and track component origin to ensure continuous compliance with international labor laws.</li>
        </ul>
      </section>

      <section class="legal-section">
        <h3>4. Reporting, Whistleblowing &amp; Corrective Action</h3>
        <p>Azoogi fosters an open, accountable culture and encourages the reporting of any suspected breaches or modern slavery risks. Employees, contractors, vendors, and external supply chain partners can report concerns directly to company management without fear of retaliation, victimization, or adverse action.</p>
        <p>Please contact <a href="mailto:admin@azoogi.com">admin@azoogi.com</a> for any queries, risk reports, or compliance documentation requests. Any verified breach or failure to comply with our ethical standards by a supplier will result in immediate corrective action requests, independent auditing, or termination of the commercial relationship.</p>
      </section>

      <section class="legal-section">
        <h3>5. Policy Governance &amp; Continuous Review</h3>
        <p>This policy is reviewed annually to adapt to emerging global supply chain risks, align with evolving statutory requirements under the Modern Slavery Act 2018 (Cth), and uphold our commitment to responsible corporate governance.</p>
      </section>
    </article>
      </div>
    </div>
  </div>
</main>
@endsection

@push('scripts')
@verbatim
<script>
document.getElementById('topbar')?.classList.add('solid');

  // Highlight active section link while scrolling
  (function () {
    const links = document.querySelectorAll('.legal-nav a[href^="#"]');
    const sections = ['privacy', 'terms', 'warranty', 'modern-slavery']
      .map((id) => document.getElementById(id))
      .filter(Boolean);

    function setActive() {
      const offset = 140;
      let current = sections[0];
      sections.forEach((sec) => {
        if (sec.getBoundingClientRect().top - offset <= 0) current = sec;
      });
      links.forEach((a) => {
        a.classList.toggle('is-active', a.getAttribute('href') === '#' + current.id);
      });
    }

    window.addEventListener('scroll', setActive, { passive: true });
    setActive();
  })();
</script>
@endverbatim
@endpush
