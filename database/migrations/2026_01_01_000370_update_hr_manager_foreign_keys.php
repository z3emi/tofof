<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'manager_id')) {
                $table->dropForeign(['manager_id']);
                $table->foreign('manager_id')->references('id')->on('managers')->nullOnDelete();
            }
        });

        if (Schema::hasTable('hr_leave_requests')) {
            Schema::table('hr_leave_requests', function (Blueprint $table) {
                if (Schema::hasColumn('hr_leave_requests', 'manager_id')) {
                    $table->dropForeign(['manager_id']);
                    $table->foreign('manager_id')->references('id')->on('managers')->nullOnDelete();
                }

                if (Schema::hasColumn('hr_leave_requests', 'reviewed_by')) {
                    $table->dropForeign(['reviewed_by']);
                    $table->foreign('reviewed_by')->references('id')->on('managers')->nullOnDelete();
                }
            });
        }

        if (Schema::hasTable('hr_advance_requests')) {
            Schema::table('hr_advance_requests', function (Blueprint $table) {
                if (Schema::hasColumn('hr_advance_requests', 'manager_id')) {
                    $table->dropForeign(['manager_id']);
                    $table->foreign('manager_id')->references('id')->on('managers')->nullOnDelete();
                }

                if (Schema::hasColumn('hr_advance_requests', 'reviewed_by')) {
                    $table->dropForeign(['reviewed_by']);
                    $table->foreign('reviewed_by')->references('id')->on('managers')->nullOnDelete();
                }
            });
        }
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'manager_id')) {
                $table->dropForeign(['manager_id']);
                $table->foreign('manager_id')->references('id')->on('users')->nullOnDelete();
            }
        });

        if (Schema::hasTable('hr_leave_requests')) {
            Schema::table('hr_leave_requests', function (Blueprint $table) {
                if (Schema::hasColumn('hr_leave_requests', 'manager_id')) {
                    $table->dropForeign(['manager_id']);
                    $table->foreign('manager_id')->references('id')->on('users')->nullOnDelete();
                }

                if (Schema::hasColumn('hr_leave_requests', 'reviewed_by')) {
                    $table->dropForeign(['reviewed_by']);
                    $table->foreign('reviewed_by')->references('id')->on('users')->nullOnDelete();
                }
            });
        }

        if (Schema::hasTable('hr_advance_requests')) {
            Schema::table('hr_advance_requests', function (Blueprint $table) {
                if (Schema::hasColumn('hr_advance_requests', 'manager_id')) {
                    $table->dropForeign(['manager_id']);
                    $table->foreign('manager_id')->references('id')->on('users')->nullOnDelete();
                }

                if (Schema::hasColumn('hr_advance_requests', 'reviewed_by')) {
                    $table->dropForeign(['reviewed_by']);
                    $table->foreign('reviewed_by')->references('id')->on('users')->nullOnDelete();
                }
            });
        }
    }
};
