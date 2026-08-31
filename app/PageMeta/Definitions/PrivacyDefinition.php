<?php

namespace App\PageMeta\Definitions;

class PrivacyDefinition extends LegalDefinition
{
    public function slug(): string
    {
        return 'privacy';
    }

    public function title(): string
    {
        return 'Privacy Policy — Azoogi Pty Ltd';
    }

    public function metaDescription(): string
    {
        return 'Azoogi Pty Ltd Privacy Policy for trade clients across Australia.';
    }

    public function seed(): array
    {
        return $this->legalSeed(
            'Legal',
            'Privacy Policy',
            'At Azoogi Pty Ltd, we keep things simple, transparent, and hassle-free. We value your trust and are committed to protecting your privacy while providing high-quality commercial and architectural lighting solutions to our trade clients across Australia. This Privacy Policy explains how we handle your personal and business information when you visit our website, use our trade portal, or deal with our team.',
            file_get_contents(database_path('seeders/data/legal/privacy.html')),
        );
    }
}
