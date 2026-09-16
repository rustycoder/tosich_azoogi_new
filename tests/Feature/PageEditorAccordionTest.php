<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\AdminUserSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PageEditorAccordionTest extends TestCase
{
    use RefreshDatabase;

    public function test_page_editor_repeatable_items_use_accordions(): void
    {
        $this->seed(AdminUserSeeder::class);
        $admin = User::query()->where('email', 'admin@azoogi.com')->firstOrFail();

        $html = $this->actingAs($admin)
            ->get('/dashboard/content/pages/home')
            ->assertOk()
            ->assertSee('<details class="dash-repeat"', false)
            ->assertSee('name="dash-repeat-slide"', false)
            ->assertSee('<summary class="dash-repeat-title">Item 1</summary>', false)
            ->assertSee('<summary class="dash-repeat-title">Item 2</summary>', false)
            ->assertDontSee('<p class="dash-repeat-title">', false)
            ->getContent();

        $this->assertDoesNotMatchRegularExpression(
            '/<details class="dash-repeat"[^>]*\sopen/i',
            $html,
        );
    }

    public function test_page_editor_image_and_video_fields_show_upload_progress(): void
    {
        $this->seed(AdminUserSeeder::class);
        $admin = User::query()->where('email', 'admin@azoogi.com')->firstOrFail();

        $this->actingAs($admin)
            ->get('/dashboard/content/pages/home')
            ->assertOk()
            ->assertSee('class="dash-upload"', false)
            ->assertSee('role="progressbar"', false)
            ->assertSee('accept="image/*"', false)
            ->assertSee('accept="video/*"', false)
            ->assertSee('Slide image', false)
            ->assertSee('Slide video', false);
    }
}
