<?php

namespace App\PageMeta\Definitions;

use App\PageMeta\Field;
use App\PageMeta\PageDefinition;

class HomeDefinition implements PageDefinition
{
    public function slug(): string
    {
        return 'home';
    }

    public function title(): string
    {
        return 'Azoogi — Award-winning LED Lighting Solutions';
    }

    public function metaDescription(): string
    {
        return 'Azoogi designs and supplies premium LED lighting — strips, neon, garden lights, drivers and architectural fittings for projects that demand more.';
    }

    public function navLabel(): string
    {
        return 'Home';
    }

    public function fields(): array
    {
        $slide = true;
        $group = 'slide';

        return [
            Field::text('slide.eyebrow', 'Slide eyebrow', $slide, $group),
            Field::textarea('slide.title', 'Slide title', $slide, $group),
            Field::textarea('slide.subtitle', 'Slide subtitle', $slide, $group),
            Field::text('slide.cta.primary.label', 'Primary button', $slide, $group),
            Field::url('slide.cta.primary.href', 'Primary href', $slide, $group),
            Field::text('slide.cta.secondary.label', 'Secondary button', $slide, $group),
            Field::url('slide.cta.secondary.href', 'Secondary href', $slide, $group),
            Field::select('slide.media.type', 'Media type', ['image' => 'Image', 'video' => 'Video'], $slide, $group),
            Field::image('slide.media.image', 'Slide image', $slide, $group),
            Field::video('slide.media.video', 'Slide video', $slide, $group),
            Field::image('slide.media.poster', 'Slide poster', $slide, $group),
            Field::text('intro.kicker', 'Intro kicker'),
            Field::text('intro.heading', 'Intro heading'),
            Field::text('values.kicker', 'Values kicker'),
            Field::textarea('values.heading', 'Values heading'),
            Field::text('values.card.title', 'Value title', true, 'values.card'),
            Field::textarea('values.card.body', 'Value body', true, 'values.card'),
            Field::image('values.card.image', 'Value image', true, 'values.card'),
            Field::url('values.card.href', 'Value href', true, 'values.card'),
            Field::text('range.kicker', 'Range kicker'),
            Field::text('range.heading', 'Range heading'),
            Field::text('range.cta.label', 'Range CTA'),
            Field::url('range.cta.href', 'Range CTA href'),
            Field::text('projects.kicker', 'Projects kicker'),
            Field::text('projects.heading', 'Projects heading'),
            Field::text('projects.cta.label', 'Projects CTA'),
            Field::url('projects.cta.href', 'Projects CTA href'),
            Field::text('stats.kicker', 'Stats kicker'),
            Field::textarea('stats.heading', 'Stats heading'),
            Field::text('stats.item.value', 'Stat value', true, 'stats.item'),
            Field::text('stats.item.label', 'Stat label', true, 'stats.item'),
        ];
    }

    public function seed(): array
    {
        $rows = [
            ...$this->slide(0, 'Made just for you', "Custom fittings\nwithout compromise.", 'Off-spec alternatives, custom fittings, matched specs and budgets — without ever compromising on quality.', 'Start a Project', '/contact', 'Our Services', '#products', 'video', '', '/assets/herovid 1.webm', '/assets/fallback.webp'),
            ...$this->slide(1, 'For those who demand more from their lighting', "Award-winning\nLED lighting solutions.", 'Premium components, strict quality assurance, and up to a 5-year warranty — engineered to perform and last.', 'Explore Products', '/products', 'View Projects', '/projects', 'video', '', '/assets/herovid 2.webm', '/assets/vid2_fallback.jpg'),
            ...$this->slide(2, 'Support that lasts', "From first idea\nto final install — and beyond.", "We don't just supply lighting. We partner with you with advice, guidance, and service that keeps your project shining.", 'Talk to a Specialist', '/contact', 'Recent Work', '/projects', 'image', '/assets/hero01.jpg', '', ''),
            ...$this->slide(3, 'Design without limits', "Architectural lighting,\nshaped to your vision.", 'From sleek LED strips to statement architectural fittings — solutions for every style and every space, indoors or out.', 'Browse Range', '/products', 'Our Story', '#about', 'image', 'https://images.unsplash.com/photo-1567016526105-22da7c13161a?w=2400&q=80', '', ''),
            ...$this->slide(4, 'Made just for you', "Custom fittings\nwithout compromise.", 'Off-spec alternatives, custom fittings, matched specs and budgets — without ever compromising on quality.', 'Start a Project', '/contact', 'Our Services', '#products', 'image', 'https://images.unsplash.com/photo-1497366216548-37526070297c?w=2400&q=80', '', ''),
            ['key' => 'intro.kicker', 'sort_order' => 0, 'value' => 'Lighting solutions from start to finish'],
            ['key' => 'intro.heading', 'sort_order' => 0, 'value' => "I'm looking for lighting as a…"],
            ['key' => 'values.kicker', 'sort_order' => 0, 'value' => 'Why Azoogi'],
            ['key' => 'values.heading', 'sort_order' => 0, 'value' => 'For those who demand more — from the components inside to the support behind every product.'],
            ...$this->valueCard(0, 'Lighting That Lasts', 'Built with premium components and backed by strict quality assurance, our products are made to perform and last – with up to a 5-year warranty for peace of mind.', '/assets/img/img-0.jpg', '#top'),
            ...$this->valueCard(1, 'Design Without Limits', 'From sleek LED strips to statement architectural fittings and rugged garden lighting, we’ve got solutions for every style, every space, and every project – indoors or out.', '/assets/img/img-1.jpg', '#top'),
            ...$this->valueCard(2, 'Made Just for You', 'From off-spec alternatives to custom fittings, we create lighting that meets your needs – adapting products, matching specs, or meeting budgets without compromising quality.', '/assets/img/img-2.jpg', '#top'),
            ...$this->valueCard(3, 'Support That Lasts', 'We don’t just supply lighting – we partner with you. From your first idea to final install (and well after), our team is here with advice, guidance, and service that keeps your project shining.', '/assets/supportthatlasts.jpeg', '#top'),
            ['key' => 'range.kicker', 'sort_order' => 0, 'value' => 'Our Range'],
            ['key' => 'range.heading', 'sort_order' => 0, 'value' => 'Explore the full Azoogi lighting catalogue.'],
            ['key' => 'range.cta.label', 'sort_order' => 0, 'value' => 'View All Products'],
            ['key' => 'range.cta.href', 'sort_order' => 0, 'value' => '/products'],
            ['key' => 'projects.kicker', 'sort_order' => 0, 'value' => 'Recent Highlights'],
            ['key' => 'projects.heading', 'sort_order' => 0, 'value' => 'Where Azoogi lighting comes to life.'],
            ['key' => 'projects.cta.label', 'sort_order' => 0, 'value' => 'View All Projects'],
            ['key' => 'projects.cta.href', 'sort_order' => 0, 'value' => '/projects'],
            ['key' => 'stats.kicker', 'sort_order' => 0, 'value' => 'By the numbers'],
            ['key' => 'stats.heading', 'sort_order' => 0, 'value' => "Engineered - Tested - Trusted\nacross Australia."],
            ...$this->stat(0, '2500', 'Projects Delivered'),
            ...$this->stat(1, '500', 'Product Lines'),
            ...$this->stat(2, '5', 'Year Warranty'),
            ...$this->stat(3, '15', 'Years in Lighting'),
        ];

        return $rows;
    }

    /**
     * @return list<array{key: string, sort_order: int, value: string}>
     */
    private function slide(int $i, string $eyebrow, string $title, string $subtitle, string $pLabel, string $pHref, string $sLabel, string $sHref, string $type, string $image, string $video, string $poster): array
    {
        return [
            ['key' => 'slide.eyebrow', 'sort_order' => $i, 'value' => $eyebrow],
            ['key' => 'slide.title', 'sort_order' => $i, 'value' => $title],
            ['key' => 'slide.subtitle', 'sort_order' => $i, 'value' => $subtitle],
            ['key' => 'slide.cta.primary.label', 'sort_order' => $i, 'value' => $pLabel],
            ['key' => 'slide.cta.primary.href', 'sort_order' => $i, 'value' => $pHref],
            ['key' => 'slide.cta.secondary.label', 'sort_order' => $i, 'value' => $sLabel],
            ['key' => 'slide.cta.secondary.href', 'sort_order' => $i, 'value' => $sHref],
            ['key' => 'slide.media.type', 'sort_order' => $i, 'value' => $type],
            ['key' => 'slide.media.image', 'sort_order' => $i, 'value' => $image],
            ['key' => 'slide.media.video', 'sort_order' => $i, 'value' => $video],
            ['key' => 'slide.media.poster', 'sort_order' => $i, 'value' => $poster],
        ];
    }

    /**
     * @return list<array{key: string, sort_order: int, value: string}>
     */
    private function valueCard(int $i, string $title, string $body, string $image, string $href): array
    {
        return [
            ['key' => 'values.card.title', 'sort_order' => $i, 'value' => $title],
            ['key' => 'values.card.body', 'sort_order' => $i, 'value' => $body],
            ['key' => 'values.card.image', 'sort_order' => $i, 'value' => $image],
            ['key' => 'values.card.href', 'sort_order' => $i, 'value' => $href],
        ];
    }

    /**
     * @return list<array{key: string, sort_order: int, value: string}>
     */
    private function stat(int $i, string $value, string $label): array
    {
        return [
            ['key' => 'stats.item.value', 'sort_order' => $i, 'value' => $value],
            ['key' => 'stats.item.label', 'sort_order' => $i, 'value' => $label],
        ];
    }
}
