<?php

namespace Tests\Feature;

use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class SectionSpacingTest extends TestCase
{
    /**
     * @return array<string, array{0: string, 1: string}>
     */
    public static function stackedSectionProvider(): array
    {
        return [
            'solutions eco' => ['assets/css/solutions.css', '.solutions-eco'],
            'madrix band' => ['assets/css/madrix.css', '.mx-band'],
            'casambi band' => ['assets/css/casambi.css', '.cb-band'],
            'silvair band' => ['assets/css/silvair.css', '.sv-band'],
            'dali band' => ['assets/css/dali-centre.css', '.dc-band'],
            'data-centre band' => ['assets/css/data-centre.css', '.dc-band'],
            'about band' => ['assets/css/about.css', '.about-band'],
            'ai band' => ['assets/css/ai-lighting.css', '.ai-band'],
            'home intro' => ['assets/css/style_demo.css', '.intro'],
            'home products' => ['assets/css/style_demo.css', '.products'],
            'home projects' => ['assets/css/style_demo.css', '.projects'],
            'home card-in' => ['assets/css/style_demo.css', '.card-in'],
        ];
    }

    #[DataProvider('stackedSectionProvider')]
    public function test_stacked_sections_keep_equal_top_and_bottom_padding(string $path, string $selector): void
    {
        $css = file_get_contents(public_path($path));

        $this->assertNotFalse($css);
        $this->assertDoesNotMatchRegularExpression(
            '/'.preg_quote($selector, '/').'\s*\{[^}]*padding:[^;}]*\s0\s+0\s*;/s',
            $css,
            $selector.' in '.$path.' must not drop bottom padding.',
        );
        $this->assertMatchesRegularExpression(
            '/'.preg_quote($selector, '/').'\s*\{[^}]*padding:\s*(?:var\(--(?:section-y|fs-h2-section)\)|clamp\([^)]+\)|[\d.]+px)\s+0(?:px)?\s*;/s',
            $css,
            $selector.' in '.$path.' should use equal top and bottom padding.',
        );
    }

    public function test_site_wrap_has_vertical_padding(): void
    {
        $css = file_get_contents(public_path('assets/css/style_demo.css'));

        $this->assertNotFalse($css);
        $this->assertMatchesRegularExpression(
            '/\.wrap\s*\{[^}]*padding:\s*10px\s+28px\s*;/s',
            $css,
        );
    }

    public function test_shared_spacing_tokens_are_defined(): void
    {
        $css = file_get_contents(public_path('assets/css/style_demo.css'));

        $this->assertNotFalse($css);
        $this->assertMatchesRegularExpression(
            '/:root\s*\{[^}]*--section-y:\s*clamp\(12px,\s*1\.5vw,\s*18px\)\s*;/s',
            $css,
        );
        $this->assertMatchesRegularExpression(
            '/:root\s*\{[^}]*--hero-min:\s*min\(52vh,\s*480px\)\s*;/s',
            $css,
        );
        $this->assertMatchesRegularExpression(
            '/:root\s*\{[^}]*--hero-pad-y-top:\s*180px\s*;/s',
            $css,
        );
        $this->assertMatchesRegularExpression(
            '/:root\s*\{[^}]*--hero-pad-y-bottom:\s*40px\s*;/s',
            $css,
        );
    }

    public function test_home_hero_fills_the_viewport(): void
    {
        $css = file_get_contents(public_path('assets/css/style_demo.css'));

        $this->assertNotFalse($css);
        $this->assertMatchesRegularExpression(
            '/\.hero\s*\{[^}]*height:\s*100vh/s',
            $css,
        );
        $this->assertMatchesRegularExpression(
            '/\.hero\s*\{[^}]*min-height:\s*100vh/s',
            $css,
        );
    }

    /**
     * @return array<string, array{0: string, 1: string}>
     */
    public static function bandPaddingProvider(): array
    {
        return [
            'casambi' => ['assets/css/casambi.css', '.cb-band'],
            'silvair' => ['assets/css/silvair.css', '.sv-band'],
            'madrix' => ['assets/css/madrix.css', '.mx-band'],
            'dali-centre' => ['assets/css/dali-centre.css', '.dc-band'],
            'data-centre' => ['assets/css/data-centre.css', '.dc-band'],
            'about' => ['assets/css/about.css', '.about-band'],
            'ai-lighting' => ['assets/css/ai-lighting.css', '.ai-band'],
            'solutions eco' => ['assets/css/solutions.css', '.solutions-eco'],
            'solutions sectors' => ['assets/css/solutions.css', '.solutions-sectors'],
            'home intro' => ['assets/css/style_demo.css', '.intro'],
            'calculator selector' => ['assets/css/led_calculator.css', '.calc-selector'],
        ];
    }

    #[DataProvider('bandPaddingProvider')]
    public function test_band_padding_uses_the_shared_section_token(string $path, string $selector): void
    {
        $css = file_get_contents(public_path($path));

        $this->assertNotFalse($css);
        $this->assertMatchesRegularExpression(
            '/'.preg_quote($selector, '/').'\s*\{[^}]*padding:\s*(?:var\(--(?:section-y|fs-h2-section)\)(?:\s+0(?:\s+calc\(var\(--section-y\)\s*\+\s*16px\))?)?|80px\s+0)\s*;/s',
            $css,
        );
    }

    /**
     * @return array<string, array{0: string, 1: string, 2: bool, 3?: bool}>
     */
    public static function pageHeroProvider(): array
    {
        return [
            'ai lighting' => ['assets/css/ai-lighting.css', '.ai-hero', false, true],
            'about' => ['assets/css/about.css', '.about-hero', false, true],
            'data centre' => ['assets/css/data-centre.css', '.dc-hero', false, true],
            'solutions' => ['assets/css/solutions.css', '.solutions-hero', false, true],
            'projects' => ['assets/css/projects.css', '.projects-hero', false, true],
            'products' => ['assets/css/products.css', '.prod-hero', false, true],
            'casambi' => ['assets/css/casambi.css', '.cb-hero', false, true],
            'silvair' => ['assets/css/silvair.css', '.sv-hero', false, true],
            'dali centre' => ['assets/css/dali-centre.css', '.dc-hero', false, true],
            'audience' => ['assets/css/audience.css', '.audience-hero', false, true],
            'calculator' => ['assets/css/led_calculator.css', '.calc-hero', false, true],
            'contact' => ['assets/css/style_demo.css', '.contact-hero', false, true],
        ];
    }

    #[DataProvider('pageHeroProvider')]
    public function test_page_heroes_match_ai_lighting(string $path, string $selector, bool $textHero, bool $expectsMinHeight = true): void
    {
        $css = file_get_contents(public_path($path));

        $this->assertNotFalse($css);

        if ($expectsMinHeight) {
            $this->assertMatchesRegularExpression(
                '/'.preg_quote($selector, '/').'\s*\{[^}]*min-height:\s*var\(--hero-min\)/s',
                $css,
                $selector.' in '.$path.' should use --hero-min.',
            );
        } else {
            $this->assertDoesNotMatchRegularExpression(
                '/'.preg_quote($selector, '/').'\s*\{[^}]*min-height:\s*var\(--hero-min\)/s',
                $css,
                $selector.' in '.$path.' should size to copy, not --hero-min.',
            );
        }

        if ($textHero) {
            $this->assertMatchesRegularExpression(
                '/'.preg_quote($selector, '/').'\s*\{[^}]*align-items:\s*start/s',
                $css,
                $selector.' in '.$path.' should start copy at a consistent offset under the nav.',
            );

            if ($expectsMinHeight) {
                $this->assertMatchesRegularExpression(
                    '/'.preg_quote($selector, '/').'\s*\{[^}]*padding:\s*var\(--hero-pad-y-top\).*var\(--hero-pad-y-bottom\)\s*;/s',
                    $css,
                    $selector.' in '.$path.' should use the shared hero vertical padding.',
                );
            } else {
                $this->assertMatchesRegularExpression(
                    '/'.preg_quote($selector, '/').'\s*\{[^}]*padding:\s*var\(--hero-pad-y-top\)\s+0\s+0\s*;/s',
                    $css,
                    $selector.' in '.$path.' should sit just under the nav.',
                );
            }
        }
    }

    /**
     * @return array<string, array{0: string, 1: string}>
     */
    public static function imageHeroCopyProvider(): array
    {
        return [
            'ai lighting' => ['assets/css/ai-lighting.css', '.ai-hero-copy'],
            'about' => ['assets/css/about.css', '.about-hero-copy'],
            'solutions' => ['assets/css/solutions.css', '.solutions-hero-copy'],
            'projects' => ['assets/css/projects.css', '.projects-hero-copy'],
            'products' => ['assets/css/products.css', '.prod-hero-copy'],
            'casambi' => ['assets/css/casambi.css', '.cb-hero-copy'],
            'silvair' => ['assets/css/silvair.css', '.sv-hero-copy'],
            'dali centre' => ['assets/css/dali-centre.css', '.dc-hero-copy'],
            'data centre' => ['assets/css/data-centre.css', '.dc-hero-copy'],
            'madrix' => ['assets/css/madrix.css', '.mx-hero-copy'],
            'project detail' => ['assets/css/projects.css', '.project-detail-hero-copy'],
            'calculator' => ['assets/css/led_calculator.css', '.calc-hero-copy'],
            'contact' => ['assets/css/style_demo.css', '.contact-hero-copy'],
        ];
    }

    #[DataProvider('imageHeroCopyProvider')]
    public function test_image_hero_copy_uses_shared_padding_tokens(string $path, string $selector): void
    {
        $css = file_get_contents(public_path($path));

        $this->assertNotFalse($css);
        $this->assertMatchesRegularExpression(
            '/'.preg_quote($selector, '/').'\s*\{[^}]*padding:\s*var\(--hero-pad-y-top\)\s+var\(--hero-pad-x\)\s+var\(--hero-pad-y-bottom\)\s*;/s',
            $css,
        );
    }
}
