<?php

namespace App\Services\Contracts;

use App\Models\User;

interface IProfileService
{
    /**
     * @param  array<string, mixed>  $data
     */
    public function update(User $user, array $data): void;
}
