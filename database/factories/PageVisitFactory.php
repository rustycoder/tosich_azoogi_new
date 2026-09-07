<?php

namespace Database\Factories;

use App\Enums\PageVisitKind;
use App\Models\PageVisit;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PageVisit>
 */
class PageVisitFactory extends Factory
{
    public function definition(): array
    {
        return [
            'kind' => PageVisitKind::Page,
            'page_slug' => 'about',
            'airtable_id' => null,
            'country' => 'AU',
            'ip_address' => '203.0.113.10',
            'user_agent' => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
        ];
    }

    public function page(string $slug = 'about'): static
    {
        return $this->state(fn (array $attributes): array => [
            'kind' => PageVisitKind::Page,
            'page_slug' => $slug,
            'airtable_id' => null,
        ]);
    }

    public function product(string $airtableId): static
    {
        return $this->state(fn (array $attributes): array => [
            'kind' => PageVisitKind::Product,
            'page_slug' => null,
            'airtable_id' => $airtableId,
        ]);
    }
}
