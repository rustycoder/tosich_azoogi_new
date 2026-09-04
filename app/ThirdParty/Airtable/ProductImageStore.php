<?php

namespace App\ThirdParty\Airtable;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class ProductImageStore
{
    /**
     * @param  array{categories: list<mixed>, products: list<array<string, mixed>>, tree: list<array<string, mixed>>}  $catalog
     * @return array{categories: list<mixed>, products: list<array<string, mixed>>, tree: list<array<string, mixed>>}
     */
    public function localize(array $catalog): array
    {
        $catalog['products'] = $this->localizeProducts($catalog['products']);
        $catalog['tree'] = array_map(fn (mixed $node): mixed => $this->localizeTreeNode($node), $catalog['tree']);

        return $catalog;
    }

    /**
     * @param  list<array<string, mixed>>  $products
     * @return list<array<string, mixed>>
     */
    public function localizeProducts(array $products): array
    {
        foreach ($products as $index => $product) {
            $prefix = (string) ($product['id'] ?? $product['product_code'] ?? 'product');
            $products[$index] = $this->localizeProduct($product, $prefix);
        }

        return $products;
    }

    /**
     * @param  array<string, mixed>  $product
     * @return array<string, mixed>
     */
    private function localizeProduct(array $product, string $prefix): array
    {
        if (isset($product['product_images'])) {
            $product['product_images'] = $this->localizeList($product['product_images'], $prefix, 'products');
        }

        if (isset($product['product_dimension'])) {
            $product['product_dimension'] = $this->localizeList($product['product_dimension'], $prefix.'_dim', 'products');
        }

        if (isset($product['technical_icons'])) {
            $product['technical_icons'] = $this->localizeList($product['technical_icons'], $prefix.'_icon', 'icons');
        }

        if (isset($product['product_features']) && is_array($product['product_features'])) {
            foreach ($product['product_features'] as $name => $items) {
                if (! is_array($items)) {
                    continue;
                }

                foreach ($items as $itemIndex => $item) {
                    if (! is_array($item) || ! is_string($item['icon'] ?? null) || ! str_starts_with($item['icon'], 'http')) {
                        continue;
                    }

                    $safeName = Str::slug((string) $name, '_');
                    $safeValue = Str::slug((string) ($item['value'] ?? ''), '_');
                    $product['product_features'][$name][$itemIndex]['icon'] = $this->download(
                        $item['icon'],
                        'attr_'.$safeName.'_'.$safeValue,
                        'attribute_icon',
                    );
                }
            }
        }

        return $product;
    }

    private function localizeTreeNode(mixed $node): mixed
    {
        if (! is_array($node)) {
            return $node;
        }

        if (isset($node['variants']) && is_array($node['variants'])) {
            foreach ($node['variants'] as $name => $data) {
                if (is_array($data)) {
                    $node['variants'][$name] = $this->localizeProduct($data, (string) ($data['id'] ?? $name));
                }
            }
        }

        if (isset($node['children']) && is_array($node['children'])) {
            $node['children'] = array_map(fn (mixed $child): mixed => $this->localizeTreeNode($child), $node['children']);
        }

        return $node;
    }

    private function localizeList(mixed $value, string $prefix, string $folder): mixed
    {
        if (is_array($value)) {
            return array_map(fn (mixed $item): mixed => is_string($item) ? $this->download($item, $prefix, $folder) : $item, $value);
        }

        return is_string($value) ? $this->download($value, $prefix, $folder) : $value;
    }

    public function download(string $url, string $prefix, string $folder): string
    {
        if (! str_starts_with($url, 'http')) {
            return $url;
        }

        $clean = explode('?', $url)[0];
        $hash = substr(md5($clean), 0, 10);
        $safe = preg_replace('/[^A-Za-z0-9_-]/', '', $prefix) ?: 'prod';
        $directory = public_path('assets/img/'.$folder);

        if (! is_dir($directory)) {
            mkdir($directory, 0755, true);
        }

        try {
            $response = Http::timeout(30)->withHeaders([
                'User-Agent' => 'AzoogiProductSync/1.0',
            ])->get($url);

            if (! $response->successful()) {
                return $this->existingPath($directory, $safe, $hash, $folder) ?? $url;
            }

            $body = $response->body();
            $ext = $this->extension($response->header('Content-Type'), $body, $clean);
            $filename = $safe.'_'.$hash.$ext;
            file_put_contents($directory.DIRECTORY_SEPARATOR.$filename, $body);

            return 'assets/img/'.$folder.'/'.$filename;
        } catch (\Throwable) {
            return $this->existingPath($directory, $safe, $hash, $folder) ?? $url;
        }
    }

    private function existingPath(string $directory, string $safe, string $hash, string $folder): ?string
    {
        foreach (['.jpg', '.jpeg', '.png', '.webp', '.gif', '.svg'] as $ext) {
            $filename = $safe.'_'.$hash.$ext;
            if (is_file($directory.DIRECTORY_SEPARATOR.$filename)) {
                return 'assets/img/'.$folder.'/'.$filename;
            }
        }

        return null;
    }

    private function extension(?string $contentType, string $body, string $url): string
    {
        $type = strtolower((string) $contentType);

        if (str_contains($type, 'svg') || str_starts_with(ltrim($body), '<svg') || str_contains(substr($body, 0, 300), '<svg')) {
            return '.svg';
        }

        if (str_contains($type, 'png') || str_starts_with($body, "\x89PNG")) {
            return '.png';
        }

        if (str_contains($type, 'webp') || (str_starts_with($body, 'RIFF') && str_contains(substr($body, 0, 20), 'WEBP'))) {
            return '.webp';
        }

        if (str_contains($type, 'gif') || str_starts_with($body, 'GIF8')) {
            return '.gif';
        }

        $path = pathinfo(parse_url($url, PHP_URL_PATH) ?? '', PATHINFO_EXTENSION);
        $path = strtolower($path);

        if (in_array($path, ['jpg', 'jpeg', 'png', 'webp', 'gif', 'svg'], true)) {
            return '.'.$path;
        }

        return '.jpg';
    }
}
