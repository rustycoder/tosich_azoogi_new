<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Enums\ContentResource;
use App\Enums\Status;
use App\Enums\UserType;
use App\Mcp\Tools\Backend\AirtableSyncAndAuditTool;
use App\Mcp\Tools\Backend\ChatSessionsInsightTool;
use App\Mcp\Tools\Backend\DashboardAnalyticsTool;
use App\Mcp\Tools\Backend\ManageAiKnowledgeTool;
use App\Mcp\Tools\Backend\MutateModelTool;
use App\Mcp\Tools\Backend\SeoAndGeoManagerTool;
use App\Models\ChatMessage;
use App\Models\ChatSession;
use App\Models\ContentPermission;
use App\Models\Page;
use App\Models\User;
use Database\Seeders\PageSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminCopilotMcpTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(PageSeeder::class);

        $this->admin = User::factory()->create([
            'name' => 'Admin User',
            'email' => 'admin@azoogi.com.au',
            'user_type' => UserType::Admin,
            'status' => Status::Active,
        ]);
    }

    public function test_dashboard_renders_admin_copilot_widget_for_authenticated_user(): void
    {
        $response = $this->actingAs($this->admin)->get(route('dashboard.home'));

        $response->assertOk();
        $response->assertSee('id="admin-copilot-widget"', false);
        $response->assertSee('Admin Copilot', false);
    }

    public function test_admin_copilot_endpoints_require_authentication(): void
    {
        $this->postJson(route('dashboard.ai-copilot.init'))
            ->assertUnauthorized();

        $this->postJson(route('dashboard.ai-copilot.message'), ['message' => 'Hello'])
            ->assertUnauthorized();
    }

    public function test_admin_copilot_init_message_and_clear_lifecycle(): void
    {
        $initResponse = $this->actingAs($this->admin)
            ->postJson(route('dashboard.ai-copilot.init'));

        $initResponse->assertOk()
            ->assertJsonPath('status', 'success')
            ->assertJsonPath('user_name', 'Admin User');

        $msgResponse = $this->actingAs($this->admin)
            ->postJson(route('dashboard.ai-copilot.message'), [
                'message' => 'Show me pending enquiries and website analytics',
                'current_url' => route('dashboard.home'),
            ]);

        $msgResponse->assertOk()
            ->assertJsonPath('status', 'success')
            ->assertJsonStructure(['status', 'reply', 'cards', 'tool_calls']);

        $this->assertNotEmpty($msgResponse->json('cards'));
        $this->assertSame('backend_dashboard_analytics', $msgResponse->json('cards.0.tool'));

        $clearResponse = $this->actingAs($this->admin)
            ->postJson(route('dashboard.ai-copilot.clear'));

        $clearResponse->assertOk()
            ->assertJsonPath('status', 'success');
    }

    public function test_manage_ai_knowledge_mcp_tool_crud(): void
    {
        $tool = app(ManageAiKnowledgeTool::class);

        // 1. Upsert a new FAQ
        $createRes = $tool->execute([
            'section' => 'faqs',
            'action' => 'upsert',
            'id' => 'faq_mcp_test',
            'category' => 'Controls',
            'title' => 'Does Azoogi support DALI-2 scenes?',
            'content' => 'Yes, all Azoogi DALI-2 drivers and controllers support multi-scene programming.',
            'is_active' => true,
        ]);

        $this->assertFalse($createRes['isError']);
        $payload = json_decode($createRes['content'], true);
        $this->assertSame('created', $payload['action']);
        $this->assertSame('faq_mcp_test', $payload['item']['id']);

        // 2. Toggle status
        $toggleRes = $tool->execute([
            'section' => 'faqs',
            'action' => 'toggle',
            'id' => 'faq_mcp_test',
        ]);
        $this->assertFalse($toggleRes['isError']);
        $togglePayload = json_decode($toggleRes['content'], true);
        $this->assertFalse($togglePayload['item']['is_active']);

        // 3. Delete FAQ
        $deleteRes = $tool->execute([
            'section' => 'faqs',
            'action' => 'delete',
            'id' => 'faq_mcp_test',
        ]);
        $this->assertFalse($deleteRes['isError']);
    }

    public function test_chat_sessions_insight_mcp_tool_lists_and_converts_to_enquiry(): void
    {
        $session = ChatSession::create([
            'uuid' => '11111111-2222-3333-4444-555555555555',
            'lead_name' => 'Architect Sam',
            'lead_email' => 'sam@studio.com.au',
            'project_name' => 'Barangaroo Tower',
            'status' => 'active',
            'is_read' => false,
            'messages_count' => 1,
        ]);

        ChatMessage::create([
            'chat_session_id' => $session->id,
            'sender' => 'user',
            'content' => 'We need 120m of recessed linear profile for Barangaroo Tower.',
        ]);

        $tool = app(ChatSessionsInsightTool::class);

        // List unread sessions
        $listRes = $tool->execute(['action' => 'list', 'filter' => 'unread']);
        $this->assertFalse($listRes['isError']);
        $listData = json_decode($listRes['content'], true);
        $this->assertSame(1, $listData['metrics']['unread_sessions']);

        // Convert to enquiry
        $convertRes = $tool->execute([
            'action' => 'convert_to_enquiry',
            'session_id' => $session->id,
        ]);
        $this->assertFalse($convertRes['isError']);
        $convertData = json_decode($convertRes['content'], true);
        $this->assertNotEmpty($convertData['enquiry_id']);
        $this->assertDatabaseHas('enquiries', [
            'id' => $convertData['enquiry_id'],
            'email' => 'sam@studio.com.au',
        ]);
    }

    public function test_seo_and_geo_manager_and_sync_status_tools(): void
    {
        $seoTool = app(SeoAndGeoManagerTool::class);
        $auditRes = $seoTool->execute(['action' => 'audit_seo']);
        $this->assertFalse($auditRes['isError']);
        $auditData = json_decode($auditRes['content'], true);
        $this->assertGreaterThan(0, $auditData['total_pages']);

        $refreshRes = $seoTool->execute(['action' => 'refresh_all']);
        $this->assertFalse($refreshRes['isError']);

        $syncTool = app(AirtableSyncAndAuditTool::class);
        $statusRes = $syncTool->execute(['action' => 'status']);
        $this->assertFalse($statusRes['isError']);

        $analyticsTool = app(DashboardAnalyticsTool::class);
        $metricsRes = $analyticsTool->execute(['metric' => 'all']);
        $this->assertFalse($metricsRes['isError'], $metricsRes['content'] ?? '');
    }

    public function test_mutation_guard_enforces_staff_role_permissions(): void
    {
        $staff = User::factory()->create([
            'user_type' => UserType::Staff,
            'status' => Status::Active,
        ]);

        // Grant only Datasheet permission, NOT Pages or Products
        ContentPermission::create([
            'user_id' => $staff->id,
            'resource' => ContentResource::Datasheet->value,
        ]);

        $this->actingAs($staff);

        $mutateTool = app(MutateModelTool::class);
        $page = Page::firstOrFail();

        // Attempting to mutate a page without permission should fail
        $deniedRes = $mutateTool->execute([
            'model' => 'page',
            'action' => 'update',
            'id' => $page->id,
            'attributes' => ['title' => 'Unauthorized Title'],
        ]);

        $this->assertTrue($deniedRes['isError']);
        $this->assertStringContainsString('permission', $deniedRes['content']);

        // Attempting to mutate Airtable-managed models (product, category, attribute) must be blocked even for admins
        $this->actingAs($this->admin);
        foreach (['product', 'category', 'attribute'] as $readOnlyModel) {
            $blockedRes = $mutateTool->execute([
                'model' => $readOnlyModel,
                'action' => 'update',
                'id' => 1,
                'attributes' => ['status' => 'draft'],
            ]);
            $this->assertTrue($blockedRes['isError']);
            $this->assertStringContainsString('not permitted', $blockedRes['content']);
        }
    }
}
