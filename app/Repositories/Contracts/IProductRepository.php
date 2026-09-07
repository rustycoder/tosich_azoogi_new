<?php

namespace App\Repositories\Contracts;

use App\Models\Product;
use App\Models\ProductSync;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

interface IProductRepository
{
    /**
     * @param  list<array<string, mixed>>  $products
     */
    public function persistProducts(array $products): void;

    /**
     * @param  list<string>  $keepAirtableIds
     */
    public function pruneMissingProducts(array $keepAirtableIds): void;

    /**
     * @param  list<array{id: string, fields: array<string, mixed>}>  $categories
     * @param  list<array{id: string, fields: array<string, mixed>}>  $attributes
     */
    public function persistLookups(array $categories, array $attributes): void;

    /**
     * @return array{categories: list<mixed>, products: list<mixed>, tree: list<mixed>}
     */
    public function compiled(): array;

    /**
     * @return LengthAwarePaginator<int, Product>
     */
    public function dashboardList(string $search = ''): LengthAwarePaginator;

    public function publishedByAirtableId(string $airtableId): ?Product;

    /**
     * @return Collection<int, Product>
     */
    public function metricIdentities(): Collection;

    public function latestSync(): ?ProductSync;

    public function isSyncRunning(): bool;

    public function startSync(string $triggeredBy): ProductSync;

    public function appendSyncLog(ProductSync $sync, string $line): void;

    public function finishSync(ProductSync $sync, bool $ok, int $productCount, ?string $error = null): void;

    public function failStaleRunningSyncs(): void;
}
