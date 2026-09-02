<?php

namespace Tests\Feature;

use App\Models\Page;
use Database\Seeders\PageSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DaliCentrePageTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(PageSeeder::class);
    }

    public function test_dali_centre_page_renders_client_copy(): void
    {
        $this->get('/dali-centre')
            ->assertOk()
            ->assertSee('/assets/logo_dark.png', false)
            ->assertSee('AZOOGI DALI Centre', false)
            ->assertSee('Centralized Architectural Lighting &amp;', false)
            ->assertSee('Smart DALI-2 Management', false)
            ->assertSee('Scalable IP Gateways. Precision Local Control. Comprehensive Energy Analytics.', false)
            ->assertSee('A powerful centralized management platform designed for public buildings', false)
            ->assertSee('youtube-nocookie.com/embed/C0KcmW6NewI', false)
            ->assertSee('Why Choose AZOOGI DALI Centre?', false)
            ->assertSee('Core Hardware &amp; Gateway System Components', false)
            ->assertSee('System Design &amp; Local Support Services', false)
            ->assertSee('Ready to specify DALI Centre for your project?', false)
            ->assertSee('Request a DALI Centre Quote', false)
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
        $this->get('/dali-centre')
            ->assertOk()
            ->assertSee('<table class="spec-table">', false)
            ->assertSee('<th>Component</th>', false)
            ->assertSee('<th>Series</th>', false)
            ->assertSee('<th>Role</th>', false)
            ->assertSee('AZOOGI Ethernet DALI Gateway', false)
            ->assertSee('EDA / PRO Series', false)
            ->assertSee('AZOOGI Multi-Protocol Gateways', false)
            ->assertSee('ZDA Series', false)
            ->assertSee('AZOOGI DALI Masters &amp; Keypads', false)
            ->assertSee('AZOOGI Sensors &amp; Input Modules', false);
    }

    public function test_hero_places_the_video_on_the_right(): void
    {
        $this->get('/dali-centre')
            ->assertOk()
            ->assertSee('class="wrap dc-hero-grid"', false)
            ->assertSee('class="dc-hero-video"', false)
            ->assertSee('youtube-nocookie.com/embed/C0KcmW6NewI', false);
    }

    public function test_page_shows_the_client_dali_system_diagram(): void
    {
        $this->get('/dali-centre')
            ->assertOk()
            ->assertSee('class="wrap dc-diagram reveal"', false)
            ->assertSee('The complete DALI bus, from sensors to BMS.', false)
            ->assertSee('Power the bus', false)
            ->assertSee('Inputs on the left', false)
            ->assertSee('Management on the right', false)
            ->assertSee('Loads on the bus', false)
            ->assertSee('/assets/img/dali-system/DALI%20system.png', false)
            ->assertSee('alt="AZOOGI DALI system architecture', false);
    }

    public function test_solutions_dali_centre_button_links_to_the_page(): void
    {
        $this->get('/solutions')
            ->assertOk()
            ->assertSee('href="/dali-centre"', false);
    }

    public function test_header_shows_site_nav_when_cms_header_is_missing(): void
    {
        Page::query()->where('slug', 'header')->forceDelete();

        $this->get('/dali-centre')
            ->assertOk()
            ->assertSee('>Projects</a>', false)
            ->assertSee('>About Us</a>', false)
            ->assertSee('>Solutions</a>', false)
            ->assertSee('>Contact</a>', false)
            ->assertSee('>AI Lighting</a>', false);
    }
}
