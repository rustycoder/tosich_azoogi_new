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
            Field::text('footer.products.heading', 'Products heading'),
            Field::text('footer.products.link.label', 'Label', true, 'footer.products.link'),
            Field::url('footer.products.link.href', 'URL', true, 'footer.products.link'),
            Field::select('footer.products.link.target', 'Target', ['_self' => 'Same tab', '_blank' => 'New tab'], true, 'footer.products.link'),
            Field::text('footer.company.heading', 'Company heading'),
            Field::text('footer.company.link.label', 'Label', true, 'footer.company.link'),
            Field::url('footer.company.link.href', 'URL', true, 'footer.company.link'),
            Field::select('footer.company.link.target', 'Target', ['_self' => 'Same tab', '_blank' => 'New tab'], true, 'footer.company.link'),
            Field::text('footer.contact.heading', 'Contact heading'),
            Field::text('footer.contact.link.label', 'Label', true, 'footer.contact.link'),
            Field::url('footer.contact.link.href', 'URL', true, 'footer.contact.link'),
            Field::select('footer.contact.link.target', 'Target', ['_self' => 'Same tab', '_blank' => 'New tab'], true, 'footer.contact.link'),
        ];
    }

    public function seed(): array
    {
        $rows = [
            ['key' => 'footer.description', 'sort_order' => 0, 'value' => 'We design, engineer, manufacture, assemble, and test our products in-house, offering custom powder coating to deliver fully tailored lighting solutions'],
            ['key' => 'footer.phone', 'sort_order' => 0, 'value' => '1300 641 261'],
            ['key' => 'footer.email', 'sort_order' => 0, 'value' => 'sales@azoogi.com'],
            ['key' => 'footer.message', 'sort_order' => 0, 'value' => 'Azoogi Pty Ltd. All rights reserved.'],
            ['key' => 'footer.products.heading', 'sort_order' => 0, 'value' => 'Products'],
            ['key' => 'footer.company.heading', 'sort_order' => 0, 'value' => 'Company'],
            ['key' => 'footer.contact.heading', 'sort_order' => 0, 'value' => 'Contact'],
        ];

        foreach ([
            ['All Products', '/products'],
            ['LED Calculator', '/led-strip-calculator'],
        ] as $order => $item) {
            $rows[] = ['key' => 'footer.products.link.label', 'sort_order' => $order, 'value' => $item[0]];
            $rows[] = ['key' => 'footer.products.link.href', 'sort_order' => $order, 'value' => $item[1]];
            $rows[] = ['key' => 'footer.products.link.target', 'sort_order' => $order, 'value' => '_self'];
        }

        foreach ([
            ['About Azoogi', '/about'],
            ['Projects', '/projects'],
            ['AI Lighting', '/ai-lighting'],
            ['Contact', '/contact'],
            ['Privacy', '/privacy'],
        ] as $order => $item) {
            $rows[] = ['key' => 'footer.company.link.label', 'sort_order' => $order, 'value' => $item[0]];
            $rows[] = ['key' => 'footer.company.link.href', 'sort_order' => $order, 'value' => $item[1]];
            $rows[] = ['key' => 'footer.company.link.target', 'sort_order' => $order, 'value' => '_self'];
        }

        $rows[] = ['key' => 'footer.contact.link.label', 'sort_order' => 0, 'value' => 'Trade Login'];
        $rows[] = ['key' => 'footer.contact.link.href', 'sort_order' => 0, 'value' => '/trade-login'];
        $rows[] = ['key' => 'footer.contact.link.target', 'sort_order' => 0, 'value' => '_self'];

        return $rows;
    }
}
