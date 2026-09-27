<?php

namespace Database\Seeders;

use App\Enums\Status;
use App\Models\Project;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;

class ProjectSeeder extends Seeder
{
    public function run(): void
    {
        $path = public_path('assets/data/projects.json');
        $payload = json_decode(File::get($path), true) ?? [];

        foreach ($payload['projects'] ?? [] as $item) {
            $attributes = [
                'title' => (string) $item['title'],
                'tag' => $item['tag'] ?? null,
                'location' => $item['location'] ?? null,
                'type' => $item['type'] ?? null,
                'completed' => $item['completed'] ?? null,
                'featured' => (bool) ($item['featured'] ?? false),
                'featured_order' => (int) ($item['featuredOrder'] ?? 0),
                'cover' => $item['cover'] ?? null,
                'summary' => $item['summary'] ?? null,
                'description' => $item['description'] ?? null,
                'gallery' => $item['gallery'] ?? [],
                'status' => Status::Active,
            ];

            $project = Project::query()->firstOrCreate(
                ['slug' => $item['slug']],
                $attributes,
            );

            if (! $project->wasRecentlyCreated && $this->shouldRestoreMedia($project, $item)) {
                $project->forceFill([
                    'cover' => $attributes['cover'],
                    'gallery' => $attributes['gallery'],
                ])->save();
            }
        }
    }

    /**
     * @param  array<string, mixed>  $item
     */
    private function shouldRestoreMedia(Project $project, array $item): bool
    {
        $seedCover = (string) ($item['cover'] ?? '');

        if ($seedCover === '' || str_starts_with($seedCover, 'http://') || str_starts_with($seedCover, 'https://')) {
            return false;
        }

        $cover = (string) ($project->cover ?? '');

        $hasRemoteGallery = false;
        if (is_array($project->gallery)) {
            foreach ($project->gallery as $img) {
                if (is_string($img) && (str_starts_with($img, 'http://') || str_starts_with($img, 'https://'))) {
                    $hasRemoteGallery = true;
                    break;
                }
            }
        }

        return $cover === ''
            || str_starts_with($cover, 'http://')
            || str_starts_with($cover, 'https://')
            || $hasRemoteGallery;
    }
}
