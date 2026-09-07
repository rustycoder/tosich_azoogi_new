<?php

namespace Tests\Feature;

use App\Enums\ContentResource;
use App\Models\ContentPermission;
use App\Models\Enquiry;
use App\Models\Page;
use App\Models\PageVisit;
use App\Models\Product;
use App\Models\ProductDatasheetExport;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardVisitMetricsTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_sees_visit_and_top_product_cards(): void
    {
        $admin = User::factory()->admin()->create();
        Page::query()->where('slug', 'about')->update(['title' => 'About']);
        Page::query()->where('slug', 'contact')->update(['title' => 'Contact']);

        PageVisit::factory()->page('about')->count(3)->create(['country' => 'NP']);
        PageVisit::factory()->page('contact')->create(['country' => 'AU']);
        PageVisit::factory()->product('recGardenLight01')->create(['country' => 'NP']);

        $product = Product::factory()->create([
            'airtable_id' => 'recGardenLight01',
            'product_name' => 'Garden Light',
            'product_code' => 'GL005',
            'cover' => 'https://cdn.example.com/garden-light.jpg',
            'product_images' => ['https://cdn.example.com/garden-light.jpg'],
        ]);
        ProductDatasheetExport::factory()->create([
            'product_id' => $product->id,
            'airtable_id' => 'recGardenLight01',
            'product_code' => 'GL005',
            'product_name' => 'Garden Light',
        ]);
        Enquiry::factory()->quote()->create([
            'payload' => [
                'products' => "Garden Light (GL005) x2\nUnknown Fixture (ZZ999) x1",
                'role' => 'I’m an Architect',
                'method' => 'Email',
            ],
        ]);

        $this->actingAs($admin)
            ->get('/dashboard')
            ->assertOk()
            ->assertSeeInOrder([
                '>Audience Engagement Metrics</h2>',
                '>Most Visited Pages</h2>',
                '>Visitors by Country</h2>',
                '>Top Products</h2>',
                '>Enquiries Metrics</h2>',
                '>Datasheet Metrics</h2>',
            ], false)
            ->assertSee('dash-top-products', false)
            ->assertSee('dash-share-card', false)
            ->assertDontSee('>Pages</li>', false)
            ->assertDontSee('>Datasheets</li>', false)
            ->assertDontSee('>Quotes</li>', false)
            ->assertSee('title="About"', false)
            ->assertSee('title="Contact"', false)
            ->assertSee('title="Nepal"', false)
            ->assertSee('title="Australia"', false)
            ->assertSee('title="Garden Light"', false)
            ->assertSee('>GL005</span>', false)
            ->assertSee('src="https://cdn.example.com/garden-light.jpg"', false)
            ->assertSee('href="/product-detail?id=recGardenLight01"', false)
            ->assertSee('title="Preview"', false)
            ->assertSee('title="ZZ999"', false)
            ->assertSee('>ZZ999</span>', false)
            ->assertDontSee('href="/product-detail?id=ZZ999"', false)
            ->assertSee('dash-list-thumb is-empty', false);
    }

    public function test_visited_pages_show_only_the_top_ten(): void
    {
        $admin = User::factory()->admin()->create();
        $slugs = ['about', 'contact', 'solutions', 'casambi', 'silvair', 'dali-centre', 'madrix', 'ai-lighting', 'data-centre', 'privacy', 'terms'];

        foreach ($slugs as $index => $slug) {
            Page::query()->where('slug', $slug)->update(['title' => 'Page '.strtoupper($slug)]);
            PageVisit::factory()->page($slug)->count(count($slugs) - $index)->create();
        }

        $this->actingAs($admin)
            ->get('/dashboard')
            ->assertOk()
            ->assertSee('title="Page ABOUT"', false)
            ->assertSee('title="Page PRIVACY"', false)
            ->assertDontSee('title="Page TERMS"', false);
    }

    public function test_top_products_sum_page_views_datasheets_and_unique_quote_skus(): void
    {
        $admin = User::factory()->admin()->create();
        $product = Product::factory()->create([
            'airtable_id' => 'recGardenLight01',
            'product_name' => 'Garden Light',
            'product_code' => 'GL005',
            'cover' => 'https://cdn.example.com/garden-light.jpg',
            'product_images' => ['https://cdn.example.com/garden-light.jpg'],
        ]);

        PageVisit::factory()->product('recGardenLight01')->count(2)->create();
        ProductDatasheetExport::factory()->count(3)->create([
            'product_id' => $product->id,
            'airtable_id' => 'recGardenLight01',
            'product_code' => 'GL005',
            'product_name' => 'Garden Light',
        ]);
        Enquiry::factory()->quote()->create([
            'payload' => [
                'products' => 'Garden Light (GL005) x4',
                'role' => 'I’m an Architect',
                'method' => 'Email',
            ],
        ]);

        $this->actingAs($admin)
            ->get('/dashboard')
            ->assertOk()
            ->assertSee('title="Garden Light"', false)
            ->assertSee('>GL005</span>', false)
            ->assertSee('src="https://cdn.example.com/garden-light.jpg"', false)
            ->assertSee('href="/product-detail?id=recGardenLight01"', false)
            ->assertDontSee('title="GL005"', false);
    }

    public function test_quote_qty_counts_as_one_mention(): void
    {
        $admin = User::factory()->admin()->create();
        Product::factory()->create([
            'airtable_id' => 'recGardenLight01',
            'product_name' => 'Garden Light',
            'product_code' => 'GL005',
        ]);
        Product::factory()->create([
            'airtable_id' => 'recNeonFlex00001',
            'product_name' => 'Neon Flex',
            'product_code' => 'NF100',
        ]);
        Product::factory()->create([
            'airtable_id' => 'recSpotlight00001',
            'product_name' => 'Spot Light',
            'product_code' => 'SP200',
        ]);

        PageVisit::factory()->product('recSpotlight00001')->count(2)->create();
        Enquiry::factory()->quote()->create([
            'payload' => [
                'products' => "Garden Light (GL005) x9\nNeon Flex (NF100) x1",
                'role' => 'I’m an Architect',
                'method' => 'Email',
            ],
        ]);

        $this->actingAs($admin)
            ->get('/dashboard')
            ->assertOk()
            ->assertSeeInOrder([
                '>Top Products</h2>',
                '--rank: #2d7a1e',
                'title="Spot Light"',
                '--rank: #3aa028',
                'title="Garden Light"',
                '--rank: #4eae3a',
                'title="Neon Flex"',
            ], false)
            ->assertSee('>GL005</span>', false)
            ->assertSee('>NF100</span>', false);
    }

    public function test_staff_without_relevant_access_do_not_see_visit_cards(): void
    {
        $staff = User::factory()->staff()->create();
        ContentPermission::query()->create([
            'user_id' => $staff->id,
            'resource' => ContentResource::Header,
        ]);

        PageVisit::factory()->page('about')->create(['country' => 'AU']);

        $this->actingAs($staff)
            ->get('/dashboard')
            ->assertOk()
            ->assertDontSee('>Most Visited Pages</h2>', false)
            ->assertDontSee('>Visitors by Country</h2>', false)
            ->assertDontSee('>Top Products</h2>', false);
    }

    public function test_pages_staff_see_visit_cards_but_not_top_products(): void
    {
        $staff = User::factory()->staff()->create();
        ContentPermission::query()->create([
            'user_id' => $staff->id,
            'resource' => ContentResource::About,
        ]);

        PageVisit::factory()->page('about')->create(['country' => 'AU']);
        PageVisit::factory()->product('recGardenLight01')->create();

        $this->actingAs($staff)
            ->get('/dashboard')
            ->assertOk()
            ->assertSee('>Most Visited Pages</h2>', false)
            ->assertSee('>Visitors by Country</h2>', false)
            ->assertDontSee('>Top Products</h2>', false);
    }

    public function test_customers_do_not_see_visit_metrics(): void
    {
        $customer = User::factory()->customer()->create();
        PageVisit::factory()->page('about')->create(['country' => 'AU']);

        $this->actingAs($customer)
            ->get('/dashboard')
            ->assertOk()
            ->assertDontSee('>Most Visited Pages</h2>', false)
            ->assertDontSee('>Visitors by Country</h2>', false)
            ->assertDontSee('>Top Products</h2>', false);
    }
}
