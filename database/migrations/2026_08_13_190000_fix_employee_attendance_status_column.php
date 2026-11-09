<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('employee_attendance')) {
            return;
        }

        // Normalize legacy string statuses to 0/1 (app expects tinyint/boolean)
        DB::table('employee_attendance')->whereIn('status', ['present', 'Present', '1'])->update(['status' => 1]);
        DB::table('employee_attendance')->whereIn('status', ['absent', 'Absent', '0'])->update(['status' => 0]);
        DB::statement("UPDATE employee_attendance SET status = 1 WHERE status NOT IN ('0', '1', 0, 1)");

        if (DB::connection()->getDriverName() === 'sqlite') {
            Schema::table('employee_attendance', function (Blueprint $table) {
                $table->unique(['employee_id', 'attendance_date'], 'employee_attendance_employee_date_unique');
            });

            return;
        }

        // Change column to boolean/tinyint
        DB::statement('ALTER TABLE employee_attendance MODIFY status TINYINT(1) NOT NULL DEFAULT 1');

        $indexExists = collect(DB::select("SHOW INDEX FROM employee_attendance WHERE Key_name = 'employee_attendance_employee_date_unique'"))->isNotEmpty();
        if (! $indexExists) {
            // Remove duplicate rows keeping the lowest id before unique index
            DB::statement('
                DELETE a FROM employee_attendance a
                INNER JOIN employee_attendance b
                    ON a.employee_id = b.employee_id
                    AND a.attendance_date = b.attendance_date
                    AND a.id > b.id
            ');

            Schema::table('employee_attendance', function (Blueprint $table) {
                $table->unique(['employee_id', 'attendance_date'], 'employee_attendance_employee_date_unique');
            });
        }
    }

    public function down(): void
    {
        if (! Schema::hasTable('employee_attendance')) {
            return;
        }

        if (DB::connection()->getDriverName() === 'sqlite') {
            Schema::table('employee_attendance', function (Blueprint $table) {
                $table->dropUnique('employee_attendance_employee_date_unique');
            });

            return;
        }

        $indexExists = collect(DB::select("SHOW INDEX FROM employee_attendance WHERE Key_name = 'employee_attendance_employee_date_unique'"))->isNotEmpty();
        if ($indexExists) {
            Schema::table('employee_attendance', function (Blueprint $table) {
                $table->dropUnique('employee_attendance_employee_date_unique');
            });
        }

        DB::statement("ALTER TABLE employee_attendance MODIFY status VARCHAR(255) NOT NULL DEFAULT 'present'");
    }
};
