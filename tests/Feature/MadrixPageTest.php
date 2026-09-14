<?php

namespace Tests\Feature;

use App\Models\Page;
use Database\Seeders\PageSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MadrixPageTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(PageSeeder::class);
    }

    public function test_madrix_page_renders_client_copy(): void
    {
        $this->get('/madrix')
            ->assertOk()
            ->assertSee('MADRIX', false)
            ->assertSee('Next-Generation', false)
            ->assertSee('Pixel Mapping', false)
            ->assertSee('Advanced LED Control Solutions', false)
            ->assertSee('Powerful German Engineering. Seamless Spatial Lighting Integration.', false)
            ->assertSee('As an official partner of MADRIX, we bring industry-leading pixel-mapping software', false)
            ->assertSee('Why Choose MADRIX?', false)
            ->assertSee('The MADRIX Product Lineup', false)
            ->assertSee('MADRIX 5 (Software):', false)
            ->assertSee('/assets/img/madrix/software.png', false)
            ->assertSee('2. Hardware Interfaces &amp; Controllers', false)
            ->assertSee('Design, Integration &amp; Local Support', false)
            ->assertSee('Ready to Bring Your Lighting Designs to Life?', false)
            ->assertSee('Request a MADRIX Quote', false)
            ->assertSee('href="'.url('/contact').'"', false)
            ->assertSee('Previous image', false)
            ->assertSee('Next image', false)
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
        $this->get('/madrix')
            ->assertOk()
            ->assertSee('<table class="spec-table">', false)
            ->assertSee('<th>Product</th>', false)
            ->assertSee('<th>Type</th>', false)
            ->assertSee('<th>Key Features</th>', false)
            ->assertSee('MADRIX NEBULA', false)
            ->assertSee('SPI Direct Decoder', false)
            ->assertSee('data-preview="/assets/img/madrix/nebula.png"', false)
            ->assertSee('data-preview="/assets/img/madrix/orion.png"', false)
            ->assertSee('MADRIX ORION', false)
            ->assertSee('Sensor Input Interface', false);
    }

    public function test_hardware_table_keeps_the_first_column_narrower_than_the_second(): void
    {
        $css = file_get_contents(public_path('assets/css/madrix.css'));

        $this->assertNotFalse($css);
        $this->assertStringContainsString('table-layout: fixed', $css);
        $this->assertMatchesRegularExpression(
            '/\.spec-table th:first-child,\s*\.spec-table td:first-child\s*\{[^}]*width:\s*15%/s',
            $css,
        );
        $this->assertMatchesRegularExpression(
            '/\.spec-table th:nth-child\(2\),\s*\.spec-table td:nth-child\(2\)\s*\{[^}]*width:\s*25%/s',
            $css,
        );
        $this->assertMatchesRegularExpression(
            '/\.spec-table th:last-child,\s*\.spec-table td:last-child\s*\{[^}]*width:\s*60%/s',
            $css,
        );
    }

    public function test_page_embeds_the_reference_welcome_video(): void
    {
        $this->get('/madrix')
            ->assertOk()
            ->assertSee('youtube-nocookie.com/embed/QELQAZu-46M', false);
    }

    public function test_solutions_madrix_button_links_to_the_page(): void
    {
        $this->get('/solutions')
            ->assertOk()
            ->assertSee('href="/madrix"', false);
    }

    public function test_header_shows_site_nav_when_cms_header_is_missing(): void
    {
        Page::query()->where('slug', 'header')->forceDelete();

        $this->get('/madrix')
            ->assertOk()
            ->assertSee('>Projects</a>', false)
            ->assertSee('>About Us</a>', false)
            ->assertSee('>Solutions</a>', false)
            ->assertSee('>Contact</a>', false)
            ->assertSee('>AI Lighting</a>', false);
    }
}
