<?php

namespace App\PageMeta;

final class SectionItems
{
    /**
     * Form alias => page_meta group prefix.
     *
     * @return array<string, string>
     */
    public static function aliases(string $slug): array
    {
        return match ($slug) {
            'header' => [
                'nav' => 'header.nav',
                'words' => 'header.word',
            ],
            'footer' => [
                'products' => 'footer.products.link',
                'company' => 'footer.company.link',
                'contact' => 'footer.contact.link',
            ],
            default => [],
        };
    }
}
