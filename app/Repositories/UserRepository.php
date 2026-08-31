<?php

namespace App\Repositories;

use App\Enums\UserType;
use App\Models\User;
use App\Repositories\Contracts\IUserRepository;
use Illuminate\Support\Collection;

class UserRepository implements IUserRepository
{
    public function staffOrdered(): Collection
    {
        return User::query()
            ->with('updater:id,name')
            ->where('user_type', UserType::Staff)
            ->orderBy('name')
            ->get();
    }

    public function findStaffOrFail(int|string $id): User
    {
        return User::query()
            ->where('user_type', UserType::Staff)
            ->findOrFail($id);
    }

    public function create(array $data): User
    {
        return User::query()->create($data);
    }

    public function save(User $user): void
    {
        $user->save();
    }
}
