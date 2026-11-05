<?php

declare(strict_types=1);

namespace App\Mcp\Tools\Backend;

use App\Mcp\Contracts\McpToolInterface;
use App\Models\Page;
use App\Support\LlmsTxtBuilder;
use App\Support\SitemapBuilder;
use Throwable;

class SeoAndGeoManagerTool implements McpToolInterface
{
    public function __construct(
        protected SitemapBuilder $sitemapBuilder,
        protected LlmsTxtBuilder $llmsBuilder,
    ) {}

    public function getName(): string
    {
        return 'backend_seo_and_geo_manager';
    }

    public function getDescription(): string
    {
        return 'Audits pages for SEO metadata completeness (titles, descriptions, OG share images), regenerates /sitemap.xml, and refreshes /llms.txt and /llms-full.txt AI feeds.';
    }

    /**
     * @return array<string, mixed>
     */
    public function getInputSchema(): array
    {
        return [
            'type' => 'object',
            'required' => ['action'],
            'properties' => [
                'action' => [
                    'type' => 'string',
                    'enum' => ['audit_seo', 'refresh_sitemap', 'refresh_llms_txt', 'refresh_all'],
                    'description' => 'SEO/GEO maintenance action to run.',
                ],
            ],
        ];
    }

    public function execute(array $arguments): array
    {
        $action = (string) ($arguments['action'] ?? 'audit_seo');
        $user = auth()->user();

        if ($user && ! $user->isAdmin() && ! $user->canManagePages()) {
            return [
                'isError' => true,
                'content' => 'You do not have permission to manage SEO or GEO feeds.',
            ];
        }

        try {
            if ($action === 'refresh_sitemap') {
                SitemapBuilder::clearCache();
                $xml = $this->sitemapBuilder->getCachedXml();

                return [
                    'isError' => false,
                    'content' => json_encode([
                        'action' => 'refresh_sitemap',
                        'message' => 'XML sitemap cache cleared and regenerated successfully.',
                        'bytes' => strlen($xml),
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES),
                ];
            }

            if ($action === 'refresh_llms_txt') {
                LlmsTxtBuilder::clearCache();
                $summary = $this->llmsBuilder->getCachedSummary();
                $full = $this->llmsBuilder->getCachedFull();

                return [
                    'isError' => false,
                    'content' => json_encode([
                        'action' => 'refresh_llms_txt',
                        'message' => 'GEO feeds (/llms.txt and /llms-full.txt) regenerated successfully.',
                        'summary_length' => strlen($summary),
                        'full_length' => strlen($full),
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES),
                ];
            }

            if ($action === 'refresh_all') {
                SitemapBuilder::clearCache();
                $xml = $this->sitemapBuilder->getCachedXml();
                LlmsTxtBuilder::clearCache();
                $summary = $this->llmsBuilder->getCachedSummary();
                $full = $this->llmsBuilder->getCachedFull();

                return [
                    'isError' => false,
                    'content' => json_encode([
                        'action' => 'refresh_all',
                        'message' => 'Both /sitemap.xml and /llms.txt GEO feeds have been refreshed.',
                        'sitemap_bytes' => strlen($xml),
                        'llms_summary_bytes' => strlen($summary),
                        'llms_full_bytes' => strlen($full),
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES),
                ];
            }

            // Default: audit_seo
            $pages = Page::with('meta')->orderBy('slug')->get();
            $auditRows = [];
            $missingMetaCount = 0;

            foreach ($pages as $page) {
                $metas = $page->meta->pluck('value', 'key');
                $metaTitle = trim((string) ($metas['meta.title'] ?? $metas['seo.title'] ?? $page->title ?? ''));
                $metaDesc = trim((string) ($page->meta_description ?? $metas['meta.description'] ?? $metas['seo.description'] ?? $metas['hero.lead'] ?? ''));
                $ogImage = trim((string) ($page->og_image ?? $metas['meta.og_image'] ?? $metas['hero.poster'] ?? ''));

                $issues = [];
                if ($metaTitle === '') {
                    $issues[] = 'missing_title';
                }
                if ($metaDesc === '') {
                    $issues[] = 'missing_description';
                } elseif (mb_strlen($metaDesc) < 50 || mb_strlen($metaDesc) > 170) {
                    $issues[] = 'description_length_warning ('.mb_strlen($metaDesc).' chars)';
                }
                if ($ogImage === '') {
                    $issues[] = 'missing_og_image';
                }

                if (! empty($issues)) {
                    $missingMetaCount++;
                }

                $auditRows[] = [
                    'slug' => $page->slug,
                    'title' => $page->title,
                    'status' => $page->status?->value ?? (string) $page->status,
                    'meta_title_length' => mb_strlen($metaTitle),
                    'meta_description_length' => mb_strlen($metaDesc),
                    'has_og_image' => $ogImage !== '',
                    'issues' => $issues,
                ];
            }

            return [
                'isError' => false,
                'content' => json_encode([
                    'action' => 'audit_seo',
                    'total_pages' => count($auditRows),
                    'pages_with_warnings' => $missingMetaCount,
                    'pages' => $auditRows,
                ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES),
            ];
        } catch (Throwable $e) {
            return [
                'isError' => true,
                'content' => 'SEO & GEO tool failed: '.$e->getMessage(),
            ];
        }
    }
}
