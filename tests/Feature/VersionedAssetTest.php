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

    public function test_site_stylesheet_defines_shared_type_tokens(): void
    {
        $css = File::get(public_path('assets/css/style_demo.css'));

        foreach (['--font-sans', '--fs-caption', '--fs-card-title', '--fs-h2', '--fs-h2-section', '--fs-lead'] as $token) {
            $this->assertStringContainsString($token, $css);
        }
    }

    public function test_public_stylesheets_do_not_use_legacy_serif_headings(): void
    {
        foreach (File::files(public_path('assets/css')) as $file) {
            if ($file->getExtension() !== 'css' || $file->getFilename() === 'dashboard.css') {
                continue;
            }

            $this->assertStringNotContainsString(
                'Cormorant Garamond',
                $file->getContents(),
                $file->getFilename().' still uses Cormorant Garamond.',
            );
        }
    }

    public function test_layout_loads_the_shared_sans_font(): void
    {
        $this->seed(PageSeeder::class);

        $this->get('/')
            ->assertOk()
            ->assertSee('Google+Sans+Flex', false)
            ->assertDontSee('Cormorant+Garamond', false);
    }
}
