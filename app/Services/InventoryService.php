<?php

namespace App\Services;

use App\Models\Product;
use Exception;

class InventoryService
{
    /**
     * Deducts stock for a given product directly.
     * Calculates the total cost based on cost_price or price.
     *
     * @param Product $product
     * @param int $quantityToDeduct
     * @return float Total cost of the deducted items.
     * @throws \Exception
     */
    public function deductStock(Product $product, int $quantityToDeduct): float
    {
        $totalStock = (int) $product->stock_quantity;
        if ($totalStock < $quantityToDeduct) {
            throw new Exception("الكمية المطلوبة للمنتج '{$product->name_ar}' غير متوفرة في المخزون. المتاح: {$totalStock}");
        }

        $product->decrement('stock_quantity', $quantityToDeduct);

        $cost = $product->price;
        $totalCost = $quantityToDeduct * $cost;

        return $totalCost;
    }

    /**
     * Restores stock for a given product directly.
     * This is used when an order is cancelled or edited.
     *
     * @param Product $product
     * @param int $quantityToRestore
     * @return void
     */
    public function restoreStock(Product $product, int $quantityToRestore): void
    {
        if ($quantityToRestore <= 0) {
            return;
        }

        $product->increment('stock_quantity', $quantityToRestore);
    }
}
