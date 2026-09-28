<?php

namespace Tests\Feature;

use App\Enums\Status;
use App\Models\Page;
use App\Models\User;
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

    public function test_admin_can_view_edit_and_preview_404_page_in_dashboard(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)
            ->get(route('dashboard.pages.index'))
            ->assertOk()
            ->assertSee('404 Page');

        $this->actingAs($admin)
            ->get(route('dashboard.pages.edit', '404'))
            ->assertOk()
            ->assertSee('404 Page');

        $this->actingAs($admin)
            ->get(route('dashboard.pages.preview', '404'))
            ->assertOk()
            ->assertSee('Page Not Found')
            ->assertSee('404');
    }

    public function test_admin_can_update_404_page_content_and_see_it_on_broken_routes(): void
    {
        $admin = User::factory()->admin()->create();
        $page = Page::query()->where('slug', '404')->with('meta')->firstOrFail();
        $metaByKey = $page->meta->keyBy('key');

        $this->actingAs($admin)
            ->put(route('dashboard.pages.update', $page), [
                'title' => 'Custom Missing Page — Azoogi',
                'meta_description' => 'Custom meta description for 404.',
                'status' => Status::Active->value,
                'meta' => [
                    $metaByKey['error.code']->id => ['value' => '404-LOST'],
                    $metaByKey['error.title']->id => ['value' => 'Looks Like You Are Lost'],
                    $metaByKey['error.lead']->id => ['value' => 'This custom error message was saved from the backend dashboard.'],
                    $metaByKey['cta.home.label']->id => ['value' => 'Return to Store'],
                    $metaByKey['cta.home.href']->id => ['value' => '/'],
                    $metaByKey['cta.products.label']->id => ['value' => 'View Catalog'],
                    $metaByKey['cta.products.href']->id => ['value' => '/products'],
                    $metaByKey['cta.contact.label']->id => ['value' => 'Get in Touch'],
                    $metaByKey['cta.contact.href']->id => ['value' => '/contact'],
                ],
            ])
            ->assertRedirect(route('dashboard.pages.edit', '404'));

        $response = $this->get('/broken-link-example');

        $response->assertNotFound();
        $response->assertSee('404-LOST');
        $response->assertSee('Looks Like You Are Lost');
        $response->assertSee('This custom error message was saved from the backend dashboard.');
        $response->assertSee('Return to Store');
        $response->assertSee('View Catalog');
        $response->assertSee('Get in Touch');
    }
}
