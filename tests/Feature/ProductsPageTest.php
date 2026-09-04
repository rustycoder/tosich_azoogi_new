<?php

namespace Tests\Feature;

use Database\Seeders\PageSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductsPageTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(PageSeeder::class);
    }

    public function test_products_page_renders_a_contained_mobile_filter_drawer(): void
    {
        $this->get('/products')
            ->assertOk()
            ->assertSee('const AZOOGI_PRODUCTS', false)
            ->assertDontSee('products_data.js', false)
            ->assertSee('id="prodSidebar"', false)
            ->assertSee('id="prodFilterOverlay"', false)
            ->assertSee('id="prodFilterOpen"', false)
            ->assertSee('max-width: 400px', false)
            ->assertSee('margin-inline: auto', false);
    }
}
