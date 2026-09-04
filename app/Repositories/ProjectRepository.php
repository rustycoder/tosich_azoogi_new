<?php

namespace App\Repositories;

use App\Models\Project;
use App\Repositories\Contracts\IProjectRepository;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class ProjectRepository implements IProjectRepository
{
    public function allOrdered(): Collection
    {
        return Project::query()->with('updater:id,name')->orderBy('featured_order')->orderBy('title')->get();
    }

    public function dashboardList(string $search = ''): LengthAwarePaginator
    {
        return Project::query()
            ->with('updater:id,name')
            ->when($search !== '', function ($query) use ($search): void {
                $query->where('title', 'like', '%'.$search.'%');
            })
            ->orderBy('featured_order')
            ->orderBy('title')
            ->paginate(15)
            ->withQueryString();
    }

    public function activeOrdered(): Collection
    {
        return Project::query()->active()->orderBy('title')->get();
    }

    public function activeFeatured(?int $limit = null): Collection
    {
        $query = Project::query()->active()->featured();

        if ($limit !== null) {
            $query->take($limit);
        }

        return $query->get();
    }

    public function findActiveBySlug(string $slug): Project
    {
        return Project::query()->active()->where('slug', $slug)->firstOrFail();
    }

    public function create(array $data): Project
    {
        return Project::query()->create($data);
    }

    public function save(Project $project): void
    {
        $project->save();
    }

    public function delete(Project $project): void
    {
        $project->delete();
    }

    public function nextFeaturedOrder(): int
    {
        return (int) Project::query()->max('featured_order') + 1;
    }

    /**
     * @param  list<int|string>  $ids
     */
    public function reorder(array $ids): void
    {
        DB::transaction(function () use ($ids): void {
            foreach (array_values($ids) as $index => $id) {
                Project::query()->whereKey($id)->update([
                    'featured_order' => $index + 1,
                ]);
            }
        });
    }
}
