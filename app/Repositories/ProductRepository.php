<?php

namespace App\Repositories;

use App\Enums\ProductSyncStatus;
use App\Models\Product;
use App\Models\ProductAttribute;
use App\Models\ProductCategory;
use App\Models\ProductSync;
use App\Repositories\Contracts\IProductRepository;
use App\ThirdParty\Airtable\ProductNormalizer;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class ProductRepository implements IProductRepository
{
    public function __construct(private ProductNormalizer $normalizer) {}

    public function persistProducts(array $products): void
    {
        foreach ($products as $product) {
            if (! is_array($product) || ! isset($product['id'])) {
                continue;
            }

            $airtableId = (string) $product['id'];
            $images = $product['product_images'] ?? [];
            $cover = is_array($images) ? (string) ($images[0] ?? '') : (is_string($images) ? $images : '');

            $row = Product::withTrashed()->firstOrNew(['airtable_id' => $airtableId]);
            $row->fill([
                'product_name' => (string) ($product['product_name'] ?? 'Unnamed Product'),
                'category' => $this->storedString($product['category'] ?? null),
                'status' => $this->storedString($product['status'] ?? null, 32),
                'sort_order' => $this->storedOrder($product),
                'cover' => $cover !== '' ? $cover : null,
                'product_code' => $this->storedString($product['product_code'] ?? null),
                'product_type' => $this->storedString($product['product_type'] ?? null),
                'stocked_item' => $this->storedString($product['stocked_item'] ?? null),
                'supplier_name' => $this->storedString($product['supplier_name'] ?? null),
                'product_short_description' => $this->storedText($product['product_short_description'] ?? null),
                'product_description' => $this->storedText($product['product_description'] ?? null),
                'meta_keywords' => $this->storedText($product['meta_keywords'] ?? null),
                'datasheet' => $this->storedArray($product['datasheet'] ?? null),
                'product_images' => $this->storedArray($images),
                'product_dimension' => $this->storedArray($product['product_dimension'] ?? null),
                'technical_icons' => $this->storedArray($product['technical_icons'] ?? null),
                'categories' => $this->storedArray($product['categories'] ?? null),
                'category_path' => $this->storedArray($product['category_path'] ?? null),
                'category_paths' => $this->storedArray($product['category_paths'] ?? null),
                'sku_mappings' => $this->storedArray($product['sku_mappings'] ?? null),
                'product_features' => $this->storedArray($product['product_features'] ?? null),
                'options' => $this->storedArray($product['options'] ?? null),
                'constraints' => $this->storedArray($product['constraints'] ?? null),
            ]);
            $row->deleted_at = null;
            $row->deleted_by = null;
            $row->save();
        }
    }

    public function pruneMissingProducts(array $keepAirtableIds): void
    {
        $stale = Product::query();

        if ($keepAirtableIds !== []) {
            $stale->whereNotIn('airtable_id', $keepAirtableIds);
        }

        $stale->get()->each(fn (Product $product) => $product->delete());
    }

    public function persistLookups(array $categories, array $attributes): void
    {
        if ($categories !== []) {
            $ids = [];

            foreach ($categories as $record) {
                if (! is_array($record) || ! isset($record['id'])) {
                    continue;
                }

                $airtableId = (string) $record['id'];
                $ids[] = $airtableId;
                $fields = is_array($record['fields'] ?? null) ? $record['fields'] : [];
                $name = trim((string) ($fields['Name'] ?? $fields['Category_Name'] ?? $fields['Category Name'] ?? $fields['Title'] ?? $fields['Category'] ?? ''));
                $parents = $fields['Parent'] ?? $fields['Parent Category'] ?? $fields['Parent_Category'] ?? [];
                $parentId = is_array($parents) ? (string) ($parents[0] ?? '') : (string) $parents;

                $row = ProductCategory::withTrashed()->firstOrNew(['airtable_id' => $airtableId]);
                $row->fill([
                    'name' => $name !== '' ? mb_substr($name, 0, 191) : 'Category',
                    'parent_airtable_id' => $parentId !== '' ? $parentId : null,
                    'sort_order' => isset($fields['Order']) && is_numeric($fields['Order']) ? (int) $fields['Order'] : null,
                ]);
                $row->deleted_at = null;
                $row->deleted_by = null;
                $row->save();
            }

            if ($ids !== []) {
                ProductCategory::query()->whereNotIn('airtable_id', $ids)->get()->each(
                    fn (ProductCategory $category) => $category->delete(),
                );
            }
        }

        if ($attributes !== []) {
            $ids = [];

            foreach ($attributes as $record) {
                if (! is_array($record) || ! isset($record['id'])) {
                    continue;
                }

                $airtableId = (string) $record['id'];
                $ids[] = $airtableId;
                $fields = is_array($record['fields'] ?? null) ? $record['fields'] : [];
                $name = trim((string) ($fields['Attribute name'] ?? $fields['Attribute_Name'] ?? $fields['Attribute Name'] ?? $fields['Name'] ?? $fields['Attribute'] ?? ''));
                $value = $fields['Term Name'] ?? $fields['Attribute Value'] ?? $fields['Attribute_Value'] ?? $fields['Value'] ?? $fields['Option'] ?? $fields['Term Value'] ?? '';

                $row = ProductAttribute::withTrashed()->firstOrNew(['airtable_id' => $airtableId]);
                $row->fill([
                    'name' => $name !== '' ? mb_substr($name, 0, 191) : 'Attribute',
                    'value' => $value === null || $value === '' ? null : mb_substr((string) $value, 0, 191),
                    'icon' => $this->storedAssetPath(
                        $fields['Attribute Icon'] ?? $fields['Attribute_Icon'] ?? $fields['Attribute icon'] ?? $fields['Icon'] ?? null,
                    ),
                    'sort_order' => isset($fields['Order']) && is_numeric($fields['Order']) ? (int) $fields['Order'] : null,
                ]);
                $row->deleted_at = null;
                $row->deleted_by = null;
                $row->save();
            }

            if ($ids !== []) {
                ProductAttribute::query()->whereNotIn('airtable_id', $ids)->get()->each(
                    fn (ProductAttribute $attribute) => $attribute->delete(),
                );
            }
        }
    }

    public function compiled(): array
    {
        $products = Product::query()
            ->orderByRaw('sort_order is null')
            ->orderBy('sort_order')
            ->orderBy('product_name')
            ->get()
            ->map(fn (Product $product): array => $product->toStorefrontArray())
            ->all();

        $categories = ProductCategory::query()
            ->orderByRaw('sort_order is null')
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get()
            ->map(function (ProductCategory $category): array {
                $fields = [
                    'Name' => $category->name,
                ];

                if ($category->parent_airtable_id) {
                    $fields['Parent'] = [$category->parent_airtable_id];
                }

                if ($category->sort_order !== null) {
                    $fields['Order'] = $category->sort_order;
                }

                return [
                    'id' => $category->airtable_id,
                    'fields' => $fields,
                ];
            })
            ->all();

        return $this->normalizer->fromStored($products, $categories);
    }

    public function dashboardList(string $search = ''): LengthAwarePaginator
    {
        return Product::query()
            ->with('updater:id,name')
            ->when($search !== '', function ($query) use ($search): void {
                $query->where(function ($query) use ($search): void {
                    $query->where('product_name', 'like', '%'.$search.'%')
                        ->orWhere('product_code', 'like', '%'.$search.'%');
                });
            })
            ->orderByRaw('product_code is null')
            ->orderBy('product_code')
            ->orderBy('product_name')
            ->paginate(15)
            ->withQueryString();
    }

    public function latestSync(): ?ProductSync
    {
        return ProductSync::query()->latest('id')->first();
    }

    public function isSyncRunning(): bool
    {
        $this->failStaleRunningSyncs();

        return ProductSync::query()
            ->where('status', ProductSyncStatus::Running)
            ->exists();
    }

    public function startSync(string $triggeredBy): ProductSync
    {
        $sync = new ProductSync([
            'status' => ProductSyncStatus::Running,
            'products_count' => 0,
            'started_at' => now(),
            'triggered_by' => $triggeredBy,
            'log' => now()->format('H:i:s').' Sync started ('.$triggeredBy.').',
        ]);
        $sync->save();

        return $sync;
    }

    public function appendSyncLog(ProductSync $sync, string $line): void
    {
        $entry = now()->format('H:i:s').' '.$line;
        $sync->log = trim((string) $sync->log."\n".$entry);
        $sync->save();
    }

    public function finishSync(ProductSync $sync, bool $ok, int $productCount, ?string $error = null): void
    {
        $sync->fill([
            'status' => $ok ? ProductSyncStatus::Ok : ProductSyncStatus::Failed,
            'products_count' => $productCount,
            'finished_at' => now(),
            'error' => $error,
        ]);
        $sync->save();
    }

    public function failStaleRunningSyncs(): void
    {
        ProductSync::query()
            ->where('status', ProductSyncStatus::Running)
            ->where('started_at', '<', now()->subMinutes(25))
            ->get()
            ->each(function (ProductSync $sync): void {
                $this->appendSyncLog($sync, 'Marked failed after 25 minutes without finishing.');
                $this->finishSync($sync, false, (int) $sync->products_count, 'Sync timed out.');
            });
    }

    private function storedString(mixed $value, int $max = 191): ?string
    {
        if (is_array($value)) {
            foreach ($value as $item) {
                $string = $this->storedString($item, $max);

                if ($string !== null) {
                    return $string;
                }
            }

            return null;
        }

        if ($value === null || $value === '') {
            return null;
        }

        return mb_substr((string) $value, 0, $max);
    }

    /**
     * @param  array<string, mixed>  $product
     */
    private function storedOrder(array $product): ?int
    {
        foreach (['order', 'sort_order'] as $key) {
            if (! isset($product[$key]) || ! is_numeric($product[$key])) {
                continue;
            }

            $value = (float) $product[$key];

            if (is_finite($value)) {
                return (int) $value;
            }
        }

        return null;
    }

    private function storedText(mixed $value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        return (string) $value;
    }

    /**
     * @return array<int|string, mixed>|null
     */
    private function storedArray(mixed $value): ?array
    {
        if ($value === null || $value === '' || $value === []) {
            return null;
        }

        return is_array($value) ? $value : [$value];
    }

    private function storedAssetPath(mixed $value): ?string
    {
        if (is_string($value)) {
            $path = trim($value);

            if ($path === '') {
                return null;
            }

            if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
                return mb_substr($path, 0, 500);
            }

            $path = ltrim($path, '/');

            if (str_starts_with($path, 'assets/')) {
                return mb_substr($path, 0, 500);
            }

            return null;
        }

        if (! is_array($value)) {
            return null;
        }

        foreach ($value as $item) {
            $candidate = is_array($item) ? ($item['url'] ?? $item['icon'] ?? null) : $item;
            $path = $this->storedAssetPath($candidate);

            if ($path !== null) {
                return $path;
            }
        }

        return null;
    }
}
