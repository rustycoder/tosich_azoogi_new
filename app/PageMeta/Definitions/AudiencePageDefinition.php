<?php

namespace App\PageMeta\Definitions;

use App\PageMeta\Field;
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
            Field::text('hero.eyebrow', 'Eyebrow'),
            Field::text('hero.title', 'Title'),
            Field::text('hero.title_accent', 'Title accent'),
            Field::textarea('hero.lead', 'Lead paragraph', true, 'hero.lead'),
            Field::text('card.heading', 'Card heading', true, 'card'),
            Field::text('card.heading_accent', 'Card accent', true, 'card'),
            Field::textarea('card.body', 'Card body', true, 'card'),
            Field::image('card.image', 'Card image', true, 'card'),
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
        $rows = [
            ['key' => 'hero.eyebrow', 'sort_order' => 0, 'value' => (string) ($audience['eyebrow'] ?? '')],
            ['key' => 'hero.title', 'sort_order' => 0, 'value' => (string) ($audience['title'] ?? '')],
            ['key' => 'hero.title_accent', 'sort_order' => 0, 'value' => (string) ($audience['titleAccent'] ?? '')],
        ];

        foreach ($audience['lead'] ?? [] as $i => $paragraph) {
            $rows[] = ['key' => 'hero.lead', 'sort_order' => $i, 'value' => (string) $paragraph];
        }

        foreach ($audience['cards'] ?? [] as $i => $card) {
            $rows[] = ['key' => 'card.heading', 'sort_order' => $i, 'value' => (string) ($card['heading'] ?? '')];
            $rows[] = ['key' => 'card.heading_accent', 'sort_order' => $i, 'value' => (string) ($card['headingAccent'] ?? '')];
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
