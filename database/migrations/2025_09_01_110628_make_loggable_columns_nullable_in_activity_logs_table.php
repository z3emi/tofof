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
        Schema::table('activity_logs', function (Blueprint $table) {
            // This is the correct code to MODIFY existing columns to accept NULL
            $table->unsignedBigInteger('user_id')->nullable()->change();
            $table->unsignedBigInteger('loggable_id')->nullable()->change();
            $table->string('loggable_type')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('activity_logs', function (Blueprint $table) {
            // This reverses the changes
            $table->unsignedBigInteger('user_id')->nullable(false)->change();
            $table->unsignedBigInteger('loggable_id')->nullable(false)->change();
            $table->string('loggable_type')->nullable(false)->change();
        });
    }
};