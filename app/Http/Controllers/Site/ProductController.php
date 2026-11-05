<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Models\ProductCategory;
use App\Repositories\Contracts\IProductRepository;
use App\Services\Contracts\IPageService;
use App\Support\ProductCatalog;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class ProductController extends Controller
{
    public function __construct(
        private IPageService $pages,
        private IProductRepository $productRepo,
    ) {}

    public function index(Request $request): View
    {
        $selectedCategory = trim((string) $request->query('category', ''));
        $presented = $this->pages->publicPage('products');
        $rangeItems = ProductCatalog::parentCategories();
        $productCatalog = $this->productRepo->compiled();

        $selectedParentCategory = null;
        $categoryProducts = collect();
        $categoryMetaDescription = null;
        if ($selectedCategory !== '') {
            $categoryProducts = $this->productRepo->storefrontProductsInCategory($selectedCategory);
            $categoryDescription = ProductCategory::query()
                ->whereRaw('LOWER(name) = ?', [mb_strtolower($selectedCategory)])
                ->value('description');
            $categoryMetaDescription = filled($categoryDescription)
                ? Str::limit(trim((string) preg_replace('/\s+/', ' ', strip_tags((string) $categoryDescription))), 160)
                : 'Browse Azoogi '.$selectedCategory.' — architectural and commercial LED lighting with specs, datasheets and trade quotes across Australia.';

            $parentCategoryMap = collect($rangeItems)->keyBy('title')->all();
            $rootParentName = ProductCatalog::findRootParentCategory($selectedCategory) ?? $selectedCategory;
            $selectedParentCategory = $parentCategoryMap[$rootParentName] ?? [
                'title' => $rootParentName,
                'body' => '',
            ];
        }

        return view($presented['view'], [
            ...$presented['data'],
            'productCatalog' => $productCatalog,
            'rangeItems' => $rangeItems,
            'selectedCategory' => $selectedCategory,
            'selectedParentCategory' => $selectedParentCategory,
            'showCatalog' => $selectedCategory !== '',
            'categoryProducts' => $categoryProducts,
            'categoryMetaDescription' => $categoryMetaDescription,
        ]);
    }
}
