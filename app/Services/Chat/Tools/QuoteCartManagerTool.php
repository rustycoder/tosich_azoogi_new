<?php

declare(strict_types=1);

namespace App\Services\Chat\Tools;

use App\Models\Product;
use App\Services\Chat\Contracts\IChatTool;

class QuoteCartManagerTool implements IChatTool
{
    public function getName(): string
    {
        return 'public_manage_quote_list';
    }

    public function getDescription(): string
    {
        return 'Manages the visitor’s active quote list during chat (adding products, updating/setting item quantities, removing items, viewing cart, or clearing cart), synchronizing with the website Quote Request cart.';
    }

    public function getParameters(): array
    {
        return [
            'type' => 'object',
            'required' => ['action'],
            'properties' => [
                'action' => [
                    'type' => 'string',
                    'enum' => ['add', 'update', 'remove', 'view', 'clear'],
                    'description' => 'Action to perform: "update" (use when user wants to change, set, or modify the quantity of an existing item, e.g. from 8 to 16 sets exact total quantity = 16), "add" (use when adding a new item or adding additional units to existing), "remove" (deletes item), "view" (shows current list), or "clear" (empties list).',
                ],
                'product_identifier' => [
                    'type' => 'string',
                    'description' => 'The product ID, product code / SKU (e.g. "GL003"), product slug, or product name to add, update, or remove.',
                ],
                'product_id' => [
                    'type' => 'string',
                    'description' => 'Alias for product_identifier. Can be numeric ID (15), product code ("GL003"), or slug.',
                ],
                'quantity' => [
                    'type' => 'integer',
                    'description' => 'The quantity number to add or the new total quantity to set when updating (default: 1).',
                    'default' => 1,
                ],
            ],
        ];
    }

    public function execute(array $arguments): array
    {
        $action = strtolower((string) ($arguments['action'] ?? 'view'));
        $rawId = $arguments['product_identifier'] ?? $arguments['product_id'] ?? $arguments['product_code'] ?? $arguments['sku'] ?? $arguments['slug'] ?? null;
        $productId = is_string($rawId) ? trim($rawId) : $rawId;
        $qty = max(1, (int) ($arguments['quantity'] ?? 1));

        $quoteList = session()->get('visitor_quote_list', []);

        if (in_array($action, ['add', 'update', 'set', 'update_quantity', 'set_quantity'], true) && ! empty($productId)) {
            $product = Product::query()
                ->where(function ($q) use ($productId) {
                    if (is_numeric($productId)) {
                        $q->where('id', (int) $productId);
                    }
                    $q->orWhere('airtable_id', (string) $productId)
                        ->orWhere('product_code', (string) $productId)
                        ->orWhere('slug', (string) $productId)
                        ->orWhere('product_name', 'like', "%{$productId}%");
                })
                ->first();

            if (! $product) {
                return [
                    'result' => ['error' => "Product '{$productId}' not found in active catalog."],
                ];
            }

            $prodKey = (string) $product->id;
            $currentQty = $quoteList[$prodKey]['quantity'] ?? 0;
            $coverUrl = $product->coverUrl();
            if (empty($coverUrl) && ! empty($product->cover)) {
                $coverUrl = str_starts_with($product->cover, 'http') ? $product->cover : asset($product->cover);
            }

            $newQty = in_array($action, ['update', 'set', 'update_quantity', 'set_quantity'], true)
                ? $qty
                : ($currentQty + $qty);

            $quoteList[$prodKey] = [
                'id' => (string) $product->id,
                'db_id' => (string) $product->id,
                'airtable_id' => (string) $product->airtable_id,
                'name' => $product->product_name,
                'code' => $product->product_code,
                'sku' => $product->product_code,
                'quantity' => $newQty,
                'image_url' => $coverUrl ?: asset('assets/quote.webp'),
                'image' => $coverUrl ?: asset('assets/quote.webp'),
                'url' => $product->publicPath() ?: route('products.show', $product->slug ?: $product->id),
            ];

            session()->put('visitor_quote_list', $quoteList);

            $actionWord = in_array($action, ['update', 'set', 'update_quantity', 'set_quantity'], true) ? 'Updated' : 'Added';

            return [
                'result' => [
                    'status' => 'success',
                    'message' => "{$actionWord} '{$product->product_name}' (Qty: {$newQty}) in your quote list.",
                    'total_items' => count($quoteList),
                    'quote_items' => array_values($quoteList),
                ],
                'cards' => [
                    'type' => 'quote_cart_card',
                    'items' => array_values($quoteList),
                    'quote_url' => route('request-a-quote'),
                ],
            ];
        }

        if ($action === 'remove' && ! empty($productId)) {
            $product = Product::query()
                ->where(function ($q) use ($productId) {
                    if (is_numeric($productId)) {
                        $q->where('id', (int) $productId);
                    }
                    $q->orWhere('airtable_id', (string) $productId)
                        ->orWhere('product_code', (string) $productId)
                        ->orWhere('slug', (string) $productId)
                        ->orWhere('product_name', 'like', "%{$productId}%");
                })
                ->first();

            $keyToRemove = $product ? (string) $product->id : (string) $productId;
            unset($quoteList[$keyToRemove]);
            session()->put('visitor_quote_list', $quoteList);

            return [
                'result' => [
                    'status' => 'success',
                    'message' => $product ? "Removed '{$product->product_name}' from quote list." : 'Product removed from quote list.',
                    'total_items' => count($quoteList),
                    'quote_items' => array_values($quoteList),
                ],
                'cards' => [
                    'type' => 'quote_cart_card',
                    'items' => array_values($quoteList),
                    'quote_url' => route('request-a-quote'),
                ],
            ];
        }

        if ($action === 'clear') {
            session()->forget('visitor_quote_list');

            return [
                'result' => [
                    'status' => 'success',
                    'message' => 'Quote list cleared.',
                    'total_items' => 0,
                    'quote_items' => [],
                ],
                'cards' => [
                    'type' => 'quote_cart_card',
                    'items' => [],
                    'quote_url' => route('request-a-quote'),
                ],
            ];
        }

        // Action: view
        return [
            'result' => [
                'status' => 'success',
                'total_items' => count($quoteList),
                'quote_items' => array_values($quoteList),
            ],
            'cards' => [
                'type' => 'quote_cart_card',
                'items' => array_values($quoteList),
                'quote_url' => route('request-a-quote'),
            ],
        ];
    }
}
