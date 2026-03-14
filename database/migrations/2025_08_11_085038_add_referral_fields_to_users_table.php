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
            // التحقق من وجود كل عمود قبل إضافته
            if (!Schema::hasColumn('users', 'referral_code')) {
                $table->string('referral_code')->unique()->nullable()->after('id');
            }
            if (!Schema::hasColumn('users', 'referred_by')) {
                $table->foreignId('referred_by')->nullable()->after('referral_code')->constrained('users')->onDelete('set null');
            }
            if (!Schema::hasColumn('users', 'referral_reward_claimed')) {
                $table->boolean('referral_reward_claimed')->default(false)->after('referred_by');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // التحقق من وجود كل عمود قبل حذفه
            if (Schema::hasColumn('users', 'referred_by')) {
                // يجب حذف المفتاح الخارجي أولاً
                $table->dropForeign(['referred_by']);
                $table->dropColumn('referred_by');
            }
            if (Schema::hasColumn('users', 'referral_code')) {
                $table->dropColumn('referral_code');
            }
            if (Schema::hasColumn('users', 'referral_reward_claimed')) {
                $table->dropColumn('referral_reward_claimed');
            }
        });
    }
};
