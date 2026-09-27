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
    'cover_alt',
    'summary',
    'description',
    'gallery',
    'gallery_alts',
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
            'gallery_alts' => 'array',
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
        return media_url($this->cover);
    }

    public function coverAlt(): string
    {
        if (is_string($this->cover_alt) && trim($this->cover_alt) !== '') {
            return trim($this->cover_alt);
        }

        $location = $this->location ? " in {$this->location}" : '';

        return "{$this->title}{$location} — architectural lighting project cover photo";
    }

    public function galleryAlt(string|int $imageOrIndex, int $fallbackIndex = 1): string
    {
        $custom = null;
        if (is_array($this->gallery_alts)) {
            if (is_string($imageOrIndex) && isset($this->gallery_alts[$imageOrIndex])) {
                $custom = $this->gallery_alts[$imageOrIndex];
            } elseif (is_numeric($imageOrIndex) && isset($this->gallery_alts[(int) $imageOrIndex])) {
                $custom = $this->gallery_alts[(int) $imageOrIndex];
            } elseif (is_string($imageOrIndex) && is_array($this->gallery)) {
                $foundIndex = array_search($imageOrIndex, $this->gallery, true);
                if ($foundIndex !== false && isset($this->gallery_alts[$foundIndex])) {
                    $custom = $this->gallery_alts[$foundIndex];
                }
            }
        }

        if (is_string($custom) && trim($custom) !== '') {
            return trim($custom);
        }

        $location = $this->location ? " in {$this->location}" : '';

        return "{$this->title}{$location} — architectural lighting gallery photo {$fallbackIndex}";
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
