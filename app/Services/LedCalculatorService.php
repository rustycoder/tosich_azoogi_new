<?php

namespace App\Services;

use App\Repositories\Contracts\IProductRepository;
use App\Services\Contracts\ILedCalculatorService;

class LedCalculatorService implements ILedCalculatorService
{
    /**
     * @var list<string>
     */
    private const STRIP_CATEGORIES = ['COB', 'SMD', 'Lumoflex', 'SMD Strip', 'RGBW Strip', 'LED Flex Sheet'];

    /**
     * @var list<string>
     */
    private const NEON_CATEGORIES = ['Top View', 'Side View', '3D', 'NEON'];

    public function __construct(private IProductRepository $products) {}

    public function catalog(): array
    {
        $lights = [];
        $drivers = [];
        $controllers = [];

        foreach ($this->products->compiled()['products'] ?? [] as $product) {
            if (! is_array($product) || ! $this->isPublished($product)) {
                continue;
            }

            $kind = $this->kind($product);

            if ($kind === 'driver') {
                $drivers[] = $this->driverCard($product);

                continue;
            }

            if ($kind === 'controller') {
                $controllers[] = $this->productCard($product, 'controller');

                continue;
            }

            if ($kind === 'strip' || $kind === 'neon') {
                $lights[] = $this->lightCard($product, $kind);
            }
        }

        return [
            'lights' => array_values(array_filter($lights)),
            'drivers' => array_values(array_filter($drivers)),
            'controllers' => array_values(array_filter($controllers)),
        ];
    }

    /**
     * @param  array<string, mixed>  $product
     */
    private function isPublished(array $product): bool
    {
        $status = strtolower(trim((string) ($product['status'] ?? 'publish')));

        return $status === '' || $status === 'publish';
    }

    /**
     * @param  array<string, mixed>  $product
     */
    private function kind(array $product): ?string
    {
        $category = (string) ($product['category'] ?? '');
        $name = (string) ($product['product_name'] ?? '');
        $haystack = $category.' '.$name;

        if (str_contains(mb_strtolower($haystack), 'driver')) {
            return 'driver';
        }

        if (preg_match('/controller|remote|wifi|wall panel|rf\b/i', $haystack) === 1) {
            return 'controller';
        }

        if (in_array($category, self::NEON_CATEGORIES, true) || preg_match('/\bneon\b/i', $name) === 1) {
            return 'neon';
        }

        if (in_array($category, self::STRIP_CATEGORIES, true) || preg_match('/strip|lumoflex|flex sheet/i', $haystack) === 1) {
            return 'strip';
        }

        return null;
    }

    /**
     * @param  array<string, mixed>  $product
     * @return array<string, mixed>|null
     */
    private function lightCard(array $product, string $family): ?array
    {
        $features = is_array($product['product_features'] ?? null) ? $product['product_features'] : [];
        $ips = $this->ipRatings($features);
        $colors = $this->colors($features);
        $voltages = $this->voltages($features);
        $powers = $this->powers($features);
        $widths = $this->widths($features);

        if ($ips === [] && $voltages === [] && $powers === []) {
            return null;
        }

        $card = $this->productCard($product, $family);

        return [
            ...$card,
            'family' => $family,
            'chip' => $this->chip($product, $family),
            'neon_type' => $family === 'neon' ? $this->neonType($product) : null,
            'ips' => $ips,
            'has_single' => $colors['single'] !== [],
            'has_multi' => $colors['multi'] !== [],
            'ccts' => $colors['single'],
            'color_types' => $colors['multi'],
            'voltages' => $voltages !== [] ? $voltages : ['24V'],
            'powers' => $powers,
            'widths' => $widths,
            'leds_per_m' => $this->firstValue($features, 'LEDs/m', 'LED Density', 'Resolution'),
            'cri' => $this->firstValue($features, 'CRI'),
            'warranty' => $this->firstValue($features, 'Warranty') ?: '5 Years',
        ];
    }

    /**
     * @param  array<string, mixed>  $product
     * @return array<string, mixed>|null
     */
    private function driverCard(array $product): ?array
    {
        $features = is_array($product['product_features'] ?? null) ? $product['product_features'] : [];
        $card = $this->productCard($product, 'driver');
        $haystack = mb_strtolower($card['name'].' '.$card['category']);

        $type = 'dimmable';
        if (str_contains($haystack, 'dali')) {
            $type = 'dali-2';
        } elseif (str_contains($haystack, 'non-dimmable') || str_contains($haystack, 'non dimmable')) {
            $type = 'non-dimmable';
        } elseif (str_contains($haystack, 'switchable') && ! str_contains($haystack, 'dimm')) {
            $type = 'non-dimmable';
        }

        return [
            ...$card,
            'type' => $type,
            'voltages' => $this->voltages($features),
            'watts' => $this->driverWatts($features),
            'ip' => $this->ipRatings($features)[0]['code'] ?? 'IP67',
        ];
    }

    /**
     * @param  array<string, mixed>  $product
     * @return array<string, mixed>
     */
    private function productCard(array $product, string $role): array
    {
        $id = (string) ($product['id'] ?? '');
        $images = $product['product_images'] ?? [];
        $image = is_array($images) ? (string) ($images[0] ?? '') : (string) $images;

        return [
            'id' => $id,
            'role' => $role,
            'name' => (string) ($product['product_name'] ?? 'LED Product'),
            'sku' => (string) ($product['product_code'] ?? ''),
            'category' => (string) ($product['category'] ?? ''),
            'image' => media_url($image !== '' ? $image : '/assets/img/neon.webp'),
            'url' => '/product-detail?id='.rawurlencode($id),
        ];
    }

    /**
     * @param  array<string, mixed>  $product
     */
    private function chip(array $product, string $family): string
    {
        if ($family === 'neon') {
            return 'neon';
        }

        $haystack = mb_strtolower((string) ($product['category'] ?? '').' '.(string) ($product['product_name'] ?? '').' '.(string) ($product['product_code'] ?? ''));

        if (str_contains($haystack, 'csp')) {
            return 'csp';
        }

        if (str_contains($haystack, 'cob')) {
            return 'cob';
        }

        return 'smd';
    }

    /**
     * @param  array<string, mixed>  $product
     */
    private function neonType(array $product): string
    {
        $haystack = mb_strtolower((string) ($product['category'] ?? '').' '.(string) ($product['product_name'] ?? ''));

        if (str_contains($haystack, '360') || str_contains($haystack, 'dual bend') || str_contains($haystack, '3d')) {
            return 'neon-360';
        }

        if (str_contains($haystack, 'top')) {
            return 'neon-top';
        }

        return 'neon-side';
    }

    /**
     * @param  array<string, mixed>  $features
     * @return list<array{code: string, nano: bool}>
     */
    private function ipRatings(array $features): array
    {
        $ratings = [];

        foreach ($this->featureValues($features, 'IP Rating') as $raw) {
            if (preg_match('/IP\s*(\d{2})/i', $raw, $match) !== 1) {
                continue;
            }

            $code = 'IP'.$match[1];
            $ratings[$code.'|'.(str_contains(mb_strtolower($raw), 'nano') ? '1' : '0')] = [
                'code' => $code,
                'nano' => str_contains(mb_strtolower($raw), 'nano'),
            ];
        }

        return array_values($ratings);
    }

    /**
     * @param  array<string, mixed>  $features
     * @return array{single: list<string>, multi: list<string>}
     */
    private function colors(array $features): array
    {
        $single = [];
        $multi = [];

        foreach ($this->featureValues($features, 'Color Temperature', 'Light Color') as $raw) {
            $upper = strtoupper(str_replace(' ', '', $raw));

            if (str_contains($upper, 'RGB+CCT') || str_contains($upper, 'RGB+TW')) {
                $multi[] = 'RGB+CCT';
            } elseif (str_contains($upper, 'RGBW')) {
                $multi[] = 'RGBW';
            } elseif (str_contains($upper, 'RGB')) {
                $multi[] = 'RGB';
            } elseif (str_contains($upper, 'CCT') || str_contains($upper, 'TW') || str_contains($raw, '-')) {
                $multi[] = 'CCT';
            }

            if (preg_match('/(\d{3,4})\s*K/i', $raw, $match) === 1 && ! str_contains($raw, '-')) {
                $single[] = $match[1].'K';
            }
        }

        return [
            'single' => array_values(array_unique($single)),
            'multi' => array_values(array_unique($multi)),
        ];
    }

    /**
     * @param  array<string, mixed>  $features
     * @return list<string>
     */
    private function voltages(array $features): array
    {
        $values = [];

        foreach ($this->featureValues($features, 'Voltage') as $raw) {
            if (preg_match_all('/(\d{1,3})\s*V/i', $raw, $matches) === 0) {
                continue;
            }

            foreach ($matches[1] as $volts) {
                $values[] = $volts.'V';
            }
        }

        return array_values(array_unique($values));
    }

    /**
     * @param  array<string, mixed>  $features
     * @return list<string>
     */
    private function powers(array $features): array
    {
        $values = [];

        foreach ($this->featureValues($features, 'Power Consumption Rate', 'Power') as $raw) {
            if (preg_match('/(\d+(?:\.\d+)?)\s*W(?:\s*\/\s*m)?/i', $raw, $match) !== 1) {
                continue;
            }

            $values[] = str_contains(mb_strtolower($raw), '/m') || str_contains(mb_strtolower($raw), 'w/m')
                ? $match[1].'W/m'
                : $match[1].'W/m';
        }

        usort($values, fn (string $a, string $b): int => ((float) $a) <=> ((float) $b));

        return array_values(array_unique($values));
    }

    /**
     * @param  array<string, mixed>  $features
     * @return list<int>
     */
    private function driverWatts(array $features): array
    {
        $values = [];

        foreach ($this->featureValues($features, 'Power', 'Power Consumption Rate') as $raw) {
            if (preg_match('/(\d+(?:\.\d+)?)\s*W/i', $raw, $match) !== 1) {
                continue;
            }

            $values[] = (int) round((float) $match[1]);
        }

        $values = array_values(array_unique($values));
        sort($values);

        return $values;
    }

    /**
     * @param  array<string, mixed>  $features
     * @return list<string>
     */
    private function widths(array $features): array
    {
        $values = [];

        foreach ($this->featureValues($features, 'Strip Width', 'Dimension') as $raw) {
            if (preg_match('/max\s|^\d[\d,]*\s*mm\s*\(L\)/i', $raw) === 1) {
                continue;
            }

            if (preg_match('/(\d+(?:\.\d+)?)\s*mm\s*\(W\)\s*x\s*(\d+(?:\.\d+)?)\s*mm/i', $raw, $match) === 1) {
                $values[] = $this->formatWidth((float) $match[1], (float) $match[2]);

                continue;
            }

            if (preg_match('/(\d+(?:\.\d+)?)\s*mm/i', $raw, $match) === 1) {
                $values[] = $this->formatWidth((float) $match[1], null);
            }
        }

        return array_values(array_unique($values));
    }

    private function formatWidth(float $width, ?float $height): string
    {
        $wide = rtrim(rtrim(number_format($width, 1, '.', ''), '0'), '.');

        if ($height === null) {
            return $wide.'mm';
        }

        $high = rtrim(rtrim(number_format($height, 1, '.', ''), '0'), '.');

        return $wide.'x'.$high.'mm';
    }

    /**
     * @param  array<string, mixed>  $features
     */
    private function firstValue(array $features, string ...$keys): string
    {
        $values = $this->featureValues($features, ...$keys);

        return $values[0] ?? '';
    }

    /**
     * @param  array<string, mixed>  $features
     * @return list<string>
     */
    private function featureValues(array $features, string ...$keys): array
    {
        $values = [];

        foreach ($keys as $key) {
            if (! array_key_exists($key, $features)) {
                continue;
            }

            foreach ($this->flattenFeature($features[$key]) as $value) {
                $values[] = $value;
            }
        }

        return $values;
    }

    /**
     * @return list<string>
     */
    private function flattenFeature(mixed $value): array
    {
        if ($value === null || $value === '') {
            return [];
        }

        if (is_string($value) || is_numeric($value)) {
            return [trim((string) $value)];
        }

        if (! is_array($value)) {
            return [];
        }

        if (isset($value['value'])) {
            return $this->flattenFeature($value['value']);
        }

        $values = [];

        foreach ($value as $item) {
            foreach ($this->flattenFeature($item) as $inner) {
                $values[] = $inner;
            }
        }

        return $values;
    }
}
