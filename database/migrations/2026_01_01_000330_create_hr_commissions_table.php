<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('hr_commissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->foreignId('employee_id')->constrained('managers')->cascadeOnDelete();
            $table->foreignId('payroll_item_id')->nullable()->constrained('hr_payroll_items')->nullOnDelete();
            $table->decimal('amount', 12, 2);
            $table->enum('status', ['accrued', 'void', 'paid'])->default('accrued');
            $table->timestamp('earned_at')->nullable();
            $table->timestamp('voided_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('hr_commissions');
    }
};
