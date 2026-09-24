<?php

use App\Models\PageMeta;
use App\PageMeta\CatalogSync;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Convert existing accent values to inline {} syntax in parent text
        $accentRows = PageMeta::query()
            ->where('key', 'like', '%_accent')
            ->get();

        foreach ($accentRows as $accentRow) {
            $parentKey = preg_replace('/_accent$/', '', $accentRow->key);
            $accentValue = trim((string) $accentRow->value);

            if ($accentValue === '' || ! is_string($parentKey)) {
                continue;
            }

            $parentRow = PageMeta::query()
                ->where('page_id', $accentRow->page_id)
                ->where('key', $parentKey)
                ->where('sort_order', $accentRow->sort_order)
                ->first();

            if ($parentRow && filled($parentRow->value)) {
                $text = (string) $parentRow->value;

                if (! str_contains($text, '{'.$accentValue.'}') && str_contains($text, $accentValue)) {
                    $position = strpos($text, $accentValue);
                    if ($position !== false) {
                        $parentRow->value = substr($text, 0, $position)
                            .'{'.$accentValue.'}'
                            .substr($text, $position + strlen($accentValue));
                        $parentRow->save();
                    }
                }
            }
        }

        // 2. Sync definitions (adds hero.image and prunes removed accent / kicker fields)
        CatalogSync::pages();
    }

    public function down(): void
    {
        // Definitions handle state
    }
};
