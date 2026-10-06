<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('chat_sessions', function (Blueprint $table): void {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->string('ip_address', 45)->nullable();
            $table->string('country', 100)->nullable();
            $table->text('user_agent')->nullable();
            $table->string('referrer_url', 500)->nullable();
            $table->string('lead_name')->nullable();
            $table->string('lead_email')->nullable();
            $table->string('lead_phone', 50)->nullable();
            $table->string('lead_company')->nullable();
            $table->foreignId('enquiry_id')->nullable()->constrained('enquiries')->nullOnDelete();
            $table->unsignedInteger('messages_count')->default(0);
            $table->string('status', 30)->default('active'); // active, completed, abandoned
            $table->text('summary')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['status', 'created_at']);
            $table->index('lead_email');
        });

        Schema::create('chat_messages', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('chat_session_id')->constrained('chat_sessions')->cascadeOnDelete();
            $table->string('sender', 20); // user, assistant, system
            $table->longText('content');
            $table->json('tool_calls')->nullable();
            $table->json('tool_results')->nullable();
            $table->json('cards_payload')->nullable();
            $table->unsignedInteger('tokens_used')->nullable();
            $table->timestamps();

            $table->index(['chat_session_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('chat_messages');
        Schema::dropIfExists('chat_sessions');
    }
};
