<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('payrolls', function (Blueprint $table) {
            $table->string('payment_method')->nullable()->change();
        });
    }

    public function down(): void
    {
        // Account IDs are stored in this field, so restoring its original enum
        // would make existing payroll records invalid.
    }
};
