<?php

namespace Tests\Feature;

use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class PageTest extends TestCase
{
    /**
     * @return array<string, array{0: string, 1: string}>
     */
    public static function pageProvider(): array
    {
        return [
            'home' => ['/', 'Azoogi'],
            'products' => ['/products', 'Products'],
            'product-detail' => ['/product-detail', 'Azoogi'],
            'projects' => ['/projects', 'Projects'],
            'project-detail' => ['/project-detail', 'Azoogi'],
            'about' => ['/about', 'About'],
            'solutions' => ['/solutions', 'Solutions'],
            'contact' => ['/contact', 'Contact'],
            'ai-lighting' => ['/ai-lighting', 'AI Lighting'],
            'calculator' => ['/led-strip-calculator', 'LED Strip Calculator'],
            'policies' => ['/policies', 'Policies'],
            'trade-login' => ['/trade-login', 'Coming Soon'],
            'audience' => ['/audience', 'Azoogi'],
            'data-centre' => ['/data-centre', 'Data Centre'],
        ];
    }

    #[DataProvider('pageProvider')]
    public function test_pages_render(string $uri, string $expected): void
    {
        $this->get($uri)
            ->assertOk()
            ->assertSee($expected, false);
    }

    public function test_legacy_html_urls_redirect(): void
    {
        $this->get('/products.html')->assertRedirect('/products');
        $this->get('/index.html')->assertRedirect('/');
        $this->get('/trade_login.html')->assertRedirect('/trade-login');
    }

    public function test_contact_form_validates_required_fields(): void
    {
        $this->from('/contact')
            ->post('/contact', [])
            ->assertRedirect('/contact')
            ->assertSessionHasErrors(['your-name', 'your-email', 'your-company', 'your-message']);
    }

    public function test_contact_form_accepts_a_valid_submission(): void
    {
        $this->from('/contact')
            ->post('/contact', [
                'your-name' => 'Jane Example',
                'your-email' => 'jane@example.com',
                'your-company' => 'Example Lighting',
                'your-message' => 'We need a quote for a hospitality fit-out.',
            ])
            ->assertRedirect('/contact')
            ->assertSessionHas('status');
    }
}
