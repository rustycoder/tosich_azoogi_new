<?php

namespace Tests\Feature;

use Database\Seeders\PageSeeder;
use Database\Seeders\ProjectSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class PageTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed([
            PageSeeder::class,
            ProjectSeeder::class,
        ]);
    }

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
            'project-detail' => ['/project-detail?slug=zushi-restaurant', 'Zushi'],
            'about' => ['/about', 'About'],
            'solutions' => ['/solutions', 'Solutions'],
            'contact' => ['/contact', 'Contact'],
            'ai-lighting' => ['/ai-lighting', 'AI Lighting'],
            'calculator' => ['/led-strip-calculator', 'LED Strip Calculator'],
            'privacy' => ['/privacy', 'Privacy'],
            'terms' => ['/terms', 'Terms'],
            'warranty' => ['/warranty-returns', 'Warranty'],
            'modern-slavery' => ['/modern-slavery', 'Modern Slavery'],
            'trade-login' => ['/trade-login', 'Coming Soon'],
            'home-owner' => ['/home-owner', 'Home Owner'],
            'architect-designer' => ['/architect-designer', 'Designers'],
            'electrician-builder' => ['/electrician-builder', 'Electricians'],
            'wholesaler' => ['/wholesaler', 'Wholesaler'],
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
        $this->get('/policies.html')->assertRedirect('/privacy');
        $this->get('/audience.html')->assertRedirect('/audience');
    }

    public function test_home_links_to_dedicated_audience_pages(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertSee('/home-owner', false)
            ->assertSee('/architect-designer', false)
            ->assertSee('/electrician-builder', false)
            ->assertSee('/wholesaler', false)
            ->assertDontSee('/audience?slug=', false);
    }

    public function test_legacy_audience_urls_redirect_to_dedicated_pages(): void
    {
        $this->get('/audience')->assertRedirect('/home-owner')->assertStatus(301);
        $this->get('/audience?slug=architect-designer')->assertRedirect('/architect-designer')->assertStatus(301);
        $this->get('/audience?slug=electrician-builder')->assertRedirect('/electrician-builder')->assertStatus(301);
        $this->get('/audience?slug=wholesaler')->assertRedirect('/wholesaler')->assertStatus(301);
        $this->get('/audience?slug=unknown')->assertRedirect('/home-owner')->assertStatus(301);
    }

    public function test_policies_redirects_to_privacy(): void
    {
        $this->get('/policies')
            ->assertRedirect('/privacy')
            ->assertStatus(301);
    }

    public function test_project_detail_without_slug_is_not_found(): void
    {
        $this->get('/project-detail')->assertNotFound();
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
