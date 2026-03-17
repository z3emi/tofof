<?php

namespace App\Exports;

use App\Models\Product;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;

class StockReportExport implements FromArray, WithHeadings
{
    protected int $month;
    protected int $year;

    public function __construct(int $month, int $year)
    {
        $this->month = $month;
        $this->year = $year;
    }

    public function array(): array
    {
        $products = Product::query()->get();

        $lowStockProducts = $products->filter(function ($product) {
            return $product->stock_quantity > 0 && $product->stock_quantity <= 10;
        })->values();

        $outOfStockProducts = $products->filter(function ($product) {
            return $product->stock_quantity <= 0;
        })->values();

        $topSellingProducts = Product::query()
            ->whereHas('orderItems', function ($query) {
                $query->whereYear('created_at', $this->year)
                    ->whereMonth('created_at', $this->month);
            })
            ->withCount(['orderItems' => function ($query) {
                $query->whereYear('created_at', $this->year)
                    ->whereMonth('created_at', $this->month);
            }])
            ->orderByDesc('order_items_count')
            ->take(10)
            ->get();

        $rows = [];

        foreach ($lowStockProducts as $product) {
            $rows[] = [
                'القسم' => 'منتجات على وشك النفاد',
                'اسم المنتج' => $product->name_ar,
                'SKU' => $product->sku ?? '',
                'الكمية الحالية' => (int) $product->stock_quantity,
                'عدد مرات البيع' => '',
            ];
        }

        foreach ($outOfStockProducts as $product) {
            $rows[] = [
                'القسم' => 'منتجات نافدة',
                'اسم المنتج' => $product->name_ar,
                'SKU' => $product->sku ?? '',
                'الكمية الحالية' => 0,
                'عدد مرات البيع' => '',
            ];
        }

        foreach ($topSellingProducts as $product) {
            $rows[] = [
                'القسم' => 'الأكثر مبيعًا',
                'اسم المنتج' => $product->name_ar,
                'SKU' => $product->sku ?? '',
                'الكمية الحالية' => (int) $product->stock_quantity,
                'عدد مرات البيع' => (int) $product->order_items_count,
            ];
        }

        return $rows;
    }

    public function headings(): array
    {
        return [
            'القسم',
            'اسم المنتج',
            'SKU',
            'الكمية الحالية',
            'عدد مرات البيع',
        ];
    }
}
