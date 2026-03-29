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
        Schema::table('customers', function (Blueprint $table) {
            // استخدام SQL مباشر لضمان تفعيل auto_increment على الحقل id
            \Illuminate\Support\Facades\DB::statement('ALTER TABLE customers MODIFY id BIGINT UNSIGNED AUTO_INCREMENT');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('customers', function (Blueprint $table) {
             \Illuminate\Support\Facades\DB::statement('ALTER TABLE customers MODIFY id BIGINT UNSIGNED');
        });
    }
};
