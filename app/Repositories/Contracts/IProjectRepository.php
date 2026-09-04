<?php

namespace App\Repositories\Contracts;

use App\Models\Project;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

interface IProjectRepository
{
    /**
     * @return Collection<int, Project>
     */
    public function allOrdered(): Collection;

    /**
     * @return LengthAwarePaginator<int, Project>
     */
    public function dashboardList(string $search = ''): LengthAwarePaginator;

    /**
     * @return Collection<int, Project>
     */
    public function activeOrdered(): Collection;

    /**
     * @return Collection<int, Project>
     */
    public function activeFeatured(?int $limit = null): Collection;

    public function findActiveBySlug(string $slug): Project;

    /**
     * @param  array<string, mixed>  $data
     */
    public function create(array $data): Project;

    public function save(Project $project): void;

    public function delete(Project $project): void;

    public function nextFeaturedOrder(): int;

    /**
     * @param  list<int|string>  $ids
     */
    public function reorder(array $ids): void;
}
