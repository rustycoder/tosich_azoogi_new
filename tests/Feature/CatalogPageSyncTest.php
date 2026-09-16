<?php

namespace Tests\Feature;

use App\Models\Page;
use App\Models\PageMeta;
use App\PageMeta\Catalog;
use App\PageMeta\CatalogSync;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CatalogPageSyncTest extends TestCase
{
    use RefreshDatabase;

    public function test_migrations_create_every_catalog_page_without_seeding(): void
    {
        foreach (Catalog::slugs() as $slug) {
            $this->assertDatabaseHas('pages', [
                'slug' => $slug,
                'status' => 'active',
            ]);
        }
    }

    public function test_core_pages_render_after_migrate_without_seeding(): void
    {
        $this->get('/about')->assertOk();
        $this->get('/contact')->assertOk();
        $this->get('/solutions')->assertOk();
        $this->get('/casambi')->assertOk();
        $this->get('/silvair')->assertOk();
        $this->get('/dali-centre')->assertOk();
        $this->get('/madrix')->assertOk();
        $this->get('/products')->assertOk();
        $this->get('/projects')->assertOk();
        $this->get('/request-a-quote')->assertOk();
        $this->get('/led-strip-calculator')->assertOk();
    }

    public function test_about_path_item_images_are_not_stored(): void
    {
        $this->assertDatabaseMissing('page_meta', [
            'key' => 'path.item.image',
        ]);
    }

    public function test_catalog_sync_prunes_leftover_about_path_item_images(): void
    {
        $page = Page::query()->where('slug', 'about')->firstOrFail();

        PageMeta::query()->create([
            'page_id' => $page->id,
            'key' => 'path.item.image',
            'sort_order' => 0,
            'value' => '/assets/img/leds.webp',
        ]);

        CatalogSync::pages();

        $this->assertDatabaseMissing('page_meta', [
            'page_id' => $page->id,
            'key' => 'path.item.image',
        ]);
    }
}
