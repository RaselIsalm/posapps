<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('branches', function (Blueprint $table) {
            $table->string('bin_number')->nullable()->after('phone');
            $table->string('telephone')->nullable()->after('bin_number');
            $table->string('website')->nullable()->after('email');
        });
    }

    public function down(): void
    {
        Schema::table('branches', function (Blueprint $table) {
            $table->dropColumn(['bin_number', 'telephone', 'website']);
        });
    }
};
