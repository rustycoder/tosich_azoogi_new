<?php

namespace Tests\Feature;

use App\Enums\Status;
use App\Models\Product;
use App\Models\Project;
use App\Support\SitemapBuilder;
use Database\Seeders\PageSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class SitemapTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(PageSeeder::class);
    }

    public function test_sitemap_returns_valid_xml_with_proper_headers(): void
    {
        $response = $this->get('/sitemap.xml');

        $response->assertOk();
        $response->assertHeader('Content-Type', 'application/xml; charset=utf-8');
        $cacheControl = (string) $response->headers->get('Cache-Control');
        $this->assertStringContainsString('max-age=86400', $cacheControl);
        $this->assertStringContainsString('public', $cacheControl);

        $content = $response->getContent();
        $this->assertStringStartsWith('<?xml version="1.0" encoding="UTF-8"?>', $content);
        $this->assertStringContainsString('<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">', $content);
        $this->assertStringEndsWith('</urlset>', trim($content));
    }

    public function test_sitemap_includes_active_pages_and_excludes_private_and_error_pages(): void
    {
        $response = $this->get('/sitemap.xml');
        $content = $response->getContent();

        // Should include standard public pages
        $this->assertStringContainsString('<loc>'.url('/').'</loc>', $content);
        $this->assertStringContainsString('<loc>'.url('/about').'</loc>', $content);
        $this->assertStringContainsString('<loc>'.url('/solutions').'</loc>', $content);
        $this->assertStringContainsString('<loc>'.url('/products').'</loc>', $content);
        $this->assertStringContainsString('<loc>'.url('/projects').'</loc>', $content);
        $this->assertStringContainsString('<loc>'.url('/contact').'</loc>', $content);

        // Priority and changefreq checks
        $this->assertStringContainsString('<priority>1.0</priority>', $content);
        $this->assertStringContainsString('<changefreq>daily</changefreq>', $content);

        // Must NOT include dashboard, login, or internal/error slugs
        $this->assertStringNotContainsString(url('/dashboard'), $content);
        $this->assertStringNotContainsString(url('/trade-login'), $content);
        $this->assertStringNotContainsString(url('/quote-products'), $content);
        $this->assertStringNotContainsString(url('/header'), $content);
        $this->assertStringNotContainsString(url('/footer'), $content);
        $this->assertStringNotContainsString(url('/404-not-found'), $content);
        $this->assertStringNotContainsString(url('/500-server-error'), $content);
    }

    public function test_sitemap_includes_published_products_and_active_projects(): void
    {
        $product = Product::factory()->create([
            'slug' => 'sample-cob-strip',
            'product_name' => 'Sample COB Strip',
            'status' => 'publish',
        ]);

        $project = Project::factory()->create([
            'slug' => 'sample-flagship-project',
            'title' => 'Sample Flagship Project',
            'status' => Status::Active,
        ]);

        // Inactive project should not be included
        $inactiveProject = Project::factory()->create([
            'slug' => 'draft-project',
            'title' => 'Draft Project',
            'status' => Status::Inactive,
        ]);

        SitemapBuilder::clearCache();

        $response = $this->get('/sitemap.xml');
        $content = $response->getContent();

        $this->assertStringContainsString('<loc>'.url('/products/sample-cob-strip').'</loc>', $content);
        $this->assertStringContainsString('<loc>'.url('/project-detail?slug=sample-flagship-project').'</loc>', $content);
        $this->assertStringNotContainsString('draft-project', $content);
    }

    public function test_sitemap_artisan_command_generates_and_clears_cache(): void
    {
        $this->artisan('sitemap:generate')
            ->expectsOutputToContain('Sitemap generated successfully')
            ->assertSuccessful();

        $this->assertTrue(Cache::has(SitemapBuilder::CACHE_KEY));

        $this->artisan('sitemap:generate --clear')
            ->expectsOutputToContain('Sitemap cache cleared successfully.')
            ->assertSuccessful();

        $this->assertFalse(Cache::has(SitemapBuilder::CACHE_KEY));
    }

    public function test_sitemap_cache_is_cleared_when_page_or_project_is_updated(): void
    {
        $this->get('/sitemap.xml');
        $this->assertTrue(Cache::has(SitemapBuilder::CACHE_KEY));

        SitemapBuilder::clearCache();
        $this->assertFalse(Cache::has(SitemapBuilder::CACHE_KEY));
    }
}
