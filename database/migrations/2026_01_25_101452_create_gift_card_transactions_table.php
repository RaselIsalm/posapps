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
        Schema::create('gift_card_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('gift_card_id')->constrained('gift_cards')->onDelete('cascade');
            $table->foreignId('sale_id')->nullable()->constrained('sales')->onDelete('set null');
            $table->decimal('amount', 12, 2);
            $table->string('transaction_type')->default('debit');
            $table->date('transaction_date');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('gift_card_transactions');
    }
};
