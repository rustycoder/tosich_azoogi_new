<?php

namespace App\PageMeta\Definitions;

class TermsDefinition extends LegalDefinition
{
    public function slug(): string
    {
        return 'terms';
    }

    public function title(): string
    {
        return 'Terms & Conditions — Azoogi Pty Ltd';
    }

    public function metaDescription(): string
    {
        return 'Azoogi Pty Ltd website and trade portal terms and conditions.';
    }

    public function seed(): array
    {
        return $this->legalSeed(
            'Legal',
            'Terms & Conditions',
            'Welcome to Azoogi Pty Ltd. We aim to be straightforward, friendly, and easy to deal with while delivering top-quality commercial and architectural lighting solutions to our Australian trade partners. These Terms & Conditions outline how our website, trade portal, and supply services operate.',
            file_get_contents(database_path('seeders/data/legal/terms.html')),
        );
    }
}
