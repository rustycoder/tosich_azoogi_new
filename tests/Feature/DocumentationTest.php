<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\AdminUserSeeder;
use Database\Seeders\PageSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DocumentationTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_are_redirected_to_login(): void
    {
        $this->get('/dashboard/docs')->assertRedirect('/login');
        $this->get('/dashboard/docs/media')->assertRedirect('/login');
    }

    public function test_authenticated_user_can_view_documentation_topics(): void
    {
        $this->seed([AdminUserSeeder::class, PageSeeder::class]);
        $user = User::query()->where('email', 'admin@azoogi.com')->firstOrFail();

        $topics = [
            'overview',
            'pages',
            'formatting',
            'counters',
            'projects',
            'products',
            'videos',
            'images',
            'alt-text',
            'seo',
            'sitemap',
            'geo',
            'enquiries',
            'datasheets',
            'emails',
            'staff',
            'deployment',
            'mcp',
            // Test backward-compatible aliases as well
            'media',
            'content',
            'maintenance',
            'alt',
            'accessibility',
            'ordering',
            'airtable',
            'catalog',
        ];

        foreach ($topics as $topic) {
            $response = $this->actingAs($user)->get("/dashboard/docs/{$topic}");
            $response->assertOk();
            $response->assertSee('Documentation & Guides', false);
            $response->assertSee('dash-doc-menu', false);
        }

        $altResponse = $this->actingAs($user)->get('/dashboard/docs/alt-text');
        $altResponse->assertOk();
        $altResponse->assertSee('Image Alt Text & Accessibility (WCAG 2.1 & SEO)', false);
        $altResponse->assertSee('Search Engine Optimization (SEO) & Google Images', false);

        $productResponse = $this->actingAs($user)->get('/dashboard/docs/products');
        $productResponse->assertOk();
        $productResponse->assertSee('Product Catalog & Airtable Guide', false);
        $productResponse->assertSee('Category Order & Number Blocks', false);
        $productResponse->assertSee('Product Order = Category Order × 1000 + position (001–999)', false);
        $productResponse->assertSee('901041', false);
        $productResponse->assertSee('201001', false);
        $productResponse->assertSee('Landscape Lighting', false);
    }
}
