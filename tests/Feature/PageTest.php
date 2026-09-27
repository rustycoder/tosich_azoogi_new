<?php

namespace Tests\Feature;

use App\Models\Page;
use App\Models\PageMeta;
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
            'quote-request' => ['/request-a-quote', 'Request a Quote'],
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

    public function test_home_range_section_shows_remote_product_images(): void
    {
        $remoteUrl = 'https://v5.airtableusercontent.com/v3/full/hero.jpg';

        ProductCategory::query()->create([
            'airtable_id' => 'recNeon',
            'name' => 'NEON',
            'sort_order' => 1,
        ]);
        Product::factory()->create([
            'product_name' => 'Neon Flex',
            'category' => 'NEON',
            'status' => 'publish',
            'categories' => ['NEON'],
            'category_path' => ['NEON'],
            'product_images' => [$remoteUrl],
        ]);

        $this->get('/')
            ->assertOk()
            ->assertSee('Explore the full Azoogi lighting catalogue.', false)
            ->assertSee('src="'.$remoteUrl.'"', false)
            ->assertDontSee('/https://v5.airtableusercontent.com', false);
    }

    public function test_home_range_cards_show_the_full_stored_category_description(): void
    {
        $featuredUrl = 'https://v5.airtableusercontent.com/v3/full/category-hero.jpg';
        $description = 'Seamless flexible linear lighting for interior and exterior architectural contours, including wet areas and long facade runs.';

        ProductCategory::query()->create([
            'airtable_id' => 'recNeon',
            'name' => 'NEON',
            'sort_order' => 1,
            'description' => $description,
            'featured_image' => $featuredUrl,
        ]);
        Product::factory()->create([
            'product_name' => 'Neon Flex',
            'category' => 'NEON',
            'status' => 'publish',
            'categories' => ['NEON'],
            'category_path' => ['NEON'],
            'product_images' => ['https://v5.airtableusercontent.com/v3/full/product.jpg'],
        ]);

        $css = file_get_contents(public_path('assets/css/style_demo.css'));

        $this->assertNotFalse($css);
        $this->assertDoesNotMatchRegularExpression(
            '/\.marquee \.card p\s*\{[^}]*line-clamp/s',
            $css,
        );

        $this->get('/')
            ->assertOk()
            ->assertSee($description, false)
            ->assertSee('src="'.$featuredUrl.'"', false)
            ->assertDontSee('src="https://v5.airtableusercontent.com/v3/full/product.jpg"', false);
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

    public function test_product_detail_configured_specification_is_built_without_padded_lines(): void
    {
        $this->get('/product-detail')
            ->assertOk()
            ->assertSee('id="quote-spec"', false)
            ->assertSee('wrap="off"', false)
            ->assertSee(".join('\\n')", false)
            ->assertDontSee('                                                                                Variant Model:', false);
    }

    public function test_about_intro_cta_links_to_contact(): void
    {
        $this->get('/about')
            ->assertOk()
            ->assertSee('Request Capability Statement', false)
            ->assertSee('href="'.url('/contact').'"', false);
    }

    public function test_about_path_heading_matches_why_choose_and_sits_above_three_boxes(): void
    {
        $this->get('/about')
            ->assertOk()
            ->assertSee('class="about-split-copy about-path-head', false)
            ->assertSee('Select Your <span class="accent">Path</span>', false)
            ->assertSee('Why Choose <span class="accent">Azoogi</span>', false)
            ->assertSee('For Architects &amp; Specifiers', false)
            ->assertSee('For Builders &amp; Contractors', false)
            ->assertSee('For Electrical Wholesalers', false)
            ->assertSee('class="more">Learn more', false)
            ->assertSee('class="about-path-row', false)
            ->assertDontSee('<div class="img">', false);

        $css = file_get_contents(public_path('assets/css/about.css'));

        $this->assertNotFalse($css);
        $this->assertMatchesRegularExpression(
            '/\.about-path-head\s*\{[^}]*text-align:\s*left/s',
            $css,
        );
        $this->assertMatchesRegularExpression(
            '/\.about-split-copy h2 span\s*\{[^}]*color:\s*var\(--accent\)/s',
            $css,
        );
        $this->assertMatchesRegularExpression(
            '/\.about-path-list\s*\{[^}]*grid-template-columns:\s*repeat\(3,\s*minmax\(0,\s*1fr\)\)/s',
            $css,
        );
        $this->assertDoesNotMatchRegularExpression(
            '/\.about-path-head\s*\{[^}]*text-align:\s*center/s',
            $css,
        );
        $this->assertMatchesRegularExpression(
            '/\.about-path-row h4\s*\{[^}]*font-size:\s*var\(--fs-meta\)/s',
            $css,
        );
        $this->assertDoesNotMatchRegularExpression(
            '/\.about-path-row \.img\s*\{/s',
            $css,
        );
    }

    public function test_about_hero_image_is_anchored_to_the_bottom(): void
    {
        $css = file_get_contents(public_path('assets/css/about.css'));

        $this->assertNotFalse($css);
        $this->assertMatchesRegularExpression(
            '/\.about-hero-media img\s*\{[^}]*object-position:\s*(?:(?:right\s+)?bottom|bottom\s+center)/s',
            $css,
        );
        $this->assertDoesNotMatchRegularExpression(
            '/\.about-hero-media img\s*\{[^}]*object-position:\s*top\s+center/s',
            $css,
        );
    }

    public function test_header_and_footer_are_not_public_pages(): void
    {
        $this->get('/header')->assertNotFound();
        $this->get('/footer')->assertNotFound();
    }

    public function test_footer_company_column_does_not_repeat_legal_links(): void
    {
        $html = $this->get('/')->assertOk()->getContent();

        $this->assertDoesNotMatchRegularExpression(
            '/<h5>Company<\/h5>.*?>Legal<\/a>.*?<h5>Contact<\/h5>/s',
            $html,
        );
        $this->assertDoesNotMatchRegularExpression(
            '/<h5>Company<\/h5>.*?>Privacy<\/a>.*?<h5>Contact<\/h5>/s',
            $html,
        );
        $this->assertMatchesRegularExpression(
            '/class="copy-links".*?>Privacy<\/a>.*?>Terms<\/a>.*?>Warranty<\/a>.*?>Modern Slavery Statement<\/a>/s',
            $html,
        );
    }

    public function test_audience_hero_uses_standard_hero_banner(): void
    {
        $css = file_get_contents(public_path('assets/css/audience.css'));

        $this->assertNotFalse($css);
        $this->assertMatchesRegularExpression(
            '/\.audience-hero\s*\{[^}]*min-height:\s*var\(--hero-max\)/s',
            $css,
        );
        $this->assertMatchesRegularExpression(
            '/\.audience-hero-media\s*\{[^}]*position:\s*absolute/s',
            $css,
        );
        $this->assertMatchesRegularExpression(
            '/\.audience-hero-copy\s*\{[^}]*padding:\s*var\(--hero-pad-y-top\)/s',
            $css,
        );
        $this->assertMatchesRegularExpression(
            '/\.audience-page #cards\s*\{[^}]*padding-top:\s*0/s',
            $css,
        );
        $this->assertMatchesRegularExpression(
            '/\.audience-page\s*\{[^}]*--card-height:\s*min\(72svh,\s*600px\)/s',
            $css,
        );
        $this->assertDoesNotMatchRegularExpression(
            '/\.audience-page\s*\{[^}]*--card-height:\s*min\(48svh,\s*400px\)/s',
            $css,
        );
        $this->assertMatchesRegularExpression(
            '/\.audience-cards \.card__content\s*\{[^}]*max-height:\s*none/s',
            $css,
        );

        $this->get('/electrician-builder')
            ->assertOk()
            ->assertSee('Electricians', false)
            ->assertSee('class="audience-hero-lead"', false);

        $this->get('/wholesaler')
            ->assertOk()
            ->assertSee('Wholesaler', false)
            ->assertSee('class="audience-hero-lead"', false);
    }

    public function test_wholesaler_page_does_not_include_the_last_off_spec_card(): void
    {
        $this->get('/wholesaler')
            ->assertOk()
            ->assertSee('Solutions That Sell', false)
            ->assertDontSee('Off-Spec Solutions That Win the Job', false);
    }

    public function test_contact_info_panel_labels_use_shared_body_type(): void
    {
        $this->get('/contact')
            ->assertOk()
            ->assertSee('class="contact-info-panel"', false)
            ->assertSee('class="info-label"', false);

        $css = file_get_contents(public_path('assets/css/style_demo.css'));

        $this->assertNotFalse($css);
        $this->assertMatchesRegularExpression(
            '/\.contact-info-panel \.info-label\s*\{[^}]*font-size:\s*var\(--fs-body\)/s',
            $css,
        );
        $this->assertMatchesRegularExpression(
            '/\.contact-title\s*\{[^}]*font-size:\s*var\(--fs-h2-section\)/s',
            $css,
        );
        $this->assertDoesNotMatchRegularExpression(
            '/\.contact-title\s*\{[^}]*font-size:\s*var\(--fs-h2\)\s*;/s',
            $css,
        );
        $this->assertDoesNotMatchRegularExpression(
            '/\.contact-title\s*\{[^}]*white-space:\s*nowrap/s',
            $css,
        );
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

        $this->get('/contact')
            ->assertOk()
            ->assertSee('id="site-toasts"', false)
            ->assertSee('data-flash="Thanks', false)
            ->assertDontSee('form-status is-success', false);

        $this->assertDatabaseHas('enquiries', [
            'type' => 'contact',
            'status' => 'pending',
            'email' => 'jane@example.com',
            'company' => 'Example Lighting',
        ]);
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

    public function test_hero_banner_renders_video_when_configured(): void
    {
        $pages = [
            'about' => '/about',
            'casambi' => '/casambi',
            'silvair' => '/silvair',
            'solutions' => '/solutions',
            'dali-centre' => '/dali-centre',
            'data-centre' => '/data-centre',
            'contact' => '/contact',
            'ai-lighting' => '/ai-lighting',
            'led-strip-calculator' => '/led-strip-calculator',
            'products' => '/products',
            'projects' => '/projects',
            'architect-designer' => '/architect-designer',
        ];

        foreach ($pages as $slug => $uri) {
            $page = Page::query()->where('slug', $slug)->firstOrFail();

            PageMeta::query()->updateOrCreate(
                ['page_id' => $page->id, 'key' => 'hero.video', 'sort_order' => 0],
                ['value' => 'assets/video/sample.mp4']
            );
            PageMeta::query()->updateOrCreate(
                ['page_id' => $page->id, 'key' => 'hero.poster', 'sort_order' => 0],
                ['value' => 'assets/img/sample-poster.jpg']
            );

            $this->get($uri)
                ->assertOk()
                ->assertSee('assets/video/sample.mp4', false)
                ->assertSee('assets/img/sample-poster.jpg', false)
                ->assertSee('<video', false);
        }
    }
}
