<?php

namespace Tests\Feature;

use App\Enums\Status;
use App\Models\Page;
use App\Models\PageMeta;
use App\Models\Project;
use App\Models\User;
use Database\Seeders\AdminUserSeeder;
use Database\Seeders\PageSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProjectsPageTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(PageSeeder::class);
    }

    public function test_projects_listing_chrome_comes_from_cms(): void
    {
        $this->get('/projects')
            ->assertOk()
            ->assertSee('Projects Powered by <span class="accent">Azoogi</span>', false)
            ->assertSee('majorprojects@azoogi.com', false)
            ->assertSee('Showing', false)
            ->assertDontSee('projects-highlights', false)
            ->assertDontSee('Recent Highlights', false);
    }

    public function test_projects_listing_does_not_render_featured_highlights(): void
    {
        Project::factory()->featured()->create([
            'title' => 'Harbour Pavilion',
        ]);

        $this->get('/projects')
            ->assertOk()
            ->assertDontSee('class="projects-highlights"', false)
            ->assertDontSee('id="highlightsGrid"', false)
            ->assertSee('class="project-card-cap"', false)
            ->assertSee('Harbour Pavilion', false);
    }

    public function test_projects_hero_uses_solid_and_outline_type(): void
    {
        $css = file_get_contents(public_path('assets/css/style_demo.css'));

        $this->assertNotFalse($css);
        $this->assertMatchesRegularExpression(
            '/\.projects-hero \.h2 span\s*,/s',
            $css,
        );
        $this->assertStringContainsString('-webkit-text-stroke: 1.35px var(--accent)', $css);

        $listingCss = file_get_contents(public_path('assets/css/projects.css'));
        $this->assertNotFalse($listingCss);
        $this->assertDoesNotMatchRegularExpression(
            '/\.projects-grid\s*\{[^}]*grid-template-columns:\s*1fr/s',
            $listingCss,
        );
        $this->assertMatchesRegularExpression(
            '/\.projects-grid\s*\{[^}]*grid-template-columns:\s*repeat\(12,\s*1fr\)/s',
            $listingCss,
        );
        $this->assertMatchesRegularExpression(
            '/\.project-card-cap h3\s*\{[^}]*font-size:\s*var\(--fs-card-title\)/s',
            $listingCss,
        );
    }

    public function test_project_detail_chrome_comes_from_cms(): void
    {
        $project = Project::factory()->create([
            'title' => 'Harbour Pavilion',
            'location' => 'Sydney',
            'type' => 'Hospitality',
            'completed' => '2024',
        ]);

        $html = $this->get('/project-detail?slug='.$project->slug)
            ->assertOk()
            ->assertSee('All Projects', false)
            ->assertSee('Project Overview', false)
            ->assertSee('Harbour Pavilion', false)
            ->assertSee('Sydney', false)
            ->assertSee('Hospitality', false)
            ->assertSee('2024', false)
            ->assertSee('class="project-detail-hero-media"', false)
            ->assertSee('class="project-detail-hero-copy"', false)
            ->assertSee('class="project-back"', false)
            ->assertSee('class="project-detail-hero-tags"', false)
            ->assertSee('class="project-tag-icon"', false)
            ->assertDontSee('Location:', false)
            ->assertDontSee('class="project-meta-rows"', false)
            ->getContent();

        $hero = strpos($html, 'class="project-detail-hero"');
        $tags = strpos($html, 'class="project-detail-hero-tags"');
        $description = strpos($html, 'class="project-description"');
        $gallery = strpos($html, 'class="project-gallery"');

        $this->assertNotFalse($hero);
        $this->assertNotFalse($tags);
        $this->assertNotFalse($description);
        $this->assertGreaterThan($hero, $tags);
        $this->assertGreaterThan($tags, $description);
        $this->assertFalse($gallery);
    }

    public function test_project_detail_renders_description_before_gallery(): void
    {
        $project = Project::factory()->create([
            'title' => 'Harbour Pavilion',
            'description' => 'Pavilion lighting across the harbour boardwalk.',
            'gallery' => ['/assets/img/img-1.jpg', '/assets/img/img-2.jpg'],
        ]);

        $html = $this->get('/project-detail?slug='.$project->slug)
            ->assertOk()
            ->assertSee('Pavilion lighting across the harbour boardwalk.', false)
            ->assertSee('class="project-gallery-section"', false)
            ->getContent();

        $hero = strpos($html, 'class="project-detail-hero"');
        $description = strpos($html, 'class="project-description"');
        $gallery = strpos($html, 'class="project-gallery-section"');

        $this->assertNotFalse($hero);
        $this->assertNotFalse($description);
        $this->assertNotFalse($gallery);
        $this->assertGreaterThan($hero, $description);
        $this->assertGreaterThan($description, $gallery);
    }

    public function test_project_detail_overview_uses_label_and_lead_type(): void
    {
        $css = file_get_contents(public_path('assets/css/projects.css'));

        $this->assertNotFalse($css);
        $this->assertMatchesRegularExpression(
            '/\.project-detail-hero\s*\{[^}]*height:\s*50vh/s',
            $css,
        );
        $this->assertMatchesRegularExpression(
            '/\.project-detail-hero\s*\{[^}]*min-height:\s*75vh/s',
            $css,
        );
        $this->assertMatchesRegularExpression(
            '/\.project-detail-hero-media\s*\{[^}]*position:\s*absolute/s',
            $css,
        );
        $this->assertMatchesRegularExpression(
            '/\.project-info h2\s*\{[^}]*font-size:\s*var\(--fs-h2-section\)/s',
            $css,
        );
        $this->assertDoesNotMatchRegularExpression(
            '/\.project-detail-hero \.project-back\s*\{[^}]*position:\s*absolute/s',
            $css,
        );
        $this->assertMatchesRegularExpression(
            '/\.project-back\s*\{[^}]*border-radius:\s*999px/s',
            $css,
        );
        $this->assertMatchesRegularExpression(
            '/\.project-detail-hero-tags\s*\{[^}]*display:\s*flex/s',
            $css,
        );
        $this->assertMatchesRegularExpression(
            '/\.project-detail-hero \.project-tag-icon\s*\{[^}]*width:\s*1em/s',
            $css,
        );
        $this->assertMatchesRegularExpression(
            '/\.project-detail-hero \.project-tag\s*\{[^}]*background:\s*var\(--accent\)/s',
            $css,
        );
        $this->assertMatchesRegularExpression(
            '/\.project-description\s*\{[^}]*font-size:\s*var\(--fs-lead\)/s',
            $css,
        );
        $this->assertDoesNotMatchRegularExpression(
            '/\.project-description\s*\{[^}]*font-size:\s*var\(--fs-h3\)/s',
            $css,
        );
        $this->assertDoesNotMatchRegularExpression(
            '/\.project-description\s*\{[^}]*max-width:\s*46em/s',
            $css,
        );
        $this->assertMatchesRegularExpression(
            '/\.project-description\s*\{[^}]*width:\s*100%/s',
            $css,
        );
        $this->assertMatchesRegularExpression(
            '/\.project-gallery \.image\s*\{[^}]*aspect-ratio:\s*1\s*\/\s*1/s',
            $css,
        );
        $this->assertDoesNotMatchRegularExpression(
            '/\.project-gallery \.image img\s*\{[^}]*min-height:\s*220px/s',
            $css,
        );
    }

    public function test_projects_page_accepts_font_size_and_alignment(): void
    {
        $this->seed(AdminUserSeeder::class);
        $admin = User::query()->where('email', 'admin@azoogi.com')->firstOrFail();
        $page = Page::query()->where('slug', 'projects')->firstOrFail();
        $meta = PageMeta::query()->where('page_id', $page->id)->where('key', 'detail.overview')->firstOrFail();

        $this->actingAs($admin)
            ->get(route('dashboard.pages.edit', $page))
            ->assertOk()
            ->assertDontSee('<label>Font size</label>', false)
            ->assertDontSee('<label>Alignment</label>', false);

        $this->actingAs($admin)
            ->put(route('dashboard.pages.update', $page), [
                'title' => $page->title,
                'meta_description' => $page->meta_description,
                'status' => Status::Active->value,
                'meta' => [
                    $meta->id => [
                        'value' => $meta->value,
                        'font_size' => '28px',
                        'text_align' => 'left',
                    ],
                ],
            ])
            ->assertRedirect();

        $this->assertSame('28px', $meta->fresh()->font_size);
        $this->assertSame('left', $meta->fresh()->text_align);
    }
}
