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
     * @return Collection<int, object{origin_key: string, total: int}>
     */
    public function productBuckets(): Collection
    {
        return ProductDatasheetExport::query()
            ->selectRaw('airtable_id, product_code, count(*) as total')
            ->groupBy('airtable_id', 'product_code')
            ->get()
            ->groupBy(function (object $row): string {
                $airtableId = trim((string) $row->airtable_id);

                if ($airtableId !== '') {
                    return $airtableId;
                }

                return 'code:'.trim((string) $row->product_code);
            })
            ->map(fn (Collection $group, string $key): object => (object) [
                'origin_key' => $key,
                'total' => (int) $group->sum('total'),
            ])
            ->sortByDesc('total')
            ->values();
    }
}
