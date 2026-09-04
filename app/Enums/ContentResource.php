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
    case RequestAQuote = 'request-a-quote';
    case Projects = 'projects';
    case Products = 'products';
    case QuoteEnquiries = 'quote-enquiries';
    case ProductEnquiries = 'product-enquiries';
    case ContactEnquiry = 'contact-enquiry';

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
            self::RequestAQuote => 'Request a Quote',
            self::Projects => 'Projects',
            self::Products => 'Products',
            self::QuoteEnquiries => 'Quote Enquiries',
            self::ProductEnquiries => 'Product Enquiries',
            self::ContactEnquiry => 'Contact Enquiries',
        };
    }

    public function isSection(): bool
    {
        return $this === self::Header || $this === self::Footer;
    }

    public function isEnquiry(): bool
    {
        return match ($this) {
            self::QuoteEnquiries, self::ProductEnquiries, self::ContactEnquiry => true,
            default => false,
        };
    }

    public function isPage(): bool
    {
        return ! $this->isSection() && ! $this->isEnquiry() && $this !== self::Products;
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

    /**
     * @return list<array{label: string, sections: list<array{label: string, resources: list<self>}>}>
     */
    public static function staffGroups(): array
    {
        $enquiries = [];
        $pages = [];
        $site = [];
        $catalog = [];

        foreach (self::cases() as $resource) {
            if ($resource->isEnquiry()) {
                $enquiries[] = $resource;
            } elseif ($resource->isSection()) {
                $site[] = $resource;
            } elseif (in_array($resource, [self::RequestAQuote, self::Projects, self::Products], true)) {
                $catalog[] = $resource;
            } else {
                $pages[] = $resource;
            }
        }

        return [
            [
                'label' => 'Enquiries',
                'sections' => [
                    ['label' => '', 'resources' => $enquiries],
                ],
            ],
            [
                'label' => 'Content Management',
                'sections' => [
                    ['label' => 'Pages', 'resources' => $pages],
                    ['label' => 'Site', 'resources' => $site],
                    ['label' => 'Catalog', 'resources' => $catalog],
                ],
            ],
        ];
    }
}
