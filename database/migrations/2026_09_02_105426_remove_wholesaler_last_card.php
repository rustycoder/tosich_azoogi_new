<?php

use App\Models\Page;
use App\Models\PageMeta;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        $page = Page::query()->where('slug', 'wholesaler')->first();

        if ($page === null) {
            return;
        }

        $orders = PageMeta::query()
            ->where('page_id', $page->id)
            ->where('key', 'card.heading')
            ->where('value', 'Off-Spec Solutions That Win the Job')
            ->pluck('sort_order');

        if ($orders->isEmpty()) {
            return;
        }

        PageMeta::query()
            ->where('page_id', $page->id)
            ->where('key', 'like', 'card.%')
            ->whereIn('sort_order', $orders)
            ->forceDelete();
    }
};
