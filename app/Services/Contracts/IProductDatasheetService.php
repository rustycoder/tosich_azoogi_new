<?php

namespace App\Services\Contracts;

use App\Models\ProductDatasheetExport;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface IProductDatasheetService
{
    /**
     * @param  array{
     *     product_id: string,
     *     project_name: string,
     *     person_name: string,
     *     product_code?: string|null,
     *     selected_options?: array<string, mixed>|null,
     *     length?: int|float|string|null
     * }  $data
     */
    public function export(array $data): ProductDatasheetExport;

    /**
     * @return array{
     *     title: string,
     *     name: string,
     *     category: string,
     *     description: string,
     *     specifications: list<array{label: string, value: string}>,
     *     product_image: string,
     *     dimension_image: string,
     *     project_name: string,
     *     person_name: string,
     *     reviewed_on: string,
     *     email: string,
     *     phone: string,
     *     website: string,
     *     address: string
     * }
     */
    public function sheet(ProductDatasheetExport $export): array;

    /**
     * @return LengthAwarePaginator<int, ProductDatasheetExport>
     */
    public function dashboardList(string $search = ''): LengthAwarePaginator;
}
