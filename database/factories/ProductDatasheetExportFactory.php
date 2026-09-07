<?php

namespace Database\Factories;

use App\Models\Product;
use App\Models\ProductDatasheetExport;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<ProductDatasheetExport>
 */
class ProductDatasheetExportFactory extends Factory
{
    public function definition(): array
    {
        return [
            'uuid' => (string) Str::uuid(),
            'product_id' => Product::factory(),
            'airtable_id' => 'rec'.fake()->unique()->bothify('??????????????'),
            'product_code' => 'GL005',
            'product_name' => 'Garden Light',
            'project_name' => 'Harbour pavilion',
            'person_name' => fake()->name(),
            'configuration' => [
                'category' => 'Garden Light',
                'description' => 'Outdoor garden luminaire.',
                'specifications' => [
                    ['label' => 'Brand', 'value' => 'Azoogi'],
                    ['label' => 'Model', 'value' => 'GL005'],
                ],
                'selected_options' => ['Finish' => 'Black'],
                'product_image' => '/assets/img/neon.webp',
                'dimension_image' => '',
            ],
        ];
    }
}
