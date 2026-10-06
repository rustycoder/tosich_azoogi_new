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

        $product = Product::query()
            ->where('status', 'active')
            ->where(function ($q) use ($id) {
                if (is_numeric($id)) {
                    $q->where('id', (int) $id);
                }
                $q->orWhere('product_code', $id)
                    ->orWhere('slug', $id)
                    ->orWhere('airtable_id', $id)
                    ->orWhere('product_name', 'like', "%{$id}%");
            })
            ->first();

        if (! $product) {
            return [
                'result' => ['error' => "Product not found matching: {$id}"],
            ];
        }

        $downloads = [];
        if (! empty($product->datasheet_file)) {
            $downloads[] = [
                'type' => 'PDF Datasheet',
                'name' => "{$product->product_name} - Datasheet (PDF)",
                'url' => asset($product->datasheet_file),
                'icon' => 'pdf',
            ];
        }
        if (! empty($product->installation_guide_file)) {
            $downloads[] = [
                'type' => 'Installation Guide',
                'name' => "{$product->product_name} - Installation Manual (PDF)",
                'url' => asset($product->installation_guide_file),
                'icon' => 'manual',
            ];
        }
        if (! empty($product->ies_file)) {
            $downloads[] = [
                'type' => 'IES Photometric File',
                'name' => "{$product->product_name} - Photometric Data (IES)",
                'url' => asset($product->ies_file),
                'icon' => 'ies',
            ];
        }

        $coverUrl = null;
        if (! empty($product->cover)) {
            $coverUrl = str_starts_with($product->cover, 'http') ? $product->cover : asset($product->cover);
        }

        $specs = [
            'id' => $product->id,
            'name' => $product->product_name,
            'code' => $product->product_code,
            'category' => $product->category,
            'type' => $product->product_type,
            'description' => strip_tags((string) $product->product_description),
            'dimming' => $product->dimming_control ? 'Supported (Casambi / DALI / Phase)' : 'Standard Non-Dimming',
            'downloads' => $downloads,
            'url' => route('products.show', $product->slug ?: $product->id),
            'image_url' => $coverUrl,
        ];

        return [
            'result' => $specs,
            'cards' => [
                'type' => 'product_detail_card',
                'data' => $specs,
            ],
        ];
    }
}
