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
            Field::video('hero.video', 'Hero video'),
            Field::image('hero.poster', 'Hero poster', hint: ImageSize::Hero),
            Field::text('hero.title', 'Hero title', typographic: false),
            Field::textarea('hero.lead', 'Hero intro', typographic: false),
        ];
    }

    public function seed(): array
    {
        return [
            ['key' => 'hero.poster', 'sort_order' => 0, 'value' => '/assets/hero02.jpg'],
            ['key' => 'hero.title', 'sort_order' => 0, 'value' => 'Our {Range}'],
            ['key' => 'hero.lead', 'sort_order' => 0, 'value' => 'Explore the full Azoogi lighting catalogue. COB Strips, SMD Strips, Neon, Outdoor Lights, Aluminium Profiles, LED Drivers and more.'],
        ];
    }
}
