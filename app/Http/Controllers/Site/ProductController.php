<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Support\ProductCatalog;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProductController extends Controller
{
    public function index(Request $request): View
    {
        $selectedCategory = trim((string) $request->query('category', ''));

        return view('pages.products', [
            'rangeItems' => ProductCatalog::parentCategories(),
            'selectedCategory' => $selectedCategory,
            'showCatalog' => $selectedCategory !== '',
        ]);
    }
}
