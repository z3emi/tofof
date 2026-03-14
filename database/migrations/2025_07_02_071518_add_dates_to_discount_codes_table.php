<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('discount_codes', function (Blueprint $table) {
            // Add start_date and end_date columns after the 'value' column
            // Make them nullable so old codes without dates don't cause errors
            $table->date('start_date')->nullable()->after('value');
            $table->date('end_date')->nullable()->after('start_date');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('discount_codes', function (Blueprint $table) {
            // This allows you to reverse the migration if needed
            $table->dropColumn('start_date');
            $table->dropColumn('end_date');
        });
    }
};