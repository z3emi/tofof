<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        if (Schema::hasTable('expenses')) {
            Schema::dropIfExists('expenses');
        }

        Schema::create('expense_categories', function (Blueprint $table) {
            $table->id();
            $table->string('code', 32)->unique();
            $table->string('name');
            $table->string('color', 16)->nullable();
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('expenses', function (Blueprint $table) {
            $table->id();
            $table->string('reference')->unique();
            $table->foreignId('expense_category_id')->constrained('expense_categories');
            $table->foreignId('cash_box_id')->nullable()->constrained('cash_boxes')->nullOnDelete();
            $table->foreignId('created_by_id')->nullable()->constrained('managers')->nullOnDelete();
            $table->decimal('amount', 14, 2);
            $table->decimal('tax_amount', 14, 2)->default(0);
            $table->decimal('total_amount', 14, 2);
            $table->date('expense_date');
            $table->dateTime('paid_at')->nullable();
            $table->string('payment_method', 32)->default('cash');
            $table->string('status', 32)->default('paid');
            $table->string('vendor_name')->nullable();
            $table->string('invoice_number')->nullable();
            $table->text('description')->nullable();
            $table->text('notes')->nullable();
            $table->json('meta')->nullable();
            $table->timestamps();
        });

        DB::table('expense_categories')->insert([
            [
                'code' => 'OPS',
                'name' => 'مصاريف تشغيلية',
                'description' => 'التكاليف اليومية مثل الإيجار والخدمات والفواتير.',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'code' => 'PAY',
                'name' => 'رواتب ومستحقات',
                'description' => 'رواتب الموظفين والعمولات والمكافآت.',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'code' => 'LOG',
                'name' => 'نقل وتوريد',
                'description' => 'تكاليف الشحن، التوريد، وخدمات التوصيل.',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'code' => 'MKT',
                'name' => 'تسويق ومبيعات',
                'description' => 'الإعلانات والحملات التسويقية والمطبوعات.',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('expenses');
        Schema::dropIfExists('expense_categories');

        Schema::create('expenses', function (Blueprint $table) {
            $table->id();
            $table->string('description');
            $table->decimal('amount', 10, 2);
            $table->date('expense_date');
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }
};
