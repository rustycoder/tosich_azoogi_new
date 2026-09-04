<?php

namespace App\Repositories\Contracts;

use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface IUserRepository
{
    /**
     * @return LengthAwarePaginator<int, User>
     */
    public function dashboardList(string $search = ''): LengthAwarePaginator;

    public function findStaffOrFail(int|string $id): User;

    /**
     * @param  array<string, mixed>  $data
     */
    public function create(array $data): User;

    public function save(User $user): void;
}
