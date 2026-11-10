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
        Schema::create('deliveries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sale_id')->nullable()->constrained('sales')->nullOnDelete();
            $table->foreignId('courier_id')->constrained('couriers');
            $table->string('tracking_code')->nullable();
            $table->string('invoice_no')->nullable();
            $table->string('recipient_name');
            $table->string('recipient_phone');
            $table->text('address');
            $table->string('area_name')->nullable();
            $table->decimal('order_amount', 10, 2);
            $table->decimal('cod_amount', 10, 2)->default(0);
            $table->decimal('delivery_charge', 8, 2)->default(0);
            $table->string('status')->default('pending');
            $table->text('note')->nullable();
            $table->json('courier_response')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('deliveries');
    }
};
