<?php

namespace App\PageMeta\Definitions;

use App\PageMeta\Field;
use App\PageMeta\PageDefinition;

class HeaderDefinition implements PageDefinition
{
    public function slug(): string
    {
        return 'header';
    }

    public function title(): string
    {
        return 'Header';
    }

    public function metaDescription(): string
    {
        return '';
    }

    public function navLabel(): string
    {
        return 'Header';
    }

    public function fields(): array
    {
        return [
            Field::textarea('header.description', 'Description'),
            Field::text('header.phone', 'Phone'),
            Field::text('header.email', 'Email'),
            Field::text('header.word.text', 'Rotating text', true, 'header.word'),
        ];
    }

    public function seed(): array
    {
        $rows = [
            ['key' => 'header.description', 'sort_order' => 0, 'value' => 'Australian-Owned B2B Trade Wholesaler - Custom Lighting & Smart Control Solutions'],
            ['key' => 'header.phone', 'sort_order' => 0, 'value' => '1300 641 261'],
            ['key' => 'header.email', 'sort_order' => 0, 'value' => 'sales@azoogi.com'],
        ];

        foreach (['DESIGN', 'ENGINEER', 'CUSTOMISE', 'SUPPLY', 'CONTROL', 'COMMISSION'] as $order => $word) {
            $rows[] = ['key' => 'header.word.text', 'sort_order' => $order, 'value' => $word];
        }

        return $rows;
    }
}
