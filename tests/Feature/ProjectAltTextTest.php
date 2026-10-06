<?php

namespace Tests\Feature;

use App\Models\Project;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProjectAltTextTest extends TestCase
{
    use RefreshDatabase;

    public function test_project_cover_and_gallery_alts_can_be_saved_via_dashboard(): void
    {
        $admin = User::factory()->admin()->create();
        $project = Project::factory()->create([
            'title' => 'The Eve Hotel',
            'slug' => 'the-eve-hotel',
            'location' => 'Sydney NSW',
            'gallery' => ['assets/img/projects/eve-1.jpg', 'assets/img/projects/eve-2.jpg'],
        ]);

        $this->actingAs($admin)
            ->get(route('dashboard.projects.edit', $project))
            ->assertOk()
            ->assertSee('name="cover_alt"', false)
            ->assertSee('name="gallery_alts[0]"', false)
            ->assertSee('name="gallery_alts[1]"', false);

        $this->actingAs($admin)
            ->put(route('dashboard.projects.update', $project), [
                'title' => 'The Eve Hotel',
                'slug' => 'the-eve-hotel',
                'cover_alt' => 'The Eve Hotel facade architectural lighting installation in Sydney',
                'gallery_sync' => '1',
                'keep_gallery' => ['0', '1'],
                'gallery_alts' => [
                    '0' => 'Custom close-up of curved neon flex under the bar counter',
                    '1' => 'Custom cove lighting in the luxury guest suites',
                ],
            ])
            ->assertRedirect(route('dashboard.projects.edit', $project));

        $fresh = $project->fresh();
        $this->assertSame('The Eve Hotel facade architectural lighting installation in Sydney', $fresh->cover_alt);
        $this->assertSame([
            'Custom close-up of curved neon flex under the bar counter',
            'Custom cove lighting in the luxury guest suites',
        ], $fresh->gallery_alts);
    }

    public function test_project_detail_and_listing_render_custom_and_fallback_alt_text(): void
    {
        $project = Project::factory()->create([
            'title' => 'The Eve Hotel',
            'slug' => 'the-eve-hotel',
            'location' => 'Sydney NSW',
            'cover' => 'assets/img/projects/eve-cover.jpg',
            'cover_alt' => 'Custom Eve Hotel Cover Alt',
            'gallery' => ['assets/img/projects/eve-1.jpg', 'assets/img/projects/eve-2.jpg'],
            'gallery_alts' => [
                '0' => 'Custom Eve Hotel Gallery 1 Alt',
                '1' => '', // Empty, should fall back
            ],
        ]);

        $this->get(route('projects'))
            ->assertOk()
            ->assertSee('alt="Custom Eve Hotel Cover Alt"', false);

        $this->get(route('project-detail', ['slug' => 'the-eve-hotel']))
            ->assertOk()
            ->assertSee('alt="Custom Eve Hotel Gallery 1 Alt"', false)
            ->assertSee('alt="The Eve Hotel in Sydney NSW — architectural lighting gallery photo 2"', false);
    }
}
