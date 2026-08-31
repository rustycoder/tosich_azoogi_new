<?php

namespace App\Repositories\Contracts;

use App\Models\Page;
use App\Models\PageMeta;
use Illuminate\Support\Collection;

interface IPageRepository
{
    public function findActiveBySlug(string $slug): Page;

    /**
     * @param  list<string>  $slugs
     * @return Collection<int, Page>
     */
    public function findBySlugs(array $slugs): Collection;

    public function save(Page $page): void;

    public function findMeta(Page $page, int $id): ?PageMeta;

    public function saveMeta(PageMeta $meta): void;

    public function createMeta(Page $page, string $key, int $sortOrder, string $value): PageMeta;

    public function deleteMetaByPrefix(Page $page, string $prefix): void;
}
