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

        return view($presented['view'], [
            ...$presented['data'],
            'rangeItems' => ProductCatalog::parentCategories(),
            'selectedCategory' => $selectedCategory,
            'showCatalog' => $selectedCategory !== '',
        ]);
    }
}
