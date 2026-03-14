    <?php

    use Illuminate\Database\Migrations\Migration;
    use Illuminate\Database\Schema\Blueprint;
    use Illuminate\Support\Facades\Schema;

    return new class extends Migration
    {
        /**
         * Run the migrations.
         */
        public function up(): void
        {
            Schema::table('categories', function (Blueprint $table) {
                // إضافة حقل المفتاح الخارجي الذي يشير إلى نفس الجدول
                // nullable() للسماح بأن يكون القسم رئيسياً (بدون أب)
                // constrained() لإنشاء القيد
                // onDelete('cascade') لحذف كل الأقسام الفرعية عند حذف القسم الأب
                $table->foreignId('parent_id')->nullable()->constrained('categories')->onDelete('cascade')->after('id');
            });
        }

        /**
         * Reverse the migrations.
         */
        public function down(): void
        {
            Schema::table('categories', function (Blueprint $table) {
                // حذف القيد والعمود عند التراجع عن الـ migration
                $table->dropForeign(['parent_id']);
                $table->dropColumn('parent_id');
            });
        }
    };
    