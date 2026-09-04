<?php

namespace Tests\Feature;

use Database\Seeders\PageSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class ProductPreviewTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(PageSeeder::class);
    }

    /**
     * @return array<string, array{0: string, 1: string}>
     */
    public static function partnerPageProvider(): array
    {
        return [
            'casambi' => ['/casambi', 'cb-product'],
            'silvair' => ['/silvair', 'sv-product'],
            'madrix' => ['/madrix', 'mx-product'],
            'dali-centre' => ['/dali-centre', 'dc-product'],
        ];
    }

    #[DataProvider('partnerPageProvider')]
    public function test_partner_pages_load_touch_capable_product_previews(string $uri, string $productClass): void
    {
        $mtime = filemtime(public_path('assets/js/product-preview.js'));

        $this->get($uri)
            ->assertOk()
            ->assertSee('/assets/js/product-preview.js?v='.$mtime, false)
            ->assertSee('data-product-preview="'.$productClass.'"', false)
            ->assertDontSee("matchMedia('(hover: hover) and (pointer: fine)').matches) {", false);
    }

    public function test_casambi_hardware_names_are_tappable_preview_targets(): void
    {
        $this->get('/casambi')
            ->assertOk()
            ->assertSee('class="cb-product"', false)
            ->assertSee('tabindex="0"', false)
            ->assertSee('data-preview="/assets/img/casambi/cbu-asd-lr.png"', false);
    }
}
