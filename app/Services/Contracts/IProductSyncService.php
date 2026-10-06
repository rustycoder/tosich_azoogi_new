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
    public function sync(string $triggeredBy = 'schedule', ?callable $onProgress = null, bool $force = false): ProductSync;

    public function dispatch(string $triggeredBy = 'schedule'): void;

    /**
     * @return LengthAwarePaginator<int, Product>
     */
    public function dashboardList(string $search = '', ?string $category = null, int $perPage = 50, ?string $status = null): LengthAwarePaginator;

    /**
     * @return list<array{name: string, label: string, depth: int, airtable_id: string}>
     */
    public function hierarchicalCategories(): array;

    public function latestSync(): ?ProductSync;
}
