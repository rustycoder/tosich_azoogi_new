<?php

namespace Tests\Feature;

use App\Enums\ContentResource;
use App\Models\ContentPermission;
use App\Models\Product;
use App\Models\ProductDatasheetExport;
use App\Models\User;
use Database\Seeders\PageSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductDatasheetTest extends TestCase
{
    use RefreshDatabase;

    public function test_custom_datasheet_prompt_is_on_the_product_page(): void
    {
        $this->seed(PageSeeder::class);

        $this->get('/product-detail')
            ->assertOk()
            ->assertSee('Download Custom Datasheet', false)
            ->assertSee('id="download-custom-datasheet"', false)
            ->assertSee('Select configuration options first', false)
            ->assertSee('datasheetBtn.disabled = !hasConfiguration', false)
            ->assertSee('id="datasheet-export-dialog"', false)
            ->assertSee('id="datasheet-export-close"', false)
            ->assertSee('Project name', false)
            ->assertSee('Client name', false)
            ->assertSee(route('products.datasheet.store'), false);
    }

    public function test_datasheet_export_requires_project_and_client_names(): void
    {
        Product::factory()->create([
            'airtable_id' => 'recGarden',
            'status' => 'publish',
        ]);

        $this->postJson('/product-datasheet', [
            'product_id' => 'recGarden',
        ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['project_name', 'person_name']);

        $this->assertDatabaseCount('product_datasheet_exports', 0);
    }

    public function test_datasheet_export_is_saved_and_sheet_shows_project_client_and_specs(): void
    {
        Product::factory()->create([
            'airtable_id' => 'recGarden',
            'product_name' => 'Garden Light',
            'product_code' => 'GL005',
            'category' => 'Garden Light',
            'status' => 'publish',
            'product_short_description' => 'Transform outdoor spaces.',
            'product_images' => ['https://example.com/garden.jpg'],
            'product_dimension' => ['https://example.com/garden-dim.jpg'],
            'product_features' => [
                'IP Rating' => [['value' => 'IP67']],
                'Wattage' => [['value' => '10W']],
                'Color Temperature' => [['value' => '3000K']],
                'Warranty' => [['value' => '5 years']],
            ],
        ]);

        $response = $this->withHeaders([
            'CF-Connecting-IP' => '203.0.113.10',
            'CF-IPCountry' => 'AU',
        ])->postJson('/product-datasheet', [
            'product_id' => 'recGarden',
            'project_name' => 'White City',
            'person_name' => 'Alex Chen',
            'product_code' => 'GL005-BLK',
            'selected_options' => [
                'Finish' => 'Black',
                'Color Temperature' => '2700K',
            ],
        ]);

        $response->assertOk()->assertJsonStructure(['url']);

        $export = ProductDatasheetExport::query()->first();
        $this->assertNotNull($export);
        $this->assertSame('recGarden', $export->airtable_id);
        $this->assertSame('White City', $export->project_name);
        $this->assertSame('Alex Chen', $export->person_name);
        $this->assertSame('GL005-BLK', $export->product_code);
        $this->assertSame('203.0.113.10', $export->ip_address);
        $this->assertSame('AU', $export->country);
        $this->assertStringContainsString((string) $export->uuid, $response->json('url'));

        $this->get($response->json('url'))
            ->assertOk()
            ->assertSee('GL005-BLK', false)
            ->assertSee('White City', false)
            ->assertDontSee('Alex Chen', false)
            ->assertSee('Checked By: <span></span>', false)
            ->assertSee('Date: <span></span>', false)
            ->assertSee('SPECIFICATIONS', false)
            ->assertSee('Lighting Technical Review', false)
            ->assertSee('Matched to Specifcation', false)
            ->assertSee('This technical review indicates general conformity', false)
            ->assertSee('IP67', false)
            ->assertSee('2700K', false)
            ->assertSee('Black', false)
            ->assertSee('https://example.com/garden.jpg', false)
            ->assertSee('https://example.com/garden-dim.jpg', false)
            ->assertSee('sales@azoogi.com', false)
            ->assertSee('family=Open+Sans', false)
            ->assertSee('assets/img/datasheet-logo.png', false)
            ->assertSee('assets/logo_dark.png', false)
            ->assertSee('assets/img/lighting-council.png', false)
            ->assertSee('Unit 47, 10-12 Girawah Place, Matraville, NSW, 2036', false)
            ->assertSee('class="ds-print"', false)
            ->assertSee('aria-label="Print / Save PDF"', false)
            ->assertDontSee('class="ds-toolbar"', false);
    }

    public function test_unpublished_products_cannot_export_a_datasheet(): void
    {
        Product::factory()->create([
            'airtable_id' => 'recDraft',
            'status' => 'draft',
        ]);

        $this->postJson('/product-datasheet', [
            'product_id' => 'recDraft',
            'project_name' => 'White City',
            'person_name' => 'Alex Chen',
        ])->assertNotFound();

        $this->postJson('/product-datasheet', [
            'product_id' => 'recMissing',
            'project_name' => 'White City',
            'person_name' => 'Alex Chen',
        ])->assertNotFound();
    }

    public function test_admin_can_browse_datasheet_exports(): void
    {
        $admin = User::factory()->admin()->create();
        ProductDatasheetExport::factory()->create([
            'project_name' => 'Harbour pavilion',
            'person_name' => 'Pat Buyer',
            'product_code' => 'GL005',
            'ip_address' => '203.0.113.10',
            'country' => 'AU',
            'user_agent' => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
        ]);

        $this->actingAs($admin)
            ->get('/dashboard')
            ->assertOk()
            ->assertSeeInOrder(['>Datasheet</div>', 'Exports'], false)
            ->assertDontSee('<h2>Datasheet</h2>', false)
            ->assertDontSee('Review generated datasheet exports.', false)
            ->assertDontSee('Datasheet exports', false);

        $this->actingAs($admin)
            ->get('/dashboard/content/products')
            ->assertOk()
            ->assertDontSee('>Datasheet exports</a>', false);

        $this->actingAs($admin)
            ->get('/dashboard/datasheets/exports')
            ->assertOk()
            ->assertSee('dash-row-link-text">GL005</span>', false)
            ->assertSee('dash-list-sub">Garden Light</p>', false)
            ->assertSee('class="dash-list-thumb"', false)
            ->assertSee('src="/assets/img/neon.webp"', false)
            ->assertSee('dash-list-card-meta is-end', false)
            ->assertSee('Exported', false)
            ->assertSee('<strong>Pat Buyer</strong>', false)
            ->assertSee('data-export-info', false)
            ->assertSee('data-export-dialog', false)
            ->assertSee('data-export-title="GL005"', false)
            ->assertSee('data-export-sub="Garden Light"', false)
            ->assertSee('dash-enquiry-dialog-sub', false)
            ->assertSee('>Client name</dt>', false)
            ->assertSee('>Project name</dt>', false)
            ->assertSee('>Country</dt>', false)
            ->assertSee('Australia', false)
            ->assertSee('>IP</dt>', false)
            ->assertSee('203.0.113.10', false)
            ->assertSee('>Device</dt>', false)
            ->assertSee('Chrome on macOS', false)
            ->assertDontSee('Origin', false)
            ->assertDontSee('Last updated', false)
            ->assertDontSee('dash-pill is-slug', false);
    }

    public function test_export_list_thumb_follows_the_live_product_image(): void
    {
        $admin = User::factory()->admin()->create();
        $product = Product::factory()->create([
            'cover' => '',
            'product_images' => ['https://example.com/synced.jpg'],
        ]);
        ProductDatasheetExport::factory()->create([
            'product_id' => $product->id,
            'configuration' => [
                'product_image' => '/assets/img/neon.webp',
            ],
        ]);

        $this->actingAs($admin)
            ->get('/dashboard/datasheets/exports')
            ->assertOk()
            ->assertSee('src="https://example.com/synced.jpg"', false)
            ->assertDontSee('src="/assets/img/neon.webp"', false);
    }

    public function test_datasheet_sheet_follows_live_product_images(): void
    {
        $product = Product::factory()->create([
            'cover' => '',
            'product_images' => ['https://example.com/synced.jpg'],
            'product_dimension' => ['https://example.com/synced-dim.jpg'],
        ]);
        $export = ProductDatasheetExport::factory()->create([
            'product_id' => $product->id,
            'configuration' => [
                'product_image' => '/assets/img/neon.webp',
                'dimension_image' => '/assets/img/old-dim.webp',
                'specifications' => [],
            ],
        ]);

        $this->get(route('products.datasheet.show', $export))
            ->assertOk()
            ->assertSee('https://example.com/synced.jpg', false)
            ->assertSee('https://example.com/synced-dim.jpg', false)
            ->assertDontSee('/assets/img/neon.webp', false)
            ->assertDontSee('/assets/img/old-dim.webp', false);
    }

    public function test_staff_need_datasheet_permission_for_exports(): void
    {
        $staff = User::factory()->staff()->create();

        $this->actingAs($staff)
            ->get('/dashboard/datasheets/exports')
            ->assertForbidden();

        ContentPermission::query()->create([
            'user_id' => $staff->id,
            'resource' => ContentResource::Products,
        ]);

        $this->actingAs($staff->fresh())
            ->get('/dashboard/datasheets/exports')
            ->assertForbidden();

        ContentPermission::query()->create([
            'user_id' => $staff->id,
            'resource' => ContentResource::Datasheet,
        ]);

        $this->actingAs($staff->fresh())
            ->get('/dashboard')
            ->assertOk()
            ->assertSeeInOrder(['>Datasheet</div>', 'Exports'], false);
    }

    public function test_datasheet_maps_dimming_control_option_to_dimming_spec(): void
    {
        $product = Product::factory()->create([
            'airtable_id' => 'recNeon01',
            'product_name' => 'Mini Neon Side View',
            'product_code' => 'SV1010-10W-3K-IP67',
            'category' => 'Side View',
            'status' => 'publish',
            'product_features' => [
                'Wattage' => [['value' => '10W']],
                'Voltage' => [['value' => '24V']],
            ],
        ]);

        $response = $this->postJson('/product-datasheet', [
            'product_id' => 'recNeon01',
            'project_name' => 'Harbour Penthouse',
            'person_name' => 'Sarah Connor',
            'product_code' => 'SV1010-10W-3K-IP67',
            'selected_options' => [
                'CCT' => '3000K',
                'Dimming Control' => 'DALI-2',
            ],
        ]);

        $response->assertOk();

        $this->get($response->json('url'))
            ->assertOk()
            ->assertSee('<td>Dimming</td>', false)
            ->assertSee('<td>DALI-2</td>', false);
    }
}
