<?php

namespace App\PageMeta\Definitions;

use App\PageMeta\Field;
use App\PageMeta\PageDefinition;

class SilvairDefinition implements PageDefinition
{
    public function slug(): string
    {
        return 'silvair';
    }

    public function title(): string
    {
        return 'Silvair — Enterprise Bluetooth® Qualified Mesh Lighting | Azoogi';
    }

    public function metaDescription(): string
    {
        return 'Official Silvair integration partner. Bluetooth Mesh and NLC lighting control, rapid mobile commissioning, and energy analytics for commercial, industrial, and education projects.';
    }

    public function navLabel(): string
    {
        return 'Silvair';
    }

    public function fields(): array
    {
        return [
            Field::text('hero.kicker', 'Hero kicker'),
            Field::image('hero.logo', 'Silvair logo'),
            Field::textarea('hero.title', 'Hero title'),
            Field::textarea('hero.lead', 'Hero lead'),
            Field::textarea('intro.body', 'Intro'),
            Field::text('why.heading', 'Why heading'),
            Field::text('why.item.title', 'Why title', true, 'why.item'),
            Field::textarea('why.item.body', 'Why body', true, 'why.item'),
            Field::text('stats.item.value', 'Stat value', true, 'stats.item'),
            Field::text('stats.item.label', 'Stat label', true, 'stats.item'),
            Field::text('pillar.heading', 'Solutions heading'),
            Field::textarea('pillar.lead', 'Solutions lead'),
            Field::text('pillar.item.title', 'Solution title', true, 'pillar.item'),
            Field::textarea('pillar.item.body', 'Solution body', true, 'pillar.item'),
            Field::text('lineup.heading', 'Lineup heading'),
            Field::text('software.heading', 'Software heading'),
            Field::text('software.title', 'Software title'),
            Field::textarea('software.body', 'Software body'),
            Field::image('software.image', 'Software image'),
            Field::text('standard.heading', 'Standard heading'),
            Field::text('standard.title', 'Standard title'),
            Field::textarea('standard.body', 'Standard body'),
            Field::image('standard.image', 'Standard image'),
            Field::text('standard.item.title', 'Standard point title', true, 'standard.item'),
            Field::textarea('standard.item.body', 'Standard point body', true, 'standard.item'),
            Field::text('hardware.heading', 'Hardware heading'),
            Field::text('hardware.col.product', 'Product column'),
            Field::text('hardware.col.type', 'Type column'),
            Field::text('hardware.col.features', 'Features column'),
            Field::text('hardware.row.product', 'Product', true, 'hardware.row'),
            Field::text('hardware.row.type', 'Type', true, 'hardware.row'),
            Field::textarea('hardware.row.features', 'Key features', true, 'hardware.row'),
            Field::image('hardware.row.image', 'Product image', true, 'hardware.row'),
            Field::text('apps.heading', 'Applications heading'),
            Field::textarea('apps.lead', 'Applications lead'),
            Field::text('apps.item.title', 'Application title', true, 'apps.item'),
            Field::textarea('apps.item.body', 'Application body', true, 'apps.item'),
            Field::image('apps.item.image', 'Application image', true, 'apps.item'),
            Field::text('flow.heading', 'How it works heading'),
            Field::textarea('flow.lead', 'How it works lead'),
            Field::text('flow.item.title', 'Step title', true, 'flow.item'),
            Field::textarea('flow.item.body', 'Step body', true, 'flow.item'),
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
            ['key' => 'hero.kicker', 'sort_order' => 0, 'value' => 'Azoogi X Silvair'],
            ['key' => 'hero.logo', 'sort_order' => 0, 'value' => '/assets/img/silvair/logo.svg'],
            ['key' => 'hero.title', 'sort_order' => 0, 'value' => 'Enterprise Bluetooth® Qualified Mesh Lighting'],
            ['key' => 'hero.lead', 'sort_order' => 0, 'value' => 'Qualified Mesh Standard. Rapid Mobile Commissioning. Intelligent Energy Analytics.'],
            ['key' => 'intro.body', 'sort_order' => 0, 'value' => 'As an official integration partner for Silvair, we deliver robust, interoperable Bluetooth® Mesh lighting control solutions for commercial real estate, industrial facilities, and educational institutions. Silvair’s software-driven architecture enables wireless fixture-level control, automated energy code compliance, and advanced building data analytics without complex control wiring or central servers.'],
            ['key' => 'why.heading', 'sort_order' => 0, 'value' => 'Why Choose Silvair Wireless Controls?'],
            ['key' => 'pillar.heading', 'sort_order' => 0, 'value' => 'Six connected solutions, one open platform.'],
            ['key' => 'pillar.lead', 'sort_order' => 0, 'value' => 'From a single light point to a portfolio-wide BMS integration — everything runs on the same wireless mesh and the same cloud.'],
            ['key' => 'lineup.heading', 'sort_order' => 0, 'value' => 'Key Silvair Platform Capabilities'],
            ['key' => 'software.heading', 'sort_order' => 0, 'value' => 'Software & Cloud Ecosystem'],
            ['key' => 'software.title', 'sort_order' => 0, 'value' => 'Mobile for installers. Web for managers.'],
            ['key' => 'software.body', 'sort_order' => 0, 'value' => 'Commission thousands of nodes with the Silvair iOS app, then manage projects, energy and network health in Silvair Cloud. Installers scan QR codes, place devices on floor plans and assign zones in minutes. Facility managers monitor occupancy, compliance and energy from anywhere — with remote health checks and downloadable reports.'],
            ['key' => 'software.image', 'sort_order' => 0, 'value' => '/assets/img/silvair/software.jpg'],
            ['key' => 'standard.heading', 'sort_order' => 0, 'value' => 'The Bluetooth® NLC Standard'],
            ['key' => 'standard.title', 'sort_order' => 0, 'value' => 'The only interoperable standard for wireless lighting control.'],
            ['key' => 'standard.body', 'sort_order' => 0, 'value' => 'Built on the official global Bluetooth® Mesh and Networked Lighting Control specification, Silvair eliminates proprietary lock-in. Every Azoogi installation works with certified luminaires, sensors and switches from any manufacturer — no proprietary islands.'],
            ['key' => 'standard.image', 'sort_order' => 0, 'value' => '/assets/img/silvair/nlc.jpg'],
            ['key' => 'hardware.heading', 'sort_order' => 0, 'value' => 'Hardware Compatibility & Ecosystem Integration'],
            ['key' => 'hardware.col.product', 'sort_order' => 0, 'value' => 'Category'],
            ['key' => 'hardware.col.type', 'sort_order' => 0, 'value' => 'Key Components'],
            ['key' => 'hardware.col.features', 'sort_order' => 0, 'value' => 'Application'],
            ['key' => 'apps.heading', 'sort_order' => 0, 'value' => 'Built for every commercial space.'],
            ['key' => 'apps.lead', 'sort_order' => 0, 'value' => 'Offices, warehouses, schools, retail and underground car parks — one platform, every environment.'],
            ['key' => 'flow.heading', 'sort_order' => 0, 'value' => 'From sensor to BMS in four steps.'],
            ['key' => 'flow.lead', 'sort_order' => 0, 'value' => 'Every Silvair-powered installation follows the same simple, open architecture — whether it is one room or one million devices.'],
            ['key' => 'support.heading', 'sort_order' => 0, 'value' => 'Complete Project & Integration Services'],
            ['key' => 'support.lead', 'sort_order' => 0, 'value' => 'Partnering with us for your Silvair wireless lighting deployments ensures a smooth transition from design to long-term operation.'],
            ['key' => 'cta.heading', 'sort_order' => 0, 'value' => 'Bring Silvair to your next building — with Azoogi.'],
            ['key' => 'cta.body', 'sort_order' => 0, 'value' => 'Talk to our team about pilot deployments, retrofits and portfolio rollouts across Australia. Whether you need Silvair-ready hardware, network design, or a complete commissioning quote, we are ready to assist.'],
            ['key' => 'cta.label', 'sort_order' => 0, 'value' => 'Request a Silvair Project Quote'],
            ['key' => 'cta.href', 'sort_order' => 0, 'value' => '/contact'],
        ];

        $why = [
            ['Bluetooth® Mesh Standard:', 'Built on the official global Bluetooth® Mesh specification, eliminating proprietary lock-in and ensuring seamless cross-vendor hardware interoperability.'],
            ['Rapid Mobile Commissioning:', 'Commission thousands of nodes efficiently using the Silvair iOS app and cloud platform, drastically cutting installation labor and system setup time.'],
            ['Qualified Luminaire Level Lighting Control (LLLC):', 'Native support for occupancy sensing, daylight harvesting, task tuning, and high-end trim directly integrated into individual fixtures.'],
            ['Data-Driven Building Intelligence:', 'Leverage occupancy and energy consumption data via the Silvair Cloud platform to optimize facility operations and reduce carbon footprint.'],
        ];
        foreach ($why as $i => [$title, $body]) {
            $rows[] = ['key' => 'why.item.title', 'sort_order' => $i, 'value' => $title];
            $rows[] = ['key' => 'why.item.body', 'sort_order' => $i, 'value' => $body];
        }

        $stats = [
            ['1M+', 'Devices managed by the Silvair platform'],
            ['100%', 'Open Bluetooth® NLC standard'],
            ['30+', 'Certified vendor partners worldwide'],
        ];
        foreach ($stats as $i => [$value, $label]) {
            $rows[] = ['key' => 'stats.item.value', 'sort_order' => $i, 'value' => $value];
            $rows[] = ['key' => 'stats.item.label', 'sort_order' => $i, 'value' => $label];
        }

        $pillars = [
            ['Lighting Control', 'Occupancy, daylight, scenes and tunable white across every space — commissioned in hours, not weeks.'],
            ['HVAC Control', 'The first Bluetooth® NLC HVAC Integration profile — one mesh for lighting and climate.'],
            ['Emergency Lighting Testing', 'Automated functional and duration tests with audit-ready reports for AS/NZS 2293 and beyond.'],
            ['Monitoring Services', 'Live energy, occupancy and asset health analytics — turn lighting into a data layer.'],
            ['BMS Integration', 'Plug into BACnet, Modbus and modern BMS stacks with native connectors and gateways.'],
            ['Open API', 'Build custom dashboards, tenant apps and digital-twin pipelines on top of Silvair’s cloud API.'],
        ];
        foreach ($pillars as $i => [$title, $body]) {
            $rows[] = ['key' => 'pillar.item.title', 'sort_order' => $i, 'value' => $title];
            $rows[] = ['key' => 'pillar.item.body', 'sort_order' => $i, 'value' => $body];
        }

        $standard = [
            ['Multi-vendor by default', 'Mix and match certified devices — today and 10 years from now.'],
            ['Wireless mesh', 'Self-healing radio network that scales from a small office to a campus.'],
            ['Cloud + edge', 'Local control even when the cloud is offline; cloud analytics when it is not.'],
        ];
        foreach ($standard as $i => [$title, $body]) {
            $rows[] = ['key' => 'standard.item.title', 'sort_order' => $i, 'value' => $title];
            $rows[] = ['key' => 'standard.item.body', 'sort_order' => $i, 'value' => $body];
        }

        $hardware = [
            ['Silvair Ready Components', 'Fixture controllers, sensors & wall switches', 'Fully interoperable Bluetooth-enabled hardware from leading global manufacturers.', '/assets/img/silvair/office.jpg'],
            ['Emergency & DALI-2 Bridge', 'DALI drivers, emergency testing, 0-10V', 'Seamless integration with standard DALI, automated emergency lighting tests, and 0-10V gear.', '/assets/img/silvair/emergency.jpg'],
            ['Gateways & Cloud', 'Silvair Gateway', 'Forward fixture data for analytics, reports, remote management, and BMS connectors.', ''],
            ['Open API', 'REST / BACnet / Modbus', 'Custom dashboards, tenant apps, and digital-twin pipelines on Silvair’s cloud API.', ''],
        ];
        foreach ($hardware as $i => [$product, $type, $features, $image]) {
            $rows[] = ['key' => 'hardware.row.product', 'sort_order' => $i, 'value' => $product];
            $rows[] = ['key' => 'hardware.row.type', 'sort_order' => $i, 'value' => $type];
            $rows[] = ['key' => 'hardware.row.features', 'sort_order' => $i, 'value' => $features];
            $rows[] = ['key' => 'hardware.row.image', 'sort_order' => $i, 'value' => $image];
        }

        $apps = [
            ['Offices', 'Occupancy-driven lighting, HVAC integration and tenant analytics.', '/assets/img/silvair/office.jpg'],
            ['Warehouses', 'Aisle-by-aisle dimming and real-time energy monitoring.', '/assets/img/silvair/warehouse.jpg'],
            ['Schools', 'Tunable white classrooms with simple, secure mobile control.', '/assets/img/silvair/classroom.jpg'],
            ['Car parks', 'Motion-activated lighting that cuts energy by up to 80%.', '/assets/img/silvair/carpark.jpg'],
        ];
        foreach ($apps as $i => [$title, $body, $image]) {
            $rows[] = ['key' => 'apps.item.title', 'sort_order' => $i, 'value' => $title];
            $rows[] = ['key' => 'apps.item.body', 'sort_order' => $i, 'value' => $body];
            $rows[] = ['key' => 'apps.item.image', 'sort_order' => $i, 'value' => $image];
        }

        $flow = [
            ['Sense', 'NLC-certified luminaires, sensors and switches detect occupancy, daylight and temperature.'],
            ['Mesh', 'Devices talk to each other on a self-healing Bluetooth® mesh — no central controller required.'],
            ['Cloud', 'Silvair gateways forward data to a secure cloud for analytics, reports and remote management.'],
            ['Integrate', 'Push live data to your BMS, dashboards or custom apps via BACnet, Modbus or open API.'],
        ];
        foreach ($flow as $i => [$title, $body]) {
            $rows[] = ['key' => 'flow.item.title', 'sort_order' => $i, 'value' => $title];
            $rows[] = ['key' => 'flow.item.body', 'sort_order' => $i, 'value' => $body];
        }

        $support = [
            ['Network Design & Planning:', 'Pre-commissioning network layout, node density mapping, and zone planning using the Silvair Cloud desktop platform.'],
            ['Local Hardware Supply:', 'Direct fulfillment of Silvair-enabled wireless controllers, occupancy sensors, daylight photocells, and wall switches.'],
            ['On-Site & Remote Commissioning:', 'Expert support for network setup, energy code compliance verification (Title 24, ASHRAE, IECC), and end-user system handoff.'],
        ];
        foreach ($support as $i => [$title, $body]) {
            $rows[] = ['key' => 'support.item.title', 'sort_order' => $i, 'value' => $title];
            $rows[] = ['key' => 'support.item.body', 'sort_order' => $i, 'value' => $body];
        }

        return $rows;
    }
}
