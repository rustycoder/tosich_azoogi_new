<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Services\Contracts\IProductSyncService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProductController extends Controller
{
    public function __construct(private IProductSyncService $products) {}

    public function index(Request $request): View
    {
        $search = dash_search_query($request->query('q'));

        return view('dashboard.products.index', [
            'products' => $this->products->dashboardList($search),
            'search' => $search,
            'latestSync' => $this->products->latestSync(),
        ]);
    }

    public function sync(): RedirectResponse
    {
        $this->products->dispatch((string) auth()->id());

        return redirect()
            ->route('dashboard.products.index')
            ->with('status', 'Product sync started. Refresh in a moment to see updates.');
    }
}
