<?php

namespace App\Models;

use App\Enums\Status;
use App\Models\Concerns\Auditable;
use Database\Factories\ProjectFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable([
    'slug',
    'title',
    'tag',
    'location',
    'type',
    'completed',
    'featured',
    'featured_order',
    'cover',
    'cover_remote',
    'summary',
    'description',
    'gallery',
    'status',
    'created_by',
    'updated_by',
    'deleted_by',
])]
class Project extends Model
{
    /** @use HasFactory<ProjectFactory> */
    use Auditable, HasFactory, SoftDeletes;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'featured' => 'boolean',
            'featured_order' => 'integer',
            'gallery' => 'array',
            'status' => Status::class,
        ];
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', Status::Active);
    }

    public function scopeFeatured(Builder $query): Builder
    {
        return $query->where('featured', true)->orderBy('featured_order');
    }

    public function coverUrl(): string
    {
        $path = $this->cover ?: $this->cover_remote;

        return media_url($path);
    }

    public function isActive(): bool
    {
        return $this->status === Status::Active;
    }

    public function publicPath(): string
    {
        return '/project-detail?slug='.$this->slug;
    }
}
