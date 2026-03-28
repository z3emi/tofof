<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Fix roles table
        try {
            DB::statement("ALTER TABLE `roles` MODIFY `id` BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT");
        } catch (\Exception $e) {
            // Fallback if the above fails or if using different driver
            Schema::table('roles', function (Blueprint $table) {
                $table->bigIncrements('id')->change();
            });
        }

        // Fix permissions table
        try {
            DB::statement("ALTER TABLE `permissions` MODIFY `id` BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT");
        } catch (\Exception $e) {
            Schema::table('permissions', function (Blueprint $table) {
                $table->bigIncrements('id')->change();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No need to reverse auto-increment fix
    }
};
