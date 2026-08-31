<?php

namespace Database\Seeders;

use App\Enums\Status;
use App\Models\Page;
use App\Models\PageMeta;
use App\PageMeta\Catalog;
use Illuminate\Database\Seeder;

class PageSeeder extends Seeder
{
    public function run(): void
    {
        foreach (Catalog::all() as $definition) {
            $page = Page::query()->firstOrCreate(
                ['slug' => $definition->slug()],
                [
                    'title' => $definition->title(),
                    'meta_description' => $definition->metaDescription(),
                    'status' => Status::Active,
                ],
            );

            $allowed = collect($definition->fields())->pluck('key')->all();

            foreach ($definition->seed() as $row) {
                if (! in_array($row['key'], $allowed, true)) {
                    continue;
                }

                PageMeta::query()->firstOrCreate(
                    [
                        'page_id' => $page->id,
                        'key' => $row['key'],
                        'sort_order' => $row['sort_order'],
                    ],
                    ['value' => $row['value']],
                );
            }

            if ($allowed !== []) {
                PageMeta::query()
                    ->where('page_id', $page->id)
                    ->whereNotIn('key', $allowed)
                    ->forceDelete();
            }
        }

        $slugs = Catalog::slugs();

        Page::query()
            ->whereNotIn('slug', $slugs)
            ->get()
            ->each(function (Page $page): void {
                PageMeta::query()->where('page_id', $page->id)->forceDelete();
                $page->forceDelete();
            });
    }
}
