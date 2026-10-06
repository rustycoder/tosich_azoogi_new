<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\ChatSession;
use App\Models\Product;
use Database\Seeders\PageSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
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
            'messages_count' => 2,
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

        $this->assertDatabaseCount('chat_messages', 0);
    }
}
