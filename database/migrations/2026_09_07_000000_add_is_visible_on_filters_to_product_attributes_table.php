<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('product_attributes', function (Blueprint $table): void {
            $table->boolean('is_visible_on_filters')->default(false)->after('sort_order');
            $table->index('is_visible_on_filters');
        });
    }

    public function down(): void
    {
        Schema::table('product_attributes', function (Blueprint $table): void {
            $table->dropIndex(['is_visible_on_filters']);
            $table->dropColumn('is_visible_on_filters');
        });
    }
};
