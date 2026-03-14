<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('hr_attendance_records')) {
            return;
        }

        Schema::table('hr_attendance_records', function (Blueprint $table) {
            if (! Schema::hasColumn('hr_attendance_records', 'check_in_latitude')) {
                $table->decimal('check_in_latitude', 10, 7)->nullable()->after('check_in_at');
            }

            if (! Schema::hasColumn('hr_attendance_records', 'check_in_longitude')) {
                $table->decimal('check_in_longitude', 10, 7)->nullable()->after('check_in_latitude');
            }

            if (! Schema::hasColumn('hr_attendance_records', 'check_out_latitude')) {
                $table->decimal('check_out_latitude', 10, 7)->nullable()->after('check_out_at');
            }

            if (! Schema::hasColumn('hr_attendance_records', 'check_out_longitude')) {
                $table->decimal('check_out_longitude', 10, 7)->nullable()->after('check_out_latitude');
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('hr_attendance_records')) {
            return;
        }

        Schema::table('hr_attendance_records', function (Blueprint $table) {
            foreach (['check_in_latitude', 'check_in_longitude', 'check_out_latitude', 'check_out_longitude'] as $column) {
                if (Schema::hasColumn('hr_attendance_records', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
