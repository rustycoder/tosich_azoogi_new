<?php

namespace App\PageMeta\Definitions;

use App\PageMeta\Field;
use App\PageMeta\PageDefinition;

class DaliCentreDefinition implements PageDefinition
{
    public function slug(): string
    {
        return 'dali-centre';
    }

    public function title(): string
    {
        return 'AZOOGI DALI Centre — Centralized Architectural Lighting & Smart DALI-2 Management';
    }

    public function metaDescription(): string
    {
        return 'AZOOGI DALI Centre centralized DALI-2 lighting management: IP gateways, DT8 colour and tunable white, scheduling, energy analytics, and local commissioning for public and commercial buildings.';
    }

    public function navLabel(): string
    {
        return 'DALI Centre';
    }

    public function fields(): array
    {
        return [
            Field::text('hero.kicker', 'Hero kicker'),
            Field::textarea('hero.title', 'Hero title'),
            Field::textarea('hero.lead', 'Hero lead'),
            Field::textarea('intro.body', 'Intro'),
            Field::url('video.embed', 'YouTube URL'),
            Field::text('feature.heading', 'Diagram heading'),
            Field::textarea('feature.lead', 'Diagram lead'),
            Field::image('feature.image', 'System diagram'),
            Field::text('feature.item.title', 'Diagram point title', true, 'feature.item'),
            Field::textarea('feature.item.body', 'Diagram point body', true, 'feature.item'),
            Field::text('why.heading', 'Why heading'),
            Field::text('why.item.title', 'Why title', true, 'why.item'),
            Field::textarea('why.item.body', 'Why body', true, 'why.item'),
            Field::text('hardware.heading', 'Hardware heading'),
            Field::text('hardware.col.product', 'Product column'),
            Field::text('hardware.col.type', 'Type column'),
            Field::text('hardware.col.features', 'Features column'),
            Field::text('hardware.row.product', 'Product', true, 'hardware.row'),
            Field::text('hardware.row.type', 'Type', true, 'hardware.row'),
            Field::textarea('hardware.row.features', 'Key features', true, 'hardware.row'),
            Field::image('hardware.row.image', 'Product image', true, 'hardware.row'),
            Field::text('support.heading', 'Support heading'),
            Field::textarea('support.lead', 'Support lead'),
            Field::text('support.item.title', 'Support title', true, 'support.item'),
            Field::textarea('support.item.body', 'Support body', true, 'support.item'),
            Field::textarea('cta.heading', 'CTA heading'),
            Field::textarea('cta.body', 'CTA body'),
            Field::text('cta.label', 'CTA label'),
            Field::url('cta.href', 'CTA href'),
        ];
    }

    public function seed(): array
    {
        $rows = [
            ['key' => 'hero.kicker', 'sort_order' => 0, 'value' => 'AZOOGI DALI Centre'],
            ['key' => 'hero.title', 'sort_order' => 0, 'value' => 'Centralized Architectural Lighting & Smart DALI-2 Management'],
            ['key' => 'hero.lead', 'sort_order' => 0, 'value' => 'Scalable IP Gateways. Precision Local Control. Comprehensive Energy Analytics.'],
            ['key' => 'intro.body', 'sort_order' => 0, 'value' => 'A powerful centralized management platform designed for public buildings, commercial offices, and complex architectural spaces. Paired with our certified IP gateways, controllers, and sensor networks, AZOOGI DALI Centre offers total command over single-fixture or multi-floor lighting infrastructures.'],
            ['key' => 'video.embed', 'sort_order' => 0, 'value' => 'https://youtu.be/C0KcmW6NewI'],
            ['key' => 'feature.heading', 'sort_order' => 0, 'value' => 'The complete DALI bus, from sensors to BMS.'],
            ['key' => 'feature.lead', 'sort_order' => 0, 'value' => 'One powered DALI line carries control and status. Inputs sit on the left of the diagram, management and loads on the right — everything shares the same bus.'],
            ['key' => 'feature.image', 'sort_order' => 0, 'value' => '/assets/img/dali-system/DALI system.png'],
            ['key' => 'why.heading', 'sort_order' => 0, 'value' => 'Why Choose AZOOGI DALI Centre?'],
            ['key' => 'hardware.heading', 'sort_order' => 0, 'value' => 'Core Hardware & Gateway System Components'],
            ['key' => 'hardware.col.product', 'sort_order' => 0, 'value' => 'Component'],
            ['key' => 'hardware.col.type', 'sort_order' => 0, 'value' => 'Series'],
            ['key' => 'hardware.col.features', 'sort_order' => 0, 'value' => 'Role'],
            ['key' => 'support.heading', 'sort_order' => 0, 'value' => 'System Design & Local Support Services'],
            ['key' => 'support.lead', 'sort_order' => 0, 'value' => 'Partnering with us for your AZOOGI deployments ensures end-to-end reliability from initial design through final commissioning.'],
            ['key' => 'cta.heading', 'sort_order' => 0, 'value' => 'Ready to specify DALI Centre for your project?'],
            ['key' => 'cta.body', 'sort_order' => 0, 'value' => 'Whether you need IP gateways, DALI-2 sensors, or a complete centralized lighting management quote, our team is ready to assist.'],
            ['key' => 'cta.label', 'sort_order' => 0, 'value' => 'Request a DALI Centre Quote'],
            ['key' => 'cta.href', 'sort_order' => 0, 'value' => '/contact'],
        ];

        $why = [
            ['Centralized Building Management:', 'Deploy locally to monitor, schedule, and configure addressing across large-scale commercial lighting environments.'],
            ['Full DT8 Color & Tunable White Support:', 'Complete control over DALI-2 DT6 (single color), DT8 Tc (tunable white), and DT8 RGBWA devices.'],
            ['Automated Scheduling & Energy Analytics:', 'Integrated Real-Time Clock (RTC) for time-based tasks and real-time power consumption metrics.'],
            ['Standardized DALI-2 Compatibility:', 'Fully compliant with DiiA/DALI-2 standards, ensuring smooth integration with third-party DALI control gear, sensors, and switches.'],
        ];
        foreach ($why as $i => [$title, $body]) {
            $rows[] = ['key' => 'why.item.title', 'sort_order' => $i, 'value' => $title];
            $rows[] = ['key' => 'why.item.body', 'sort_order' => $i, 'value' => $body];
        }

        $diagram = [
            ['Power the bus', 'The DALI power supply at the top energizes the line — typically 16V DC with a 250mA current limit so every node can talk reliably.'],
            ['Inputs on the left', 'Wireless-to-DALI converters (Wi-Fi, Bluetooth, Zigbee, Matter), wall and rotary switches, DALI-2 / D4i sensors, and a KNX gateway all inject commands onto the same bus.'],
            ['Management on the right', 'DALI Master and DALI Centre sit on laptops for addressing, groups and scenes. An IoT gateway extends the same network to a phone for remote checks.'],
            ['Loads on the bus', 'DT8 LED controllers, NFC / D4i drivers and relays take those commands out to strips, downlights and switched circuits — still on one DALI backbone.'],
        ];
        foreach ($diagram as $i => [$title, $body]) {
            $rows[] = ['key' => 'feature.item.title', 'sort_order' => $i, 'value' => $title];
            $rows[] = ['key' => 'feature.item.body', 'sort_order' => $i, 'value' => $body];
        }

        $hardware = [
            ['AZOOGI Ethernet DALI Gateway', 'EDA / PRO Series', 'DIN-rail IP interface connecting local networks to DALI channels with built-in power supply.'],
            ['AZOOGI Multi-Protocol Gateways', 'ZDA Series', 'Hybrids bridging DALI-2 to Zigbee, Bluetooth, and IoT platforms.'],
            ['AZOOGI DALI Masters & Keypads', 'Wall controls', 'Wall touch panels, push-button couplers, and rotary masters for room-level manual overrides.'],
            ['AZOOGI Sensors & Input Modules', 'DALI-2 certified', 'DALI-2 certified PIR motion and daylight harvesting photocell sensors.'],
        ];
        foreach ($hardware as $i => [$product, $type, $features]) {
            $rows[] = ['key' => 'hardware.row.product', 'sort_order' => $i, 'value' => $product];
            $rows[] = ['key' => 'hardware.row.type', 'sort_order' => $i, 'value' => $type];
            $rows[] = ['key' => 'hardware.row.features', 'sort_order' => $i, 'value' => $features];
        }

        $support = [
            ['Topology & Bus Calculation:', 'Channel-by-channel DALI power supply and current load planning (250mA bus limit checks).'],
            ['Local Hardware Supply:', 'Fast regional shipping on gateways, relay modules, and phase-cut dimmers.'],
            ['Commissioning & DALI Centre Setup:', 'Device discovery, group assignment, scene programming, and building management system (BMS) handoff.'],
        ];
        foreach ($support as $i => [$title, $body]) {
            $rows[] = ['key' => 'support.item.title', 'sort_order' => $i, 'value' => $title];
            $rows[] = ['key' => 'support.item.body', 'sort_order' => $i, 'value' => $body];
        }

        return $rows;
    }
}
