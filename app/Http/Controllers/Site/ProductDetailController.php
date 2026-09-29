<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Services\Contracts\IPageVisitService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProductDetailController extends Controller
{
    public function __construct(private IPageVisitService $visits) {}

    public function __invoke(Request $request, ?string $slug = null): View|RedirectResponse
    {
        $identifier = $slug
            ?? $request->query('id')
            ?? $request->query('product')
            ?? $request->query('name')
            ?? $request->query('code')
            ?? $request->query('sku')
            ?? $request->query('file')
            ?? $request->query('variant');

        $product = null;
        if (is_string($identifier) && trim($identifier) !== '') {
            $trimmed = trim(preg_replace('/\.json$/i', '', trim($identifier)));
            $product = Product::query()->where('slug', $trimmed)->first()
                ?? Product::query()->whereRaw('LOWER(product_code) = ?', [strtolower($trimmed)])->first()
                ?? Product::query()->where('airtable_id', $trimmed)->first()
                ?? Product::query()->whereRaw('LOWER(product_name) = ?', [strtolower($trimmed)])->first();
        } else {
            $product = Product::query()->where('status', 'Published')->first()
                ?? Product::query()->first();
        }

        if (is_string($identifier) && trim($identifier) !== '' && $product === null) {
            abort(404, 'Product not found.');
        }

        if ($product !== null && ! $product->isVisibleOnStorefront()) {
            abort(404, 'Product not found.');
        }

        if ($product !== null) {
            $assetRedirect = $this->handleAssetRedirect($request, $product);
            if ($assetRedirect !== null) {
                return $assetRedirect;
            }
        }

        $recordedId = $product?->airtable_id ?? (is_string($identifier) ? $identifier : null);
        $this->visits->recordProduct($recordedId, $request);

        $recommendedProducts = [];
        if ($product !== null) {
            $category = $product->category;
            $recommendedProducts = Product::query()
                ->where('status', 'Published')
                ->where('id', '!=', $product->id)
                ->when($category, fn ($q) => $q->where('category', $category))
                ->limit(4)
                ->get()
                ->map(fn (Product $p) => [
                    'id' => $p->airtable_id ?? (string) $p->id,
                    'name' => $p->product_name,
                    'slug' => $p->slug,
                    'sku' => $p->product_code,
                    'category' => $p->category,
                    'sub' => $p->category,
                    'img' => $p->coverUrl(),
                    'url' => $p->slug ? route('products.show', $p->slug) : url('/product-detail?id='.$p->airtable_id),
                ])
                ->all();
        }

        return view('pages.product-detail', [
            'product' => $product,
            'slug' => $product?->slug ?? $slug,
            'recommendedProducts' => $recommendedProducts,
        ]);
    }

    private function handleAssetRedirect(Request $request, Product $product): ?RedirectResponse
    {
        if ($request->hasAny(['image', 'img', 'photo'])) {
            $rawIndex = $request->query('image') ?? $request->query('img') ?? $request->query('photo');
            $index = is_numeric($rawIndex) ? (int) $rawIndex : null;
            $url = $product->coverUrl($index);

            if ($url !== '') {
                return redirect()->away(str_starts_with($url, 'http') ? $url : url($url), 302);
            }

            abort(404, 'Product image not found.');
        }

        if ($request->hasAny(['dimension', 'dim', 'dimension_image', 'dimensions'])) {
            $url = $product->dimensionUrl();
            if ($url !== null && $url !== '') {
                return redirect()->away(str_starts_with($url, 'http') ? $url : url($url), 302);
            }

            abort(404, 'Product dimension image not found.');
        }

        if ($request->hasAny(['datasheet', 'datasheet_file', 'spec', 'pdf'])) {
            $url = $product->datasheetUrl();
            if ($url !== null && $url !== '') {
                return redirect()->away(str_starts_with($url, 'http') ? $url : url($url), 302);
            }

            abort(404, 'Product datasheet not found.');
        }

        if ($request->hasAny(['manual', 'user_manual'])) {
            $url = $product->manualUrl();
            if ($url !== null && $url !== '') {
                return redirect()->away(str_starts_with($url, 'http') ? $url : url($url), 302);
            }

            abort(404, 'Product manual not found.');
        }

        if ($request->hasAny(['guide', 'installation', 'install', 'installation_guide', 'installation_guide_file'])) {
            $url = $product->guideUrl();
            if ($url !== null && $url !== '') {
                return redirect()->away(str_starts_with($url, 'http') ? $url : url($url), 302);
            }

            abort(404, 'Product installation guide not found.');
        }

        if ($request->hasAny(['ies', 'ies_file', 'photometric'])) {
            $url = $product->iesUrl();
            if ($url !== null && $url !== '') {
                return redirect()->away(str_starts_with($url, 'http') ? $url : url($url), 302);
            }

            abort(404, 'Product IES file not found.');
        }

        return null;
    }
}
