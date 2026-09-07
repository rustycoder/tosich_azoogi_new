<?php

namespace App\Repositories;

use App\Models\ProductDatasheetExport;
use App\Repositories\Concerns\CountsByMonth;
use App\Repositories\Contracts\IProductDatasheetRepository;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class ProductDatasheetRepository implements IProductDatasheetRepository
{
    use CountsByMonth;

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
            ->with('product')
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

    /**
     * @return array{
     *     countries: Collection<int, object>,
     *     user_agents: Collection<int, object>
     * }
     */
    public function originBuckets(): array
    {
        $query = ProductDatasheetExport::query();

        return [
            'countries' => (clone $query)
                ->selectRaw('country as origin_key, count(*) as total')
                ->groupBy('country')
                ->orderByDesc('total')
                ->get(),
            'user_agents' => (clone $query)
                ->selectRaw('user_agent as origin_key, count(*) as total')
                ->groupBy('user_agent')
                ->orderByDesc('total')
                ->get(),
        ];
    }

    /**
     * @return array<int, int>
     */
    public function monthlyCounts(int $year): array
    {
        return $this->countsByMonth(ProductDatasheetExport::query(), $year);
    }

    /**
     * @return Collection<int, object>
     */
    public function productBuckets(): Collection
    {
        return ProductDatasheetExport::query()
            ->selectRaw("COALESCE(NULLIF(airtable_id, ''), CONCAT('code:', COALESCE(product_code, ''))) as origin_key, count(*) as total")
            ->groupByRaw("COALESCE(NULLIF(airtable_id, ''), CONCAT('code:', COALESCE(product_code, '')))")
            ->orderByDesc('total')
            ->get();
    }
}
