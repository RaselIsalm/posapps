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
        Schema::create('product_imeis', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->nullable()->constrained('products')->nullOnDelete();
            $table->foreignId('variant_id')->nullable()->constrained('product_variants')->nullOnDelete();
            $table->foreignId('branch_id')->nullable()->constrained('branches')->nullOnDelete();
            $table->string('imei_number')->unique();
            $table->string('status')->default('in_stock')->index();
            $table->boolean('is_sold')->default(0);
            $table->string('condition')->default('new');
            $table->foreignId('purchase_id')->nullable()->constrained('purchases')->nullOnDelete();
            $table->foreignId('sale_id')->nullable()->constrained('sales')->nullOnDelete();
            $table->foreignId('return_id')->nullable();
            $table->foreignId('purchase_return_id')->nullable();
            $table->decimal('cost_price', 10, 2)->nullable();
            $table->decimal('selling_price', 10, 2)->nullable();
            $table->date('warranty_start_date')->nullable();
            $table->date('warranty_end_date')->nullable();
            $table->integer('warranty_days')->default(365);
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users');
            $table->foreignId('updated_by')->nullable()->constrained('users');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_imeis');
    }
};
