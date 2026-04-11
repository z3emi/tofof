<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$product = \App\Models\Product::find(4);
if ($product) {
    echo "Product ID: " . $product->id . "\n";
    echo "Name: " . $product->name . "\n";
    echo "Current Price: " . $product->current_price . "\n";
    echo "Stock: " . $product->stock_quantity . "\n";
    echo "Active: " . ($product->is_active ? 'Yes' : 'No') . "\n";
    echo "Images count: " . $product->images->count() . "\n";
    if ($product->images->first()) {
        echo "First image: " . $product->images->first()->image_path . "\n";
    }
} else {
    echo "Product 4 not found\n";
}
