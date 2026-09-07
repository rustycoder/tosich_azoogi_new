<?php

namespace App\Repositories\Contracts;

use App\Models\PageVisit;
use Illuminate\Support\Collection;

interface IPageVisitRepository
{
    /**
     * @param  array<string, mixed>  $data
     */
    public function create(array $data): PageVisit;

    /**
     * @return Collection<int, object>
     */
    public function pageBuckets(): Collection;

    /**
     * @return Collection<int, object>
     */
    public function countryBuckets(): Collection;

    /**
     * @return Collection<int, object>
     */
    public function productViewBuckets(): Collection;
}
