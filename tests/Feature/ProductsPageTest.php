<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\ProductCategory;
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

    public function test_products_page_matches_categories_without_merging_same_named_items(): void
    {
        $this->get('/products')
            ->assertOk()
            ->assertSee('function productMatchesCategory', false)
            ->assertDontSee('p.modelName && p.modelName.trim().toLowerCase() === selLower', false)
            ->assertDontSee('(p.id && p.id === itemKey) || p.name === vName', false);
    }

    public function test_sibling_profile_products_with_the_same_name_keep_separate_categories(): void
    {
        ProductCategory::query()->create([
            'airtable_id' => 'recProfiles',
            'name' => 'Profiles',
            'sort_order' => 1,
        ]);
        ProductCategory::query()->create([
            'airtable_id' => 'recTrimless',
            'name' => 'Trimless',
            'parent_airtable_id' => 'recProfiles',
            'sort_order' => 1,
        ]);
        ProductCategory::query()->create([
            'airtable_id' => 'recSuspended',
            'name' => 'Suspended',
            'parent_airtable_id' => 'recProfiles',
            'sort_order' => 2,
        ]);

        Product::factory()->create([
            'airtable_id' => 'recTrimlessSku',
            'product_name' => '50mm (W) x 75mm (H)',
            'product_code' => 'PR130-W',
            'category' => 'Trimless',
            'categories' => ['Trimless'],
            'category_path' => ['Profiles', 'Trimless'],
            'category_paths' => [['Profiles', 'Trimless']],
        ]);
        Product::factory()->create([
            'airtable_id' => 'recSuspendedSku',
            'product_name' => '50mm (W) x 75mm (H)',
            'product_code' => 'PR127-3m-Silver',
            'category' => 'Suspended',
            'categories' => ['Suspended'],
            'category_path' => ['Profiles', 'Suspended'],
            'category_paths' => [['Profiles', 'Suspended']],
        ]);

        $this->get('/products')
            ->assertOk()
            ->assertSee('recTrimlessSku', false)
            ->assertSee('recSuspendedSku', false)
            ->assertSee('"categories":["Trimless"]', false)
            ->assertSee('"categories":["Suspended"]', false)
            ->assertDontSee('"categories":["Trimless","Suspended"]', false)
            ->assertDontSee('"categories":["Suspended","Trimless"]', false);
    }
}
