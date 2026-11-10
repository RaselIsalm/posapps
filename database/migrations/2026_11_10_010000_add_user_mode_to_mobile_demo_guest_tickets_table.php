<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('mobile_demo_guest_tickets', function (Blueprint $table) {
            $table->string('mode', 16)->default('demo')->after('token_hash');
            $table->foreignId('user_id')->nullable()->after('mode')->constrained()->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('mobile_demo_guest_tickets', function (Blueprint $table) {
            $table->dropConstrainedForeignId('user_id');
            $table->dropColumn('mode');
        });
    }
};
