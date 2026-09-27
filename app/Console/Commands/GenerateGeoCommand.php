<?php

namespace App\Console\Commands;

use App\Support\LlmsTxtBuilder;
use App\Support\SitemapBuilder;
use Illuminate\Console\Command;

class GenerateGeoCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'geo:generate {--clear : Clear the GEO and LLM text cache without regenerating}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate and pre-warm Generative Engine Optimization (GEO) feeds and LLM Markdown indexes';

    /**
     * Execute the console command.
     */
    public function handle(LlmsTxtBuilder $llms, SitemapBuilder $sitemap): int
    {
        if ($this->option('clear')) {
            LlmsTxtBuilder::clearCache();
            SitemapBuilder::clearCache();
            $this->info('GEO and Sitemap caches cleared successfully.');

            return self::SUCCESS;
        }

        LlmsTxtBuilder::clearCache();
        SitemapBuilder::clearCache();

        $llms->getCachedSummary();
        $llms->getCachedFull();
        $sitemap->getCachedXml();

        $this->info('GEO feeds (/llms.txt, /llms-full.txt, /sitemap.xml) generated and cached successfully.');

        return self::SUCCESS;
    }
}
