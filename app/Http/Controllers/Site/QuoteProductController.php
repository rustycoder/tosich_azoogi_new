<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Http\Requests\QuoteProductsRequest;
use App\Models\Product;
use App\Repositories\Contracts\IProductRepository;
use Illuminate\Http\JsonResponse;

class QuoteProductController extends Controller
{
    public function __construct(private IProductRepository $products) {}

    public function __invoke(QuoteProductsRequest $request): JsonResponse
    {
        $products = $this->products->publishedForQuoteIds($request->ids());

        return response()->json([
            'products' => $products
                ->map(fn (Product $product): array => $product->quoteSummary())
                ->values()
                ->all(),
        ]);
    }
}
