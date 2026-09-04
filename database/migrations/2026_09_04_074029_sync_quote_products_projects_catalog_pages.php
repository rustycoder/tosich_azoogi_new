<?php

use App\PageMeta\CatalogSync;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        CatalogSync::pages();
        CatalogSync::pruneUnknown();
    }

    public function down(): void
    {
        // Catalog pages are owned by later seeders and partner-page migrations.
    }
};
