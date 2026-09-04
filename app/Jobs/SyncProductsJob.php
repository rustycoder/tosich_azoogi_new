<?php

namespace App\Jobs;

use App\Repositories\Contracts\IProductRepository;
use App\Services\Contracts\IProductSyncService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;
use Throwable;

class SyncProductsJob implements ShouldQueue
{
    use Queueable;

    public int $timeout = 1200;

    public function __construct(public string $triggeredBy = 'schedule') {}

    public function handle(IProductSyncService $products): void
    {
        Log::info('Product sync job started.', ['triggered_by' => $this->triggeredBy]);

        $products->sync($this->triggeredBy);

        Log::info('Product sync job finished.', ['triggered_by' => $this->triggeredBy]);
    }

    public function failed(?Throwable $exception): void
    {
        $message = $exception?->getMessage() ?? 'Sync job failed.';

        Log::error('Product sync job failed.', [
            'triggered_by' => $this->triggeredBy,
            'message' => $message,
        ]);

        $repository = app(IProductRepository::class);
        $run = $repository->latestSync();

        if ($run === null || ! $run->isRunning()) {
            return;
        }

        $repository->appendSyncLog($run, 'Job failed: '.$message);
        $repository->finishSync($run, false, (int) $run->products_count, $message);
    }
}
