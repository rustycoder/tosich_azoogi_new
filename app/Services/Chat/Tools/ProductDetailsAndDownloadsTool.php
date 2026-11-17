<?php

declare(strict_types=1);

namespace App\Services\Chat\Tools;

use App\Models\Product;
use App\Services\Chat\Contracts\IChatTool;

class ProductDetailsAndDownloadsTool implements IChatTool
{
    public function getName(): string
    {
        return 'get_product_details_and_downloads';
    }

    public function getDescription(): string
    {
        return 'Fetches full technical specifications, photometric files, installation guides, and datasheet downloads for a specific product by ID, code, or name.';
    }

    public function getParameters(): array
    {
        return [
            'type' => 'object',
            'required' => ['product_identifier'],
            'properties' => [
                'product_identifier' => [
                    'type' => 'string',
                    'description' => 'Product ID, product code (SKU), or product name.',
                ],
            ],
        ];
    }

    public function execute(array $arguments): array
    {
        $id = trim((string) ($arguments['product_identifier'] ?? ''));

        $baseQuery = Product::query()
            ->when(app()->isProduction(), function ($query) {
                $query->where(function ($q) {
                    $q->whereNull('status')
                        ->orWhere('status', '')
                        ->orWhere('status', 'publish')
                        ->orWhere('status', 'published')
                        ->orWhere('status', 'active');
                });
            });

        $idLower = mb_strtolower($id);
        $product = (clone $baseQuery)
            ->where(function ($q) use ($id, $idLower) {
                if (is_numeric($id)) {
                    $q->where('id', (int) $id);
                }
                $q->orWhereRaw('LOWER(product_code) = ?', [$idLower])
                    ->orWhereRaw('LOWER(slug) = ?', [$idLower])
                    ->orWhere('airtable_id', $id);
            })
            ->first();

        if (! $product) {
            $product = (clone $baseQuery)
                ->where(function ($q) use ($id) {
                    $q->where('sku_mappings', 'like', "%{$id}%")
                        ->orWhere('product_name', 'like', "%{$id}%");
                })
                ->first();
        }

        if (! $product) {
            return [
                'result' => ['error' => "Product not found matching: {$id}"],
            ];
        }

        $downloads = [];
        $datasheetUrl = $product->datasheetUrl();
        if (! empty($datasheetUrl)) {
            $downloads[] = [
                'type' => 'PDF Datasheet',
                'name' => "{$product->product_name} - Datasheet (PDF)",
                'url' => $datasheetUrl,
                'icon' => 'pdf',
            ];
        }

        $guideUrl = $product->guideUrl();
        if (! empty($guideUrl)) {
            $downloads[] = [
                'type' => 'Installation Guide',
                'name' => "{$product->product_name} - Installation Manual (PDF)",
                'url' => $guideUrl,
                'icon' => 'manual',
            ];
        }

        $iesUrl = $product->iesUrl();
        if (! empty($iesUrl)) {
            $downloads[] = [
                'type' => 'IES Photometric File',
                'name' => "{$product->product_name} - Photometric Data (IES)",
                'url' => $iesUrl,
                'icon' => 'ies',
            ];
        }

        $coverUrl = $product->storefrontImageUrl();
        if (empty($coverUrl) && ! empty($product->cover)) {
            $coverUrl = str_starts_with($product->cover, 'http') ? $product->cover : asset($product->cover);
        }

        $specs = [
            'id' => $product->id,
            'name' => $product->product_name,
            'code' => $product->product_code,
            'category' => $product->category,
            'description' => strip_tags((string) $product->product_description),
            'dimming' => $product->dimming_control ? 'Supported (Casambi / DALI / Phase)' : 'Standard Non-Dimming',
            'downloads' => $downloads,
            'url' => $product->publicPath() ?: route('products.show', $product->slug ?: $product->id),
            'image_url' => $coverUrl ?: asset('assets/bg_default.png'),
        ];

        $llmResult = [
            'name' => $product->product_name,
            'code' => $product->product_code,
            'category' => $product->category,
            'specs' => strip_tags((string) $product->product_description),
            'dimming' => $specs['dimming'],
            'datasheet_url' => $datasheetUrl,
            'manual_url' => $guideUrl,
            'ies_url' => $iesUrl,
            'url' => $specs['url'],
        ];

        return [
            'result' => $llmResult,
            'cards' => [
                'type' => 'product_detail_card',
                'data' => $specs,
            ],
        ];
    }
}
