<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\ChatMessage;
use App\Models\ChatSession;
use App\Models\User;
use App\Services\Chat\ChatOrchestrator;
use Database\Seeders\PageSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ChatTokenAndCostTrackingTest extends TestCase
{
    use RefreshDatabase;

    protected User $adminUser;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(PageSeeder::class);
        config(['services.chat.driver' => 'mock']);

        $this->adminUser = User::factory()->admin()->create();
    }

    public function test_chat_orchestrator_tracks_tokens_and_calculates_cost_on_messages_and_sessions(): void
    {
        $orchestrator = app(ChatOrchestrator::class);
        $session = $orchestrator->getOrCreateSession();

        $result = $orchestrator->handleUserMessage(
            session: $session,
            userText: 'Can I get downlights for a residential kitchen?'
        );

        $this->assertNotEmpty($result['session_uuid']);
        $this->assertNotEmpty($result['response']);

        $session->refresh();
        $this->assertGreaterThan(0, $session->total_tokens);
        $this->assertGreaterThanOrEqual(0, (float) $session->total_cost);
        $this->assertNotEmpty($session->primary_model);

        $assistantMessage = ChatMessage::where('chat_session_id', $session->id)
            ->where('sender', 'assistant')
            ->firstOrFail();

        $this->assertGreaterThan(0, $assistantMessage->tokens_used);
        $this->assertGreaterThan(0, $assistantMessage->prompt_tokens);
        $this->assertGreaterThan(0, $assistantMessage->completion_tokens);
        $this->assertGreaterThanOrEqual(0, (float) $assistantMessage->estimated_cost);
        $this->assertNotEmpty($assistantMessage->model);
        $this->assertNotEmpty($assistantMessage->driver);
    }

    public function test_dashboard_chat_logs_index_displays_token_and_cost_metrics(): void
    {
        $session = ChatSession::create([
            'uuid' => 'test-session-uuid-12345',
            'ip_address' => '127.0.0.1',
            'country' => 'Australia',
            'status' => 'active',
            'messages_count' => 2,
            'total_tokens' => 1500,
            'total_cost' => 0.007500,
            'primary_model' => 'claude-3-5-sonnet-20241022',
        ]);

        $session->messages()->create([
            'sender' => 'user',
            'content' => 'Hello AI',
        ]);

        $session->messages()->create([
            'sender' => 'assistant',
            'content' => 'Hello! How can I assist with your architectural lighting project?',
            'tokens_used' => 1500,
            'prompt_tokens' => 1000,
            'completion_tokens' => 500,
            'model' => 'claude-3-5-sonnet-20241022',
            'driver' => 'anthropic',
            'estimated_cost' => 0.007500,
        ]);

        $response = $this->actingAs($this->adminUser)->get(route('dashboard.chat-sessions.index'));

        $response->assertOk();
        $response->assertSee('Total Tokens Consumed');
        $response->assertSee('Total Estimated Spend');
        $response->assertSee('1,500');
        $response->assertSee('$0.0075');
    }

    public function test_dashboard_chat_session_show_displays_token_breakdown_and_cost_audit(): void
    {
        $session = ChatSession::create([
            'uuid' => 'test-audit-uuid-67890',
            'ip_address' => '127.0.0.1',
            'country' => 'Australia',
            'status' => 'active',
            'messages_count' => 2,
            'total_tokens' => 2200,
            'total_cost' => 0.012500,
            'primary_model' => 'claude-3-5-sonnet-20241022',
        ]);

        $session->messages()->create([
            'sender' => 'user',
            'content' => 'Show me garden lights',
        ]);

        $session->messages()->create([
            'sender' => 'assistant',
            'content' => 'Here are garden fixtures.',
            'tokens_used' => 2200,
            'prompt_tokens' => 1800,
            'completion_tokens' => 400,
            'model' => 'claude-3-5-sonnet-20241022',
            'driver' => 'anthropic',
            'estimated_cost' => 0.012500,
        ]);

        $response = $this->actingAs($this->adminUser)->get(route('dashboard.chat-sessions.show', $session));

        $response->assertOk();
        $response->assertSee('Token &amp; Compute Cost Audit', false);
        $response->assertSee('1,800');
        $response->assertSee('400');
        $response->assertSee('$0.012500');
    }
}
