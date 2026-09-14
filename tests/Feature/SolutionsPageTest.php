<?php

namespace Tests\Feature;

use Database\Seeders\PageSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SolutionsPageTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(PageSeeder::class);
    }

    public function test_solutions_page_renders_hero_copy(): void
    {
        $this->get('/solutions')
            ->assertOk()
            ->assertSee('class="h2 solutions-title"', false)
            ->assertSee('End-to-End Lighting Solutions', false)
            ->assertSee('<span>Intelligent Controls</span>', false)
            ->assertDontSee('solutions-kicker', false)
            ->assertDontSee('solutions-hero-logo', false);
    }

    public function test_solutions_hero_aligns_with_the_projects_hero(): void
    {
        $css = file_get_contents(public_path('assets/css/solutions.css'));

        $this->assertNotFalse($css);
        $this->assertMatchesRegularExpression(
            '/\.solutions-hero\s*\{[^}]*padding:\s*140px\s+0\s+0\s*;/s',
            $css,
        );
        $this->assertMatchesRegularExpression(
            '/\.solutions-title\s*\{[^}]*font-size:\s*var\(--fs-h2\)/s',
            $css,
        );
        $this->assertMatchesRegularExpression(
            '/\.solutions-title\s*\{[^}]*margin:\s*0\s+0\s+20px\s*;/s',
            $css,
        );
        $this->assertMatchesRegularExpression(
            '/\.solutions-copy\s*\{[^}]*max-width:\s*820px/s',
            $css,
        );
        $this->assertStringNotContainsString('.solutions-copy::before', $css);
        $this->assertDoesNotMatchRegularExpression(
            '/\.solutions-main\s*\{[^}]*padding:\s*1[12]0px/s',
            $css,
        );
    }
}
