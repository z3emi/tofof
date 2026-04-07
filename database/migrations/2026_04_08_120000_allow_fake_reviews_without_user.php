<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('product_reviews')) {
            return;
        }

        Schema::table('product_reviews', function (Blueprint $table) {
            if (! Schema::hasColumn('product_reviews', 'fake_name')) {
                $table->string('fake_name', 120)->nullable()->after('user_id');
            }
        });

        // Allow fake reviews with no linked user account.
        if (Schema::hasColumn('product_reviews', 'user_id')) {
            DB::statement('ALTER TABLE `product_reviews` MODIFY `user_id` BIGINT UNSIGNED NULL');
        }
    }

    public function down(): void
    {
        if (! Schema::hasTable('product_reviews')) {
            return;
        }

        // Remove user-less reviews before restoring NOT NULL constraint.
        DB::table('product_reviews')->whereNull('user_id')->delete();

        if (Schema::hasColumn('product_reviews', 'user_id')) {
            DB::statement('ALTER TABLE `product_reviews` MODIFY `user_id` BIGINT UNSIGNED NOT NULL');
        }

        Schema::table('product_reviews', function (Blueprint $table) {
            if (Schema::hasColumn('product_reviews', 'fake_name')) {
                $table->dropColumn('fake_name');
            }
        });
    }
};
