<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('page_meta', function (Blueprint $table) {
            $table->string('font_size', 16)->nullable()->after('value');
            $table->string('text_align', 16)->nullable()->after('font_size');
        });
    }

    public function down(): void
    {
        Schema::table('page_meta', function (Blueprint $table) {
            $table->dropColumn(['font_size', 'text_align']);
        });
    }
};
