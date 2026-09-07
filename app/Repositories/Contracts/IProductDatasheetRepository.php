<?php

namespace App\Repositories\Contracts;

use App\Models\ProductDatasheetExport;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

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
}
