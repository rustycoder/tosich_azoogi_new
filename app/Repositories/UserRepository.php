<?php

namespace App\Repositories;

use App\Enums\UserType;
use App\Models\User;
use App\Repositories\Contracts\IUserRepository;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class UserRepository implements IUserRepository
{
    public function dashboardList(string $search = ''): LengthAwarePaginator
    {
        return User::query()
            ->with('updater:id,name')
            ->where('user_type', UserType::Staff)
            ->when($search !== '', function ($query) use ($search): void {
                $query->where('name', 'like', '%'.$search.'%');
            })
            ->orderBy('name')
            ->paginate(15)
            ->withQueryString();
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
