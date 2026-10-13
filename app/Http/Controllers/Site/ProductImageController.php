<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Services\Contracts\IProductImageService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

class ProductImageController extends Controller
{
    public function __construct(private IProductImageService $imageService) {}

    public function show(Request $request, string $identifier, string $format): Response
    {
        $normalizedFormat = strtolower($format);
        if ($normalizedFormat === 'jpeg') {
            $normalizedFormat = 'jpg';
        }

        if (! in_array($normalizedFormat, ['jpg', 'png', 'webp'], true)) {
            abort(404, 'Unsupported image format.');
        }

        $isDimension = false;
        $galleryIndex = null;
        $baseIdentifier = $identifier;

        if (preg_match('/^(.*?)-(?:dimension|dim)$/i', $identifier, $matches)) {
            $isDimension = true;
            $baseIdentifier = $matches[1];
        } else {
            $directProduct = $this->findProduct($identifier);
            if ($directProduct === null && preg_match('/^(.*?)-(\d+)$/', $identifier, $matches)) {
                $baseIdentifier = $matches[1];
                $galleryIndex = (int) $matches[2];
            }
        }

        $product = $this->findProduct($baseIdentifier);

        if ($product === null || ! $product->isVisibleOnStorefront()) {
            abort(404, 'Product not found.');
        }

        $sourceUrl = $isDimension
            ? $product->dimensionUrl()
            : $product->coverUrl($galleryIndex);

        if ($sourceUrl === null || trim($sourceUrl) === '') {
            abort(404, $isDimension ? 'Product dimension image not found.' : 'Product image not found.');
        }

        $image = $this->imageService->deliver($sourceUrl, $normalizedFormat);

        if ($image === null) {
            abort(404, 'Product image could not be loaded.');
        }

        $ifNoneMatch = $request->header('If-None-Match');
        if ($ifNoneMatch !== null && trim($ifNoneMatch, '"') === trim($image['etag'], '"')) {
            return response('', 304, [
                'ETag' => $image['etag'],
                'Cache-Control' => 'public, max-age=604800, stale-while-revalidate=86400',
            ]);
        }

        $isDownload = $request->hasAny(['download', 'dl']);
        if ($isDownload) {
            $baseName = $product->product_code ?: ($product->slug ?: 'product');
            $cleanName = Str::slug($baseName);
            $suffix = $isDimension
                ? '-dimension'
                : ($galleryIndex !== null ? '-'.$galleryIndex : '');
            $filename = $cleanName.$suffix.'.'.$normalizedFormat;
            $disposition = 'attachment; filename="'.$filename.'"';
        } else {
            $disposition = 'inline';
        }

        return response()->file($image['path'], [
            'Content-Type' => $image['mime'],
            'Content-Disposition' => $disposition,
            'Cache-Control' => 'public, max-age=604800, stale-while-revalidate=86400',
            'ETag' => $image['etag'],
            'Last-Modified' => gmdate('D, d M Y H:i:s', $image['last_modified']).' GMT',
        ]);
    }

    private function findProduct(string $identifier): ?Product
    {
        $trimmed = trim(preg_replace('/\.json$/i', '', trim($identifier)));
        if ($trimmed === '') {
            return null;
        }

        $normalizedWhitespace = preg_replace('/\s+/', ' ', $trimmed);
        $slugified = Str::slug($trimmed);

        return Product::query()->where('slug', $trimmed)->first()
            ?? Product::query()->whereRaw('LOWER(product_code) = ?', [strtolower($trimmed)])->first()
            ?? Product::query()->where('airtable_id', $trimmed)->first()
            ?? Product::query()->whereRaw('LOWER(product_name) = ?', [strtolower($trimmed)])->first()
            ?? ($slugified !== '' ? Product::query()->where('slug', $slugified)->first() : null)
            ?? ($normalizedWhitespace !== $trimmed ? Product::query()->whereRaw('LOWER(product_name) = ?', [strtolower($normalizedWhitespace)])->first() : null);
    }
}
