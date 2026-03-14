<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        // ملاحظة: استخدام SQL مباشر لتفادي الاعتماد على doctrine/dbal
        // غيّر الصياغة لو قاعدة بياناتك غير MySQL
        DB::statement("ALTER TABLE discount_codes MODIFY COLUMN type VARCHAR(32) NOT NULL");
        DB::statement("ALTER TABLE discount_codes MODIFY COLUMN value DECIMAL(10,2) NULL");
        DB::statement("ALTER TABLE discount_codes MODIFY COLUMN max_discount_amount DECIMAL(10,2) NULL");
    }

    public function down(): void
    {
        // رجوع تقريبي (لو كنت سابقًا تستعمل فقط fixed/percentage مع قيمة غير NULL)
        DB::statement("ALTER TABLE discount_codes MODIFY COLUMN type VARCHAR(32) NOT NULL");
        DB::statement("UPDATE discount_codes SET value = 0 WHERE value IS NULL");
        DB::statement("ALTER TABLE discount_codes MODIFY COLUMN value DECIMAL(10,2) NOT NULL DEFAULT 0");
    }
};
