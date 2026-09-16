<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Services\Contracts\IPageVisitService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProductDetailController extends Controller
{
    public function __construct(private IPageVisitService $visits) {}

    public function __invoke(Request $request, ?string $slug = null): View
    {
        $airtableId = $request->query('id') ?? $request->query('product');

        $product = null;
        if (is_string($slug) && $slug !== '') {
            $product = Product::query()->where('slug', $slug)->first()
                ?? Product::query()->where('airtable_id', $slug)->first();
        }

        if (! $product && is_string($airtableId) && $airtableId !== '') {
            $product = Product::query()->where('airtable_id', $airtableId)->first()
                ?? Product::query()->where('slug', $airtableId)->first();
        }

        $recordedId = $product?->airtable_id ?? (is_string($airtableId) ? $airtableId : (is_string($slug) ? $slug : null));
        $this->visits->recordProduct($recordedId, $request);

        return view('pages.product-detail', [
            'product' => $product,
            'slug' => $product?->slug ?? $slug,
        ]);
    }
}
