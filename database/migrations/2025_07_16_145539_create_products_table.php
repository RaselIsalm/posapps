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
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('product_title');
            $table->string('product_code')->unique();
            $table->string('slug')->unique();
            $table->boolean('has_imei')->default(0);
            $table->integer('product_type')->default(1);
            $table->double('product_cost')->default(0);
            $table->double('selling_price');
            $table->double('wholesale_price')->nullable();
            $table->integer('category_id')->nullable();
            $table->integer('brand_id')->nullable();
            $table->enum('comission_type', ['fixed', 'percentage'])->default('fixed');
            $table->double('max_comission')->default(0)->nullable();
            $table->enum('tax_method', ['no_tax', 'inclusive', 'exclusive'])->default('no_tax');
            $table->double('tax')->default(0)->nullable();
            $table->integer('product_unit')->nullable();
            $table->integer('purchase_unit')->nullable();
            $table->integer('selling_unit')->nullable();
            $table->integer('quantity')->default(0)->nullable();
            $table->longText('description')->nullable();
            $table->boolean('is_variation')->default(false);
            $table->boolean('has_expiry_date')->default(false);
            $table->boolean('is_feature')->default(false);
            $table->double('promotional_price')->default(0)->nullable();
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->integer('limit')->default(0)->nullable();
            $table->boolean('is_online_seo')->default(false);
            $table->string('thumbnail')->nullable();
            $table->boolean('is_published')->default(true);
            $table->boolean('is_active')->default(true);
            $table->text('tags')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->unsignedBigInteger('deleted_by')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
