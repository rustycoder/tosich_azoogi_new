<?php

namespace Tests\Feature;

use App\Enums\Status;
use App\Models\Page;
use App\Models\PageMeta;
use App\Models\User;
use Database\Seeders\AdminUserSeeder;
use Database\Seeders\PageSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class OutlineAccentTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed([AdminUserSeeder::class, PageSeeder::class]);
    }

    /**
     * @return array<string, array{0: string, 1: list<string>}>
     */
    public static function seededAccentProvider(): array
    {
        return [
            'madrix' => ['/madrix', ['<span class="accent">Advanced LED Control Solutions</span>']],
            'casambi' => ['/casambi', ['<span class="accent">Smart Ecosystems</span>']],
            'silvair' => ['/silvair', ['<span class="accent">Qualified Mesh Lighting</span>']],
            'solutions' => ['/solutions', ['<span class="accent">Intelligent Controls</span>', '<span class="accent">Sector</span>']],
            'dali-centre' => ['/dali-centre', ['<span class="accent">Smart DALI-2 Management</span>']],
            'projects' => ['/projects', ['<span class="accent">Azoogi</span>']],
            'products' => ['/products', ['<span class="accent">Range</span>']],
            'contact' => ['/contact', ['<span class="accent">Touch</span>', '<span class="accent">hear</span>']],
            'about' => ['/about', ['<span class="accent">Zero Compromise.</span>', '<span class="accent">Azoogi</span>', '<span class="accent">Reach</span>', '<span class="accent">Path</span>']],
            'ai-lighting' => ['/ai-lighting', ['<span class="accent">for retail</span>', '<span class="accent">Four hard advantages.</span>', '<span class="accent">spectrum</span>', '<span class="accent">analysis</span>', '<span class="accent">temperature</span>', '<span class="accent">management</span>']],
            'data-centre' => ['/data-centre', ['<span class="accent">Lighting &amp; Design Services</span>', '<span class="accent">White &amp; Grey Spaces</span>', '<span class="accent">Data Hall Conditions</span>', '<span class="accent">Building Automation</span>', '<span class="accent">Emergency Lighting</span>', '<span class="accent">Across All Zones</span>', '<span class="accent">Data Centre Project?</span>']],
            'home-owner' => ['/home-owner', ['<span class="accent">Home Owner</span>']],
            'architect-designer' => ['/architect-designer', ['<span class="accent">Designers</span>']],
            'electrician-builder' => ['/electrician-builder', ['<span class="accent">Electricians and Builders</span>']],
            'wholesaler' => ['/wholesaler', ['<span class="accent">Wholesaler</span>']],
            'calculator' => ['/led-strip-calculator', ['<span class="accent">Calculator</span>']],
        ];
    }

    /**
     * @param  list<string>  $spans
     */
    #[DataProvider('seededAccentProvider')]
    public function test_seeded_outline_phrases_render_as_spans(string $uri, array $spans): void
    {
        $response = $this->get($uri)->assertOk();

        foreach ($spans as $span) {
            $response->assertSee($span, false);
        }
    }

    public function test_audience_pages_render_hero_banner(): void
    {
        foreach (['/home-owner', '/architect-designer', '/electrician-builder', '/wholesaler'] as $uri) {
            $this->get($uri)
                ->assertOk()
                ->assertSee('class="audience-hero"', false)
                ->assertSee('class="audience-hero-media"', false)
                ->assertSee('class="audience-hero-copy"', false)
                ->assertSee('class="h2 audience-hero-title"', false)
                ->assertSee('class="audience-hero-lead"', false);
        }
    }

    public function test_about_page_renders_hero_title_and_lead(): void
    {
        $this->get('/about')
            ->assertOk()
            ->assertSee('class="about-hero"', false)
            ->assertSee('class="about-hero-copy"', false)
            ->assertSee('class="about-hero-lead"', false)
            ->assertSee('We design, assemble, and optimize architectural', false);
    }

    public function test_outline_accent_is_editable_from_the_page_editor(): void
    {
        $admin = User::query()->where('email', 'admin@azoogi.com')->firstOrFail();
        $page = Page::query()->where('slug', 'madrix')->firstOrFail();
        $titleMeta = PageMeta::query()
            ->where('page_id', $page->id)
            ->where('key', 'hero.title')
            ->firstOrFail();

        $this->actingAs($admin)
            ->get(route('dashboard.pages.edit', $page))
            ->assertOk()
            ->assertDontSee('Title accent', false)
            ->assertSee('{Advanced LED Control Solutions}', false);

        $this->actingAs($admin)
            ->put(route('dashboard.pages.update', $page), [
                'title' => $page->title,
                'meta_description' => $page->meta_description,
                'status' => Status::Active->value,
                'meta' => [
                    $titleMeta->id => [
                        'value' => "MADRIX\n{Pixel Mapping}",
                    ],
                ],
            ])
            ->assertRedirect();

        $this->get('/madrix')
            ->assertOk()
            ->assertSee('<span class="accent">Pixel Mapping</span>', false)
            ->assertDontSee('<span class="accent">Advanced LED Control Solutions</span>', false);

        $this->actingAs($admin)
            ->get(route('dashboard.pages.edit', 'led-strip-calculator'))
            ->assertOk()
            ->assertDontSee('Title accent', false)
            ->assertSee('{Calculator}', false);
    }

    public function test_hero_paragraph_text_in_curly_braces_renders_as_accent_spans(): void
    {
        $admin = User::query()->where('email', 'admin@azoogi.com')->firstOrFail();
        $page = Page::query()->where('slug', 'contact')->firstOrFail();
        $leadMeta = PageMeta::query()
            ->where('page_id', $page->id)
            ->where('key', 'hero.lead')
            ->firstOrFail();

        $this->actingAs($admin)
            ->put(route('dashboard.pages.update', $page), [
                'title' => $page->title,
                'meta_description' => $page->meta_description,
                'status' => Status::Active->value,
                'meta' => [
                    $leadMeta->id => [
                        'value' => 'Have questions about our {custom lighting} solutions? We are here to help.',
                    ],
                ],
            ])
            ->assertRedirect();

        $this->get('/contact')
            ->assertOk()
            ->assertSee('<p class="contact-hero-lead">Have questions about our <span class="accent">custom lighting</span> solutions? We are here to help.</p>', false);
    }
}
