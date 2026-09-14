<?php

namespace Tests\Feature;

use Database\Seeders\PageSeeder;
use Database\Seeders\ProjectSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HomeHeroTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed([
            PageSeeder::class,
            ProjectSeeder::class,
        ]);
    }

    public function test_home_hero_omits_progress_lines_and_eyebrow(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertDontSee('class="eyebrow"', false)
            ->assertDontSee('id="lines"', false)
            ->assertDontSee('class="line"', false)
            ->assertSee('class="slider-ctrl"', false)
            ->assertSee('id="pp"', false);
    }

    public function test_public_pages_omit_hero_eyebrow_labels(): void
    {
        $this->get('/about')->assertOk()->assertDontSee('class="kicker"', false);
        $this->get('/solutions')->assertOk()->assertDontSee('solutions-kicker', false);
        $this->get('/dali-centre')->assertOk()->assertDontSee('dc-kicker', false);
        $this->get('/home-owner')->assertOk()->assertDontSee('class="kicker"', false);
    }

    public function test_about_numbering_and_hero_accents_use_outline_type(): void
    {
        $this->get('/about')
            ->assertOk()
            ->assertSee('about-why-ghost', false)
            ->assertSee('Zero Compromise.', false);

        $this->get('/silvair')
            ->assertOk()
            ->assertSee('<span>Qualified Mesh Lighting</span>', false);
    }

    public function test_home_marquee_cards_reserve_space_for_titles_and_cta(): void
    {
        $css = file_get_contents(public_path('assets/css/style_demo.css'));

        $this->assertNotFalse($css);
        $this->assertDoesNotMatchRegularExpression(
            '/\.marquee \.card\s*\{[^}]*max-height:\s*360px/s',
            $css,
        );
        $this->assertMatchesRegularExpression(
            '/\.marquee \.card\s*\{[^}]*min-height:\s*420px/s',
            $css,
        );
        $this->assertMatchesRegularExpression(
            '/\.marquee \.card\s*\{[^}]*width:\s*clamp\(220px,\s*28vw,\s*320px\)/s',
            $css,
        );
        $this->assertMatchesRegularExpression(
            '/\.marquee \.card h4\s*\{[^}]*font-size:\s*var\(--fs-meta\)/s',
            $css,
        );
        $this->assertDoesNotMatchRegularExpression(
            '/\.marquee \.card h4\s*\{[^}]*font-size:\s*var\(--fs-card-title/s',
            $css,
        );
        $this->assertMatchesRegularExpression(
            '/\.marquee \.card h4\s*\{[^}]*white-space:\s*nowrap/s',
            $css,
        );
        $this->assertMatchesRegularExpression(
            '/\.marquee \.card \.more\s*\{[^}]*flex-shrink:\s*0/s',
            $css,
        );
    }

    public function test_home_section_headings_use_the_section_size(): void
    {
        $css = file_get_contents(public_path('assets/css/style_demo.css'));

        $this->assertNotFalse($css);
        $this->assertMatchesRegularExpression(
            '/\.h2\s*\{[^}]*font-size:\s*var\(--fs-h2-section\)/s',
            $css,
        );
        $this->assertMatchesRegularExpression(
            '/\.slide-title\s*\{[^}]*font-size:\s*var\(--fs-h2\)/s',
            $css,
        );
        $this->assertDoesNotMatchRegularExpression(
            '/\.h2\s*\{[^}]*font-size:\s*var\(--fs-h2\)\s*;/s',
            $css,
        );
    }

    public function test_stacked_value_cards_use_a_compact_height_and_padded_copy(): void
    {
        $css = file_get_contents(public_path('assets/css/style_demo.css'));
        $aiCss = file_get_contents(public_path('assets/css/ai-lighting.css'));

        $this->assertNotFalse($css);
        $this->assertNotFalse($aiCss);
        $this->assertMatchesRegularExpression(
            '/--card-height:\s*min\(48svh,\s*400px\)/',
            $css,
        );
        $this->assertDoesNotMatchRegularExpression(
            '/--card-height:\s*min\(72svh,\s*600px\)/',
            $css,
        );
        $this->assertMatchesRegularExpression(
            '/\.card__content>div\s*\{[^}]*place-self:\s*stretch/s',
            $css,
        );
        $this->assertMatchesRegularExpression(
            '/\.card__content>div\s*\{[^}]*align-content:\s*start/s',
            $css,
        );
        $this->assertDoesNotMatchRegularExpression(
            '/\.card__content>div\s*\{[^}]*width:\s*80%/s',
            $css,
        );
        $this->assertMatchesRegularExpression(
            '/\.ai-insights #cards\s*\{[^}]*--card-height:\s*min\(48svh,\s*400px\)/s',
            $aiCss,
        );
        $this->assertDoesNotMatchRegularExpression(
            '/--card-height:\s*min\(7[08]svh,\s*5[68]0px\)/',
            $aiCss,
        );
        $this->assertDoesNotMatchRegularExpression(
            '/--card-height:\s*min\(68svh,\s*640px\)/',
            $aiCss,
        );
    }

    public function test_home_stat_numbers_use_the_hero_size(): void
    {
        $css = file_get_contents(public_path('assets/css/style_demo.css'));

        $this->assertNotFalse($css);
        $this->assertMatchesRegularExpression(
            '/\.stat \.num\s*\{[^}]*font-size:\s*var\(--fs-h2\)/s',
            $css,
        );
        $this->assertDoesNotMatchRegularExpression(
            '/\.stat \.num\s*\{[^}]*font-size:\s*var\(--fs-h2-section\)/s',
            $css,
        );
    }
}
