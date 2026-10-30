<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('product_attributes', function (Blueprint $table): void {
            $table->string('attribute_group', 100)->nullable()->after('is_visible_on_filters');
            $table->index('attribute_group');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('product_attributes', function (Blueprint $table): void {
            $table->dropIndex(['attribute_group']);
            $table->dropColumn('attribute_group');
        });
    }
};
