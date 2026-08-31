<?php

namespace App\PageMeta\Definitions;

use App\PageMeta\Field;
use App\PageMeta\PageDefinition;

class FooterDefinition implements PageDefinition
{
    public function slug(): string
    {
        return 'footer';
    }

    public function title(): string
    {
        return 'Footer';
    }

    public function metaDescription(): string
    {
        return '';
    }

    public function navLabel(): string
    {
        return 'Footer';
    }

    public function fields(): array
    {
        return [
            Field::textarea('footer.description', 'Description'),
            Field::text('footer.phone', 'Phone'),
            Field::text('footer.email', 'Email'),
            Field::text('footer.message', 'Message'),
        ];
    }

    public function seed(): array
    {
        return [
            ['key' => 'footer.description', 'sort_order' => 0, 'value' => 'We design, engineer, manufacture, assemble, and test our products in-house, offering custom powder coating to deliver fully tailored lighting solutions'],
            ['key' => 'footer.phone', 'sort_order' => 0, 'value' => '1300 641 261'],
            ['key' => 'footer.email', 'sort_order' => 0, 'value' => 'sales@azoogi.com'],
            ['key' => 'footer.message', 'sort_order' => 0, 'value' => 'Azoogi Pty Ltd. All rights reserved.'],
        ];
    }
}
