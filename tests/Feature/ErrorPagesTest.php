<?php

namespace Tests\Feature;

use App\Enums\Status;
use App\Models\Page;
use App\Models\User;
use Database\Seeders\PageSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class ErrorPagesTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed([
            PageSeeder::class,
        ]);
    }

    /**
     * @return array<string, array{slug: string, navLabel: string, defaultCode: string, defaultTitle: string}>
     */
    public static function errorPageProvider(): array
    {
        return [
            '404 Not Found' => [
                'slug' => '404',
                'navLabel' => '404 Page',
                'defaultCode' => '404',
                'defaultTitle' => 'Page Not Found',
            ],
            '403 Forbidden' => [
                'slug' => '403',
                'navLabel' => '403 Page',
                'defaultCode' => '403',
                'defaultTitle' => 'Access Forbidden',
            ],
            '419 Page Expired' => [
                'slug' => '419',
                'navLabel' => '419 Page',
                'defaultCode' => '419',
                'defaultTitle' => 'Page Expired',
            ],
            '500 Server Error' => [
                'slug' => '500',
                'navLabel' => '500 Page',
                'defaultCode' => '500',
                'defaultTitle' => 'Something Went Wrong',
            ],
            '503 Maintenance' => [
                'slug' => '503',
                'navLabel' => '503 Page',
                'defaultCode' => '503',
                'defaultTitle' => 'Under Maintenance',
            ],
        ];
    }

    #[DataProvider('errorPageProvider')]
    public function test_admin_can_view_edit_and_preview_error_pages_in_dashboard(
        string $slug,
        string $navLabel,
        string $defaultCode,
        string $defaultTitle
    ): void {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)
            ->get(route('dashboard.pages.index'))
            ->assertOk()
            ->assertSee($navLabel);

        $this->actingAs($admin)
            ->get(route('dashboard.pages.edit', $slug))
            ->assertOk()
            ->assertSee($navLabel);

        $this->actingAs($admin)
            ->get(route('dashboard.pages.preview', $slug))
            ->assertOk()
            ->assertSee($defaultCode)
            ->assertSee($defaultTitle);
    }

    #[DataProvider('errorPageProvider')]
    public function test_admin_can_update_error_page_content_in_dashboard(
        string $slug,
        string $navLabel,
        string $defaultCode,
        string $defaultTitle
    ): void {
        $admin = User::factory()->admin()->create();
        $page = Page::query()->where('slug', $slug)->with('meta')->firstOrFail();
        $metaByKey = $page->meta->keyBy('key');

        $customTitle = "Custom {$slug} Title Heading";
        $customLead = "Custom {$slug} lead explanation text.";

        $this->actingAs($admin)
            ->put(route('dashboard.pages.update', $page), [
                'title' => "Custom {$slug} — Azoogi",
                'meta_description' => "Custom meta description for {$slug}.",
                'status' => Status::Active->value,
                'meta' => [
                    $metaByKey['error.title']->id => ['value' => $customTitle],
                    $metaByKey['error.lead']->id => ['value' => $customLead],
                ],
            ])
            ->assertRedirect(route('dashboard.pages.edit', $slug));

        $this->actingAs($admin)
            ->get(route('dashboard.pages.preview', $slug))
            ->assertOk()
            ->assertSee($customTitle)
            ->assertSee($customLead);
    }
}
