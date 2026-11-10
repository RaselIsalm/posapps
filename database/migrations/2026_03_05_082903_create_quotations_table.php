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
        Schema::create('quotations', function (Blueprint $table) {
            $table->id();
            $table->string('reference_no')->unique();
            $table->date('date')->nullable();
            $table->foreignId('customer_id')->nullable()->constrained('customers')->nullOnDelete();
            $table->foreignId('seller_id')->nullable()->constrained('employees')->nullOnDelete();
            $table->foreignId('branch_id')->nullable()->constrained('branches')->nullOnDelete();
            $table->foreignId('account_id')->nullable()->constrained('accounts')->nullOnDelete();
            $table->foreignId('supplier_id')->nullable()->constrained('suppliers')->nullOnDelete();
            $table->integer('item_count')->default(0);
            $table->decimal('total_qty', 10, 2)->default(0);
            $table->decimal('total_discount', 10, 2)->default(0);
            $table->decimal('total_tax', 10, 2)->default(0);
            $table->decimal('total_price', 10, 2)->default(0);
            $table->decimal('order_tax_rate', 10, 2)->default(0);
            $table->decimal('order_tax', 10, 2)->default(0);
            $table->decimal('order_discount', 10, 2)->default(0);
            $table->decimal('shipping_cost', 10, 2)->default(0);
            $table->decimal('grand_total', 10, 2)->default(0);
            $table->string('status')->default('pending');
            $table->string('document')->nullable();
            $table->string('note')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('quotations');
    }
};
