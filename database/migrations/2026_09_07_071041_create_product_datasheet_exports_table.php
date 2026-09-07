<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('product_datasheet_exports', function (Blueprint $table) {
            $table->id();
            $table->string('uuid', 36)->unique();
            $table->unsignedBigInteger('product_id')->nullable();
            $table->string('airtable_id', 32);
            $table->string('product_code', 191)->nullable();
            $table->string('product_name', 191);
            $table->string('project_name', 191);
            $table->string('person_name', 191);
            $table->longText('configuration')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->string('user_agent', 191)->nullable();
            $table->timestamps();

            $table->index('product_id');
            $table->index('airtable_id');
            $table->index('product_code');
            $table->index('project_name');
            $table->index('person_name');
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_datasheet_exports');
    }
};
