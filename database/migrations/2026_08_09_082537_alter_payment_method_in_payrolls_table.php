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
        if (DB::connection()->getDriverName() === 'sqlite') {
            return; // SQLite stores ENUM as TEXT and allows NULL in test databases.
        }
        Schema::table('payrolls', function (Blueprint $table) {
            DB::statement('ALTER TABLE payrolls MODIFY payment_method VARCHAR(255) DEFAULT NULL;');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (DB::connection()->getDriverName() === 'sqlite') {
            return;
        }
        Schema::table('payrolls', function (Blueprint $table) {
            // Can't reliably restore enum values, so just leave it or revert to a generic string
            DB::statement("ALTER TABLE payrolls MODIFY payment_method ENUM('bank', 'cash', 'mobile_banking', 'other') DEFAULT 'cash';");
        });
    }
};
