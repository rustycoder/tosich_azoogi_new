<?php

namespace Tests\Feature;

use App\Models\Page;
use Database\Seeders\PageSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CasambiPageTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(PageSeeder::class);
    }

    public function test_casambi_page_renders_client_copy(): void
    {
        $this->get('/casambi')
            ->assertOk()
            ->assertSee('/assets/img/casambi/logo-dark.svg', false)
            ->assertSee('/assets/logo_dark.png', false)
            ->assertSee('alt="Casambi"', false)
            ->assertDontSee('cb-hero-slides', false)
            ->assertSee('Advanced Wireless Lighting Control &amp;', false)
            ->assertSee('Smart Ecosystems', false)
            ->assertSee('Scalable Bluetooth Mesh Technology. Standardized Luminaire Integration.', false)
            ->assertSee('As an official Casambi technology and distribution partner, we bring intelligent, ultra-reliable Bluetooth Low Energy', false)
            ->assertSee('Why Choose Casambi?', false)
            ->assertSee('Casambi App &amp; Product Categories', false)
            ->assertSee('Configure. Control. Automate.', false)
            ->assertSee('/assets/img/casambi/software.png', false)
            ->assertSee('Key Casambi Product Categories', false)
            ->assertSee('Full System Specification &amp; Commissioning', false)
            ->assertSee('Ready to specify Casambi for your project?', false)
            ->assertSee('Request a Casambi Quote', false)
            ->assertSee('href="'.url('/contact').'"', false)
            ->assertDontSee('youtube-nocookie.com/embed', false)
            ->assertSee('>Products <', false)
            ->assertSee('>Projects</a>', false)
            ->assertSee('>About Us</a>', false)
            ->assertSee('>Solutions</a>', false)
            ->assertSee('>Contact</a>', false)
            ->assertSee('>AI Lighting</a>', false)
            ->assertSee('LED Calculator', false);
    }

    public function test_hardware_lineup_is_a_table(): void
    {
        $this->get('/casambi')
            ->assertOk()
            ->assertSee('<table class="spec-table">', false)
            ->assertSee('<th>Category</th>', false)
            ->assertSee('<th>Key Components</th>', false)
            ->assertSee('<th>Application</th>', false)
            ->assertSee('CBU Modules', false)
            ->assertSee('CBU-ASD, CBU-PWM4, CBU-TED', false)
            ->assertSee('data-preview="/assets/img/casambi/cbu-asd-lr.png"', false)
            ->assertSee('data-preview="/assets/img/casambi/xpress-lr.jpg"', false)
            ->assertSee('data-preview="/assets/img/casambi/cloud-gateway.png"', false)
            ->assertSee('data-preview="/assets/img/casambi/cbm-003.png"', false)
            ->assertSee('OEM Integration', false)
            ->assertSee('Casambi Gateway', false);
    }

    public function test_solutions_casambi_button_links_to_the_page(): void
    {
        $this->get('/solutions')
            ->assertOk()
            ->assertSee('href="/casambi"', false);
    }

    public function test_header_shows_site_nav_when_cms_header_is_missing(): void
    {
        Page::query()->where('slug', 'header')->forceDelete();

        $this->get('/casambi')
            ->assertOk()
            ->assertSee('>Projects</a>', false)
            ->assertSee('>About Us</a>', false)
            ->assertSee('>Solutions</a>', false)
            ->assertSee('>Contact</a>', false)
            ->assertSee('>AI Lighting</a>', false);
    }
}
