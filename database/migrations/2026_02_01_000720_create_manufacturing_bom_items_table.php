<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('manufacturing_bom_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('bom_id')->constrained('manufacturing_boms')->cascadeOnDelete();
            $table->foreignId('material_id')->constrained('manufacturing_materials')->cascadeOnDelete();
            $table->decimal('quantity', 12, 3)->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('manufacturing_bom_items');
    }
};
