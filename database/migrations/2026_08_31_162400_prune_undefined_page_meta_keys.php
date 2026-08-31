<?php

use App\Models\Page;
use App\Models\PageMeta;
use App\PageMeta\Catalog;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        foreach (Catalog::all() as $definition) {
            $page = Page::query()->where('slug', $definition->slug())->first();

            if ($page === null) {
                continue;
            }

            $allowed = collect($definition->fields())->pluck('key')->all();

            if ($allowed === []) {
                continue;
            }

            PageMeta::query()
                ->where('page_id', $page->id)
                ->whereNotIn('key', $allowed)
                ->forceDelete();
        }
    }

    public function down(): void
    {
        // Orphaned keys cannot be restored.
    }
};
