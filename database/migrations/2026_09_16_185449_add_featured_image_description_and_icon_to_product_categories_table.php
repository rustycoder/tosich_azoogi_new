<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('product_categories', function (Blueprint $table): void {
            $table->text('description')->nullable()->after('name');
            $table->text('featured_image')->nullable()->after('description');
            $table->text('icon')->nullable()->after('featured_image');
        });
    }

    public function down(): void
    {
        Schema::table('product_categories', function (Blueprint $table): void {
            $table->dropColumn(['description', 'featured_image', 'icon']);
        });
    }
};
