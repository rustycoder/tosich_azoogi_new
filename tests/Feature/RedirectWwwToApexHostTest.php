<?php

namespace Tests\Feature;

use Database\Seeders\PageSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RedirectWwwToApexHostTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config(['app.url' => 'https://azoogi.com']);
        $this->seed(PageSeeder::class);
    }

    public function test_www_requests_permanently_redirect_to_the_apex_host_keeping_path_and_query(): void
    {
        $this->get('https://www.azoogi.com/products?category=NEON')
            ->assertStatus(301)
            ->assertRedirect('https://azoogi.com/products?category=NEON');
    }

    public function test_apex_host_requests_are_served_without_redirect(): void
    {
        $this->get('https://azoogi.com/about')->assertOk();
    }
}
