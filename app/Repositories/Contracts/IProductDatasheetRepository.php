<?php

namespace App\Repositories\Contracts;

use App\Models\ProductDatasheetExport;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

interface IProductDatasheetRepository
{
    /**
     * @param  array<string, mixed>  $data
     */
    public function create(array $data): ProductDatasheetExport;

    /**
     * @return LengthAwarePaginator<int, ProductDatasheetExport>
     */
    public function dashboardList(string $search = ''): LengthAwarePaginator;

    /**
     * @return array{
     *     countries: Collection<int, object>,
     *     user_agents: Collection<int, object>
     * }
     */
    public function originBuckets(): array;

    /**
     * @return array<int, int>
     */
    public function monthlyCounts(int $year): array;
}
