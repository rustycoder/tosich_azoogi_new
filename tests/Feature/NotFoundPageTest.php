<?php

namespace Tests\Feature;

use Database\Seeders\PageSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NotFoundPageTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed([
            PageSeeder::class,
        ]);
    }

    public function test_non_existent_page_renders_custom_404_view(): void
    {
        $response = $this->get('/non-existent-page-url-xyz');

        $response->assertNotFound();
        $response->assertSee('Page Not Found');
        $response->assertSee('404');
        $response->assertSee('Back to Home');
        $response->assertSee(route('products'));
        $response->assertSee(route('contact'));
    }
}
