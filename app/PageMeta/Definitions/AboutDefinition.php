<?php

namespace App\PageMeta\Definitions;

use App\PageMeta\Field;
use App\PageMeta\PageDefinition;

class AboutDefinition implements PageDefinition
{
    public function slug(): string
    {
        return 'about';
    }

    public function title(): string
    {
        return 'About Us — Engineered Lighting | Azoogi';
    }

    public function metaDescription(): string
    {
        return 'Azoogi designs, assembles, and optimizes architectural, commercial, and industrial lighting for projects of every scale.';
    }

    public function navLabel(): string
    {
        return 'About';
    }

    public function fields(): array
    {
        return [
            Field::text('hero.kicker', 'Hero kicker'),
            Field::textarea('hero.title', 'Hero title'),
            Field::image('hero.image', 'Hero image'),
            Field::textarea('intro.body', 'Intro'),
            Field::text('intro.cta.label', 'Intro CTA'),
            Field::url('intro.cta.href', 'Intro CTA link'),
            Field::text('why.kicker', 'Why kicker'),
            Field::text('why.heading', 'Why heading'),
            Field::text('why.item.title', 'Why title', true, 'why.item'),
            Field::textarea('why.item.body', 'Why body', true, 'why.item'),
            Field::image('why.item.image', 'Why image', true, 'why.item'),
            Field::text('reach.kicker', 'Reach kicker'),
            Field::text('reach.heading', 'Reach heading'),
            Field::textarea('reach.body', 'Reach body'),
            Field::image('reach.image', 'Reach image'),
            Field::text('path.kicker', 'Path kicker'),
            Field::text('path.heading', 'Path heading'),
            Field::text('path.item.title', 'Path title', true, 'path.item'),
            Field::textarea('path.item.body', 'Path body', true, 'path.item'),
            Field::image('path.item.image', 'Path image', true, 'path.item'),
            Field::url('path.item.href', 'Path href', true, 'path.item'),
        ];
    }

    public function seed(): array
    {
        $why = [
            ['Bespoke Custom Engineering', 'As an active manufacturer equipped with specialized equipment and production lines - we deliver rapid turnarounds where speed counts.', '/assets/img/leds.webp'],
            ['Design Integrity & Schedule Optimization', 'Quality doesn\'t have to mean cost overruns. We collaborate with designers, builders and electrical contractors to optimize lighting schedules - delivering spec-grade fixtures that respect design intent, photometric standards, and commercial targets.', '/assets/img/img-1.jpg'],
            ['Global Sourcing, Local Assembly & QA', 'We source the highest-grade raw materials and components from leading global partners, bringing them together under our own strict in-house assembly and Quality Assurance processes. Every fitting is thoroughly tested before it lands on your site.', '/assets/img/drivers.webp'],
            ['Direct Technical & On-Site Support', 'We don\'t just ship boxes - we partner with your team on the ground. From compliance reporting and photometric modeling to rapid on-site technical assistance, we ensure complete project continuity', '/assets/img/datacenter1.webp'],
            ['Verified Ethical & Transparent Operations', 'Tier 1 and civil projects demand total supply chain accountability. As a certified Sedex Plus Member, our supply chain and manufacturing operations are independently verified against strict global standards for fair labor, health and safety, business ethics, and environmental responsibility.', '/assets/img/acdm.jpg'],
            ['100% Wholesale Channel Protected', 'We proudly support our trade distributor network. Every project inquiry, custom schedule, and commercial order is routed and fulfilled strictly through your nominated local electrical wholesaler.', '/assets/img/prod-3.jpg'],
        ];

        $paths = [
            ['For Architects & Specifiers', 'Protect your design intent. Partner early with us - custom modifications, photometric testing, and spec-grade fixtures that match your vision.', '/assets/img/img-1.jpg', '/architect-designer'],
            ['For Builders & Contractors', 'On time and on budget. We catch potential site issues before your installer ever opens a box.', '/assets/img/datacenter2.webp', '/electrician-builder'],
            ['For Electrical Wholesalers', '100% channel protected. Guaranteed trade margins, fast quotes, and reliable local stock support.', '/assets/img/prod-4.jpg', '/wholesaler'],
        ];

        $rows = [
            ['key' => 'hero.kicker', 'sort_order' => 0, 'value' => 'About Us'],
            ['key' => 'hero.title', 'sort_order' => 0, 'value' => "Engineered Lighting.\nInfinite Scale.\nZero Compromise."],
            ['key' => 'hero.image', 'sort_order' => 0, 'value' => '/assets/img/ai-lighting/hero.jpg'],
            ['key' => 'intro.body', 'sort_order' => 0, 'value' => 'We design, assemble, and optimize architectural, commercial, and industrial lighting for projects of every scale - from bespoke residential projects to Tier 1 developments.'],
            ['key' => 'intro.cta.label', 'sort_order' => 0, 'value' => 'Request Capability Statement'],
            ['key' => 'intro.cta.href', 'sort_order' => 0, 'value' => '/contact'],
            ['key' => 'why.kicker', 'sort_order' => 0, 'value' => 'Why Azoogi'],
            ['key' => 'why.heading', 'sort_order' => 0, 'value' => 'Why Choose Azoogi'],
            ['key' => 'reach.kicker', 'sort_order' => 0, 'value' => 'Worldwide'],
            ['key' => 'reach.heading', 'sort_order' => 0, 'value' => 'International Project Reach'],
            ['key' => 'reach.body', 'sort_order' => 0, 'value' => 'For over two decades, our engineering footprint has extended far beyond Australia - delivering technical lighting packages for major developments and luxury resorts across Fiji, Vanuatu, Bali, the Maldives. With extensive export expertise, multi-currency processing, and deep experience navigating international compliance standards, we ensure seamless project delivery anywhere in the world.'],
            ['key' => 'reach.image', 'sort_order' => 0, 'value' => '/assets/img/sydney-night.jpg'],
            ['key' => 'path.kicker', 'sort_order' => 0, 'value' => 'Audiences'],
            ['key' => 'path.heading', 'sort_order' => 0, 'value' => "Select Your\nPath"],
        ];

        foreach ($why as $i => [$title, $body, $image]) {
            $rows[] = ['key' => 'why.item.title', 'sort_order' => $i, 'value' => $title];
            $rows[] = ['key' => 'why.item.body', 'sort_order' => $i, 'value' => $body];
            $rows[] = ['key' => 'why.item.image', 'sort_order' => $i, 'value' => $image];
        }

        foreach ($paths as $i => [$title, $body, $image, $href]) {
            $rows[] = ['key' => 'path.item.title', 'sort_order' => $i, 'value' => $title];
            $rows[] = ['key' => 'path.item.body', 'sort_order' => $i, 'value' => $body];
            $rows[] = ['key' => 'path.item.image', 'sort_order' => $i, 'value' => $image];
            $rows[] = ['key' => 'path.item.href', 'sort_order' => $i, 'value' => $href];
        }

        return $rows;
    }
}
