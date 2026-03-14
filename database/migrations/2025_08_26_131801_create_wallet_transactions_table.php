<?php

// In database/migrations/xxxx_xx_xx_xxxxxx_create_wallet_transactions_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('wallet_transactions', function (Blueprint $table) {
       $table->id();
    $table->foreignId('user_id')->constrained()->cascadeOnDelete();
    // إذا DB عندك MySQL يدعم enum بشكل طبيعي:
    $table->enum('type', ['credit','debit']); // إيداع/سحب
    // لو تفضل بدل enum:
    // $table->string('type', 10); // واحفظ 'credit' أو 'debit'
    $table->decimal('amount', 12, 2);
    $table->string('description')->nullable();
    $table->decimal('balance_after', 12, 2)->default(0);
    $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('wallet_transactions');
    }
};
