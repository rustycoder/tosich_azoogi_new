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
            Project::query()->firstOrCreate(
                ['slug' => $item['slug']],
                [
                    'title' => (string) $item['title'],
                    'tag' => $item['tag'] ?? null,
                    'location' => $item['location'] ?? null,
                    'type' => $item['type'] ?? null,
                    'completed' => $item['completed'] ?? null,
                    'featured' => (bool) ($item['featured'] ?? false),
                    'featured_order' => (int) ($item['featuredOrder'] ?? 0),
                    'cover' => $item['cover'] ?? null,
                    'cover_remote' => $item['coverRemote'] ?? null,
                    'summary' => $item['summary'] ?? null,
                    'description' => $item['description'] ?? null,
                    'gallery' => $item['gallery'] ?? [],
                    'status' => Status::Active,
                ],
            );
        }
    }
}
