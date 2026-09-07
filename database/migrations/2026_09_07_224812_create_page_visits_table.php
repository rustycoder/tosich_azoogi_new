<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('page_visits', function (Blueprint $table) {
            $table->id();
            $table->string('kind', 16);
            $table->string('page_slug', 191)->nullable();
            $table->string('airtable_id', 32)->nullable();
            $table->string('country', 2)->nullable();
            $table->timestamp('created_at')->nullable();

            $table->index(['kind', 'page_slug']);
            $table->index(['kind', 'airtable_id']);
            $table->index('country');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('page_visits');
    }
};
