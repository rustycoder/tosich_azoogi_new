<?php

namespace Database\Seeders;

use App\Models\DatasheetTemplate;
use Illuminate\Database\Seeder;

class DatasheetTemplateSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DatasheetTemplate::query()->updateOrCreate(
            ['slug' => 'standard-azoogi-a4'],
            [
                'name' => 'Standard Azoogi A4',
                'description' => 'Official factory specification datasheet template.',
                'is_active' => true,
                'body_html' => DatasheetTemplate::defaultHtml(),
                'custom_css' => DatasheetTemplate::defaultCss(),
                'available_tokens' => DatasheetTemplate::defaultTokens(),
            ],
        );

        DatasheetTemplate::query()->updateOrCreate(
            ['slug' => 'modern-specifier-a4'],
            [
                'name' => 'Modern Specifier A4',
                'description' => 'Architectural specifier layout with submission block, order code badges, dual product/dimension showcase, and structured technical data tables.',
                'is_active' => false,
                'body_html' => DatasheetTemplate::modernSpecifierHtml(),
                'custom_css' => DatasheetTemplate::modernSpecifierCss(),
                'available_tokens' => DatasheetTemplate::defaultTokens(),
            ],
        );
    }
}
