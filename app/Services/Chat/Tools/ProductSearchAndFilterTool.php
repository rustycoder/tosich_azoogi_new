<?php

declare(strict_types=1);

namespace App\Services\Chat\Tools;

use App\Models\Product;
use App\Services\Chat\Contracts\IChatTool;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

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
                    'description' => 'Optional category filter (e.g., "Downlight", "LED Strip", "Garden Light", "Pool Light", "Neon", "Driver").',
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

        // Extract IP rating from query if not provided
        if (empty($ipRating) && preg_match('/\b(IP[2456][0-9])\b/i', $rawQuery, $ipMatch)) {
            $ipRating = strtoupper($ipMatch[1]);
        }

        // Extract dimming protocol from query
        if (empty($dimming) && preg_match('/\b(DALI(?:-2)?|Casambi|Triac|0-10V|1-10V|MADRIX|Silvair|Phase)\b/i', $rawQuery, $dimMatch)) {
            $dimming = $dimMatch[1];
        }

        // Extract dimension keywords from query
        if (empty($dimension) && preg_match_all('/\b(?:Ø)?\d+(?:\.\d+)?\s*(?:mm|m|cm)?\b/i', $rawQuery, $dimenMatches)) {
            $dimension = implode(' ', $dimenMatches[0]);
        }

        // Check if query implies outdoor
        $isOutdoor = (bool) (preg_match('/\b(outdoor|exterior|garden|pool|waterproof|weatherproof|submersible|inground)\b/i', $rawQuery) || in_array($ipRating, ['IP65', 'IP66', 'IP67', 'IP68', 'IP69'], true));

        // Tokenize and stem query keywords
        $keywords = $this->extractAndStemKeywords($rawQuery);

        // Auto-detect category from keywords
        if (empty($category)) {
            if (in_array('downlight', $keywords, true)) {
                $category = 'Downlight';
            } elseif (in_array('strip', $keywords, true) || in_array('tape', $keywords, true)) {
                $category = 'Strip';
            } elseif (in_array('neon', $keywords, true)) {
                $category = 'Neon';
            } elseif (in_array('garden', $keywords, true) || in_array('spike', $keywords, true)) {
                $category = 'Garden Light';
            } elseif (in_array('pool', $keywords, true)) {
                $category = 'Pool Light';
            } elseif (in_array('driver', $keywords, true) || in_array('power', $keywords, true)) {
                $category = 'Driver';
            } elseif (in_array('profile', $keywords, true) || in_array('extrusion', $keywords, true)) {
                $category = 'Profile';
            }
        }

        // 1. First priority: High precision search with scoring
        $scored = $this->searchAndScoreProducts($keywords, $category, $ipRating, $dimension, $dimming, $isOutdoor);

        // 2. If no products scored, perform broad fallback
        if ($scored->isEmpty()) {
            if ($isOutdoor) {
                $scored = Product::query()
                    ->where(fn ($q) => $this->applyPublishedScope($q))
                    ->where(function ($q) {
                        $q->where('product_name', 'like', '%Garden%')
                            ->orWhere('product_name', 'like', '%Pool%')
                            ->orWhere('category', 'like', '%Garden%')
                            ->orWhere('category', 'like', '%Pool%')
                            ->orWhere('product_description', 'like', '%IP6%')
                            ->orWhere('product_description', 'like', '%outdoor%');
                    })
                    ->orderBy('sort_order', 'asc')
                    ->limit($limit)
                    ->get();
            } elseif (! empty($category)) {
                $scored = Product::query()
                    ->where(fn ($q) => $this->applyPublishedScope($q))
                    ->where(function ($q) use ($category) {
                        $q->where('category', 'like', "%{$category}%")
                            ->orWhere('product_name', 'like', "%{$category}%");
                    })
                    ->orderBy('sort_order', 'asc')
                    ->limit($limit)
                    ->get();
            } else {
                $scored = Product::query()
                    ->where(fn ($q) => $this->applyPublishedScope($q))
                    ->orderBy('sort_order', 'asc')
                    ->limit($limit)
                    ->get();
            }
        }

        $products = $scored->take($limit);

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

            // Check if IP rating found in description or name
            if (preg_match('/(IP6[5-9]|IP54|IP68)/i', $p->product_name.' '.$p->product_description, $ipB)) {
                $badges[] = strtoupper($ipB[1]);
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
                'badges' => array_values(array_unique($badges)),
            ];
        })->values()->toArray();

        return [
            'result' => [
                'matched_count' => count($productCards),
                'query_used' => $rawQuery,
                'detected_category' => $category,
                'detected_ip_rating' => $ipRating,
                'is_outdoor' => $isOutdoor,
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
     * @return Collection<int, Product>
     */
    protected function searchAndScoreProducts(
        array $keywords,
        string $category,
        string $ipRating,
        string $dimension,
        string $dimming,
        bool $isOutdoor
    ): Collection {
        $builder = Product::query()->where(fn ($q) => $this->applyPublishedScope($q));

        // Get candidate products that match at least one attribute
        $builder->where(function ($q) use ($keywords, $category, $ipRating, $dimming, $isOutdoor) {
            $hasCondition = false;

            if (! empty($category)) {
                $q->where('category', 'like', "%{$category}%")
                    ->orWhere('product_name', 'like', "%{$category}%");
                $hasCondition = true;
            }

            if (! empty($ipRating)) {
                $q->orWhere('product_description', 'like', "%{$ipRating}%")
                    ->orWhere('product_name', 'like', "%{$ipRating}%")
                    ->orWhere('product_features', 'like', "%{$ipRating}%");
                $hasCondition = true;
            }

            if ($isOutdoor) {
                $q->orWhere('category', 'like', '%Garden%')
                    ->orWhere('category', 'like', '%Pool%')
                    ->orWhere('category', 'like', '%Neon%')
                    ->orWhere('product_name', 'like', '%Garden%')
                    ->orWhere('product_name', 'like', '%Pool%')
                    ->orWhere('product_name', 'like', '%Outdoor%')
                    ->orWhere('product_description', 'like', '%IP6%')
                    ->orWhere('product_description', 'like', '%IP67%')
                    ->orWhere('product_description', 'like', '%IP68%')
                    ->orWhere('product_description', 'like', '%outdoor%');
                $hasCondition = true;
            }

            if (! empty($dimming)) {
                $q->orWhere('product_description', 'like', "%{$dimming}%")
                    ->orWhere('product_name', 'like', "%{$dimming}%")
                    ->orWhere('dimming_control', true);
                $hasCondition = true;
            }

            if (! empty($keywords)) {
                foreach ($keywords as $kw) {
                    $q->orWhere('product_name', 'like', "%{$kw}%")
                        ->orWhere('category', 'like', "%{$kw}%")
                        ->orWhere('product_code', 'like', "%{$kw}%")
                        ->orWhere('product_description', 'like', "%{$kw}%");
                }
                $hasCondition = true;
            }

            if (! $hasCondition) {
                $q->whereRaw('1 = 1');
            }
        });

        $candidates = $builder->get();

        // Score candidates based on relevance
        $dimNumbers = [];
        if (! empty($dimension)) {
            preg_match_all('/\d+/', $dimension, $matches);
            $dimNumbers = $matches[0] ?? [];
        }

        $scored = $candidates->map(function (Product $p) use ($keywords, $category, $ipRating, $dimNumbers, $dimming, $isOutdoor) {
            $score = 0;
            $name = mb_strtolower((string) $p->product_name);
            $cat = mb_strtolower((string) $p->category);
            $desc = mb_strtolower((string) $p->product_description);
            $code = mb_strtolower((string) $p->product_code);

            // Category match bonus & Cross-category exclusion penalty
            if (! empty($category)) {
                $catLower = mb_strtolower($category);
                $isTargetProduct = str_contains($cat, $catLower) || str_contains($name, $catLower);

                // Downlight specific subcategories (Surface Mounted, Pendant, Recessed)
                if ($catLower === 'downlight') {
                    if (str_contains($cat, 'downlight') || str_contains($name, 'downlight') || in_array($cat, ['surface mounted', 'pendant', 'recessed'], true)) {
                        $isTargetProduct = true;
                        $score += 60;
                    }
                }

                if ($isTargetProduct) {
                    $score += 40;
                } else {
                    // Penalize irrelevant categories when user is looking for a distinct type
                    if (str_contains($cat, 'profile') || str_contains($name, 'profile') || str_contains($cat, 'accessory') || str_contains($cat, 'accessories')) {
                        $score -= 80;
                    }
                }
            }

            // IP Rating match bonus
            if (! empty($ipRating)) {
                $ipLower = mb_strtolower($ipRating);
                if (str_contains($desc, $ipLower) || str_contains($name, $ipLower)) {
                    $score += 40;
                }
            }

            // Outdoor relevance
            if ($isOutdoor) {
                if (str_contains($cat, 'garden') || str_contains($cat, 'pool')) {
                    $score += 35;
                }
                if (str_contains($desc, 'ip68') || str_contains($desc, 'ip67') || str_contains($desc, 'ip66') || str_contains($desc, 'ip65')) {
                    $score += 25;
                }
                if (str_contains($desc, 'outdoor') || str_contains($name, 'outdoor')) {
                    $score += 20;
                }
            }

            // Dimension number match bonus (e.g. 80mm, 82mm, 55mm)
            foreach ($dimNumbers as $num) {
                if (str_contains($name, $num.'mm') || str_contains($name, $num.' mm') || str_contains($name, 'ø'.$num)) {
                    $score += 35;
                } elseif (str_contains($desc, $num.'mm') || str_contains($desc, $num)) {
                    $score += 15;
                }
            }

            // Keyword matches
            foreach ($keywords as $kw) {
                if (str_contains($name, $kw)) {
                    $score += 20;
                }
                if (str_contains($cat, $kw)) {
                    $score += 15;
                }
                if (str_contains($code, $kw)) {
                    $score += 15;
                }
                if (str_contains($desc, $kw)) {
                    $score += 5;
                }
            }

            // Dimming match
            if (! empty($dimming)) {
                $dimLower = mb_strtolower($dimming);
                if (str_contains($name, $dimLower) || str_contains($desc, $dimLower)) {
                    $score += 20;
                }
            }

            return ['product' => $p, 'score' => $score];
        });

        return $scored->filter(fn ($item) => $item['score'] > 0)
            ->sortByDesc('score')
            ->map(fn ($item) => $item['product'])
            ->values();
    }

    protected function applyPublishedScope(Builder $query): void
    {
        if (app()->isProduction()) {
            $query->where(function ($q) {
                $q->whereNull('status')
                    ->orWhere('status', '')
                    ->orWhere('status', 'publish')
                    ->orWhere('status', 'published')
                    ->orWhere('status', 'active');
            });
        } else {
            // In local/testing/dev environments, allow draft/pending catalog items so development items can be explored
            $query->where(function ($q) {
                $q->whereNull('status')
                    ->orWhere('status', '')
                    ->orWhere('status', 'publish')
                    ->orWhere('status', 'published')
                    ->orWhere('status', 'pending')
                    ->orWhere('status', 'draft')
                    ->orWhere('status', 'active');
            });
        }
    }

    /**
     * @return list<string>
     */
    protected function extractAndStemKeywords(string $text): array
    {
        $filler = [
            'i', 'want', 'to', 'explore', 'show', 'me', 'find', 'search', 'with', 'dimension',
            'fixtures', 'fixtures', 'lights', 'lighting', 'options', 'give', 'please', 'some',
            'a', 'an', 'the', 'for', 'in', 'of', 'and', 'or', 'at', 'on', 'by', 'is', 'are',
            'tell', 'about', 'what', 'which', 'do', 'you', 'have', 'can', 'get', 'need', 'looking',
        ];

        $cleaned = preg_replace('/[^\p{L}\p{N}\s-]/u', ' ', mb_strtolower($text));
        $words = preg_split('/\s+/', (string) $cleaned) ?: [];

        $stemMap = [
            'downlights' => 'downlight',
            'strips' => 'strip',
            'profiles' => 'profile',
            'extrusions' => 'extrusion',
            'drivers' => 'driver',
            'fixtures' => 'fixture',
            'accessories' => 'accessory',
            'spikes' => 'spike',
            'bulbs' => 'bulb',
            'controllers' => 'controller',
            'sensors' => 'sensor',
        ];

        $keywords = [];
        foreach ($words as $w) {
            $w = trim($w);
            if (isset($stemMap[$w])) {
                $w = $stemMap[$w];
            }
            if (mb_strlen($w) >= 2 && ! in_array($w, $filler, true)) {
                $keywords[] = $w;
            }
        }

        return array_values(array_unique($keywords));
    }
}
