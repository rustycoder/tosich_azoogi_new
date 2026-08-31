<?php

namespace Tests\Feature;

use App\Models\Page;
use App\Models\PageMeta;
use App\Models\Project;
use App\Models\User;
use Database\Seeders\AdminUserSeeder;
use Database\Seeders\PageSeeder;
use Database\Seeders\ProjectSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SeederIdempotencyTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_seeder_does_not_overwrite_existing_records(): void
    {
        $this->seed(AdminUserSeeder::class);

        $admin = User::query()->where('email', 'admin@azoogi.com')->firstOrFail();
        $admin->update(['name' => 'Changed Admin']);

        $this->seed(AdminUserSeeder::class);

        $this->assertSame('Changed Admin', $admin->fresh()->name);
        $this->assertSame(1, User::query()->where('email', 'admin@azoogi.com')->count());
    }

    public function test_page_and_project_seeders_are_insert_if_missing(): void
    {
        $this->seed([PageSeeder::class, ProjectSeeder::class]);

        $pages = Page::query()->count();
        $meta = PageMeta::query()->count();
        $projects = Project::query()->count();

        $row = PageMeta::query()->where('key', 'intro.kicker')->firstOrFail();
        $row->update(['value' => 'Edited kicker']);

        $project = Project::query()->where('slug', 'zushi-restaurant')->firstOrFail();
        $project->update(['title' => 'Edited Zushi']);

        $this->seed([PageSeeder::class, ProjectSeeder::class]);

        $this->assertSame($pages, Page::query()->count());
        $this->assertSame($meta, PageMeta::query()->count());
        $this->assertSame($projects, Project::query()->count());
        $this->assertSame('Edited kicker', $row->fresh()->value);
        $this->assertSame('Edited Zushi', $project->fresh()->title);
        $this->assertTrue(Page::query()->where('slug', 'home-owner')->exists());
        $this->assertTrue(Page::query()->where('slug', 'architect-designer')->exists());
        $this->assertTrue(Page::query()->where('slug', 'electrician-builder')->exists());
        $this->assertTrue(Page::query()->where('slug', 'wholesaler')->exists());
        $this->assertFalse(Page::query()->where('slug', 'audience')->exists());
    }

    public function test_page_seeder_removes_keys_that_are_no_longer_defined(): void
    {
        $this->seed(PageSeeder::class);

        $page = Page::query()->where('slug', 'home')->firstOrFail();

        PageMeta::query()->create([
            'page_id' => $page->id,
            'key' => 'range.item.title',
            'sort_order' => 0,
            'value' => 'Orphan',
        ]);

        $this->assertTrue(
            PageMeta::query()
                ->where('page_id', $page->id)
                ->where('key', 'range.item.title')
                ->exists()
        );

        $this->seed(PageSeeder::class);

        $this->assertFalse(
            PageMeta::query()
                ->where('page_id', $page->id)
                ->where('key', 'range.item.title')
                ->exists()
        );
    }

    public function test_page_seeder_removes_pages_that_are_no_longer_defined(): void
    {
        $this->seed(PageSeeder::class);

        $orphan = Page::factory()->create([
            'slug' => 'legacy-audience',
            'title' => 'Legacy Audience',
        ]);

        PageMeta::query()->create([
            'page_id' => $orphan->id,
            'key' => 'orphan.key',
            'sort_order' => 0,
            'value' => 'Gone',
        ]);

        $this->seed(PageSeeder::class);

        $this->assertNull(Page::withTrashed()->where('slug', 'legacy-audience')->first());
        $this->assertFalse(
            PageMeta::withTrashed()->where('key', 'orphan.key')->exists()
        );
    }
}
