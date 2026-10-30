<?php

namespace Tests\Feature;

use App\Enums\UserType;
use App\Models\DatasheetTemplate;
use App\Models\Product;
use App\Models\ProductAttribute;
use App\Models\ProductDatasheetExport;
use App\Models\User;
use App\Repositories\Contracts\IProductRepository;
use App\Services\Contracts\IProductDatasheetService;
use Database\Seeders\DatasheetTemplateSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DatasheetTemplateTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    private User $staff;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create([
            'user_type' => UserType::Admin,
        ]);

        $this->staff = User::factory()->create([
            'user_type' => UserType::Staff,
        ]);
    }

    public function test_unauthenticated_user_cannot_access_datasheet_templates(): void
    {
        $this->get(route('dashboard.datasheet-templates.index'))
            ->assertRedirect(route('login'));
    }

    public function test_staff_cannot_access_datasheet_templates(): void
    {
        $this->actingAs($this->staff)
            ->get(route('dashboard.datasheet-templates.index'))
            ->assertForbidden();
    }

    public function test_admin_can_view_datasheet_templates_index(): void
    {
        $this->seed(DatasheetTemplateSeeder::class);

        $response = $this->actingAs($this->admin)
            ->get(route('dashboard.datasheet-templates.index'));

        $response->assertOk()
            ->assertSee('Datasheet Templates')
            ->assertSee('Standard Azoogi A4')
            ->assertSee('standard-azoogi-a4')
            ->assertSee('Modern Specifier A4')
            ->assertSee('modern-specifier-a4')
            ->assertSee('Active');
    }

    public function test_admin_can_view_create_datasheet_template_page(): void
    {
        $response = $this->actingAs($this->admin)
            ->get(route('dashboard.datasheet-templates.create'));

        $response->assertOk()
            ->assertSee('New Datasheet Template')
            ->assertSee('Dynamic Placeholder Tokens')
            ->assertSee('HTML Structure')
            ->assertSee('Custom CSS')
            ->assertSee('Live A4 Preview');
    }

    public function test_admin_can_create_datasheet_template(): void
    {
        $response = $this->actingAs($this->admin)
            ->post(route('dashboard.datasheet-templates.store'), [
                'name' => 'Architectural Modern A4',
                'description' => 'Modern architectural format with dark headings.',
                'body_html' => '<html><head></head><body><h1>{{product_name}}</h1><p>{{product_code}}</p></body></html>',
                'custom_css' => 'body { font-family: sans-serif; }',
                'available_tokens' => 'product_name, product_code, specifications_table',
                'is_active' => '1',
            ]);

        $template = DatasheetTemplate::query()->where('name', 'Architectural Modern A4')->first();
        $this->assertNotNull($template);
        $this->assertSame('architectural-modern-a4', $template->slug);
        $this->assertTrue($template->is_active);
        $this->assertEquals(['product_name', 'product_code', 'specifications_table'], $template->available_tokens);

        $response->assertRedirect(route('dashboard.datasheet-templates.edit', $template))
            ->assertSessionHas('status', 'Datasheet template created successfully.');
    }

    public function test_admin_can_view_edit_datasheet_template_page(): void
    {
        $template = DatasheetTemplate::create([
            'name' => 'Landscape Spec Sheet',
            'slug' => 'landscape-spec-sheet',
            'description' => 'Landscape orientation layout',
            'body_html' => '<html><body><h1>{{product_name}}</h1></body></html>',
            'custom_css' => 'body { color: #333; }',
            'available_tokens' => ['product_name', 'product_code'],
            'is_active' => false,
        ]);

        $response = $this->actingAs($this->admin)
            ->get(route('dashboard.datasheet-templates.edit', $template));

        $response->assertOk()
            ->assertSee('Edit Landscape Spec Sheet')
            ->assertSee('landscape-spec-sheet')
            ->assertSee('Landscape orientation layout');
    }

    public function test_admin_can_update_datasheet_template(): void
    {
        $template = DatasheetTemplate::create([
            'name' => 'Original Name',
            'slug' => 'original-slug',
            'description' => 'Original description',
            'body_html' => '<h1>Old</h1>',
            'custom_css' => 'body { color: black; }',
            'available_tokens' => ['product_name'],
            'is_active' => false,
        ]);

        $response = $this->actingAs($this->admin)
            ->put(route('dashboard.datasheet-templates.update', $template), [
                'name' => 'Updated Name',
                'slug' => 'updated-slug',
                'description' => 'Updated description',
                'body_html' => '<h1>New {{product_name}}</h1>',
                'custom_css' => 'body { color: green; }',
                'available_tokens' => 'product_name, category, specifications_table',
            ]);

        $response->assertRedirect(route('dashboard.datasheet-templates.edit', $template))
            ->assertSessionHas('status', 'Datasheet template updated successfully.');

        $template->refresh();
        $this->assertSame('Updated Name', $template->name);
        $this->assertSame('updated-slug', $template->slug);
        $this->assertSame('Updated description', $template->description);
        $this->assertSame('<h1>New {{product_name}}</h1>', $template->body_html);
        $this->assertSame('body { color: green; }', $template->custom_css);
        $this->assertEquals(['product_name', 'category', 'specifications_table'], $template->available_tokens);
    }

    public function test_admin_can_update_datasheet_template_keeping_same_slug(): void
    {
        $template = DatasheetTemplate::create([
            'name' => 'Original Name',
            'slug' => 'original-slug',
            'description' => 'Original description',
            'body_html' => '<h1>Old</h1>',
            'custom_css' => 'body { color: black; }',
            'available_tokens' => ['product_name'],
            'is_active' => false,
        ]);

        $response = $this->actingAs($this->admin)
            ->put(route('dashboard.datasheet-templates.update', $template), [
                'name' => 'Original Name Renamed',
                'slug' => 'original-slug',
                'description' => 'Updated description without slug change',
                'body_html' => '<h1>New Content</h1>',
                'custom_css' => 'body { color: blue; }',
            ]);

        $response->assertRedirect(route('dashboard.datasheet-templates.edit', $template))
            ->assertSessionHas('status', 'Datasheet template updated successfully.');

        $this->assertSame('original-slug', $template->fresh()->slug);
    }

    public function test_admin_can_activate_template(): void
    {
        $template1 = DatasheetTemplate::create([
            'name' => 'Template 1',
            'slug' => 'template-1',
            'body_html' => '<h1>1</h1>',
            'is_active' => true,
        ]);

        $template2 = DatasheetTemplate::create([
            'name' => 'Template 2',
            'slug' => 'template-2',
            'body_html' => '<h1>2</h1>',
            'is_active' => false,
        ]);

        $response = $this->actingAs($this->admin)
            ->patch(route('dashboard.datasheet-templates.activate', $template2));

        $response->assertRedirect();

        $this->assertFalse($template1->fresh()->is_active);
        $this->assertTrue($template2->fresh()->is_active);

        $indexResponse = $this->actingAs($this->admin)->get(route('dashboard.datasheet-templates.index'));
        $indexResponse->assertOk()
            ->assertSee('Active: Template 2')
            ->assertSee('Active');

        $editResponse = $this->actingAs($this->admin)->get(route('dashboard.datasheet-templates.edit', $template2));
        $editResponse->assertOk()
            ->assertSee('Active Live Template');
    }

    public function test_active_template_renders_on_public_datasheet_generation(): void
    {
        // Create product
        Product::factory()->create([
            'airtable_id' => 'recCustomTest',
            'product_name' => 'Custom Pendant Light',
            'product_code' => 'CPL-900',
            'category' => 'Pendant',
            'status' => 'publish',
            'product_description' => 'Architectural suspended pendant.',
            'product_images' => ['https://example.com/pendant.jpg'],
        ]);

        // Create and activate custom template
        $customTemplate = DatasheetTemplate::create([
            'name' => 'Vibrant Neon Brand Template',
            'slug' => 'vibrant-neon-brand',
            'body_html' => '<div class="custom-sheet-test"><h1>{{product_name}}</h1><span class="custom-brand-marker">AZOOGI-CUSTOM-TEST-OK</span><p>{{project_name}}</p></div>',
            'custom_css' => '.custom-sheet-test { background: yellow; }',
            'is_active' => true,
        ]);

        // Create export record using factory
        $export = ProductDatasheetExport::factory()->create([
            'airtable_id' => 'recCustomTest',
            'project_name' => 'Crown Sydney Tower',
            'person_name' => 'Jessica Alba',
            'product_code' => 'CPL-900',
            'product_name' => 'Custom Pendant Light',
        ]);

        $response = $this->get(route('products.datasheet.show', $export));

        $response->assertOk()
            ->assertSee('AZOOGI-CUSTOM-TEST-OK')
            ->assertSee('Custom Pendant Light')
            ->assertSee('Crown Sydney Tower')
            ->assertSee('.custom-sheet-test { background: yellow; }', false);
    }

    public function test_admin_can_duplicate_datasheet_template(): void
    {
        $original = DatasheetTemplate::create([
            'name' => 'Corporate Standard',
            'slug' => 'corporate-standard',
            'body_html' => '<div>Corporate</div>',
            'custom_css' => 'div { color: navy; }',
            'available_tokens' => ['product_name', 'email'],
            'is_active' => true,
        ]);

        $response = $this->actingAs($this->admin)
            ->post(route('dashboard.datasheet-templates.duplicate', $original));

        $copy = DatasheetTemplate::query()->where('name', 'Corporate Standard (Copy)')->first();
        $this->assertNotNull($copy);
        $this->assertFalse($copy->is_active);
        $this->assertSame($original->body_html, $copy->body_html);
        $this->assertSame($original->custom_css, $copy->custom_css);
        $this->assertEquals($original->available_tokens, $copy->available_tokens);

        $response->assertRedirect(route('dashboard.datasheet-templates.edit', $copy));
    }

    public function test_admin_cannot_delete_active_template(): void
    {
        $active = DatasheetTemplate::create([
            'name' => 'Active Template',
            'slug' => 'active-template',
            'body_html' => '<h1>Active</h1>',
            'is_active' => true,
        ]);

        $response = $this->actingAs($this->admin)
            ->delete(route('dashboard.datasheet-templates.destroy', $active));

        $response->assertRedirect()
            ->assertSessionHasErrors(['error']);

        $this->assertDatabaseHas('datasheet_templates', ['id' => $active->id]);
    }

    public function test_admin_can_delete_inactive_template(): void
    {
        $inactive = DatasheetTemplate::create([
            'name' => 'Draft Template',
            'slug' => 'draft-template',
            'body_html' => '<h1>Draft</h1>',
            'is_active' => false,
        ]);

        $response = $this->actingAs($this->admin)
            ->delete(route('dashboard.datasheet-templates.destroy', $inactive));

        $response->assertRedirect(route('dashboard.datasheet-templates.index'))
            ->assertSessionHas('status');

        $this->assertSoftDeleted('datasheet_templates', ['id' => $inactive->id]);
    }

    public function test_admin_can_reset_template_to_default(): void
    {
        $template = DatasheetTemplate::create([
            'name' => 'Modified Template',
            'slug' => 'modified-template',
            'body_html' => '<p>Ruined HTML</p>',
            'custom_css' => 'p { color: red; }',
            'is_active' => false,
        ]);

        $response = $this->actingAs($this->admin)
            ->post(route('dashboard.datasheet-templates.reset', $template));

        $response->assertRedirect(route('dashboard.datasheet-templates.edit', $template))
            ->assertSessionHas('status');

        $template->refresh();
        $this->assertStringContainsString('Open+Sans', $template->body_html);
        $this->assertSame(DatasheetTemplate::defaultCss(), $template->custom_css);
        $this->assertEquals(DatasheetTemplate::defaultTokens(), $template->available_tokens);
    }

    public function test_admin_can_request_live_preview(): void
    {
        $response = $this->actingAs($this->admin)
            ->postJson(route('dashboard.datasheet-templates.preview'), [
                'body_html' => '<div class="preview-box"><h2>{{product_name}}</h2><span>{{product_code}}</span></div>',
                'custom_css' => '.preview-box { padding: 10px; }',
            ]);

        $response->assertOk()
            ->assertJsonStructure(['rendered']);

        $rendered = $response->json('rendered');
        $this->assertStringContainsString('Mini Neon Side View', $rendered);
        $this->assertStringContainsString('SV1010-10W-3K-IP67', $rendered);
        $this->assertStringContainsString('.preview-box { padding: 10px; }', $rendered);
    }

    public function test_template_css_is_stored_in_database_and_isolated_between_templates(): void
    {
        $templateA = DatasheetTemplate::create([
            'name' => 'Template A',
            'slug' => 'template-a',
            'body_html' => '<head></head><body><h1>{{product_name}}</h1></body>',
            'custom_css' => '.page { background: #111111; color: #ffffff; }',
            'is_active' => false,
        ]);

        $templateB = DatasheetTemplate::create([
            'name' => 'Template B',
            'slug' => 'template-b',
            'body_html' => '<head></head><body><h1>{{product_name}}</h1></body>',
            'custom_css' => '.page { background: #f0f0f0; color: #000000; }',
            'is_active' => false,
        ]);

        $renderedA = $templateA->render(['product_name' => 'Luminaire A']);
        $renderedB = $templateB->render(['product_name' => 'Luminaire B']);

        $this->assertStringContainsString('<style>', $renderedA);
        $this->assertStringContainsString('.page { background: #111111; color: #ffffff; }', $renderedA);
        $this->assertStringNotContainsString('.page { background: #f0f0f0; color: #000000; }', $renderedA);

        $this->assertStringContainsString('<style>', $renderedB);
        $this->assertStringContainsString('.page { background: #f0f0f0; color: #000000; }', $renderedB);
        $this->assertStringNotContainsString('.page { background: #111111; color: #ffffff; }', $renderedB);
    }

    public function test_datasheet_renders_technical_specification_icons_next_to_order_code(): void
    {
        $this->seed(DatasheetTemplateSeeder::class);

        /** @var DatasheetTemplate $template */
        $template = DatasheetTemplate::query()->where('slug', 'modern-specifier-a4')->firstOrFail();
        $this->assertContains('technical_icons', $template->available_tokens);

        // Case 1: Product with technical_icons images
        $productWithIcons = Product::factory()->create([
            'product_name' => 'Iconic Linear Light',
            'product_code' => 'ILL-001',
            'technical_icons' => [
                ['url' => 'https://example.com/icons/ip67.png'],
                ['url' => 'https://example.com/icons/5yr.svg'],
            ],
        ]);

        $exportWithIcons = ProductDatasheetExport::factory()->create([
            'product_id' => $productWithIcons->id,
            'product_name' => 'Iconic Linear Light',
            'product_code' => 'ILL-001',
        ]);

        /** @var IProductDatasheetService $service */
        $service = app(IProductDatasheetService::class);
        $renderedWithIcons = $service->renderTemplate($template, $exportWithIcons);

        $this->assertStringContainsString('ORDER CODE', $renderedWithIcons);
        $this->assertStringContainsString('ILL-001', $renderedWithIcons);
        $this->assertStringContainsString('class="spec-icon-img"', $renderedWithIcons);
        $this->assertStringContainsString('https://example.com/icons/ip67.png', $renderedWithIcons);
        $this->assertStringContainsString('https://example.com/icons/5yr.svg', $renderedWithIcons);

        // Case 2: Product without image icons falls back to spec badges (e.g. 5YR, IP65)
        $productWithoutIcons = Product::factory()->create([
            'product_name' => 'Standard Linear Light',
            'product_code' => 'SLL-002',
            'technical_icons' => null,
            'product_features' => [
                'Warranty' => ['5 Years'],
                'IP Rating' => ['IP65'],
            ],
        ]);

        $exportWithoutIcons = ProductDatasheetExport::factory()->create([
            'product_id' => $productWithoutIcons->id,
            'product_name' => 'Standard Linear Light',
            'product_code' => 'SLL-002',
            'configuration' => [
                'specifications' => [
                    ['label' => 'Warranty', 'value' => '5 Years'],
                    ['label' => 'IP Rating', 'value' => 'IP65'],
                ],
            ],
        ]);

        $renderedWithoutIcons = $service->renderTemplate($template, $exportWithoutIcons);
        $this->assertStringContainsString('ORDER CODE', $renderedWithoutIcons);
        $this->assertStringContainsString('SLL-002', $renderedWithoutIcons);
        $this->assertStringContainsString('<span class="feature-badge">5YR</span>', $renderedWithoutIcons);
        $this->assertStringContainsString('<span class="feature-badge">IP65</span>', $renderedWithoutIcons);
    }

    public function test_product_attributes_save_attribute_group(): void
    {
        /** @var IProductRepository $repo */
        $repo = app(IProductRepository::class);

        $attributes = [
            [
                'id' => 'recOptic1',
                'fields' => [
                    'Attribute name' => 'Optics',
                    'Term Name' => '36° Beam',
                    'Attribute Group' => 'Light',
                ],
            ],
            [
                'id' => 'recVolt1',
                'fields' => [
                    'Attribute name' => 'Voltage',
                    'Term Name' => '24V DC',
                    'Attribute Group' => 'Electrical & Control',
                ],
            ],
            [
                'id' => 'recMat1',
                'fields' => [
                    'Attribute name' => 'Housing',
                    'Term Name' => 'Aluminium',
                    'Attribute Group' => 'Physical',
                ],
            ],
            [
                'id' => 'recIp1',
                'fields' => [
                    'Attribute name' => 'IP Rating',
                    'Term Name' => 'IP68',
                    'Attribute Group' => 'Compliance & Life',
                ],
            ],
        ];

        $repo->persistLookups([], $attributes);

        $opticAttr = ProductAttribute::where('airtable_id', 'recOptic1')->first();
        $this->assertNotNull($opticAttr);
        $this->assertSame('Light', $opticAttr->attribute_group);

        $voltAttr = ProductAttribute::where('airtable_id', 'recVolt1')->first();
        $this->assertNotNull($voltAttr);
        $this->assertSame('Electrical & Control', $voltAttr->attribute_group);

        $matAttr = ProductAttribute::where('airtable_id', 'recMat1')->first();
        $this->assertNotNull($matAttr);
        $this->assertSame('Physical', $matAttr->attribute_group);

        $ipAttr = ProductAttribute::where('airtable_id', 'recIp1')->first();
        $this->assertNotNull($ipAttr);
        $this->assertSame('Compliance & Life', $ipAttr->attribute_group);
    }

    public function test_modern_specifier_template_groups_attributes_into_canonical_sections(): void
    {
        $this->seed(DatasheetTemplateSeeder::class);

        $template = DatasheetTemplate::query()->where('slug', 'modern-specifier-a4')->firstOrFail();
        $this->assertContains('grouped_specifications_table', $template->available_tokens);

        $product = Product::factory()->create([
            'product_name' => 'Architectural Spotlight',
            'product_code' => 'ARC-SPOT-01',
        ]);

        $export = ProductDatasheetExport::factory()->create([
            'product_id' => $product->id,
            'product_name' => 'Architectural Spotlight',
            'product_code' => 'ARC-SPOT-01',
            'configuration' => [
                'specifications' => [
                    ['label' => 'Delivered output', 'value' => '1,440 lm (20 W, 2700 K)', 'group' => 'Light'],
                    ['label' => 'Beam angle', 'value' => '36°', 'group' => 'Light'],
                    ['label' => 'System power', 'value' => '20 W', 'group' => 'Electrical & Control'],
                    ['label' => 'Dimming', 'value' => 'DALI, phase-cut', 'group' => 'Electrical & Control'],
                    ['label' => 'Housing', 'value' => 'Aluminium, powder-coated', 'group' => 'Physical'],
                    ['label' => 'Size', 'value' => 'Ø80 × 124 mm', 'group' => 'Physical'],
                    ['label' => 'Ingress', 'value' => 'IP20', 'group' => 'Compliance & Life'],
                    ['label' => 'Warranty', 'value' => '5 years', 'group' => 'Compliance & Life'],
                ],
            ],
        ]);

        /** @var IProductDatasheetService $service */
        $service = app(IProductDatasheetService::class);
        $rendered = $service->renderTemplate($template, $export);

        // Verify all 4 canonical group titles are rendered
        $this->assertStringContainsString('LIGHT', $rendered);
        $this->assertStringContainsString('ELECTRICAL &amp; CONTROL', $rendered);
        $this->assertStringContainsString('PHYSICAL', $rendered);
        $this->assertStringContainsString('COMPLIANCE &amp; LIFE', $rendered);

        // Verify specs within groups
        $this->assertStringContainsString('Delivered output', $rendered);
        $this->assertStringContainsString('1,440 lm (20 W, 2700 K)', $rendered);
        $this->assertStringContainsString('System power', $rendered);
        $this->assertStringContainsString('20 W', $rendered);
        $this->assertStringContainsString('Housing', $rendered);
        $this->assertStringContainsString('Aluminium, powder-coated', $rendered);
        $this->assertStringContainsString('Ingress', $rendered);
        $this->assertStringContainsString('IP20', $rendered);
        $this->assertStringContainsString('Warranty', $rendered);
        $this->assertStringContainsString('5 years', $rendered);

        // Verify 2-column structure
        $this->assertStringContainsString('class="specs-grid-wrap"', $rendered);
        $this->assertStringContainsString('class="specs-column"', $rendered);
    }

    public function test_standard_template_preserves_flat_specification_table(): void
    {
        $this->seed(DatasheetTemplateSeeder::class);

        $template = DatasheetTemplate::query()->where('slug', 'standard-azoogi-a4')->firstOrFail();

        $product = Product::factory()->create([
            'product_name' => 'Standard Linear Flex',
            'product_code' => 'SLF-01',
        ]);

        $export = ProductDatasheetExport::factory()->create([
            'product_id' => $product->id,
            'product_name' => 'Standard Linear Flex',
            'product_code' => 'SLF-01',
            'configuration' => [
                'specifications' => [
                    ['label' => 'Wattage', 'value' => '15W/m', 'group' => 'Electrical & Control'],
                    ['label' => 'IP Rating', 'value' => 'IP67', 'group' => 'Compliance & Life'],
                ],
            ],
        ]);

        /** @var IProductDatasheetService $service */
        $service = app(IProductDatasheetService::class);
        $rendered = $service->renderTemplate($template, $export);

        // Standard template uses flat table with params class and does NOT use the grouped grid
        $this->assertStringContainsString('<p class="params-title">SPECIFICATIONS</p>', $rendered);
        $this->assertStringContainsString('<table class="params">', $rendered);
        $this->assertStringContainsString('<td>Wattage</td>', $rendered);
        $this->assertStringContainsString('<td>15W/m</td>', $rendered);
        $this->assertStringNotContainsString('class="specs-grid-wrap"', $rendered);
    }
}
