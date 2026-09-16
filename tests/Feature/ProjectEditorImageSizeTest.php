<?php

namespace Tests\Feature;

use App\Models\Project;
use App\Models\User;
use App\PageMeta\ImageSize;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProjectEditorImageSizeTest extends TestCase
{
    use RefreshDatabase;

    public function test_create_and_edit_forms_show_recommended_cover_and_gallery_sizes(): void
    {
        $admin = User::factory()->admin()->create();
        $project = Project::factory()->create();

        $this->actingAs($admin)
            ->get('/dashboard/content/projects/create')
            ->assertOk()
            ->assertSee('<small>'.ImageSize::Cover.'</small>', false)
            ->assertSee('<small>'.ImageSize::Gallery.'</small>', false);

        $this->actingAs($admin)
            ->get(route('dashboard.projects.edit', $project))
            ->assertOk()
            ->assertSee('<small>'.ImageSize::Cover.'</small>', false)
            ->assertSee('<small>'.ImageSize::Gallery.'</small>', false);
    }

    public function test_cover_image_upload_sits_below_the_remote_url(): void
    {
        $admin = User::factory()->admin()->create();

        $html = $this->actingAs($admin)
            ->get('/dashboard/content/projects/create')
            ->assertOk()
            ->getContent();

        $this->assertMatchesRegularExpression(
            '/<div class="dash-field is-wide">\s*<label for="cover_remote"[\s\S]*?<div class="dash-field is-wide">\s*<label for="cover_file"/',
            $html,
        );
    }
}
