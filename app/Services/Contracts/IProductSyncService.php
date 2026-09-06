<?php

namespace App\Services\Contracts;

use App\Models\Product;
use App\Models\ProductSync;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface IProductSyncService
{
    /**
     * @param  (callable(array<string, mixed> $event): void)|null  $onProgress
     */
    public function sync(string $triggeredBy = 'schedule', ?callable $onProgress = null): ProductSync;

    public function dispatch(string $triggeredBy = 'schedule'): void;

    /**
     * @return LengthAwarePaginator<int, Product>
     */
    public function dashboardList(string $search = ''): LengthAwarePaginator;

    public function latestSync(): ?ProductSync;
}
