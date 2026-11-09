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
        Schema::create('reward_point_settings', function (Blueprint $table) {
            $table->id();
            $table->boolean('is_active')->default(1);
            $table->decimal('min_purchase_amount', 10, 2)->default(1000);
            $table->decimal('point_earning_rate', 10, 2)->default(100);
            $table->integer('min_redeem_point')->default(100);
            $table->decimal('point_redemption_rate', 10, 2)->default(1);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reward_point_settings');
    }
};
