<?php

namespace Tests\Feature;

use App\Enums\Status;
use App\Models\Page;
use App\Models\Project;
use Database\Seeders\PageSeeder;
use Database\Seeders\ProjectSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ContentVisibilityTest extends TestCase
{
    use RefreshDatabase;

    public function test_inactive_pages_are_hidden_on_the_public_site(): void
    {
        $this->seed(PageSeeder::class);

        $page = Page::query()->where('slug', 'about')->firstOrFail();
        $page->update(['status' => Status::Inactive]);

        $this->get('/about')->assertNotFound();
    }

    public function test_soft_deleted_pages_are_hidden_on_the_public_site(): void
    {
        $this->seed(PageSeeder::class);

        $page = Page::query()->where('slug', 'about')->firstOrFail();
        $page->delete();

        $this->get('/about')->assertNotFound();
        $this->assertSoftDeleted($page);
    }

    public function test_inactive_and_deleted_projects_are_hidden_on_the_public_site(): void
    {
        $inactive = Project::factory()->inactive()->create(['slug' => 'quiet-house']);
        $deleted = Project::factory()->create(['slug' => 'gone-gallery']);
        $deleted->delete();

        $this->get('/project-detail?slug=quiet-house')->assertNotFound();
        $this->get('/project-detail?slug=gone-gallery')->assertNotFound();
        $this->get('/projects')->assertDontSee($inactive->title, false);
    }

    public function test_active_projects_appear_on_the_listing(): void
    {
        $this->seed(ProjectSeeder::class);

        $this->get('/projects')
            ->assertOk()
            ->assertSee('Zushi Restaurant', false);
    }

    public function test_home_shows_every_featured_project(): void
    {
        $this->seed(PageSeeder::class);

        $featured = collect(range(1, 5))->map(
            fn (int $order) => Project::factory()->featured($order)->create([
                'title' => "Featured Hall {$order}",
            ]),
        );

        $hidden = Project::factory()->create([
            'title' => 'Not Featured Hall',
            'featured' => false,
        ]);

        $home = $this->get('/')->assertOk();

        foreach ($featured as $project) {
            $home->assertSee($project->title, false);
        }

        $home->assertDontSee($hidden->title, false);
    }
}
