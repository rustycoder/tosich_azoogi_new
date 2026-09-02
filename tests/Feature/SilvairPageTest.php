<?php

namespace Tests\Feature;

use App\Models\Page;
use Database\Seeders\PageSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SilvairPageTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(PageSeeder::class);
    }

    public function test_silvair_page_renders_client_copy(): void
    {
        $this->get('/silvair')
            ->assertOk()
            ->assertSee('/assets/img/silvair/logo.svg', false)
            ->assertSee('/assets/logo_dark.png', false)
            ->assertSee('alt="Silvair"', false)
            ->assertSee('Enterprise Bluetooth®', false)
            ->assertSee('Qualified Mesh Lighting', false)
            ->assertSee('Qualified Mesh Standard. Rapid Mobile Commissioning. Intelligent Energy Analytics.', false)
            ->assertSee('As an official integration partner for Silvair, we deliver robust, interoperable Bluetooth® Mesh lighting control solutions', false)
            ->assertSee('Why Choose Silvair Wireless Controls?', false)
            ->assertSee('Key Silvair Platform Capabilities', false)
            ->assertSee('Mobile for installers. Web for managers.', false)
            ->assertSee('/assets/img/silvair/software.jpg', false)
            ->assertSee('Hardware Compatibility &amp; Ecosystem Integration', false)
            ->assertSee('Complete Project &amp; Integration Services', false)
            ->assertSee('Bring Silvair to your next building — with Azoogi.', false)
            ->assertSee('Request a Silvair Project Quote', false)
            ->assertSee('href="'.url('/contact').'"', false)
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
        $this->get('/silvair')
            ->assertOk()
            ->assertSee('<table class="spec-table">', false)
            ->assertSee('<th>Category</th>', false)
            ->assertSee('<th>Key Components</th>', false)
            ->assertSee('<th>Application</th>', false)
            ->assertSee('Silvair Ready Components', false)
            ->assertSee('Fixture controllers, sensors &amp; wall switches', false)
            ->assertSee('data-preview="/assets/img/silvair/office.jpg"', false)
            ->assertSee('data-preview="/assets/img/silvair/emergency.jpg"', false)
            ->assertSee('Emergency &amp; DALI-2 Bridge', false)
            ->assertSee('Open API', false);
    }

    public function test_page_includes_old_site_mix_sections(): void
    {
        $this->get('/silvair')
            ->assertOk()
            ->assertSee('1M+', false)
            ->assertSee('Six connected solutions, one open platform.', false)
            ->assertSee('Emergency Lighting Testing', false)
            ->assertSee('The Bluetooth® NLC Standard', false)
            ->assertSee('/assets/img/silvair/nlc.jpg', false)
            ->assertSee('Built for every commercial space.', false)
            ->assertSee('/assets/img/silvair/warehouse.jpg', false)
            ->assertSee('/assets/img/silvair/classroom.jpg', false)
            ->assertSee('/assets/img/silvair/carpark.jpg', false)
            ->assertSee('From sensor to BMS in four steps.', false)
            ->assertSee('On-Site &amp; Remote Commissioning:', false);
    }

    public function test_solutions_silvair_button_links_to_the_page(): void
    {
        $this->get('/solutions')
            ->assertOk()
            ->assertSee('href="/silvair"', false);
    }

    public function test_header_shows_site_nav_when_cms_header_is_missing(): void
    {
        Page::query()->where('slug', 'header')->forceDelete();

        $this->get('/silvair')
            ->assertOk()
            ->assertSee('>Projects</a>', false)
            ->assertSee('>About Us</a>', false)
            ->assertSee('>Solutions</a>', false)
            ->assertSee('>Contact</a>', false)
            ->assertSee('>AI Lighting</a>', false);
    }
}
