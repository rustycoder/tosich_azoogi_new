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
        Schema::table('products', function (Blueprint $table) {
            $table->longText('datasheet_file')->nullable()->after('datasheet');
            $table->longText('installation_guide_file')->nullable()->after('datasheet_file');
            $table->longText('user_manual')->nullable()->after('installation_guide_file');
            $table->longText('ies_file')->nullable()->after('user_manual');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn([
                'datasheet_file',
                'installation_guide_file',
                'user_manual',
                'ies_file',
            ]);
        });
    }
};
