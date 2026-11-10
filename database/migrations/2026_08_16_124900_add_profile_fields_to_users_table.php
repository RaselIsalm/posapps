<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (! Schema::hasColumn('users', 'designation')) {
                $table->string('designation')->nullable()->after('phone');
            }
            if (! Schema::hasColumn('users', 'avatar')) {
                $table->string('avatar')->nullable()->after('designation');
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'avatar')) {
                $table->dropColumn('avatar');
            }
            if (Schema::hasColumn('users', 'designation')) {
                $table->dropColumn('designation');
            }
        });
    }
};
