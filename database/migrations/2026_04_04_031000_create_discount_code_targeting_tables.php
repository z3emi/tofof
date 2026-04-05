<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('discount_code_user', function (Blueprint $table) {
            $table->id();
            $table->foreignId('discount_code_id')->constrained('discount_codes')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->timestamps();
            $table->unique(['discount_code_id', 'user_id']);
        });

        Schema::create('discount_code_primary_category', function (Blueprint $table) {
            $table->id();
            $table->foreignId('discount_code_id')->constrained('discount_codes')->cascadeOnDelete();
            $table->foreignId('primary_category_id')->constrained('primary_categories')->cascadeOnDelete();
            $table->timestamps();
            $table->unique(['discount_code_id', 'primary_category_id'], 'discount_code_primary_category_unique');
        });

        Schema::create('discount_code_delivery_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('discount_code_id')->constrained('discount_codes')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('channel', 50);
            $table->string('status', 20)->default('sent');
            $table->string('payload_hash', 64)->nullable();
            $table->text('error')->nullable();
            $table->timestamp('sent_at')->nullable();
            $table->timestamps();

            $table->index(['discount_code_id', 'user_id']);
            $table->unique(['discount_code_id', 'user_id', 'channel', 'payload_hash'], 'discount_code_delivery_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('discount_code_delivery_logs');
        Schema::dropIfExists('discount_code_primary_category');
        Schema::dropIfExists('discount_code_user');
    }
};
