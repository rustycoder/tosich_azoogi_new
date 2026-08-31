<?php

namespace App\Enums;

enum ContentResource: string
{
    case Home = 'home';
    case About = 'about';
    case Solutions = 'solutions';
    case AiLighting = 'ai-lighting';
    case DataCentre = 'data-centre';
    case Contact = 'contact';
    case HomeOwner = 'home-owner';
    case ArchitectDesigner = 'architect-designer';
    case ElectricianBuilder = 'electrician-builder';
    case Wholesaler = 'wholesaler';
    case Privacy = 'privacy';
    case Terms = 'terms';
    case WarrantyReturns = 'warranty-returns';
    case ModernSlavery = 'modern-slavery';
    case Projects = 'projects';

    public function label(): string
    {
        return match ($this) {
            self::Home => 'Home',
            self::About => 'About',
            self::Solutions => 'Solutions',
            self::AiLighting => 'AI Lighting',
            self::DataCentre => 'Data Centre',
            self::Contact => 'Contact',
            self::HomeOwner => 'Home Owner',
            self::ArchitectDesigner => 'Architect / Designer',
            self::ElectricianBuilder => 'Electrician / Builder',
            self::Wholesaler => 'Wholesaler',
            self::Privacy => 'Privacy',
            self::Terms => 'Terms',
            self::WarrantyReturns => 'Warranty & Returns',
            self::ModernSlavery => 'Modern Slavery',
            self::Projects => 'Projects',
        };
    }

    public function isPage(): bool
    {
        return $this !== self::Projects;
    }

    /**
     * @return list<self>
     */
    public static function pages(): array
    {
        return array_values(array_filter(
            self::cases(),
            fn (self $resource): bool => $resource->isPage(),
        ));
    }
}
