<?php

namespace App\Support;

use App\Models\Page;
use App\Models\PageMeta;
use Illuminate\Support\Collection;

final class PageMetaBag
{
    /**
     * @param  Collection<int, PageMeta>  $rows
     */
    public function __construct(
        public Page $page,
        private Collection $rows,
    ) {}

    public static function for(Page $page): self
    {
        $rows = $page->relationLoaded('meta') ? $page->meta : $page->meta()->get();

        return new self($page, $rows);
    }

    public function get(string $key, int $sortOrder = 0, string $default = ''): string
    {
        $row = $this->rows->first(
            fn (PageMeta $meta): bool => $meta->key === $key && $meta->sort_order === $sortOrder,
        );

        return $row?->value ?? $default;
    }

    /**
     * Group repeating keys by sort_order, stripping the prefix.
     *
     * @return array<int, array<string, string>>
     */
    public function group(string $prefix): array
    {
        $needle = $prefix.'.';
        $grouped = [];

        foreach ($this->rows as $row) {
            if (! str_starts_with($row->key, $needle)) {
                continue;
            }

            $field = substr($row->key, strlen($needle));
            $grouped[$row->sort_order][$field] = $row->value ?? '';
        }

        ksort($grouped);

        return $grouped;
    }

    /**
     * @return list<string>
     */
    public function list(string $key): array
    {
        return $this->rows
            ->where('key', $key)
            ->sortBy('sort_order')
            ->pluck('value')
            ->map(fn (?string $value): string => $value ?? '')
            ->values()
            ->all();
    }
}
