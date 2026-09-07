<?php

namespace App\Repositories;

use App\Models\ProductDatasheetExport;
use App\Repositories\Contracts\IProductDatasheetRepository;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class ProductDatasheetRepository implements IProductDatasheetRepository
{
    /**
     * @param  array<string, mixed>  $data
     */
    public function create(array $data): ProductDatasheetExport
    {
        return ProductDatasheetExport::query()->create($data);
    }

    /**
     * @return LengthAwarePaginator<int, ProductDatasheetExport>
     */
    public function dashboardList(string $search = ''): LengthAwarePaginator
    {
        return ProductDatasheetExport::query()
            ->when($search !== '', function ($query) use ($search): void {
                $query->where(function ($query) use ($search): void {
                    $query->where('project_name', 'like', '%'.$search.'%')
                        ->orWhere('person_name', 'like', '%'.$search.'%')
                        ->orWhere('product_code', 'like', '%'.$search.'%')
                        ->orWhere('product_name', 'like', '%'.$search.'%');
                });
            })
            ->latest('id')
            ->paginate(20)
            ->withQueryString();
    }
}
