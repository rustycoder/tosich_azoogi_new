<?php

namespace Tests\Feature;

use App\Models\User;
use App\PageMeta\Catalog;
use App\PageMeta\FieldType;
use App\PageMeta\ImageSize;
use Database\Seeders\AdminUserSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PageEditorImageSizeTest extends TestCase
{
    use RefreshDatabase;

    public function test_every_image_field_has_a_recommended_size_hint(): void
    {
        $imageFields = 0;

        foreach (Catalog::all() as $definition) {
            foreach ($definition->fields() as $field) {
                if ($field->type !== FieldType::Image) {
                    continue;
                }

                $imageFields++;
                $this->assertNotSame(
                    '',
                    $field->hint,
                    "{$definition->slug()} {$field->key} is missing a recommended image size.",
                );
                $this->assertStringStartsWith('Recommended:', $field->hint);
            }
        }

        $this->assertGreaterThan(0, $imageFields);
    }

    public function test_page_editor_shows_recommended_sizes_next_to_image_uploads(): void
    {
        $this->seed(AdminUserSeeder::class);
        $admin = User::query()->where('email', 'admin@azoogi.com')->firstOrFail();

        $this->actingAs($admin)
            ->get('/dashboard/content/pages/home')
            ->assertOk()
            ->assertSee('<small>'.ImageSize::Hero.'</small>', false)
            ->assertSee('<small>'.ImageSize::Card.'</small>', false);

        $this->actingAs($admin)
            ->get('/dashboard/content/pages/about')
            ->assertOk()
            ->assertSee('<small>'.ImageSize::Hero.'</small>', false)
            ->assertSee('<small>'.ImageSize::Panel.'</small>', false)
            ->assertSee('<small>'.ImageSize::Banner.'</small>', false)
            ->assertSee('Path title', false)
            ->assertSee('Path body', false)
            ->assertSee('Path href', false)
            ->assertDontSee('Path image', false)
            ->assertDontSee('<small>'.ImageSize::Square.'</small>', false);

        $this->actingAs($admin)
            ->get('/dashboard/content/pages/casambi')
            ->assertOk()
            ->assertSee('<small>'.ImageSize::Banner.'</small>', false)
            ->assertSee('<small>'.ImageSize::Logo.'</small>', false)
            ->assertSee('<small>'.ImageSize::Software.'</small>', false)
            ->assertSee('<small>'.ImageSize::Product.'</small>', false);
    }
}
