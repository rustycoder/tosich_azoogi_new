<?php

namespace Tests\Feature;

use App\Models\Page;
use App\Models\PageMeta;
use App\Models\User;
use Database\Seeders\AdminUserSeeder;
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

    public function test_home_hero_uses_slide_image_as_video_poster_and_image_fallback(): void
    {
        $html = $this->get('/')->assertOk()->getContent();

        $this->assertStringContainsString('poster="/assets/fallback.webp"', $html);
        $this->assertStringContainsString('poster="/assets/vid2_fallback.jpg"', $html);
        $this->assertStringContainsString("background-image:url('/assets/hero01.jpg')", $html);
        $this->assertStringNotContainsString('Slide poster', $html);
        $this->assertEquals(2, substr_count($html, '<video class="bg-video"'));
    }

    public function test_home_hero_shows_slide_image_when_video_is_empty(): void
    {
        $page = Page::query()->where('slug', 'home')->firstOrFail();

        PageMeta::query()
            ->where('page_id', $page->id)
            ->where('key', 'slide.media.video')
            ->where('sort_order', 0)
            ->update(['value' => '']);

        $html = $this->get('/')->assertOk()->getContent();

        $this->assertStringContainsString("background-image:url('/assets/fallback.webp')", $html);
        $this->assertEquals(1, substr_count($html, '<video class="bg-video"'));
    }

    public function test_home_page_editor_omits_slide_poster(): void
    {
        $this->seed(AdminUserSeeder::class);
        $admin = User::query()->where('email', 'admin@azoogi.com')->firstOrFail();

        $this->actingAs($admin)
            ->get('/dashboard/content/pages/home')
            ->assertOk()
            ->assertDontSee('Slide poster', false)
            ->assertSee('Slide image', false)
            ->assertSee('Slide video', false);
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
            ->assertSee('<span class="accent">Qualified Mesh Lighting</span>', false);

        $css = file_get_contents(public_path('assets/css/style_demo.css'));

        $this->assertNotFalse($css);
        $this->assertMatchesRegularExpression(
            '/-webkit-text-stroke:\s*1\.35px\s*var\(--accent\);\s*filter:\s*drop-shadow\(0 0 0\.1em/s',
            $css,
        );
        $this->assertDoesNotMatchRegularExpression(
            '/:root\s*\{[^}]*--outline-glow:/s',
            $css,
        );
        $this->assertMatchesRegularExpression(
            '/-webkit-text-stroke:\s*1\.35px\s*var\(--accent\)/',
            $css,
        );
        $this->assertDoesNotMatchRegularExpression(
            '/-webkit-text-stroke-color:\s*#8cc63f/',
            $css,
        );
        $this->assertDoesNotMatchRegularExpression(
            '/@keyframes\s+outline-led\s*\{/',
            $css,
        );
        $this->assertDoesNotMatchRegularExpression(
            '/animation:\s*outline-led/',
            $css,
        );

        $aboutCss = file_get_contents(public_path('assets/css/about.css'));

        $this->assertNotFalse($aboutCss);
        $this->assertDoesNotMatchRegularExpression(
            '/animation:\s*outline-led/',
            $aboutCss,
        );
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
            '/--card-height:\s*min\((?:48|56)svh,\s*400px\)/',
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
        $this->assertMatchesRegularExpression(
            '/#cards\s*\{[^}]*grid-template-rows:\s*repeat\(var\(--numcards\),\s*auto\)/s',
            $css,
        );
        $this->assertMatchesRegularExpression(
            '/\.card__content\s*\{[^}]*max-height:\s*none/s',
            $css,
        );
        $this->assertDoesNotMatchRegularExpression(
            '/\.card__content>div\s*\{[^}]*overflow:\s*auto/s',
            $css,
        );
        $this->assertMatchesRegularExpression(
            '/\.ai-insights \.card__content\s*\{[^}]*max-height:\s*none/s',
            $aiCss,
        );
        $this->assertMatchesRegularExpression(
            '/\.card__content>figure>img\s*\{[^}]*position:\s*absolute/s',
            $css,
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
