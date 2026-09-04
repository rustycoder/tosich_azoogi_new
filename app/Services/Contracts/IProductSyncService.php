<?php

namespace App\Services\Contracts;

use App\Models\Product;
use App\Models\ProductSync;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface IProductSyncService
{
    public function sync(string $triggeredBy = 'schedule'): ProductSync;

    public function dispatch(string $triggeredBy = 'schedule'): void;

    /**
     * @return LengthAwarePaginator<int, Product>
     */
    public function dashboardList(string $search = ''): LengthAwarePaginator;

    public function latestSync(): ?ProductSync;
}
