<?php

declare(strict_types=1);

namespace App\Mcp\Tools\PublicChat;

use App\Mcp\Contracts\McpToolInterface;
use App\Models\Product;

class SearchProductsTool implements McpToolInterface
{
    public function getName(): string
    {
        return 'public_search_products';
    }

    public function getDescription(): string
    {
        return 'Searches the product catalog by keyword, application (e.g. outdoor, garden, linear, floodlight, track, strip), or category, returning product details, specifications, and links.';
    }

    /**
     * @return array<string, mixed>
     */
    public function getInputSchema(): array
    {
        return [
            'type' => 'object',
            'properties' => [
                'query' => [
                    'type' => 'string',
                    'description' => 'Search term (e.g., "outdoor lighting", "garden spike", "linear highbay", "neon flex").',
                ],
                'category' => [
                    'type' => 'string',
                    'description' => 'Optional category name filter.',
                ],
                'limit' => [
                    'type' => 'integer',
                    'description' => 'Number of products to return (default 5, max 10).',
                ],
            ],
        ];
    }

    public function execute(array $arguments): array
    {
        $query = trim((string) ($arguments['query'] ?? ''));
        $category = trim((string) ($arguments['category'] ?? ''));
        $limit = min(10, max(1, (int) ($arguments['limit'] ?? 5)));

        $builder = Product::query()->where('status', 'active');

        if (! empty($query)) {
            $builder->where(function ($q) use ($query) {
                $q->where('product_name', 'like', "%{$query}%")
                    ->orWhere('product_description', 'like', "%{$query}%")
                    ->orWhere('product_code', 'like', "%{$query}%")
                    ->orWhere('category', 'like', "%{$query}%")
                    ->orWhere('product_type', 'like', "%{$query}%");
            });
        }

        if (! empty($category)) {
            $builder->where(function ($q) use ($category) {
                $q->where('category', 'like', "%{$category}%")
                    ->orWhere('category_path', 'like', "%{$category}%");
            });
        }

        $products = $builder->limit($limit)->get()->map(function (Product $p) {
            $coverUrl = null;
            if (! empty($p->cover)) {
                $coverUrl = str_starts_with($p->cover, 'http') ? $p->cover : asset($p->cover);
            }

            return [
                'id' => $p->id,
                'name' => $p->product_name,
                'code' => $p->product_code,
                'category' => $p->category,
                'type' => $p->product_type,
                'description' => $p->product_description,
                'image_url' => $coverUrl,
                'url' => route('products.show', $p->slug ?: $p->id),
            ];
        });

        return [
            'isError' => false,
            'content' => json_encode([
                'count' => $products->count(),
                'products' => $products,
            ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES),
        ];
    }
}
