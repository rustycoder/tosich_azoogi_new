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
        $this->seedRangeCategories();
    }

    public function test_products_index_opens_as_a_parent_category_gallery(): void
    {
        $mtime = filemtime(public_path('assets/css/products.css'));
        $css = file_get_contents(public_path('assets/css/products.css'));

        $this->assertNotFalse($css);
        $this->assertStringNotContainsString('.prod-gallery-card .img', $css);
        $this->assertDoesNotMatchRegularExpression(
            '/\.prod-gallery-card p\s*\{[^}]*line-clamp/s',
            $css,
        );

        $this->get('/products')
            ->assertOk()
            ->assertSee('class="prod-gallery"', false)
            ->assertSee('class="prod-gallery-card"', false)
            ->assertSee('<h3>NEON</h3>', false)
            ->assertSee('<h3>Profiles</h3>', false)
            ->assertSee('Seamless flexible linear lighting for interior and exterior architectural contours, including wet areas and long facade runs.', false)
            ->assertSee('/products?category=NEON', false)
            ->assertSee('/products?category=Profiles', false)
            ->assertSee('View Range', false)
            ->assertSee('/assets/css/products.css?v='.$mtime, false)
            ->assertDontSee('Trimless plaster-in, recessed, surfaced and corner aluminium extrusion channels.', false)
            ->assertDontSee('products available', false)
            ->assertDontSee('class="img"', false)
            ->assertDontSee('id="prodSidebar"', false)
            ->assertDontSee('id="prodSearchInput"', false)
            ->assertDontSee('id="productGrid"', false)
            ->assertDontSee('id="prodFilterOpen"', false)
            ->assertDontSee('products found', false)
            ->assertDontSee('AZOOGI_PRODUCTS.filterable_attributes.length > 0', false);
    }

    public function test_products_gallery_uses_the_same_parent_categories_as_the_home_marquee(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertSee('<h4>NEON</h4>', false)
            ->assertSee('<h4>Profiles</h4>', false)
            ->assertSee('Seamless flexible linear lighting for interior and exterior architectural contours, including wet areas and long facade runs.', false)
            ->assertSee('/products?category=NEON', false)
            ->assertDontSee('Trimless plaster-in, recessed, surfaced and corner aluminium extrusion channels.', false)
            ->assertDontSee('products available', false);

        $this->get('/products')
            ->assertOk()
            ->assertSee('<h3>NEON</h3>', false)
            ->assertSee('<h3>Profiles</h3>', false)
            ->assertSee('Seamless flexible linear lighting for interior and exterior architectural contours, including wet areas and long facade runs.', false)
            ->assertSee('/products?category=NEON', false)
            ->assertDontSee('Trimless plaster-in, recessed, surfaced and corner aluminium extrusion channels.', false)
            ->assertDontSee('products available', false);
    }

    public function test_category_query_opens_the_filtered_catalogue(): void
    {
        $this->get('/products?category=NEON')
            ->assertOk()
            ->assertSee('const AZOOGI_PRODUCTS', false)
            ->assertDontSee('products_data.js', false)
            ->assertDontSee('class="prod-gallery"', false)
            ->assertSee('id="prodSidebar"', false)
            ->assertSee('id="prodFilterOverlay"', false)
            ->assertSee('id="prodFilterOpen"', false)
            ->assertSee('id="prodSearchInput"', false)
            ->assertSee('id="productGrid"', false)
            ->assertSee('class="prod-hero"', false)
            ->assertDontSee('id="breadcrumbs"', false)
            ->assertSee('NEON', false)
            ->assertSee('max-width: 400px', false)
            ->assertSee('margin-inline: auto', false)
            ->assertSee('AZOOGI_PRODUCTS.filterable_attributes.length > 0', false)
            ->assertSee("urlParams.get('category')", false);
    }

    public function test_products_page_renders_a_contained_mobile_filter_drawer(): void
    {
        $this->get('/products?category=NEON')
            ->assertOk()
            ->assertSee('const AZOOGI_PRODUCTS', false)
            ->assertDontSee('products_data.js', false)
            ->assertSee('id="prodSidebar"', false)
            ->assertSee('id="prodFilterOverlay"', false)
            ->assertSee('id="prodFilterOpen"', false)
            ->assertSee('max-width: 400px', false)
            ->assertSee('margin-inline: auto', false)
            ->assertSee('AZOOGI_PRODUCTS.filterable_attributes.length > 0', false);
    }

    private function seedRangeCategories(): void
    {
        ProductCategory::query()->create([
            'airtable_id' => 'recNeon',
            'name' => 'NEON',
            'sort_order' => 1,
            'description' => 'Seamless flexible linear lighting for interior and exterior architectural contours, including wet areas and long facade runs.',
        ]);
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
    }

    public function test_products_page_matches_categories_without_merging_same_named_items(): void
    {
        $this->get('/products?category=Profiles')
            ->assertOk()
            ->assertSee('function productMatchesCategory', false)
            ->assertDontSee('p.modelName && p.modelName.trim().toLowerCase() === selLower', false)
            ->assertDontSee('(p.id && p.id === itemKey) || p.name === vName', false);
    }

    public function test_sibling_profile_products_with_the_same_name_keep_separate_categories(): void
    {
        ProductCategory::query()->updateOrCreate(
            ['airtable_id' => 'recProfiles'],
            ['name' => 'Profiles', 'sort_order' => 1]
        );
        ProductCategory::query()->updateOrCreate(
            ['airtable_id' => 'recTrimless'],
            ['name' => 'Trimless', 'parent_airtable_id' => 'recProfiles', 'sort_order' => 1]
        );
        ProductCategory::query()->updateOrCreate(
            ['airtable_id' => 'recSuspended'],
            ['name' => 'Suspended', 'parent_airtable_id' => 'recProfiles', 'sort_order' => 2]
        );

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

        $this->get('/products?category=Profiles')
            ->assertOk()
            ->assertSee('recTrimlessSku', false)
            ->assertSee('recSuspendedSku', false)
            ->assertSee('"categories":["Trimless"]', false)
            ->assertSee('"categories":["Suspended"]', false)
            ->assertDontSee('"categories":["Trimless","Suspended"]', false)
            ->assertDontSee('"categories":["Suspended","Trimless"]', false);
    }

    public function test_products_page_uses_exact_matching_for_specification_filters(): void
    {
        $this->get('/products?category=NEON')
            ->assertOk()
            ->assertSee('String(rawVal || \'\').trim().toLowerCase() === targetSel', false)
            ->assertDontSee('String(rawVal).toLowerCase().indexOf(String(selVal).toLowerCase()) !== -1', false);
    }
}
