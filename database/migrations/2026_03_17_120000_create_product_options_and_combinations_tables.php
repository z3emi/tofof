<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Fix products.id if it is missing its PRIMARY KEY (can happen after a raw SQL import)
        if (!DB::select("SHOW KEYS FROM `products` WHERE Key_name = 'PRIMARY'")) {
            DB::statement('ALTER TABLE `products` MODIFY `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT, ADD PRIMARY KEY (`id`)');
        }

        if (!Schema::hasTable('product_options')) {
            Schema::create('product_options', function (Blueprint $table) {
                $table->id();
                $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
                $table->string('name_ar');
                $table->string('name_en')->nullable();
                $table->unsignedInteger('sort_order')->default(0);
                $table->boolean('is_required')->default(true);
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('product_option_values')) {
            Schema::create('product_option_values', function (Blueprint $table) {
                $table->id();
                $table->foreignId('product_option_id')->constrained('product_options')->cascadeOnDelete();
                $table->string('value_ar');
                $table->string('value_en')->nullable();
                $table->unsignedInteger('sort_order')->default(0);
                $table->timestamps();
            });
        }

        // Drop and recreate if it already exists without proper constraints
        Schema::dropIfExists('product_option_combination_images');
        Schema::dropIfExists('product_option_combinations');

        Schema::create('product_option_combinations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            $table->string('combination_key');
            $table->json('option_value_ids');
            $table->timestamps();

            $table->unique(['product_id', 'combination_key']);
        });

        if (!Schema::hasTable('product_option_combination_images')) {
            Schema::create('product_option_combination_images', function (Blueprint $table) {
                $table->id();
                $table->foreignId('product_option_combination_id')
                    ->constrained('product_option_combinations')
                    ->name('poi_combo_img_combo_fk')
                    ->cascadeOnDelete();
                $table->foreignId('product_image_id')->nullable()
                    ->constrained('product_images')
                    ->name('poi_combo_img_img_fk')
                    ->nullOnDelete();
                $table->string('image_path')->nullable();
                $table->timestamps();

                $table->index('product_image_id');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('product_option_combination_images');
        Schema::dropIfExists('product_option_combinations');
        Schema::dropIfExists('product_option_values');
        Schema::dropIfExists('product_options');
    }
};
