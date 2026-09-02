<?php

use App\Models\Page;
use App\Models\PageMeta;
use App\PageMeta\Catalog;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        $definition = Catalog::for('madrix');
        $page = Page::query()->where('slug', $definition->slug())->first();

        if ($page === null) {
            return;
        }

        $allowed = collect($definition->fields())->pluck('key')->all();

        foreach ($definition->seed() as $row) {
            if (! in_array($row['key'], $allowed, true)) {
                continue;
            }

            if (! in_array($row['key'], ['software.image', 'hardware.row.image'], true)) {
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
    }

    public function down(): void
    {
        $page = Page::query()->where('slug', 'madrix')->first();

        if ($page === null) {
            return;
        }

        PageMeta::query()
            ->where('page_id', $page->id)
            ->whereIn('key', ['software.image', 'hardware.row.image'])
            ->forceDelete();
    }
};
