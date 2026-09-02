<?php

use App\Enums\Status;
use App\Models\Page;
use App\Models\PageMeta;
use App\PageMeta\Catalog;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        $definition = Catalog::for('silvair');
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

        $name = PageMeta::query()
            ->whereHas('page', fn ($query) => $query->where('slug', 'solutions'))
            ->where('key', 'eco.item.name')
            ->where('value', 'Silvair')
            ->first();

        if ($name !== null) {
            PageMeta::query()
                ->where('page_id', $name->page_id)
                ->where('key', 'eco.item.href')
                ->where('sort_order', $name->sort_order)
                ->update(['value' => '/silvair']);
        }
    }

    public function down(): void
    {
        $page = Page::query()->where('slug', 'silvair')->first();

        if ($page !== null) {
            PageMeta::query()->where('page_id', $page->id)->forceDelete();
            $page->forceDelete();
        }

        $name = PageMeta::query()
            ->whereHas('page', fn ($query) => $query->where('slug', 'solutions'))
            ->where('key', 'eco.item.name')
            ->where('value', 'Silvair')
            ->first();

        if ($name !== null) {
            PageMeta::query()
                ->where('page_id', $name->page_id)
                ->where('key', 'eco.item.href')
                ->where('sort_order', $name->sort_order)
                ->update(['value' => '#']);
        }
    }
};
