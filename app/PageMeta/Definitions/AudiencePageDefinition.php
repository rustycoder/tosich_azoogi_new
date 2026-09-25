<?php

namespace App\PageMeta\Definitions;

use App\PageMeta\Field;
use App\PageMeta\ImageSize;
use App\PageMeta\PageDefinition;
use Illuminate\Support\Facades\File;

abstract class AudiencePageDefinition implements PageDefinition
{
    /**
     * @var list<string>
     */
    public const SLUGS = [
        'home-owner',
        'architect-designer',
        'electrician-builder',
        'wholesaler',
    ];

    /**
     * @return list<Field>
     */
    public function fields(): array
    {
        return [
            Field::video('hero.video', 'Hero video'),
            Field::image('hero.poster', 'Hero poster', hint: ImageSize::Hero),
            Field::text('hero.eyebrow', 'Eyebrow', typographic: false),
            Field::text('hero.title', 'Title', typographic: false)->recommendedWords(3, 8),
            Field::textarea('hero.lead', 'Lead paragraph', true, 'hero.lead', typographic: false)->recommendedWords(15, 35),
            Field::text('card.heading', 'Card heading', true, 'card')->recommendedWords(3, 8),
            Field::textarea('card.body', 'Card body', true, 'card')->recommendedWords(20, 60),
            Field::image('card.image', 'Card image', true, 'card', ImageSize::Card),
            Field::text('card.cta.label', 'CTA label', true, 'card'),
            Field::url('card.cta.href', 'CTA href', true, 'card'),
        ];
    }

    /**
     * @return list<array{key: string, sort_order: int, value: string}>
     */
    public function seed(): array
    {
        $audience = $this->payload();
        $title = (string) ($audience['title'] ?? '');
        $titleAccent = (string) ($audience['titleAccent'] ?? '');
        $titleValue = ($titleAccent !== '' && str_contains($title, $titleAccent))
            ? str_replace($titleAccent, '{'.$titleAccent.'}', $title)
            : $title;

        $heroImages = [
            'home-owner' => '/assets/img/img-0.jpg',
            'architect-designer' => '/assets/hero02.jpg',
            'electrician-builder' => '/assets/img/img-1.jpg',
            'wholesaler' => '/assets/img/img-2.jpg',
        ];

        $defaultLeads = [
            'home-owner' => ['Explore high-quality LED lighting solutions tailored for Australian homes — combining style, energy efficiency, and lasting performance.'],
            'architect-designer' => ['Specification-grade LED lighting crafted to enhance contemporary interiors and bring your architectural vision to life.'],
            'electrician-builder' => ['Engineered for straightforward installation, rapid turnarounds, and reliable performance on every residential and commercial build.'],
            'wholesaler' => ['Stock with confidence. Fast quotes, protected trade margins, and dependable nationwide supply for leading electrical distributors.'],
        ];

        $rows = [
            ['key' => 'hero.poster', 'sort_order' => 0, 'value' => $heroImages[$this->slug()] ?? '/assets/img/img-0.jpg'],
            ['key' => 'hero.eyebrow', 'sort_order' => 0, 'value' => (string) ($audience['eyebrow'] ?? '')],
            ['key' => 'hero.title', 'sort_order' => 0, 'value' => $titleValue],
        ];

        $leadParagraphs = $audience['lead'] ?? [];
        if (empty($leadParagraphs) && isset($defaultLeads[$this->slug()])) {
            $leadParagraphs = $defaultLeads[$this->slug()];
        }

        foreach ($leadParagraphs as $i => $paragraph) {
            $rows[] = ['key' => 'hero.lead', 'sort_order' => $i, 'value' => (string) $paragraph];
        }

        foreach ($audience['cards'] ?? [] as $i => $card) {
            $heading = (string) ($card['heading'] ?? '');
            $headingAccent = (string) ($card['headingAccent'] ?? '');
            $headingValue = ($headingAccent !== '' && str_contains($heading, $headingAccent))
                ? str_replace($headingAccent, '{'.$headingAccent.'}', $heading)
                : $heading;

            $rows[] = ['key' => 'card.heading', 'sort_order' => $i, 'value' => $headingValue];
            $rows[] = ['key' => 'card.body', 'sort_order' => $i, 'value' => implode("\n\n", $card['body'] ?? [])];
            $rows[] = ['key' => 'card.image', 'sort_order' => $i, 'value' => (string) ($card['image'] ?? '')];
            $rows[] = ['key' => 'card.cta.label', 'sort_order' => $i, 'value' => (string) ($card['cta']['label'] ?? '')];
            $rows[] = ['key' => 'card.cta.href', 'sort_order' => $i, 'value' => (string) ($card['cta']['href'] ?? '')];
        }

        return $rows;
    }

    /**
     * @return array<string, mixed>
     */
    private function payload(): array
    {
        $path = public_path('assets/data/audiences.json');
        $payload = json_decode(File::get($path), true);

        foreach ($payload['audiences'] ?? [] as $audience) {
            if (($audience['slug'] ?? null) === $this->slug()) {
                return $audience;
            }
        }

        return [];
    }
}
