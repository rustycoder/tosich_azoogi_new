<?php

declare(strict_types=1);

namespace App\Services\Chat\Tools;

use App\Models\Product;
use App\Services\Chat\Contracts\IChatTool;
use App\Services\Contracts\IProductDatasheetService;
use Throwable;

class CustomDatasheetGeneratorTool implements IChatTool
{
    public function __construct(
        protected IProductDatasheetService $datasheetService
    ) {}

    public function getName(): string
    {
        return 'generate_custom_datasheet';
    }

    public function getDescription(): string
    {
        return 'Generates a custom downloadable PDF datasheet for a product with configured technical options (e.g. CCT, beam angle, length, finish).';
    }

    public function getParameters(): array
    {
        return [
            'type' => 'object',
            'required' => ['product_id'],
            'properties' => [
                'product_id' => [
                    'type' => 'string',
                    'description' => 'Product ID or Airtable ID.',
                ],
                'product_code' => [
                    'type' => 'string',
                    'description' => 'Configured product code SKU.',
                ],
                'length' => [
                    'type' => 'number',
                    'description' => 'Custom length in meters (for linear profiles / LED strips).',
                ],
                'selected_options' => [
                    'type' => 'object',
                    'description' => 'Key-value map of selected technical options (e.g., {"CCT": "3000K", "Finish": "Black", "Beam": "60°"}).',
                ],
            ],
        ];
    }

    public function execute(array $arguments): array
    {
        $id = (string) ($arguments['product_id'] ?? '');
        $product = Product::query()
            ->where(function ($q) use ($id) {
                if (is_numeric($id)) {
                    $q->where('id', (int) $id);
                }
                $q->orWhere('airtable_id', $id)
                    ->orWhere('slug', $id)
                    ->orWhere('product_code', $id);
            })
            ->first();

        if (! $product) {
            return [
                'result' => ['error' => "Product not found: {$id}"],
            ];
        }

        try {
            $export = $this->datasheetService->export([
                'product_id' => $product->airtable_id ?: (string) $product->id,
                'product_code' => $arguments['product_code'] ?? $product->product_code,
                'length' => $arguments['length'] ?? null,
                'selected_options' => (array) ($arguments['selected_options'] ?? []),
            ]);

            $downloadUrl = route('datasheet.download', $export->uuid);

            return [
                'result' => [
                    'status' => 'success',
                    'product_name' => $product->product_name,
                    'export_uuid' => $export->uuid,
                    'download_url' => $downloadUrl,
                    'message' => "Custom PDF datasheet generated successfully for {$product->product_name}.",
                ],
                'cards' => [
                    'type' => 'datasheet_download_card',
                    'data' => [
                        'product_name' => $product->product_name,
                        'product_code' => $product->product_code,
                        'download_url' => $downloadUrl,
                        'uuid' => $export->uuid,
                    ],
                ],
            ];
        } catch (Throwable $e) {
            return [
                'result' => [
                    'status' => 'error',
                    'message' => 'Unable to generate datasheet: '.$e->getMessage(),
                ],
            ];
        }
    }
}
