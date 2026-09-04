<?php

namespace App\Enums;

enum ContentResource: string
{
    case Home = 'home';
    case About = 'about';
    case Solutions = 'solutions';
    case Casambi = 'casambi';
    case Silvair = 'silvair';
    case DaliCentre = 'dali-centre';
    case Madrix = 'madrix';
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
    case Header = 'header';
    case Footer = 'footer';
    case Projects = 'projects';
    case Products = 'products';

    public function label(): string
    {
        return match ($this) {
            self::Home => 'Home',
            self::About => 'About',
            self::Solutions => 'Solutions',
            self::Casambi => 'Casambi',
            self::Silvair => 'Silvair',
            self::DaliCentre => 'DALI Centre',
            self::Madrix => 'MADRIX',
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
            self::ModernSlavery => 'Modern Slavery Statement',
            self::Header => 'Header',
            self::Footer => 'Footer',
            self::Projects => 'Projects',
            self::Products => 'Products',
        };
    }

    public function isSection(): bool
    {
        return $this === self::Header || $this === self::Footer;
    }

    public function isPage(): bool
    {
        return $this !== self::Projects && $this !== self::Products && ! $this->isSection();
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
