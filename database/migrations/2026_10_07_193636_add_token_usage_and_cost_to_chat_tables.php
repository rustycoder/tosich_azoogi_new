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
        Schema::table('chat_messages', function (Blueprint $table) {
            $table->unsignedInteger('prompt_tokens')->default(0)->after('tokens_used');
            $table->unsignedInteger('completion_tokens')->default(0)->after('prompt_tokens');
            $table->string('model', 150)->nullable()->after('completion_tokens');
            $table->string('driver', 100)->nullable()->after('model');
            $table->decimal('estimated_cost', 10, 6)->default(0)->after('driver');
        });

        Schema::table('chat_sessions', function (Blueprint $table) {
            $table->unsignedInteger('total_tokens')->default(0)->after('messages_count');
            $table->decimal('total_cost', 10, 6)->default(0)->after('total_tokens');
            $table->string('primary_model', 150)->nullable()->after('total_cost');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('chat_messages', function (Blueprint $table) {
            $table->dropColumn(['prompt_tokens', 'completion_tokens', 'model', 'driver', 'estimated_cost']);
        });

        Schema::table('chat_sessions', function (Blueprint $table) {
            $table->dropColumn(['total_tokens', 'total_cost', 'primary_model']);
        });
    }
};
