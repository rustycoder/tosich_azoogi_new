<?php

namespace App\Repositories\Contracts;

use App\Models\User;
use Illuminate\Support\Collection;

interface IUserRepository
{
    /**
     * @return Collection<int, User>
     */
    public function staffOrdered(): Collection;

    public function findStaffOrFail(int|string $id): User;

    /**
     * @param  array<string, mixed>  $data
     */
    public function create(array $data): User;

    public function save(User $user): void;
}
