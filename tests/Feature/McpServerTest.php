<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Mcp\Protocol\McpServer;
use App\Mcp\Tools\Backend\InspectSchemaTool;
use App\Mcp\Tools\Backend\MutateModelTool;
use App\Mcp\Tools\Backend\QueryModelRecordsTool;
use App\Mcp\Tools\Backend\UpdatePageContentTool;
use App\Mcp\Tools\Frontend\ListBladeViewsTool;
use App\Mcp\Tools\Frontend\ListRoutesTool;
use App\Mcp\Tools\Frontend\ReadBladeViewTool;
use App\Mcp\Tools\Frontend\SearchFrontendAssetsTool;
use App\Mcp\Tools\PublicChat\ManageQuoteListTool;
use App\Mcp\Tools\PublicChat\SearchProductsTool;
use App\Mcp\Tools\PublicChat\SubmitQuoteEnquiryTool;
use App\Models\Enquiry;
use App\Models\Page;
use App\Models\PageMeta;
use App\Models\Product;
use Database\Seeders\PageSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class McpServerTest extends TestCase
{
    use RefreshDatabase;

    protected McpServer $server;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(PageSeeder::class);

        $this->server = new McpServer;
        $this->server->registerTool(app(ListBladeViewsTool::class));
        $this->server->registerTool(app(ReadBladeViewTool::class));
        $this->server->registerTool(app(ListRoutesTool::class));
        $this->server->registerTool(app(SearchFrontendAssetsTool::class));
        $this->server->registerTool(app(InspectSchemaTool::class));
        $this->server->registerTool(app(QueryModelRecordsTool::class));
        $this->server->registerTool(app(MutateModelTool::class));
        $this->server->registerTool(app(UpdatePageContentTool::class));
        $this->server->registerTool(app(SearchProductsTool::class));
        $this->server->registerTool(app(ManageQuoteListTool::class));
        $this->server->registerTool(app(SubmitQuoteEnquiryTool::class));
    }

    public function test_mcp_initialize_and_tools_list(): void
    {
        $initResponse = $this->server->handleRequest([
            'jsonrpc' => '2.0',
            'id' => 1,
            'method' => 'initialize',
        ]);

        $this->assertSame('2.0', $initResponse['jsonrpc']);
        $this->assertSame(1, $initResponse['id']);
        $this->assertSame('2024-11-05', $initResponse['result']['protocolVersion']);

        $listResponse = $this->server->handleRequest([
            'jsonrpc' => '2.0',
            'id' => 2,
            'method' => 'tools/list',
        ]);

        $this->assertIsArray($listResponse['result']['tools']);
        $toolNames = array_column($listResponse['result']['tools'], 'name');
        $this->assertContains('frontend_list_blade_views', $toolNames);
        $this->assertContains('backend_update_page_content', $toolNames);
        $this->assertContains('public_search_products', $toolNames);
    }

    public function test_frontend_exploration_tools(): void
    {
        // 1. List views
        $listResult = $this->server->handleRequest([
            'jsonrpc' => '2.0',
            'id' => 3,
            'method' => 'tools/call',
            'params' => [
                'name' => 'frontend_list_blade_views',
                'arguments' => ['subdirectory' => 'pages'],
            ],
        ]);
        $this->assertFalse($listResult['result']['isError']);
        $this->assertStringContainsString('solutions', $listResult['result']['content'][0]['text']);

        // 2. Read view
        $readResult = $this->server->handleRequest([
            'jsonrpc' => '2.0',
            'id' => 4,
            'method' => 'tools/call',
            'params' => [
                'name' => 'frontend_read_blade_view',
                'arguments' => ['view' => 'pages.solutions'],
            ],
        ]);
        $this->assertFalse($readResult['result']['isError']);
        $this->assertStringContainsString('solutions-hero', $readResult['result']['content'][0]['text']);

        // 3. List routes
        $routeResult = $this->server->handleRequest([
            'jsonrpc' => '2.0',
            'id' => 5,
            'method' => 'tools/call',
            'params' => [
                'name' => 'frontend_list_routes',
                'arguments' => ['search' => 'solutions'],
            ],
        ]);
        $this->assertFalse($routeResult['result']['isError']);
        $this->assertStringContainsString('solutions', $routeResult['result']['content'][0]['text']);
    }

    public function test_backend_update_solution_page_hero_banner_text(): void
    {
        // Execute MCP update on Solution page hero banner
        $response = $this->server->handleRequest([
            'jsonrpc' => '2.0',
            'id' => 6,
            'method' => 'tools/call',
            'params' => [
                'name' => 'backend_update_page_content',
                'arguments' => [
                    'page_slug' => 'solutions',
                    'meta_updates' => [
                        'hero.title' => 'Innovative Architectural Lighting Solutions',
                        'hero.lead' => 'Engineered for Performance & Sustainability',
                    ],
                ],
            ],
        ]);

        $this->assertFalse($response['result']['isError']);
        $this->assertStringContainsString('Page content successfully updated in MySQL', $response['result']['content'][0]['text']);

        // Verify in database
        $page = Page::where('slug', 'solutions')->firstOrFail();
        $titleMeta = PageMeta::where('page_id', $page->id)->where('key', 'hero.title')->first();
        $leadMeta = PageMeta::where('page_id', $page->id)->where('key', 'hero.lead')->first();

        $this->assertNotNull($titleMeta);
        $this->assertSame('Innovative Architectural Lighting Solutions', $titleMeta->value);
        $this->assertNotNull($leadMeta);
        $this->assertSame('Engineered for Performance & Sustainability', $leadMeta->value);
    }

    public function test_public_chat_search_quote_and_enquiry_flow(): void
    {
        // Seed a sample product
        $product = Product::create([
            'airtable_id' => 'rec_test_12345',
            'product_name' => 'Solaris Outdoor Garden Spike 50W',
            'slug' => 'solaris-outdoor-garden-spike-50w',
            'status' => 'active',
            'category' => 'Outdoor',
            'product_code' => 'SOL-50W',
            'product_description' => 'High efficiency waterproof IP66 outdoor garden spotlight.',
        ]);

        // 1. Search products
        $searchResponse = $this->server->handleRequest([
            'jsonrpc' => '2.0',
            'id' => 7,
            'method' => 'tools/call',
            'params' => [
                'name' => 'public_search_products',
                'arguments' => ['query' => 'outdoor garden'],
            ],
        ]);
        $this->assertFalse($searchResponse['result']['isError']);
        $this->assertStringContainsString('Solaris Outdoor Garden Spike 50W', $searchResponse['result']['content'][0]['text']);

        // 2. Add to quote list
        $quoteResponse = $this->server->handleRequest([
            'jsonrpc' => '2.0',
            'id' => 8,
            'method' => 'tools/call',
            'params' => [
                'name' => 'public_manage_quote_list',
                'arguments' => [
                    'action' => 'add',
                    'product_id' => $product->id,
                    'quantity' => 4,
                ],
            ],
        ]);
        $this->assertFalse($quoteResponse['result']['isError']);
        $this->assertStringContainsString('Added 4x', $quoteResponse['result']['content'][0]['text']);

        // 3. Submit quote enquiry
        $enquiryResponse = $this->server->handleRequest([
            'jsonrpc' => '2.0',
            'id' => 9,
            'method' => 'tools/call',
            'params' => [
                'name' => 'public_submit_quote_enquiry',
                'arguments' => [
                    'name' => 'Jane Doe',
                    'email' => 'jane@example.com',
                    'phone' => '+61 400 123 456',
                    'notes' => 'Need delivery to Sydney site by next Tuesday.',
                ],
            ],
        ]);
        $this->assertFalse($enquiryResponse['result']['isError']);
        $this->assertStringContainsString('submitted successfully', $enquiryResponse['result']['content'][0]['text']);

        // Verify enquiry in database
        $enquiry = Enquiry::where('email', 'jane@example.com')->first();
        $this->assertNotNull($enquiry);
        $this->assertSame('Jane Doe', $enquiry->name);
        $this->assertArrayHasKey('items', $enquiry->payload);
        $this->assertCount(1, $enquiry->payload['items']);
    }
}
