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

    public function dashboardList(
        string $search = '',
        ?string $status = null,
        ?string $featured = null,
        int $perPage = 15
    ): LengthAwarePaginator {
        return $this->projects->dashboardList($search, $status, $featured, $perPage);
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
            $galleryAlts = [];
            foreach ($galleryFiles as $file) {
                $gallery[] = $this->storage->storeProjectUpload($project->slug, 'gallery', $file);
                $galleryAlts[] = '';
            }
            $project->gallery = $gallery;
            $project->gallery_alts = $galleryAlts;
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
        ?array $keepGallery = null,
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
        $existingAlts = is_array($project->gallery_alts) ? $project->gallery_alts : [];
        $inputAlts = isset($data['gallery_alts']) && is_array($data['gallery_alts']) ? $data['gallery_alts'] : [];

        if ($keepGallery !== null) {
            $keepLookup = [];
            foreach ($keepGallery as $index) {
                $keepLookup[(int) $index] = true;
            }

            $kept = [];
            $keptAlts = [];
            foreach ($gallery as $index => $path) {
                if (isset($keepLookup[$index])) {
                    $kept[] = $path;
                    $altVal = $inputAlts[$index] ?? ($existingAlts[$index] ?? ($existingAlts[$path] ?? ''));
                    $keptAlts[] = is_string($altVal) ? $altVal : '';

                    continue;
                }

                if (is_string($path) && $path !== '') {
                    $this->storage->deleteManaged($this->normalizedGalleryPath($path));
                }
            }

            $gallery = $kept;
            $galleryAlts = $keptAlts;
        } else {
            $galleryAlts = array_values($inputAlts ?: $existingAlts);
        }

        foreach ($galleryFiles as $file) {
            $gallery[] = $this->storage->storeProjectUpload($project->slug, 'gallery', $file);
            $galleryAlts[] = '';
        }

        $project->gallery = array_values($gallery);
        $project->gallery_alts = array_values($galleryAlts);
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
