<?php

namespace App\Repositories;

use App\Enums\PageVisitKind;
use App\Models\PageVisit;
use App\Repositories\Contracts\IPageVisitRepository;
use Illuminate\Support\Collection;

class PageVisitRepository implements IPageVisitRepository
{
    /**
     * @param  array<string, mixed>  $data
     */
    public function create(array $data): PageVisit
    {
        return PageVisit::query()->create($data);
    }

    /**
     * @return Collection<int, object>
     */
    public function pageBuckets(): Collection
    {
        return PageVisit::query()
            ->where('kind', PageVisitKind::Page)
            ->whereNotNull('page_slug')
            ->selectRaw('page_slug as origin_key, count(*) as total')
            ->groupBy('page_slug')
            ->orderByDesc('total')
            ->get();
    }

    /**
     * @return Collection<int, object>
     */
    public function countryBuckets(): Collection
    {
        return PageVisit::query()
            ->selectRaw('country as origin_key, count(*) as total')
            ->groupBy('country')
            ->orderByDesc('total')
            ->get();
    }

    /**
     * @return Collection<int, object>
     */
    public function productViewBuckets(): Collection
    {
        return PageVisit::query()
            ->where('kind', PageVisitKind::Product)
            ->whereNotNull('airtable_id')
            ->selectRaw('airtable_id as origin_key, count(*) as total')
            ->groupBy('airtable_id')
            ->orderByDesc('total')
            ->get();
    }
}
