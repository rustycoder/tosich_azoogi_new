<?php

namespace App\ThirdParty\Airtable;

final class ProductNormalizer
{
    /**
     * @param  list<array{id: string, fields: array<string, mixed>}>  $products
     * @param  list<array{id: string, fields: array<string, mixed>}>  $categories
     * @param  list<array{id: string, fields: array<string, mixed>}>  $attributes
     * @return array{categories: list<string>, products: list<array<string, mixed>>, tree: list<array<string, mixed>>}
     */
    public function compile(array $products, array $categories, array $attributes): array
    {
        $compiled = $this->compileProducts($products, $categories, $attributes);
        [$categoryNames, $tree] = $this->buildCategoryTree($categories, $compiled);

        return [
            'categories' => $categoryNames,
            'products' => $compiled,
            'tree' => $tree,
        ];
    }

    /**
     * @param  list<array{id: string, fields: array<string, mixed>}>  $products
     * @param  list<array{id: string, fields: array<string, mixed>}>  $categories
     * @param  list<array{id: string, fields: array<string, mixed>}>  $attributes
     * @return list<array<string, mixed>>
     */
    public function compileProducts(array $products, array $categories, array $attributes): array
    {
        usort($products, fn (array $a, array $b): int => $this->order($a) <=> $this->order($b));
        usort($categories, fn (array $a, array $b): int => $this->order($a) <=> $this->order($b));
        usort($attributes, fn (array $a, array $b): int => $this->order($a) <=> $this->order($b));

        $attrIndex = $this->attributeIndex($attributes);
        $attrLookup = [];

        foreach ($attrIndex as $info) {
            $key = mb_strtolower(trim((string) $info['name'])).'|'.mb_strtolower(trim((string) $info['value']));
            if (($info['icon'] ?? '') !== '') {
                $attrLookup[$key] = (string) $info['icon'];
            }
        }

        $catIndex = [];

        foreach ($categories as $category) {
            $fields = $category['fields'];
            $name = trim((string) ($fields['Category_Name'] ?? $fields['Category Name'] ?? $fields['Name'] ?? ''));
            $catIndex[$category['id']] = $name;
        }

        $compiled = [];

        foreach ($products as $record) {
            $fields = $record['fields'];
            $status = $this->statusValue($fields['Status'] ?? $fields['status'] ?? '');

            if ($status !== '' && $status !== 'publish') {
                continue;
            }

            $name = (string) ($fields['Product_Name'] ?? $fields['Product Name'] ?? $fields['Name'] ?? $fields['Title'] ?? 'Unnamed Product');
            $resolvedCategories = $this->resolveCategories($fields['Category'] ?? $fields['Product Category'] ?? $fields['Categories'] ?? 'General', $catIndex);
            $features = [];

            foreach ($this->parseAttributeKeys((string) ($fields['Attributes keys'] ?? $fields['Attribute Keys'] ?? $fields['Attributes Keys'] ?? '')) as $key => $items) {
                foreach ($items as $item) {
                    $this->addFeature($features, $key, $item['value'], $item['icon'] ?? '', $attrLookup);
                }
            }

            foreach ($fields as $value) {
                if (! is_array($value)) {
                    continue;
                }

                foreach ($value as $item) {
                    if (! is_string($item) || ! isset($attrIndex[$item])) {
                        continue;
                    }

                    $linked = $attrIndex[$item];
                    $this->addFeature($features, (string) $linked['name'], $linked['value'], (string) ($linked['icon'] ?? ''), $attrLookup);
                }
            }

            foreach ($attrIndex as $linked) {
                if (in_array($record['id'], $linked['product_ids'], true)) {
                    $this->addFeature($features, (string) $linked['name'], $linked['value'], (string) ($linked['icon'] ?? ''), $attrLookup);
                }
            }

            $skuMappings = $this->compactSkuMappings($this->parseJsonField(
                $fields['SKU Mappings'] ?? $fields['SKU mappings'] ?? $fields['sku_mappings'] ?? $fields['SKU Mapping'] ?? $fields['sku_mapping'] ?? [],
                [],
            ));

            $entry = [
                'id' => $record['id'],
                'product_name' => $name,
                'order' => $this->orderValue($fields),
                'category' => $resolvedCategories[0],
                'categories' => $resolvedCategories,
                'product_code' => $this->sanitize($fields['Product Code'] ?? $fields['Product code'] ?? $fields['product_code'] ?? ''),
                'sku_mappings' => $skuMappings,
                'product_short_description' => (string) ($fields['Product short description'] ?? $fields['Short description'] ?? $fields['short_description'] ?? $fields['Short Description'] ?? ''),
                'product_description' => (string) ($fields['Product long description'] ?? $fields['Product description'] ?? $fields['Long description'] ?? $fields['Description'] ?? $fields['description'] ?? ''),
                'product_images' => $this->imageUrls($fields),
                'product_dimension' => $this->sanitize($fields['Product Dimension'] ?? $fields['Product dimension'] ?? ''),
                'stocked_item' => $this->sanitize($fields['Stocked Item'] ?? $fields['Stock / Quantity'] ?? ''),
                'datasheet' => $this->sanitize($fields['Datasheet'] ?? ''),
                'technical_icons' => $this->sanitize($fields['Technical Icons'] ?? $fields['Technical icons'] ?? $fields['Technical_Icons'] ?? $fields['Product Icons'] ?? $fields['Product icons'] ?? ''),
                'meta_keywords' => $this->sanitize($fields['Meta Keywords'] ?? $fields['meta_keywords'] ?? $fields['Meta keywords'] ?? $fields['meta keywords'] ?? ''),
                'supplier_name' => $this->sanitize($fields['Supplier Name'] ?? ''),
                'status' => $this->sanitize($fields['Status'] ?? ''),
                'product_type' => $this->sanitize($fields['Product type'] ?? ''),
                'product_features' => $features,
                'options' => $this->parseJsonField($fields['Options'] ?? $fields['options'] ?? [], []),
                'constraints' => $this->parseJsonField($fields['Constraints'] ?? $fields['constraints'] ?? [], []),
            ];

            $product = [];

            foreach ($entry as $key => $value) {
                if ($value !== null && $value !== '' && $value !== 'No' && $value !== 'draft' && $value !== 'simple' && $value !== [''] && $value !== []) {
                    $product[$key] = $value;
                }
            }

            $compiled[] = $product;
        }

        return $compiled;
    }

    /**
     * Rebuild the storefront payload from products and categories already stored in MySQL.
     *
     * @param  list<array<string, mixed>>  $products
     * @param  list<array{id: string, fields: array<string, mixed>}>  $categories
     * @return array{categories: list<string>, products: list<array<string, mixed>>, tree: list<array<string, mixed>>}
     */
    public function fromStored(array $products, array $categories): array
    {
        [$categoryNames, $tree] = $this->buildCategoryTree($categories, $products);

        return [
            'categories' => $categoryNames,
            'products' => $products,
            'tree' => $tree,
        ];
    }

    /**
     * @param  array<string, mixed>  $fields
     */
    public function order(array $record): float
    {
        $fields = $record['fields'] ?? $record;

        return is_array($fields) ? $this->orderValue($fields) : INF;
    }

    /**
     * @param  array<string, mixed>  $fields
     */
    public function orderValue(array $fields): float
    {
        $raw = $fields['Order'] ?? $fields['order'] ?? null;

        if ($raw === null || $raw === '') {
            return INF;
        }

        return is_numeric($raw) ? (float) $raw : INF;
    }

    /**
     * @param  array<string, mixed>  $fields
     * @return list<string>
     */
    public function imageUrls(array $fields): array
    {
        $images = [];
        $keys = ['images', 'product_images', 'image', 'attachments', 'photos', 'media', 'product image', 'product gallery', 'gallery', 'photo'];

        foreach ($fields as $key => $value) {
            $lower = mb_strtolower((string) $key);

            if (str_contains($lower, 'dimension') || (! in_array($lower, $keys, true) && ! str_contains($lower, 'image') && ! str_contains($lower, 'attachment') && ! str_contains($lower, 'gallery') && ! str_contains($lower, 'photo'))) {
                continue;
            }

            foreach ($this->urlList($value) as $url) {
                $images[] = $url;
            }
        }

        return array_values(array_unique($images));
    }

    /**
     * @param  list<array{id: string, fields: array<string, mixed>}>  $records
     * @return array<string, array{id: string, name: string, value: string, icon: string, order: float, product_ids: list<string>}>
     */
    private function attributeIndex(array $records): array
    {
        $index = [];

        foreach ($records as $record) {
            $fields = $record['fields'];
            $name = trim((string) ($fields['Attribute name'] ?? $fields['Attribute_Name'] ?? $fields['Attribute Name'] ?? $fields['Name'] ?? $fields['Attribute'] ?? ''));
            $value = $fields['Term Name'] ?? $fields['Attribute Value'] ?? $fields['Attribute_Value'] ?? $fields['Value'] ?? $fields['Option'] ?? $fields['Term Value'] ?? '';
            $icon = $this->firstUrl($fields['Attribute Icon'] ?? $fields['Attribute_Icon'] ?? $fields['Attribute icon'] ?? $fields['Icon'] ?? $fields['attribute_icon'] ?? $fields['attribute icon'] ?? '');
            $links = $fields['Simple Products'] ?? $fields['Product'] ?? $fields['Products'] ?? [];

            if (is_string($links)) {
                $links = [$links];
            }

            $index[$record['id']] = [
                'id' => $record['id'],
                'name' => $name,
                'value' => $value === null ? '' : (string) $value,
                'icon' => $icon,
                'order' => $this->orderValue($fields),
                'product_ids' => array_values(array_filter(
                    is_array($links) ? $links : [],
                    fn (mixed $id): bool => is_string($id) && $id !== '',
                )),
            ];
        }

        return $index;
    }

    /**
     * @param  array<string, list<array{value: string, icon: string}>>  $features
     * @param  array<string, string>  $lookup
     */
    private function addFeature(array &$features, string $name, mixed $value, string $icon, array $lookup): void
    {
        $name = trim($name);

        if ($name === '' || $value === null || $value === '') {
            return;
        }

        if (is_array($value)) {
            if (isset($value['value'])) {
                $this->addFeature($features, $name, $value['value'], (string) ($value['icon'] ?? $icon), $lookup);

                return;
            }

            foreach ($value as $item) {
                $this->addFeature($features, $name, $item, $icon, $lookup);
            }

            return;
        }

        $text = trim((string) $value);

        if ($text === '') {
            return;
        }

        if ($icon === '') {
            $icon = $lookup[mb_strtolower($name).'|'.mb_strtolower($text)] ?? '';
        }

        $features[$name] ??= [];
        $existing = array_column($features[$name], 'value');

        if (! in_array($text, $existing, true)) {
            $features[$name][] = ['value' => $text, 'icon' => $icon];

            return;
        }

        if ($icon === '') {
            return;
        }

        foreach ($features[$name] as $index => $item) {
            if (($item['value'] ?? '') === $text && ($item['icon'] ?? '') === '') {
                $features[$name][$index]['icon'] = $icon;
            }
        }
    }

    /**
     * @return array<string, list<array{value: string, icon: string}>>
     */
    private function parseAttributeKeys(string $raw): array
    {
        $result = [];

        foreach (array_filter(array_map('trim', explode('|', $raw))) as $part) {
            if (! str_contains($part, ':')) {
                continue;
            }

            [$key, $value] = explode(':', $part, 2);
            $key = trim($key);
            $value = trim($value);

            if ($key === '' || $value === '') {
                continue;
            }

            $result[$key] ??= [];

            if (! in_array($value, array_column($result[$key], 'value'), true)) {
                $result[$key][] = ['value' => $value, 'icon' => ''];
            }
        }

        return $result;
    }

    /**
     * @param  array<string, string>  $catIndex
     * @return list<string>
     */
    private function resolveCategories(mixed $raw, array $catIndex): array
    {
        $names = [];

        if (is_array($raw)) {
            foreach ($raw as $item) {
                $name = trim($catIndex[$item] ?? (string) $item);
                if ($name !== '' && ! in_array($name, $names, true)) {
                    $names[] = $name;
                }
            }
        } elseif (is_string($raw) && $raw !== '') {
            foreach (preg_split('/[,|]/', $raw) ?: [] as $part) {
                $name = trim($part);
                if ($name !== '' && ! in_array($name, $names, true)) {
                    $names[] = $name;
                }
            }
        }

        return $names === [] ? ['General'] : $names;
    }

    private function parseJsonField(mixed $value, mixed $default): mixed
    {
        if (is_array($value)) {
            return $value;
        }

        if (is_string($value) && trim($value) !== '') {
            $decoded = json_decode(trim($value), true);

            if (is_array($decoded)) {
                return $decoded;
            }
        }

        return $default;
    }

    /**
     * @return array<string, string>
     */
    private function compactSkuMappings(mixed $mappings): array
    {
        if (! is_array($mappings)) {
            return [];
        }

        if ($mappings !== [] && array_is_list($mappings)) {
            $compact = [];

            foreach ($mappings as $item) {
                if (! is_array($item) || ! isset($item['sku'], $item['combination']) || ! is_array($item['combination'])) {
                    continue;
                }

                $compact[implode(',', array_map(strval(...), $item['combination']))] = (string) $item['sku'];
            }

            return $compact;
        }

        $compact = [];

        foreach ($mappings as $key => $value) {
            if (is_string($key) && (is_string($value) || is_numeric($value))) {
                $compact[$key] = (string) $value;
            }
        }

        return $compact;
    }

    private function sanitize(mixed $value): mixed
    {
        if (is_string($value) || is_int($value) || is_float($value) || is_bool($value) || $value === null) {
            return $value;
        }

        $urls = $this->urlList($value);

        if ($urls !== []) {
            return $urls;
        }

        if (is_object($value)) {
            $array = (array) $value;

            return $array['url'] ?? $value;
        }

        return $value;
    }

    /**
     * @return list<string>
     */
    private function urlList(mixed $value): array
    {
        $urls = [];

        if (is_array($value)) {
            foreach ($value as $item) {
                foreach ($this->urlList($item) as $url) {
                    $urls[] = $url;
                }
            }
        } elseif (is_string($value) && str_starts_with($value, 'http')) {
            $urls[] = $value;
        }

        if (is_array($value) && isset($value['url']) && is_string($value['url']) && str_starts_with($value['url'], 'http')) {
            $urls[] = $value['url'];
        }

        return $urls;
    }

    private function firstUrl(mixed $value): string
    {
        $urls = $this->urlList($value);

        return $urls[0] ?? '';
    }

    private function statusValue(mixed $raw): string
    {
        if (is_array($raw) && isset($raw['name'])) {
            return mb_strtolower(trim((string) $raw['name']));
        }

        if (is_array($raw) && $raw !== []) {
            return mb_strtolower(trim((string) $raw[0]));
        }

        return mb_strtolower(trim((string) $raw));
    }

    /**
     * @param  list<array{id: string, fields: array<string, mixed>}>  $categoriesRaw
     * @param  list<array<string, mixed>>  $products
     * @return array{0: list<string>, 1: list<array<string, mixed>>}
     */
    private function buildCategoryTree(array $categoriesRaw, array &$products): array
    {
        $byId = [];
        $byName = [];

        foreach ($categoriesRaw as $record) {
            $fields = $record['fields'];
            $name = trim((string) ($fields['Name'] ?? $fields['Category_Name'] ?? $fields['Category Name'] ?? $fields['Title'] ?? $fields['Category'] ?? ''));

            if ($name === '') {
                continue;
            }

            $parents = $fields['Parent'] ?? $fields['Parent Category'] ?? $fields['Parent_Category'] ?? [];
            $children = $fields['Child Categories'] ?? $fields['Subcategories'] ?? $fields['Children'] ?? [];

            $info = [
                'id' => $record['id'],
                'name' => $name,
                'order' => $this->orderValue($fields),
                'parent_refs' => $this->stringList($parents),
                'child_refs' => $this->stringList($children),
                'parent_ids' => [],
                'child_ids' => [],
                'products' => [],
            ];

            $byId[$record['id']] = $info;
            $byName[mb_strtolower($name)] = &$byId[$record['id']];
        }

        foreach ($byId as $id => &$cat) {
            foreach ($cat['parent_refs'] as $ref) {
                $parent = $byId[$ref] ?? $byName[mb_strtolower($ref)] ?? null;
                if ($parent === null) {
                    continue;
                }
                if (! in_array($parent['id'], $cat['parent_ids'], true)) {
                    $cat['parent_ids'][] = $parent['id'];
                }
                if (! in_array($id, $byId[$parent['id']]['child_ids'], true)) {
                    $byId[$parent['id']]['child_ids'][] = $id;
                }
            }

            foreach ($cat['child_refs'] as $ref) {
                $child = $byId[$ref] ?? $byName[mb_strtolower($ref)] ?? null;
                if ($child === null) {
                    continue;
                }
                if (! in_array($child['id'], $cat['child_ids'], true)) {
                    $cat['child_ids'][] = $child['id'];
                }
                if (! in_array($id, $byId[$child['id']]['parent_ids'], true)) {
                    $byId[$child['id']]['parent_ids'][] = $id;
                }
            }
        }
        unset($cat);

        $fallback = [];

        foreach ($products as &$product) {
            $raw = $product['categories'] ?? (isset($product['category']) ? [$product['category']] : ['General']);
            $raw = is_array($raw) ? $raw : [$raw];
            $matched = [];

            foreach ($raw as $item) {
                $text = trim((string) $item);
                if (isset($byId[$text])) {
                    $matched[] = $text;
                } elseif (isset($byName[mb_strtolower($text)])) {
                    $matched[] = $byName[mb_strtolower($text)]['id'];
                }
            }

            if ($matched !== []) {
                $names = [];
                $paths = [];

                foreach ($matched as $catId) {
                    $names[] = $byId[$catId]['name'];
                    $path = [];
                    $current = $catId;
                    $seen = [];

                    while ($current !== null && ! in_array($current, $seen, true)) {
                        $seen[] = $current;
                        array_unshift($path, $byId[$current]['name']);
                        $current = $byId[$current]['parent_ids'][0] ?? null;
                    }

                    $paths[] = $path;
                    $byId[$catId]['products'][] = $product;
                }

                $product['category'] = $names[0];
                $product['categories'] = array_values(array_unique($names));
                $product['category_path'] = $paths[0];
                $product['category_paths'] = $paths;
            } else {
                $name = trim((string) ($raw[0] ?? 'General')) ?: 'General';
                $product['category'] = $name;
                $product['categories'] = [$name];
                $product['category_path'] = [$name];
                $product['category_paths'] = [[$name]];
                $fallback[$name][] = $product;
            }
        }
        unset($product);

        $roots = array_values(array_filter($byId, fn (array $cat): bool => $cat['parent_ids'] === []));

        if ($roots === [] && $byId !== []) {
            $roots = array_values($byId);
        }

        usort($roots, fn (array $a, array $b): int => ($a['order'] <=> $b['order']));

        $tree = [];

        foreach ($roots as $root) {
            $tree[] = $this->categoryNode($root, $byId);
        }

        if ($tree === []) {
            foreach ($fallback as $name => $list) {
                $children = [];
                usort($list, fn (array $a, array $b): int => ($a['order'] ?? INF) <=> ($b['order'] ?? INF));

                foreach ($list as $product) {
                    $title = (string) ($product['product_name'] ?? 'Product');
                    $children[] = [
                        'type' => 'product_row',
                        'name' => $title,
                        'variants' => [$title => $product['id'] ?? $title],
                    ];
                }

                $tree[] = ['type' => 'category', 'name' => $name, 'children' => $children];
            }
        }

        $names = [];
        $collect = function (array $nodes) use (&$collect, &$names): void {
            foreach ($nodes as $node) {
                if (($node['type'] ?? '') === 'category' && ($node['name'] ?? '') !== '') {
                    $names[] = $node['name'];
                }
                if (isset($node['children']) && is_array($node['children'])) {
                    $collect($node['children']);
                }
            }
        };
        $collect($tree);

        return [array_values(array_unique($names)), $tree];
    }

    /**
     * @param  array<string, mixed>  $cat
     * @param  array<string, array<string, mixed>>  $byId
     * @return array<string, mixed>
     */
    private function categoryNode(array $cat, array $byId): array
    {
        $children = [];
        $childInfos = [];

        foreach ($cat['child_ids'] as $childId) {
            if (isset($byId[$childId])) {
                $childInfos[] = $byId[$childId];
            }
        }

        usort($childInfos, fn (array $a, array $b): int => ($a['order'] <=> $b['order']));

        foreach ($childInfos as $child) {
            $children[] = $this->categoryNode($child, $byId);
        }

        $products = $cat['products'];
        usort($products, fn (array $a, array $b): int => ($a['order'] ?? INF) <=> ($b['order'] ?? INF));

        foreach ($products as $product) {
            $title = (string) ($product['product_name'] ?? 'Product');
            $children[] = [
                'type' => 'product_row',
                'name' => $title,
                'variants' => [$title => $product['id'] ?? $title],
            ];
        }

        return [
            'type' => 'category',
            'name' => $cat['name'],
            'children' => $children,
        ];
    }

    /**
     * @return list<string>
     */
    private function stringList(mixed $value): array
    {
        if (! is_array($value)) {
            $value = $value === null || $value === '' ? [] : [$value];
        }

        return array_values(array_filter(array_map(
            fn (mixed $item): string => trim((string) $item),
            $value,
        )));
    }
}
