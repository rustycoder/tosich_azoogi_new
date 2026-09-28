<?php

namespace Tests\Feature;

use App\Models\Product;
use App\ThirdParty\Airtable\ProductNormalizer;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductAssetRedirectTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_view_product_detail_by_slug_and_product_code(): void
    {
        $product = Product::factory()->create([
            'slug' => 'test-linear-profile',
            'product_code' => 'PR130-W',
            'product_name' => 'Linear Profile 130',
            'status' => 'publish',
        ]);

        $this->get('/products/test-linear-profile')
            ->assertOk()
            ->assertSee('Linear Profile 130');

        $this->get('/products/PR130-W')
            ->assertOk()
            ->assertSee('Linear Profile 130');

        $this->get('/products/pr130-w')
            ->assertOk()
            ->assertSee('Linear Profile 130');
    }

    public function test_image_query_redirects_to_primary_cover_image(): void
    {
        Product::factory()->create([
            'slug' => 'sample-strip',
            'product_code' => 'ST-001',
            'cover' => 'https://cdn.azoogi.com/images/st-001-cover.jpg',
            'product_images' => [
                'https://cdn.azoogi.com/images/st-001-cover.jpg',
                'https://cdn.azoogi.com/images/st-001-gallery.jpg',
            ],
            'status' => 'publish',
        ]);

        $response = $this->get('/products/ST-001?image');
        $response->assertStatus(302);
        $this->assertEquals('https://cdn.azoogi.com/images/st-001-cover.jpg', $response->headers->get('Location'));

        // Alias ?img
        $this->get('/products/sample-strip?img')
            ->assertStatus(302)
            ->assertRedirect('https://cdn.azoogi.com/images/st-001-cover.jpg');

        // Specific gallery index ?image=1
        $this->get('/products/ST-001?image=1')
            ->assertStatus(302)
            ->assertRedirect('https://cdn.azoogi.com/images/st-001-gallery.jpg');
    }

    public function test_dimension_query_redirects_to_dimension_image(): void
    {
        Product::factory()->create([
            'product_code' => 'DIM-SKU',
            'slug' => 'dimension-sample',
            'product_dimension' => [
                'https://cdn.azoogi.com/dimensions/dim-sku-cad.png',
            ],
            'status' => 'publish',
        ]);

        $this->get('/products/DIM-SKU?dimension')
            ->assertStatus(302)
            ->assertRedirect('https://cdn.azoogi.com/dimensions/dim-sku-cad.png');

        $this->get('/products/dimension-sample?dim')
            ->assertStatus(302)
            ->assertRedirect('https://cdn.azoogi.com/dimensions/dim-sku-cad.png');
    }

    public function test_datasheet_query_redirects_to_datasheet_pdf(): void
    {
        Product::factory()->create([
            'product_code' => 'DS-SKU',
            'datasheet_file' => [
                'https://cdn.azoogi.com/docs/ds-sku-spec.pdf',
            ],
            'status' => 'publish',
        ]);

        $this->get('/products/DS-SKU?datasheet')
            ->assertStatus(302)
            ->assertRedirect('https://cdn.azoogi.com/docs/ds-sku-spec.pdf');

        $this->get('/products/DS-SKU?pdf')
            ->assertStatus(302)
            ->assertRedirect('https://cdn.azoogi.com/docs/ds-sku-spec.pdf');
    }

    public function test_manual_guide_and_ies_redirects(): void
    {
        Product::factory()->create([
            'product_code' => 'FULL-ASSET-SKU',
            'user_manual' => 'https://cdn.azoogi.com/docs/manual.pdf',
            'installation_guide_file' => 'https://cdn.azoogi.com/docs/installation.pdf',
            'ies_file' => 'https://cdn.azoogi.com/photometrics/test.ies',
            'status' => 'publish',
        ]);

        $this->get('/products/FULL-ASSET-SKU?manual')
            ->assertStatus(302)
            ->assertRedirect('https://cdn.azoogi.com/docs/manual.pdf');

        $this->get('/products/FULL-ASSET-SKU?guide')
            ->assertStatus(302)
            ->assertRedirect('https://cdn.azoogi.com/docs/installation.pdf');

        $this->get('/products/FULL-ASSET-SKU?ies')
            ->assertStatus(302)
            ->assertRedirect('https://cdn.azoogi.com/photometrics/test.ies');
    }

    public function test_returns_404_when_requested_asset_is_missing(): void
    {
        Product::factory()->create([
            'product_code' => 'NO-ASSETS',
            'product_dimension' => null,
            'datasheet' => null,
            'datasheet_file' => null,
            'status' => 'publish',
        ]);

        $this->get('/products/NO-ASSETS?dimension')->assertNotFound();
        $this->get('/products/NO-ASSETS?datasheet')->assertNotFound();
    }

    public function test_returns_404_when_product_does_not_exist(): void
    {
        $this->get('/products/DOES-NOT-EXIST')->assertNotFound();
        $this->get('/products/DOES-NOT-EXIST?image')->assertNotFound();
    }

    public function test_draft_product_visibility_by_environment(): void
    {
        $draft = Product::factory()->create([
            'product_code' => 'DRAFT-SKU',
            'slug' => 'draft-product',
            'status' => 'draft',
        ]);

        // In testing / local environment: visible
        $this->get('/products/DRAFT-SKU')->assertOk();

        // In production environment: 404
        $this->app->detectEnvironment(fn (): string => 'production');

        $this->get('/products/DRAFT-SKU')->assertNotFound();
        $this->get('/products/draft-product')->assertNotFound();
    }

    public function test_product_normalizer_compiles_all_statuses(): void
    {
        $normalizer = new ProductNormalizer;

        $rawProducts = [
            [
                'id' => 'recPublish',
                'fields' => [
                    'Product Name' => 'Published Item',
                    'Status' => 'Publish',
                    'Product Code' => 'PUB-01',
                ],
            ],
            [
                'id' => 'recDraft',
                'fields' => [
                    'Product Name' => 'Draft Item',
                    'Status' => 'Draft',
                    'Product Code' => 'DRF-01',
                ],
            ],
            [
                'id' => 'recArchived',
                'fields' => [
                    'Product Name' => 'Archived Item',
                    'Status' => 'Archived',
                    'Product Code' => 'ARC-01',
                ],
            ],
        ];

        $compiled = $normalizer->compileProducts($rawProducts, [], []);

        $this->assertCount(3, $compiled);
        $ids = array_column($compiled, 'id');
        $this->assertContains('recPublish', $ids);
        $this->assertContains('recDraft', $ids);
        $this->assertContains('recArchived', $ids);

        $draftProduct = collect($compiled)->firstWhere('id', 'recDraft');
        $this->assertEquals('draft', $draftProduct['status'] ?? null);
    }
}
