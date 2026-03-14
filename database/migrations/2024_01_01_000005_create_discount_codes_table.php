<?php
// ======================================================================
// الملف: database/migrations/xxxx_create_discount_codes_table.php
// ======================================================================
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
        Schema::create('discount_codes', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->enum('type', ['percentage', 'fixed'])->default('fixed');
            $table->decimal('value', 10, 2);
            $table->timestamp('expires_at')->nullable();
            $table->integer('max_uses')->nullable()->comment('إجمالي عدد مرات الاستخدام');
            $table->integer('max_uses_per_user')->nullable()->comment('عدد مرات الاستخدام للعميل الواحد');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            // If you intended to add soft deletes for discount codes,
            // you would typically add $table->softDeletes(); here.
            // For this specific issue, we're focusing on the 'orders' table.
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('discount_codes');
    }
};