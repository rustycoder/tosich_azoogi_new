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
            'home intro' => ['assets/css/style_demo.css', '.intro'],
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
            '/'.preg_quote($selector, '/').'\s*\{[^}]*padding:\s*(?:clamp\([^)]+\)|[\d.]+px)\s+0\s*;/s',
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

    /**
     * @return array<string, array{0: string, 1: string, 2: string}>
     */
    public static function bandPaddingProvider(): array
    {
        return [
            'casambi' => ['assets/css/casambi.css', '.cb-band', 'clamp(9px, 1.5vw, 16px) 0'],
            'silvair' => ['assets/css/silvair.css', '.sv-band', 'clamp(9px, 1.5vw, 16px) 0'],
            'madrix' => ['assets/css/madrix.css', '.mx-band', 'clamp(9px, 1.5vw, 16px) 0'],
            'dali-centre' => ['assets/css/dali-centre.css', '.dc-band', 'clamp(9px, 1.5vw, 16px) 0'],
            'data-centre' => ['assets/css/data-centre.css', '.dc-band', '8px 0'],
            'about' => ['assets/css/about.css', '.about-band', 'clamp(12px, 1.5vw, 18px) 0'],
            'ai-lighting' => ['assets/css/ai-lighting.css', '.ai-band', 'clamp(9px, 1.25vw, 14px) 0'],
            'solutions sectors' => ['assets/css/solutions.css', '.solutions-sectors', 'clamp(13px, 1.5vw, 20px) 0'],
        ];
    }

    #[DataProvider('bandPaddingProvider')]
    public function test_band_padding_is_halved_on_top_and_bottom(string $path, string $selector, string $padding): void
    {
        $css = file_get_contents(public_path($path));

        $this->assertNotFalse($css);
        $this->assertMatchesRegularExpression(
            '/'.preg_quote($selector, '/').'\s*\{[^}]*padding:\s*'.preg_quote($padding, '/').'\s*;/s',
            $css,
        );
    }
}
