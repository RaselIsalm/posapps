<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sms_setting_params', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sms_setting_id')
                ->constrained('sms_settings')
                ->onDelete('cascade');
            $table->string('param_key');
            $table->text('param_value')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sms_setting_params');
    }
};
