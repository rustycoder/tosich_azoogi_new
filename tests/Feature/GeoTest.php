<?php

namespace Tests\Feature;

use App\Enums\Status;
use App\Models\Product;
use App\Models\Project;
use App\Models\User;
use App\Support\LlmsTxtBuilder;
use Database\Seeders\PageSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class GeoTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(PageSeeder::class);
    }

    public function test_robots_txt_permits_verified_ai_search_crawlers_and_links_sitemap(): void
    {
        $robotsPath = public_path('robots.txt');
        $this->assertFileExists($robotsPath);

        $robotsContent = (string) file_get_contents($robotsPath);
        $this->assertStringContainsString('GPTBot', $robotsContent);
        $this->assertStringContainsString('OAI-SearchBot', $robotsContent);
        $this->assertStringContainsString('PerplexityBot', $robotsContent);
        $this->assertStringContainsString('ClaudeBot', $robotsContent);
        $this->assertStringContainsString('Google-Extended', $robotsContent);
        $this->assertStringContainsString('Applebot-Extended', $robotsContent);
        $this->assertStringContainsString('Bingbot', $robotsContent);
        $this->assertStringContainsString('Sitemap: https://azoogi.com/sitemap.xml', $robotsContent);
        $this->assertStringContainsString('Disallow: /dashboard/', $robotsContent);
    }

    public function test_llms_txt_summary_endpoint_returns_valid_markdown_and_headers(): void
    {
        $response = $this->get('/llms.txt');

        $response->assertOk();
        $response->assertHeader('Content-Type', 'text/plain; charset=utf-8');
        $cacheControl = (string) $response->headers->get('Cache-Control');
        $this->assertStringContainsString('max-age=86400', $cacheControl);
        $this->assertStringContainsString('public', $cacheControl);

        $content = $response->getContent();
        $this->assertStringContainsString('# Azoogi | Australian Manufacturer of Custom Architectural LED Lighting', $content);
        $this->assertStringContainsString('Matraville NSW', $content);
        $this->assertStringContainsString('Australian Standards and EESS', $content);
        $this->assertStringContainsString('Azoogi does not install', $content);
        $this->assertStringContainsString('Casambi Wireless Mesh Control', $content);
        $this->assertStringContainsString('Silvair Bluetooth Mesh', $content);
        $this->assertStringContainsString('DALI Control Centre', $content);
        $this->assertStringContainsString('Madrix Pixel Mapping', $content);
        $this->assertStringContainsString('LED Strip Calculator', $content);
        $this->assertStringContainsString('/llms-full.txt', $content);
    }

    public function test_llms_full_txt_endpoint_returns_comprehensive_catalogue_and_case_studies(): void
    {
        Product::factory()->create([
            'slug' => 'ultra-cob-pro-24v',
            'product_name' => 'Ultra COB Pro 24V Strip',
            'product_code' => 'AZ-COB-24V',
            'category' => 'Strips and Flex',
            'status' => 'publish',
            'product_description' => 'Continuous dotless COB architectural LED strip with CRI 95+.',
        ]);

        Project::factory()->create([
            'slug' => 'sydney-commercial-tower',
            'title' => 'Sydney Commercial Tower',
            'tag' => 'Commercial High-Rise',
            'location' => 'Sydney CBD, NSW',
            'status' => Status::Active,
            'summary' => 'Custom linear extrusion lighting across 40 commercial floors.',
        ]);

        LlmsTxtBuilder::clearCache();

        $response = $this->get('/llms-full.txt');
        $response->assertOk();
        $response->assertHeader('Content-Type', 'text/plain; charset=utf-8');

        $content = $response->getContent();
        $this->assertStringContainsString('Ultra COB Pro 24V Strip', $content);
        $this->assertStringContainsString('AZ-COB-24V', $content);
        $this->assertStringContainsString('Sydney Commercial Tower', $content);
        $this->assertStringContainsString('Sydney CBD, NSW', $content);
        $this->assertStringContainsString('Custom linear extrusion lighting', $content);
    }

    public function test_geo_artisan_command_generates_and_clears_cache(): void
    {
        $this->artisan('geo:generate')
            ->expectsOutputToContain('GEO feeds (/llms.txt, /llms-full.txt, /sitemap.xml) generated and cached successfully.')
            ->assertSuccessful();

        $this->assertTrue(Cache::has(LlmsTxtBuilder::CACHE_KEY_SUMMARY));
        $this->assertTrue(Cache::has(LlmsTxtBuilder::CACHE_KEY_FULL));

        $this->artisan('geo:generate --clear')
            ->expectsOutputToContain('GEO and Sitemap caches cleared successfully.')
            ->assertSuccessful();

        $this->assertFalse(Cache::has(LlmsTxtBuilder::CACHE_KEY_SUMMARY));
        $this->assertFalse(Cache::has(LlmsTxtBuilder::CACHE_KEY_FULL));
    }

    public function test_site_layout_renders_enriched_json_ld_knowledge_graph(): void
    {
        $response = $this->get('/');
        $response->assertOk();

        $content = $response->getContent();
        $this->assertStringContainsString('"@context": "https://schema.org"', $content);
        $this->assertStringContainsString('"@type": "Organization"', $content);
        $this->assertStringContainsString('"legalName": "Azoogi LED Lighting"', $content);
        $this->assertStringContainsString('"@type": "WebSite"', $content);
        $this->assertStringContainsString('"@type": "SearchAction"', $content);
        $this->assertStringContainsString('Casambi Bluetooth Low Energy Mesh', $content);
    }

    public function test_authenticated_admin_can_view_and_edit_llm_feeds_in_dashboard(): void
    {
        $admin = User::factory()->admin()->create();

        $response = $this->actingAs($admin)->get('/dashboard/content/llms');
        $response->assertOk();
        $response->assertSee('AI &amp; LLM Feeds', false);
        $response->assertSee('Summary Mode');

        // Update with custom content
        $customText = "# Custom Azoogi LLM Index\n\n> Custom AI feed description for testing.";
        $updateResponse = $this->actingAs($admin)->put('/dashboard/content/llms', [
            'content' => $customText,
            'is_custom' => '1',
        ]);

        $updateResponse->assertRedirect(route('dashboard.llms.index'));
        $updateResponse->assertSessionHas('status');

        // Verify /llms.txt serves custom content
        $llmsResponse = $this->get('/llms.txt');
        $llmsResponse->assertOk();
        $this->assertStringContainsString('Custom Azoogi LLM Index', $llmsResponse->getContent());

        // Reset back to default
        $resetResponse = $this->actingAs($admin)->post('/dashboard/content/llms/reset');
        $resetResponse->assertRedirect(route('dashboard.llms.index'));

        $resetLlmsResponse = $this->get('/llms.txt');
        $this->assertStringContainsString('Australian Manufacturer of Custom Architectural LED Lighting', $resetLlmsResponse->getContent());
    }
}
