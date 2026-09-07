<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreProductDatasheetRequest;
use App\Models\ProductDatasheetExport;
use App\Services\Contracts\IProductDatasheetService;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;

class ProductDatasheetController extends Controller
{
    public function __construct(private IProductDatasheetService $datasheets) {}

    public function store(StoreProductDatasheetRequest $request): JsonResponse
    {
        $export = $this->datasheets->export(
            $request->validated(),
            $request->ip(),
            $request->userAgent(),
        );

        return response()->json([
            'url' => route('products.datasheet.show', [
                'export' => $export,
                'print' => 1,
            ]),
        ]);
    }

    public function show(ProductDatasheetExport $export): View
    {
        return view('pages.product-datasheet', [
            'sheet' => $this->datasheets->sheet($export),
            'autoPrint' => request()->boolean('print'),
        ]);
    }
}
