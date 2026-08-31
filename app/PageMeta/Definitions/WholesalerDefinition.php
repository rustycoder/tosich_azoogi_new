<?php

namespace App\PageMeta\Definitions;

class WholesalerDefinition extends AudiencePageDefinition
{
    public function slug(): string
    {
        return 'wholesaler';
    }

    public function title(): string
    {
        return 'Wholesaler — Azoogi';
    }

    public function metaDescription(): string
    {
        return 'Azoogi LED lighting for electrical wholesalers — channel-protected products, reliable stock, and trade margins.';
    }

    public function navLabel(): string
    {
        return 'Wholesaler';
    }
}
