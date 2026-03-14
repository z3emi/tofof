<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('allowance_vouchers', function (Blueprint $table) {
            $table->id();
            $table->string('number')->unique();
            $table->dateTime('voucher_date');
            $table->foreignId('customer_id')->constrained()->cascadeOnDelete();
            $table->foreignId('manager_id')->nullable()->constrained('managers')->nullOnDelete();
            $table->string('type');
            $table->decimal('amount', 18, 2);
            $table->text('description')->nullable();
            $table->foreignId('customer_transaction_id')->nullable()->constrained('customer_transactions')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('allowance_vouchers');
    }
};
