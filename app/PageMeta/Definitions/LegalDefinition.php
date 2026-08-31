<?php

namespace App\PageMeta\Definitions;

use App\PageMeta\Field;
use App\PageMeta\PageDefinition;

abstract class LegalDefinition implements PageDefinition
{
    public function navLabel(): string
    {
        return $this->title();
    }

    /**
     * @return list<Field>
     */
    public function fields(): array
    {
        return [
            Field::text('legal.kicker', 'Kicker'),
            Field::text('legal.title', 'Title'),
            Field::textarea('legal.lead', 'Lead'),
            Field::html('legal.html', 'Body'),
        ];
    }

    /**
     * @return list<array{key: string, sort_order: int, value: string}>
     */
    protected function legalSeed(string $kicker, string $title, string $lead, string $html): array
    {
        return [
            ['key' => 'legal.kicker', 'sort_order' => 0, 'value' => $kicker],
            ['key' => 'legal.title', 'sort_order' => 0, 'value' => $title],
            ['key' => 'legal.lead', 'sort_order' => 0, 'value' => $lead],
            ['key' => 'legal.html', 'sort_order' => 0, 'value' => $html],
        ];
    }
}
