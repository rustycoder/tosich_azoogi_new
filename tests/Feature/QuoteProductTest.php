<?php

namespace Tests\Feature;

use App\Models\Product;
use Database\Seeders\PageSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class QuoteProductTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(PageSeeder::class);
    }

    public function test_quote_products_endpoint_returns_live_name_image_and_url(): void
    {
        $image = 'https://v5.airtableusercontent.com/v3/full/garden.jpg';

        Product::factory()->create([
            'airtable_id' => 'recGarden01',
            'product_name' => '3W Garden Light',
            'product_code' => 'GL005',
            'slug' => 'garden-light',
            'status' => 'publish',
            'cover' => $image,
            'product_images' => [$image],
        ]);

        $this->getJson(route('quote.products', [
            'ids' => ['recGarden01'],
        ]))
            ->assertOk()
            ->assertJsonCount(1, 'products')
            ->assertJsonPath('products.0.id', 'recGarden01')
            ->assertJsonPath('products.0.sku', 'GL005')
            ->assertJsonPath('products.0.name', '3W Garden Light')
            ->assertJsonPath('products.0.image', $image)
            ->assertJsonPath('products.0.url', '/products/garden-light');
    }

    public function test_quote_products_endpoint_finds_products_by_sku_or_slug(): void
    {
        $image = 'https://v5.airtableusercontent.com/v3/full/neon.jpg';

        Product::factory()->create([
            'airtable_id' => 'recNeon01',
            'product_name' => 'Neon Flex',
            'product_code' => 'NF-12',
            'slug' => 'neon-flex',
            'status' => 'publish',
            'cover' => $image,
            'product_images' => [$image],
            'sku_mappings' => ['Black' => 'NF-12-BLK'],
        ]);

        $this->getJson(route('quote.products', [
            'ids' => ['NF-12', 'neon-flex', 'NF-12-BLK'],
        ]))
            ->assertOk()
            ->assertJsonCount(1, 'products')
            ->assertJsonPath('products.0.id', 'recNeon01')
            ->assertJsonPath('products.0.name', 'Neon Flex')
            ->assertJsonPath('products.0.image', $image);
    }

    public function test_quote_products_endpoint_omits_unpublished_products(): void
    {
        Product::factory()->create([
            'airtable_id' => 'recDraft01',
            'product_name' => 'Hidden Fitting',
            'product_code' => 'HF-01',
            'status' => 'draft',
        ]);

        $this->getJson(route('quote.products', [
            'ids' => ['recDraft01', 'HF-01'],
        ]))
            ->assertOk()
            ->assertJsonPath('products', []);
    }

    public function test_quote_products_endpoint_returns_an_empty_list_without_ids(): void
    {
        $this->getJson(route('quote.products'))
            ->assertOk()
            ->assertJsonPath('products', []);
    }

    public function test_quote_products_endpoint_rejects_too_many_ids(): void
    {
        $ids = [];

        for ($i = 0; $i < 51; $i++) {
            $ids[] = 'rec'.str_pad((string) $i, 14, '0', STR_PAD_LEFT);
        }

        $this->getJson(route('quote.products', ['ids' => $ids]))
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['ids']);
    }
}
