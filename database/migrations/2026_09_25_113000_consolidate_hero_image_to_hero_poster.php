<?php

use App\Models\PageMeta;
use App\PageMeta\CatalogSync;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        // Copy any existing hero.image values to hero.poster if hero.poster is missing/empty
        $imageRows = PageMeta::query()
            ->where('key', 'hero.image')
            ->where('sort_order', 0)
            ->get();

        foreach ($imageRows as $imageRow) {
            if (blank($imageRow->value)) {
                continue;
            }

            $posterRow = PageMeta::query()
                ->where('page_id', $imageRow->page_id)
                ->where('key', 'hero.poster')
                ->where('sort_order', 0)
                ->first();

            if ($posterRow) {
                if (blank($posterRow->value)) {
                    $posterRow->value = $imageRow->value;
                    $posterRow->save();
                }
            } else {
                PageMeta::query()->create([
                    'page_id' => $imageRow->page_id,
                    'key' => 'hero.poster',
                    'sort_order' => 0,
                    'value' => $imageRow->value,
                ]);
            }
        }

        // Sync catalog pages (prunes hero.image and ensures hero.poster & hero.video exist)
        CatalogSync::pages();
    }

    public function down(): void
    {
        // Definitions handle state
    }
};
