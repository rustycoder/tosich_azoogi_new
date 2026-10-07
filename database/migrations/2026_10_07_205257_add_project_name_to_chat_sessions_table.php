<?php

declare(strict_types=1);

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
        Schema::table('chat_sessions', function (Blueprint $table): void {
            $table->string('project_name', 255)->nullable()->after('lead_company');
            $table->index('project_name');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('chat_sessions', function (Blueprint $table): void {
            $table->dropIndex(['project_name']);
            $table->dropColumn('project_name');
        });
    }
};
