<footer>
    <div class="wrap">
        <div class="foot">
            <div>
                <a href="{{ url('/') }}" class="logo"><img src="{{ asset('assets/logo_dark.png') }}" width="150" alt="Azoogi"></a>
                <p>We design, engineer, manufacture, assemble, and test our products in-house, offering custom powder coating to deliver fully tailored lighting solutions</p>
            </div>
            <div>
                <h5>Products</h5>
                <a href="{{ url('/products') }}">All Products</a>
                <a href="{{ url('/led-strip-calculator') }}">LED Calculator</a>
            </div>
            <div>
                <h5>Company</h5>
                <a href="{{ url('/about') }}">About Azoogi</a>
                <a href="{{ url('/projects') }}">Projects</a>
                <a href="{{ url('/ai-lighting') }}">AI Lighting</a>
                <a href="{{ url('/contact') }}">Contact</a>
                <a href="{{ url('/privacy') }}">Privacy</a>
            </div>
            <div>
                <h5>Contact</h5>
                <a href="tel:1300641261">1300 641 261</a>
                <a href="mailto:sales@azoogi.com">sales@azoogi.com</a>
                <a href="{{ url('/trade-login') }}">Trade Login</a>
            </div>
        </div>
        <div class="copy">
            <div>&copy; {{ date('Y') }} Azoogi Pty Ltd. All rights reserved.</div>
            <div class="copy-links">
                <a href="{{ url('/privacy') }}">Privacy</a><span class="sep">&middot;</span>
                <a href="{{ url('/terms') }}">Terms</a><span class="sep">&middot;</span>
                <a href="{{ url('/warranty-returns') }}">Warranty</a><span class="sep">&middot;</span>
                <a href="{{ url('/modern-slavery') }}">Modern Slavery</a>
            </div>
        </div>
    </div>
</footer>
