@php
    $description = $footerMeta->get('footer.description', 0, 'We design, engineer, manufacture, assemble, and test our products in-house, offering custom powder coating to deliver fully tailored lighting solutions');
    $phone = $footerMeta->get('footer.phone', 0, '1300 641 261');
    $email = $footerMeta->get('footer.email', 0, 'sales@azoogi.com');
    $message = $footerMeta->get('footer.message', 0, 'Azoogi Pty Ltd. All rights reserved.');
    $productHeading = $footerMeta->get('footer.products.heading', 0, 'Products');
    $companyHeading = $footerMeta->get('footer.company.heading', 0, 'Company');
    $contactHeading = $footerMeta->get('footer.contact.heading', 0, 'Contact');
    $productLinks = collect($footerMeta->group('footer.products.link'))->filter(fn (array $item): bool => trim($item['label'] ?? '') !== '');
    $companyLinks = collect($footerMeta->group('footer.company.link'))->filter(fn (array $item): bool => trim($item['label'] ?? '') !== '');
    $contactLinks = collect($footerMeta->group('footer.contact.link'))->filter(fn (array $item): bool => trim($item['label'] ?? '') !== '');
@endphp
<footer>
    <div class="wrap">
        <div class="foot">
            <div>
                <a href="{{ url('/') }}" class="logo"><img src="{{ asset('assets/logo_dark.png') }}" width="150" alt="Azoogi"></a>
                <p>{{ $description }}</p>
            </div>
            <div>
                <h5>{{ $productHeading }}</h5>
                @foreach ($productLinks as $item)
                    <a href="{{ chrome_url($item['href'] ?? '') }}"{!! chrome_target_attrs($item['target'] ?? null) !!}>{{ $item['label'] }}</a>
                @endforeach
            </div>
            <div>
                <h5>{{ $companyHeading }}</h5>
                @foreach ($companyLinks as $item)
                    <a href="{{ chrome_url($item['href'] ?? '') }}"{!! chrome_target_attrs($item['target'] ?? null) !!}>{{ $item['label'] }}</a>
                @endforeach
            </div>
            <div>
                <h5>{{ $contactHeading }}</h5>
                <a href="{{ tel_href($phone) }}">{{ $phone }}</a>
                <a href="mailto:{{ $email }}">{{ $email }}</a>
                @foreach ($contactLinks as $item)
                    <a href="{{ chrome_url($item['href'] ?? '') }}"{!! chrome_target_attrs($item['target'] ?? null) !!}>{{ $item['label'] }}</a>
                @endforeach
            </div>
        </div>
        <div class="copy">
            <div>&copy; {{ date('Y') }} {{ $message }}</div>
            <div class="copy-links">
                <a href="{{ url('/privacy') }}">Privacy</a><span class="sep">&middot;</span>
                <a href="{{ url('/terms') }}">Terms</a><span class="sep">&middot;</span>
                <a href="{{ url('/warranty-returns') }}">Warranty</a><span class="sep">&middot;</span>
                <a href="{{ url('/modern-slavery') }}">Modern Slavery</a>
            </div>
        </div>
    </div>
</footer>
