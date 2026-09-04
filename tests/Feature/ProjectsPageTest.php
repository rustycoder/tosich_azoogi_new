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
            ->assertSee('Projects Powered by Azoogi', false)
            ->assertSee('Recent', false)
            ->assertSee('Highlights', false)
            ->assertSee('majorprojects@azoogi.com', false)
            ->assertSee('Showing', false);
    }

    public function test_project_detail_chrome_comes_from_cms(): void
    {
        $project = Project::factory()->create([
            'title' => 'Harbour Pavilion',
            'location' => 'Sydney',
            'type' => 'Hospitality',
            'completed' => '2024',
        ]);

        $this->get('/project-detail?slug='.$project->slug)
            ->assertOk()
            ->assertSee('All Projects', false)
            ->assertSee('Project Overview', false)
            ->assertSee('Location:', false)
            ->assertSee('Type:', false)
            ->assertSee('Completed:', false)
            ->assertSee('Harbour Pavilion', false);
    }

    public function test_projects_page_accepts_font_size_and_alignment(): void
    {
        $this->seed(AdminUserSeeder::class);
        $admin = User::query()->where('email', 'admin@azoogi.com')->firstOrFail();
        $page = Page::query()->where('slug', 'projects')->firstOrFail();
        $meta = PageMeta::query()->where('page_id', $page->id)->where('key', 'hero.title')->firstOrFail();

        $this->actingAs($admin)
            ->get(route('dashboard.pages.edit', $page))
            ->assertOk()
            ->assertSee('Font size', false)
            ->assertSee('Alignment', false);

        $this->actingAs($admin)
            ->put(route('dashboard.pages.update', $page), [
                'title' => $page->title,
                'meta_description' => $page->meta_description,
                'status' => Status::Active->value,
                'meta' => [
                    $meta->id => [
                        'value' => $meta->value,
                        'font_size' => '32px',
                        'text_align' => 'left',
                    ],
                ],
            ])
            ->assertRedirect();

        $this->assertSame('32px', $meta->fresh()->font_size);
        $this->assertSame('left', $meta->fresh()->text_align);

        $this->get('/projects')
            ->assertOk()
            ->assertSee('style="font-size: 32px; text-align: left"', false);
    }
}
