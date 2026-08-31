<?php

namespace App\PageMeta\Definitions;

class WarrantyReturnsDefinition extends LegalDefinition
{
    public function slug(): string
    {
        return 'warranty-returns';
    }

    public function title(): string
    {
        return 'Warranty & Returns — Azoogi Pty Ltd';
    }

    public function metaDescription(): string
    {
        return 'Azoogi Pty Ltd warranty information and returns policy.';
    }

    public function navLabel(): string
    {
        return 'Warranty & Returns';
    }

    public function seed(): array
    {
        return $this->legalSeed(
            'Legal',
            'Warranty & Returns Policy',
            'At Azoogi Pty Ltd, we stand behind the quality and reliability of our lighting products. We strive to offer a fair, hassle-free warranty process to keep your projects running smoothly.',
            file_get_contents(database_path('seeders/data/legal/warranty.html')),
        );
    }
}
