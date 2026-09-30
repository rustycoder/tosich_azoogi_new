<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\ProductCategory;
use App\Repositories\Contracts\IProductRepository;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CategoryController extends Controller
{
    public function __construct(private IProductRepository $products) {}

    public function index(Request $request): View
    {
        $search = dash_search_query($request->query('q'));
        $parent = $request->query('parent');
        $activeParent = filled($parent) && $parent !== 'all' ? (string) $parent : null;

        $rawPerPage = (int) $request->query('per_page', 50);
        $perPage = in_array($rawPerPage, [50, 100, 150, 200], true) ? $rawPerPage : 50;

        $categories = $this->products->categoryDashboardList($search, $activeParent, $perPage);

        $parentCategories = ProductCategory::query()
            ->where(function ($q): void {
                $q->whereNull('parent_airtable_id')
                    ->orWhere('parent_airtable_id', '');
            })
            ->orderByRaw('sort_order is null')
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        return view('dashboard.categories.index', [
            'categories' => $categories,
            'search' => $search,
            'parentCategories' => $parentCategories,
            'activeParent' => $activeParent,
            'perPage' => $perPage,
            'perPageOptions' => [50, 100, 150, 200],
        ]);
    }
}
