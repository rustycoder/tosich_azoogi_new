<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Services\Contracts\ILedCalculatorService;
use Database\Seeders\PageSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LedCalculatorTest extends TestCase
{
    use RefreshDatabase;

    public function test_calculator_page_embeds_live_catalogue_products(): void
    {
        $this->seed(PageSeeder::class);

        Product::factory()->create([
            'airtable_id' => 'recCob',
            'product_name' => 'COB Strip',
            'product_code' => 'COB001',
            'category' => 'COB',
            'status' => 'publish',
            'categories' => ['COB'],
            'category_path' => ['COB'],
            'product_images' => ['https://example.com/cob.jpg'],
            'product_features' => [
                'IP Rating' => [['value' => 'IP20'], ['value' => 'IP65 (Nano-Coated)']],
                'Voltage' => [['value' => '24V']],
                'Power Consumption Rate' => [['value' => '10W/m']],
                'Strip Width' => [['value' => '8mm (W)']],
                'Color Temperature' => [['value' => '2700K'], ['value' => '3000K']],
            ],
        ]);

        $this->get('/led-strip-calculator')
            ->assertOk()
            ->assertSee('LED Strip Calculator', false)
            ->assertSee('AZOOGI_LED_CALC', false)
            ->assertSee('COB001', false)
            ->assertSee('https://example.com/cob.jpg', false)
            ->assertDontSee('COB019', false);
    }

    public function test_catalog_classifies_strips_neon_and_drivers(): void
    {
        Product::factory()->create([
            'airtable_id' => 'recCob',
            'product_name' => 'COB Strip',
            'product_code' => 'COB001',
            'category' => 'COB',
            'status' => 'publish',
            'product_features' => [
                'IP Rating' => [['value' => 'IP20'], ['value' => 'IP65 (Nano-Coated)']],
                'Voltage' => [['value' => '24V']],
                'Power Consumption Rate' => [['value' => '10W/m'], ['value' => '15W/m']],
                'Strip Width' => [['value' => '8mm (W)']],
                'Color Temperature' => [['value' => '2700K'], ['value' => 'RGBW (3000K)']],
                'Light Color' => [['value' => 'RGB']],
            ],
        ]);
        Product::factory()->create([
            'airtable_id' => 'recNeon',
            'product_name' => 'Neon Side View',
            'product_code' => 'SV1617',
            'category' => 'Side View',
            'status' => 'publish',
            'product_features' => [
                'IP Rating' => [['value' => 'IP67']],
                'Voltage' => [['value' => '24V']],
                'Power Consumption Rate' => [['value' => '10W/m']],
                'Dimension' => [['value' => '16mm (W) x 17mm (H)']],
                'Color Temperature' => [['value' => '2700K']],
            ],
        ]);
        Product::factory()->create([
            'airtable_id' => 'recDriver',
            'product_name' => 'Non-Dimmable Driver',
            'product_code' => 'ADR001',
            'category' => 'Non-Dimmable Driver',
            'status' => 'publish',
            'product_features' => [
                'IP Rating' => [['value' => 'IP67']],
                'Voltage' => [['value' => '12V'], ['value' => '24V']],
                'Power' => [['value' => '60W'], ['value' => '100W']],
            ],
        ]);
        Product::factory()->create([
            'airtable_id' => 'recDraft',
            'product_name' => 'Hidden Draft Strip',
            'product_code' => 'DRAFT1',
            'category' => 'SMD',
            'status' => 'draft',
            'product_features' => [
                'IP Rating' => [['value' => 'IP20']],
            ],
        ]);

        $catalog = app(ILedCalculatorService::class)->catalog();
        $lights = collect($catalog['lights']);
        $drivers = collect($catalog['drivers']);

        $cob = $lights->firstWhere('sku', 'COB001');
        $neon = $lights->firstWhere('sku', 'SV1617');
        $driver = $drivers->firstWhere('sku', 'ADR001');

        $this->assertNotNull($cob);
        $this->assertSame('strip', $cob['family']);
        $this->assertSame('cob', $cob['chip']);
        $this->assertTrue($cob['has_single']);
        $this->assertTrue($cob['has_multi']);
        $this->assertContains('IP20', array_column($cob['ips'], 'code'));
        $this->assertTrue(collect($cob['ips'])->contains(fn (array $ip): bool => $ip['code'] === 'IP65' && $ip['nano'] === true));
        $this->assertSame(['10W/m', '15W/m'], $cob['powers']);
        $this->assertContains('8mm', $cob['widths']);

        $this->assertNotNull($neon);
        $this->assertSame('neon', $neon['family']);
        $this->assertSame('neon-side', $neon['neon_type']);
        $this->assertContains('16x17mm', $neon['widths']);

        $this->assertNotNull($driver);
        $this->assertSame('non-dimmable', $driver['type']);
        $this->assertSame([60, 100], $driver['watts']);
        $this->assertFalse($lights->contains(fn (array $light): bool => $light['sku'] === 'DRAFT1'));
    }
}
