<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            if (! Schema::hasColumn('orders', 'is_gift')) {
                $table->boolean('is_gift')->default(false)->after('notes');
            }

            if (! Schema::hasColumn('orders', 'gift_recipient_name')) {
                $table->string('gift_recipient_name')->nullable()->after('is_gift');
            }

            if (! Schema::hasColumn('orders', 'gift_recipient_phone')) {
                $table->string('gift_recipient_phone')->nullable()->after('gift_recipient_name');
            }

            if (! Schema::hasColumn('orders', 'gift_recipient_address_details')) {
                $table->text('gift_recipient_address_details')->nullable()->after('gift_recipient_phone');
            }

            if (! Schema::hasColumn('orders', 'gift_message')) {
                $table->text('gift_message')->nullable()->after('gift_recipient_address_details');
            }
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $columns = [
                'is_gift',
                'gift_recipient_name',
                'gift_recipient_phone',
                'gift_recipient_address_details',
                'gift_message',
            ];

            foreach ($columns as $column) {
                if (Schema::hasColumn('orders', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
