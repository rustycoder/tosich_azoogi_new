<?php

namespace App\Models;

use App\Enums\ContentResource;
use App\Enums\Status;
use App\Enums\UserType;
use App\Models\Concerns\Auditable;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

#[Fillable(['name', 'email', 'password', 'user_type', 'status', 'created_by', 'updated_by', 'deleted_by'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use Auditable, HasFactory, Notifiable, SoftDeletes;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'user_type' => UserType::class,
            'status' => Status::class,
        ];
    }

    public function contentPermissions(): HasMany
    {
        return $this->hasMany(ContentPermission::class);
    }

    public function isActive(): bool
    {
        return $this->status === Status::Active;
    }

    public function isAdmin(): bool
    {
        return $this->user_type === UserType::Admin;
    }

    public function isStaff(): bool
    {
        return $this->user_type === UserType::Staff;
    }

    public function isCustomer(): bool
    {
        return $this->user_type === UserType::Customer;
    }

    public function isTrader(): bool
    {
        return $this->user_type === UserType::Trader;
    }

    public function canManage(string $resource): bool
    {
        if ($this->isAdmin()) {
            return true;
        }

        if (! $this->isStaff()) {
            return false;
        }

        return $this->contentPermissions()
            ->where('resource', $resource)
            ->exists();
    }

    /**
     * @return list<string>
     */
    public function managedResources(): array
    {
        if ($this->isAdmin()) {
            return array_map(
                fn (ContentResource $resource): string => $resource->value,
                ContentResource::cases(),
            );
        }

        if (! $this->isStaff()) {
            return [];
        }

        return $this->contentPermissions()
            ->pluck('resource')
            ->map(fn (ContentResource|string $resource): string => $resource instanceof ContentResource ? $resource->value : $resource)
            ->values()
            ->all();
    }

    /**
     * @return list<string>
     */
    public function managedPageSlugs(): array
    {
        return array_values(array_filter(
            $this->managedResources(),
            fn (string $resource): bool => ContentResource::tryFrom($resource)?->isPage() ?? false,
        ));
    }

    public function canManagePages(): bool
    {
        return $this->managedPageSlugs() !== [];
    }

    /**
     * @return list<string>
     */
    public function managedSectionSlugs(): array
    {
        return array_values(array_filter(
            $this->managedResources(),
            fn (string $resource): bool => ContentResource::tryFrom($resource)?->isSection() ?? false,
        ));
    }

    public function canManageSections(): bool
    {
        return $this->managedSectionSlugs() !== [];
    }

    public function canManageProjects(): bool
    {
        return $this->canManage(ContentResource::Projects->value);
    }
}
