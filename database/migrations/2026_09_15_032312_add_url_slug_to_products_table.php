<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->string('slug', 191)->nullable()->after('product_name');
            $table->index('slug');
        });

        // Backfill slug for any existing products
        DB::table('products')
            ->whereNull('slug')
            ->orWhere('slug', '')
            ->get()
            ->each(function ($p) {
                $slug = Str::slug((string) $p->product_name);
                if ($slug === '') {
                    $slug = 'product-'.(string) $p->id;
                }
                DB::table('products')
                    ->where('id', $p->id)
                    ->update(['slug' => $slug]);
            });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropIndex(['slug']);
            $table->dropColumn('slug');
        });
    }
};
