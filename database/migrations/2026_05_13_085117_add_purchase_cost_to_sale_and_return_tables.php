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
        Schema::table('product_sales', function (Blueprint $table) {
            $table->decimal('purchase_unit_cost', 10, 2)->default(0)->after('unit_price');
        });

        Schema::table('sale_return_details', function (Blueprint $table) {
            $table->decimal('purchase_unit_cost', 10, 2)->default(0)->after('unit_price');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('product_sales', function (Blueprint $table) {
            $table->dropColumn('purchase_unit_cost');
        });

        Schema::table('sale_return_details', function (Blueprint $table) {
            $table->dropColumn('purchase_unit_cost');
        });
    }
};
