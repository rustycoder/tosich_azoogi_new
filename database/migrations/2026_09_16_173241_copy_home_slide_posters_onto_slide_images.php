<?php

use App\Models\Page;
use App\Models\PageMeta;
use App\PageMeta\CatalogSync;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        $home = Page::query()->where('slug', 'home')->first();

        if ($home !== null) {
            $posters = PageMeta::query()
                ->where('page_id', $home->id)
                ->where('key', 'slide.media.poster')
                ->get();

            foreach ($posters as $poster) {
                $image = PageMeta::query()
                    ->where('page_id', $home->id)
                    ->where('key', 'slide.media.image')
                    ->where('sort_order', $poster->sort_order)
                    ->first();

                if ($image === null || trim((string) $image->value) !== '' || trim((string) $poster->value) === '') {
                    continue;
                }

                $image->value = $poster->value;
                $image->save();
            }
        }

        CatalogSync::pages();
    }

    public function down(): void
    {
        // Slide posters are removed from the home definition.
    }
};
