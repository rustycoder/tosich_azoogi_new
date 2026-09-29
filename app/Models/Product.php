<?php

namespace App\Models;

use App\Models\Concerns\Auditable;
use Database\Factories\ProductFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable([
    'airtable_id',
    'product_name',
    'slug',
    'category',
    'status',
    'sort_order',
    'cover',
    'product_code',
    'product_type',
    'stocked_item',
    'supplier_name',
    'meta_title',
    'meta_description',
    'product_description',
    'meta_keywords',
    'datasheet',
    'datasheet_file',
    'installation_guide_file',
    'user_manual',
    'ies_file',
    'product_images',
    'product_dimension',
    'technical_icons',
    'categories',
    'category_path',
    'category_paths',
    'sku_mappings',
    'product_features',
    'options',
    'dimming_control',
    'created_by',
    'updated_by',
    'deleted_by',
])]
class Product extends Model
{
    /** @use HasFactory<ProductFactory> */
    use Auditable, HasFactory, SoftDeletes;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'sort_order' => 'integer',
            'dimming_control' => 'boolean',
            'product_images' => 'array',
            'product_dimension' => 'array',
            'technical_icons' => 'array',
            'datasheet' => 'array',
            'datasheet_file' => 'array',
            'installation_guide_file' => 'array',
            'user_manual' => 'array',
            'ies_file' => 'array',
            'categories' => 'array',
            'category_path' => 'array',
            'category_paths' => 'array',
            'sku_mappings' => 'array',
            'product_features' => 'array',
            'options' => 'array',
        ];
    }

    public function publicPath(): string
    {
        if (! empty($this->slug)) {
            $encoded = implode('/', array_map('rawurlencode', explode('/', (string) $this->slug)));

            return '/products/'.ltrim($encoded, '/');
        }

        return '/product-detail?id='.rawurlencode((string) $this->airtable_id);
    }

    public function coverUrl(?int $index = null): string
    {
        if ($index !== null && is_array($this->product_images) && isset($this->product_images[$index])) {
            $val = $this->product_images[$index];
            $url = is_string($val) ? $val : (is_array($val) ? ($val['url'] ?? '') : '');
            if ($url !== '') {
                return media_url((string) $url);
            }
        }

        $path = $this->cover;

        if (($path === null || $path === '') && is_array($this->product_images) && $this->product_images !== []) {
            $first = $this->product_images[0];
            $path = is_string($first) ? $first : (is_array($first) ? ($first['url'] ?? '') : '');
        }

        return media_url((string) $path);
    }

    public function dimensionUrl(): ?string
    {
        $url = $this->firstAssetUrl($this->product_dimension);

        return $url !== null ? media_url($url) : null;
    }

    public function datasheetUrl(): ?string
    {
        $url = $this->firstAssetUrl($this->datasheet_file)
            ?? $this->firstAssetUrl($this->datasheet);

        return $url !== null ? media_url($url) : null;
    }

    public function manualUrl(): ?string
    {
        $url = $this->firstAssetUrl($this->user_manual);

        return $url !== null ? media_url($url) : null;
    }

    public function guideUrl(): ?string
    {
        $url = $this->firstAssetUrl($this->installation_guide_file);

        return $url !== null ? media_url($url) : null;
    }

    public function iesUrl(): ?string
    {
        $url = $this->firstAssetUrl($this->ies_file);

        return $url !== null ? media_url($url) : null;
    }

    public function firstAssetUrl(mixed $value): ?string
    {
        if ($value === null || $value === '' || $value === []) {
            return null;
        }

        if (is_string($value)) {
            $trimmed = trim($value);

            return $trimmed !== '' ? $trimmed : null;
        }

        if (is_array($value)) {
            if (isset($value['url']) && is_string($value['url']) && trim($value['url']) !== '') {
                return trim($value['url']);
            }

            foreach ($value as $item) {
                $found = $this->firstAssetUrl($item);
                if ($found !== null) {
                    return $found;
                }
            }
        }

        if (is_object($value)) {
            return $this->firstAssetUrl((array) $value);
        }

        return null;
    }

    public function isPublished(): bool
    {
        $status = strtolower(trim((string) ($this->status ?? 'publish')));

        return $status === '' || $status === 'publish';
    }

    public function isVisibleOnStorefront(bool $allowPreviewForAuth = true): bool
    {
        if (! app()->isProduction()) {
            return true;
        }

        if ($allowPreviewForAuth && auth()->check()) {
            return true;
        }

        return $this->isPublished();
    }

    /**
     * @return array{id: string, sku: string, name: string, image: string, url: string}
     */
    public function quoteSummary(): array
    {
        return [
            'id' => (string) $this->airtable_id,
            'sku' => trim((string) ($this->product_code ?? '')),
            'name' => (string) $this->product_name,
            'image' => $this->coverUrl(),
            'url' => $this->publicPath(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function toStorefrontArray(): array
    {
        $entry = [
            'id' => $this->airtable_id,
            'product_name' => $this->product_name,
            'slug' => $this->slug,
            'order' => $this->sort_order,
            'category' => $this->category,
            'categories' => $this->categories,
            'category_path' => $this->category_path,
            'category_paths' => $this->category_paths,
            'product_code' => $this->product_code,
            'sku_mappings' => $this->sku_mappings,
            'product_description' => $this->product_description,
            'product_images' => $this->product_images,
            'product_dimension' => $this->product_dimension,
            'stocked_item' => $this->stocked_item,
            'datasheet' => $this->datasheet,
            'datasheet_file' => $this->datasheet_file,
            'installation_guide_file' => $this->installation_guide_file,
            'user_manual' => $this->user_manual,
            'ies_file' => $this->ies_file,
            'technical_icons' => $this->technical_icons,
            'meta_keywords' => $this->meta_keywords,
            'meta_title' => $this->meta_title,
            'meta_description' => $this->meta_description,
            'status' => $this->status,
            'product_type' => $this->product_type,
            'product_features' => $this->product_features,
            'options' => $this->options,
            'dimming_control' => (bool) $this->dimming_control,
        ];

        foreach ($entry as $key => $value) {
            if ($value === null || $value === '' || $value === []) {
                unset($entry[$key]);
            }
        }

        return $entry;
    }
}
