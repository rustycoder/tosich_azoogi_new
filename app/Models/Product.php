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
    'category',
    'status',
    'sort_order',
    'cover',
    'product_code',
    'product_type',
    'stocked_item',
    'supplier_name',
    'product_short_description',
    'product_description',
    'meta_keywords',
    'datasheet',
    'product_images',
    'product_dimension',
    'technical_icons',
    'categories',
    'category_path',
    'category_paths',
    'sku_mappings',
    'product_features',
    'options',
    'constraints',
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
            'product_images' => 'array',
            'product_dimension' => 'array',
            'technical_icons' => 'array',
            'datasheet' => 'array',
            'categories' => 'array',
            'category_path' => 'array',
            'category_paths' => 'array',
            'sku_mappings' => 'array',
            'product_features' => 'array',
            'options' => 'array',
            'constraints' => 'array',
        ];
    }

    public function publicPath(): string
    {
        return '/product-detail?id='.rawurlencode($this->airtable_id);
    }

    public function coverUrl(): string
    {
        return media_url($this->cover);
    }

    /**
     * @return array<string, mixed>
     */
    public function toStorefrontArray(): array
    {
        $entry = [
            'id' => $this->airtable_id,
            'product_name' => $this->product_name,
            'order' => $this->sort_order,
            'category' => $this->category,
            'categories' => $this->categories,
            'category_path' => $this->category_path,
            'category_paths' => $this->category_paths,
            'product_code' => $this->product_code,
            'sku_mappings' => $this->sku_mappings,
            'product_short_description' => $this->product_short_description,
            'product_description' => $this->product_description,
            'product_images' => $this->product_images,
            'product_dimension' => $this->product_dimension,
            'stocked_item' => $this->stocked_item,
            'datasheet' => $this->datasheet,
            'technical_icons' => $this->technical_icons,
            'meta_keywords' => $this->meta_keywords,
            'supplier_name' => $this->supplier_name,
            'status' => $this->status,
            'product_type' => $this->product_type,
            'product_features' => $this->product_features,
            'options' => $this->options,
            'constraints' => $this->constraints,
        ];

        foreach ($entry as $key => $value) {
            if ($value === null || $value === '' || $value === []) {
                unset($entry[$key]);
            }
        }

        return $entry;
    }
}
