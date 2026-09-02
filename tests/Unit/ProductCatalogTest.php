<?php

namespace Tests\Unit;

use App\Support\ProductCatalog;
use Tests\TestCase;

class ProductCatalogTest extends TestCase
{
    public function test_it_extracts_parent_categories_from_products_json(): void
    {
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
}
