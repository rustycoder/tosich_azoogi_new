<?php

namespace Tests\Feature;

use App\Models\Page;
use App\Models\Product;
use App\Models\User;
use Database\Seeders\AdminUserSeeder;
use Database\Seeders\PageSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class SeoMetaTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_pages_render_comprehensive_seo_meta_and_open_graph_tags(): void
    {
        $this->seed(PageSeeder::class);

        $response = $this->get('/');
        $response->assertOk();
        $response->assertSee('<link rel="canonical"', false);
        $response->assertSee('<meta name="robots"', false);
        $response->assertSee('<meta property="og:type" content="website">', false);
        $response->assertSee('<meta property="og:site_name" content="Azoogi">', false);
        $response->assertSee('<meta property="og:title"', false);
        $response->assertSee('<meta property="og:description"', false);
        $response->assertSee('<meta property="og:image"', false);
        $response->assertSee('<meta name="twitter:card" content="summary_large_image">', false);
        $response->assertSee('https://schema.org', false);
    }

    public function test_product_detail_renders_product_schema_and_product_open_graph(): void
    {
        $this->seed(PageSeeder::class);

        $product = Product::factory()->create([
            'slug' => 'test-linear-extrusion',
            'product_name' => 'Test Linear Extrusion',
            'meta_description' => 'Architectural LED extrusion for slim profile lighting.',
        ]);

        $response = $this->get('/products/'.$product->slug);
        $response->assertOk();
        $response->assertSee('<meta property="og:type" content="product">', false);
        $response->assertSee('Test Linear Extrusion', false);
        $response->assertSee('"@type":"Product"', false);
    }

    public function test_robots_txt_disallows_dashboard(): void
    {
        $robotsContent = file_get_contents(public_path('robots.txt'));

        $this->assertStringContainsString('Disallow: /dashboard/', $robotsContent);
        $this->assertStringContainsString('Sitemap:', $robotsContent);
    }

    public function test_admin_can_update_page_seo_and_og_image(): void
    {
        $this->seed([AdminUserSeeder::class, PageSeeder::class]);
        $admin = User::query()->where('email', 'admin@azoogi.com')->firstOrFail();
        $page = Page::query()->where('slug', 'about')->firstOrFail();

        Storage::fake('public');
        $file = UploadedFile::fake()->image('og_about.jpg', 1200, 630);

        $response = $this->actingAs($admin)->put("/dashboard/content/pages/{$page->slug}", [
            'editor_section' => 'meta',
            'title' => 'Custom About SEO Title',
            'meta_description' => 'Custom About Description',
            'status' => 'active',
            'og_image_file' => $file,
        ]);

        $response->assertRedirect();
        $page->refresh();

        $this->assertSame('Custom About SEO Title', $page->title);
        $this->assertSame('Custom About Description', $page->meta_description);
        $this->assertNotNull($page->og_image);
        $this->assertStringContainsString('/storage/pages/about/og_image/0/', $page->og_image);

        $publicResponse = $this->get('/about');
        $publicResponse->assertOk();
        $publicResponse->assertSee('<meta property="og:title" content="Custom About SEO Title">', false);
        $publicResponse->assertSee('<meta property="og:description" content="Custom About Description">', false);
        $publicResponse->assertSee('<meta property="og:image" content="'.media_url($page->og_image).'">', false);
    }
}
