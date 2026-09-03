<?php

namespace Tests\Feature;

use Database\Seeders\PageSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\File;
use Tests\TestCase;

class VersionedAssetTest extends TestCase
{
    use RefreshDatabase;

    public function test_versioned_asset_appends_the_file_mtime(): void
    {
        $path = 'assets/css/solutions.css';
        $mtime = filemtime(public_path($path));

        $this->assertNotFalse($mtime);
        $this->assertSame(asset($path).'?v='.$mtime, versioned_asset($path));
    }

    public function test_views_link_stable_css_filenames(): void
    {
        foreach (File::allFiles(resource_path('views')) as $file) {
            $this->assertDoesNotMatchRegularExpression(
                '/assets\/css\/[^\'"]+\.v-\d/',
                $file->getContents(),
                $file->getRelativePathname().' still links a versioned CSS filename.',
            );
        }
    }

    public function test_solutions_page_uses_query_string_cache_busting(): void
    {
        $this->seed(PageSeeder::class);

        $mtime = filemtime(public_path('assets/css/solutions.css'));

        $this->get('/solutions')
            ->assertOk()
            ->assertSee('/assets/css/solutions.css?v='.$mtime, false)
            ->assertDontSee('solutions.v-', false);
    }
}
