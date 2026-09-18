<?php

namespace App\Services;

use App\Enums\Status;
use App\Models\Project;
use App\Repositories\Contracts\IProjectRepository;
use App\Services\Contracts\IProjectService;
use App\Support\ContentStorage;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;

class ProjectService implements IProjectService
{
    public function __construct(
        private IProjectRepository $projects,
        private ContentStorage $storage,
    ) {}

    public function dashboardList(string $search = ''): LengthAwarePaginator
    {
        return $this->projects->dashboardList($search);
    }

    public function publicListing(): array
    {
        return [
            'projects' => $this->projects->activeOrdered(),
        ];
    }

    public function publicDetail(string $slug): Project
    {
        return $this->projects->findActiveBySlug($slug);
    }

    public function create(array $data, ?UploadedFile $cover = null, array $galleryFiles = []): Project
    {
        $galleryFiles = $this->uploadedFiles($galleryFiles);
        $data['slug'] = ($data['slug'] ?? '') !== '' ? $data['slug'] : Str::slug($data['title']);
        unset($data['featured'], $data['featured_order'], $data['status']);
        $data['featured'] = false;
        $data['featured_order'] = $this->projects->nextFeaturedOrder();
        $data['status'] = Status::Active;
        $data['gallery'] = [];

        $project = $this->projects->create($data);

        if ($cover) {
            $project->cover = $this->storage->storeProjectUpload($project->slug, 'cover', $cover);
        }

        if ($galleryFiles !== []) {
            $gallery = [];
            foreach ($galleryFiles as $file) {
                $gallery[] = $this->storage->storeProjectUpload($project->slug, 'gallery', $file);
            }
            $project->gallery = $gallery;
        }

        if ($cover || $galleryFiles !== []) {
            $this->projects->save($project);
        }

        return $project;
    }

    public function update(
        Project $project,
        array $data,
        ?UploadedFile $cover = null,
        array $galleryFiles = [],
        array $removeGallery = [],
    ): void {
        $galleryFiles = $this->uploadedFiles($galleryFiles);
        unset($data['featured'], $data['featured_order'], $data['status']);
        $project->fill($data);

        if ($cover) {
            $project->cover = $this->storage->storeProjectUpload(
                $project->slug,
                'cover',
                $cover,
                $project->cover,
            );
        }

        $gallery = is_array($project->gallery) ? $project->gallery : [];
        $removeLookup = $this->galleryRemoveLookup($removeGallery);

        if ($removeLookup !== []) {
            foreach ($removeLookup as $path) {
                $this->storage->deleteManaged($path);
            }

            $gallery = array_values(array_filter(
                $gallery,
                function (mixed $path) use ($removeLookup): bool {
                    if (! is_string($path) || $path === '') {
                        return false;
                    }

                    return ! isset($removeLookup[$this->normalizedGalleryPath($path)]);
                },
            ));
        }

        foreach ($galleryFiles as $file) {
            $gallery[] = $this->storage->storeProjectUpload($project->slug, 'gallery', $file);
        }

        $project->gallery = $gallery;
        $this->projects->save($project);
    }

    public function delete(Project $project): void
    {
        $this->projects->delete($project);
    }

    public function toggleStatus(Project $project): Project
    {
        $project->status = $project->status->toggle();
        $this->projects->save($project);

        return $project;
    }

    public function toggleFeatured(Project $project): Project
    {
        $project->featured = ! $project->featured;
        $this->projects->save($project);

        return $project;
    }

    /**
     * @param  list<int|string>  $ids
     */
    public function reorder(array $ids): void
    {
        $this->projects->reorder($ids);
    }

    /**
     * @param  UploadedFile|list<UploadedFile>|array<int, mixed>  $files
     * @return list<UploadedFile>
     */
    private function uploadedFiles(UploadedFile|array $files): array
    {
        if ($files instanceof UploadedFile) {
            return [$files];
        }

        return array_values(array_filter(
            $files,
            fn (mixed $file): bool => $file instanceof UploadedFile,
        ));
    }

    /**
     * @param  list<mixed>  $paths
     * @return array<string, string>
     */
    private function galleryRemoveLookup(array $paths): array
    {
        $lookup = [];

        foreach ($paths as $path) {
            if (! is_string($path)) {
                continue;
            }

            $normalized = $this->normalizedGalleryPath($path);

            if ($normalized === '') {
                continue;
            }

            $lookup[$normalized] = $normalized;
        }

        return $lookup;
    }

    private function normalizedGalleryPath(string $path): string
    {
        $path = trim(rawurldecode($path));
        $path = str_replace('\\', '/', $path);

        if ($path === '') {
            return '';
        }

        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
            return $path;
        }

        return '/'.ltrim($path, '/');
    }
}
