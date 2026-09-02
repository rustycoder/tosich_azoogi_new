<?php

namespace Database\Seeders;

use App\PageMeta\CatalogSync;
use Illuminate\Database\Seeder;

class PageSeeder extends Seeder
{
    public function run(): void
    {
        CatalogSync::pages();
        CatalogSync::pruneUnknown();
    }
}
