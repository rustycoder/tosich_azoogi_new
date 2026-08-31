<?php

namespace App\Models;

use App\Enums\ContentResource;
use App\Enums\Status;
use App\Models\Concerns\Auditable;
use Database\Factories\PageFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable(['slug', 'title', 'meta_description', 'status', 'created_by', 'updated_by', 'deleted_by'])]
class Page extends Model
{
    /** @use HasFactory<PageFactory> */
    use Auditable, HasFactory, SoftDeletes;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'status' => Status::class,
        ];
    }

    public function meta(): HasMany
    {
        return $this->hasMany(PageMeta::class)->orderBy('key')->orderBy('sort_order');
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', Status::Active);
    }

    public function isActive(): bool
    {
        return $this->status === Status::Active;
    }

    public function publicPath(): string
    {
        return match ($this->slug) {
            'home' => '/',
            default => '/'.$this->slug,
        };
    }

    public function resource(): ContentResource
    {
        return ContentResource::from($this->slug);
    }
}
