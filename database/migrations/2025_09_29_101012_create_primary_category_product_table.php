<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('primary_category_product', function (Blueprint $table) {
            $table->foreignId('primary_category_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->primary(['primary_category_id', 'product_id']);
        });
    }

    public function down(): void {
        Schema::dropIfExists('primary_category_product');
    }
};

