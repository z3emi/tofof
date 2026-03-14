<?php

// database/migrations/2025_01_01_000000_add_stats_columns_to_barcodes_table.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('barcodes', function (Blueprint $table) {
            if (!Schema::hasColumn('barcodes', 'hits')) {
                $table->unsignedBigInteger('hits')->default(0)->after('is_active');
            }
            if (!Schema::hasColumn('barcodes', 'last_hit_at')) {
                $table->timestamp('last_hit_at')->nullable()->after('hits');
            }
            if (!Schema::hasColumn('barcodes', 'last_ip')) {
                $table->string('last_ip', 45)->nullable()->after('last_hit_at');
            }
            if (!Schema::hasColumn('barcodes', 'last_user_agent')) {
                $table->string('last_user_agent', 255)->nullable()->after('last_ip');
            }
        });

        // تأمين البيانات القديمة
        DB::table('barcodes')->whereNull('hits')->update(['hits' => 0]);
    }

    public function down(): void
    {
        Schema::table('barcodes', function (Blueprint $table) {
            if (Schema::hasColumn('barcodes', 'last_user_agent')) $table->dropColumn('last_user_agent');
            if (Schema::hasColumn('barcodes', 'last_ip')) $table->dropColumn('last_ip');
            if (Schema::hasColumn('barcodes', 'last_hit_at')) $table->dropColumn('last_hit_at');
            if (Schema::hasColumn('barcodes', 'hits')) $table->dropColumn('hits');
        });
    }
};

