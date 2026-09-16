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
        // Path images stay removed from the about definition.
    }
};
