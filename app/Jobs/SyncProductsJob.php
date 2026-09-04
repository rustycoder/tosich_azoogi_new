<?php

namespace App\Jobs;

use App\Services\Contracts\IProductSyncService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class SyncProductsJob implements ShouldQueue
{
    use Queueable;

    public int $timeout = 1200;

    public function __construct(public string $triggeredBy = 'schedule') {}

    public function handle(IProductSyncService $products): void
    {
        $products->sync($this->triggeredBy);
    }
}
