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
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'whatsapp_otp')) {
                $table->string('whatsapp_otp')->nullable()->after('phone_verified_at');
            }
            if (!Schema::hasColumn('users', 'whatsapp_otp_expires_at')) {
                $table->timestamp('whatsapp_otp_expires_at')->nullable()->after('whatsapp_otp');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'whatsapp_otp')) {
                $table->dropColumn('whatsapp_otp');
            }
            if (Schema::hasColumn('users', 'whatsapp_otp_expires_at')) {
                $table->dropColumn('whatsapp_otp_expires_at');
            }
        });
    }
};
