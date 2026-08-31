<?php

namespace App\Repositories;

use App\Enums\ContentResource;
use App\Models\ContentPermission;
use App\Models\User;
use App\Repositories\Contracts\IContentPermissionRepository;

class ContentPermissionRepository implements IContentPermissionRepository
{
    public function syncForStaff(User $staff, array $resources): void
    {
        $existing = ContentPermission::query()
            ->withTrashed()
            ->where('user_id', $staff->id)
            ->get()
            ->keyBy(fn (ContentPermission $permission): string => $permission->resource->value);

        foreach (ContentResource::cases() as $resource) {
            $permission = $existing->get($resource->value);
            $shouldHave = in_array($resource->value, $resources, true);

            if ($shouldHave) {
                if ($permission?->trashed()) {
                    $permission->restore();
                    $permission->forceFill(['deleted_by' => null])->save();
                } elseif ($permission === null) {
                    ContentPermission::query()->create([
                        'user_id' => $staff->id,
                        'resource' => $resource,
                    ]);
                }
            } elseif ($permission && ! $permission->trashed()) {
                $permission->delete();
            }
        }
    }
}
