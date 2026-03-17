<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('managers')) {
            return;
        }

        Schema::table('managers', function (Blueprint $table) {
            if (! Schema::hasColumn('managers', 'permissions')) {
                $column = $table->json('permissions')->nullable();

                if (Schema::hasColumn('managers', 'allowances')) {
                    $column->after('allowances');
                }
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('managers')) {
            return;
        }

        Schema::table('managers', function (Blueprint $table) {
            if (Schema::hasColumn('managers', 'permissions')) {
                $table->dropColumn('permissions');
            }
        });
    }
};
