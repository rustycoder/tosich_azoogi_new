<form class="quote-form" id="quote-request-form" action="{{ route('quote.submit') }}" method="post" novalidate>
  @csrf
  <div class="quote-form-section">
    <div class="quote-form-tag"{!! cms_style($meta, 'form.contact_tag') !!}>{{ $meta->get('form.contact_tag') }}</div>
    <div class="quote-form-row">
      <div class="form-group">
        <label for="your-name"{!! cms_style($meta, 'form.name_label') !!}>{{ $meta->get('form.name_label') }}</label>
        <input id="your-name" name="your-name" type="text" maxlength="400" required autocomplete="given-name" value="{{ old('your-name') }}">
      </div>
      <div class="form-group">
        <label for="your-email"{!! cms_style($meta, 'form.email_label') !!}>{{ $meta->get('form.email_label') }}</label>
        <input id="your-email" name="your-email" type="email" maxlength="400" required autocomplete="email" value="{{ old('your-email') }}">
      </div>
      <div class="form-group">
        <label for="your-phone"{!! cms_style($meta, 'form.phone_label') !!}>{{ $meta->get('form.phone_label') }}</label>
        <input id="your-phone" name="your-phone" type="tel" maxlength="400" required autocomplete="tel" value="{{ old('your-phone') }}">
      </div>
    </div>
  </div>

  <div class="quote-form-section">
    <div class="quote-form-tag"{!! cms_style($meta, 'form.project_tag') !!}>{{ $meta->get('form.project_tag') }}</div>
    <div class="form-group">
      <label for="your-description"{!! cms_style($meta, 'form.description_label') !!}>{{ $meta->get('form.description_label') }}</label>
      <input id="your-description" name="your-description" type="text" maxlength="400" value="{{ old('your-description') }}">
    </div>
    <div class="form-group">
      <label for="your-products"{!! cms_style($meta, 'form.products_label') !!}>{{ $meta->get('form.products_label') }}</label>
      <textarea id="your-products" name="your-products" rows="8" maxlength="2000">{{ old('your-products') }}</textarea>
    </div>
  </div>

  <div class="quote-form-section">
    <div class="quote-form-tag"{!! cms_style($meta, 'form.role_tag') !!}>{{ $meta->get('form.role_tag') }}</div>
    <div class="quote-radios">
      @foreach ($meta->list('form.role.label') as $index => $role)
        @if (trim($role) !== '')
          <label{!! cms_style($meta, 'form.role.label', $index) !!}><input type="radio" name="radio-choice" value="{{ $role }}" @checked(old('radio-choice') === $role)> {{ $role }}</label>
        @endif
      @endforeach
    </div>
  </div>

  <div class="quote-form-section">
    <div class="quote-form-tag"{!! cms_style($meta, 'form.method_tag') !!}>{{ $meta->get('form.method_tag') }}</div>
    <div class="quote-radios quote-radios-inline">
      @foreach ($meta->list('form.method.label') as $index => $method)
        @if (trim($method) !== '')
          <label{!! cms_style($meta, 'form.method.label', $index) !!}><input type="radio" name="contact-choice" value="{{ $method }}" @checked(old('contact-choice') === $method)> {{ $method }}</label>
        @endif
      @endforeach
    </div>
  </div>

  <div class="quote-form-section">
    <div class="quote-form-tag"{!! cms_style($meta, 'form.supplier_tag') !!}>{{ $meta->get('form.supplier_tag') }}</div>
    <div class="form-group">
      <label for="suburb-retailer"{!! cms_style($meta, 'form.suburb_label') !!}>{{ $meta->get('form.suburb_label') }}</label>
      <input id="suburb-retailer" name="suburb-retailer" type="text" maxlength="400" value="{{ old('suburb-retailer') }}">
    </div>
  </div>

  <div class="form-actions">
    <button type="submit" class="btn primary"{!! cms_style($meta, 'form.submit') !!}>{{ $meta->get('form.submit') }}</button>
  </div>
  @if ($errors->any())
    <p class="form-status is-error">{{ $errors->first() }}</p>
  @elseif (session('status'))
    <p class="form-status is-success">{{ session('status') }}</p>
  @else
    <p class="form-status" id="quote-form-status" hidden></p>
  @endif
</form>
