<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Services\Contracts\IPageService;
use App\Support\ProductCatalog;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProductController extends Controller
{
    public function __construct(
        private IPageService $pages,
    ) {}

    public function index(Request $request): View
    {
        $selectedCategory = trim((string) $request->query('category', ''));
        $presented = $this->pages->publicPage('products');
        $rangeItems = ProductCatalog::parentCategories();

        $selectedParentCategory = null;
        if ($selectedCategory !== '') {
            $parentCategoryMap = collect($rangeItems)->keyBy('title')->all();
            $rootParentName = ProductCatalog::findRootParentCategory($selectedCategory) ?? $selectedCategory;
            $selectedParentCategory = $parentCategoryMap[$rootParentName] ?? [
                'title' => $rootParentName,
                'body' => '',
            ];
        }

        return view($presented['view'], [
            ...$presented['data'],
            'rangeItems' => $rangeItems,
            'selectedCategory' => $selectedCategory,
            'selectedParentCategory' => $selectedParentCategory,
            'showCatalog' => $selectedCategory !== '',
        ]);
    }
}
