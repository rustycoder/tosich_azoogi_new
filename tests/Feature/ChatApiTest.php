<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Mail\ChatIssueReportedMail;
use App\Models\ChatSession;
use App\Models\LlmFeed;
use App\Models\Product;
use App\Services\Chat\Tools\ProductSearchAndFilterTool;
use App\Services\Chat\Tools\QuoteCartManagerTool;
use Database\Seeders\PageSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class ChatApiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(PageSeeder::class);
    }

    public function test_visitor_can_send_chat_message_and_receive_recommendations(): void
    {
        // Seed active sample product
        Product::create([
            'airtable_id' => 'rec_chat_test_01',
            'product_name' => 'Solaris Outdoor Garden Spotlight 50W',
            'slug' => 'solaris-outdoor-garden-spotlight-50w',
            'status' => 'active',
            'category' => 'Outdoor',
            'product_code' => 'SOL-50W-IP66',
            'product_description' => 'IP66 waterproof commercial outdoor garden spotlight with 3000K warm white LED.',
        ]);

        $response = $this->postJson('/api/chat/message', [
            'message' => 'Show me outdoor garden lights',
        ]);

        $response->assertOk()
            ->assertJsonStructure([
                'status',
                'session_uuid',
                'reply',
                'cards',
                'quote_count',
            ])
            ->assertJson([
                'status' => 'success',
            ]);

        $this->assertDatabaseHas('chat_sessions', [
            'messages_count' => 2, // 1 user + 1 assistant
        ]);

        $this->assertDatabaseHas('chat_messages', [
            'sender' => 'user',
            'content' => 'Show me outdoor garden lights',
        ]);

        $this->assertDatabaseHas('chat_messages', [
            'sender' => 'assistant',
        ]);
    }

    public function test_visitor_can_add_product_to_quote_from_chat(): void
    {
        $product = Product::create([
            'airtable_id' => 'rec_chat_test_02',
            'product_name' => 'Linear Architectural Extrusion 24W',
            'slug' => 'linear-architectural-extrusion-24w',
            'status' => 'active',
            'category' => 'Linear',
            'product_code' => 'LIN-24W-DALI',
        ]);

        $response = $this->postJson('/api/chat/quote/add', [
            'product_id' => $product->id,
            'quantity' => 2,
        ]);

        $response->assertOk()
            ->assertJson([
                'status' => 'success',
                'quote_count' => 1,
            ]);

        $quoteList = session()->get('visitor_quote_list', []);
        $this->assertCount(1, $quoteList);
        $this->assertSame(2, $quoteList[$product->id]['quantity']);
    }

    public function test_quote_cart_manager_tool_can_update_quantity(): void
    {
        $product = Product::create([
            'airtable_id' => 'rec_chat_test_03',
            'product_name' => '12W Garden Light',
            'slug' => '12w-garden-light',
            'status' => 'active',
            'category' => 'Garden',
            'product_code' => 'GL003',
        ]);

        $tool = app(QuoteCartManagerTool::class);

        // 1. Initial add of 8 units
        $tool->execute([
            'action' => 'add',
            'product_identifier' => 'GL003',
            'quantity' => 8,
        ]);

        $quoteList = session()->get('visitor_quote_list', []);
        $this->assertSame(8, $quoteList[$product->id]['quantity']);

        // 2. Update quantity from 8 to 16
        $tool->execute([
            'action' => 'update',
            'product_identifier' => 'GL003',
            'quantity' => 16,
        ]);

        $quoteList = session()->get('visitor_quote_list', []);
        $this->assertSame(16, $quoteList[$product->id]['quantity']);
    }

    public function test_visitor_can_load_conversation_history_by_session_uuid(): void
    {
        $session = ChatSession::create([
            'uuid' => 'a1b2c3d4-e5f6-7890-abcd-ef1234567890',
            'ip_address' => '127.0.0.1',
            'status' => 'active',
        ]);

        $session->messages()->create([
            'sender' => 'user',
            'content' => 'Hello Azoogi',
        ]);

        $session->messages()->create([
            'sender' => 'assistant',
            'content' => 'Welcome! How can I assist you?',
        ]);

        $response = $this->getJson("/api/chat/session/{$session->uuid}");

        $response->assertOk()
            ->assertJson([
                'status' => 'success',
                'session_uuid' => $session->uuid,
            ])
            ->assertJsonCount(2, 'messages');
    }

    public function test_visitor_can_clear_chat_history(): void
    {
        $session = ChatSession::create([
            'uuid' => 'b2c3d4e5-f6a7-8901-bcde-f12345678901',
            'ip_address' => '127.0.0.1',
            'status' => 'active',
            'messages_count' => 1,
        ]);

        $session->messages()->create([
            'sender' => 'user',
            'content' => 'Previous inquiry',
        ]);

        $response = $this->postJson('/api/chat/clear', [
            'session_uuid' => $session->uuid,
        ]);

        $response->assertOk()
            ->assertJson(['status' => 'success']);

        $this->assertDatabaseHas('chat_sessions', [
            'id' => $session->id,
            'status' => 'completed',
        ]);
        $this->assertDatabaseCount('chat_messages', 1);
    }

    public function test_visitor_can_initialize_session_with_pre_chat_intake_data(): void
    {
        $response = $this->postJson('/api/chat/init', [
            'name' => 'Sarah Connor',
            'email' => 'sarah@cyberdyne.com',
            'project_name' => 'Skynet Headquarters Facade',
        ]);

        $response->assertOk()
            ->assertJsonStructure([
                'status',
                'session_uuid',
                'lead_name',
                'lead_email',
                'project_name',
                'greeting',
                'has_lead',
            ])
            ->assertJson([
                'status' => 'success',
                'lead_name' => 'Sarah Connor',
                'lead_email' => 'sarah@cyberdyne.com',
                'project_name' => 'Skynet Headquarters Facade',
                'has_lead' => true,
            ]);

        $this->assertStringContainsString('Sarah', $response->json('greeting'));
        $this->assertStringContainsString('Skynet Headquarters Facade', $response->json('greeting'));

        $this->assertDatabaseHas('chat_sessions', [
            'uuid' => $response->json('session_uuid'),
            'lead_name' => 'Sarah Connor',
            'lead_email' => 'sarah@cyberdyne.com',
            'project_name' => 'Skynet Headquarters Facade',
        ]);
    }

    public function test_visitor_message_persists_lead_and_project_name_in_database(): void
    {
        $response = $this->postJson('/api/chat/message', [
            'message' => 'Need 4000K linear fixtures',
            'name' => 'Marcus Wright',
            'email' => 'marcus@resistance.org',
            'project_name' => 'Underground Bunker Sector 4',
        ]);

        $response->assertOk();

        $this->assertDatabaseHas('chat_sessions', [
            'uuid' => $response->json('session_uuid'),
            'lead_name' => 'Marcus Wright',
            'lead_email' => 'marcus@resistance.org',
            'project_name' => 'Underground Bunker Sector 4',
        ]);
    }

    public function test_quote_cart_manager_tool_updates_quantity_and_deduplicates_keys(): void
    {
        $product = Product::create([
            'airtable_id' => 'rec_gl_003',
            'product_name' => '12W Garden Light',
            'slug' => '12w-garden-light',
            'status' => 'active',
            'category' => 'Garden Light',
            'product_code' => 'GL003',
        ]);

        $tool = new QuoteCartManagerTool;

        // 1. Initial add: 8 units by SKU "GL003"
        $addResult = $tool->execute([
            'action' => 'add',
            'product_identifier' => 'GL003',
            'quantity' => 8,
        ]);

        $this->assertSame('success', $addResult['result']['status']);
        $this->assertSame(1, $addResult['result']['total_items']);
        $this->assertSame(8, $addResult['result']['quote_items'][0]['quantity']);

        // 2. User requests update to 16 units by product slug "12w-garden-light"
        $updateResult = $tool->execute([
            'action' => 'update',
            'product_identifier' => '12w-garden-light',
            'quantity' => 16,
        ]);

        $this->assertSame('success', $updateResult['result']['status']);
        $this->assertSame(1, $updateResult['result']['total_items'], 'Must not create duplicate item entries for different key representations');
        $this->assertSame(16, $updateResult['result']['quote_items'][0]['quantity'], 'Quantity must be updated to 16 rather than 8+16=24');
    }

    public function test_visitor_can_report_chat_issue_and_email_is_sent_to_admins(): void
    {
        Mail::fake();

        $session = ChatSession::create([
            'uuid' => '550e8400-e29b-41d4-a716-446655440099',
            'ip_address' => '127.0.0.1',
            'lead_name' => 'Alex Architect',
            'lead_email' => 'alex@studio.com.au',
            'status' => 'active',
            'messages_count' => 1,
        ]);

        $session->messages()->create([
            'sender' => 'user',
            'content' => 'Can I get DALI specs?',
        ]);

        $response = $this->postJson('/api/chat/report-issue', [
            'issue' => 'The datasheet download link did not open in Safari.',
            'session_uuid' => $session->uuid,
            'name' => 'Alex Architect',
            'email' => 'alex@studio.com.au',
            'page_url' => 'http://localhost:8000/solutions',
        ]);

        $response->assertOk()
            ->assertJson([
                'status' => 'success',
            ]);

        Mail::assertSent(ChatIssueReportedMail::class, function (ChatIssueReportedMail $mail) {
            return $mail->hasTo('admin@azoogi.com')
                && $mail->hasTo('adrian@azoogi.com')
                && $mail->issueDescription === 'The datasheet download link did not open in Safari.'
                && $mail->reporterEmail === 'alex@studio.com.au';
        });
    }

    public function test_report_chat_issue_uses_configured_widget_recipient_emails(): void
    {
        Mail::fake();

        LlmFeed::query()->updateOrCreate(
            ['key' => 'ai_widget_branding'],
            [
                'content' => json_encode([
                    'report_issue_emails' => 'support@azoogi.com, qa@azoogi.com',
                ]),
                'is_custom' => true,
            ]
        );

        $response = $this->postJson('/api/chat/report-issue', [
            'issue' => 'Custom recipient check.',
        ]);

        $response->assertOk()->assertJson(['status' => 'success']);

        Mail::assertSent(ChatIssueReportedMail::class, function (ChatIssueReportedMail $mail) {
            return $mail->hasTo('support@azoogi.com')
                && $mail->hasTo('qa@azoogi.com')
                && ! $mail->hasTo('admin@azoogi.com');
        });
    }

    public function test_plaster_profile_search_excludes_strip_connectors(): void
    {
        Product::create([
            'airtable_id' => 'rec_plaster_01',
            'product_name' => 'Bendable Plaster Aluminium Profile',
            'slug' => 'bendable-plaster-aluminium-profile',
            'status' => 'active',
            'category' => 'Profile',
            'product_code' => 'PROF-PLASTER-1616',
            'product_description' => 'Flush-mounted plaster-in aluminium extrusion profile for walls and ceilings.',
        ]);

        Product::create([
            'airtable_id' => 'rec_conn_01',
            'product_name' => 'COB LED Strip Connector',
            'slug' => 'cob-led-strip-connector',
            'status' => 'active',
            'category' => 'Accessories',
            'product_code' => 'CONN-COB-10',
            'product_description' => 'Solderless strip connector compatible with aluminium and plaster profile installations.',
        ]);

        /** @var ProductSearchAndFilterTool $tool */
        $tool = app(ProductSearchAndFilterTool::class);
        $result = $tool->execute(['query' => 'plaster profile']);

        $names = array_column($result['result']['products'], 'name');
        $this->assertContains('Bendable Plaster Aluminium Profile', $names);
        $this->assertNotContains('COB LED Strip Connector', $names);
    }

    public function test_cob_strip_search_returns_strips_and_excludes_connectors_and_profiles(): void
    {
        Product::create([
            'airtable_id' => 'rec_cob_strip_01',
            'product_name' => 'Single Colour COB',
            'slug' => 'single-colour-cob',
            'status' => 'active',
            'category' => 'LED Strip',
            'category_path' => ['LED Strip', 'COB'],
            'product_code' => 'COB001',
            'product_description' => 'Dot-free 24V COB LED strip.',
        ]);

        Product::create([
            'airtable_id' => 'rec_cob_conn_01',
            'product_name' => 'COB LED Strip Connector',
            'slug' => 'cob-led-strip-connector',
            'status' => 'active',
            'category' => 'Accessories',
            'product_code' => 'CONN-COB-01',
            'product_description' => 'Solderless connector for COB LED strip.',
        ]);

        Product::create([
            'airtable_id' => 'rec_strip_prof_01',
            'product_name' => 'Recessed LED Strip Profile',
            'slug' => 'recessed-led-strip-profile',
            'status' => 'active',
            'category' => 'Profile',
            'product_code' => 'PROF-REC-01',
            'product_description' => 'Aluminium profile for COB and SMD LED strip.',
        ]);

        /** @var ProductSearchAndFilterTool $tool */
        $tool = app(ProductSearchAndFilterTool::class);
        $result = $tool->execute(['query' => 'COB strip']);

        $names = array_column($result['result']['products'], 'name');
        $this->assertContains('Single Colour COB', $names);
        $this->assertNotContains('COB LED Strip Connector', $names);
        $this->assertNotContains('Recessed LED Strip Profile', $names);
    }

    public function test_vague_query_returns_category_redirect_and_visual_category_cards(): void
    {
        /** @var ProductSearchAndFilterTool $tool */
        $tool = app(ProductSearchAndFilterTool::class);
        $result = $tool->execute(['query' => 'show me outdoor lights']);

        $this->assertSame('category_redirect', $result['result']['mode']);
        $this->assertSame([], $result['result']['products']);
        $this->assertNotEmpty($result['result']['categories']);
        $this->assertSame('categories_carousel', $result['cards']['type']);
        $this->assertNotEmpty($result['cards']['items']);
        $this->assertSame('category_card', $result['cards']['items'][0]['type']);
        $this->assertStringContainsString('/products?category=', $result['cards']['items'][0]['url']);
    }

    public function test_catalog_wide_vague_query_returns_category_cards(): void
    {
        /** @var ProductSearchAndFilterTool $tool */
        $tool = app(ProductSearchAndFilterTool::class);
        $result = $tool->execute(['query' => 'what lighting do you have?']);

        $this->assertSame('category_redirect', $result['result']['mode']);
        $this->assertSame([], $result['result']['products']);
        $this->assertSame('categories_carousel', $result['cards']['type']);
        $this->assertNotEmpty($result['cards']['items']);
        $this->assertNotEmpty($result['result']['catalog_url']);
    }

    public function test_outdoor_strip_returns_outdoor_strips_and_excludes_garden_lights_accessories_and_controllers(): void
    {
        Product::create([
            'airtable_id' => 'rec_out_strip_01',
            'product_name' => 'IP67 Outdoor LED Strip 24V',
            'slug' => 'ip67-outdoor-led-strip-24v',
            'status' => 'active',
            'category' => 'LED Strip',
            'category_path' => ['Strips and Flex', 'Outdoor Strips'],
            'product_code' => 'STRIP-OUT-67',
            'product_description' => 'Waterproof IP67 silicone encapsulated outdoor LED strip.',
        ]);

        Product::create([
            'airtable_id' => 'rec_garden_01',
            'product_name' => '7W Garden Spike Light',
            'slug' => '7w-garden-spike-light',
            'status' => 'active',
            'category' => 'Garden Light',
            'category_path' => ['Outdoor & Architectural', 'Garden Light'],
            'product_code' => 'GL001',
            'product_description' => 'IP65 outdoor garden spike spotlight.',
        ]);

        Product::create([
            'airtable_id' => 'rec_strip_conn_01',
            'product_name' => 'IP67 Strip Power Feed Cable',
            'slug' => 'ip67-strip-power-feed-cable',
            'status' => 'active',
            'category' => 'Strip Accessories',
            'category_path' => ['Strips and Flex', 'Accessories'],
            'product_code' => 'ACC-67',
            'product_description' => 'Waterproof power feed cable for outdoor LED strip.',
        ]);

        Product::create([
            'airtable_id' => 'rec_strip_ctrl_01',
            'product_name' => 'Outdoor Strip RGB Controller',
            'slug' => 'outdoor-strip-rgb-controller',
            'status' => 'active',
            'category' => 'Controllers',
            'category_path' => ['Strips and Flex', 'Controllers'],
            'product_code' => 'CTRL-67',
            'product_description' => 'IP67 outdoor LED strip controller.',
        ]);

        /** @var ProductSearchAndFilterTool $tool */
        $tool = app(ProductSearchAndFilterTool::class);
        $result = $tool->execute(['query' => 'Do you have outdoor strip?']);

        $this->assertSame('products_carousel', $result['cards']['type']);
        $names = array_column($result['result']['products'], 'name');
        $this->assertContains('IP67 Outdoor LED Strip 24V', $names);
        $this->assertNotContains('7W Garden Spike Light', $names);
        $this->assertNotContains('IP67 Strip Power Feed Cable', $names);
        $this->assertNotContains('Outdoor Strip RGB Controller', $names);
    }

    public function test_outdoor_strip_with_only_accessories_falls_back_to_category_cards(): void
    {
        Product::create([
            'airtable_id' => 'rec_strip_acc_only',
            'product_name' => 'Strip Mounting Clip Set',
            'slug' => 'strip-mounting-clip-set',
            'status' => 'active',
            'category' => 'Strip Accessories',
            'category_path' => ['Strips and Flex', 'Accessories'],
            'product_code' => 'CLIP-01',
            'product_description' => 'Silicone mounting clip for LED strip.',
        ]);

        Product::create([
            'airtable_id' => 'rec_strip_ctrl_only',
            'product_name' => 'Strip RF Controller',
            'slug' => 'strip-rf-controller',
            'status' => 'active',
            'category' => 'Controllers',
            'category_path' => ['Drivers & Controls', 'Controllers'],
            'product_code' => 'CTRL-01',
            'product_description' => 'RF controller for LED strip.',
        ]);

        /** @var ProductSearchAndFilterTool $tool */
        $tool = app(ProductSearchAndFilterTool::class);
        $result = $tool->execute(['query' => 'Do you have outdoor strip?']);

        $this->assertSame('category_redirect', $result['result']['mode']);
        $this->assertSame([], $result['result']['products']);
        $this->assertSame('categories_carousel', $result['cards']['type']);
    }

    public function test_outdoor_downlight_returns_downlights_and_excludes_garden_lights(): void
    {
        Product::create([
            'airtable_id' => 'rec_out_dl_01',
            'product_name' => 'IP65 Outdoor Architectural Downlight',
            'slug' => 'ip65-outdoor-architectural-downlight',
            'status' => 'active',
            'category' => 'Downlight',
            'category_path' => ['Downlight', 'Exterior Downlights'],
            'product_code' => 'DL-OUT-65',
            'product_description' => 'Weatherproof IP65 outdoor soffit downlight.',
        ]);

        Product::create([
            'airtable_id' => 'rec_garden_02',
            'product_name' => 'Garden Bollard Light',
            'slug' => 'garden-bollard-light',
            'status' => 'active',
            'category' => 'Garden Light',
            'category_path' => ['Outdoor & Architectural', 'Garden Light'],
            'product_code' => 'GB001',
            'product_description' => 'Outdoor IP65 garden bollard.',
        ]);

        /** @var ProductSearchAndFilterTool $tool */
        $tool = app(ProductSearchAndFilterTool::class);
        $result = $tool->execute(['query' => 'outdoor downlight']);

        $this->assertSame('products_carousel', $result['cards']['type']);
        $names = array_column($result['result']['products'], 'name');
        $this->assertContains('IP65 Outdoor Architectural Downlight', $names);
        $this->assertNotContains('Garden Bollard Light', $names);
    }
}
