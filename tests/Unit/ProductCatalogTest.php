<?php

namespace Tests\Unit;

use App\Models\Product;
use App\Models\ProductCategory;
use App\Support\ProductCatalog;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductCatalogTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_extracts_parent_categories_from_the_products_table(): void
    {
        ProductCategory::query()->create(['airtable_id' => 'recNeon', 'name' => 'NEON', 'sort_order' => 1]);
        ProductCategory::query()->create(['airtable_id' => 'recProfiles', 'name' => 'Profiles', 'sort_order' => 2]);

        Product::factory()->create([
            'product_name' => 'Neon Flex',
            'category' => 'NEON',
            'categories' => ['NEON'],
            'category_path' => ['NEON'],
        ]);
        Product::factory()->create([
            'product_name' => 'Trimless Profile',
            'category' => 'Profiles',
            'categories' => ['Profiles'],
            'category_path' => ['Profiles'],
        ]);

        $categories = ProductCatalog::parentCategories();

        $this->assertNotEmpty($categories);

        foreach ($categories as $category) {
            $this->assertArrayHasKey('title', $category);
            $this->assertArrayHasKey('body', $category);
            $this->assertArrayHasKey('image', $category);
            $this->assertArrayHasKey('href', $category);
            $this->assertArrayHasKey('count', $category);

            $this->assertNotEmpty($category['title']);
            $this->assertNotEmpty($category['image']);
            $this->assertStringContainsString('/products?category=', $category['href']);
        }

        $titles = array_column($categories, 'title');
        $this->assertContains('NEON', $titles);
        $this->assertContains('Profiles', $titles);
    }

    public function test_it_returns_no_categories_when_the_products_table_is_empty(): void
    {
        $this->assertSame([], ProductCatalog::parentCategories());
    }
}
