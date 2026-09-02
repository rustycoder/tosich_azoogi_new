<?php

namespace App\PageMeta\Definitions;

use App\PageMeta\Field;
use App\PageMeta\PageDefinition;

class MadrixDefinition implements PageDefinition
{
    public function slug(): string
    {
        return 'madrix';
    }

    public function title(): string
    {
        return 'MADRIX — Next-Generation Pixel Mapping & Advanced LED Control | Azoogi';
    }

    public function metaDescription(): string
    {
        return 'Official MADRIX partner. Pixel-mapping software and hardware interfaces for architectural lighting, event stages, and immersive environments — with local design, stock, and support.';
    }

    public function navLabel(): string
    {
        return 'MADRIX';
    }

    public function fields(): array
    {
        return [
            Field::image('slide.image', 'Slide image', true, 'slide'),
            Field::text('slide.alt', 'Slide alt', true, 'slide'),
            Field::text('hero.kicker', 'Hero kicker'),
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
            ['key' => 'hero.kicker', 'sort_order' => 0, 'value' => 'Madrix'],
            ['key' => 'hero.title', 'sort_order' => 0, 'value' => 'Next-Generation Pixel Mapping & Advanced LED Control Solutions'],
            ['key' => 'hero.lead', 'sort_order' => 0, 'value' => 'Powerful German Engineering. Seamless Spatial Lighting Integration.'],
            ['key' => 'intro.body', 'sort_order' => 0, 'value' => 'As an official partner of MADRIX, we bring industry-leading pixel-mapping software and hardware interfaces directly to your projects. Whether you are designing dynamic architectural lighting, large-scale event stages, or custom immersive environments, MADRIX delivers rock-solid control over thousands of DMX/SPI universes with real-time audio reactivity.'],
            ['key' => 'why.heading', 'sort_order' => 0, 'value' => 'Why Choose MADRIX?'],
            ['key' => 'lineup.heading', 'sort_order' => 0, 'value' => 'The MADRIX Product Lineup'],
            ['key' => 'software.heading', 'sort_order' => 0, 'value' => '1. Software Solutions'],
            ['key' => 'software.title', 'sort_order' => 0, 'value' => 'MADRIX 5 (Software):'],
            ['key' => 'software.body', 'sort_order' => 0, 'value' => 'The ultimate pixel-mapping engine. Generates state-of-the-art visual effects, media playback, and custom lighting patterns with ultra-intuitive user controls.'],
            ['key' => 'software.image', 'sort_order' => 0, 'value' => '/assets/img/madrix/software.png'],
            ['key' => 'hardware.heading', 'sort_order' => 0, 'value' => '2. Hardware Interfaces & Controllers'],
            ['key' => 'hardware.col.product', 'sort_order' => 0, 'value' => 'Product'],
            ['key' => 'hardware.col.type', 'sort_order' => 0, 'value' => 'Type'],
            ['key' => 'hardware.col.features', 'sort_order' => 0, 'value' => 'Key Features'],
            ['key' => 'support.heading', 'sort_order' => 0, 'value' => 'Design, Integration & Local Support'],
            ['key' => 'support.lead', 'sort_order' => 0, 'value' => 'When you partner with us for your MADRIX deployments, you get complete project support from concept to turn-on:'],
            ['key' => 'video.embed', 'sort_order' => 0, 'value' => 'https://www.youtube.com/watch?v=QELQAZu-46M'],
            ['key' => 'cta.heading', 'sort_order' => 0, 'value' => 'Ready to Bring Your Lighting Designs to Life?'],
            ['key' => 'cta.body', 'sort_order' => 0, 'value' => 'Whether you need a software license key, hardware nodes, or a complete system design quote, our engineering team is ready to assist.'],
            ['key' => 'cta.label', 'sort_order' => 0, 'value' => 'Request a MADRIX Quote'],
            ['key' => 'cta.href', 'sort_order' => 0, 'value' => '/contact'],
        ];

        $slides = [
            ['/assets/img/madrix/banner.png', 'MADRIX pixel-mapping lighting installation'],
            ['/assets/img/madrix/bellagio.jpg', 'MADRIX architectural lighting at a hospitality venue'],
            ['/assets/img/madrix/ploenchit.jpg', 'MADRIX facade lighting installation'],
            ['/assets/img/madrix/pullman.jpg', 'MADRIX interior lighting at Pullman Berlin'],
            ['/assets/img/madrix/kantine.jpg', 'MADRIX immersive lighting in a hospitality space'],
            ['/assets/img/madrix/strike.jpg', 'MADRIX dynamic lighting installation'],
        ];
        foreach ($slides as $i => [$image, $alt]) {
            $rows[] = ['key' => 'slide.image', 'sort_order' => $i, 'value' => $image];
            $rows[] = ['key' => 'slide.alt', 'sort_order' => $i, 'value' => $alt];
        }

        $why = [
            ['Real-Time 2D & 3D Pixel Mapping:', 'Easily control LEDs in two or three dimensions—from simple video walls to complex, custom 3D structures.'],
            ['Integrated Hardware & Software Ecosystem:', 'Purpose-built software pairs directly with robust, high-performance hardware for zero-latency data output.'],
            ['Audio-Reactive Visuals:', 'Generate dynamic lighting effects automatically driven by live sound inputs or ambient music.'],
            ['Global Protocol Support:', 'Native compatibility with Art-Net, sACN, DMX512, SPI, and third-party media servers.'],
        ];
        foreach ($why as $i => [$title, $body]) {
            $rows[] = ['key' => 'why.item.title', 'sort_order' => $i, 'value' => $title];
            $rows[] = ['key' => 'why.item.body', 'sort_order' => $i, 'value' => $body];
        }

        $hardware = [
            ['MADRIX NEBULA', 'SPI Direct Decoder', 'Directly drives digital pixel tape/dots via SPI over Ethernet or USB.', '/assets/img/madrix/nebula.png'],
            ['MADRIX STELLA', 'DMX/Art-Net Interface', '2-port network node engineered specifically for solid permanent installations.', '/assets/img/madrix/stella.png'],
            ['MADRIX LUNA', 'High-Density Node', 'Reliable Art-Net/sACN to DMX distribution with built-in sync mode.', '/assets/img/madrix/luna.png'],
            ['MADRIX AURA', 'Stand-Alone Recorder', 'Central playback unit for recording and running large pixel-mapping shows without a PC.', '/assets/img/madrix/aura.png'],
            ['MADRIX ORION', 'Sensor Input Interface', 'Converts analog inputs (sensors, switches) into Ethernet data for interactive lighting.', '/assets/img/madrix/orion.png'],
        ];
        foreach ($hardware as $i => [$product, $type, $features, $image]) {
            $rows[] = ['key' => 'hardware.row.product', 'sort_order' => $i, 'value' => $product];
            $rows[] = ['key' => 'hardware.row.type', 'sort_order' => $i, 'value' => $type];
            $rows[] = ['key' => 'hardware.row.features', 'sort_order' => $i, 'value' => $features];
            $rows[] = ['key' => 'hardware.row.image', 'sort_order' => $i, 'value' => $image];
        }

        $support = [
            ['System Design & Specifying:', 'Custom hardware and software license configuration tailored to your fixture counts and universe requirements.'],
            ['Local Stock & Fast Delivery:', 'Direct access to MADRIX hardware inventory for quick turnarounds.'],
            ['Technical Support & Training:', 'Local commissioning support, system configuration, and software training for your operators.'],
        ];
        foreach ($support as $i => [$title, $body]) {
            $rows[] = ['key' => 'support.item.title', 'sort_order' => $i, 'value' => $title];
            $rows[] = ['key' => 'support.item.body', 'sort_order' => $i, 'value' => $body];
        }

        return $rows;
    }
}
