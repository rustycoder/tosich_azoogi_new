<?php

namespace Tests\Feature;

use Database\Seeders\PageSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DataCentrePageTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(PageSeeder::class);
    }

    public function test_why_section_puts_four_cards_after_the_heading(): void
    {
        $html = $this->get('/data-centre')->assertOk()->getContent();

        $heading = strpos($html, 'Complete Certainty for Your');
        $body = strpos($html, 'Lighting in mission-critical facilities');
        $cards = strpos($html, 'class="dc-caps"');
        $firstCard = strpos($html, 'Audit-Ready Compliance');

        $this->assertNotFalse($heading);
        $this->assertNotFalse($body);
        $this->assertNotFalse($cards);
        $this->assertNotFalse($firstCard);
        $this->assertLessThan($body, $heading);
        $this->assertLessThan($cards, $body);
        $this->assertLessThan($firstCard, $cards);
        $this->assertStringContainsString('class="dc-section-head reveal"', $html);
        $this->assertStringNotContainsString('class="wrap dc-split"', $html);
        $this->assertStringContainsString('3D Photometric Engineering', $html);
        $this->assertStringContainsString('Cable &amp; Asset Protection', $html);
        $this->assertStringContainsString('Rapid Deployment &amp; Circular', $html);
    }

    public function test_why_cards_sit_in_a_four_column_row(): void
    {
        $css = file_get_contents(public_path('assets/css/data-centre.css'));

        $this->assertNotFalse($css);
        $this->assertDoesNotMatchRegularExpression(
            '/\.dc-split\s*\{[^}]*grid-template-columns:\s*1fr\s+1fr/s',
            $css,
        );
        $this->assertMatchesRegularExpression(
            '/\.dc-caps\s*\{[^}]*grid-template-columns:\s*repeat\(4,\s*minmax\(0,\s*1fr\)\)/s',
            $css,
        );
        $this->assertMatchesRegularExpression(
            '/\.dc-section-head p\s*\{[^}]*width:\s*100%/s',
            $css,
        );
        $this->assertMatchesRegularExpression(
            '/\.dc-section-head p\s*\{[^}]*max-width:\s*none/s',
            $css,
        );
        $this->assertDoesNotMatchRegularExpression(
            '/\.dc-section-head p\s*\{[^}]*max-width:\s*70ch/s',
            $css,
        );
    }

    public function test_control_section_sits_on_a_grey_band(): void
    {
        $html = $this->get('/data-centre')->assertOk()->getContent();

        $this->assertMatchesRegularExpression(
            '/class="dc-band dc-band--feature">\s*<div class="wrap dc-feature"[\s\S]*?Data Hall Conditions[\s\S]*?class="dc-band dc-band--alt dc-band--feature">\s*<div class="wrap dc-feature dc-feature--flip"[\s\S]*?Building Automation/',
            $html,
        );
        $this->assertStringContainsString('<span>Data Hall Conditions</span>', $html);
        $this->assertStringContainsString('<span>Building Automation</span>', $html);
        $this->assertStringContainsString('datacenter1.webp', $html);
        $this->assertStringContainsString('datacenter2.webp', $html);

        $hardware = strpos($html, 'Data Hall Conditions');
        $control = strpos($html, 'Building Automation');
        $emergency = strpos($html, 'Fail-Safe');
        $this->assertNotFalse($hardware);
        $this->assertNotFalse($control);
        $this->assertNotFalse($emergency);
        $this->assertLessThan($control, $hardware);
        $this->assertLessThan($emergency, $control);

        $controlBlock = substr($html, strpos($html, 'class="wrap dc-feature dc-feature--flip"'), $emergency - strpos($html, 'class="wrap dc-feature dc-feature--flip"'));
        $this->assertMatchesRegularExpression(
            '/dc-feature--flip.*?Building Automation.*?dc-feature-img.*?datacenter2\.webp/s',
            $controlBlock,
        );
        $this->assertStringNotContainsString('Data Hall Conditions', $controlBlock);
    }

    public function test_emergency_is_white_and_zones_is_grey_with_tick_cards(): void
    {
        $html = $this->get('/data-centre')->assertOk()->getContent();

        $this->assertStringNotContainsString('class="wrap dc-pair"', $html);
        $this->assertStringNotContainsString('dc-grid-item', $html);
        $this->assertStringNotContainsString('dc-zone-list', $html);

        $emergency = strpos($html, 'Fail-Safe');
        $zones = strpos($html, 'Across All Zones');
        $cta = strpos($html, 'class="dc-cta reveal"');
        $this->assertNotFalse($emergency);
        $this->assertNotFalse($zones);
        $this->assertNotFalse($cta);
        $this->assertLessThan($zones, $emergency);
        $this->assertLessThan($cta, $zones);

        $emergencyOpen = substr($html, (int) strrpos(substr($html, 0, $emergency), '<section'), 90);
        $this->assertStringContainsString('dc-band--feature', $emergencyOpen);
        $this->assertStringNotContainsString('dc-band--alt', $emergencyOpen);

        $zonesOpen = substr($html, (int) strrpos(substr($html, 0, $zones), '<section'), 90);
        $this->assertStringContainsString('dc-band--alt', $zonesOpen);
        $this->assertStringContainsString('dc-band--feature', $zonesOpen);

        $emergencyBlock = substr($html, $emergency, $zones - $emergency);
        $this->assertStringContainsString('class="dc-ticks"', $emergencyBlock);
        $this->assertStringContainsString('datacenter1.webp', $emergencyBlock);
        $this->assertStringContainsString('<strong>Centralized Emergency Power (CBS):</strong>', $emergencyBlock);

        $zonesBlock = substr($html, $zones, $cta - $zones);
        $this->assertStringContainsString('class="dc-ticks"', $zonesBlock);
        $this->assertStringContainsString('datacenter2.webp', $zonesBlock);
        $this->assertStringContainsString('<strong>Data Halls &amp; Server Rooms:</strong>', $zonesBlock);
    }

    public function test_cta_sits_on_the_page_background(): void
    {
        $css = file_get_contents(public_path('assets/css/data-centre.css'));

        $this->assertNotFalse($css);
        $this->assertMatchesRegularExpression(
            '/\.dc-cta\s*\{[^}]*background:\s*var\(--bg\)/s',
            $css,
        );
        $this->assertDoesNotMatchRegularExpression(
            '/\.dc-cta\s*\{[^}]*background:\s*var\(--card-bg\)/s',
            $css,
        );
        $this->assertMatchesRegularExpression(
            '/\.dc-band--alt\s*\{[^}]*background:\s*var\(--card-bg\)/s',
            $css,
        );
        $this->assertMatchesRegularExpression(
            '/\.dc-feature--flip \.dc-feature-img\s*\{[^}]*order:\s*1/s',
            $css,
        );
        $this->assertMatchesRegularExpression(
            '/\.dc-band--feature\s*\{[^}]*padding:\s*calc\(var\(--section-y\)\s*\+\s*16px\)\s+0/s',
            $css,
        );
        $this->assertMatchesRegularExpression(
            '/\.dc-feature\s*\{[^}]*align-items:\s*center/s',
            $css,
        );
        $this->assertMatchesRegularExpression(
            '/\.dc-feature-img\s*\{[^}]*align-items:\s*center/s',
            $css,
        );
        $this->assertMatchesRegularExpression(
            '/\.dc-feature-img img\s*\{[^}]*object-position:\s*center/s',
            $css,
        );
    }
}
