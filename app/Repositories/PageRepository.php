<?php

namespace App\Repositories;

use App\Models\Page;
use App\Models\PageMeta;
use App\Repositories\Contracts\IPageRepository;
use Illuminate\Support\Collection;

class PageRepository implements IPageRepository
{
    public function findActiveBySlug(string $slug): Page
    {
        return Page::query()
            ->with('meta')
            ->where('slug', $slug)
            ->active()
            ->firstOrFail();
    }

    public function findBySlugs(array $slugs): Collection
    {
        if ($slugs === []) {
            return collect();
        }

        return Page::query()->with('updater:id,name')->whereIn('slug', $slugs)->get();
    }

    public function save(Page $page): void
    {
        $page->save();
    }

    public function findMeta(Page $page, int $id): ?PageMeta
    {
        return PageMeta::query()
            ->where('page_id', $page->id)
            ->whereKey($id)
            ->first();
    }

    public function saveMeta(PageMeta $meta): void
    {
        $meta->save();
    }
}
