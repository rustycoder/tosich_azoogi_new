<?php

namespace App\Support;

use App\Models\ProductCategory;
use App\Repositories\Contracts\IProductRepository;

class ProductCatalog
{
    /**
     * Get parent categories from products and product categories stored in MySQL.
     *
     * @return array<int, array{title: string, body: string, image: string, href: string, count: int}>
     */
    public static function parentCategories(): array
    {
        $data = app(IProductRepository::class)->compiled();

        if ($data['tree'] === []) {
            return [];
        }

        $tree = $data['tree'];
        $products = $data['products'];
        $storedByName = ProductCategory::query()
            ->get(['name', 'description', 'featured_image'])
            ->keyBy('name');

        // Preferred order for core lighting categories
        $priorityOrder = [
            'NEON' => 1,
            'Profiles' => 2,
            'Linear Lights' => 3,
            'Strips and Flex' => 4,
            'Outdoor & Architectural' => 5,
            'Drivers' => 6,
            'Accessories' => 7,
        ];

        $categories = [];

        foreach ($tree as $node) {
            if (! is_array($node) || ($node['type'] ?? '') !== 'category') {
                continue;
            }

            $name = (string) ($node['name'] ?? '');
            if ($name === '') {
                continue;
            }

            $count = 0;
            $stored = $storedByName->get($name);
            $storedBody = trim((string) ($stored?->description ?? ''));
            $storedImage = trim((string) ($stored?->featured_image ?? ''));
            $image = $storedImage !== '' ? $storedImage : null;

            foreach ($products as $product) {
                if (! is_array($product)) {
                    continue;
                }

                if (! empty($product['status']) && strtolower(trim((string) $product['status'])) !== 'publish') {
                    continue;
                }

                $pathCategory = $product['category_path'][0] ?? null;
                $directCategory = $product['category'] ?? null;

                if ($pathCategory === $name || $directCategory === $name) {
                    $count++;

                    if ($image === null && ! empty($product['product_images'][0])) {
                        $image = (string) $product['product_images'][0];
                    }
                }
            }

            $categories[] = [
                'title' => $name,
                'body' => $storedBody,
                'image' => self::rangeImage($image, $name),
                'href' => url('/products').'?category='.urlencode($name),
                'count' => $count,
                '_priority' => $priorityOrder[$name] ?? 99,
            ];
        }

        usort($categories, fn (array $a, array $b): int => ($a['_priority'] ?? 99) <=> ($b['_priority'] ?? 99));

        return array_map(function (array $item): array {
            unset($item['_priority']);

            return $item;
        }, $categories);
    }

    public static function fallbackImage(string $name = ''): string
    {
        $categoryFallbackImages = [
            'NEON' => '/assets/img/neon.webp',
            'Profiles' => '/assets/img/prod-1.jpg',
            'Linear Lights' => '/assets/img/prod-3.jpg',
            'Strips and Flex' => '/assets/img/leds.webp',
            'Outdoor & Architectural' => '/assets/img/GL001.webp',
            'Drivers' => '/assets/img/drivers.webp',
            'Accessories' => '/assets/img/prod-4.jpg',
            'LED Lights' => '/assets/img/prod-5.jpg',
        ];

        return $categoryFallbackImages[$name] ?? '/assets/img/neon.webp';
    }

    /**
     * Find the root parent category name for a given category name from compiled tree or database.
     */
    public static function findRootParentCategory(string $categoryName): ?string
    {
        $categoryName = trim($categoryName);
        if ($categoryName === '') {
            return null;
        }

        $data = app(IProductRepository::class)->compiled();
        $tree = $data['tree'] ?? [];

        foreach ($tree as $node) {
            if (! is_array($node) || ($node['type'] ?? '') !== 'category') {
                continue;
            }

            $rootName = (string) ($node['name'] ?? '');
            if (strcasecmp($rootName, $categoryName) === 0) {
                return $rootName;
            }

            if (self::categoryTreeContains($node['children'] ?? [], $categoryName)) {
                return $rootName;
            }
        }

        // Fallback: check ProductCategory database hierarchy
        $category = ProductCategory::query()->where('name', $categoryName)->first();
        if ($category) {
            $current = $category;
            while ($current->parent_airtable_id) {
                $parent = ProductCategory::query()->where('airtable_id', $current->parent_airtable_id)->first();
                if (! $parent) {
                    break;
                }
                $current = $parent;
            }

            return $current->name;
        }

        return $categoryName;
    }

    /**
     * @param  array<int|string, mixed>  $children
     */
    private static function categoryTreeContains(array $children, string $categoryName): bool
    {
        foreach ($children as $child) {
            if (! is_array($child)) {
                continue;
            }

            $childName = (string) ($child['name'] ?? '');
            if (($child['type'] ?? '') === 'category' && strcasecmp($childName, $categoryName) === 0) {
                return true;
            }

            if (! empty($child['children']) && is_array($child['children'])) {
                if (self::categoryTreeContains($child['children'], $categoryName)) {
                    return true;
                }
            }
        }

        return false;
    }

    private static function rangeImage(?string $image, string $name = ''): string
    {
        if ($image === null || $image === '') {
            return self::fallbackImage($name);
        }

        if (str_starts_with($image, 'http://') || str_starts_with($image, 'https://')) {
            return $image;
        }

        return '/'.ltrim($image, '/');
    }
}
