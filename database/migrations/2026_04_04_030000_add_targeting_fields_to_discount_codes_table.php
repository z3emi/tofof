<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('discount_codes', function (Blueprint $table) {
            $table->enum('audience_mode', ['all', 'eligible', 'selected'])->default('all')->after('is_active');
            $table->enum('order_count_operator', ['gte', 'lte'])->nullable()->after('audience_mode');
            $table->unsignedInteger('order_count_threshold')->nullable()->after('order_count_operator');
            $table->enum('amount_operator', ['gte', 'lte'])->nullable()->after('order_count_threshold');
            $table->decimal('amount_threshold', 12, 2)->nullable()->after('amount_operator');
            $table->boolean('notify_via_bell')->default(true)->after('amount_threshold');
            $table->boolean('notify_via_push')->default(true)->after('notify_via_bell');
            $table->timestamp('sent_at')->nullable()->after('notify_via_push');
        });
    }

    public function down(): void
    {
        Schema::table('discount_codes', function (Blueprint $table) {
            $table->dropColumn([
                'audience_mode',
                'order_count_operator',
                'order_count_threshold',
                'amount_operator',
                'amount_threshold',
                'notify_via_bell',
                'notify_via_push',
                'sent_at',
            ]);
        });
    }
};
