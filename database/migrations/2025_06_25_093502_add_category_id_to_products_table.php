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
            Schema::table('products', function (Blueprint $table) {
                // إضافة حقل الربط مع جدول الأقسام
                $table->foreignId('category_id')->nullable()->constrained('categories')->onDelete('set null')->after('id');
            });
        }

        /**
         * Reverse the migrations.
         */
        public function down(): void
        {
            Schema::table('products', function (Blueprint $table) {
                // حذف حقل الربط
                $table->dropForeign(['category_id']);
                $table->dropColumn('category_id');
            });
        }
    };
    