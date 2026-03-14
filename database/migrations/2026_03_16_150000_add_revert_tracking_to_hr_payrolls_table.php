<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('hr_payrolls', function (Blueprint $table) {
            $table->string('original_period_code')->nullable()->after('period_code');
            $table->timestamp('reverted_at')->nullable()->after('processed_at');
            $table->foreignId('reverted_by')->nullable()->after('reverted_at')->constrained('managers')->nullOnDelete();
            $table->text('revert_reason')->nullable()->after('reverted_by');
        });

        DB::table('hr_payrolls')->whereNull('original_period_code')->update([
            'original_period_code' => DB::raw('period_code'),
        ]);
    }

    public function down(): void
    {
        Schema::table('hr_payrolls', function (Blueprint $table) {
            $table->dropForeign(['reverted_by']);
            $table->dropColumn(['original_period_code', 'reverted_at', 'reverted_by', 'revert_reason']);
        });
    }
};
