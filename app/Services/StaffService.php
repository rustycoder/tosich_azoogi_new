<?php

namespace App\Services;

use App\Enums\ContentResource;
use App\Enums\Status;
use App\Enums\UserType;
use App\Models\ContentPermission;
use App\Models\User;
use App\Repositories\Contracts\IContentPermissionRepository;
use App\Repositories\Contracts\IUserRepository;
use App\Services\Contracts\IStaffService;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class StaffService implements IStaffService
{
    public function __construct(
        private IUserRepository $users,
        private IContentPermissionRepository $permissions,
    ) {}

    public function all(string $search = ''): LengthAwarePaginator
    {
        return $this->users->dashboardList($search);
    }

    public function create(array $data, array $resources = []): User
    {
        unset($data['status']);

        $staff = $this->users->create([
            ...$data,
            'user_type' => UserType::Staff,
            'status' => Status::Active,
            'email_verified_at' => now(),
        ]);

        $this->permissions->syncForStaff($staff, $resources);

        return $staff;
    }

    public function editorData(User $staff): array
    {
        $this->assertStaff($staff);

        $staff->load('contentPermissions');

        return [
            'staff' => $staff,
            'resources' => ContentResource::staffGroups(),
            'assigned' => $staff->contentPermissions
                ->map(fn (ContentPermission $permission): string => $permission->resource->value)
                ->all(),
        ];
    }

    public function update(User $staff, array $data, array $resources = []): void
    {
        $this->assertStaff($staff);

        unset($data['status']);
        $staff->fill($data);
        $this->users->save($staff);
        $this->permissions->syncForStaff($staff, $resources);
    }

    public function toggleStatus(User $staff): User
    {
        $this->assertStaff($staff);

        $staff->status = $staff->status->toggle();
        $this->users->save($staff);

        return $staff;
    }

    private function assertStaff(User $staff): void
    {
        if (! $staff->isStaff()) {
            throw new NotFoundHttpException;
        }
    }
}
