<?php

namespace App\Repositories\Contracts;

use App\Models\Product;
use App\Models\ProductAttribute;
use App\Models\ProductCategory;
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
     * @return array{categories: list<mixed>, products: list<mixed>, tree: list<mixed>}
     */
    public function navigationCatalog(): array;

    /**
     * @return LengthAwarePaginator<int, Product>
     */
    public function dashboardList(string $search = '', ?string $category = null, int $perPage = 50, ?string $status = null): LengthAwarePaginator;

    /**
     * @return list<array{name: string, label: string, depth: int, airtable_id: string}>
     */
    public function hierarchicalCategories(): array;

    /**
     * @return LengthAwarePaginator<int, ProductCategory>
     */
    public function categoryDashboardList(string $search = '', ?string $parent = null, int $perPage = 50, ?string $status = null): LengthAwarePaginator;

    /**
     * @return LengthAwarePaginator<int, ProductAttribute>
     */
    public function attributeDashboardList(string $search = '', ?string $group = null, ?bool $visibleOnly = null, int $perPage = 50): LengthAwarePaginator;

    /**
     * @return list<string>
     */
    public function attributeGroups(): array;

    public function publishedByAirtableId(string $airtableId): ?Product;

    /**
     * @param  list<string>  $ids
     * @return Collection<int, Product>
     */
    public function publishedForQuoteIds(array $ids): Collection;

    /**
     * @return Collection<int, Product>
     */
    public function metricIdentities(): Collection;

    public function latestSync(): ?ProductSync;

    public function isSyncRunning(): bool;

    public function startSync(string $triggeredBy): ProductSync;

    public function appendSyncLog(ProductSync $sync, string $line): void;

    public function finishSync(ProductSync $sync, bool $ok, int $productCount, ?string $error = null): void;

    public function failStaleRunningSyncs(bool $forceAll = false): void;
}
