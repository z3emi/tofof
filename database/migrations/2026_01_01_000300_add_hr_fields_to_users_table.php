<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'base_salary')) {
                $table->decimal('base_salary', 12, 2)->default(0)->after('wallet_balance');
            }

            if (!Schema::hasColumn('users', 'allowances')) {
                $table->decimal('allowances', 12, 2)->default(0)->after('base_salary');
            }

            if (!Schema::hasColumn('users', 'commission_rate')) {
                $table->decimal('commission_rate', 5, 4)->default(0)->after('allowances');
            }

            if (!Schema::hasColumn('users', 'bank_account_details')) {
                $table->text('bank_account_details')->nullable()->after('commission_rate');
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'bank_account_details')) {
                $table->dropColumn('bank_account_details');
            }

            if (Schema::hasColumn('users', 'commission_rate')) {
                $table->dropColumn('commission_rate');
            }

            if (Schema::hasColumn('users', 'allowances')) {
                $table->dropColumn('allowances');
            }

            if (Schema::hasColumn('users', 'base_salary')) {
                $table->dropColumn('base_salary');
            }
        });
    }
};
