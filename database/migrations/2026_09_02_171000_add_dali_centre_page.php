<?php

use App\Models\Page;
use App\Models\PageMeta;
use App\PageMeta\CatalogSync;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        CatalogSync::pages();

        $name = PageMeta::query()
            ->whereHas('page', fn ($query) => $query->where('slug', 'solutions'))
            ->where('key', 'eco.item.name')
            ->where('value', 'DALI Center')
            ->first();

        if ($name !== null) {
            PageMeta::query()
                ->where('page_id', $name->page_id)
                ->where('key', 'eco.item.href')
                ->where('sort_order', $name->sort_order)
                ->update(['value' => '/dali-centre']);
        }
    }

    public function down(): void
    {
        $page = Page::query()->where('slug', 'dali-centre')->first();

        if ($page !== null) {
            PageMeta::query()->where('page_id', $page->id)->forceDelete();
            $page->forceDelete();
        }

        $name = PageMeta::query()
            ->whereHas('page', fn ($query) => $query->where('slug', 'solutions'))
            ->where('key', 'eco.item.name')
            ->where('value', 'DALI Center')
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
