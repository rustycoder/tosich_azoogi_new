<?php

namespace App\Services\CatalogAuditService;

namespace App\Services;

use App\Models\Product;
use App\Models\ProductAttribute;
use App\Models\ProductCategory;
use App\Models\ProductSync;
use App\Services\Contracts\ICatalogAuditService;
use Illuminate\Support\Facades\DB;

class CatalogAuditService implements ICatalogAuditService
{
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

        // Health Score Calculation (0-100%)
        $healthScore = 100;
        if ($totalProducts > 0) {
            $coverPenalty = ($missingCoverCount / $totalProducts) * 20;
            $skuPenalty = ($missingSkuCount / $totalProducts) * 20;
            $slugPenalty = ($missingSlugCount / $totalProducts) * 15;
            $categoryPenalty = ($missingCategoryCount / $totalProducts) * 15;
            $descPenalty = ($missingDescriptionCount / $totalProducts) * 10;
            $metaPenalty = ($missingMetaDescriptionCount / $totalProducts) * 10;
            $datasheetPenalty = ($missingDatasheetFileCount / max(1, $totalProducts)) * 10;

            $totalPenalty = $coverPenalty + $skuPenalty + $slugPenalty + $categoryPenalty + $descPenalty + $metaPenalty + $datasheetPenalty;
            $healthScore = max(0, min(100, (int) round(100 - $totalPenalty)));
        }

        $latestSync = ProductSync::query()->latest('id')->first();

        return [
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
        ];
    }
}
