<?php

declare(strict_types=1);

namespace App\Mcp\Tools\PublicChat;

use App\Mcp\Contracts\McpToolInterface;
use App\Models\Product;

class ManageQuoteListTool implements McpToolInterface
{
    public function getName(): string
    {
        return 'public_manage_quote_list';
    }

    public function getDescription(): string
    {
        return 'Adds, removes, or views items in the visitor’s active quote list during chat.';
    }

    /**
     * @return array<string, mixed>
     */
    public function getInputSchema(): array
    {
        return [
            'type' => 'object',
            'required' => ['action'],
            'properties' => [
                'action' => [
                    'type' => 'string',
                    'enum' => ['add', 'remove', 'view', 'clear'],
                    'description' => 'Action to perform on the quote list.',
                ],
                'product_id' => [
                    'type' => 'integer',
                    'description' => 'Product ID (required for add and remove).',
                ],
                'quantity' => [
                    'type' => 'integer',
                    'description' => 'Quantity to add (default: 1).',
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
                return ['isError' => true, 'content' => "Product #{$productId} not found."];
            }

            $currentQty = $quoteList[$productId]['quantity'] ?? 0;
            $quoteList[$productId] = [
                'id' => $product->id,
                'name' => $product->product_name,
                'code' => $product->product_code,
                'quantity' => $currentQty + $qty,
            ];

            session()->put('visitor_quote_list', $quoteList);

            return [
                'isError' => false,
                'content' => json_encode([
                    'message' => "Added {$qty}x '{$product->product_name}' to your quote list.",
                    'current_quote_list' => array_values($quoteList),
                ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES),
            ];
        }

        if ($action === 'remove' && $productId) {
            unset($quoteList[$productId]);
            session()->put('visitor_quote_list', $quoteList);

            return [
                'isError' => false,
                'content' => json_encode([
                    'message' => 'Product removed from quote list.',
                    'current_quote_list' => array_values($quoteList),
                ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES),
            ];
        }

        if ($action === 'clear') {
            session()->forget('visitor_quote_list');

            return [
                'isError' => false,
                'content' => json_encode([
                    'message' => 'Quote list cleared.',
                    'current_quote_list' => [],
                ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES),
            ];
        }

        return [
            'isError' => false,
            'content' => json_encode([
                'current_quote_list' => array_values($quoteList),
            ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES),
        ];
    }
}
