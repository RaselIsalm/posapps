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
        Schema::create('accounts', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('account_number');
            $table->double('initial_balance')->default(0);
            $table->double('current_balance')->default(0);
            $table->string('note')->nullable();
            $table->enum('type', ['bank', 'cash', 'mobile banking', 'digital wallet', 'other'])->default('bank');
            $table->boolean('is_active')->default(1);
            $table->boolean('is_default')->default(0);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('accounts');
    }
};
