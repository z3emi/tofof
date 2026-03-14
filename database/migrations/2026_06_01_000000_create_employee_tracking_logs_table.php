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
        if (Schema::hasTable('employee_tracking_logs')) {
            return;
        }

        $employeeTable = config('tracking.employee_table', 'managers');

        Schema::create('employee_tracking_logs', function (Blueprint $table) use ($employeeTable) {
            $table->id();
            $table->unsignedBigInteger('employee_id');
            $table->decimal('gps_lat', 10, 6)->nullable();
            $table->decimal('gps_long', 10, 6)->nullable();
            $table->string('address')->nullable();
            $table->enum('action', ['checkin', 'checkout', 'move'])->default('move');
            $table->decimal('speed', 6, 2)->nullable();
            $table->decimal('battery_level', 5, 2)->nullable();
            $table->string('device_id', 50)->nullable();
            $table->timestamp('recorded_at')->useCurrent();

            $table->foreign('employee_id')
                ->references('id')
                ->on($employeeTable)
                ->cascadeOnDelete();
            $table->index(['employee_id', 'recorded_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('employee_tracking_logs');
    }
};
