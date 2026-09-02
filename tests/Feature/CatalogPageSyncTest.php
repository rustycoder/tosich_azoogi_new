<?php

namespace Tests\Feature;

use App\PageMeta\Catalog;
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
        $this->get('/madrix')->assertOk();
    }
}
