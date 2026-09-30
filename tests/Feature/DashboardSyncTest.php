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

    protected function setUp(): void
    {
        parent::setUp();

        app(ICatalogAuditService::class)->clearStoredAudit();
    }

    public function test_guests_cannot_access_sync_dashboard(): void
    {
        $this->get('/dashboard/sync')->assertRedirect('/login');
    }

    public function test_admin_can_view_sync_dashboard_and_trigger_manual_audit(): void
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

        // 1. Initial view before any audit is run shows the empty state with the Run Audit action button
        $response = $this->actingAs($admin)->get('/dashboard/sync');
        $response->assertOk()
            ->assertSee('Airtable Sync &amp; Catalog Audit', false)
            ->assertSee('Run Catalog Audit', false)
            ->assertSee('No Catalog Audit Report Available', false);

        // 2. Triggering the on-demand audit action calculates and caches the report
        $auditPost = $this->actingAs($admin)->post('/dashboard/sync/audit');
        $auditPost->assertRedirect('/dashboard/sync');

        $this->assertNotNull(app(ICatalogAuditService::class)->getLatestAudit());

        // 3. Visiting/reloading /dashboard/sync now displays the stored audit metrics
        $responseAfterAudit = $this->actingAs($admin)->get('/dashboard/sync');
        $responseAfterAudit->assertOk()
            ->assertSee('Catalog Health Score', false)
            ->assertSee('Total Products', false)
            ->assertSee('Asset Format Standards', false)
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

    public function test_catalog_audit_flags_non_webp_images_and_non_svg_icons(): void
    {
        // 1. Product with non-webp cover (.jpg), non-webp dimension (.png), and non-svg technical icon (.png)
        Product::factory()->create([
            'product_name' => 'Legacy Format Light',
            'product_code' => 'AZ-LEGACY-01',
            'slug' => 'legacy-format-light',
            'cover' => 'https://example.com/images/lamp.jpg',
            'product_images' => ['https://example.com/images/lamp_gallery.png'],
            'product_dimension' => ['https://example.com/images/lamp_dim.png'],
            'technical_icons' => ['https://example.com/icons/dimmable.png'],
        ]);

        // 2. Product with compliant webp images, webp dimensions, and svg icons
        Product::factory()->create([
            'product_name' => 'Modern Compliant Light',
            'product_code' => 'AZ-MODERN-01',
            'slug' => 'modern-compliant-light',
            'cover' => 'https://example.com/images/lamp.webp',
            'product_images' => ['https://example.com/images/lamp_gallery.webp'],
            'product_dimension' => ['https://example.com/images/lamp_dim.webp'],
            'technical_icons' => ['https://example.com/icons/dimmable.svg'],
        ]);

        /** @var ICatalogAuditService $service */
        $service = app(ICatalogAuditService::class);
        $audit = $service->audit();

        $this->assertEquals(1, $audit['image_standards']['non_webp_covers']['count']);
        $this->assertEquals('JPG', $audit['image_standards']['non_webp_covers']['samples'][0]['format']);
        $this->assertEquals(1, $audit['image_standards']['non_webp_gallery']['count']);
        $this->assertEquals(1, $audit['image_standards']['non_webp_dimensions']['count']);
        $this->assertEquals('PNG', $audit['image_standards']['non_webp_dimensions']['samples'][0]['format']);
        $this->assertEquals(1, $audit['image_standards']['non_svg_tech_icons']['count']);
        $this->assertEquals('PNG', $audit['image_standards']['non_svg_tech_icons']['samples'][0]['format']);
    }
}
