<?php

namespace App\PageMeta\Definitions;

use App\PageMeta\Field;
use App\PageMeta\PageDefinition;

class DataCentreDefinition implements PageDefinition
{
    public function slug(): string
    {
        return 'data-centre';
    }

    public function title(): string
    {
        return 'Data Centre Lighting — Mission-Critical Design | Azoogi';
    }

    public function metaDescription(): string
    {
        return 'Azoogi data centre lighting: mission-critical lighting & design services engineered for ANZ standards, max uptime, low PUE, and rapid deployment.';
    }

    public function navLabel(): string
    {
        return 'Data Centre';
    }

    public function fields(): array
    {
        return [
            Field::text('hero.kicker', 'Hero kicker'),
            Field::textarea('hero.title', 'Hero title'),
            Field::textarea('hero.lead', 'Hero lead'),
            Field::video('hero.video', 'Hero video'),
            Field::image('hero.poster', 'Hero poster'),
            Field::textarea('intro.body', 'Intro'),
            Field::text('intro.cta.primary.label', 'Primary CTA'),
            Field::url('intro.cta.primary.href', 'Primary href'),
            Field::text('intro.cta.secondary.label', 'Secondary CTA'),
            Field::url('intro.cta.secondary.href', 'Secondary href'),
            Field::text('why.kicker', 'Why kicker'),
            Field::textarea('why.heading', 'Why heading'),
            Field::textarea('why.body', 'Why body'),
            Field::text('why.item.title', 'Why title', true, 'why.item'),
            Field::textarea('why.item.body', 'Why item body', true, 'why.item'),
            Field::text('hardware.kicker', 'Hardware kicker'),
            Field::textarea('hardware.heading', 'Hardware heading'),
            Field::textarea('hardware.tick', 'Hardware tick', true, 'hardware.tick'),
            Field::image('hardware.image', 'Hardware image'),
            Field::text('control.kicker', 'Control kicker'),
            Field::textarea('control.heading', 'Control heading'),
            Field::textarea('control.tick', 'Control tick', true, 'control.tick'),
            Field::image('control.image', 'Control image'),
            Field::text('emergency.heading', 'Emergency heading'),
            Field::text('emergency.item.title', 'Emergency title', true, 'emergency.item'),
            Field::textarea('emergency.item.body', 'Emergency body', true, 'emergency.item'),
            Field::text('zones.heading', 'Zones heading'),
            Field::text('zones.item.title', 'Zone title', true, 'zones.item'),
            Field::textarea('zones.item.body', 'Zone body', true, 'zones.item'),
            Field::textarea('cta.heading', 'CTA heading'),
            Field::textarea('cta.body', 'CTA body'),
            Field::text('cta.primary.label', 'CTA primary'),
            Field::url('cta.primary.href', 'CTA primary href'),
            Field::text('cta.secondary.label', 'CTA secondary'),
            Field::url('cta.secondary.href', 'CTA secondary href'),
        ];
    }

    public function seed(): array
    {
        $rows = [
            ['key' => 'hero.kicker', 'sort_order' => 0, 'value' => 'Azoogi Data Centre Lighting'],
            ['key' => 'hero.title', 'sort_order' => 0, 'value' => "Mission-Critical Data Centre\nLighting & Design Services"],
            ['key' => 'hero.lead', 'sort_order' => 0, 'value' => 'Engineered for ANZ Standards. Built for Maximum Uptime, Low PUE, and Rapid Deployment.'],
            ['key' => 'hero.video', 'sort_order' => 0, 'value' => '/assets/img/Data_Centre_DRAFT_optimized.webm'],
            ['key' => 'hero.poster', 'sort_order' => 0, 'value' => '/assets/img/datacenter.webp'],
            ['key' => 'intro.body', 'sort_order' => 0, 'value' => 'From hyperscale developments to brownfield retrofits, Azoogi delivers end-to-end, fully compliant lighting strategies across Australia and New Zealand. We combine tailored design expertise with intelligent controls, thermal-resilient hardware, and integrated sensing technology to de-risk your facility, cut operational energy, and optimize your Power Usage Effectiveness (PUE).'],
            ['key' => 'intro.cta.primary.label', 'sort_order' => 0, 'value' => 'Request a Design Consultation'],
            ['key' => 'intro.cta.primary.href', 'sort_order' => 0, 'value' => '/contact'],
            ['key' => 'intro.cta.secondary.label', 'sort_order' => 0, 'value' => 'Request Capability Statement'],
            ['key' => 'intro.cta.secondary.href', 'sort_order' => 0, 'value' => '/contact'],
            ['key' => 'why.kicker', 'sort_order' => 0, 'value' => 'Why Azoogi Design Services?'],
            ['key' => 'why.heading', 'sort_order' => 0, 'value' => "Complete Certainty for Your\nWhite & Grey Spaces"],
            ['key' => 'why.body', 'sort_order' => 0, 'value' => 'Lighting in mission-critical facilities is an operational tool that directly influences technician safety, maintenance precision, ambient heat loads, and overall facility efficiency.'],
            ['key' => 'hardware.kicker', 'sort_order' => 0, 'value' => 'Hardware'],
            ['key' => 'hardware.heading', 'sort_order' => 0, 'value' => "Engineered for Extreme\nData Hall Conditions"],
            ['key' => 'hardware.image', 'sort_order' => 0, 'value' => '/assets/img/datacenter1.webp'],
            ['key' => 'control.kicker', 'sort_order' => 0, 'value' => 'Control'],
            ['key' => 'control.heading', 'sort_order' => 0, 'value' => "Smart Sensors &\nBuilding Automation"],
            ['key' => 'control.image', 'sort_order' => 0, 'value' => '/assets/img/datacenter2.webp'],
            ['key' => 'emergency.heading', 'sort_order' => 0, 'value' => 'Fail-Safe Emergency Lighting'],
            ['key' => 'zones.heading', 'sort_order' => 0, 'value' => 'Tailored Lighting Across All Zones'],
            ['key' => 'cta.heading', 'sort_order' => 0, 'value' => 'Ready to Optimise Your Next Data Centre Project?'],
            ['key' => 'cta.body', 'sort_order' => 0, 'value' => 'Whether you are designing a new hyperscale facility or upgrading an operational server hall, Azoogi provides complete technical support from initial concept through to final commissioning.'],
            ['key' => 'cta.primary.label', 'sort_order' => 0, 'value' => 'Request a Design Consultation'],
            ['key' => 'cta.primary.href', 'sort_order' => 0, 'value' => '/contact'],
            ['key' => 'cta.secondary.label', 'sort_order' => 0, 'value' => 'Email datacenters@azoogi.com'],
            ['key' => 'cta.secondary.href', 'sort_order' => 0, 'value' => 'mailto:datacenters@azoogi.com'],
        ];

        $why = [
            ['Audit-Ready Compliance', 'Fully engineered to satisfy AS/NZS 1680 (interior illuminance & glare control), AS/NZS 2293 (emergency lighting), and global TIA-942 standards.'],
            ['3D Photometric Engineering', 'Detailed modeling calculating Lux levels, glare control (UGR <19), and color rendering across server aisles, control rooms, and corridors.'],
            ['Cable & Asset Protection', 'Zero-UV LED light engines prevent photo-degradation and embrittlement of sensitive fiber-optic jacketing, patch cables, and plastic server components.'],
            ['Rapid Deployment & Circular', 'Pre-configured, modular linear systems designed to minimize labor. High-efficacy luminaires utilizing recycled aluminum profiles and low-embodied-carbon materials.'],
        ];
        foreach ($why as $i => [$title, $body]) {
            $rows[] = ['key' => 'why.item.title', 'sort_order' => $i, 'value' => $title];
            $rows[] = ['key' => 'why.item.body', 'sort_order' => $i, 'value' => $body];
        }

        $hardware = [
            'High-Ambient Thermal Resilience: Industrial linear solutions feature extruded aluminum heat sinks paired with long-life drivers tested for continuous 24/7 operation in elevated-temperature environments.',
            'Reduced HVAC Load: Ultra-efficient LEDs release minimal heat at the ceiling grid level, relieving thermal strain on CRAC/CRAH air conditioning units and lowering facility PUE.',
            'Precision Vertical Optics: Customized optical distribution delivers uniform vertical illuminance down server rack faces - eliminating dark spots and eye strain without wasting light on empty floors.',
            'Instant-On Reliability: Zero re-strike delay provides 100% instant visual output during power transfers, generator switchovers, or emergency events.',
        ];
        foreach ($hardware as $i => $tick) {
            $rows[] = ['key' => 'hardware.tick', 'sort_order' => $i, 'value' => $tick];
        }

        $control = [
            'Dynamic Occupancy Sensing: Lights automatically step up from low-power standby mode to full brightness upon technician entry, cutting baseline energy use when aisles are empty.',
            'Environmental Data Monitoring: Integrated sensors capture localized temperature, humidity, and air quality metrics directly at the ceiling grid level.',
            'Workflow & Security Insights: Occupancy heatmaps assist operational planning and support security protocols by tracking movement in restricted zones.',
            'Open-Protocol Control Integration: Seamlessly connect white space lighting with HVAC, access control, and master Building Management Systems (BMS) using open-standard wireless or wired architectures.',
        ];
        foreach ($control as $i => $tick) {
            $rows[] = ['key' => 'control.tick', 'sort_order' => $i, 'value' => $tick];
        }

        $emergency = [
            ['Centralized Emergency Power (CBS)', 'Central addressable emergency battery systems located in climate-controlled utility rooms eliminate heat-related battery degradation, simplify automated testing, and extend service life.'],
            ['High-Temperature Standalone Units', 'Self-contained emergency fittings and self-testing spitfires built with high-temperature battery chemistry for reliable egress illumination.'],
        ];
        foreach ($emergency as $i => [$title, $body]) {
            $rows[] = ['key' => 'emergency.item.title', 'sort_order' => $i, 'value' => $title];
            $rows[] = ['key' => 'emergency.item.body', 'sort_order' => $i, 'value' => $body];
        }

        $zones = [
            ['Data Halls & Server Rooms', 'High-efficiency optical linear trunking engineered for thermal durability across hot and cold aisles.'],
            ['UPS, Switchrooms & Battery Rooms', 'Heavy-duty, IP/IK-rated industrial luminaires built for tough, continuous operational environments.'],
            ['Network Operations Centres (NOC)', 'Glare-free, comfort-focused ambient lighting designed to eliminate screen reflections and eye fatigue (UGR <19).'],
            ['Offices & Circulation', 'Architectural linear profiles, recessed downlights, and perimeter illumination to maintain visual clarity and staff alertness.'],
            ['Exterior & Perimeters', 'High-performance post-tops, bollards, and wall-mounted luminaires engineered to eliminate dark spots for facial recognition and CCTV surveillance.'],
        ];
        foreach ($zones as $i => [$title, $body]) {
            $rows[] = ['key' => 'zones.item.title', 'sort_order' => $i, 'value' => $title];
            $rows[] = ['key' => 'zones.item.body', 'sort_order' => $i, 'value' => $body];
        }

        return $rows;
    }
}
