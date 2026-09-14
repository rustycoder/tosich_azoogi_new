<?php

namespace App\PageMeta\Definitions;

use App\PageMeta\Field;
use App\PageMeta\PageDefinition;

class LedCalculatorDefinition implements PageDefinition
{
    public function slug(): string
    {
        return 'led-strip-calculator';
    }

    public function title(): string
    {
        return 'LED Strip Calculator — Azoogi';
    }

    public function metaDescription(): string
    {
        return 'Build your perfect LED strip setup with Azoogi’s LED Strip Calculator. Choose location, chip, colour, voltage, power and more.';
    }

    public function navLabel(): string
    {
        return 'LED Calculator';
    }

    public function fields(): array
    {
        return [
            Field::text('hero.title', 'Hero title'),
            Field::text('hero.title_accent', 'Title accent'),
            Field::textarea('hero.lead', 'Hero lead'),
            Field::text('hero.cta.label', 'Hero CTA'),
        ];
    }

    public function seed(): array
    {
        return [
            ['key' => 'hero.title', 'sort_order' => 0, 'value' => 'LED Strip Calculator'],
            ['key' => 'hero.title_accent', 'sort_order' => 0, 'value' => 'Calculator'],
            ['key' => 'hero.lead', 'sort_order' => 0, 'value' => 'Azoogi’s versatile range of interior and exterior LED Strip Lights is perfect for both residential and commercial spaces. With smart control options and custom configurations, creating the ideal lighting solution has never been easier. Use our simple selector tools to build your perfect LED strip setup today.'],
            ['key' => 'hero.cta.label', 'sort_order' => 0, 'value' => 'Begin LED Selector'],
        ];
    }
}
