<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up(): void {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('name_ar');
            $table->string('name_en')->nullable();
            $table->string('name_ku')->nullable();
            $table->text('description_ar');
            $table->text('description_en')->nullable();
            $table->text('description_ku')->nullable();
            $table->decimal('cost_price', 10, 2)->default(0);
            $table->decimal('price', 10, 2);
            $table->string('image_url')->nullable();
            $table->integer('stock_quantity')->default(0);
            $table->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('products'); }
};