<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('hr_payrolls')) {
            Schema::table('hr_payrolls', function (Blueprint $table) {
                if (!Schema::hasColumn('hr_payrolls', 'currency')) {
                    $table->string('currency', 3)->default('IQD')->after('processed_by');
                }
                if (!Schema::hasColumn('hr_payrolls', 'exchange_rate_used')) {
                    $table->decimal('exchange_rate_used', 12, 4)->nullable()->after('currency');
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('hr_payrolls')) {
            Schema::table('hr_payrolls', function (Blueprint $table) {
                if (Schema::hasColumn('hr_payrolls', 'exchange_rate_used')) {
                    $table->dropColumn('exchange_rate_used');
                }
                if (Schema::hasColumn('hr_payrolls', 'currency')) {
                    $table->dropColumn('currency');
                }
            });
        }
    }
};
