<?php

namespace App\PageMeta\Definitions;

use App\PageMeta\Field;
use App\PageMeta\PageDefinition;

class SolutionsDefinition implements PageDefinition
{
    public function slug(): string
    {
        return 'solutions';
    }

    public function title(): string
    {
        return 'Azoogi Solutions — End-to-End Lighting & Intelligent Controls';
    }

    public function metaDescription(): string
    {
        return 'End-to-end lighting solutions and intelligent controls — Casambi, MADRIX, Silvair and DALI Center ecosystems, plus custom LED capabilities across eight sectors.';
    }

    public function navLabel(): string
    {
        return 'Solutions';
    }

    public function fields(): array
    {
        return [
            Field::textarea('hero.title', 'Hero title'),
            Field::textarea('hero.lead', 'Hero lead'),
            Field::text('hero.claim', 'Hero claim'),
            Field::textarea('hero.sub', 'Hero sub'),
            Field::text('eco.heading', 'Ecosystems heading'),
            Field::textarea('eco.lead', 'Ecosystems lead'),
            Field::text('eco.item.name', 'Platform name', true, 'eco.item'),
            Field::text('eco.item.sub', 'Platform subtitle', true, 'eco.item'),
            Field::url('eco.item.href', 'Platform href', true, 'eco.item'),
            Field::text('eco.cta.heading', 'Eco CTA heading'),
            Field::textarea('eco.cta.body', 'Eco CTA body'),
            Field::text('eco.cta.label', 'Eco CTA label'),
            Field::url('eco.cta.href', 'Eco CTA href'),
            Field::text('sector.heading', 'Sectors heading'),
            Field::text('sector.hint', 'Sectors hint'),
            Field::text('sector.item.title', 'Sector title', true, 'sector.item'),
            Field::textarea('sector.item.body', 'Sector body', true, 'sector.item'),
            Field::text('sector.cta.label', 'Sector CTA'),
            Field::url('sector.cta.href', 'Sector CTA href'),
        ];
    }

    public function seed(): array
    {
        $eco = [
            ['Casambi', 'Wireless BLE Mesh', '#'],
            ['MADRIX', 'Pixel Mapping & Visuals', '/madrix'],
            ['Silvair', 'Enterprise Bluetooth Mesh', '#'],
            ['DALI Center', 'Centralized DALI-2 Management & Analytics', '#'],
        ];

        $sectors = [
            ['Commercial & Workspace', 'High-efficiency linear extrusion, low-glare task profiles, and acoustic-integrated lighting tailored for modern offices, education campuses, and corporate environments.'],
            ['Urban, Facade & Architectural', 'Exterior-rated IP67/IP68 fixtures, dynamic RGBW colour washing, and architectural linear runs engineered to highlight building structures and public spaces.'],
            ['Healthcare & Wellness', 'Circadian CCT tuning, high colour accuracy (CRI 95+), flicker-free dimming, and IP-sealed hygienic luminaires for hospitals, laboratories, and care facilities.'],
            ['Retail, Display & Hospitality', 'High-R9 accent lighting, precision optical beam shaping, and custom ambient extrusions designed to enhance product textures, dining, and gallery environments.'],
            ['Adverse, Security & Custodial', 'Heavy-duty, anti-ligature, and vandal-resistant (IK10+) luminaires built for high-security infrastructure, correctional facilities, and extreme environments.'],
            ['Civil, Sports & Infrastructure', 'Built to AS standards for ovals, transit hubs, roadways, and council infrastructure. High-output floodlighting, pole packages, and rugged utility luminaires'],
            ['Emergency & Exit Safety Systems', 'AS/NZS 2293-compliant escape route fittings, exit signage, and centralized battery monitoring designed for automated testing and life-safety integration.'],
            ['Bespoke Residential & Lifestyle', 'Ultra-slim micro-profiles, plaster-in channels, custom cove extrusions, and tailored powder-coating finishes for luxury homes and high-end living spaces.'],
        ];

        $rows = [
            ['key' => 'hero.title', 'sort_order' => 0, 'value' => 'End-to-End Lighting Solutions & Intelligent Controls'],
            ['key' => 'hero.lead', 'sort_order' => 0, 'value' => 'From initial plans through to final commissioning, we provide complete, custom packages - staying at the absolute forefront of modern lighting technology and smart control automation.'],
            ['key' => 'hero.claim', 'sort_order' => 0, 'value' => 'Azoogi does it all.'],
            ['key' => 'hero.sub', 'sort_order' => 0, 'value' => 'We design, engineer, customize, supply, control, and commission tailored lighting environments. By pairing custom hardware with seamless intelligent controls, we give you a single, trusted technology partner from concept through to final handover.'],
            ['key' => 'eco.heading', 'sort_order' => 0, 'value' => 'Explore Our Core Intelligent Control Ecosystems'],
            ['key' => 'eco.lead', 'sort_order' => 0, 'value' => 'Select a platform below to view technical specifications, system capabilities, and intro videos.'],
            ['key' => 'eco.cta.heading', 'sort_order' => 0, 'value' => 'Need a custom or unlisted application?'],
            ['key' => 'eco.cta.body', 'sort_order' => 0, 'value' => 'We design, engineer, and custom-manufacture bespoke LED solutions for any project requirement.'],
            ['key' => 'eco.cta.label', 'sort_order' => 0, 'value' => 'Contact Us'],
            ['key' => 'eco.cta.href', 'sort_order' => 0, 'value' => '/contact'],
            ['key' => 'sector.heading', 'sort_order' => 0, 'value' => 'Our Lighting Capabilities by Sector'],
            ['key' => 'sector.hint', 'sort_order' => 0, 'value' => 'Hover or tap a sector to read the detail.'],
            ['key' => 'sector.cta.label', 'sort_order' => 0, 'value' => 'Explore Data Center Lighting Solutions'],
            ['key' => 'sector.cta.href', 'sort_order' => 0, 'value' => '/data-centre'],
        ];

        foreach ($eco as $i => [$name, $sub, $href]) {
            $rows[] = ['key' => 'eco.item.name', 'sort_order' => $i, 'value' => $name];
            $rows[] = ['key' => 'eco.item.sub', 'sort_order' => $i, 'value' => $sub];
            $rows[] = ['key' => 'eco.item.href', 'sort_order' => $i, 'value' => $href];
        }

        foreach ($sectors as $i => [$title, $body]) {
            $rows[] = ['key' => 'sector.item.title', 'sort_order' => $i, 'value' => $title];
            $rows[] = ['key' => 'sector.item.body', 'sort_order' => $i, 'value' => $body];
        }

        return $rows;
    }
}
