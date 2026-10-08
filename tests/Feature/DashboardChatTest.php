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
}
