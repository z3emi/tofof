<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (! Schema::hasTable('users')) {
            return;
        }

        Schema::table('users', function (Blueprint $table) {
            if (! Schema::hasColumn('users', 'wallet_balance')) {
                $table->decimal('wallet_balance', 12, 2)->default(0)->after('password');
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('users') || ! Schema::hasColumn('users', 'wallet_balance')) {
            return;
        }

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('wallet_balance');
        });
    }
};
