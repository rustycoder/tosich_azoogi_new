<?php

namespace App\PageMeta\Definitions;

use App\PageMeta\Field;
use App\PageMeta\PageDefinition;

class NotFoundDefinition implements PageDefinition
{
    public function slug(): string
    {
        return '404';
    }

    public function title(): string
    {
        return 'Page Not Found — Azoogi';
    }

    public function metaDescription(): string
    {
        return 'The page you requested could not be found.';
    }

    public function navLabel(): string
    {
        return '404 Page';
    }

    public function fields(): array
    {
        return [
            Field::text('error.code', 'Error code'),
            Field::text('error.title', 'Title'),
            Field::textarea('error.lead', 'Lead text'),
            Field::text('cta.home.label', 'Home button label'),
            Field::url('cta.home.href', 'Home button link'),
            Field::text('cta.products.label', 'Products button label'),
            Field::url('cta.products.href', 'Products button link'),
            Field::text('cta.contact.label', 'Contact button label'),
            Field::url('cta.contact.href', 'Contact button link'),
        ];
    }

    public function seed(): array
    {
        return [
            ['key' => 'error.code', 'sort_order' => 0, 'value' => '404'],
            ['key' => 'error.title', 'sort_order' => 0, 'value' => 'Page Not Found'],
            ['key' => 'error.lead', 'sort_order' => 0, 'value' => "The page you are looking for doesn't exist, has been removed, or is temporarily unavailable."],
            ['key' => 'cta.home.label', 'sort_order' => 0, 'value' => 'Back to Home'],
            ['key' => 'cta.home.href', 'sort_order' => 0, 'value' => '/'],
            ['key' => 'cta.products.label', 'sort_order' => 0, 'value' => 'Browse Products'],
            ['key' => 'cta.products.href', 'sort_order' => 0, 'value' => '/products'],
            ['key' => 'cta.contact.label', 'sort_order' => 0, 'value' => 'Contact Us'],
            ['key' => 'cta.contact.href', 'sort_order' => 0, 'value' => '/contact'],
        ];
    }
}
