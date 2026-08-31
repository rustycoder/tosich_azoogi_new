<?php

namespace App\PageMeta\Definitions;

class ArchitectDesignerDefinition extends AudiencePageDefinition
{
    public function slug(): string
    {
        return 'architect-designer';
    }

    public function title(): string
    {
        return 'Architect / Designer — Azoogi';
    }

    public function metaDescription(): string
    {
        return 'Azoogi LED lighting for interior and lighting designers — specify with certainty and design without limits.';
    }

    public function navLabel(): string
    {
        return 'Architect / Designer';
    }
}
