<?php

namespace App\Repositories\Contracts;

use App\Models\User;

interface IContentPermissionRepository
{
    /**
     * @param  list<string>  $resources
     */
    public function syncForStaff(User $staff, array $resources): void;
}
