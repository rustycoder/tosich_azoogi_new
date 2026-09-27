<?php

namespace App\Console\Commands;

use App\Support\SitemapBuilder;
use Illuminate\Console\Command;

class GenerateSitemapCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sitemap:generate {--clear : Clear the sitemap cache without regenerating}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate and cache the XML sitemap for the website';

    /**
     * Execute the console command.
     */
    public function handle(SitemapBuilder $builder): int
    {
        if ($this->option('clear')) {
            SitemapBuilder::clearCache();
            $this->info('Sitemap cache cleared successfully.');

            return self::SUCCESS;
        }

        SitemapBuilder::clearCache();
        $xml = $builder->getCachedXml();
        $urls = $builder->collectUrls();

        $this->info(sprintf('Sitemap generated successfully with %d URLs.', count($urls)));

        return self::SUCCESS;
    }
}
