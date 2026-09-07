<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Services\Contracts\IProductDatasheetService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProductDatasheetExportController extends Controller
{
    public function __construct(private IProductDatasheetService $datasheets) {}

    public function index(Request $request): View
    {
        $search = dash_search_query($request->query('q'));

        return view('dashboard.datasheets.exports', [
            'exports' => $this->datasheets->dashboardList($search),
            'search' => $search,
        ]);
    }
}
