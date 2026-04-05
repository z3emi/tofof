<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (! Schema::hasTable('product_reviews')) {
            return;
        }

        if (! Schema::hasColumn('product_reviews', 'show_on_homepage')) {
            Schema::table('product_reviews', function (Blueprint $table) {
                $table->boolean('show_on_homepage')->default(false)->after('status');
                $table->index(['show_on_homepage', 'status'], 'product_reviews_homepage_status_index');
            });
        }
    }

    public function down(): void
    {
        if (! Schema::hasTable('product_reviews')) {
            return;
        }

        if (Schema::hasColumn('product_reviews', 'show_on_homepage')) {
            Schema::table('product_reviews', function (Blueprint $table) {
                $table->dropIndex('product_reviews_homepage_status_index');
                $table->dropColumn('show_on_homepage');
            });
        }
    }
};
