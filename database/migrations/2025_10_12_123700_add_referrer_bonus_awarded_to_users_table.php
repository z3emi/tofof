<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // هذا الحقل يتأكد من أن الداعي حصل على مكافأته مرة واحدة فقط
            $table->boolean('referrer_bonus_awarded')->default(false)->after('referral_reward_claimed');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('referrer_bonus_awarded');
        });
    }
};