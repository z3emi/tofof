<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('manufacturing_order_materials', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained('manufacturing_orders')->cascadeOnDelete();
            $table->foreignId('material_id')->constrained('manufacturing_materials')->cascadeOnDelete();
            $table->decimal('quantity_used', 12, 3)->default(0);
            $table->decimal('cost', 12, 2)->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('manufacturing_order_materials');
    }
};
