<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('managers') && !Schema::hasColumn('managers', 'salary_currency')) {
            Schema::table('managers', function (Blueprint $table) {
                $table->string('salary_currency', 3)->default('IQD')->after('commission_rate');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('managers') && Schema::hasColumn('managers', 'salary_currency')) {
            Schema::table('managers', function (Blueprint $table) {
                $table->dropColumn('salary_currency');
            });
        }
    }
};
