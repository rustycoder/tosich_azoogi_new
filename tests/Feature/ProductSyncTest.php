<?php

namespace Tests\Feature;

use App\Enums\ContentResource;
use App\Enums\ProductSyncStatus;
use App\Jobs\SyncProductsJob;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\ProductSync;
use App\Models\User;
use App\Services\Contracts\IProductSyncService;
use App\Support\ProductCatalog;
use Database\Seeders\PageSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class ProductSyncTest extends TestCase
{
    use RefreshDatabase;

    public function test_product_tables_use_long_text_and_have_no_export_table(): void
    {
        $this->assertTrue(Schema::hasTable('products'));
        $this->assertTrue(Schema::hasTable('product_categories'));
        $this->assertTrue(Schema::hasTable('product_attributes'));
        $this->assertTrue(Schema::hasTable('product_syncs'));
        $this->assertFalse(Schema::hasTable('catalog_exports'));
        $this->assertFalse(Schema::hasTable('catalog_products'));

        $migration = file_get_contents(database_path('migrations/2026_09_04_120000_create_product_tables.php'));

        $this->assertNotFalse($migration);
        $this->assertFalse(Schema::hasColumn('products', 'payload'));
        $this->assertFalse(Schema::hasColumn('product_categories', 'payload'));
        $this->assertFalse(Schema::hasColumn('product_attributes', 'payload'));
        $this->assertTrue(Schema::hasColumn('products', 'product_images'));
        $this->assertTrue(Schema::hasColumn('products', 'product_features'));
        $this->assertTrue(Schema::hasColumn('product_attributes', 'icon'));
        $this->assertStringContainsString("longText('product_images')", $migration);
        $this->assertStringNotContainsString('$table->json(', $migration);
    }

    public function test_public_pages_load_products_from_the_database(): void
    {
        $this->seed(PageSeeder::class);

        Product::factory()->create([
            'airtable_id' => 'recGarden',
            'product_name' => 'Garden Light',
            'category' => 'NEON',
            'status' => 'publish',
            'categories' => ['NEON'],
            'category_path' => ['NEON'],
            'product_images' => ['assets/img/neon.webp'],
        ]);

        ProductCategory::query()->create([
            'airtable_id' => 'recNeon',
            'name' => 'NEON',
            'sort_order' => 1,
        ]);

        $this->get('/products')
            ->assertOk()
            ->assertSee('const AZOOGI_PRODUCTS', false)
            ->assertSee('Garden Light', false)
            ->assertSee('NEON', false)
            ->assertDontSee('products_data.js', false)
            ->assertDontSee('/products.js', false);
    }

    public function test_parent_categories_come_from_the_products_table(): void
    {
        Product::factory()->create([
            'airtable_id' => 'recCustom',
            'product_name' => 'Custom Neon',
            'category' => 'Custom Cat',
            'status' => 'publish',
            'categories' => ['Custom Cat'],
            'category_path' => ['Custom Cat'],
            'product_images' => ['assets/img/neon.webp'],
        ]);

        ProductCategory::query()->create([
            'airtable_id' => 'recCustomCat',
            'name' => 'Custom Cat',
            'sort_order' => 1,
        ]);

        $titles = array_column(ProductCatalog::parentCategories(), 'title');

        $this->assertContains('Custom Cat', $titles);
        $this->assertNotContains('NEON', $titles);
    }

    public function test_sync_stores_published_products_and_skips_drafts(): void
    {
        config([
            'airtable.api_key' => 'test-key',
            'airtable.base_id' => 'appTest',
        ]);

        Http::fake(function (Request $request) {
            $url = $request->url();

            if (str_contains($url, 'Categories')) {
                return Http::response(['records' => [
                    ['id' => 'recNeon', 'fields' => ['Name' => 'NEON', 'Order' => 1]],
                ]]);
            }

            if (str_contains($url, 'attributes') || str_contains($url, 'Attributes')) {
                return Http::response(['records' => []]);
            }

            return Http::response(['records' => [
                [
                    'id' => 'recPublish',
                    'fields' => [
                        'Product Name' => 'Garden Light',
                        'Status' => 'publish',
                        'Order' => 1,
                        'Category' => 'NEON',
                    ],
                ],
                [
                    'id' => 'recDraft',
                    'fields' => [
                        'Product Name' => 'Hidden Draft',
                        'Status' => 'draft',
                        'Order' => 2,
                    ],
                ],
            ]]);
        });

        app(IProductSyncService::class)->sync('test');

        $this->assertDatabaseHas('products', [
            'airtable_id' => 'recPublish',
            'product_name' => 'Garden Light',
            'category' => 'NEON',
        ]);
        $this->assertFalse(Schema::hasColumn('products', 'payload'));
        $this->assertDatabaseMissing('products', [
            'airtable_id' => 'recDraft',
        ]);
        $this->assertDatabaseHas('product_categories', [
            'airtable_id' => 'recNeon',
            'name' => 'NEON',
        ]);

        $this->get('/products')
            ->assertOk()
            ->assertSee('Garden Light', false)
            ->assertDontSee('Hidden Draft', false);
    }

    public function test_sync_fetches_all_airtable_pages_before_saving(): void
    {
        config([
            'airtable.api_key' => 'test-key',
            'airtable.base_id' => 'appTest',
        ]);

        Product::factory()->create([
            'airtable_id' => 'recStale',
            'product_name' => 'Removed Light',
        ]);

        Http::fake(function (Request $request) {
            $url = $request->url();

            if (str_contains($url, 'Categories')) {
                return Http::response(['records' => [
                    ['id' => 'recNeon', 'fields' => ['Name' => 'NEON', 'Order' => 1]],
                ]]);
            }

            if (str_contains($url, 'attributes') || str_contains($url, 'Attributes')) {
                return Http::response(['records' => []]);
            }

            parse_str((string) parse_url($url, PHP_URL_QUERY), $query);
            $offset = $request->data()['offset'] ?? $query['offset'] ?? null;

            if ($offset === 'page2') {
                $this->assertDatabaseMissing('products', [
                    'airtable_id' => 'recPage1',
                ]);

                return Http::response(['records' => [
                    [
                        'id' => 'recPage2',
                        'fields' => [
                            'Product Name' => 'Second Light',
                            'Status' => 'publish',
                            'Order' => 2,
                            'Category' => 'NEON',
                        ],
                    ],
                ]]);
            }

            return Http::response([
                'records' => [
                    [
                        'id' => 'recPage1',
                        'fields' => [
                            'Product Name' => 'First Light',
                            'Status' => 'publish',
                            'Order' => 1,
                            'Category' => 'NEON',
                        ],
                    ],
                ],
                'offset' => 'page2',
            ]);
        });

        app(IProductSyncService::class)->sync('test');

        $this->assertDatabaseHas('products', [
            'airtable_id' => 'recPage1',
            'product_name' => 'First Light',
        ]);
        $this->assertDatabaseHas('products', [
            'airtable_id' => 'recPage2',
            'product_name' => 'Second Light',
        ]);
        $this->assertSoftDeleted('products', [
            'airtable_id' => 'recStale',
        ]);
    }

    public function test_staff_products_permission_is_required_and_can_be_assigned(): void
    {
        $this->seed(PageSeeder::class);

        $admin = User::factory()->admin()->create();
        $staff = User::factory()->staff()->create();

        $this->actingAs($admin)
            ->get(route('dashboard.staff.create'))
            ->assertOk()
            ->assertSee('value="products"', false);

        $this->actingAs($admin)
            ->put(route('dashboard.staff.update', $staff), [
                'name' => $staff->name,
                'email' => $staff->email,
                'resources' => [ContentResource::Products->value],
            ])
            ->assertRedirect();

        $this->assertTrue($staff->fresh()->canManageProducts());

        Product::factory()->create([
            'product_name' => 'Garden Light',
            'product_code' => 'GL-100',
            'category' => 'Accessories',
        ]);

        Queue::fake();

        $this->actingAs($staff)
            ->get('/dashboard/content/products')
            ->assertOk()
            ->assertSee('dash-row-link-text">GL-100</span>', false)
            ->assertSee('dash-list-sub">Garden Light</p>', false)
            ->assertDontSee('Accessories', false)
            ->assertSee('Sync', false)
            ->assertDontSee('Product is pulled from Airtable', false)
            ->assertSee('aria-label="Preview"', false)
            ->assertDontSee('aria-label="Edit"', false)
            ->assertSee('dash-pill is-active', false)
            ->assertSee('Publish', false);

        $this->actingAs($staff)
            ->post(route('dashboard.products.sync'))
            ->assertRedirect(route('dashboard.products.index'));

        Queue::assertPushed(SyncProductsJob::class);
    }

    public function test_sync_is_skipped_while_another_run_is_in_progress(): void
    {
        ProductSync::query()->create([
            'status' => ProductSyncStatus::Running,
            'products_count' => 0,
            'started_at' => now(),
            'triggered_by' => 'schedule',
        ]);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('A product sync is already running.');

        app(IProductSyncService::class)->sync('test');
    }
}
