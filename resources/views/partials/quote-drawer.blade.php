<div class="quote-overlay" data-quote-close></div>
<aside class="quote-drawer" id="quote-drawer" role="dialog" aria-labelledby="quote-drawer-title" aria-hidden="true">
  <div class="quote-drawer-head">
    <h2 id="quote-drawer-title"{!! cms_style($quoteMeta, 'drawer.title') !!}>{{ $quoteMeta->get('drawer.title', 0, 'Quote List') }}</h2>
    <button type="button" class="quote-drawer-close" data-quote-close aria-label="{{ $quoteMeta->get('drawer.close', 0, 'Close quote list') }}">&times;</button>
  </div>
  <div class="quote-drawer-body" data-quote-list="drawer"></div>
  <div class="quote-drawer-foot">
    <a href="{{ url('/request-a-quote') }}" class="btn primary" data-quote-submit{!! cms_style($quoteMeta, 'drawer.submit') !!}>{{ $quoteMeta->get('drawer.submit', 0, 'Request a Quote') }}</a>
  </div>
</aside>
