<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('projects', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique();
            $table->string('title');
            $table->string('tag')->nullable();
            $table->string('location')->nullable();
            $table->string('type')->nullable();
            $table->string('completed')->nullable();
            $table->boolean('featured')->default(false);
            $table->unsignedInteger('featured_order')->default(0);
            $table->string('cover')->nullable();
            $table->string('cover_remote')->nullable();
            $table->text('summary')->nullable();
            $table->text('description')->nullable();
            $table->json('gallery')->nullable();
            $table->string('status')->default('active');
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('deleted_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['featured', 'featured_order']);
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('projects');
    }
};
