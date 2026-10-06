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
        return 'Adds, removes, or views items in the visitor’s active quote list during chat, synchronizing with the website Quote Request cart.';
    }

    public function getParameters(): array
    {
        return [
            'type' => 'object',
            'required' => ['action'],
            'properties' => [
                'action' => [
                    'type' => 'string',
                    'enum' => ['add', 'remove', 'view', 'clear'],
                    'description' => 'Action to perform: add, remove, view, or clear.',
                ],
                'product_id' => [
                    'type' => 'integer',
                    'description' => 'Product ID (required for add and remove).',
                ],
                'quantity' => [
                    'type' => 'integer',
                    'description' => 'Quantity to add or set (default: 1).',
                    'default' => 1,
                ],
            ],
        ];
    }

    public function execute(array $arguments): array
    {
        $action = (string) ($arguments['action'] ?? 'view');
        $productId = isset($arguments['product_id']) ? (int) $arguments['product_id'] : null;
        $qty = max(1, (int) ($arguments['quantity'] ?? 1));

        $quoteList = session()->get('visitor_quote_list', []);

        if ($action === 'add' && $productId) {
            $product = Product::find($productId);
            if (! $product) {
                return [
                    'result' => ['error' => "Product #{$productId} not found."],
                ];
            }

            $currentQty = $quoteList[$productId]['quantity'] ?? 0;
            $coverUrl = null;
            if (! empty($product->cover)) {
                $coverUrl = str_starts_with($product->cover, 'http') ? $product->cover : asset($product->cover);
            }

            $quoteList[$productId] = [
                'id' => $product->id,
                'name' => $product->product_name,
                'code' => $product->product_code,
                'quantity' => $currentQty + $qty,
                'image_url' => $coverUrl,
                'url' => route('products.show', $product->slug ?: $product->id),
            ];

            session()->put('visitor_quote_list', $quoteList);

            return [
                'result' => [
                    'message' => "Added {$qty}x '{$product->product_name}' to your quote list.",
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

        if ($action === 'remove' && $productId) {
            unset($quoteList[$productId]);
            session()->put('visitor_quote_list', $quoteList);

            return [
                'result' => [
                    'message' => 'Product removed from quote list.',
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
