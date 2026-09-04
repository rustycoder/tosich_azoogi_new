<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('airtable_id', 32)->unique();
            $table->string('product_name', 191);
            $table->string('category', 191)->nullable();
            $table->string('status', 32)->nullable();
            $table->unsignedInteger('sort_order')->nullable();
            $table->string('cover', 500)->nullable();
            $table->string('product_code', 191)->nullable();
            $table->string('product_type', 191)->nullable();
            $table->string('stocked_item', 191)->nullable();
            $table->string('supplier_name', 191)->nullable();
            $table->text('product_short_description')->nullable();
            $table->longText('product_description')->nullable();
            $table->text('meta_keywords')->nullable();
            $table->longText('datasheet')->nullable();
            $table->longText('product_images')->nullable();
            $table->longText('product_dimension')->nullable();
            $table->longText('technical_icons')->nullable();
            $table->longText('categories')->nullable();
            $table->longText('category_path')->nullable();
            $table->longText('category_paths')->nullable();
            $table->longText('sku_mappings')->nullable();
            $table->longText('product_features')->nullable();
            $table->longText('options')->nullable();
            $table->longText('constraints')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->unsignedBigInteger('deleted_by')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index('product_name');
            $table->index('category');
            $table->index('status');
            $table->index('product_code');
        });

        Schema::create('product_categories', function (Blueprint $table) {
            $table->id();
            $table->string('airtable_id', 32)->unique();
            $table->string('name', 191);
            $table->string('parent_airtable_id', 32)->nullable();
            $table->unsignedInteger('sort_order')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->unsignedBigInteger('deleted_by')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index('name');
            $table->index('parent_airtable_id');
        });

        Schema::create('product_attributes', function (Blueprint $table) {
            $table->id();
            $table->string('airtable_id', 32)->unique();
            $table->string('name', 191);
            $table->string('value', 191)->nullable();
            $table->string('icon', 500)->nullable();
            $table->unsignedInteger('sort_order')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->unsignedBigInteger('deleted_by')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index('name');
        });

        Schema::create('product_syncs', function (Blueprint $table) {
            $table->id();
            $table->string('status', 32);
            $table->unsignedInteger('products_count')->default(0);
            $table->timestamp('started_at')->nullable();
            $table->timestamp('finished_at')->nullable();
            $table->longText('error')->nullable();
            $table->string('triggered_by', 32)->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamps();

            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_syncs');
        Schema::dropIfExists('product_attributes');
        Schema::dropIfExists('product_categories');
        Schema::dropIfExists('products');
    }
};
