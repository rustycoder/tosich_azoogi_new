<?php

namespace App\Support;

use Illuminate\Support\Facades\File;

class ProductCatalog
{
    /**
     * Get parent categories dynamically extracted from public/assets/data/products.json.
     *
     * @return array<int, array{title: string, body: string, image: string, href: string, count: int}>
     */
    public static function parentCategories(): array
    {
        $path = public_path('assets/data/products.json');

        if (! File::exists($path)) {
            return [];
        }

        $data = json_decode((string) File::get($path), true);

        if (! is_array($data) || empty($data['tree']) || ! is_array($data['tree'])) {
            return [];
        }

        $tree = $data['tree'];
        $products = is_array($data['products'] ?? null) ? $data['products'] : [];

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

        // Refined architectural descriptions
        $descriptions = [
            'NEON' => 'Seamless flexible linear lighting for interior and exterior architectural contours.',
            'Profiles' => 'Trimless plaster-in, recessed, surfaced and corner aluminium extrusion channels.',
            'Linear Lights' => 'Architectural linear fixtures and integrated illuminated systems.',
            'Strips and Flex' => 'High-output dotless COB, SMD strips and flexible LED sheets.',
            'Outdoor & Architectural' => 'High-grade IP67/IP68 landscape, garden, and pathway luminaires.',
            'Drivers' => 'Intelligent 5-in-1 dimming, DALI-2, and switchable power supplies.',
            'Accessories' => 'Precision diffusers, aluminium mounting tracks, and end caps.',
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
            $image = null;
            $fallbackDesc = '';

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

                    if ($fallbackDesc === '' && ! empty($product['product_short_description'])) {
                        $fallbackDesc = trim((string) $product['product_short_description']);
                    }
                }
            }

            $body = $descriptions[$name] ?? ($fallbackDesc !== '' ? $fallbackDesc : "{$count} products available");

            $categories[] = [
                'title' => $name,
                'body' => $body,
                'image' => $image ? '/'.ltrim($image, '/') : '/assets/img/neon.webp',
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
}
