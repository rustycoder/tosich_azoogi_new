<?php

namespace App\PageMeta\Definitions;

use App\PageMeta\Field;
use App\PageMeta\PageDefinition;

class CasambiDefinition implements PageDefinition
{
    public function slug(): string
    {
        return 'casambi';
    }

    public function title(): string
    {
        return 'Casambi — Advanced Wireless Lighting Control & Smart Ecosystems | Azoogi';
    }

    public function metaDescription(): string
    {
        return 'Official Casambi technology and distribution partner. Scalable Bluetooth mesh lighting control, sensors, switches, and commissioning for architectural, commercial, and industrial projects.';
    }

    public function navLabel(): string
    {
        return 'Casambi';
    }

    public function fields(): array
    {
        return [
            Field::image('slide.image', 'Slide image', true, 'slide'),
            Field::text('slide.alt', 'Slide alt', true, 'slide'),
            Field::text('hero.kicker', 'Hero kicker'),
            Field::image('hero.logo', 'Casambi logo'),
            Field::textarea('hero.title', 'Hero title'),
            Field::textarea('hero.lead', 'Hero lead'),
            Field::textarea('intro.body', 'Intro'),
            Field::text('why.heading', 'Why heading'),
            Field::text('why.item.title', 'Why title', true, 'why.item'),
            Field::textarea('why.item.body', 'Why body', true, 'why.item'),
            Field::text('lineup.heading', 'Lineup heading'),
            Field::text('software.heading', 'Software heading'),
            Field::text('software.title', 'Software title'),
            Field::textarea('software.body', 'Software body'),
            Field::image('software.image', 'Software image'),
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
            Field::url('video.embed', 'YouTube URL'),
            Field::textarea('cta.heading', 'CTA heading'),
            Field::textarea('cta.body', 'CTA body'),
            Field::text('cta.label', 'CTA label'),
            Field::url('cta.href', 'CTA href'),
        ];
    }

    public function seed(): array
    {
        $rows = [
            ['key' => 'hero.kicker', 'sort_order' => 0, 'value' => 'Azoogi X Casambi'],
            ['key' => 'hero.logo', 'sort_order' => 0, 'value' => '/assets/img/casambi/logo-dark.svg'],
            ['key' => 'hero.title', 'sort_order' => 0, 'value' => 'Advanced Wireless Lighting Control & Smart Ecosystems'],
            ['key' => 'hero.lead', 'sort_order' => 0, 'value' => 'Scalable Bluetooth Mesh Technology. Standardized Luminaire Integration.'],
            ['key' => 'intro.body', 'sort_order' => 0, 'value' => 'As an official Casambi technology and distribution partner, we bring intelligent, ultra-reliable Bluetooth Low Energy (BLE) wireless lighting controls directly to your architectural, commercial, and industrial projects. Casambi creates a self-healing mesh network that eliminates single points of failure, requiring no complex control wiring or central servers.'],
            ['key' => 'why.heading', 'sort_order' => 0, 'value' => 'Why Choose Casambi?'],
            ['key' => 'lineup.heading', 'sort_order' => 0, 'value' => 'Casambi App & Product Categories'],
            ['key' => 'software.heading', 'sort_order' => 0, 'value' => 'Casambi App'],
            ['key' => 'software.title', 'sort_order' => 0, 'value' => 'Configure. Control. Automate.'],
            ['key' => 'software.body', 'sort_order' => 0, 'value' => 'Commission and automate lighting with Casambi App or Casambi Pro. Use Casambi App for intuitive, complete system control without complex programming. Choose Casambi Pro for large-scale projects with repetitive functionality, using floorplans, templates, batch programming and remote commissioning.'],
            ['key' => 'software.image', 'sort_order' => 0, 'value' => '/assets/img/casambi/software.png'],
            ['key' => 'hardware.heading', 'sort_order' => 0, 'value' => 'Key Casambi Product Categories'],
            ['key' => 'hardware.col.product', 'sort_order' => 0, 'value' => 'Category'],
            ['key' => 'hardware.col.type', 'sort_order' => 0, 'value' => 'Key Components'],
            ['key' => 'hardware.col.features', 'sort_order' => 0, 'value' => 'Application'],
            ['key' => 'support.heading', 'sort_order' => 0, 'value' => 'Full System Specification & Commissioning'],
            ['key' => 'support.lead', 'sort_order' => 0, 'value' => ''],
            ['key' => 'video.embed', 'sort_order' => 0, 'value' => ''],
            ['key' => 'cta.heading', 'sort_order' => 0, 'value' => 'Ready to specify Casambi for your project?'],
            ['key' => 'cta.body', 'sort_order' => 0, 'value' => 'Whether you need CBU modules, sensors and switches, or a complete wireless mesh design quote, our team is ready to assist.'],
            ['key' => 'cta.label', 'sort_order' => 0, 'value' => 'Request a Casambi Quote'],
            ['key' => 'cta.href', 'sort_order' => 0, 'value' => '/contact'],
            ['key' => 'slide.image', 'sort_order' => 0, 'value' => '/assets/img/casambi/banner.png'],
            ['key' => 'slide.alt', 'sort_order' => 0, 'value' => 'Casambi app — configure, control, and automate lighting from a smartphone'],
        ];

        $why = [
            ['No Control Wiring Required:', 'Operates over a secure 2.4 GHz Bluetooth mesh—dramatically reducing installation labor and material costs.'],
            ['Interoperable Ecosystem:', 'Native support across thousands of "Casambi Ready" luminaires, sensors, switches, and LED drivers from top global brands.'],
            ['Intuitive iOS & Android App:', 'Commission, group, schedule, and create scene presets directly from a smartphone or tablet.'],
            ['DALI & Sensor Integration:', 'Seamlessly bridge wireless mesh controls with wired DALI systems, gateways, and building management systems (BMS).'],
        ];
        foreach ($why as $i => [$title, $body]) {
            $rows[] = ['key' => 'why.item.title', 'sort_order' => $i, 'value' => $title];
            $rows[] = ['key' => 'why.item.body', 'sort_order' => $i, 'value' => $body];
        }

        $hardware = [
            ['CBU Modules', 'CBU-ASD, CBU-PWM4, CBU-TED', 'Bluetooth-to-DALI/0-10V controllers, phase-cut dimmers, and LED strip drivers.', '/assets/img/casambi/cbu-asd-lr.png'],
            ['Sensors & Switches', 'Wireless switches, occupancy & daylight sensors', 'Battery-free EnOcean switches and wireless motion detection nodes.', '/assets/img/casambi/xpress-lr.jpg'],
            ['Gateways & Interface', 'Casambi Gateway', 'Remote access, cloud synchronization, and multi-site network monitoring.', '/assets/img/casambi/cloud-gateway.png'],
            ['OEM Integration', 'Integrated Casambi-ready drivers & modules', 'Embedded wireless control inside third-party fixtures.', '/assets/img/casambi/cbm-003.png'],
        ];
        foreach ($hardware as $i => [$product, $type, $features, $image]) {
            $rows[] = ['key' => 'hardware.row.product', 'sort_order' => $i, 'value' => $product];
            $rows[] = ['key' => 'hardware.row.type', 'sort_order' => $i, 'value' => $type];
            $rows[] = ['key' => 'hardware.row.features', 'sort_order' => $i, 'value' => $features];
            $rows[] = ['key' => 'hardware.row.image', 'sort_order' => $i, 'value' => $image];
        }

        $support = [
            ['Design & Network Layout:', 'Network topology planning, node placement, and signal density specification.'],
            ['Local Product Supply:', 'Direct fulfillment of CBU modules, sensors, and Casambi-enabled hardware.'],
            ['Commissioning & Setup:', 'On-site or remote network programming, scene setting, and end-user handoff.'],
        ];
        foreach ($support as $i => [$title, $body]) {
            $rows[] = ['key' => 'support.item.title', 'sort_order' => $i, 'value' => $title];
            $rows[] = ['key' => 'support.item.body', 'sort_order' => $i, 'value' => $body];
        }

        return $rows;
    }
}
