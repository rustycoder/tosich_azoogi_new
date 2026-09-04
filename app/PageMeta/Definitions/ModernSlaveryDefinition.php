<?php

namespace App\PageMeta\Definitions;

class ModernSlaveryDefinition extends LegalDefinition
{
    public function slug(): string
    {
        return 'modern-slavery';
    }

    public function title(): string
    {
        return 'Modern Slavery Policy — Azoogi Pty Ltd';
    }

    public function metaDescription(): string
    {
        return 'Azoogi Pty Ltd Anti-Modern Slavery Policy Statement.';
    }

    public function navLabel(): string
    {
        return 'Modern Slavery Statement';
    }

    public function seed(): array
    {
        return $this->legalSeed(
            'Legal',
            'Azoogi Pty Ltd — Anti-Modern Slavery Policy Statement',
            '',
            file_get_contents(database_path('seeders/data/legal/modern-slavery.html')),
        );
    }
}
