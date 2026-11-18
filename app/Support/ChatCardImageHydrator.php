<?php

declare(strict_types=1);

namespace App\Support;

use App\Models\ChatMessage;
use App\Models\Product;
use Illuminate\Support\Collection;

class ChatCardImageHydrator
{
    /**
     * Replace expired Airtable attachment URLs in stored chat card payloads with stable storefront image routes.
     *
     * @param  Collection<int, ChatMessage>  $messages
     */
    public static function hydrate(Collection $messages): void
    {
        $productIds = [];
        $hasCategories = false;

        foreach ($messages as $msg) {
            if (! is_array($msg->cards_payload)) {
                continue;
            }

            foreach ($msg->cards_payload as $card) {
                if (! is_array($card) || ! is_array($card['items'] ?? null)) {
                    continue;
                }

                if (($card['type'] ?? '') === 'products_carousel') {
                    foreach ($card['items'] as $item) {
                        if (! empty($item['id'])) {
                            $productIds[] = (int) $item['id'];
                        }
                    }
                } elseif (($card['type'] ?? '') === 'categories_carousel') {
                    $hasCategories = true;
                }
            }
        }

        $productsById = $productIds !== []
            ? Product::query()->whereIn('id', array_unique($productIds))->get()->keyBy('id')
            : collect();

        $categoriesByName = $hasCategories
            ? collect(ProductCatalog::parentCategories())->keyBy(
                fn (array $c): string => mb_strtolower((string) ($c['title'] ?? ''))
            )
            : collect();

        foreach ($messages as $msg) {
            if (! is_array($msg->cards_payload)) {
                continue;
            }

            $cards = $msg->cards_payload;
            $changed = false;

            foreach ($cards as $cIdx => $card) {
                if (! is_array($card) || ! is_array($card['items'] ?? null)) {
                    continue;
                }

                if (($card['type'] ?? '') === 'products_carousel') {
                    foreach ($card['items'] as $iIdx => $item) {
                        $pid = isset($item['id']) ? (int) $item['id'] : null;
                        $product = $pid ? $productsById->get($pid) : null;
                        $currentUrl = (string) ($item['image_url'] ?? '');

                        if ($product instanceof Product && self::isExpiringOrEmpty($currentUrl)) {
                            $stableUrl = $product->storefrontImageUrl();
                            if ($stableUrl !== '') {
                                $cards[$cIdx]['items'][$iIdx]['image_url'] = $stableUrl;
                                $changed = true;
                            }
                        }
                    }
                } elseif (($card['type'] ?? '') === 'categories_carousel') {
                    foreach ($card['items'] as $iIdx => $item) {
                        $currentUrl = (string) ($item['image_url'] ?? '');
                        $nameKey = mb_strtolower((string) ($item['name'] ?? ''));
                        $cat = $categoriesByName->get($nameKey);

                        if (self::isExpiringOrEmpty($currentUrl) && is_array($cat) && ! empty($cat['image']) && ! self::isExpiringOrEmpty((string) $cat['image'])) {
                            $cards[$cIdx]['items'][$iIdx]['image_url'] = (string) $cat['image'];
                            $changed = true;
                        }
                    }
                }
            }

            if ($changed) {
                $msg->setAttribute('cards_payload', $cards);
            }
        }
    }

    public static function isExpiringOrEmpty(?string $url): bool
    {
        if ($url === null || trim($url) === '') {
            return true;
        }

        $host = strtolower((string) parse_url($url, PHP_URL_HOST));

        return str_ends_with($host, 'airtableusercontent.com') || str_ends_with($host, 'airtable.com');
    }
}
