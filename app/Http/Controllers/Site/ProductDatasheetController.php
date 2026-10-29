<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreProductDatasheetRequest;
use App\Models\DatasheetTemplate;
use App\Models\ProductDatasheetExport;
use App\Services\Contracts\IProductDatasheetService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\View\View;

class ProductDatasheetController extends Controller
{
    public function __construct(private IProductDatasheetService $datasheets) {}

    public function store(StoreProductDatasheetRequest $request): JsonResponse
    {
        $export = $this->datasheets->export($request->validated());

        return response()->json([
            'url' => route('products.datasheet.show', [
                'export' => $export,
                'print' => 1,
            ], false),
        ]);
    }

    public function show(ProductDatasheetExport $export): Response|View
    {
        $sheetData = $this->datasheets->sheet($export);
        $autoPrint = request()->boolean('print');

        $template = DatasheetTemplate::query()->where('is_active', true)->first();
        if ($template !== null) {
            $rendered = $template->render($sheetData, $autoPrint);

            return response($rendered, 200, ['Content-Type' => 'text/html; charset=UTF-8']);
        }

        return view('pages.product-datasheet', [
            'sheet' => $sheetData,
            'autoPrint' => $autoPrint,
        ]);
    }
}
