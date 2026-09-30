<?php

namespace Tests\Feature;

use App\Enums\ContentResource;
use App\Enums\UserType;
use App\Models\ContentPermission;
use App\Models\Product;
use App\Models\ProductAttribute;
use App\Models\ProductCategory;
use App\Models\User;
use Database\Seeders\PageSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardProductTableTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_view_products_airtable_grid(): void
    {
        $this->seed(PageSeeder::class);

        $admin = User::factory()->create([
            'user_type' => UserType::Admin,
        ]);

        $product = Product::factory()->create([
            'airtable_id' => 'recPROD10001',
            'product_name' => 'Linear LED Luminaire',
            'product_code' => 'LIN-5000',
            'category' => 'Linear Profile',
            'categories' => ['Linear Profile', 'Surface Mounted'],
            'status' => 'publish',
            'stocked_item' => 'In Stock',
            'product_type' => 'Linear',
            'supplier_name' => 'Azoogi Tech',
            'supplier_code' => 'SUP-9988',
            'slug' => 'linear-led-luminaire-pro',
            'product_description' => 'Detailed engineering specifications for linear light profile.',
            'meta_title' => 'Linear LED Luminaire | Azoogi',
            'meta_description' => 'Architectural grade linear lighting profile by Azoogi.',
            'meta_keywords' => 'linear, luminaire, architectural',
            'cover' => 'https://example.com/cover.jpg',
            'product_images' => [
                'https://example.com/gallery1.jpg',
                'https://example.com/gallery2.jpg',
                'https://example.com/gallery3.jpg',
                'https://example.com/gallery4.jpg',
            ],
            'product_dimension' => 'https://example.com/dimension.jpg',
            'technical_icons' => [
                'https://example.com/icon1.svg',
                'https://example.com/icon2.svg',
            ],
            'datasheet' => 'Yes',
            'datasheet_file' => 'https://example.com/datasheet.pdf',
            'installation_guide_file' => 'https://example.com/guide.pdf',
            'user_manual' => 'https://example.com/manual.pdf',
            'ies_file' => 'https://example.com/light.ies',
            'dimming_control' => true,
            'sort_order' => 5,
        ]);

        $response = $this->actingAs($admin)->get(route('dashboard.products.index'));

        $response->assertOk();
        $response->assertSee('dash-airtable-table', false);
        $response->assertSee('1. Primary ID &amp; Visuals', false);
        $response->assertSee('2. Media &amp; Schematics', false);
        $response->assertSee('3. Supplier &amp; Inventory', false);
        $response->assertSee('4. Technical Documents', false);
        $response->assertSee('5. Copy &amp; SEO', false);
        $response->assertSee('6. Audit', false);
        $response->assertSee('Product Code');
        $response->assertSee('Product Name');
        $response->assertSee('Supplier Name');
        $response->assertSee('Supplier Code');
        $response->assertSee('Datasheet File');
        $response->assertSee('Installation Guide');
        $response->assertSee('User Manual');
        $response->assertSee('IES File');
        $response->assertSee('Linear LED Luminaire');
        $response->assertSee('LIN-5000');
        $response->assertSee('SUP-9988');
        $response->assertSee('recPROD10001');
        $response->assertSee('linear-led-luminaire-pro');
        $response->assertSee('Detailed engineering specifications');
        $response->assertSee('Linear LED Luminaire | Azoogi');
        $response->assertSee('Architectural grade linear lighting profile');
        $response->assertSee('linear');
        $response->assertSee('Linear Profile');
        $response->assertSee('Surface Mounted');
        $response->assertSee('In Stock');
        $response->assertSee('Linear');
        $response->assertSee('Azoogi Tech');
        $response->assertSee('https://example.com/cover.jpg');
        $response->assertSee('https://example.com/gallery1.jpg');
        $response->assertSee('https://example.com/gallery2.jpg');
        $response->assertSee('https://example.com/gallery3.jpg');
        $response->assertSee('https://example.com/gallery4.jpg');
        $response->assertSee('https://example.com/dimension.jpg');
        $response->assertSee('https://example.com/icon1.svg');
        $response->assertSee('https://example.com/datasheet.pdf');
        $response->assertSee('https://example.com/guide.pdf');
        $response->assertSee('https://example.com/manual.pdf');
        $response->assertSee('https://example.com/light.ies');
        $response->assertSee('#5');
        $response->assertSee('Yes'); // Dimming control & Datasheet status
        $response->assertSee('Publish');

        // Direct product link redirects to live product page
        $response->assertSee('href="/products/linear-led-luminaire-pro"', false);
        // No separate action header column
        $response->assertDontSee('<th scope="col" style="width: 70px; text-align: center;">Action</th>', false);
    }

    public function test_products_search_and_category_filter(): void
    {
        $admin = User::factory()->create([
            'user_type' => UserType::Admin,
        ]);

        Product::factory()->create([
            'product_name' => 'Downlight Alpha',
            'product_code' => 'DL-ALPHA',
            'category' => 'Downlight',
            'sort_order' => 1,
        ]);

        Product::factory()->create([
            'product_name' => 'Spotlight Beta',
            'product_code' => 'SP-BETA',
            'category' => 'Spotlight',
            'sort_order' => 2,
        ]);

        // Search query
        $res = $this->actingAs($admin)->get(route('dashboard.products.index', ['q' => 'ALPHA']));
        $res->assertOk();
        $res->assertSee('Downlight Alpha');
        $res->assertDontSee('Spotlight Beta');

        // Category filter
        $res2 = $this->actingAs($admin)->get(route('dashboard.products.index', ['category' => 'Spotlight']));
        $res2->assertOk();
        $res2->assertSee('Spotlight Beta');
        $res2->assertDontSee('Downlight Alpha');
    }

    public function test_products_status_filter_and_ordering(): void
    {
        $admin = User::factory()->create([
            'user_type' => UserType::Admin,
        ]);

        $published = Product::factory()->create([
            'product_name' => 'First In Order Published',
            'product_code' => 'FIRST-PUB',
            'status' => 'publish',
            'sort_order' => 1,
        ]);

        $draft = Product::factory()->create([
            'product_name' => 'Second In Order Draft',
            'product_code' => 'SECOND-DFT',
            'status' => 'draft',
            'sort_order' => 2,
        ]);

        // Filter by Published status
        $pubRes = $this->actingAs($admin)->get(route('dashboard.products.index', ['status' => 'publish']));
        $pubRes->assertOk();
        $pubRes->assertSee('First In Order Published');
        $pubRes->assertDontSee('Second In Order Draft');

        // Filter by Draft status
        $draftRes = $this->actingAs($admin)->get(route('dashboard.products.index', ['status' => 'draft']));
        $draftRes->assertOk();
        $draftRes->assertSee('Second In Order Draft');
        $draftRes->assertDontSee('First In Order Published');

        // Check sort order in HTML
        $allRes = $this->actingAs($admin)->get(route('dashboard.products.index'));
        $allRes->assertOk();
        $firstPos = strpos($allRes->getContent(), 'First In Order Published');
        $secondPos = strpos($allRes->getContent(), 'Second In Order Draft');
        $this->assertTrue($firstPos < $secondPos);
    }

    public function test_products_category_hierarchy_dropdown_and_per_page_options(): void
    {
        $admin = User::factory()->create([
            'user_type' => UserType::Admin,
        ]);

        ProductCategory::query()->create([
            'airtable_id' => 'recCAT_ARCH',
            'name' => 'Architectural',
            'sort_order' => 1,
        ]);

        ProductCategory::query()->create([
            'airtable_id' => 'recCAT_DOWN',
            'name' => 'Downlights',
            'parent_airtable_id' => 'recCAT_ARCH',
            'sort_order' => 2,
        ]);

        ProductCategory::query()->create([
            'airtable_id' => 'recCAT_FIXED',
            'name' => 'Fixed Downlights',
            'parent_airtable_id' => 'recCAT_DOWN',
            'sort_order' => 3,
        ]);

        Product::factory()->create([
            'product_name' => 'Fixed Downlight 10W',
            'category' => 'Fixed Downlights',
        ]);

        $res = $this->actingAs($admin)->get(route('dashboard.products.index'));
        $res->assertOk();
        $res->assertSee('Architectural');
        $res->assertSee('— Downlights');
        $res->assertSee('— — Fixed Downlights');
        $res->assertSee('Show 50');
        $res->assertSee('Show 100');
        $res->assertSee('Show 150');
        $res->assertSee('Show 200');

        // Filtering by parent category 'Architectural' matches child product 'Fixed Downlights'
        $parentFilterRes = $this->actingAs($admin)->get(route('dashboard.products.index', ['category' => 'Architectural']));
        $parentFilterRes->assertOk();
        $parentFilterRes->assertSee('Fixed Downlight 10W');

        // Test custom per_page parameter
        $perPageRes = $this->actingAs($admin)->get(route('dashboard.products.index', ['per_page' => 100]));
        $perPageRes->assertOk();
        $perPageRes->assertSee('value="100" selected', false);
    }

    public function test_admin_can_view_categories_table_with_hierarchy_and_order(): void
    {
        $admin = User::factory()->create([
            'user_type' => UserType::Admin,
        ]);

        $parent = ProductCategory::query()->create([
            'airtable_id' => 'recCAT_PARENT',
            'name' => 'Commercial Lighting',
            'description' => 'Commercial luminaire range',
            'featured_image' => 'https://example.com/cat_featured.jpg',
            'icon' => 'https://example.com/cat_icon.svg',
            'sort_order' => 1,
        ]);

        $child = ProductCategory::query()->create([
            'airtable_id' => 'recCAT_CHILD',
            'name' => 'High Bay',
            'parent_airtable_id' => 'recCAT_PARENT',
            'description' => 'Industrial high bay lighting',
            'sort_order' => 2,
        ]);

        Product::factory()->create([
            'product_name' => 'Industrial High Bay 200W',
            'category' => 'High Bay',
        ]);

        $response = $this->actingAs($admin)->get(route('dashboard.categories.index'));

        $response->assertOk();
        $response->assertSee('dash-airtable-table', false);
        $response->assertSee('Commercial Lighting');
        $response->assertSee('High Bay');
        $response->assertSee('recCAT_PARENT');
        $response->assertSee('recCAT_CHILD');
        $response->assertSee('Commercial luminaire range');
        $response->assertSee('https://example.com/cat_featured.jpg');
        $response->assertSee('https://example.com/cat_icon.svg');
        $response->assertSee('Root Category');
        $response->assertSee('1 product');

        $html = $response->getContent();
        $this->assertTrue(strpos($html, 'Commercial Lighting') < strpos($html, 'High Bay'));

        // Test root category filter
        $rootFilterRes = $this->actingAs($admin)->get(route('dashboard.categories.index', ['parent' => 'recCAT_PARENT']));
        $rootFilterRes->assertOk();
        $rootFilterRes->assertSee('Commercial Lighting');
        $rootFilterRes->assertSee('High Bay');

        // Test root only filter
        $rootOnlyRes = $this->actingAs($admin)->get(route('dashboard.categories.index', ['parent' => 'root_only']));
        $rootOnlyRes->assertOk();
        $rootOnlyRes->assertSee('Commercial Lighting');
        $rootOnlyRes->assertDontSee('High Bay');

        // Test per_page selector
        $perPageRes = $this->actingAs($admin)->get(route('dashboard.categories.index', ['per_page' => 100]));
        $perPageRes->assertOk();
        $perPageRes->assertSee('value="100" selected', false);
    }

    public function test_admin_can_view_product_attributes_table_ordered_by_sort_order(): void
    {
        $admin = User::factory()->create([
            'user_type' => UserType::Admin,
        ]);

        ProductAttribute::query()->create([
            'airtable_id' => 'recATTR_CCT_3000',
            'name' => 'Colour Temperature',
            'value' => '3000K Warm White',
            'icon' => 'https://example.com/3000k.svg',
            'is_visible_on_filters' => true,
            'sort_order' => 1,
        ]);

        ProductAttribute::query()->create([
            'airtable_id' => 'recATTR_IP65',
            'name' => 'IP Rating',
            'value' => 'IP65 Water Resistant',
            'icon' => null,
            'is_visible_on_filters' => false,
            'sort_order' => 2,
        ]);

        $response = $this->actingAs($admin)->get(route('dashboard.product-attributes.index'));

        $response->assertOk();
        $response->assertSee('dash-airtable-table', false);
        $response->assertSee('Colour Temperature');
        $response->assertSee('3000K Warm White');
        $response->assertSee('recATTR_CCT_3000');
        $response->assertSee('https://example.com/3000k.svg');
        $response->assertSee('Visible on Filters');
        $response->assertSee('IP Rating');
        $response->assertSee('IP65 Water Resistant');
        $response->assertSee('Internal Only');

        $html = $response->getContent();
        $this->assertTrue(strpos($html, '3000K Warm White') < strpos($html, 'IP65 Water Resistant'));

        // Filter by group
        $groupRes = $this->actingAs($admin)->get(route('dashboard.product-attributes.index', ['group' => 'IP Rating']));
        $groupRes->assertOk();
        $groupRes->assertSee('IP65 Water Resistant');
        $groupRes->assertDontSee('3000K Warm White');

        // Test per_page selector
        $attrPerPageRes = $this->actingAs($admin)->get(route('dashboard.product-attributes.index', ['per_page' => 100]));
        $attrPerPageRes->assertOk();
        $attrPerPageRes->assertSee('value="100" selected', false);
    }

    public function test_staff_with_products_permission_can_access_all_three_pages(): void
    {
        $staff = User::factory()->create([
            'user_type' => UserType::Staff,
        ]);

        ContentPermission::query()->create([
            'user_id' => $staff->id,
            'resource' => ContentResource::Products,
        ]);

        $this->actingAs($staff)->get(route('dashboard.products.index'))->assertOk();
        $this->actingAs($staff)->get(route('dashboard.categories.index'))->assertOk();
        $this->actingAs($staff)->get(route('dashboard.product-attributes.index'))->assertOk();

        // Check sidebar contains links to all three
        $dash = $this->actingAs($staff)->get(route('dashboard.home'));
        $dash->assertOk();
        $dash->assertSee(route('dashboard.products.index'));
        $dash->assertSee(route('dashboard.categories.index'));
        $dash->assertSee(route('dashboard.product-attributes.index'));
    }

    public function test_staff_without_products_permission_is_forbidden(): void
    {
        $staff = User::factory()->create([
            'user_type' => UserType::Staff,
        ]);

        $this->actingAs($staff)->get(route('dashboard.products.index'))->assertForbidden();
        $this->actingAs($staff)->get(route('dashboard.categories.index'))->assertForbidden();
        $this->actingAs($staff)->get(route('dashboard.product-attributes.index'))->assertForbidden();
    }

    public function test_product_gallery_excludes_cover_image(): void
    {
        $admin = User::factory()->create([
            'user_type' => UserType::Admin,
        ]);

        $productWithSingleImage = Product::factory()->create([
            'product_name' => 'Single Image Lamp',
            'cover' => 'https://example.com/lamp-cover.jpg',
            'product_images' => ['https://example.com/lamp-cover.jpg'],
        ]);

        $productWithMultipleImages = Product::factory()->create([
            'product_name' => 'Multi Image Spotlight',
            'cover' => 'https://example.com/spot-cover.jpg',
            'product_images' => [
                'https://example.com/spot-cover.jpg',
                'https://example.com/spot-gallery-1.jpg',
                'https://example.com/spot-gallery-2.jpg',
            ],
        ]);

        $this->assertSame([], $productWithSingleImage->galleryImageUrls());
        $this->assertSame([
            'https://example.com/spot-gallery-1.jpg',
            'https://example.com/spot-gallery-2.jpg',
        ], $productWithMultipleImages->galleryImageUrls());

        $res = $this->actingAs($admin)->get(route('dashboard.products.index'));
        $res->assertOk();
        $res->assertSee('https://example.com/spot-gallery-1.jpg');
        $res->assertSee('https://example.com/spot-gallery-2.jpg');
    }
}
