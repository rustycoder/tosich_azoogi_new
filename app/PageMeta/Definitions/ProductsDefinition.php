<?php

namespace App\PageMeta\Definitions;

use App\PageMeta\Field;
use App\PageMeta\ImageSize;
use App\PageMeta\PageDefinition;

class ProductsDefinition implements PageDefinition
{
    public function slug(): string
    {
        return 'products';
    }

    public function title(): string
    {
        return 'Products — Azoogi';
    }

    public function metaDescription(): string
    {
        return 'Browse the full Azoogi LED lighting catalogue. COB Strips, SMD Strips, Neon, Outdoor Lights, Aluminium Profiles, LED Drivers and more.';
    }

    public function navLabel(): string
    {
        return 'Products';
    }

    public function fields(): array
    {
        return [
            Field::image('hero.image', 'Hero image', hint: ImageSize::Hero),
            Field::text('hero.title', 'Hero title'),
            Field::textarea('hero.lead', 'Hero intro'),
        ];
    }

    public function seed(): array
    {
        return [
            ['key' => 'hero.image', 'sort_order' => 0, 'value' => '/assets/hero02.jpg'],
            ['key' => 'hero.title', 'sort_order' => 0, 'value' => 'Our {Range}'],
            ['key' => 'hero.lead', 'sort_order' => 0, 'value' => 'Explore the full Azoogi lighting catalogue. COB Strips, SMD Strips, Neon, Outdoor Lights, Aluminium Profiles, LED Drivers and more.'],
        ];
    }
}
