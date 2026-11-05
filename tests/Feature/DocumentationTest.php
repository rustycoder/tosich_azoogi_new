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
            // AI & Automation topics
            'ai-models',
            'ai-rates',
            'ai-widget',
            'ai-rules',
            'ai-context',
            'ai-faqs',
            'ai-prompt',
            'ai-chat-logs',
            'ai-testing',
            // Test backward-compatible aliases as well
            'media',
            'content',
            'maintenance',
            'alt',
            'accessibility',
            'ordering',
            'airtable',
            'catalog',
            'models',
            'model',
            'rates',
            'rate',
            'pricing',
            'cost',
            'widget',
            'branding',
            'rules',
            'rule',
            'context',
            'company-info',
            'faqs',
            'faq',
            'prompt',
            'chat-logs',
            'chat',
            'conversations',
            'sessions',
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

        $mcpResponse = $this->actingAs($user)->get('/dashboard/docs/mcp');
        $mcpResponse->assertOk();
        $mcpResponse->assertSee('In-Dashboard Admin Copilot', false);
        $mcpResponse->assertSee('frontend_list_blade_views', false);
        $mcpResponse->assertSee('frontend_read_blade_view', false);
        $mcpResponse->assertSee('frontend_list_routes', false);
        $mcpResponse->assertSee('frontend_search_assets', false);
        $mcpResponse->assertSee('backend_inspect_schema', false);
        $mcpResponse->assertSee('backend_query_records', false);
        $mcpResponse->assertSee('backend_update_page_content', false);
        $mcpResponse->assertSee('backend_mutate_model', false);
        $mcpResponse->assertSee('backend_airtable_sync_audit', false);
        $mcpResponse->assertSee('backend_manage_ai_knowledge', false);
        $mcpResponse->assertSee('backend_chat_sessions_insight', false);
        $mcpResponse->assertSee('backend_dashboard_analytics', false);
        $mcpResponse->assertSee('backend_seo_and_geo_manager', false);
        $mcpResponse->assertSee('public_search_products', false);
        $mcpResponse->assertSee('public_manage_quote_list', false);
        $mcpResponse->assertSee('public_submit_quote_enquiry', false);
    }
}
