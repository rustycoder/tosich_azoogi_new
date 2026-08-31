<?php

namespace App\PageMeta\Definitions;

use App\PageMeta\Field;
use App\PageMeta\PageDefinition;

class AiLightingDefinition implements PageDefinition
{
    public function slug(): string
    {
        return 'ai-lighting';
    }

    public function title(): string
    {
        return 'AI Lighting — Intelligent Retail Lighting | Azoogi';
    }

    public function metaDescription(): string
    {
        return 'Azoogi AI lighting for retail: adaptive spectrum, colour temperature, space sensing, and store data insights — beyond illumination.';
    }

    public function navLabel(): string
    {
        return 'AI Lighting';
    }

    public function fields(): array
    {
        return [
            Field::text('hero.kicker', 'Hero kicker'),
            Field::textarea('hero.title', 'Hero title'),
            Field::textarea('hero.lead', 'Hero lead'),
            Field::image('hero.image', 'Hero image'),
            Field::text('caps.kicker', 'Caps kicker'),
            Field::textarea('caps.heading', 'Caps heading'),
            Field::textarea('caps.body', 'Caps body'),
            Field::text('caps.item.title', 'Capability', true, 'caps.item'),
            Field::text('spectrum.kicker', 'Spectrum kicker'),
            Field::textarea('spectrum.heading', 'Spectrum heading'),
            Field::textarea('spectrum.body', 'Spectrum body'),
            Field::text('spectrum.tick', 'Spectrum tick', true, 'spectrum.tick'),
            Field::image('spectrum.compare.traditional.image', 'Traditional image'),
            Field::text('spectrum.compare.traditional.caption', 'Traditional caption'),
            Field::image('spectrum.compare.ai.image', 'AI image'),
            Field::text('spectrum.compare.ai.caption', 'AI caption'),
            Field::text('insights.kicker', 'Insights kicker'),
            Field::textarea('insights.heading', 'Insights heading'),
            Field::textarea('insights.lead', 'Insights lead'),
            Field::text('insights.item.title', 'Insight title', true, 'insights.item'),
            Field::textarea('insights.item.body', 'Insight body', true, 'insights.item'),
            Field::image('insights.item.image', 'Insight image', true, 'insights.item'),
            Field::text('cct.kicker', 'CCT kicker'),
            Field::textarea('cct.heading', 'CCT heading'),
            Field::textarea('cct.body', 'CCT body'),
            Field::image('cct.image', 'CCT image'),
            Field::text('space.kicker', 'Space kicker'),
            Field::textarea('space.heading', 'Space heading'),
            Field::textarea('space.lead', 'Space lead'),
            Field::text('space.item.title', 'Space title', true, 'space.item'),
            Field::textarea('space.item.body', 'Space body', true, 'space.item'),
            Field::image('space.item.image', 'Space image', true, 'space.item'),
            Field::text('cta.heading', 'CTA heading'),
            Field::textarea('cta.body', 'CTA body'),
            Field::text('cta.label', 'CTA label'),
            Field::url('cta.href', 'CTA href'),
        ];
    }

    public function seed(): array
    {
        $rows = [
            ['key' => 'hero.kicker', 'sort_order' => 0, 'value' => 'AI Lighting'],
            ['key' => 'hero.title', 'sort_order' => 0, 'value' => 'Lighting that thinks for retail.'],
            ['key' => 'hero.lead', 'sort_order' => 0, 'value' => 'Adaptive spectrum. Live store insight. Energy that follows the floor.'],
            ['key' => 'hero.image', 'sort_order' => 0, 'value' => '/assets/img/ai-lighting/hero.jpg'],
            ['key' => 'caps.kicker', 'sort_order' => 0, 'value' => 'Beyond illumination'],
            ['key' => 'caps.heading', 'sort_order' => 0, 'value' => "One intelligent platform.\nFour hard advantages."],
            ['key' => 'caps.body', 'sort_order' => 0, 'value' => 'AI-assisted colour recognition, adaptive control, occupancy sensing, and store data — built to sell product, protect stock, and cut waste.'],
            ['key' => 'spectrum.kicker', 'sort_order' => 0, 'value' => 'Spectrum'],
            ['key' => 'spectrum.heading', 'sort_order' => 0, 'value' => 'Adaptive light spectrum'],
            ['key' => 'spectrum.body', 'sort_order' => 0, 'value' => 'AI tunes the spectrum to product category and colour — so every zone gets the light that makes goods look right, not average.'],
            ['key' => 'spectrum.compare.traditional.image', 'sort_order' => 0, 'value' => '/assets/img/ai-lighting/compare-traditional.jpg'],
            ['key' => 'spectrum.compare.traditional.caption', 'sort_order' => 0, 'value' => 'Traditional'],
            ['key' => 'spectrum.compare.ai.image', 'sort_order' => 0, 'value' => '/assets/img/ai-lighting/compare-ai.jpg'],
            ['key' => 'spectrum.compare.ai.caption', 'sort_order' => 0, 'value' => 'AI-optimised'],
            ['key' => 'insights.kicker', 'sort_order' => 0, 'value' => 'Insights'],
            ['key' => 'insights.heading', 'sort_order' => 0, 'value' => 'Business data analysis'],
            ['key' => 'insights.lead', 'sort_order' => 0, 'value' => 'Physical-store signals in near real time — views, picks, trends — so the floor runs with the clarity online teams expect.'],
            ['key' => 'cct.kicker', 'sort_order' => 0, 'value' => 'Colour'],
            ['key' => 'cct.heading', 'sort_order' => 0, 'value' => 'Adaptive colour temperature'],
            ['key' => 'cct.body', 'sort_order' => 0, 'value' => 'AI shifts CCT to merchandise colour and category — so each product is lit to show its true character on the shelf.'],
            ['key' => 'cct.image', 'sort_order' => 0, 'value' => '/assets/img/ai-lighting/cct-bg.jpg'],
            ['key' => 'space.kicker', 'sort_order' => 0, 'value' => 'Space'],
            ['key' => 'space.heading', 'sort_order' => 0, 'value' => 'Advanced space management'],
            ['key' => 'space.lead', 'sort_order' => 0, 'value' => 'Absence lighting, merchandise care, and energy management in one system — responding to traffic and conditions.'],
            ['key' => 'cta.heading', 'sort_order' => 0, 'value' => 'Want to explore AI lighting for your project?'],
            ['key' => 'cta.body', 'sort_order' => 0, 'value' => 'Talk to our team about intelligent spectrum, sensing, and retail-ready control.'],
            ['key' => 'cta.label', 'sort_order' => 0, 'value' => 'Contact Us'],
            ['key' => 'cta.href', 'sort_order' => 0, 'value' => '/contact'],
        ];

        foreach (['Adaptive Light Spectrum', 'Advanced Space Management', 'Intelligent Data Analysis', 'Adaptive Colour Temperature'] as $i => $title) {
            $rows[] = ['key' => 'caps.item.title', 'sort_order' => $i, 'value' => $title];
        }

        foreach (['Category-aware spectrum control', 'Colour-true merchandising', 'Zone-by-zone optimisation'] as $i => $tick) {
            $rows[] = ['key' => 'spectrum.tick', 'sort_order' => $i, 'value' => $tick];
        }

        $insights = [
            ['People counting', 'Spot strengths and gaps in performance, then refine campaigns, pricing, and floor priorities with clearer evidence.', '/assets/img/ai-lighting/people-counting.gif'],
            ['Store heatmap', 'Track dwell and movement to guide inventory and layout — highlight high- and low-traffic zones that shape the shopper journey.', '/assets/img/ai-lighting/heatmap.gif'],
        ];
        foreach ($insights as $i => [$title, $body, $image]) {
            $rows[] = ['key' => 'insights.item.title', 'sort_order' => $i, 'value' => $title];
            $rows[] = ['key' => 'insights.item.body', 'sort_order' => $i, 'value' => $body];
            $rows[] = ['key' => 'insights.item.image', 'sort_order' => $i, 'value' => $image];
        }

        $space = [
            ['Absence lighting', 'Absence sensing dims empty zones — welcoming where shoppers are, lean where they aren’t.', '/assets/img/ai-lighting/space-absence.gif'],
            ['Commodity protection', 'Tuned light recipes help shield sensitive materials from unnecessary exposure — preserving look and display life.', '/assets/img/ai-lighting/space-protect.gif'],
            ['Energy management', 'Scenes follow live traffic so energy goes where it matters — consistent brand look, lower running cost.', '/assets/img/ai-lighting/space-energy.gif'],
        ];
        foreach ($space as $i => [$title, $body, $image]) {
            $rows[] = ['key' => 'space.item.title', 'sort_order' => $i, 'value' => $title];
            $rows[] = ['key' => 'space.item.body', 'sort_order' => $i, 'value' => $body];
            $rows[] = ['key' => 'space.item.image', 'sort_order' => $i, 'value' => $image];
        }

        return $rows;
    }
}
