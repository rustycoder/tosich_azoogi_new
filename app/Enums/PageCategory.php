<?php

namespace App\Enums;

enum PageCategory: string
{
    case Core = 'core';
    case Technology = 'technology';
    case Audience = 'audience';
    case Legal = 'legal';
    case System = 'system';

    public function label(): string
    {
        return match ($this) {
            self::Core => 'Core & Marketing',
            self::Technology => 'Smart Controls & Tech',
            self::Audience => 'Audience Portals',
            self::Legal => 'Legal & Policies',
            self::System => 'System & Errors',
        };
    }

    public function shortLabel(): string
    {
        return match ($this) {
            self::Core => 'Core',
            self::Technology => 'Technology',
            self::Audience => 'Audience',
            self::Legal => 'Legal',
            self::System => 'System',
        };
    }

    public function badgeClass(): string
    {
        return match ($this) {
            self::Core => 'dash-cat-core',
            self::Technology => 'dash-cat-tech',
            self::Audience => 'dash-cat-audience',
            self::Legal => 'dash-cat-legal',
            self::System => 'dash-cat-system',
        };
    }

    public function description(): string
    {
        return match ($this) {
            self::Core => 'Primary public marketing and brand pages',
            self::Technology => 'Smart lighting ecosystems, protocols, and specialized solutions',
            self::Audience => 'Dedicated portals for trade and design customer segments',
            self::Legal => 'Terms, compliance, warranties, and privacy statements',
            self::System => 'HTTP status and error response pages',
        };
    }
}
