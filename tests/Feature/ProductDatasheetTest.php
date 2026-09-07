<?php

namespace Tests\Feature;

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

        $response = $this->postJson('/product-datasheet', [
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
        $this->assertStringContainsString((string) $export->uuid, $response->json('url'));

        $this->get($response->json('url'))
            ->assertOk()
            ->assertSee('GL005-BLK', false)
            ->assertSee('Garden Light', false)
            ->assertSee('White City', false)
            ->assertSee('Alex Chen', false)
            ->assertSee('SPECIFICATIONS', false)
            ->assertSee('Lighting Technical Review', false)
            ->assertSee('IP67', false)
            ->assertSee('2700K', false)
            ->assertSee('Black', false)
            ->assertSee('https://example.com/garden.jpg', false)
            ->assertSee('https://example.com/garden-dim.jpg', false)
            ->assertSee('sales@azoogi.com', false);
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
        ]);

        $this->actingAs($admin)
            ->get('/dashboard/content/products')
            ->assertOk()
            ->assertSee('Datasheet exports', false);

        $this->actingAs($admin)
            ->get('/dashboard/content/products/datasheets')
            ->assertOk()
            ->assertSee('Harbour pavilion', false)
            ->assertSee('Pat Buyer', false)
            ->assertSee('GL005', false);
    }

    public function test_staff_need_products_permission_for_datasheet_exports(): void
    {
        $staff = User::factory()->staff()->create();

        $this->actingAs($staff)
            ->get('/dashboard/content/products/datasheets')
            ->assertForbidden();
    }
}
