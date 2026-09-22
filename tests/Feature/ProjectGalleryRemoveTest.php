<?php

namespace Tests\Feature;

use App\Models\Project;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ProjectGalleryRemoveTest extends TestCase
{
    use RefreshDatabase;

    public function test_edit_form_keeps_gallery_images_by_index(): void
    {
        $admin = User::factory()->admin()->create();
        $project = Project::factory()->create([
            'gallery' => ['assets/img/projects/keep-me.jpg'],
        ]);

        $this->actingAs($admin)
            ->get(route('dashboard.projects.edit', $project))
            ->assertOk()
            ->assertSee('name="gallery_sync"', false)
            ->assertSee('data-gallery-item', false)
            ->assertSee('data-remove-gallery', false)
            ->assertSee('name="keep_gallery[]"', false)
            ->assertSee('value="0"', false);
    }

    public function test_updating_a_project_drops_gallery_images_not_kept(): void
    {
        $admin = User::factory()->admin()->create();
        $keep = 'assets/img/projects/keep.jpg';
        $remove = 'assets/img/projects/drop.jpg';
        $project = Project::factory()->create([
            'title' => 'Gallery Project',
            'slug' => 'gallery-project',
            'gallery' => [$keep, $remove],
        ]);

        $this->actingAs($admin)
            ->from(route('dashboard.projects.edit', $project))
            ->put(route('dashboard.projects.update', $project), [
                'title' => 'Gallery Project',
                'slug' => 'gallery-project',
                'gallery_sync' => '1',
                'keep_gallery' => ['0'],
            ])
            ->assertRedirect(route('dashboard.projects.edit', $project));

        $this->assertSame([$keep], $project->fresh()->gallery);
    }

    public function test_updating_a_project_can_clear_the_gallery(): void
    {
        $admin = User::factory()->admin()->create();
        $project = Project::factory()->create([
            'title' => 'Empty Gallery',
            'slug' => 'empty-gallery',
            'gallery' => ['assets/img/projects/drop.jpg'],
        ]);

        $this->actingAs($admin)
            ->put(route('dashboard.projects.update', $project), [
                'title' => 'Empty Gallery',
                'slug' => 'empty-gallery',
                'gallery_sync' => '1',
            ])
            ->assertRedirect();

        $this->assertSame([], $project->fresh()->gallery);
    }

    public function test_saving_without_removing_keeps_the_gallery(): void
    {
        $admin = User::factory()->admin()->create();
        $gallery = ['assets/img/projects/one.jpg', 'assets/img/projects/two.jpg'];
        $project = Project::factory()->create([
            'title' => 'Kept Gallery',
            'slug' => 'kept-gallery',
            'gallery' => $gallery,
        ]);

        $this->actingAs($admin)
            ->put(route('dashboard.projects.update', $project), [
                'title' => 'Kept Gallery',
                'slug' => 'kept-gallery',
                'gallery_sync' => '1',
                'keep_gallery' => ['0', '1'],
            ])
            ->assertRedirect();

        $this->assertSame($gallery, $project->fresh()->gallery);
    }

    public function test_removing_a_managed_gallery_image_deletes_the_stored_file(): void
    {
        Storage::fake('public');

        $admin = User::factory()->admin()->create();
        $keep = '/storage/projects/managed-gallery/gallery/keep.jpg';
        $remove = '/storage/projects/managed-gallery/gallery/drop.jpg';
        Storage::disk('public')->put('projects/managed-gallery/gallery/keep.jpg', 'keep');
        Storage::disk('public')->put('projects/managed-gallery/gallery/drop.jpg', 'drop');

        $project = Project::factory()->create([
            'title' => 'Managed Gallery',
            'slug' => 'managed-gallery',
            'gallery' => [$keep, $remove],
        ]);

        $this->actingAs($admin)
            ->put(route('dashboard.projects.update', $project), [
                'title' => 'Managed Gallery',
                'slug' => 'managed-gallery',
                'gallery_sync' => '1',
                'keep_gallery' => [0],
            ])
            ->assertRedirect();

        $this->assertSame([$keep], $project->fresh()->gallery);
        Storage::disk('public')->assertExists('projects/managed-gallery/gallery/keep.jpg');
        Storage::disk('public')->assertMissing('projects/managed-gallery/gallery/drop.jpg');
    }

    public function test_updating_a_project_removes_remote_gallery_urls_with_query_strings(): void
    {
        $admin = User::factory()->admin()->create();
        $keep = 'https://i0.wp.com/azoogi.com.au/wp-content/uploads/2025/06/Picture3.jpg?fit=1884%2C1513&ssl=1';
        $remove = 'https://i0.wp.com/azoogi.com.au/wp-content/uploads/2025/06/Picture4.jpg?fit=1825%2C2290&ssl=1';
        $project = Project::factory()->create([
            'title' => 'Remote Gallery',
            'slug' => 'remote-gallery',
            'gallery' => [$remove, $keep],
        ]);

        $this->actingAs($admin)
            ->get(route('dashboard.projects.edit', $project))
            ->assertOk()
            ->assertSee('name="keep_gallery[]"', false)
            ->assertDontSee('name="remove_gallery[]"', false);

        $this->actingAs($admin)
            ->from(route('dashboard.projects.edit', $project))
            ->put(route('dashboard.projects.update', $project), [
                'title' => 'Remote Gallery',
                'slug' => 'remote-gallery',
                'gallery_sync' => '1',
                'keep_gallery' => [1],
            ])
            ->assertRedirect(route('dashboard.projects.edit', $project));

        $this->assertSame([$keep], $project->fresh()->gallery);
    }
}
