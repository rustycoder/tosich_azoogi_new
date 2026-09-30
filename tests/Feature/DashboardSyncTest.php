<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\ProductAttribute;
use App\Models\ProductCategory;
use App\Models\User;
use App\Services\Contracts\ICatalogAuditService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardSyncTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_cannot_access_sync_dashboard(): void
    {
        $this->get('/dashboard/sync')->assertRedirect('/login');
    }

    public function test_admin_can_view_airtable_sync_dashboard(): void
    {
        $admin = User::factory()->admin()->create();

        ProductCategory::create([
            'airtable_id' => 'recCatTest001',
            'name' => 'Downlights',
            'sort_order' => 1,
        ]);
        ProductAttribute::create([
            'airtable_id' => 'recAttrTest001',
            'name' => 'Color Temp',
            'value' => '3000K',
            'is_visible_on_filters' => true,
        ]);
        Product::factory()->create([
            'product_name' => 'Azoogi LED Downlight Pro',
            'product_code' => 'AZ-DL-001',
            'slug' => 'azoogi-led-downlight-pro',
            'category' => 'Downlights',
            'status' => 'publish',
            'cover' => 'https://example.com/cover.jpg',
            'datasheet' => 'Yes',
            'datasheet_file' => 'https://example.com/datasheet.pdf',
        ]);

        $response = $this->actingAs($admin)->get('/dashboard/sync');

        $response->assertOk()
            ->assertSee('Airtable Sync', false)
            ->assertSee('Catalog Health Score', false)
            ->assertSee('Total Products', false)
            ->assertSee('Categories', false)
            ->assertSee('Product Attributes', false)
            ->assertSee('Media &amp; Schematics', false)
            ->assertSee('Technical Documents', false)
            ->assertSee('Core ID &amp; Inventory', false)
            ->assertSee('Copy &amp; SEO Meta', false);
    }

    public function test_catalog_audit_service_calculates_health_and_issues(): void
    {
        Product::factory()->create([
            'product_name' => 'Complete Product',
            'product_code' => 'SKU-001',
            'slug' => 'complete-product',
            'category' => 'Linear Lights',
            'status' => 'publish',
            'cover' => 'https://example.com/cover.jpg',
            'product_images' => ['https://example.com/cover.jpg'],
            'product_dimension' => ['https://example.com/dim.jpg'],
            'technical_icons' => ['https://example.com/icon.svg'],
            'product_description' => 'A complete architectural linear light description.',
            'meta_title' => 'Complete Product Title',
            'meta_description' => 'Complete meta description for SEO.',
            'datasheet' => 'Yes',
            'datasheet_file' => 'https://example.com/datasheet.pdf',
            'installation_guide_file' => 'https://example.com/guide.pdf',
            'user_manual' => 'https://example.com/manual.pdf',
            'ies_file' => 'https://example.com/light.ies',
            'supplier_code' => 'SUP-001',
        ]);

        Product::factory()->create([
            'product_name' => 'Incomplete Product',
            'product_code' => null,
            'slug' => null,
            'category' => null,
            'cover' => null,
            'product_images' => null,
            'product_dimension' => null,
            'datasheet' => 'Yes',
            'datasheet_file' => null,
            'product_description' => null,
            'meta_title' => null,
            'meta_description' => null,
        ]);

        /** @var ICatalogAuditService $service */
        $service = app(ICatalogAuditService::class);
        $audit = $service->audit();

        $this->assertEquals(2, $audit['summary']['total_products']);
        $this->assertGreaterThanOrEqual(1, $audit['media']['missing_cover']['count']);
        $this->assertGreaterThanOrEqual(1, $audit['documents']['missing_datasheet_file']['count']);
        $this->assertGreaterThanOrEqual(1, $audit['core']['missing_sku']['count']);
        $this->assertGreaterThanOrEqual(1, $audit['seo']['missing_slug']['count']);
        $this->assertIsInt($audit['summary']['health_score']);
    }
}
