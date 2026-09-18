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

    public function test_edit_form_marks_gallery_images_for_removal(): void
    {
        $admin = User::factory()->admin()->create();
        $image = 'assets/img/projects/keep-me.jpg';
        $project = Project::factory()->create([
            'gallery' => [$image],
        ]);

        $this->actingAs($admin)
            ->get(route('dashboard.projects.edit', $project))
            ->assertOk()
            ->assertSee('data-gallery-item', false)
            ->assertSee('data-remove-gallery="'.$image.'"', false)
            ->assertSee('name="remove_gallery[]"', false)
            ->assertSee('value="'.$image.'"', false);
    }

    public function test_updating_a_project_removes_selected_gallery_images(): void
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
                'remove_gallery' => [$remove],
            ])
            ->assertRedirect(route('dashboard.projects.edit', $project));

        $this->assertSame([$keep], $project->fresh()->gallery);
    }

    public function test_gallery_remove_matches_paths_with_or_without_a_leading_slash(): void
    {
        $admin = User::factory()->admin()->create();
        $project = Project::factory()->create([
            'title' => 'Slash Gallery',
            'slug' => 'slash-gallery',
            'gallery' => ['/assets/img/projects/drop.jpg', 'assets/img/projects/keep.jpg'],
        ]);

        $this->actingAs($admin)
            ->put(route('dashboard.projects.update', $project), [
                'title' => 'Slash Gallery',
                'slug' => 'slash-gallery',
                'remove_gallery' => ['assets/img/projects/drop.jpg'],
            ])
            ->assertRedirect();

        $this->assertSame(['assets/img/projects/keep.jpg'], $project->fresh()->gallery);
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
                'remove_gallery' => ['storage/projects/managed-gallery/gallery/drop.jpg'],
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

        $html = $this->actingAs($admin)
            ->get(route('dashboard.projects.edit', $project))
            ->assertOk()
            ->getContent();

        $this->assertStringContainsString('data-remove-gallery="'.e($remove).'"', $html);
        $this->assertStringContainsString('value="'.e($remove).'"', $html);

        $this->actingAs($admin)
            ->from(route('dashboard.projects.edit', $project))
            ->put(route('dashboard.projects.update', $project), [
                'title' => 'Remote Gallery',
                'slug' => 'remote-gallery',
                'remove_gallery' => [rawurldecode($remove)],
            ])
            ->assertRedirect(route('dashboard.projects.edit', $project));

        $this->assertSame([$keep], $project->fresh()->gallery);
    }
}
