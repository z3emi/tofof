<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('categories', 'sort_order')) {
            Schema::table('categories', function (Blueprint $table) {
                $table->unsignedInteger('sort_order')->default(0)->after('parent_id');
            });
        }

        $groups = DB::table('categories')
            ->select('parent_id')
            ->distinct()
            ->pluck('parent_id');

        foreach ($groups as $parentId) {
            $rows = DB::table('categories')
                ->where('parent_id', $parentId)
                ->orderBy('sort_order')
                ->orderBy('id')
                ->pluck('id');

            $order = 1;
            foreach ($rows as $id) {
                DB::table('categories')->where('id', $id)->update(['sort_order' => $order]);
                $order++;
            }
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('categories', 'sort_order')) {
            Schema::table('categories', function (Blueprint $table) {
                $table->dropColumn('sort_order');
            });
        }
    }
};
