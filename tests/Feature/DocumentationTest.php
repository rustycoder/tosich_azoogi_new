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
            'media',
            'content',
            'products',
            'enquiries',
            'datasheets',
            'emails',
            'staff',
            'maintenance',
        ];

        foreach ($topics as $topic) {
            $response = $this->actingAs($user)->get("/dashboard/docs/{$topic}");
            $response->assertOk();
            $response->assertSee('Documentation & Guides', false);
            $response->assertSee('dash-doc-menu', false);
        }
    }
}
