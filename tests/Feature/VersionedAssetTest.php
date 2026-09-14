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

        foreach (['--font-sans', '--font-outline', '--fs-caption', '--fs-card-title', '--fs-body', '--fs-h2', '--fs-h2-section', '--fs-lead'] as $token) {
            $this->assertStringContainsString($token, $css);
        }

        $this->assertMatchesRegularExpression(
            '/html,\s*body\s*\{[^}]*font-size:\s*var\(--fs-body\)/s',
            $css,
        );
        $this->assertStringContainsString('proba-pro-regular.woff2', $css);
        $this->assertStringContainsString('google-sans-flex-latin.woff2', $css);
        $this->assertStringContainsString('-webkit-text-stroke', $css);
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

    public function test_layout_loads_local_fonts_instead_of_google_cdn(): void
    {
        $this->seed(PageSeeder::class);

        $this->assertFileExists(public_path('assets/fonts/google-sans-flex-latin.woff2'));
        $this->assertFileExists(public_path('assets/fonts/proba-pro-regular.woff2'));

        $this->get('/')
            ->assertOk()
            ->assertDontSee('fonts.googleapis.com', false)
            ->assertDontSee('Google+Sans+Flex', false)
            ->assertDontSee('Cormorant+Garamond', false)
            ->assertSee('assets/css/style_demo.css', false);
    }

    public function test_public_page_titles_use_the_shared_display_token(): void
    {
        $files = [
            'style_demo.css' => '.slide-title',
            'casambi.css' => '.cb-title',
            'silvair.css' => '.sv-title',
            'dali-centre.css' => '.dc-title',
            'led_calculator.css' => '.calc-hero-title',
        ];

        foreach ($files as $file => $selector) {
            $css = File::get(public_path('assets/css/'.$file));
            $pattern = '/'.preg_quote($selector, '/').'\s*\{[^}]*font-size:\s*var\(--fs-h2\)/s';
            $this->assertMatchesRegularExpression(
                $pattern,
                $css,
                $file.' '.$selector.' should use --fs-h2.',
            );
        }

        $productCss = File::get(public_path('assets/css/product_detail.css'));
        $this->assertMatchesRegularExpression(
            '/\.product-title-group h1\s*\{[^}]*font-size:\s*var\(--fs-h2-section\)/s',
            $productCss,
        );
        $this->assertMatchesRegularExpression(
            '/\.reset-selection a\s*\{[^}]*font-size:\s*var\(--fs-kicker\)/s',
            $productCss,
        );
        $this->assertMatchesRegularExpression(
            '/\.prod-card-title\s*\{[^}]*font-size:\s*var\(--fs-meta\)/s',
            $productCss,
        );
        $this->assertDoesNotMatchRegularExpression(
            '/\.prod-card-title\s*\{[^}]*font-size:\s*var\(--fs-kicker\)/s',
            $productCss,
        );
        $this->assertMatchesRegularExpression(
            '/\.prod-card-title \.cat-label\s*\{[^}]*font-size:\s*var\(--fs-caption\)/s',
            $productCss,
        );
    }

    public function test_public_views_do_not_use_the_removed_serif_token(): void
    {
        foreach (File::allFiles(resource_path('views')) as $file) {
            if (! str_ends_with($file->getFilename(), '.blade.php')) {
                continue;
            }

            $this->assertStringNotContainsString(
                '--font-serif',
                $file->getContents(),
                $file->getRelativePathname().' still references --font-serif.',
            );
        }
    }

    public function test_dashboard_buttons_keep_readable_labels_in_both_themes(): void
    {
        $css = File::get(public_path('assets/css/dashboard.css'));

        $this->assertMatchesRegularExpression(
            '/:root\s*\{[^}]*--dash-bg:\s*#101410/s',
            $css,
        );
        $this->assertMatchesRegularExpression(
            '/:root\[data-theme="light"\]\s*\{[^}]*--dash-bg:\s*#f3f5f1/s',
            $css,
        );
        $this->assertDoesNotMatchRegularExpression(
            '/html:has\(\.dash\)\s*\{[^}]*--pure-bg:\s*#ffffff/s',
            $css,
        );
        $this->assertMatchesRegularExpression(
            '/\.dash \.btn(?:\s*,\s*\.dash a\.btn)?\s*\{[^}]*color:\s*var\(--dash-ink\)/s',
            $css,
        );
        $this->assertMatchesRegularExpression(
            '/\.dash \.btn\.primary(?:\s*,\s*\.dash a\.btn\.primary)?\s*\{[^}]*color:\s*#fff/s',
            $css,
        );
        $this->assertMatchesRegularExpression(
            '/\.dash \.btn\.primary:hover(?:\s*,\s*\.dash a\.btn\.primary:hover)?\s*\{[^}]*color:\s*#fff/s',
            $css,
        );
    }

    public function test_dashboard_scrollbars_match_the_product_filter_chrome(): void
    {
        $css = File::get(public_path('assets/css/dashboard.css'));

        $this->assertMatchesRegularExpression(
            '/\.dash ::-webkit-scrollbar\s*\{[^}]*width:\s*4px/s',
            $css,
        );
        $this->assertMatchesRegularExpression(
            '/\.dash ::-webkit-scrollbar-thumb\s*\{[^}]*background:\s*var\(--dash-line\)/s',
            $css,
        );
        $this->assertMatchesRegularExpression(
            '/\.dash(?:\s*,\s*\.dash \*)?\s*\{[^}]*scrollbar-width:\s*thin/s',
            $css,
        );
    }

    public function test_footer_link_columns_sit_on_the_right(): void
    {
        $this->seed(PageSeeder::class);

        $this->get('/')
            ->assertOk()
            ->assertSee('class="foot-brand"', false)
            ->assertSee('class="foot-links"', false)
            ->assertSeeInOrder(['foot-brand', 'foot-links']);

        $css = File::get(public_path('assets/css/style_demo.css'));

        $this->assertMatchesRegularExpression(
            '/\.foot\s*\{[^}]*display:\s*flex/s',
            $css,
        );
        $this->assertMatchesRegularExpression(
            '/\.foot-links\s*\{[^}]*display:\s*flex/s',
            $css,
        );
        $this->assertMatchesRegularExpression(
            '/\.foot-links\s*\{[^}]*gap:\s*90px/s',
            $css,
        );
        $this->assertDoesNotMatchRegularExpression(
            '/\.foot\s*\{[^}]*grid-template-columns/s',
            $css,
        );
    }
}
