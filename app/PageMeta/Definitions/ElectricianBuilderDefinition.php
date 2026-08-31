<?php

namespace App\PageMeta\Definitions;

class ElectricianBuilderDefinition extends AudiencePageDefinition
{
    public function slug(): string
    {
        return 'electrician-builder';
    }

    public function title(): string
    {
        return 'Electrician / Builder — Azoogi';
    }

    public function metaDescription(): string
    {
        return 'Azoogi LED lighting for electricians and builders — reliable products, fast turnaround, and trade support.';
    }

    public function navLabel(): string
    {
        return 'Electrician / Builder';
    }
}
