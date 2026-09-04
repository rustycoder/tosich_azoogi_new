<?php

namespace App\Services\Contracts;

use App\Enums\ContentResource;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface IStaffService
{
    /**
     * @return LengthAwarePaginator<int, User>
     */
    public function all(string $search = ''): LengthAwarePaginator;

    /**
     * @param  array<string, mixed>  $data
     * @param  list<string>  $resources
     */
    public function create(array $data, array $resources = []): User;

    /**
     * @return array{staff: User, resources: list<ContentResource>, assigned: list<string>}
     */
    public function editorData(User $staff): array;

    /**
     * @param  array<string, mixed>  $data
     * @param  list<string>  $resources
     */
    public function update(User $staff, array $data, array $resources = []): void;

    public function toggleStatus(User $staff): User;
}
