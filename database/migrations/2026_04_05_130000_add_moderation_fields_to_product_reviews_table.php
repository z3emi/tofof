<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('product_reviews', function (Blueprint $table) {
            if (! Schema::hasColumn('product_reviews', 'moderation_score')) {
                $table->unsignedSmallInteger('moderation_score')->default(0)->after('status');
            }

            if (! Schema::hasColumn('product_reviews', 'moderation_flags')) {
                $table->json('moderation_flags')->nullable()->after('moderation_score');
            }
        });
    }

    public function down(): void
    {
        Schema::table('product_reviews', function (Blueprint $table) {
            if (Schema::hasColumn('product_reviews', 'moderation_flags')) {
                $table->dropColumn('moderation_flags');
            }

            if (Schema::hasColumn('product_reviews', 'moderation_score')) {
                $table->dropColumn('moderation_score');
            }
        });
    }
};
