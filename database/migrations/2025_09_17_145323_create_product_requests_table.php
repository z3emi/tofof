<?php

// database/migrations/2025_09_17_000000_create_product_requests_table.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('product_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('product_name');
            $table->string('brand')->nullable();
            $table->string('link')->nullable();
            $table->string('phone');
            $table->text('notes')->nullable();
            $table->enum('status', ['pending','processing','done'])->default('pending');
            $table->timestamps();
        });
    }
    public function down(): void {
        Schema::dropIfExists('product_requests');
    }
};
