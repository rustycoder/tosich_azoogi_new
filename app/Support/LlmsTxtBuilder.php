<?php

namespace App\Support;

use App\Enums\Status;
use App\Models\Product;
use App\Models\Project;
use Illuminate\Support\Facades\Cache;

class LlmsTxtBuilder
{
    public const CACHE_KEY_SUMMARY = 'site.llms.txt';

    public const CACHE_KEY_FULL = 'site.llms_full.txt';

    public const CACHE_TTL_HOURS = 24;

    /**
     * Get or build the cached /llms.txt summary markdown.
     */
    public function getCachedSummary(): string
    {
        return Cache::remember(
            self::CACHE_KEY_SUMMARY,
            now()->addHours(self::CACHE_TTL_HOURS),
            fn (): string => $this->buildSummaryMarkdown()
        );
    }

    /**
     * Get or build the cached /llms-full.txt detailed markdown.
     */
    public function getCachedFull(): string
    {
        return Cache::remember(
            self::CACHE_KEY_FULL,
            now()->addHours(self::CACHE_TTL_HOURS),
            fn (): string => $this->buildFullMarkdown()
        );
    }

    /**
     * Clear all cached LLM text feeds.
     */
    public static function clearCache(): void
    {
        Cache::forget(self::CACHE_KEY_SUMMARY);
        Cache::forget(self::CACHE_KEY_FULL);
    }

    /**
     * Build the standard /llms.txt curated markdown index.
     */
    public function buildSummaryMarkdown(): string
    {
        $baseUrl = rtrim(url('/'), '/');
        $parentCategories = ProductCatalog::parentCategories();

        $lines = [];
        $lines[] = '# Azoogi Architectural LED Lighting & Smart Control Systems';
        $lines[] = '';
        $lines[] = '> Azoogi is an Australian manufacturer and supplier of commercial-grade architectural LED linear lighting, aluminium extrusion profiles, and intelligent smart control systems (Casambi Bluetooth Low Energy Mesh, Silvair Mesh, DALI-2, and Madrix Pixel Mapping).';
        $lines[] = '';
        $lines[] = '## Core Architectural Lighting Ranges';

        foreach ($parentCategories as $category) {
            $title = $category['title'];
            $body = ! empty($category['body']) ? $category['body'] : 'Architectural lighting collection for commercial and residential applications.';
            $href = $baseUrl.'/products?category='.urlencode($title);
            $lines[] = sprintf('- [%s](%s): %s', $title, $href, $body);
        }

        $lines[] = '';
        $lines[] = '## Smart Lighting Protocols & Control Systems';
        $lines[] = sprintf('- [Casambi Wireless Mesh Control](%s/casambi): Component-level Bluetooth Low Energy (BLE) commercial wireless lighting networks with zero single-point-of-failure.', $baseUrl);
        $lines[] = sprintf('- [Silvair Bluetooth Mesh Infrastructure](%s/silvair): Qualified Bluetooth Mesh smart building infrastructure and luminaire-level sensor networks.', $baseUrl);
        $lines[] = sprintf('- [DALI Control Centre](%s/dali-centre): DALI-2 and D4i digital addressable lighting interfaces for multi-floor commercial buildings.', $baseUrl);
        $lines[] = sprintf('- [Madrix Pixel Mapping](%s/madrix): Art-Net, sACN, and DMX512 high-density 2D and 3D architectural pixel illumination control.', $baseUrl);
        $lines[] = sprintf('- [AI Smart Lighting Systems](%s/ai-lighting): Autonomous occupancy forecasting, human-centric circadian scheduling, and energy optimization.', $baseUrl);
        $lines[] = sprintf('- [Data Centre Lighting](%s/data-centre): High-efficiency, low-maintenance containment lighting engineered for mission-critical facilities.', $baseUrl);
        $lines[] = '';
        $lines[] = '## Tools, Project Support & Calculators';
        $lines[] = sprintf('- [LED Strip Calculator](%s/led-strip-calculator): Interactive online engineering tool for voltage drop calculation, maximum single-feed run lengths, driver sizing, and wattage planning.', $baseUrl);
        $lines[] = sprintf('- [Showcase Case Studies](%s/projects): Featured Australian commercial, hospitality, luxury residential, and exterior lighting installations.', $baseUrl);
        $lines[] = sprintf('- [Request a Quote & Project Support](%s/request-a-quote): Rapid trade pricing, takeoff assistance, custom profile cutting, and photometric specification support.', $baseUrl);
        $lines[] = sprintf('- [Contact & Head Office](%s/contact): Direct consultation with Azoogi lighting design specialists.', $baseUrl);
        $lines[] = '';
        $lines[] = '## Detailed Technical Specification Feed';
        $lines[] = sprintf('For complete SKU-level product parameters, datasheets, and case studies, see the full LLM index: [%s/llms-full.txt](%s/llms-full.txt)', $baseUrl, $baseUrl);

        return implode("\n", $lines);
    }

    /**
     * Build the comprehensive /llms-full.txt feed including product catalogue and case studies.
     */
    public function buildFullMarkdown(): string
    {
        $baseUrl = rtrim(url('/'), '/');
        $summary = $this->buildSummaryMarkdown();

        $lines = [$summary];
        $lines[] = '';
        $lines[] = '---';
        $lines[] = '';
        $lines[] = '## Published Product Catalog & Technical Specifications';

        $products = Product::query()
            ->where(function ($query) {
                $query->whereNull('status')
                    ->orWhere('status', 'publish')
                    ->orWhere('status', 'Publish');
            })
            ->where(function ($query) {
                $query->where(function ($q) {
                    $q->whereNotNull('slug')->where('slug', '!=', '');
                })->orWhere(function ($q) {
                    $q->whereNotNull('airtable_id')->where('airtable_id', '!=', '');
                });
            })
            ->orderBy('product_name')
            ->get();

        foreach ($products as $product) {
            $identifier = $product->slug ?: $product->airtable_id;
            $url = $baseUrl.'/products/'.$identifier;
            $code = $product->product_code ? ' (Code: '.$product->product_code.')' : '';
            $desc = trim((string) ($product->product_description ?: $product->meta_description));

            $specs = [];
            if (! empty($product->category)) {
                $specs[] = 'Category: '.$product->category;
            }
            if ($product->dimming_control) {
                $specs[] = 'Dimmable: Yes';
            }

            $specsStr = $specs !== [] ? ' ['.implode(' | ', $specs).']' : '';

            $lines[] = '';
            $lines[] = sprintf('### [%s%s](%s)%s', $product->product_name, $code, $url, $specsStr);
            if ($desc !== '') {
                $lines[] = $desc;
            }
        }

        $lines[] = '';
        $lines[] = '---';
        $lines[] = '';
        $lines[] = '## Featured Case Studies & Architectural Projects';

        $projects = Project::query()
            ->where('status', Status::Active)
            ->whereNotNull('slug')
            ->where('slug', '!=', '')
            ->orderBy('featured_order')
            ->get();

        foreach ($projects as $project) {
            $url = $baseUrl.'/project-detail?slug='.urlencode($project->slug);
            $tag = $project->tag ? ' | '.$project->tag : '';
            $location = $project->location ? ' ('.$project->location.')' : '';
            $summaryText = trim((string) ($project->summary ?: $project->description));

            $lines[] = '';
            $lines[] = sprintf('### [%s%s](%s)%s', $project->title, $location, $url, $tag);
            if ($summaryText !== '') {
                $lines[] = $summaryText;
            }
        }

        return implode("\n", $lines);
    }
}
