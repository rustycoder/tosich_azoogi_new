<?php

namespace App\PageMeta;

use App\PageMeta\Definitions\AboutDefinition;
use App\PageMeta\Definitions\AiLightingDefinition;
use App\PageMeta\Definitions\ArchitectDesignerDefinition;
use App\PageMeta\Definitions\ContactDefinition;
use App\PageMeta\Definitions\DataCentreDefinition;
use App\PageMeta\Definitions\ElectricianBuilderDefinition;
use App\PageMeta\Definitions\FooterDefinition;
use App\PageMeta\Definitions\HeaderDefinition;
use App\PageMeta\Definitions\HomeDefinition;
use App\PageMeta\Definitions\HomeOwnerDefinition;
use App\PageMeta\Definitions\ModernSlaveryDefinition;
use App\PageMeta\Definitions\PrivacyDefinition;
use App\PageMeta\Definitions\SolutionsDefinition;
use App\PageMeta\Definitions\TermsDefinition;
use App\PageMeta\Definitions\WarrantyReturnsDefinition;
use App\PageMeta\Definitions\WholesalerDefinition;
use InvalidArgumentException;

final class Catalog
{
    /**
     * @var array<string, class-string<PageDefinition>>
     */
    private const DEFINITIONS = [
        'home' => HomeDefinition::class,
        'about' => AboutDefinition::class,
        'solutions' => SolutionsDefinition::class,
        'ai-lighting' => AiLightingDefinition::class,
        'data-centre' => DataCentreDefinition::class,
        'contact' => ContactDefinition::class,
        'home-owner' => HomeOwnerDefinition::class,
        'architect-designer' => ArchitectDesignerDefinition::class,
        'electrician-builder' => ElectricianBuilderDefinition::class,
        'wholesaler' => WholesalerDefinition::class,
        'privacy' => PrivacyDefinition::class,
        'terms' => TermsDefinition::class,
        'warranty-returns' => WarrantyReturnsDefinition::class,
        'modern-slavery' => ModernSlaveryDefinition::class,
        'header' => HeaderDefinition::class,
        'footer' => FooterDefinition::class,
    ];

    /**
     * @return list<string>
     */
    public static function sectionSlugs(): array
    {
        return ['header', 'footer'];
    }

    public static function isSection(string $slug): bool
    {
        return in_array($slug, self::sectionSlugs(), true);
    }

    public static function sectionDescription(string $slug): string
    {
        return match ($slug) {
            'header' => 'Logo, contact details, rotating words, and navigation shown at the top of every public page.',
            'footer' => 'Company copy, menus, and legal links shown at the bottom of every public page.',
            default => '',
        };
    }

    public static function for(string $slug): PageDefinition
    {
        $class = self::DEFINITIONS[$slug] ?? null;

        if ($class === null) {
            throw new InvalidArgumentException("Unknown page slug [{$slug}].");
        }

        return new $class;
    }

    /**
     * @return list<string>
     */
    public static function slugs(): array
    {
        return array_keys(self::DEFINITIONS);
    }

    /**
     * @return list<PageDefinition>
     */
    public static function all(): array
    {
        return array_map(fn (string $slug): PageDefinition => self::for($slug), self::slugs());
    }

    public static function has(string $slug): bool
    {
        return isset(self::DEFINITIONS[$slug]);
    }
}
