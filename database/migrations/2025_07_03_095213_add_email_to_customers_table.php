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
        Schema::table('customers', function (Blueprint $table) {
            // Add the new 'email' column.
            // It should be nullable because it's an optional field.
            // We can place it after the 'phone_number' column for organization.
            $table->string('email')->nullable()->after('phone_number');
        });
    }
};
