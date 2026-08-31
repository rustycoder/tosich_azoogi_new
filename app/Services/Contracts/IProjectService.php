<?php

namespace App\Services\Contracts;

use App\Models\Project;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;

interface IProjectService
{
    /**
     * @return Collection<int, Project>
     */
    public function dashboardList(): Collection;

    /**
     * @return array{highlights: Collection<int, Project>, projects: Collection<int, Project>}
     */
    public function publicListing(): array;

    public function publicDetail(string $slug): Project;

    /**
     * @param  array<string, mixed>  $data
     * @param  list<UploadedFile>  $galleryFiles
     */
    public function create(array $data, ?UploadedFile $cover = null, array $galleryFiles = []): Project;

    /**
     * @param  array<string, mixed>  $data
     * @param  list<UploadedFile>  $galleryFiles
     * @param  list<string>  $removeGallery
     */
    public function update(
        Project $project,
        array $data,
        ?UploadedFile $cover = null,
        array $galleryFiles = [],
        array $removeGallery = [],
    ): void;

    public function delete(Project $project): void;

    public function toggleStatus(Project $project): Project;

    public function toggleFeatured(Project $project): Project;

    /**
     * @param  list<int|string>  $ids
     */
    public function reorder(array $ids): void;
}
