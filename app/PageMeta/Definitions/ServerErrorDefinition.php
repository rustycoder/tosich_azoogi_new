<?php

namespace App\PageMeta\Definitions;

use App\PageMeta\Field;
use App\PageMeta\PageDefinition;

class ServerErrorDefinition implements PageDefinition
{
    public function slug(): string
    {
        return '500';
    }

    public function title(): string
    {
        return 'Server Error — Azoogi';
    }

    public function metaDescription(): string
    {
        return 'An unexpected server error occurred.';
    }

    public function navLabel(): string
    {
        return '500 Page';
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
            ['key' => 'error.code', 'sort_order' => 0, 'value' => '500'],
            ['key' => 'error.title', 'sort_order' => 0, 'value' => 'Something Went Wrong'],
            ['key' => 'error.lead', 'sort_order' => 0, 'value' => 'We encountered an unexpected server error. Please try again or contact our team if the issue persists.'],
            ['key' => 'cta.home.label', 'sort_order' => 0, 'value' => 'Back to Home'],
            ['key' => 'cta.home.href', 'sort_order' => 0, 'value' => '/'],
            ['key' => 'cta.contact.label', 'sort_order' => 0, 'value' => 'Contact Support'],
            ['key' => 'cta.contact.href', 'sort_order' => 0, 'value' => '/contact'],
        ];
    }
}
