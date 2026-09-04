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
use Illuminate\Support\Facades\Log;
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
            $categoriesTable = (string) config('airtable.categories_table');
            $attributesTable = (string) config('airtable.attributes_table');
            $productsTable = (string) config('airtable.products_table');

            $this->log($run, 'Fetching categories from Airtable ['.$categoriesTable.'].');
            $categories = $this->safeFetch($run, $categoriesTable);
            $this->log($run, 'Fetched '.count($categories).' categor'.(count($categories) === 1 ? 'y' : 'ies').'.');

            $this->log($run, 'Fetching attributes from Airtable ['.$attributesTable.'].');
            $attributes = $this->safeFetch($run, $attributesTable);
            $this->log($run, 'Fetched '.count($attributes).' attribute'.(count($attributes) === 1 ? '' : 's').'.');

            $this->log($run, 'Fetching products from Airtable ['.$productsTable.'].');
            $records = $this->airtable->fetchRecords($productsTable);
            $this->log($run, 'Fetched '.count($records).' product record'.(count($records) === 1 ? '' : 's').'.');

            $this->log($run, 'Compiling published products.');
            $compiled = $this->normalizer->compileProducts($records, $categories, $attributes);
            $this->log($run, 'Compiled '.count($compiled).' published product'.(count($compiled) === 1 ? '' : 's').'.');

            $this->log($run, 'Localizing product images.');
            $localized = $this->images->localizeProducts($compiled);
            $this->log($run, $this->images->lastSummary());

            $keepIds = array_values(array_filter(array_map(
                fn (array $product): string => (string) ($product['id'] ?? ''),
                $localized,
            )));

            $this->log($run, 'Saving categories, attributes, and products.');
            $this->products->persistLookups($categories, $attributes);
            $this->products->persistProducts($localized);
            $this->products->pruneMissingProducts($keepIds);
            $this->log($run, 'Saved '.count($keepIds).' product'.(count($keepIds) === 1 ? '' : 's').'.');

            $this->products->appendSyncLog($run, 'Sync finished.');
            $this->products->finishSync($run, true, count($keepIds));
        } catch (Throwable $exception) {
            $this->log($run, 'Failed: '.$exception->getMessage());
            Log::error('Product sync failed.', [
                'id' => $run->id,
                'exception' => $exception,
            ]);
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
    private function safeFetch(ProductSync $run, string $table): array
    {
        try {
            return $this->airtable->fetchRecords($table);
        } catch (Throwable $exception) {
            $this->log($run, 'Could not fetch ['.$table.']: '.$exception->getMessage());

            return [];
        }
    }

    private function log(ProductSync $run, string $message): void
    {
        Log::info('Product sync: '.$message, ['id' => $run->id]);
        $this->products->appendSyncLog($run, $message);
    }
}
