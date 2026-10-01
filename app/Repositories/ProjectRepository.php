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

    public function dashboardList(
        string $search = '',
        ?string $status = null,
        ?string $featured = null,
        int $perPage = 15
    ): LengthAwarePaginator {
        return Project::query()
            ->with('updater:id,name')
            ->when($search !== '', function ($query) use ($search): void {
                $query->where(function ($q) use ($search): void {
                    $q->where('title', 'like', '%'.$search.'%')
                        ->orWhere('tag', 'like', '%'.$search.'%')
                        ->orWhere('location', 'like', '%'.$search.'%')
                        ->orWhere('type', 'like', '%'.$search.'%')
                        ->orWhere('summary', 'like', '%'.$search.'%');
                });
            })
            ->when($status !== null, function ($query) use ($status): void {
                $query->where('status', $status);
            })
            ->when($featured !== null, function ($query) use ($featured): void {
                if ($featured === 'featured' || $featured === '1') {
                    $query->where('featured', true);
                } elseif ($featured === 'not_featured' || $featured === '0') {
                    $query->where('featured', false);
                }
            })
            ->orderBy('featured_order')
            ->orderBy('title')
            ->paginate($perPage)
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
