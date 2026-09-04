<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\ProductAttribute;
use App\Repositories\Contracts\IProductRepository;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;

class ProductSeeder extends Seeder
{
    public function run(IProductRepository $products): void
    {
        $payload = $this->catalogPayload();

        if ($payload === null) {
            return;
        }

        $catalog = is_array($payload['products'] ?? null) ? $payload['products'] : [];
        $tree = is_array($payload['tree'] ?? null) ? $payload['tree'] : [];
        $catalog = $this->withListOrder($catalog);
        [$catalog, $attributes] = $this->attributesFromProducts($catalog);

        if (Product::query()->exists()) {
            if (! ProductAttribute::query()->exists()) {
                $products->persistLookups([], $attributes);
                $this->command?->info('Seeded '.count($attributes).' product attribute(s) from products.json.');
            }

            $this->backfillProductSortOrder($catalog);

            if (ProductAttribute::query()->exists() && ! Product::query()->whereNull('sort_order')->exists()) {
                $this->command?->info('Products already seeded; skipping JSON import.');
            }

            return;
        }

        $order = 1;
        $products->persistLookups($this->categoryRecords($tree, null, $order), $attributes);
        $products->persistProducts($catalog);

        $this->command?->info('Seeded '.count($catalog).' product(s) from products.json.');
    }

    /**
     * @return array<string, mixed>|null
     */
    private function catalogPayload(): ?array
    {
        $path = public_path('assets/data/products.json');

        if (! File::exists($path)) {
            $this->command?->warn('products.json is missing; skipping product seed.');

            return null;
        }

        $payload = json_decode(File::get($path), true);

        if (! is_array($payload)) {
            $this->command?->warn('products.json is invalid; skipping product seed.');

            return null;
        }

        return $payload;
    }

    /**
     * @param  list<mixed>  $nodes
     * @return list<array{id: string, fields: array<string, mixed>}>
     */
    private function categoryRecords(array $nodes, ?string $parentId, int &$order): array
    {
        $records = [];

        foreach ($nodes as $node) {
            if (! is_array($node) || ($node['type'] ?? '') !== 'category') {
                continue;
            }

            $name = trim((string) ($node['name'] ?? ''));

            if ($name === '') {
                continue;
            }

            $id = md5(($parentId ?? '').'|'.$name);
            $fields = [
                'Name' => $name,
                'Order' => $order,
            ];
            $order++;

            if ($parentId !== null) {
                $fields['Parent'] = [$parentId];
            }

            $records[] = [
                'id' => $id,
                'fields' => $fields,
            ];

            $children = is_array($node['children'] ?? null) ? $node['children'] : [];
            $records = [...$records, ...$this->categoryRecords($children, $id, $order)];
        }

        return $records;
    }

    /**
     * @param  list<mixed>  $products
     * @return list<mixed>
     */
    private function withListOrder(array $products): array
    {
        foreach ($products as $index => $product) {
            if (! is_array($product)) {
                continue;
            }

            $order = $product['order'] ?? $product['sort_order'] ?? null;

            if (is_numeric($order) && is_finite((float) $order)) {
                $products[$index]['order'] = (int) $order;

                continue;
            }

            $products[$index]['order'] = $index + 1;
        }

        return $products;
    }

    /**
     * @param  list<mixed>  $products
     */
    private function backfillProductSortOrder(array $products): void
    {
        foreach ($products as $index => $product) {
            if (! is_array($product) || ! isset($product['id'])) {
                continue;
            }

            $order = $product['order'] ?? $index + 1;

            Product::query()
                ->where('airtable_id', $product['id'])
                ->whereNull('sort_order')
                ->update(['sort_order' => (int) $order]);
        }
    }

    /**
     * @param  list<mixed>  $products
     * @return array{0: list<array<string, mixed>>, 1: list<array{id: string, fields: array<string, mixed>}>}
     */
    private function attributesFromProducts(array $products): array
    {
        $records = [];

        foreach ($products as $product) {
            if (! is_array($product) || ! is_array($product['product_features'] ?? null)) {
                continue;
            }

            foreach ($product['product_features'] as $name => $items) {
                if (! is_string($name) || $name === '') {
                    continue;
                }

                foreach ($this->featureItems($items) as $item) {
                    $value = trim((string) ($item['value'] ?? ''));

                    if ($value === '') {
                        continue;
                    }

                    $id = md5(mb_strtolower($name).'|'.mb_strtolower($value));
                    $icon = trim((string) ($item['icon'] ?? ''));
                    $existing = $records[$id]['fields']['Icon'] ?? '';

                    if (! isset($records[$id])) {
                        $records[$id] = [
                            'id' => $id,
                            'fields' => [
                                'Attribute name' => $name,
                                'Term Name' => $value,
                                'Icon' => $icon,
                                'Order' => count($records) + 1,
                            ],
                        ];
                    } elseif ($existing === '' && $icon !== '') {
                        $records[$id]['fields']['Icon'] = $icon;
                    }
                }
            }
        }

        foreach ($products as $index => $product) {
            if (! is_array($product) || ! is_array($product['product_features'] ?? null)) {
                continue;
            }

            foreach ($product['product_features'] as $name => $items) {
                if (! is_string($name) || ! is_array($items)) {
                    continue;
                }

                foreach ($items as $itemIndex => $item) {
                    if (! is_array($item)) {
                        continue;
                    }

                    $value = trim((string) ($item['value'] ?? ''));
                    $id = md5(mb_strtolower($name).'|'.mb_strtolower($value));
                    $icon = (string) ($records[$id]['fields']['Icon'] ?? '');

                    if (($item['icon'] ?? '') === '' && $icon !== '') {
                        $products[$index]['product_features'][$name][$itemIndex]['icon'] = $icon;
                    }
                }
            }
        }

        return [$products, array_values($records)];
    }

    /**
     * @return list<array{value: string, icon: string}>
     */
    private function featureItems(mixed $items): array
    {
        if (! is_array($items)) {
            $value = trim((string) $items);

            return $value === '' ? [] : [['value' => $value, 'icon' => '']];
        }

        if (isset($items['value'])) {
            return [[
                'value' => (string) $items['value'],
                'icon' => (string) ($items['icon'] ?? ''),
            ]];
        }

        $normalized = [];

        foreach ($items as $item) {
            if (is_array($item)) {
                $value = trim((string) ($item['value'] ?? ''));

                if ($value === '') {
                    continue;
                }

                $normalized[] = [
                    'value' => $value,
                    'icon' => (string) ($item['icon'] ?? ''),
                ];

                continue;
            }

            $value = trim((string) $item);

            if ($value !== '') {
                $normalized[] = [
                    'value' => $value,
                    'icon' => '',
                ];
            }
        }

        return $normalized;
    }
}
