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
        Schema::create('payrolls', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->nullable()->constrained('employees')->nullOnDelete();
            $table->string('salary_month', 20);
            $table->year('salary_year');
            $table->decimal('basic_salary', 10, 2)->default(0);
            $table->decimal('total_allowance', 10, 2)->default(0);
            $table->decimal('total_deduction', 10, 2)->default(0);
            $table->decimal('net_salary', 10, 2)->default(0);
            $table->enum('payment_method', ['bank', 'cash', 'mobile_banking', 'other'])->default('cash');
            $table->string('transaction_id')->nullable();
            $table->date('payment_date')->nullable();
            $table->enum('status', ['pending', 'paid', 'cancelled'])->default('pending');
            $table->string('note')->nullable();
            $table->timestamps();
            $table->unique(['employee_id', 'salary_month', 'salary_year']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payrolls');
    }
};
