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
        Schema::create('product_combo_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('product_id')->comment('The Combo Product ID');
            $table->unsignedBigInteger('combo_item_id')->comment('The individual Product ID in the combo');
            $table->unsignedBigInteger('variant_id')->nullable()->comment('Variant ID if applicable');
            $table->double('quantity')->default(1);
            $table->double('unit_price')->default(0);
            $table->double('total_price')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_combo_items');
    }
};
