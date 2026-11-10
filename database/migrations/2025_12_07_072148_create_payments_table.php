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
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sale_id')->nullable()->constrained('sales')->nullOnDelete();
            $table->decimal('amount', 10, 2);
            $table->decimal('due_amount', 10, 2)->default(0);
            $table->decimal('change_amount', 10, 2)->default(0);
            $table->string('payment_method');
            $table->string('transaction_id')->nullable();
            $table->text('note')->nullable();
            $table->date('payment_date');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
