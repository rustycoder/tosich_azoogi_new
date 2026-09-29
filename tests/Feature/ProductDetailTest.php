<?php

namespace Tests\Feature;

use App\Models\Product;
use Database\Seeders\PageSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductDetailTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(PageSeeder::class);
    }

    public function test_product_detail_renders_with_slug_and_valid_scoped_json(): void
    {
        $product = Product::query()->create([
            'airtable_id' => 'recNeon360Test',
            'product_name' => 'Neon Flex Series 360',
            'slug' => 'neon-flex-series-360',
            'category' => 'IP67 Neon',
            'status' => 'Published',
            'product_code' => 'AZ-NF360-TEST',
            'supplier_name' => 'Secret Supplier Ltd',
            'product_images' => [
                'https://example.com/neon1.jpg',
                'https://example.com/neon2.jpg',
            ],
            'technical_icons' => [
                'https://example.com/icon-ip67.png',
                'https://example.com/icon-24v.png',
            ],
            'product_features' => [
                'Wattage' => '14.4W/m',
                'Voltage' => '24V DC',
                'CRI' => '90+',
            ],
            'options' => [
                'CCT' => ['3000K', '4000K'],
            ],
            'sku_mappings' => [
                '3000K' => 'AZ-NF360-3000K',
                '4000K' => 'AZ-NF360-4000K',
            ],
            'dimming_control' => true,
        ]);

        $response = $this->get('/products/'.$product->slug);

        $response->assertOk();
        $response->assertSee('Neon Flex Series 360', false);

        // Ensure Blade @json evaluated properly and is not output literally
        $response->assertDontSee('@json(', false);
        $response->assertDontSee('@verbatim', false);
        $response->assertDontSee('@endverbatim', false);

        // Ensure DOM attributes were removed to prevent data leakage
        $response->assertDontSee('data-product-slug', false);
        $response->assertDontSee('data-server-product', false);
        $response->assertDontSee('Secret Supplier Ltd', false);

        // Ensure essential fields exist in the embedded CURRENT_PRODUCT script
        $content = $response->getContent();
        $this->assertNotFalse($content);
        $this->assertStringContainsString('const CURRENT_PRODUCT = {', $content);
        $this->assertStringContainsString('neon1.jpg', $content);
        $this->assertStringContainsString('icon-ip67.png', $content);
        $this->assertStringContainsString('AZ-NF360-TEST', $content);
    }

    public function test_product_detail_resolves_various_query_parameters(): void
    {
        $product = Product::query()->create([
            'airtable_id' => 'recLinearTest',
            'product_name' => 'Linear Extrusion GL005',
            'slug' => 'linear-extrusion-gl005',
            'category' => 'Profiles',
            'status' => 'Published',
            'product_code' => 'GL005',
            'product_images' => ['https://example.com/gl005.jpg'],
            'technical_icons' => ['https://example.com/icon.png'],
        ]);

        // 1. By ID
        $this->get('/product-detail?id=recLinearTest')
            ->assertOk()
            ->assertSee('Linear Extrusion GL005', false)
            ->assertDontSee('@json(', false);

        // 2. By product code / SKU
        $this->get('/product-detail?code=GL005')
            ->assertOk()
            ->assertSee('Linear Extrusion GL005', false);

        $this->get('/product-detail?sku=gl005')
            ->assertOk()
            ->assertSee('Linear Extrusion GL005', false);

        // 3. By legacy file name with .json extension
        $this->get('/product-detail?file=GL005.json')
            ->assertOk()
            ->assertSee('Linear Extrusion GL005', false);

        // 4. By product name
        $this->get('/product-detail?name=Linear+Extrusion+GL005')
            ->assertOk()
            ->assertSee('Linear Extrusion GL005', false);
    }

    public function test_product_detail_without_parameter_falls_back_to_published_product(): void
    {
        $product = Product::query()->create([
            'airtable_id' => 'recFallbackTest',
            'product_name' => 'Fallback Showcase Fitting',
            'slug' => 'fallback-showcase-fitting',
            'category' => 'Fittings',
            'status' => 'Published',
            'product_code' => 'AZ-FALLBACK-01',
        ]);

        $this->get('/product-detail')
            ->assertOk()
            ->assertSee('Fallback Showcase Fitting', false)
            ->assertDontSee('@json(', false);
    }

    public function test_product_detail_returns_404_for_nonexistent_product(): void
    {
        $this->get('/products/does-not-exist-at-all')
            ->assertNotFound();

        $this->get('/product-detail?id=recNonExistent123')
            ->assertNotFound();

        $this->get('/product-detail?code=INVALID_CODE_999')
            ->assertNotFound();
    }

    public function test_product_detail_passes_recommended_products(): void
    {
        $mainProduct = Product::query()->create([
            'airtable_id' => 'recMainProd',
            'product_name' => 'Architectural Spotlight Main',
            'slug' => 'architectural-spotlight-main',
            'category' => 'Spotlights',
            'status' => 'Published',
            'product_code' => 'SPOT-01',
        ]);

        $relatedProduct = Product::query()->create([
            'airtable_id' => 'recRelatedProd',
            'product_name' => 'Architectural Spotlight Accessory',
            'slug' => 'architectural-spotlight-accessory',
            'category' => 'Spotlights',
            'status' => 'Published',
            'product_code' => 'SPOT-ACC',
        ]);

        $response = $this->get('/products/'.$mainProduct->slug);

        $response->assertOk();
        $response->assertSee('const RECOMMENDED_ACCESSORIES = [', false);
        $response->assertSee('Architectural Spotlight Accessory', false);
    }
}
