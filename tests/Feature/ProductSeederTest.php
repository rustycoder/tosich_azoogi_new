<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\ProductAttribute;
use App\Models\ProductCategory;
use App\Repositories\Contracts\IProductRepository;
use Database\Seeders\DatabaseSeeder;
use Database\Seeders\ProductSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class ProductSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_first_seed_loads_products_from_json(): void
    {
        Http::fake();

        $this->seed(DatabaseSeeder::class);

        Http::assertNothingSent();
        $this->assertDatabaseHas('products', [
            'airtable_id' => 'rec7gengQm0proqz5',
            'product_name' => 'Garden Light',
            'product_code' => 'GL005',
        ]);
        $this->assertTrue(ProductCategory::query()->where('name', 'NEON')->exists());
        $this->assertGreaterThan(1, Product::query()->count());
        $this->assertDatabaseHas('products', [
            'airtable_id' => 'recHbgdqI7oH13d98',
            'stocked_item' => 'In Stock',
        ]);
        $this->assertDatabaseHas('product_attributes', [
            'name' => 'Finish',
            'value' => 'Black',
            'icon' => 'assets/img/attribute_icon/attr_finish_black_fc3762bf71.svg',
        ]);
        $this->assertGreaterThan(1, ProductAttribute::query()->count());

        $garden = collect(app(IProductRepository::class)->compiled()['products'])
            ->firstWhere('id', 'rec7gengQm0proqz5');

        $this->assertIsArray($garden);
        $this->assertNotEmpty($garden['product_features']['Finish'] ?? []);
        $this->assertDatabaseCount('product_syncs', 0);
    }

    public function test_seeder_skips_json_when_products_already_exist(): void
    {
        Product::factory()->create([
            'airtable_id' => 'recExisting',
            'product_name' => 'Existing Light',
        ]);

        $this->seed(ProductSeeder::class);

        $this->assertDatabaseCount('products', 1);
        $this->assertDatabaseHas('products', [
            'airtable_id' => 'recExisting',
        ]);
        $this->assertDatabaseMissing('products', [
            'airtable_id' => 'rec7gengQm0proqz5',
        ]);
        $this->assertDatabaseHas('product_attributes', [
            'name' => 'Finish',
            'value' => 'Black',
        ]);
    }
}
