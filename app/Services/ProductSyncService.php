<?php

namespace App\Services;

use App\Jobs\SyncProductsJob;
use App\Models\ProductSync;
use App\Repositories\Contracts\IProductRepository;
use App\Services\Contracts\IProductSyncService;
use App\ThirdParty\Airtable\Contracts\IAirtableClient;
use App\ThirdParty\Airtable\ProductImageStore;
use App\ThirdParty\Airtable\ProductNormalizer;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use RuntimeException;
use Throwable;

class ProductSyncService implements IProductSyncService
{
    public function __construct(
        private IAirtableClient $airtable,
        private IProductRepository $products,
        private ProductNormalizer $normalizer,
        private ProductImageStore $images,
    ) {}

    public function sync(string $triggeredBy = 'schedule'): ProductSync
    {
        if ($this->products->isSyncRunning()) {
            throw new RuntimeException('A product sync is already running.');
        }

        $run = $this->products->startSync($triggeredBy);

        try {
            $categories = $this->safeFetch((string) config('airtable.categories_table'));
            $attributes = $this->safeFetch((string) config('airtable.attributes_table'));
            $records = $this->airtable->fetchRecords((string) config('airtable.products_table'));
            $compiled = $this->normalizer->compileProducts($records, $categories, $attributes);
            $localized = $this->images->localizeProducts($compiled);
            $keepIds = array_values(array_filter(array_map(
                fn (array $product): string => (string) ($product['id'] ?? ''),
                $localized,
            )));

            $this->products->persistLookups($categories, $attributes);
            $this->products->persistProducts($localized);
            $this->products->pruneMissingProducts($keepIds);
            $this->products->finishSync($run, true, count($keepIds));
        } catch (Throwable $exception) {
            $this->products->finishSync($run->fresh() ?? $run, false, 0, $exception->getMessage());
            throw $exception;
        }

        return $run->fresh() ?? $run;
    }

    public function dispatch(string $triggeredBy = 'schedule'): void
    {
        if ($this->products->isSyncRunning()) {
            return;
        }

        SyncProductsJob::dispatch($triggeredBy);
    }

    public function dashboardList(string $search = ''): LengthAwarePaginator
    {
        return $this->products->dashboardList($search);
    }

    public function latestSync(): ?ProductSync
    {
        return $this->products->latestSync();
    }

    /**
     * @return list<array{id: string, fields: array<string, mixed>}>
     */
    private function safeFetch(string $table): array
    {
        try {
            return $this->airtable->fetchRecords($table);
        } catch (Throwable) {
            return [];
        }
    }
}
