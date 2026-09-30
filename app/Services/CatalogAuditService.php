<?php

namespace App\Services;

use App\Models\Product;
use App\Models\ProductAttribute;
use App\Models\ProductCategory;
use App\Models\ProductSync;
use App\Services\Contracts\ICatalogAuditService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class CatalogAuditService implements ICatalogAuditService
{
    public const string CACHE_KEY = 'catalog_audit_latest_report';

    public static ?array $memoryReport = null;

    /**
     * Run a comprehensive audit of the product, category, and attribute catalog.
     *
     * @return array<string, mixed>
     */
    public function audit(): array
    {
        $totalProducts = Product::query()->count();
        $publishedProducts = Product::query()->where('status', 'publish')->count();
        $draftProducts = Product::query()->where('status', 'draft')->count();
        $inactiveProducts = Product::query()->where('status', 'inactive')->count();

        $totalCategories = ProductCategory::query()->count();
        $rootCategories = ProductCategory::query()->whereNull('parent_airtable_id')->orWhere('parent_airtable_id', '')->count();

        $totalAttributes = ProductAttribute::query()->count();
        $visibleAttributes = ProductAttribute::query()->where('is_visible_on_filters', true)->count();

        // 1. Media & Assets Audits
        $missingCoverQuery = Product::query()
            ->where(function ($q) {
                $q->whereNull('cover')->orWhere('cover', '');
            })
            ->where(function ($q) {
                $q->whereNull('product_images')
                    ->orWhere('product_images', '')
                    ->orWhere('product_images', '[]')
                    ->orWhere('product_images', 'null');
            });
        $missingCoverCount = (clone $missingCoverQuery)->count();
        $missingCoverSamples = (clone $missingCoverQuery)->take(10)->get(['id', 'product_name', 'product_code', 'slug'])->toArray();

        $missingGalleryQuery = Product::query()->where(function ($q) {
            $q->whereNull('product_images')
                ->orWhere('product_images', '')
                ->orWhere('product_images', '[]')
                ->orWhere('product_images', 'null');
        });
        $missingGalleryCount = (clone $missingGalleryQuery)->count();

        $missingDimensionQuery = Product::query()->where(function ($q) {
            $q->whereNull('product_dimension')
                ->orWhere('product_dimension', '')
                ->orWhere('product_dimension', '[]')
                ->orWhere('product_dimension', 'null');
        });
        $missingDimensionCount = (clone $missingDimensionQuery)->count();
        $missingDimensionSamples = (clone $missingDimensionQuery)->take(10)->get(['id', 'product_name', 'product_code', 'slug'])->toArray();

        $missingTechIconsQuery = Product::query()->where(function ($q) {
            $q->whereNull('technical_icons')
                ->orWhere('technical_icons', '')
                ->orWhere('technical_icons', '[]')
                ->orWhere('technical_icons', 'null');
        });
        $missingTechIconsCount = (clone $missingTechIconsQuery)->count();

        // 2. Technical Documents Audits
        $missingDatasheetFileQuery = Product::query()
            ->where(function ($q) {
                $q->where('datasheet', 'like', '%Yes%')
                    ->orWhere('datasheet', '1')
                    ->orWhere('datasheet', 'true')
                    ->orWhere('datasheet', '["Yes"]');
            })
            ->where(function ($q) {
                $q->whereNull('datasheet_file')
                    ->orWhere('datasheet_file', '')
                    ->orWhere('datasheet_file', '[]')
                    ->orWhere('datasheet_file', 'null');
            });
        $missingDatasheetFileCount = (clone $missingDatasheetFileQuery)->count();
        $missingDatasheetFileSamples = (clone $missingDatasheetFileQuery)->take(10)->get(['id', 'product_name', 'product_code', 'slug'])->toArray();

        $missingGuideQuery = Product::query()->where(function ($q) {
            $q->whereNull('installation_guide_file')
                ->orWhere('installation_guide_file', '')
                ->orWhere('installation_guide_file', '[]')
                ->orWhere('installation_guide_file', 'null');
        });
        $missingGuideCount = (clone $missingGuideQuery)->count();

        $missingManualQuery = Product::query()->where(function ($q) {
            $q->whereNull('user_manual')
                ->orWhere('user_manual', '')
                ->orWhere('user_manual', '[]')
                ->orWhere('user_manual', 'null');
        });
        $missingManualCount = (clone $missingManualQuery)->count();

        $missingIesQuery = Product::query()->where(function ($q) {
            $q->whereNull('ies_file')
                ->orWhere('ies_file', '')
                ->orWhere('ies_file', '[]')
                ->orWhere('ies_file', 'null');
        });
        $missingIesCount = (clone $missingIesQuery)->count();
        $missingIesSamples = (clone $missingIesQuery)->take(10)->get(['id', 'product_name', 'product_code', 'slug'])->toArray();

        // 3. Core ID & Specifications Audits
        $missingSkuQuery = Product::query()->where(function ($q) {
            $q->whereNull('product_code')->orWhere('product_code', '');
        });
        $missingSkuCount = (clone $missingSkuQuery)->count();
        $missingSkuSamples = (clone $missingSkuQuery)->take(10)->get(['id', 'product_name', 'product_code', 'slug'])->toArray();

        $missingSupplierCodeQuery = Product::query()->where(function ($q) {
            $q->whereNull('supplier_code')->orWhere('supplier_code', '');
        });
        $missingSupplierCodeCount = (clone $missingSupplierCodeQuery)->count();

        $missingCategoryQuery = Product::query()->where(function ($q) {
            $q->whereNull('category')->orWhere('category', '');
        });
        $missingCategoryCount = (clone $missingCategoryQuery)->count();

        // 4. SEO & Copy Audits
        $missingSlugQuery = Product::query()->where(function ($q) {
            $q->whereNull('slug')->orWhere('slug', '');
        });
        $missingSlugCount = (clone $missingSlugQuery)->count();

        $missingDescriptionQuery = Product::query()->where(function ($q) {
            $q->whereNull('product_description')->orWhere('product_description', '');
        });
        $missingDescriptionCount = (clone $missingDescriptionQuery)->count();
        $missingDescriptionSamples = (clone $missingDescriptionQuery)->take(10)->get(['id', 'product_name', 'product_code', 'slug'])->toArray();

        $missingMetaTitleQuery = Product::query()->where(function ($q) {
            $q->whereNull('meta_title')->orWhere('meta_title', '');
        });
        $missingMetaTitleCount = (clone $missingMetaTitleQuery)->count();

        $missingMetaDescriptionQuery = Product::query()->where(function ($q) {
            $q->whereNull('meta_description')->orWhere('meta_description', '');
        });
        $missingMetaDescriptionCount = (clone $missingMetaDescriptionQuery)->count();

        // 5. Data Integrity & Relational Audits
        $duplicateSlugs = Product::query()
            ->select('slug', DB::raw('COUNT(*) as count'))
            ->whereNotNull('slug')
            ->where('slug', '!=', '')
            ->groupBy('slug')
            ->having('count', '>', 1)
            ->pluck('count', 'slug')
            ->toArray();

        $duplicateSkus = Product::query()
            ->select('product_code', DB::raw('COUNT(*) as count'))
            ->whereNotNull('product_code')
            ->where('product_code', '!=', '')
            ->groupBy('product_code')
            ->having('count', '>', 1)
            ->pluck('count', 'product_code')
            ->toArray();

        // Category Integrity: Categories attached to products that do not exist in product_categories
        $existingCategoryNames = ProductCategory::query()->pluck('name')->all();
        $existingCategoryAirtableIds = ProductCategory::query()->pluck('airtable_id')->all();
        $productCategoriesUsed = Product::query()->whereNotNull('category')->where('category', '!=', '')->distinct()->pluck('category')->all();
        $orphanCategories = array_values(array_diff($productCategoriesUsed, $existingCategoryNames));

        // Subcategories referencing non-existent parent categories
        $orphanParents = ProductCategory::query()
            ->whereNotNull('parent_airtable_id')
            ->where('parent_airtable_id', '!=', '')
            ->whereNotIn('parent_airtable_id', $existingCategoryAirtableIds)
            ->pluck('parent_airtable_id', 'name')
            ->toArray();

        // 6. Image Standards & Diagnostics (WebP Images & SVG Icons)
        $imageDiagnostics = $this->auditImageStandards();

        // 7. SEO & Copywriting Character Count Standards
        $seoStandards = $this->auditSeoAndCopyStandards();

        // Health Score Calculation (0-100%)
        $healthScore = 100;
        if ($totalProducts > 0) {
            $coverPenalty = ($missingCoverCount / $totalProducts) * 15;
            $skuPenalty = ($missingSkuCount / $totalProducts) * 15;
            $slugPenalty = ($missingSlugCount / $totalProducts) * 15;
            $categoryPenalty = ($missingCategoryCount / $totalProducts) * 10;
            $descPenalty = ($missingDescriptionCount / $totalProducts) * 10;
            $metaPenalty = ($missingMetaDescriptionCount / $totalProducts) * 10;
            $datasheetPenalty = ($missingDatasheetFileCount / max(1, $totalProducts)) * 10;
            $imageFormatPenalty = ($imageDiagnostics['non_webp_covers']['count'] / $totalProducts) * 10;
            $iconFormatPenalty = ($imageDiagnostics['non_svg_tech_icons']['count'] / $totalProducts) * 5;

            $seoLengthPenalty = (($seoStandards['meta_title']['too_long_count'] + $seoStandards['meta_title']['too_short_count'] + $seoStandards['meta_description']['too_short_count'] + $seoStandards['meta_description']['too_long_count']) / $totalProducts) * 5;

            $totalPenalty = $coverPenalty + $skuPenalty + $slugPenalty + $categoryPenalty + $descPenalty + $metaPenalty + $datasheetPenalty + $imageFormatPenalty + $iconFormatPenalty + $seoLengthPenalty;
            $healthScore = max(0, min(100, (int) round(100 - $totalPenalty)));
        }

        $latestSync = ProductSync::query()->latest('id')->first();

        $report = [
            'summary' => [
                'total_products' => $totalProducts,
                'published_products' => $publishedProducts,
                'draft_products' => $draftProducts,
                'inactive_products' => $inactiveProducts,
                'total_categories' => $totalCategories,
                'root_categories' => $rootCategories,
                'total_attributes' => $totalAttributes,
                'visible_attributes' => $visibleAttributes,
                'health_score' => $healthScore,
            ],
            'media' => [
                'missing_cover' => [
                    'label' => 'Missing Cover Images',
                    'count' => $missingCoverCount,
                    'severity' => $missingCoverCount > 0 ? 'high' : 'ok',
                    'samples' => $missingCoverSamples,
                ],
                'missing_gallery' => [
                    'label' => 'Missing Gallery Images',
                    'count' => $missingGalleryCount,
                    'severity' => $missingGalleryCount > 0 ? 'medium' : 'ok',
                ],
                'missing_dimensions' => [
                    'label' => 'Missing Dimension Diagrams',
                    'count' => $missingDimensionCount,
                    'severity' => $missingDimensionCount > 0 ? 'medium' : 'ok',
                    'samples' => $missingDimensionSamples,
                ],
                'missing_tech_icons' => [
                    'label' => 'Missing Technical Icons',
                    'count' => $missingTechIconsCount,
                    'severity' => $missingTechIconsCount > 0 ? 'low' : 'ok',
                ],
            ],
            'image_standards' => $imageDiagnostics,
            'documents' => [
                'missing_datasheet_file' => [
                    'label' => 'Missing Datasheet PDF (Required)',
                    'count' => $missingDatasheetFileCount,
                    'severity' => $missingDatasheetFileCount > 0 ? 'high' : 'ok',
                    'samples' => $missingDatasheetFileSamples,
                ],
                'missing_guide' => [
                    'label' => 'Missing Installation Guides',
                    'count' => $missingGuideCount,
                    'severity' => $missingGuideCount > 0 ? 'low' : 'ok',
                ],
                'missing_manual' => [
                    'label' => 'Missing User Manuals',
                    'count' => $missingManualCount,
                    'severity' => $missingManualCount > 0 ? 'low' : 'ok',
                ],
                'missing_ies' => [
                    'label' => 'Missing IES Photometric Files',
                    'count' => $missingIesCount,
                    'severity' => $missingIesCount > 0 ? 'medium' : 'ok',
                    'samples' => $missingIesSamples,
                ],
            ],
            'core' => [
                'missing_sku' => [
                    'label' => 'Missing Product Code (SKU)',
                    'count' => $missingSkuCount,
                    'severity' => $missingSkuCount > 0 ? 'critical' : 'ok',
                    'samples' => $missingSkuSamples,
                ],
                'missing_supplier_code' => [
                    'label' => 'Missing Supplier Codes',
                    'count' => $missingSupplierCodeCount,
                    'severity' => $missingSupplierCodeCount > 0 ? 'medium' : 'ok',
                ],
                'missing_category' => [
                    'label' => 'Missing Primary Category',
                    'count' => $missingCategoryCount,
                    'severity' => $missingCategoryCount > 0 ? 'high' : 'ok',
                ],
            ],
            'seo' => [
                'missing_slug' => [
                    'label' => 'Missing URL Slugs',
                    'count' => $missingSlugCount,
                    'severity' => $missingSlugCount > 0 ? 'critical' : 'ok',
                ],
                'missing_description' => [
                    'label' => 'Missing Product Descriptions',
                    'count' => $missingDescriptionCount,
                    'severity' => $missingDescriptionCount > 0 ? 'medium' : 'ok',
                    'samples' => $missingDescriptionSamples,
                ],
                'missing_meta_title' => [
                    'label' => 'Missing Meta Titles',
                    'count' => $missingMetaTitleCount,
                    'severity' => $missingMetaTitleCount > 0 ? 'medium' : 'ok',
                ],
                'missing_meta_description' => [
                    'label' => 'Missing Meta Descriptions',
                    'count' => $missingMetaDescriptionCount,
                    'severity' => $missingMetaDescriptionCount > 0 ? 'medium' : 'ok',
                ],
                'standards' => $seoStandards,
            ],
            'integrity' => [
                'duplicate_slugs' => [
                    'label' => 'Duplicate URL Slugs',
                    'count' => count($duplicateSlugs),
                    'items' => $duplicateSlugs,
                    'severity' => count($duplicateSlugs) > 0 ? 'critical' : 'ok',
                ],
                'duplicate_skus' => [
                    'label' => 'Duplicate Product Codes (SKUs)',
                    'count' => count($duplicateSkus),
                    'items' => $duplicateSkus,
                    'severity' => count($duplicateSkus) > 0 ? 'critical' : 'ok',
                ],
                'orphan_categories' => [
                    'label' => 'Orphan Categories on Products',
                    'count' => count($orphanCategories),
                    'items' => $orphanCategories,
                    'severity' => count($orphanCategories) > 0 ? 'medium' : 'ok',
                ],
                'orphan_parents' => [
                    'label' => 'Categories with Missing Parent',
                    'count' => count($orphanParents),
                    'items' => $orphanParents,
                    'severity' => count($orphanParents) > 0 ? 'medium' : 'ok',
                ],
            ],
            'latest_sync' => $latestSync,
            'audited_at' => now()->toIso8601String(),
            'audited_at_human' => now()->format('d M Y, g:i A'),
        ];

        self::$memoryReport = $report;
        Cache::forever(self::CACHE_KEY, $report);

        try {
            $storageDir = storage_path('framework/cache');
            if (! is_dir($storageDir)) {
                @mkdir($storageDir, 0755, true);
            }
            $encoded = json_encode($report, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
            if ($encoded) {
                @file_put_contents($storageDir.'/catalog_audit.json', $encoded);
            }
        } catch (\Throwable) {
            // Silently ignore file write issues if storage permissions are restricted
        }

        return $report;
    }

    /**
     * Get the latest stored audit report without recalculating against the database.
     *
     * @return array<string, mixed>|null
     */
    public function getLatestAudit(): ?array
    {
        if (self::$memoryReport !== null) {
            return self::$memoryReport;
        }

        $cached = Cache::get(self::CACHE_KEY);
        if ($cached !== null) {
            self::$memoryReport = $cached;

            return $cached;
        }

        $filePath = storage_path('framework/cache/catalog_audit.json');
        if (file_exists($filePath)) {
            $contents = @file_get_contents($filePath);
            if ($contents) {
                $decoded = json_decode($contents, true);
                if (is_array($decoded)) {
                    self::$memoryReport = $decoded;

                    return $decoded;
                }
            }
        }

        return null;
    }

    /**
     * Clear any cached/stored audit report.
     */
    public function clearStoredAudit(): void
    {
        self::$memoryReport = null;
        Cache::forget(self::CACHE_KEY);

        $filePath = storage_path('framework/cache/catalog_audit.json');
        if (file_exists($filePath)) {
            @unlink($filePath);
        }
    }

    /**
     * Audit image format standards (WebP for images, SVG for icons), dimensions, and aspect ratio.
     *
     * @return array<string, mixed>
     */
    private function auditImageStandards(): array
    {
        $nonWebpCovers = [];
        $nonWebpGallery = [];
        $nonWebpDimensions = [];
        $nonSvgTechIcons = [];
        $nonSquareCovers = [];
        $lowResCovers = [];

        $products = Product::query()
            ->select(['id', 'product_name', 'product_code', 'slug', 'cover', 'product_images', 'product_dimension', 'technical_icons'])
            ->get();

        foreach ($products as $product) {
            $coverUrl = $product->coverUrl();
            if (filled($coverUrl)) {
                $ext = $this->extractExtension($coverUrl);
                if ($ext && $ext !== 'webp') {
                    $nonWebpCovers[] = [
                        'id' => $product->id,
                        'name' => $product->product_name,
                        'code' => $product->product_code,
                        'url' => $coverUrl,
                        'format' => strtoupper($ext),
                    ];
                }

                // Check local dimension & aspect ratio
                $dimensions = $this->getImageDimensions($coverUrl);
                if ($dimensions) {
                    if (! $dimensions['is_square']) {
                        $nonSquareCovers[] = [
                            'id' => $product->id,
                            'name' => $product->product_name,
                            'code' => $product->product_code,
                            'url' => $coverUrl,
                            'dimensions' => "{$dimensions['width']}×{$dimensions['height']}",
                            'ratio' => $dimensions['ratio'],
                        ];
                    }
                    if ($dimensions['width'] < 600 || $dimensions['height'] < 600) {
                        $lowResCovers[] = [
                            'id' => $product->id,
                            'name' => $product->product_name,
                            'code' => $product->product_code,
                            'url' => $coverUrl,
                            'dimensions' => "{$dimensions['width']}×{$dimensions['height']}",
                        ];
                    }
                }
            }

            // Check Gallery Images
            $gallery = $product->galleryImageUrls();
            foreach ($gallery as $gUrl) {
                $ext = $this->extractExtension($gUrl);
                if ($ext && $ext !== 'webp') {
                    $nonWebpGallery[] = [
                        'id' => $product->id,
                        'name' => $product->product_name,
                        'code' => $product->product_code,
                        'url' => $gUrl,
                        'format' => strtoupper($ext),
                    ];
                    break;
                }
            }

            // Check Dimension Diagrams (Must be WebP only)
            $dimUrl = $product->dimensionUrl();
            if (filled($dimUrl)) {
                $ext = $this->extractExtension($dimUrl);
                if ($ext && $ext !== 'webp') {
                    $nonWebpDimensions[] = [
                        'id' => $product->id,
                        'name' => $product->product_name,
                        'code' => $product->product_code,
                        'url' => $dimUrl,
                        'format' => strtoupper($ext),
                    ];
                }
            }

            // Check Technical Icons (Must be SVG only)
            $techIcons = $product->technicalIconUrls();
            foreach ($techIcons as $tUrl) {
                $ext = $this->extractExtension($tUrl);
                if ($ext && $ext !== 'svg') {
                    $nonSvgTechIcons[] = [
                        'id' => $product->id,
                        'name' => $product->product_name,
                        'code' => $product->product_code,
                        'url' => $tUrl,
                        'format' => strtoupper($ext),
                    ];
                    break;
                }
            }
        }

        // Category Featured Images & Icons
        $nonWebpCategories = [];
        $nonSvgCategoryIcons = [];
        $categories = ProductCategory::query()->select(['id', 'name', 'featured_image', 'icon'])->get();
        foreach ($categories as $cat) {
            $fImg = $cat->featuredImageUrl();
            if ($fImg) {
                $ext = $this->extractExtension($fImg);
                if ($ext && $ext !== 'webp') {
                    $nonWebpCategories[] = [
                        'id' => $cat->id,
                        'name' => $cat->name,
                        'url' => $fImg,
                        'format' => strtoupper($ext),
                    ];
                }
            }
            $icon = $cat->iconUrl();
            if ($icon) {
                $ext = $this->extractExtension($icon);
                if ($ext && $ext !== 'svg') {
                    $nonSvgCategoryIcons[] = [
                        'id' => $cat->id,
                        'name' => $cat->name,
                        'url' => $icon,
                        'format' => strtoupper($ext),
                    ];
                }
            }
        }

        // Product Attribute Icons
        $nonSvgAttributeIcons = [];
        $attributes = ProductAttribute::query()->whereNotNull('icon')->where('icon', '!=', '')->select(['id', 'name', 'value', 'icon'])->get();
        foreach ($attributes as $attr) {
            $icon = $attr->iconUrl();
            if ($icon) {
                $ext = $this->extractExtension($icon);
                if ($ext && $ext !== 'svg') {
                    $nonSvgAttributeIcons[] = [
                        'id' => $attr->id,
                        'group' => $attr->name,
                        'value' => $attr->value,
                        'url' => $icon,
                        'format' => strtoupper($ext),
                    ];
                }
            }
        }

        return [
            'non_webp_covers' => [
                'label' => 'Non-WebP Product Covers (Expected: WebP)',
                'count' => count($nonWebpCovers),
                'samples' => array_slice($nonWebpCovers, 0, 10),
                'severity' => count($nonWebpCovers) > 0 ? 'high' : 'ok',
            ],
            'non_webp_gallery' => [
                'label' => 'Non-WebP Gallery Images (Expected: WebP)',
                'count' => count($nonWebpGallery),
                'samples' => array_slice($nonWebpGallery, 0, 10),
                'severity' => count($nonWebpGallery) > 0 ? 'medium' : 'ok',
            ],
            'non_webp_dimensions' => [
                'label' => 'Non-WebP Dimension Diagrams (Expected: WebP)',
                'count' => count($nonWebpDimensions),
                'samples' => array_slice($nonWebpDimensions, 0, 10),
                'severity' => count($nonWebpDimensions) > 0 ? 'medium' : 'ok',
            ],
            'non_webp_categories' => [
                'label' => 'Non-WebP Category Banners (Expected: WebP)',
                'count' => count($nonWebpCategories),
                'samples' => array_slice($nonWebpCategories, 0, 10),
                'severity' => count($nonWebpCategories) > 0 ? 'medium' : 'ok',
            ],
            'non_svg_tech_icons' => [
                'label' => 'Non-SVG Technical Icons (Expected: SVG only)',
                'count' => count($nonSvgTechIcons),
                'samples' => array_slice($nonSvgTechIcons, 0, 10),
                'severity' => count($nonSvgTechIcons) > 0 ? 'high' : 'ok',
            ],
            'non_svg_category_icons' => [
                'label' => 'Non-SVG Category Icons (Expected: SVG only)',
                'count' => count($nonSvgCategoryIcons),
                'samples' => array_slice($nonSvgCategoryIcons, 0, 10),
                'severity' => count($nonSvgCategoryIcons) > 0 ? 'medium' : 'ok',
            ],
            'non_svg_attribute_icons' => [
                'label' => 'Non-SVG Attribute Icons (Expected: SVG only)',
                'count' => count($nonSvgAttributeIcons),
                'samples' => array_slice($nonSvgAttributeIcons, 0, 10),
                'severity' => count($nonSvgAttributeIcons) > 0 ? 'medium' : 'ok',
            ],
            'non_square_covers' => [
                'label' => 'Non-Square Product Covers (Target: 1:1)',
                'count' => count($nonSquareCovers),
                'samples' => array_slice($nonSquareCovers, 0, 10),
                'severity' => count($nonSquareCovers) > 0 ? 'medium' : 'ok',
            ],
            'low_res_covers' => [
                'label' => 'Low Resolution Product Covers (< 600px)',
                'count' => count($lowResCovers),
                'samples' => array_slice($lowResCovers, 0, 10),
                'severity' => count($lowResCovers) > 0 ? 'medium' : 'ok',
            ],
        ];
    }

    /**
     * Extract extension from URL/path ignoring query params.
     */
    private function extractExtension(?string $url): ?string
    {
        if (empty($url)) {
            return null;
        }

        $cleanPath = parse_url($url, PHP_URL_PATH) ?? $url;
        $ext = strtolower(pathinfo($cleanPath, PATHINFO_EXTENSION));

        return $ext !== '' ? $ext : null;
    }

    /**
     * Get image dimensions and aspect ratio for local assets.
     *
     * @return array{width: int, height: int, ratio: float, is_square: bool}|null
     */
    private function getImageDimensions(?string $url): ?array
    {
        if (empty($url)) {
            return null;
        }

        $cleanPath = parse_url($url, PHP_URL_PATH) ?? $url;
        $localFile = null;

        if (str_starts_with($cleanPath, '/assets/') || str_starts_with($cleanPath, 'assets/')) {
            $localFile = public_path(ltrim($cleanPath, '/'));
        } elseif (str_starts_with($cleanPath, '/storage/') || str_starts_with($cleanPath, 'storage/')) {
            $localFile = public_path(ltrim($cleanPath, '/'));
        }

        if ($localFile && file_exists($localFile) && is_file($localFile)) {
            $info = @getimagesize($localFile);
            if ($info && isset($info[0], $info[1]) && $info[1] > 0) {
                $width = (int) $info[0];
                $height = (int) $info[1];
                $ratio = round($width / $height, 2);

                return [
                    'width' => $width,
                    'height' => $height,
                    'ratio' => $ratio,
                    'is_square' => ($width / $height) >= 0.95 && ($width / $height) <= 1.05,
                ];
            }
        }

        return null;
    }

    /**
     * Audit character count standards for product copy, meta title, and meta description.
     *
     * @return array<string, mixed>
     */
    private function auditSeoAndCopyStandards(): array
    {
        $products = Product::query()
            ->select(['id', 'product_name', 'product_code', 'slug', 'meta_title', 'meta_description', 'product_description'])
            ->get();

        $metaTitleOptimal = 0;
        $metaTitleTooShort = [];
        $metaTitleTooLong = [];

        $metaDescOptimal = 0;
        $metaDescTooShort = [];
        $metaDescTooLong = [];

        $descOptimal = 0;
        $descTooShort = [];
        $descTooLong = [];

        foreach ($products as $product) {
            // Meta Title: Target 30 - 60 chars
            $title = trim((string) $product->meta_title);
            if ($title !== '') {
                $len = mb_strlen($title);
                if ($len < 30) {
                    $metaTitleTooShort[] = [
                        'id' => $product->id,
                        'name' => $product->product_name,
                        'code' => $product->product_code,
                        'length' => $len,
                        'value' => $title,
                    ];
                } elseif ($len > 60) {
                    $metaTitleTooLong[] = [
                        'id' => $product->id,
                        'name' => $product->product_name,
                        'code' => $product->product_code,
                        'length' => $len,
                        'value' => $title,
                    ];
                } else {
                    $metaTitleOptimal++;
                }
            }

            // Meta Description: Target 70 - 160 chars
            $metaDesc = trim((string) $product->meta_description);
            if ($metaDesc !== '') {
                $len = mb_strlen($metaDesc);
                if ($len < 70) {
                    $metaDescTooShort[] = [
                        'id' => $product->id,
                        'name' => $product->product_name,
                        'code' => $product->product_code,
                        'length' => $len,
                        'value' => $metaDesc,
                    ];
                } elseif ($len > 160) {
                    $metaDescTooLong[] = [
                        'id' => $product->id,
                        'name' => $product->product_name,
                        'code' => $product->product_code,
                        'length' => $len,
                        'value' => $metaDesc,
                    ];
                } else {
                    $metaDescOptimal++;
                }
            }

            // Product Description: Target 80 - 1500 chars (plain text length)
            $rawDesc = trim((string) $product->product_description);
            $plainDesc = trim(strip_tags($rawDesc));
            if ($plainDesc !== '') {
                $len = mb_strlen($plainDesc);
                if ($len < 80) {
                    $descTooShort[] = [
                        'id' => $product->id,
                        'name' => $product->product_name,
                        'code' => $product->product_code,
                        'length' => $len,
                        'value' => mb_strimwidth($plainDesc, 0, 100, '...'),
                    ];
                } elseif ($len > 1500) {
                    $descTooLong[] = [
                        'id' => $product->id,
                        'name' => $product->product_name,
                        'code' => $product->product_code,
                        'length' => $len,
                        'value' => mb_strimwidth($plainDesc, 0, 100, '...'),
                    ];
                } else {
                    $descOptimal++;
                }
            }
        }

        return [
            'meta_title' => [
                'label' => 'Meta Title Length (Target: 30–60 chars)',
                'min' => 30,
                'max' => 60,
                'optimal_count' => $metaTitleOptimal,
                'too_short_count' => count($metaTitleTooShort),
                'too_short_samples' => array_slice($metaTitleTooShort, 0, 10),
                'too_long_count' => count($metaTitleTooLong),
                'too_long_samples' => array_slice($metaTitleTooLong, 0, 10),
                'severity' => (count($metaTitleTooShort) + count($metaTitleTooLong)) > 0 ? 'medium' : 'ok',
            ],
            'meta_description' => [
                'label' => 'Meta Description Length (Target: 70–160 chars)',
                'min' => 70,
                'max' => 160,
                'optimal_count' => $metaDescOptimal,
                'too_short_count' => count($metaDescTooShort),
                'too_short_samples' => array_slice($metaDescTooShort, 0, 10),
                'too_long_count' => count($metaDescTooLong),
                'too_long_samples' => array_slice($metaDescTooLong, 0, 10),
                'severity' => (count($metaDescTooShort) + count($metaDescTooLong)) > 0 ? 'medium' : 'ok',
            ],
            'product_description' => [
                'label' => 'Product Description Length (Target: 80–1,500 chars)',
                'min' => 80,
                'max' => 1500,
                'optimal_count' => $descOptimal,
                'too_short_count' => count($descTooShort),
                'too_short_samples' => array_slice($descTooShort, 0, 10),
                'too_long_count' => count($descTooLong),
                'too_long_samples' => array_slice($descTooLong, 0, 10),
                'severity' => (count($descTooShort) + count($descTooLong)) > 0 ? 'medium' : 'ok',
            ],
        ];
    }
}
