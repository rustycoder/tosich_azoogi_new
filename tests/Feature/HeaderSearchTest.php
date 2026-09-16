<?php

namespace Tests\Feature;

use Database\Seeders\PageSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HeaderSearchTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(PageSeeder::class);
    }

    public function test_header_includes_search_icon_beside_quote_list(): void
    {
        $cssMtime = filemtime(public_path('assets/css/site-search.css'));
        $jsMtime = filemtime(public_path('assets/js/site-search.js'));

        $this->get('/products')
            ->assertOk()
            ->assertSee('id="search-trigger"', false)
            ->assertSee('aria-label="Search products"', false)
            ->assertSee('search-trigger-icon', false)
            ->assertSee('id="site-search"', false)
            ->assertSee('id="site-search-q"', false)
            ->assertSee('id="site-search-clear"', false)
            ->assertSee('aria-label="Clear search"', false)
            ->assertSee('placeholder="Search products..."', false)
            ->assertSeeInOrder([
                'Trade Login',
                'id="theme-toggle"',
                '<nav class="nav">',
                'class="nav-actions"',
                'LED Calculator',
                'id="search-trigger"',
                'id="quote-trigger"',
                '</nav>',
                'id="site-search"',
                'id="site-search-q"',
            ], false)
            ->assertSee('/assets/css/site-search.css?v='.$cssMtime, false)
            ->assertSee('/assets/js/site-search.js?v='.$jsMtime, false);
    }

    public function test_search_script_opens_the_bar_and_filters_products(): void
    {
        $script = file_get_contents(public_path('assets/js/site-search.js'));

        $this->assertNotFalse($script);
        $this->assertStringContainsString("document.body.classList.add('search-open')", $script);
        $this->assertStringContainsString('input.focus()', $script);
        $this->assertStringContainsString("getElementById('search-trigger')", $script);
        $this->assertStringContainsString("getElementById('site-search-q')", $script);
        $this->assertStringContainsString("getElementById('site-search-clear')", $script);
        $this->assertStringContainsString("input.value = ''", $script);
        $this->assertStringContainsString('AZOOGI_PRODUCTS.products', $script);
        $this->assertStringContainsString('/products/', $script);
        $this->assertStringContainsString("window.addEventListener('quote:open', closeSearch)", $script);
    }
}
