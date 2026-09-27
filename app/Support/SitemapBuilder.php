<?php

namespace App\Support;

use App\Enums\Status;
use App\Models\Page;
use App\Models\Product;
use App\Models\Project;
use Illuminate\Support\Facades\Cache;

class SitemapBuilder
{
    public const CACHE_KEY = 'site.sitemap.xml';

    public const CACHE_TTL_HOURS = 24;

    /**
     * Non-public page slugs that should never appear in the sitemap.
     */
    private const EXCLUDED_PAGE_SLUGS = [
        'header',
        'footer',
        '403-forbidden',
        '404-not-found',
        '419-page-expired',
        '500-server-error',
        '503-service-unavailable',
    ];

    /**
     * Get or build the cached sitemap XML string.
     */
    public function getCachedXml(): string
    {
        return Cache::remember(self::CACHE_KEY, now()->addHours(self::CACHE_TTL_HOURS), fn (): string => $this->buildXml());
    }

    /**
     * Clear the cached sitemap.
     */
    public static function clearCache(): void
    {
        Cache::forget(self::CACHE_KEY);
    }

    /**
     * Build the raw XML sitemap string.
     */
    public function buildXml(): string
    {
        $urls = $this->collectUrls();

        $xml = '<?xml version="1.0" encoding="UTF-8"?>'."\n";
        $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">'."\n";

        foreach ($urls as $entry) {
            $xml .= "  <url>\n";
            $xml .= '    <loc>'.htmlspecialchars($entry['loc'], ENT_XML1, 'UTF-8')."</loc>\n";

            if (! empty($entry['lastmod'])) {
                $xml .= '    <lastmod>'.htmlspecialchars($entry['lastmod'], ENT_XML1, 'UTF-8')."</lastmod>\n";
            }

            if (! empty($entry['changefreq'])) {
                $xml .= '    <changefreq>'.htmlspecialchars($entry['changefreq'], ENT_XML1, 'UTF-8')."</changefreq>\n";
            }

            if (isset($entry['priority'])) {
                $xml .= '    <priority>'.number_format((float) $entry['priority'], 1, '.', '')."</priority>\n";
            }

            $xml .= "  </url>\n";
        }

        $xml .= '</urlset>';

        return $xml;
    }

    /**
     * Collect all public URLs with their metadata.
     *
     * @return list<array{loc: string, lastmod: ?string, changefreq: string, priority: float}>
     */
    public function collectUrls(): array
    {
        $urls = [];

        // 1. CMS Public Pages
        $pages = Page::query()
            ->where('status', Status::Active)
            ->whereNotIn('slug', self::EXCLUDED_PAGE_SLUGS)
            ->get();

        foreach ($pages as $page) {
            $slug = $page->slug;
            $loc = url($page->publicPath());
            $lastmod = $page->updated_at?->format('Y-m-d');

            [$priority, $changefreq] = $this->pagePriorityAndFrequency($slug);

            $urls[] = [
                'loc' => $loc,
                'lastmod' => $lastmod,
                'changefreq' => $changefreq,
                'priority' => $priority,
            ];
        }

        // 2. Parent Product Categories
        $parentCategories = ProductCatalog::parentCategories();
        foreach ($parentCategories as $category) {
            if (empty($category['title'])) {
                continue;
            }

            $urls[] = [
                'loc' => url('/products').'?category='.urlencode($category['title']),
                'lastmod' => null,
                'changefreq' => 'weekly',
                'priority' => 0.8,
            ];
        }

        // 3. Published Individual Products
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
            ->get(['slug', 'airtable_id', 'updated_at']);

        foreach ($products as $product) {
            $identifier = $product->slug ?: $product->airtable_id;
            $urls[] = [
                'loc' => url('/products/'.$identifier),
                'lastmod' => $product->updated_at?->format('Y-m-d'),
                'changefreq' => 'weekly',
                'priority' => 0.8,
            ];
        }

        // 4. Active Projects
        $projects = Project::query()
            ->where('status', Status::Active)
            ->whereNotNull('slug')
            ->where('slug', '!=', '')
            ->orderBy('featured_order')
            ->get(['slug', 'updated_at']);

        foreach ($projects as $project) {
            $urls[] = [
                'loc' => url('/project-detail').'?slug='.urlencode($project->slug),
                'lastmod' => $project->updated_at?->format('Y-m-d'),
                'changefreq' => 'monthly',
                'priority' => 0.7,
            ];
        }

        return $urls;
    }

    /**
     * @return array{0: float, 1: string}
     */
    private function pagePriorityAndFrequency(string $slug): array
    {
        return match ($slug) {
            'home' => [1.0, 'daily'],
            'products', 'solutions' => [0.9, 'weekly'],
            'casambi', 'silvair', 'dali-centre', 'madrix', 'ai-lighting', 'data-centre', 'projects' => [0.8, 'weekly'],
            'about', 'led-strip-calculator', 'contact', 'request-a-quote', 'architect-designer', 'electrician-builder', 'home-owner', 'wholesaler' => [0.7, 'monthly'],
            'privacy', 'terms', 'warranty-returns', 'modern-slavery' => [0.3, 'yearly'],
            default => [0.5, 'monthly'],
        };
    }
}
