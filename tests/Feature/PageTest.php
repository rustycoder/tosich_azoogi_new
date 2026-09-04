<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\ProductCategory;
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
            'casambi' => ['/casambi', 'Casambi'],
            'silvair' => ['/silvair', 'Silvair'],
            'dali-centre' => ['/dali-centre', 'DALI Centre'],
            'madrix' => ['/madrix', 'MADRIX'],
            'contact' => ['/contact', 'Contact'],
            'ai-lighting' => ['/ai-lighting', 'AI Lighting'],
            'calculator' => ['/led-strip-calculator', 'LED Strip Calculator'],
            'privacy' => ['/privacy', 'Privacy'],
            'terms' => ['/terms', 'Terms'],
            'warranty' => ['/warranty-returns', 'Warranty'],
            'modern-slavery' => ['/modern-slavery', 'Modern Slavery Statement'],
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

    public function test_legacy_static_site_urls_are_gone(): void
    {
        $this->get('/products.html')->assertNotFound();
        $this->get('/index.html')->assertNotFound();
        $this->get('/trade_login.html')->assertNotFound();
        $this->get('/policies.html')->assertNotFound();
        $this->get('/audience.html')->assertNotFound();
        $this->get('/jr-neon')->assertNotFound();
        $this->get('/test-configuration')->assertNotFound();
        $this->get('/jr-neon.html')->assertNotFound();
        $this->get('/test-configuration.html')->assertNotFound();
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

    public function test_legacy_audience_and_policies_urls_are_gone(): void
    {
        $this->get('/audience')->assertNotFound();
        $this->get('/audience?slug=architect-designer')->assertNotFound();
        $this->get('/policies')->assertNotFound();
    }

    public function test_project_detail_without_slug_is_not_found(): void
    {
        $this->get('/project-detail')->assertNotFound();
    }

    public function test_about_intro_cta_links_to_contact(): void
    {
        $this->get('/about')
            ->assertOk()
            ->assertSee('Request Capability Statement', false)
            ->assertSee('href="'.url('/contact').'"', false);
    }

    public function test_header_and_footer_are_not_public_pages(): void
    {
        $this->get('/header')->assertNotFound();
        $this->get('/footer')->assertNotFound();
    }

    public function test_wholesaler_page_does_not_include_the_last_off_spec_card(): void
    {
        $this->get('/wholesaler')
            ->assertOk()
            ->assertSee('Solutions That Sell', false)
            ->assertDontSee('Off-Spec Solutions That Win the Job', false);
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

    public function test_home_page_renders_parent_categories_in_products_marquee(): void
    {
        ProductCategory::query()->create(['airtable_id' => 'recNeon', 'name' => 'NEON', 'sort_order' => 1]);
        ProductCategory::query()->create(['airtable_id' => 'recProfiles', 'name' => 'Profiles', 'sort_order' => 2]);
        Product::factory()->create([
            'product_name' => 'Neon Flex',
            'category' => 'NEON',
            'categories' => ['NEON'],
            'category_path' => ['NEON'],
        ]);
        Product::factory()->create([
            'product_name' => 'Trimless Profile',
            'category' => 'Profiles',
            'categories' => ['Profiles'],
            'category_path' => ['Profiles'],
        ]);

        $this->get('/')
            ->assertOk()
            ->assertSee('section class="products"', false)
            ->assertSee('<h4>NEON</h4>', false)
            ->assertSee('<h4>Profiles</h4>', false)
            ->assertSee('/products?category=NEON', false);
    }
}
