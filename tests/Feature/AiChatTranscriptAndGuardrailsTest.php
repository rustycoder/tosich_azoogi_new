<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Mail\ChatTranscriptForwardedMail;
use App\Models\ChatMessage;
use App\Models\ChatSession;
use App\Services\Chat\ChatOrchestrator;
use App\Services\Chat\Tools\SendChatTranscriptToSalesTool;
use Database\Seeders\PageSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class AiChatTranscriptAndGuardrailsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(PageSeeder::class);
    }

    public function test_system_prompt_compiles_trade_specialist_persona_and_pricing_lockdown(): void
    {
        /** @var ChatOrchestrator $orchestrator */
        $orchestrator = app(ChatOrchestrator::class);
        $prompt = $orchestrator->getSystemPrompt();

        $this->assertStringContainsString('Professional Trade Specialist', $prompt);
        $this->assertStringContainsString('Strict Pricing Lockdown', $prompt);
        $this->assertStringContainsString('Azoogi Trade Portal', $prompt);
        $this->assertStringContainsString('Clarifying Protocol', $prompt);
        $this->assertStringContainsString('public_send_chat_transcript_to_sales', $prompt);
    }

    public function test_send_chat_transcript_tool_sends_email_to_sales_team(): void
    {
        Mail::fake();

        $session = ChatSession::create([
            'uuid' => '99999999-8888-7777-6666-555555555555',
            'lead_name' => 'Michael Chang',
            'lead_email' => 'michael@apexlighting.com.au',
            'project_name' => 'Crown Sydney Tower Podium',
            'ip_address' => '127.0.0.1',
            'country' => 'Australia',
        ]);

        ChatMessage::create([
            'chat_session_id' => $session->id,
            'sender' => 'user',
            'content' => 'We need 120m of continuous 24V linear profile with DALI-2 drivers for the podium ceiling.',
        ]);

        ChatMessage::create([
            'chat_session_id' => $session->id,
            'sender' => 'assistant',
            'content' => 'I recommend our architectural linear profile with CRI90+ 3000K LED tape paired with certified DALI-2 constant voltage drivers.',
        ]);

        session()->put('visitor_quote_list', [
            101 => [
                'id' => 101,
                'name' => 'Architectural Linear Extrusion 24W',
                'sku' => 'AZ-LIN-24W-DALI',
                'quantity' => 12,
            ],
        ]);

        /** @var SendChatTranscriptToSalesTool $tool */
        $tool = app(SendChatTranscriptToSalesTool::class);
        $result = $tool->execute([
            'session_uuid' => $session->uuid,
            'notes' => 'Please confirm stock and lead time for 120m custom cutting.',
        ]);

        $this->assertSame('success', $result['result']['status']);
        $this->assertSame('transcript_sent_card', $result['cards']['type']);

        Mail::assertSent(ChatTranscriptForwardedMail::class, function (ChatTranscriptForwardedMail $mail) use ($session) {
            return $mail->session->id === $session->id &&
                   $mail->notes === 'Please confirm stock and lead time for 120m custom cutting.' &&
                   count($mail->messages) === 2 &&
                   count($mail->quoteItems) === 1;
        });
    }

    public function test_visitor_can_trigger_transcript_send_via_api(): void
    {
        Mail::fake();

        $session = ChatSession::create([
            'uuid' => '11111111-2222-3333-4444-555555555555',
            'ip_address' => '127.0.0.1',
        ]);

        ChatMessage::create([
            'chat_session_id' => $session->id,
            'sender' => 'user',
            'content' => 'Looking for IP67 exterior step lights.',
        ]);

        $response = $this->postJson('/api/chat/transcript/send', [
            'session_uuid' => $session->uuid,
            'name' => 'Sarah Connor',
            'email' => 'sarah@designpractice.com.au',
            'company' => 'Nexus Design Studio',
            'project_name' => 'Harbour Penthouse',
            'notes' => 'Please prepare a fixture schedule with IES photometric files.',
        ]);

        $response->assertOk()
            ->assertJson([
                'status' => 'success',
            ])
            ->assertJsonStructure([
                'status',
                'message',
                'cards',
            ]);

        $session->refresh();
        $this->assertSame('Sarah Connor', $session->lead_name);
        $this->assertSame('sarah@designpractice.com.au', $session->lead_email);
        $this->assertSame('Nexus Design Studio', $session->lead_company);
        $this->assertSame('Harbour Penthouse', $session->project_name);

        Mail::assertSent(ChatTranscriptForwardedMail::class);
    }
}
