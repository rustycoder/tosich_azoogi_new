<?php

namespace Tests\Feature;

use Database\Seeders\PageSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class ThemeToggleTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(PageSeeder::class);
    }

    /**
     * @return array<string, array{0: string}>
     */
    public static function publicPageProvider(): array
    {
        return [
            'home' => ['/'],
            'products' => ['/products'],
            'solutions' => ['/solutions'],
            'about' => ['/about'],
            'contact' => ['/contact'],
        ];
    }

    #[DataProvider('publicPageProvider')]
    public function test_public_pages_include_the_theme_toggle(string $path): void
    {
        $jsMtime = filemtime(public_path('assets/js/site-theme.js'));

        $this->get($path)
            ->assertOk()
            ->assertSee("localStorage.getItem('theme')", false)
            ->assertSee('id="theme-toggle"', false)
            ->assertSee('aria-label="Toggle theme"', false)
            ->assertSee('class="theme-btn"', false)
            ->assertSeeInOrder([
                'Trade Login',
                'id="theme-toggle"',
                'class="nav-actions"',
            ], false)
            ->assertSee('class="sun-icon"', false)
            ->assertSee('class="moon-icon"', false)
            ->assertSee('/assets/js/site-theme.js?v='.$jsMtime, false);
    }

    public function test_theme_script_persists_choice_and_swaps_logos(): void
    {
        $script = file_get_contents(public_path('assets/js/site-theme.js'));

        $this->assertNotFalse($script);
        $this->assertStringContainsString("localStorage.setItem('theme', newTheme)", $script);
        $this->assertStringContainsString("setAttribute('data-theme', newTheme)", $script);
        $this->assertStringContainsString('/assets/logo_dark.png', $script);
        $this->assertStringContainsString('/assets/logo_white.png', $script);
        $this->assertStringContainsString("getElementById('theme-toggle')", $script);
    }

    public function test_home_no_longer_clears_the_saved_theme(): void
    {
        $home = file_get_contents(resource_path('views/pages/home.blade.php'));

        $this->assertNotFalse($home);
        $this->assertStringNotContainsString("localStorage.removeItem('theme')", $home);
    }

    public function test_site_stylesheet_defaults_to_dark_with_a_light_override(): void
    {
        $css = file_get_contents(public_path('assets/css/style_demo.css'));

        $this->assertNotFalse($css);
        $this->assertStringContainsString('--bg: #0b0b0b;', $css);
        $this->assertStringContainsString(':root[data-theme="light"]', $css);
        $this->assertStringContainsString('.theme-btn', $css);
        $this->assertMatchesRegularExpression(
            '/\.theme-btn\s*\{[^}]*border:\s*0;/s',
            $css,
        );
        $this->assertStringContainsString('background: rgba(11, 11, 11, 0.92);', $css);
        $this->assertStringContainsString(':root[data-theme="light"] .topbar.solid', $css);
    }

    public function test_public_card_panels_use_theme_surfaces(): void
    {
        $files = [
            'assets/css/madrix.css',
            'assets/css/ai-lighting.css',
            'assets/css/casambi.css',
            'assets/css/silvair.css',
            'assets/css/dali-centre.css',
            'assets/css/data-centre.css',
            'assets/css/products.css',
            'assets/css/led_calculator.css',
            'assets/css/about.css',
        ];

        foreach ($files as $path) {
            $css = file_get_contents(public_path($path));

            $this->assertNotFalse($css);
            $this->assertStringContainsString(
                'background: var(--bg-2)',
                $css,
                $path.' should use theme card surfaces.',
            );
            $this->assertDoesNotMatchRegularExpression(
                '/\.(mx-caps|ai-caps|cb-caps|sv-caps|dc-caps|mx-support|cb-support|ai-accordion)[^{]*\{[^}]*background:\s*#fff/s',
                $css,
                $path.' still paints cards white.',
            );
        }
    }
}
