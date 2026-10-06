<?php

namespace App\Console\Commands;

use App\Services\Contracts\IProductSyncService;
use Illuminate\Console\Command;
use RuntimeException;
use Throwable;

class SyncProductsCommand extends Command
{
    protected $signature = 'products:sync {--force : Force reset any running or stuck syncs before executing}';

    protected $description = 'Pull Products, Categories, and Product attributes from Airtable into the product tables.';

    public function handle(IProductSyncService $products): int
    {
        $force = (bool) $this->option('force');

        try {
            $sync = $products->sync('schedule', null, $force);
        } catch (RuntimeException $exception) {
            $this->warn($exception->getMessage());

            return self::SUCCESS;
        } catch (Throwable $exception) {
            $this->error($exception->getMessage());

            $log = $products->latestSync()?->log;
            if (filled($log)) {
                $this->line($log);
            }

            return self::FAILURE;
        }

        $this->info('Synced '.$sync->products_count.' product(s).');
        if (filled($sync->log)) {
            $this->line($sync->log);
        }

        return self::SUCCESS;
    }
}
