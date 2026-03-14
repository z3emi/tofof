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
            // We can add all the address fields after the 'email' column
            $table->string('governorate')->nullable()->after('email');
            $table->string('city')->nullable()->after('governorate');
            $table->text('address_details')->nullable()->after('city');
        });
    }
};
