<?php

namespace App\PageMeta\Definitions;

use App\PageMeta\Field;
use App\PageMeta\PageDefinition;

class ServiceUnavailableDefinition implements PageDefinition
{
    public function slug(): string
    {
        return '503';
    }

    public function title(): string
    {
        return 'Service Unavailable — Azoogi';
    }

    public function metaDescription(): string
    {
        return 'We are currently performing scheduled maintenance.';
    }

    public function navLabel(): string
    {
        return '503 Page';
    }

    public function fields(): array
    {
        return [
            Field::text('error.code', 'Error code'),
            Field::text('error.title', 'Title'),
            Field::textarea('error.lead', 'Lead text'),
            Field::text('cta.home.label', 'Home button label'),
            Field::url('cta.home.href', 'Home button link'),
            Field::text('cta.contact.label', 'Contact button label'),
            Field::url('cta.contact.href', 'Contact button link'),
        ];
    }

    public function seed(): array
    {
        return [
            ['key' => 'error.code', 'sort_order' => 0, 'value' => '503'],
            ['key' => 'error.title', 'sort_order' => 0, 'value' => 'Under Maintenance'],
            ['key' => 'error.lead', 'sort_order' => 0, 'value' => 'We are currently performing scheduled maintenance to improve your experience. We will be back online shortly.'],
            ['key' => 'cta.home.label', 'sort_order' => 0, 'value' => 'Back to Home'],
            ['key' => 'cta.home.href', 'sort_order' => 0, 'value' => '/'],
            ['key' => 'cta.contact.label', 'sort_order' => 0, 'value' => 'Contact Us'],
            ['key' => 'cta.contact.href', 'sort_order' => 0, 'value' => '/contact'],
        ];
    }
}
