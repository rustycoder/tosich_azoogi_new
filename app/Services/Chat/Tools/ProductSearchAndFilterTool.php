<?php

declare(strict_types=1);

namespace App\Services\Chat\Tools;

use App\Models\Product;
use App\Services\Chat\Contracts\IChatTool;
use App\Support\ProductCatalog;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

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

        // 0. For vague / broad category queries, direct to category pages instead of a messy product list
        if ($this->isBroadCategoryQuery($keywords, $category, $ipRating, $dimension, $dimming)) {
            return $this->buildCategoryRedirectResponse($keywords, $category, $isOutdoor);
        }

        // 1. First priority: High precision search with scoring
        $scored = $this->searchAndScoreProducts($keywords, $category, $ipRating, $dimension, $dimming, $isOutdoor);

        // 2. If no products scored, perform broad fallback (respecting explicit/detected category first, never falling back to accessories/controllers)
        if ($scored->isEmpty()) {
            if (! empty($category)) {
                $fallbackCandidates = Product::query()
                    ->where(fn ($q) => $this->applyPublishedScope($q))
                    ->where(function ($q) use ($category) {
                        $q->where('category', 'like', "%{$category}%")
                            ->orWhere('category_path', 'like', "%{$category}%")
                            ->orWhere('product_name', 'like', "%{$category}%");
                    })
                    ->orderBy('sort_order', 'asc')
                    ->limit($limit * 4)
                    ->get();

                $scored = $fallbackCandidates
                    ->reject(fn (Product $p): bool => $this->shouldSuppressNonFixture($p, $keywords, $category))
                    ->take($limit)
                    ->values();

                if ($scored->isEmpty()) {
                    return $this->buildCategoryRedirectResponse($keywords, $category, $isOutdoor);
                }
            } elseif ($isOutdoor) {
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
            $coverUrl = $p->storefrontImageUrl();
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
                'slug' => $p->slug ?: (string) $p->id,
                'airtable_id' => $p->airtable_id,
                'name' => $p->product_name,
                'code' => $p->product_code,
                'category' => $p->category,
                'description' => $p->shortDescription() ?: mb_substr(strip_tags((string) $p->product_description), 0, 140).'...',
                'image_url' => $coverUrl ?: asset('assets/bg_default.png'),
                'url' => $p->publicPath() ?: route('products.show', $p->slug ?: $p->id),
                'datasheet_url' => $p->datasheetUrl(),
                'badges' => array_values(array_unique($badges)),
            ];
        })->values()->toArray();

        $compactProducts = array_map(function ($p) {
            return [
                'id' => $p['id'],
                'name' => $p['name'],
                'code' => $p['code'],
                'slug' => $p['slug'] ?? null,
                'category' => $p['category'],
                'specs' => $p['description'],
                'url' => $p['url'],
            ];
        }, $productCards);

        return [
            'result' => [
                'matched_count' => count($productCards),
                'detected_category' => $category,
                'detected_ip_rating' => $ipRating,
                'products' => $compactProducts,
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
                    ->orWhere('category_path', 'like', "%{$category}%")
                    ->orWhere('product_name', 'like', "%{$category}%");
                $hasCondition = true;
            }

            if (! empty($ipRating)) {
                $q->orWhere('product_description', 'like', "%{$ipRating}%")
                    ->orWhere('product_name', 'like', "%{$ipRating}%")
                    ->orWhere('product_features', 'like', "%{$ipRating}%");
                $hasCondition = true;
            }

            if ($isOutdoor && empty($category)) {
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
                        ->orWhere('sku_mappings', 'like', "%{$kw}%")
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
            $catPath = mb_strtolower(is_array($p->category_path) ? implode(' ', $p->category_path) : (string) $p->category_path);
            $desc = mb_strtolower((string) $p->product_description);
            $featuresText = mb_strtolower(is_array($p->product_features) ? json_encode($p->product_features) : (string) $p->product_features);
            $code = mb_strtolower(trim((string) $p->product_code));
            $skuMappingsText = mb_strtolower(is_array($p->sku_mappings) ? implode(' ', array_map(fn ($v) => is_scalar($v) ? (string) $v : json_encode($v), $p->sku_mappings)) : (string) $p->sku_mappings);

            // Identify non-fixture product types first so category expansion does not falsely boost them
            $wantsDriver = $this->wantsDriverOrControl($keywords);
            $wantsAccessory = $this->wantsAccessory($keywords);
            $wantsProfile = $this->wantsProfile($keywords, $category);

            $isDriverProduct = $this->isDriverOrControlProduct($name, $cat, $catPath);
            $isAccessoryProduct = $this->isAccessoryProduct($name, $cat, $catPath);
            $isProfileProduct = (str_contains($cat, 'profile') || str_contains($name, 'profile') || str_contains($cat, 'extrusion') || str_contains($name, 'extrusion'))
                && ! $isAccessoryProduct;

            // Category match bonus & Cross-category exclusion penalty
            if (! empty($category)) {
                $catLower = mb_strtolower($category);
                $isTargetProduct = str_contains($cat, $catLower) || str_contains($catPath, $catLower) || str_contains($name, $catLower);

                // Domain specific category expansions
                if (str_contains($catLower, 'downlight')) {
                    $isTargetProduct = str_contains($cat, 'downlight') || str_contains($name, 'downlight') || in_array($cat, ['surface mounted', 'pendant', 'recessed'], true);
                } elseif (str_contains($catLower, 'garden')) {
                    $isTargetProduct = str_contains($cat, 'garden') || str_contains($name, 'garden') || str_contains($cat, 'spike') || str_contains($name, 'spike');
                } elseif (str_contains($catLower, 'pool')) {
                    $isTargetProduct = str_contains($cat, 'pool') || str_contains($name, 'pool');
                } elseif (str_contains($catLower, 'neon')) {
                    $isTargetProduct = str_contains($cat, 'neon') || str_contains($name, 'neon') || str_contains($name, 'lumoflex') || str_contains($name, 'bend');
                } elseif (str_contains($catLower, 'strip') || str_contains($catLower, 'tape')) {
                    $isTargetProduct = (
                        str_contains($cat, 'strip')
                        || str_contains($catPath, 'strip')
                        || str_contains($name, 'strip')
                        || str_contains($cat, 'tape')
                        || str_contains($name, 'tape')
                        || (in_array('cob', $keywords, true) && str_contains($name, 'cob'))
                    ) && ! $isAccessoryProduct && ! $isProfileProduct && ! $isDriverProduct;
                } elseif (str_contains($catLower, 'handrail')) {
                    $isTargetProduct = str_contains($cat, 'handrail') || str_contains($name, 'handrail');
                } elseif (str_contains($catLower, 'profile') || str_contains($catLower, 'extrusion')) {
                    $isTargetProduct = $isProfileProduct;
                } elseif (str_contains($catLower, 'driver') || str_contains($catLower, 'power')) {
                    $isTargetProduct = $isDriverProduct;
                }

                if ($isTargetProduct) {
                    $score += 80;
                } else {
                    // Heavily penalize unrelated categories when user explicitly asked for a specific category
                    $score -= 120;
                }
            }

            if (! $wantsDriver && $isDriverProduct) {
                $score -= 300; // Suppress drivers when searching for lights/fixtures
            }

            if (! $wantsAccessory && $isAccessoryProduct) {
                $score -= 300; // Suppress connectors, mounting clips, and end caps unless requested
            }

            if (! $wantsProfile && $isProfileProduct) {
                $score -= 200; // Suppress profiles/extrusions unless explicitly requested
            }

            // IP Rating match bonus
            if (! empty($ipRating)) {
                $ipLower = mb_strtolower($ipRating);
                if (str_contains($desc, $ipLower) || str_contains($name, $ipLower) || str_contains($featuresText, $ipLower)) {
                    $score += 40;
                }
            }

            // Outdoor relevance
            if ($isOutdoor) {
                if (empty($category) && (str_contains($cat, 'garden') || str_contains($cat, 'pool') || str_contains($name, 'garden') || str_contains($name, 'pool'))) {
                    $score += 40;
                }
                if (empty($category) && (str_contains($cat, 'neon') || str_contains($name, 'bend'))) {
                    $score += 20;
                }
                if (str_contains($desc, 'ip68') || str_contains($desc, 'ip67') || str_contains($desc, 'ip66') || str_contains($desc, 'ip65')
                    || str_contains($featuresText, 'ip68') || str_contains($featuresText, 'ip67') || str_contains($featuresText, 'ip66') || str_contains($featuresText, 'ip65')) {
                    $score += 25;
                }
                if (str_contains($desc, 'outdoor') || str_contains($name, 'outdoor') || str_contains($desc, 'waterproof')
                    || str_contains($featuresText, 'outdoor') || str_contains($featuresText, 'waterproof')) {
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
                if ($code !== '' && $code === $kw) {
                    $score += 500;
                } elseif ($code !== '' && str_contains($code, $kw)) {
                    $score += 35;
                }
                if ($skuMappingsText !== '' && str_contains($skuMappingsText, $kw)) {
                    $score += 120;
                }
                if (str_contains($name, $kw)) {
                    $score += 20;
                }
                if (str_contains($cat, $kw)) {
                    $score += 15;
                }
                if (str_contains($desc, $kw)) {
                    $score += 5;
                }
            }

            if ($keywords !== []) {
                $nameAndCat = $name.' '.$cat;
                $allKeywordsMatched = true;
                foreach ($keywords as $kw) {
                    if (! str_contains($nameAndCat, $kw)) {
                        $allKeywordsMatched = false;
                        break;
                    }
                }
                if ($allKeywordsMatched) {
                    $score += 50;
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

    /**
     * Determine whether the query is too broad/vague to show specific products.
     *
     * @param  list<string>  $keywords
     */
    protected function isBroadCategoryQuery(
        array $keywords,
        string $category,
        string $ipRating,
        string $dimension,
        string $dimming
    ): bool {
        if ($ipRating !== '' || $dimension !== '' || $dimming !== '') {
            return false;
        }

        if ($keywords === []) {
            return true;
        }

        // Environment modifier + concrete product category (e.g. "outdoor strip", "outdoor downlight")
        // is a specific filtered search, not a generic category browse.
        $environmentModifiers = ['outdoor', 'exterior', 'indoor', 'interior'];
        if ($category !== '' && array_intersect($keywords, $environmentModifiers) !== []) {
            return false;
        }

        $genericBroadTokens = [
            'product', 'products', 'catalog', 'catalogue', 'range', 'ranges', 'category', 'categories',
            'light', 'lighting', 'led', 'fixture', 'architectural', 'commercial', 'indoor', 'interior',
            'outdoor', 'exterior', 'all', 'browse', 'view', 'see',
            'downlight', 'strip', 'tape', 'flex', 'neon', 'profile', 'extrusion', 'linear',
            'driver', 'power', 'supply', 'supplies', 'garden', 'pool', 'accessory',
        ];

        foreach ($keywords as $kw) {
            if (preg_match('/\d/', $kw) === 1) {
                return false;
            }

            if (! in_array($kw, $genericBroadTokens, true)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Build a category-level redirect payload with visual category cards.
     *
     * @param  list<string>  $keywords
     * @return array<string, mixed>
     */
    protected function buildCategoryRedirectResponse(array $keywords, string $category, bool $isOutdoor): array
    {
        $categoryCards = $this->resolveCategoryCards($keywords, $category, $isOutdoor);

        return [
            'result' => [
                'mode' => 'category_redirect',
                'matched_count' => 0,
                'detected_category' => $category,
                'catalog_url' => url('/products'),
                'categories' => array_map(fn (array $card): array => [
                    'name' => $card['name'],
                    'url' => $card['url'],
                    'product_count' => $card['product_count'],
                ], $categoryCards),
                'products' => [],
            ],
            'cards' => [
                'type' => 'categories_carousel',
                'items' => $categoryCards,
            ],
        ];
    }

    /**
     * Resolve matching visual category cards from the catalog's parent categories.
     *
     * @param  list<string>  $keywords
     * @return list<array<string, mixed>>
     */
    protected function resolveCategoryCards(array $keywords, string $category, bool $isOutdoor): array
    {
        $parentCategories = ProductCatalog::parentCategories();

        $keywordSynonyms = [
            'strip' => ['strip', 'strips and flex', 'flex', 'tape'],
            'tape' => ['strip', 'strips and flex', 'flex'],
            'flex' => ['strip', 'strips and flex', 'neon'],
            'neon' => ['neon'],
            'profile' => ['profile', 'profiles', 'linear'],
            'extrusion' => ['profile', 'profiles', 'linear'],
            'linear' => ['linear', 'linear lights', 'profile', 'profiles'],
            'driver' => ['driver', 'drivers'],
            'power' => ['driver', 'drivers'],
            'outdoor' => ['outdoor', 'outdoor & architectural', 'garden', 'pool'],
            'exterior' => ['outdoor', 'outdoor & architectural', 'garden', 'pool'],
            'garden' => ['garden', 'outdoor', 'outdoor & architectural'],
            'pool' => ['pool', 'outdoor', 'outdoor & architectural'],
            'downlight' => ['downlight', 'downlights'],
            'accessory' => ['accessory', 'accessories'],
        ];

        $searchTerms = [];
        if ($category !== '') {
            $searchTerms[] = mb_strtolower($category);
        } elseif ($isOutdoor) {
            $searchTerms[] = 'outdoor';
        }
        foreach ($keywords as $kw) {
            if ($category !== '' && in_array($kw, ['outdoor', 'exterior', 'indoor', 'interior'], true)) {
                continue;
            }
            $searchTerms[] = $kw;
            foreach ($keywordSynonyms[$kw] ?? [] as $syn) {
                $searchTerms[] = $syn;
            }
        }
        $searchTerms = array_values(array_unique(array_filter($searchTerms)));

        $formatCard = function (string $title, string $body, string $imageUrl, string $href, ?int $count): array {
            $cleanDesc = trim((string) preg_replace('/\s+/', ' ', strip_tags($body)));

            return [
                'type' => 'category_card',
                'name' => $title,
                'description' => $cleanDesc !== '' ? Str::limit($cleanDesc, 80) : "Browse {$title} range",
                'image_url' => $imageUrl !== '' ? $imageUrl : asset('assets/bg_default.png'),
                'url' => $href,
                'product_count' => $count,
            ];
        };

        if ($parentCategories !== []) {
            $titleMatched = [];
            $bodyMatched = [];

            foreach ($parentCategories as $item) {
                $titleLower = mb_strtolower((string) ($item['title'] ?? ''));
                $bodyLower = mb_strtolower((string) ($item['body'] ?? ''));
                $card = $formatCard(
                    (string) $item['title'],
                    (string) ($item['body'] ?? ''),
                    (string) ($item['image'] ?? ''),
                    (string) ($item['href'] ?? url('/products')),
                    isset($item['count']) ? (int) $item['count'] : null
                );

                $matchedByTitle = false;
                foreach ($searchTerms as $term) {
                    if ($term !== '' && str_contains($titleLower, $term)) {
                        $titleMatched[] = $card;
                        $matchedByTitle = true;
                        break;
                    }
                }

                if (! $matchedByTitle) {
                    foreach ($searchTerms as $term) {
                        if ($term !== '' && str_contains($bodyLower, $term)) {
                            $bodyMatched[] = $card;
                            break;
                        }
                    }
                }
            }

            if ($titleMatched !== []) {
                return array_slice($titleMatched, 0, 6);
            }

            if ($bodyMatched !== []) {
                return array_slice($bodyMatched, 0, 6);
            }

            // Catalog-wide / general query: return top parent categories
            return array_slice(array_map(
                fn (array $item): array => $formatCard(
                    (string) $item['title'],
                    (string) ($item['body'] ?? ''),
                    (string) ($item['image'] ?? ''),
                    (string) ($item['href'] ?? url('/products')),
                    isset($item['count']) ? (int) $item['count'] : null
                ),
                $parentCategories
            ), 0, 6);
        }

        // Fallback when compiled tree is empty (e.g. isolated test environments)
        $fallbackCategories = $category !== ''
            ? [$category]
            : ($isOutdoor ? ['Outdoor & Architectural', 'Garden Light', 'Pool Light'] : ['Profiles', 'Linear Lights', 'Strips and Flex', 'NEON', 'Outdoor & Architectural', 'Drivers']);

        return array_map(
            fn (string $name): array => $formatCard(
                $name,
                '',
                asset('assets/bg_default.png'),
                url('/products').'?category='.urlencode($name),
                null
            ),
            $fallbackCategories
        );
    }

    /**
     * @param  list<string>  $keywords
     */
    protected function shouldSuppressNonFixture(Product $p, array $keywords, string $category): bool
    {
        $name = mb_strtolower((string) $p->product_name);
        $cat = mb_strtolower((string) $p->category);
        $catPath = mb_strtolower(is_array($p->category_path) ? implode(' ', $p->category_path) : (string) $p->category_path);

        $isAccessory = $this->isAccessoryProduct($name, $cat, $catPath);
        $isDriverOrControl = $this->isDriverOrControlProduct($name, $cat, $catPath);
        $isProfile = (str_contains($cat, 'profile') || str_contains($name, 'profile') || str_contains($cat, 'extrusion') || str_contains($name, 'extrusion'))
            && ! $isAccessory;

        if (! $this->wantsAccessory($keywords) && $isAccessory) {
            return true;
        }
        if (! $this->wantsDriverOrControl($keywords) && $isDriverOrControl) {
            return true;
        }
        if (! $this->wantsProfile($keywords, $category) && $isProfile) {
            return true;
        }

        return false;
    }

    /**
     * @param  list<string>  $keywords
     */
    protected function wantsDriverOrControl(array $keywords): bool
    {
        return in_array('driver', $keywords, true)
            || in_array('power', $keywords, true)
            || in_array('psu', $keywords, true)
            || in_array('controller', $keywords, true)
            || in_array('dimmer', $keywords, true)
            || in_array('decoder', $keywords, true)
            || in_array('repeater', $keywords, true);
    }

    /**
     * @param  list<string>  $keywords
     */
    protected function wantsAccessory(array $keywords): bool
    {
        return in_array('accessory', $keywords, true)
            || in_array('accessories', $keywords, true)
            || in_array('connector', $keywords, true)
            || in_array('joiner', $keywords, true)
            || in_array('coupler', $keywords, true)
            || in_array('clip', $keywords, true)
            || in_array('cap', $keywords, true)
            || in_array('bracket', $keywords, true)
            || in_array('cable', $keywords, true)
            || in_array('lead', $keywords, true)
            || in_array('diffuser', $keywords, true)
            || in_array('sealant', $keywords, true)
            || in_array('glue', $keywords, true);
    }

    /**
     * @param  list<string>  $keywords
     */
    protected function wantsProfile(array $keywords, string $category): bool
    {
        return in_array('profile', $keywords, true)
            || in_array('extrusion', $keywords, true)
            || str_contains(mb_strtolower($category), 'profile')
            || str_contains(mb_strtolower($category), 'extrusion');
    }

    protected function isDriverOrControlProduct(string $name, string $cat, string $catPath): bool
    {
        return str_contains($cat, 'driver')
            || str_contains($catPath, 'driver')
            || str_contains($name, 'driver')
            || str_contains($cat, 'power supply')
            || str_contains($cat, 'controller')
            || str_contains($catPath, 'controller')
            || str_contains($name, 'controller')
            || str_contains($cat, 'dimmer')
            || str_contains($name, 'dimmer')
            || str_contains($name, 'decoder')
            || str_contains($name, 'repeater');
    }

    protected function isAccessoryProduct(string $name, string $cat, string $catPath): bool
    {
        return str_contains($cat, 'accessory')
            || str_contains($cat, 'accessories')
            || str_contains($catPath, 'accessory')
            || str_contains($catPath, 'accessories')
            || str_contains($name, 'end cap')
            || str_contains($name, 'clip')
            || str_contains($name, 'mounting set')
            || str_contains($name, 'splicing kit')
            || str_contains($name, 'connector')
            || str_contains($name, 'joiner')
            || str_contains($name, 'coupler')
            || str_contains($name, 'power feed')
            || str_contains($name, 'cable')
            || str_contains($name, 'lead')
            || str_contains($name, 'glue')
            || str_contains($name, 'sealant')
            || str_contains($name, 'diffuser')
            || str_contains($name, 'bracket');
    }
}
