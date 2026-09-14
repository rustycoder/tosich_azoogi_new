<?php

use App\Models\Page;
use App\Models\PageMeta;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        $page = Page::query()->where('slug', 'footer')->first();

        if ($page === null) {
            return;
        }

        $orders = PageMeta::query()
            ->where('page_id', $page->id)
            ->where('key', 'footer.company.link.href')
            ->where('value', '/privacy')
            ->pluck('sort_order');

        if ($orders->isEmpty()) {
            return;
        }

        PageMeta::query()
            ->where('page_id', $page->id)
            ->where('key', 'like', 'footer.company.link.%')
            ->whereIn('sort_order', $orders)
            ->forceDelete();
    }
};
