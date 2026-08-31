<?php

namespace App\Services;

use App\Models\User;
use App\Repositories\Contracts\IUserRepository;
use App\Services\Contracts\IProfileService;

class ProfileService implements IProfileService
{
    public function __construct(private IUserRepository $users) {}

    public function update(User $user, array $data): void
    {
        $user->fill($data);
        $this->users->save($user);
    }
}
