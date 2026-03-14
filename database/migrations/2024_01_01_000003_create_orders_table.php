<?php
// ======================================================================
// الملف: database/migrations/xxxx_create_orders_table.php
// (تم تعديل user_id إلى customer_id)
// ======================================================================
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            // ** التعديل الرئيسي هنا **
            $table->foreignId('customer_id')->constrained('customers')->onDelete('cascade');
            $table->string('governorate');
            $table->string('city');
            $table->string('nearest_landmark');
            $table->text('notes')->nullable();
            $table->decimal('total_amount', 10, 2);
            $table->enum('status', ['pending', 'processing', 'shipped', 'delivered', 'cancelled', 'returned'])->default('pending');
            $table->string('source')->default('website');
            $table->string('payment_method')->default('cash_on_delivery');
            $table->softDeletes();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};