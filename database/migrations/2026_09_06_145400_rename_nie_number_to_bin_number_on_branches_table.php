<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('branches', 'nie_number') && ! Schema::hasColumn('branches', 'bin_number')) {
            if (DB::connection()->getDriverName() === 'sqlite') {
                Schema::table('branches', fn ($table) => $table->renameColumn('nie_number', 'bin_number'));
            } else {
                DB::statement('ALTER TABLE `branches` CHANGE `nie_number` `bin_number` VARCHAR(255) NULL');
            }
        }

        if (Schema::hasTable('general_settings')) {
            DB::table('general_settings')
                ->where('key_name', 'thermal_show_nie')
                ->update(['key_name' => 'thermal_show_bin']);

            DB::table('general_settings')
                ->where('key_name', 'company_nie_number')
                ->update(['key_name' => 'company_bin_number']);
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('branches', 'bin_number') && ! Schema::hasColumn('branches', 'nie_number')) {
            if (DB::connection()->getDriverName() === 'sqlite') {
                Schema::table('branches', fn ($table) => $table->renameColumn('bin_number', 'nie_number'));
            } else {
                DB::statement('ALTER TABLE `branches` CHANGE `bin_number` `nie_number` VARCHAR(255) NULL');
            }
        }

        if (Schema::hasTable('general_settings')) {
            DB::table('general_settings')
                ->where('key_name', 'thermal_show_bin')
                ->update(['key_name' => 'thermal_show_nie']);

            DB::table('general_settings')
                ->where('key_name', 'company_bin_number')
                ->update(['key_name' => 'company_nie_number']);
        }
    }
};
