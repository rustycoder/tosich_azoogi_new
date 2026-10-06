<?php

declare(strict_types=1);

namespace App\Services\Chat\Tools;

use App\Models\Product;
use App\Services\Chat\Contracts\IChatTool;
use Illuminate\Database\Eloquent\Builder;

class ProductSearchAndFilterTool implements IChatTool
{
    public function getName(): string
    {
        return 'public_search_and_filter_products';
    }

    public function getDescription(): string
    {
        return 'Searches and filters active lighting products by keyword, category, application (e.g. outdoor, strip, downlight, highbay, profile), IP rating, dimension, or dimming protocol.';
    }

    public function getParameters(): array
    {
        return [
            'type' => 'object',
            'properties' => [
                'query' => [
                    'type' => 'string',
                    'description' => 'Search term or keyword (e.g., "downlight", "outdoor lighting", "linear extrusion", "garden spike", "neon flex").',
                ],
                'category' => [
                    'type' => 'string',
                    'description' => 'Optional category filter (e.g., "Downlights", "LED Strips", "Garden Light", "Pool Light").',
                ],
                'ip_rating' => [
                    'type' => 'string',
                    'description' => 'IP rating filter (e.g. "IP65", "IP66", "IP67", "IP68").',
                ],
                'dimension' => [
                    'type' => 'string',
                    'description' => 'Dimension requirement (e.g. "82mm", "80mm", "Ø82mm").',
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
        $rawQuery = trim((string) ($arguments['query'] ?? ''));
        $category = trim((string) ($arguments['category'] ?? ''));
        $ipRating = trim((string) ($arguments['ip_rating'] ?? ''));
        $dimension = trim((string) ($arguments['dimension'] ?? ''));
        $dimming = trim((string) ($arguments['dimming'] ?? ''));
        $limit = min(8, max(1, (int) ($arguments['limit'] ?? 4)));

        // Extract IP rating from query if not explicitly passed
        if (empty($ipRating) && preg_match('/\b(IP[2456][0-8])\b/i', $rawQuery, $ipMatch)) {
            $ipRating = strtoupper($ipMatch[1]);
        }

        // Extract dimming protocol from query if not explicitly passed
        if (empty($dimming) && preg_match('/\b(DALI|Casambi|Triac|0-10V|MADRIX|Silvair|Phase)\b/i', $rawQuery, $dimMatch)) {
            $dimming = $dimMatch[1];
        }

        // Extract dimension from query if not explicitly passed
        if (empty($dimension) && preg_match('/\b(?:Ø|\b)?\d+(?:\.\d+)?\s*(?:mm|m|cm)?\b/i', $rawQuery, $dimenMatch)) {
            $dimension = trim($dimenMatch[0]);
        }

        // Tokenize and clean query
        $keywords = $this->extractKeywords($rawQuery);

        // 1. Try structured search with high precision
        $products = $this->queryProducts($keywords, $category, $ipRating, $dimension, $dimming, $limit, 'and');

        // 2. Fallback: Relaxed OR search if no products found
        if ($products->isEmpty() && (! empty($keywords) || ! empty($category) || ! empty($ipRating) || ! empty($dimming))) {
            $products = $this->queryProducts($keywords, $category, $ipRating, $dimension, $dimming, $limit, 'or');
        }

        // 3. Fallback: Default to top published products if still empty
        if ($products->isEmpty()) {
            $products = $this->basePublishedQuery()
                ->orderBy('sort_order', 'asc')
                ->limit($limit)
                ->get();
        }

        $productCards = $products->map(function (Product $p) {
            $coverUrl = $p->coverUrl();
            if (empty($coverUrl) && ! empty($p->cover)) {
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
            if (! empty($p->stocked_item) && strtolower((string) $p->stocked_item) !== 'no') {
                $badges[] = 'In Stock';
            }

            return [
                'type' => 'product_card',
                'id' => $p->id,
                'airtable_id' => $p->airtable_id,
                'name' => $p->product_name,
                'code' => $p->product_code,
                'category' => $p->category,
                'description' => $p->shortDescription() ?: mb_substr(strip_tags((string) $p->product_description), 0, 140).'...',
                'image_url' => $coverUrl ?: asset('assets/quote.webp'),
                'url' => $p->publicPath() ?: route('products.show', $p->slug ?: $p->id),
                'datasheet_url' => $p->datasheetUrl(),
                'badges' => $badges,
            ];
        })->values()->toArray();

        return [
            'result' => [
                'matched_count' => count($productCards),
                'query_used' => $rawQuery,
                'products' => $productCards,
            ],
            'cards' => [
                'type' => 'products_carousel',
                'items' => $productCards,
            ],
        ];
    }

    /**
     * @param  list<string>  $keywords
     */
    protected function queryProducts(
        array $keywords,
        string $category,
        string $ipRating,
        string $dimension,
        string $dimming,
        int $limit,
        string $mode = 'and'
    ) {
        $builder = $this->basePublishedQuery();

        if (! empty($category)) {
            $builder->where(function ($q) use ($category) {
                $q->where('category', 'like', "%{$category}%")
                    ->orWhere('category_path', 'like', "%{$category}%")
                    ->orWhere('categories', 'like', "%{$category}%");
            });
        }

        if (! empty($ipRating)) {
            $builder->where(function ($q) use ($ipRating) {
                $q->where('product_description', 'like', "%{$ipRating}%")
                    ->orWhere('product_name', 'like', "%{$ipRating}%")
                    ->orWhere('product_features', 'like', "%{$ipRating}%")
                    ->orWhere('options', 'like', "%{$ipRating}%");
            });
        }

        if (! empty($dimming)) {
            $builder->where(function ($q) use ($dimming) {
                $q->where('product_description', 'like', "%{$dimming}%")
                    ->orWhere('product_name', 'like', "%{$dimming}%")
                    ->orWhere('product_features', 'like', "%{$dimming}%")
                    ->orWhere('dimming_control', true);
            });
        }

        if (! empty($dimension)) {
            $cleanDim = preg_replace('/[^\d.]/', '', $dimension);
            if (! empty($cleanDim)) {
                $builder->where(function ($q) use ($cleanDim) {
                    $q->where('product_dimension', 'like', "%{$cleanDim}%")
                        ->orWhere('product_description', 'like', "%{$cleanDim}%")
                        ->orWhere('product_name', 'like', "%{$cleanDim}%");
                });
            }
        }

        if (! empty($keywords)) {
            $builder->where(function ($q) use ($keywords, $mode) {
                foreach ($keywords as $index => $kw) {
                    $method = ($mode === 'and' && $index > 0) ? 'where' : 'orWhere';
                    $q->{$method}(function ($sub) use ($kw) {
                        $sub->where('product_name', 'like', "%{$kw}%")
                            ->orWhere('category', 'like', "%{$kw}%")
                            ->orWhere('product_code', 'like', "%{$kw}%")
                            ->orWhere('product_description', 'like', "%{$kw}%")
                            ->orWhere('product_features', 'like', "%{$kw}%");
                    });
                }
            });
        }

        return $builder->orderBy('sort_order', 'asc')->limit($limit)->get();
    }

    protected function basePublishedQuery(): Builder
    {
        return Product::query()->where(function ($q) {
            $q->whereNull('status')
                ->orWhere('status', '')
                ->orWhere('status', 'publish')
                ->orWhere('status', 'published')
                ->orWhere('status', 'active');
        });
    }

    /**
     * @return list<string>
     */
    protected function extractKeywords(string $text): array
    {
        $filler = [
            'i', 'want', 'to', 'explore', 'show', 'me', 'find', 'search', 'with', 'dimension',
            'fixtures', 'fixtures', 'lights', 'lighting', 'options', 'give', 'please', 'some',
            'a', 'an', 'the', 'for', 'in', 'of', 'and', 'or', 'at', 'on', 'by', 'is', 'are',
            'tell', 'about', 'what', 'which', 'do', 'you', 'have', 'can', 'get', 'need', 'looking',
        ];

        $cleaned = preg_replace('/[^\p{L}\p{N}\s-]/u', ' ', mb_strtolower($text));
        $words = preg_split('/\s+/', (string) $cleaned) ?: [];

        $keywords = [];
        foreach ($words as $w) {
            $w = trim($w);
            if (mb_strlen($w) >= 2 && ! in_array($w, $filler, true)) {
                $keywords[] = $w;
            }
        }

        return array_values(array_unique($keywords));
    }
}
