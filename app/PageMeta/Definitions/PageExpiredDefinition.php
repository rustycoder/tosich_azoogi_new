<?php

namespace App\PageMeta\Definitions;

use App\PageMeta\Field;
use App\PageMeta\PageDefinition;

class PageExpiredDefinition implements PageDefinition
{
    public function slug(): string
    {
        return '419';
    }

    public function title(): string
    {
        return 'Page Expired — Azoogi';
    }

    public function metaDescription(): string
    {
        return 'Your session has expired due to inactivity.';
    }

    public function navLabel(): string
    {
        return '419 Page';
    }

    public function fields(): array
    {
        return [
            Field::text('error.code', 'Error code'),
            Field::text('error.title', 'Title'),
            Field::textarea('error.lead', 'Lead text'),
            Field::text('cta.home.label', 'Home button label'),
            Field::url('cta.home.href', 'Home button link'),
            Field::text('cta.login.label', 'Login button label'),
            Field::url('cta.login.href', 'Login button link'),
        ];
    }

    public function seed(): array
    {
        return [
            ['key' => 'error.code', 'sort_order' => 0, 'value' => '419'],
            ['key' => 'error.title', 'sort_order' => 0, 'value' => 'Page Expired'],
            ['key' => 'error.lead', 'sort_order' => 0, 'value' => 'Your session has expired due to inactivity. Please refresh the page and try again.'],
            ['key' => 'cta.home.label', 'sort_order' => 0, 'value' => 'Back to Home'],
            ['key' => 'cta.home.href', 'sort_order' => 0, 'value' => '/'],
            ['key' => 'cta.login.label', 'sort_order' => 0, 'value' => 'Go to Login'],
            ['key' => 'cta.login.href', 'sort_order' => 0, 'value' => '/login'],
        ];
    }
}
