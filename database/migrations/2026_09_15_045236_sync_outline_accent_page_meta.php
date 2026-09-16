<?php

use App\PageMeta\CatalogSync;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        CatalogSync::pages();
    }

    public function down(): void
    {
        // Outline accent keys stay until CatalogSync prunes them from the definition.
    }
};
