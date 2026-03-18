<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\QueryException;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('wallet_transactions')) {
            return;
        }

        try {
            Schema::create('wallet_transactions', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('user_id');
                $table->enum('type', ['credit', 'debit']);
                $table->decimal('amount', 12, 2);
                $table->string('description')->nullable();
                $table->decimal('balance_after', 12, 2)->default(0);
                $table->timestamps();
            });
        } catch (QueryException $e) {
            if (($e->errorInfo[1] ?? null) !== 1050) {
                throw $e;
            }
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('wallet_transactions');
    }
};