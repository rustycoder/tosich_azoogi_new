<form class="quote-form" id="quote-request-form" action="#" method="post" novalidate>
  <div class="quote-form-section">
    <div class="quote-form-tag">1. Contact information</div>
    <div class="quote-form-row">
      <div class="form-group">
        <label for="your-name">First Name*</label>
        <input id="your-name" name="your-name" type="text" maxlength="400" required autocomplete="given-name">
      </div>
      <div class="form-group">
        <label for="your-email">Email*</label>
        <input id="your-email" name="your-email" type="email" maxlength="400" required autocomplete="email">
      </div>
      <div class="form-group">
        <label for="your-phone">Mobile*</label>
        <input id="your-phone" name="your-phone" type="tel" maxlength="400" required autocomplete="tel">
      </div>
    </div>
  </div>

  <div class="quote-form-section">
    <div class="quote-form-tag">2. Project information</div>
    <div class="form-group">
      <label for="your-description">Short Description</label>
      <input id="your-description" name="your-description" type="text" maxlength="400">
    </div>
    <div class="form-group">
      <label for="your-products">Products Needed + Quantities</label>
      <textarea id="your-products" name="your-products" rows="8" maxlength="2000"></textarea>
    </div>
  </div>

  <div class="quote-form-section">
    <div class="quote-form-tag">3. Which describes you best</div>
    <div class="quote-radios">
      <label><input type="radio" name="radio-choice" value="I’m a Builder"> I’m a Builder</label>
      <label><input type="radio" name="radio-choice" value="I’m an Architect"> I’m an Architect</label>
      <label><input type="radio" name="radio-choice" value="I’m Renovating my Home"> I’m Renovating my Home</label>
      <label><input type="radio" name="radio-choice" value="I own a Lighting Store"> I own a Lighting Store</label>
      <label><input type="radio" name="radio-choice" value="I’m a Consultant"> I’m a Consultant</label>
      <label><input type="radio" name="radio-choice" value="I’m a Distributor"> I’m a Distributor</label>
      <label><input type="radio" name="radio-choice" value="I’m a Contractor"> I’m a Contractor</label>
    </div>
  </div>

  <div class="quote-form-section">
    <div class="quote-form-tag">4. Preferred contact method</div>
    <div class="quote-radios quote-radios-inline">
      <label><input type="radio" name="contact-choice" value="Phone"> Phone</label>
      <label><input type="radio" name="contact-choice" value="Email"> Email</label>
    </div>
  </div>

  <div class="quote-form-section">
    <div class="quote-form-tag">5. Preferred retail supplier or suburb</div>
    <div class="form-group">
      <label for="suburb-retailer">Suburb or Retailer</label>
      <input id="suburb-retailer" name="suburb-retailer" type="text" maxlength="400">
    </div>
  </div>

  <div class="form-actions">
    <button type="submit" class="btn primary">Get a Custom Quote</button>
  </div>
  <p class="form-status" id="quote-form-status" hidden></p>
</form>
