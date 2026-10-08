<?php

namespace App\Models;

use App\Models\Concerns\Auditable;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable([
    'airtable_id',
    'name',
    'status',
    'description',
    'featured_image',
    'icon',
    'parent_airtable_id',
    'sort_order',
    'created_by',
    'updated_by',
    'deleted_by',
])]
class ProductCategory extends Model
{
    use Auditable, SoftDeletes;

    /**
     * Scope query to only include published categories.
     *
     * @param  Builder<ProductCategory>  $query
     */
    public function scopePublished(Builder $query): void
    {
        $query->where(function (Builder $q): void {
            $q->whereNull('status')
                ->orWhere('status', '')
                ->orWhereRaw('LOWER(status) = ?', ['publish']);
        });
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'sort_order' => 'integer',
        ];
    }

    public function featuredImageUrl(): ?string
    {
        if (filled($this->featured_image)) {
            return media_url((string) $this->featured_image);
        }

        return null;
    }

    public function iconUrl(): ?string
    {
        if (filled($this->icon)) {
            return media_url((string) $this->icon);
        }

        return null;
    }
}
