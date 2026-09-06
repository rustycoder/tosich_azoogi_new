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

    /**
     * @param  (callable(array<string, mixed> $event): void)|null  $onProgress
     */
    public function sync(string $triggeredBy = 'schedule', ?callable $onProgress = null): ProductSync
    {
        if ($this->products->isSyncRunning()) {
            throw new RuntimeException('A product sync is already running.');
        }

        $startTime = microtime(true);

        $emit = function (
            int $percentage,
            string $step,
            string $log,
            int $current = 0,
            int $total = 0,
            ?int $etaSeconds = null,
            string $status = 'running',
        ) use ($onProgress, $startTime): void {
            if ($onProgress === null) {
                return;
            }

            $elapsed = (int) round(microtime(true) - $startTime);
            $etaHuman = null;

            if ($etaSeconds !== null) {
                if ($etaSeconds <= 0) {
                    $etaHuman = '< 5s';
                } elseif ($etaSeconds < 60) {
                    $etaHuman = "{$etaSeconds}s";
                } else {
                    $mins = intdiv($etaSeconds, 60);
                    $secs = $etaSeconds % 60;
                    $etaHuman = $secs > 0 ? "{$mins}m {$secs}s" : "{$mins}m";
                }
            }

            $onProgress([
                'percentage' => min(100, max(0, $percentage)),
                'step' => $step,
                'log' => $log,
                'current' => $current,
                'total' => $total,
                'elapsed_seconds' => $elapsed,
                'eta_seconds' => $etaSeconds,
                'eta_human' => $etaHuman,
                'status' => $status,
                'time' => now()->format('H:i:s'),
            ]);
        };

        $run = $this->products->startSync($triggeredBy);
        $emit(5, 'Initializing sync...', 'Starting product synchronization from Airtable...');

        try {
            $categoriesTable = (string) config('airtable.categories_table');
            $attributesTable = (string) config('airtable.attributes_table');
            $productsTable = (string) config('airtable.products_table');

            $emit(10, 'Fetching categories...', 'Connecting to Airtable categories table...');
            $this->log($run, 'Fetching categories from Airtable ['.$categoriesTable.'].');
            $categories = $this->safeFetch($run, $categoriesTable);
            $this->log($run, 'Fetched '.count($categories).' categor'.(count($categories) === 1 ? 'y' : 'ies').'.');
            $emit(15, 'Categories fetched', 'Retrieved '.count($categories).' category records.');

            $emit(20, 'Fetching attributes...', 'Connecting to Airtable attributes table...');
            $this->log($run, 'Fetching attributes from Airtable ['.$attributesTable.'].');
            $attributes = $this->safeFetch($run, $attributesTable);
            $this->log($run, 'Fetched '.count($attributes).' attribute'.(count($attributes) === 1 ? '' : 's').'.');
            $emit(25, 'Attributes fetched', 'Retrieved '.count($attributes).' attribute records.');

            $emit(28, 'Fetching products...', 'Connecting to Airtable products table...');
            $this->log($run, 'Fetching products from Airtable ['.$productsTable.'].');
            $records = $this->airtable->fetchRecords($productsTable);
            $this->log($run, 'Fetched '.count($records).' product record'.(count($records) === 1 ? '' : 's').'.');
            $emit(32, 'Products fetched', 'Retrieved '.count($records).' raw product records.');

            $emit(35, 'Compiling catalog...', 'Compiling and normalizing catalog data...');
            $this->log($run, 'Compiling published products.');
            $compiled = $this->normalizer->compileProducts($records, $categories, $attributes);
            $totalProducts = count($compiled);
            $this->log($run, 'Compiled '.$totalProducts.' published product'.($totalProducts === 1 ? '' : 's').'.');
            $emit(38, 'Catalog compiled', "Compiled {$totalProducts} published products for processing.", 0, $totalProducts);

            $this->log($run, 'Localizing product images.');
            $localizeStartTime = microtime(true);
            $localized = $this->images->localizeProducts(
                $compiled,
                function (int $current, int $total, array $product) use ($emit, $localizeStartTime): void {
                    $elapsedLoc = microtime(true) - $localizeStartTime;
                    $avgPerItem = $current > 0 ? ($elapsedLoc / $current) : 0;
                    $remainingItems = max(0, $total - $current);
                    $etaSec = (int) ceil($remainingItems * $avgPerItem);
                    $pct = 38 + (int) round(($current / max(1, $total)) * 47); // 38% to 85%
                    $name = (string) ($product['product_name'] ?? $product['product_code'] ?? "Product #{$current}");

                    $emit(
                        $pct,
                        "Downloading assets ({$current}/{$total})...",
                        "Cached assets for \"{$name}\" ({$current}/{$total})",
                        $current,
                        $total,
                        $etaSec,
                    );
                },
            );
            $this->log($run, $this->images->lastSummary());

            $keepIds = array_values(array_filter(array_map(
                fn (array $product): string => (string) ($product['id'] ?? ''),
                $localized,
            )));

            $emit(88, 'Saving lookups...', 'Persisting category and attribute records to database...');
            $this->log($run, 'Saving categories, attributes, and products.');
            $this->products->persistLookups($categories, $attributes);

            $emit(92, 'Saving products...', 'Persisting '.count($localized).' products to database...');
            $this->products->persistProducts($localized);

            $emit(96, 'Pruning stale records...', 'Pruning removed products...');
            $this->products->pruneMissingProducts($keepIds);
            $this->log($run, 'Saved '.count($keepIds).' product'.(count($keepIds) === 1 ? '' : 's').'.');

            $this->products->appendSyncLog($run, 'Sync finished.');
            $this->products->finishSync($run, true, count($keepIds));

            $totalTime = (int) round(microtime(true) - $startTime);
            $emit(
                100,
                'Sync completed successfully',
                "Product sync finished in {$totalTime}s! Active products: ".count($keepIds),
                count($keepIds),
                count($keepIds),
                0,
                'completed',
            );
        } catch (Throwable $exception) {
            $this->log($run, 'Failed: '.$exception->getMessage());
            Log::error('Product sync failed.', [
                'id' => $run->id,
                'exception' => $exception,
            ]);
            $this->products->finishSync($run->fresh() ?? $run, false, 0, $exception->getMessage());
            $emit(0, 'Sync failed', 'Error: '.$exception->getMessage(), 0, 0, null, 'failed');
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
