<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        if (!Schema::hasTable('cash_boxes')) {
            return;
        }

        Schema::table('cash_boxes', function (Blueprint $table) {
            if (!Schema::hasColumn('cash_boxes', 'code')) {
                $table->string('code', 32)->nullable()->after('id');
            }
            if (!Schema::hasColumn('cash_boxes', 'type')) {
                $table->string('type', 32)->default('cash')->after('name');
            }
            if (!Schema::hasColumn('cash_boxes', 'currency')) {
                $table->string('currency', 3)->default('IQD')->after('type');
            }
            if (!Schema::hasColumn('cash_boxes', 'is_primary')) {
                $table->boolean('is_primary')->default(false)->after('balance');
            }
            if (!Schema::hasColumn('cash_boxes', 'notes')) {
                $table->text('notes')->nullable()->after('is_primary');
            }
        });

        $counter = 1;
        DB::table('cash_boxes')->orderBy('id')->chunkById(50, function ($cashBoxes) use (&$counter) {
            foreach ($cashBoxes as $cashBox) {
                $code = $cashBox->code ?: sprintf('CB-%04d', $counter++);
                DB::table('cash_boxes')
                    ->where('id', $cashBox->id)
                    ->update([
                        'code' => $code,
                        'type' => $cashBox->type ?: 'cash',
                        'currency' => $cashBox->currency ?: 'IQD',
                    ]);
            }
        });

        Schema::table('cash_boxes', function (Blueprint $table) {
            if (Schema::hasColumn('cash_boxes', 'code')) {
                $table->unique('code');
            }
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('cash_boxes')) {
            return;
        }

        Schema::table('cash_boxes', function (Blueprint $table) {
            if (Schema::hasColumn('cash_boxes', 'code')) {
                try {
                    $table->dropUnique('cash_boxes_code_unique');
                } catch (\Throwable $exception) {
                    // ignore if the unique index name differs
                }
                $table->dropColumn('code');
            }
            if (Schema::hasColumn('cash_boxes', 'type')) {
                $table->dropColumn('type');
            }
            if (Schema::hasColumn('cash_boxes', 'currency')) {
                $table->dropColumn('currency');
            }
            if (Schema::hasColumn('cash_boxes', 'is_primary')) {
                $table->dropColumn('is_primary');
            }
            if (Schema::hasColumn('cash_boxes', 'notes')) {
                $table->dropColumn('notes');
            }
        });
    }
};
