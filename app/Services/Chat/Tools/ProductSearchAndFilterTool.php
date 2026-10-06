<?php

declare(strict_types=1);

namespace App\Services\Chat\Tools;

use App\Models\Product;
use App\Services\Chat\Contracts\IChatTool;

class ProductSearchAndFilterTool implements IChatTool
{
    public function getName(): string
    {
        return 'public_search_and_filter_products';
    }

    public function getDescription(): string
    {
        return 'Searches and filters active lighting products by keyword, category, application (e.g. outdoor, strip, downlight, highbay, profile), IP rating, or dimming protocol.';
    }

    public function getParameters(): array
    {
        return [
            'type' => 'object',
            'properties' => [
                'query' => [
                    'type' => 'string',
                    'description' => 'Search term or keyword (e.g., "outdoor lighting", "linear extrusion", "garden spike", "neon flex").',
                ],
                'category' => [
                    'type' => 'string',
                    'description' => 'Optional category filter.',
                ],
                'ip_rating' => [
                    'type' => 'string',
                    'description' => 'IP rating filter (e.g. "IP65", "IP66", "IP67", "IP68").',
                ],
                'dimming' => [
                    'type' => 'string',
                    'description' => 'Dimming / control protocol (e.g. "DALI", "Casambi", "Triac", "0-10V").',
                ],
                'limit' => [
                    'type' => 'integer',
                    'description' => 'Max products to return (default 4, max 8).',
                ],
            ],
        ];
    }

    public function execute(array $arguments): array
    {
        $query = trim((string) ($arguments['query'] ?? ''));
        $category = trim((string) ($arguments['category'] ?? ''));
        $ipRating = trim((string) ($arguments['ip_rating'] ?? ''));
        $dimming = trim((string) ($arguments['dimming'] ?? ''));
        $limit = min(8, max(1, (int) ($arguments['limit'] ?? 4)));

        $builder = Product::query()->where('status', 'active');

        if (! empty($query)) {
            $builder->where(function ($q) use ($query) {
                $q->where('product_name', 'like', "%{$query}%")
                    ->orWhere('product_description', 'like', "%{$query}%")
                    ->orWhere('product_code', 'like', "%{$query}%")
                    ->orWhere('category', 'like', "%{$query}%");
            });
        }

        if (! empty($category)) {
            $builder->where(function ($q) use ($category) {
                $q->where('category', 'like', "%{$category}%")
                    ->orWhere('category_path', 'like', "%{$category}%");
            });
        }

        if (! empty($ipRating)) {
            $builder->where(function ($q) use ($ipRating) {
                $q->where('product_description', 'like', "%{$ipRating}%")
                    ->orWhere('product_name', 'like', "%{$ipRating}%");
            });
        }

        if (! empty($dimming)) {
            $builder->where(function ($q) use ($dimming) {
                $q->where('product_description', 'like', "%{$dimming}%")
                    ->orWhere('product_name', 'like', "%{$dimming}%");
            });
        }

        $products = $builder->limit($limit)->get();

        $productCards = $products->map(function (Product $p) {
            $coverUrl = null;
            if (! empty($p->cover)) {
                $coverUrl = str_starts_with($p->cover, 'http') ? $p->cover : asset($p->cover);
            }

            // Specs badges
            $badges = [];
            if (! empty($p->category)) {
                $badges[] = $p->category;
            }
            if ($p->dimming_control) {
                $badges[] = 'Smart Dimming';
            }

            return [
                'type' => 'product_card',
                'id' => $p->id,
                'airtable_id' => $p->airtable_id,
                'name' => $p->product_name,
                'code' => $p->product_code,
                'category' => $p->category,
                'description' => mb_substr(strip_tags((string) $p->product_description), 0, 140).'...',
                'image_url' => $coverUrl,
                'url' => route('products.show', $p->slug ?: $p->id),
                'datasheet_url' => $p->datasheet_file ? asset($p->datasheet_file) : null,
                'badges' => $badges,
            ];
        })->toArray();

        return [
            'result' => [
                'matched_count' => count($productCards),
                'products' => $productCards,
            ],
            'cards' => [
                'type' => 'products_carousel',
                'items' => $productCards,
            ],
        ];
    }
}
