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
        Schema::table('users', function (Blueprint $table) {
            // Add the new 'avatar' column.
            // It should be nullable so existing users don't cause an error.
            // We place it after the 'email' column for organization.
            $table->string('avatar')->nullable()->after('email');
        });
    }
};
