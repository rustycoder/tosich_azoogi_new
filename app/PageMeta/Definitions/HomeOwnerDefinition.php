<?php

namespace App\PageMeta\Definitions;

class HomeOwnerDefinition extends AudiencePageDefinition
{
    public function slug(): string
    {
        return 'home-owner';
    }

    public function title(): string
    {
        return 'Home Owner — Azoogi';
    }

    public function metaDescription(): string
    {
        return 'Azoogi LED lighting for home owners — quality fittings, expert advice, and lasting performance.';
    }

    public function navLabel(): string
    {
        return 'Home Owner';
    }
}
