<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (!Schema::hasTable('product_images')) {
            return;
        }

        if (!Schema::hasColumn('product_images', 'sort_order')) {
            Schema::table('product_images', function (Blueprint $table) {
                $table->unsignedInteger('sort_order')->default(0)->after('image_path');
            });
        }

        $productIds = DB::table('product_images')
            ->select('product_id')
            ->distinct()
            ->pluck('product_id');

        foreach ($productIds as $productId) {
            $imageIds = DB::table('product_images')
                ->where('product_id', $productId)
                ->orderBy('id')
                ->pluck('id');

            $order = 1;
            foreach ($imageIds as $imageId) {
                DB::table('product_images')
                    ->where('id', $imageId)
                    ->update(['sort_order' => $order]);
                $order++;
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (!Schema::hasTable('product_images')) {
            return;
        }

        if (Schema::hasColumn('product_images', 'sort_order')) {
            Schema::table('product_images', function (Blueprint $table) {
                $table->dropColumn('sort_order');
            });
        }
    }
};
