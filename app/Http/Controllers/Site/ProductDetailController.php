<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Services\Contracts\IPageVisitService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProductDetailController extends Controller
{
    public function __construct(private IPageVisitService $visits) {}

    public function __invoke(Request $request): View
    {
        $airtableId = $request->query('id');

        $this->visits->recordProduct(is_string($airtableId) ? $airtableId : null, $request);

        return view('pages.product-detail');
    }
}
