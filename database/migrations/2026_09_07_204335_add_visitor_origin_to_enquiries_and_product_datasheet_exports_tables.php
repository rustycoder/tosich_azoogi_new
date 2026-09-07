<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('enquiries', function (Blueprint $table) {
            $table->string('ip_address', 45)->nullable()->after('payload');
            $table->string('country', 2)->nullable()->after('ip_address');
        });

        Schema::table('product_datasheet_exports', function (Blueprint $table) {
            $table->string('country', 2)->nullable()->after('ip_address');
        });
    }

    public function down(): void
    {
        Schema::table('enquiries', function (Blueprint $table) {
            $table->dropColumn(['ip_address', 'country']);
        });

        Schema::table('product_datasheet_exports', function (Blueprint $table) {
            $table->dropColumn('country');
        });
    }
};
