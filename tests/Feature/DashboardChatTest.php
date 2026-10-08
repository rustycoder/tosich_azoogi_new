<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\ChatSession;
use App\Models\User;
use Database\Seeders\AdminUserSeeder;
use Database\Seeders\PageSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardChatTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed([AdminUserSeeder::class, PageSeeder::class]);
        $this->admin = User::where('email', 'admin@azoogi.com')->firstOrFail();
    }

    public function test_guests_cannot_access_chat_sessions_dashboard(): void
    {
        $this->get('/dashboard/chat-sessions')->assertRedirect('/login');
    }

    public function test_admin_can_view_chat_sessions_index(): void
    {
        $session = ChatSession::create([
            'uuid' => 'c3d4e5f6-a7b8-9012-cdef-123456789012',
            'ip_address' => '203.0.113.195',
            'country' => 'Australia',
            'lead_name' => 'Sarah Connor',
            'lead_email' => 'sarah@skynet.com',
            'summary' => 'Inquiry for highbay fixtures',
            'status' => 'completed',
        ]);

        $response = $this->actingAs($this->admin)->get('/dashboard/chat-sessions');

        $response->assertOk()
            ->assertSee('AI Chat Logs')
            ->assertSee('Sarah Connor')
            ->assertSee('sarah@skynet.com')
            ->assertSee('203.0.113.195');
    }

    public function test_admin_can_view_conversation_transcript_details(): void
    {
        $session = ChatSession::create([
            'uuid' => 'd4e5f6a7-b8c9-0123-defa-234567890123',
            'ip_address' => '203.0.113.196',
            'country' => 'Australia',
            'status' => 'active',
        ]);

        $session->messages()->create([
            'sender' => 'user',
            'content' => 'What is the beam angle on your spotlights?',
        ]);

        $session->messages()->create([
            'sender' => 'assistant',
            'content' => 'Our spotlights feature interchangeable optical lenses ranging from 15° to 60° beam angles.',
        ]);

        $response = $this->actingAs($this->admin)->get("/dashboard/chat-sessions/{$session->id}");

        $response->assertOk()
            ->assertSee('Conversation Transcript')
            ->assertSee('What is the beam angle on your spotlights?')
            ->assertSee('Our spotlights feature interchangeable optical lenses');
    }

    public function test_admin_can_convert_chat_session_to_official_enquiry(): void
    {
        $session = ChatSession::create([
            'uuid' => 'e5f6a7b8-c9d0-1234-efab-345678901234',
            'ip_address' => '203.0.113.197',
            'lead_name' => 'Michael Scott',
            'lead_email' => 'michael@dundermifflin.com',
            'status' => 'active',
        ]);

        $session->messages()->create([
            'sender' => 'user',
            'content' => 'Please quote 50 units for our office renovation.',
        ]);

        $response = $this->actingAs($this->admin)->post("/dashboard/chat-sessions/{$session->id}/convert-enquiry");

        $response->assertRedirect();
        $this->assertDatabaseHas('enquiries', [
            'email' => 'michael@dundermifflin.com',
            'name' => 'Michael Scott',
        ]);

        $session->refresh();
        $this->assertNotNull($session->enquiry_id);
        $this->assertSame('completed', $session->status);
    }

    public function test_admin_can_toggle_chat_session_read_status(): void
    {
        $session = ChatSession::create([
            'uuid' => 'f6a7b8c9-d0e1-2345-fabc-456789012345',
            'ip_address' => '203.0.113.198',
            'status' => 'active',
            'is_read' => false,
        ]);

        $response = $this->actingAs($this->admin)->patchJson("/dashboard/chat-sessions/{$session->id}/toggle-read");

        $response->assertOk()
            ->assertJson([
                'success' => true,
                'is_read' => true,
            ]);

        $this->assertTrue($session->fresh()->is_read);

        // Toggle back to unread
        $response2 = $this->actingAs($this->admin)->patchJson("/dashboard/chat-sessions/{$session->id}/toggle-read");

        $response2->assertOk()
            ->assertJson([
                'success' => true,
                'is_read' => false,
            ]);

        $this->assertFalse($session->fresh()->is_read);
    }

    public function test_admin_can_toggle_chat_session_favorite_status(): void
    {
        $session = ChatSession::create([
            'uuid' => 'a7b8c9d0-e1f2-3456-abcd-567890123456',
            'ip_address' => '203.0.113.199',
            'status' => 'active',
            'is_favorite' => false,
        ]);

        $response = $this->actingAs($this->admin)->patchJson("/dashboard/chat-sessions/{$session->id}/toggle-favorite");

        $response->assertOk()
            ->assertJson([
                'success' => true,
                'is_favorite' => true,
            ]);

        $this->assertTrue($session->fresh()->is_favorite);

        // Toggle back to unfavorite
        $response2 = $this->actingAs($this->admin)->patchJson("/dashboard/chat-sessions/{$session->id}/toggle-favorite");

        $response2->assertOk()
            ->assertJson([
                'success' => true,
                'is_favorite' => false,
            ]);

        $this->assertFalse($session->fresh()->is_favorite);
    }

    public function test_admin_can_filter_chat_sessions_by_read_status_and_favorites(): void
    {
        ChatSession::create([
            'uuid' => 'b8c9d0e1-f2a3-4567-bcde-678901234567',
            'lead_name' => 'Unread Lead',
            'status' => 'active',
            'is_read' => false,
            'is_favorite' => false,
        ]);

        ChatSession::create([
            'uuid' => 'c9d0e1f2-a3b4-5678-cdef-789012345678',
            'lead_name' => 'Starred Lead',
            'status' => 'active',
            'is_read' => true,
            'is_favorite' => true,
        ]);

        // Filter unread
        $responseUnread = $this->actingAs($this->admin)->get('/dashboard/chat-sessions?read_status=unread');
        $responseUnread->assertOk()
            ->assertSee('Unread Lead')
            ->assertDontSee('Starred Lead');

        // Filter favorites
        $responseFav = $this->actingAs($this->admin)->get('/dashboard/chat-sessions?favorite_only=1');
        $responseFav->assertOk()
            ->assertSee('Starred Lead')
            ->assertDontSee('Unread Lead');
    }
}
