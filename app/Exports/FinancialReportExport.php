<?php

namespace App\Exports;

use App\Models\Order;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Carbon\Carbon;

class FinancialReportExport implements FromCollection, WithHeadings
{
    protected $month;
    protected $year;

    public function __construct($month, $year)
    {
        $this->month = $month;
        $this->year = $year;
    }

    public function collection(): Collection
    {
        $start = Carbon::createFromDate($this->year, $this->month, 1)->startOfMonth();
        $end = Carbon::createFromDate($this->year, $this->month, 1)->endOfMonth();

        return Order::with(['customer', 'items'])
            ->where('status', 'delivered')
            ->whereBetween('created_at', [$start, $end])
            ->get()
            ->map(function ($order) {
                return [
                    'ID الطلب'         => $order->id,
                    'اسم العميل'      => $order->customer->name ?? 'غير معروف',
                    'المبلغ الكلي'     => $order->total_amount,
                    'الخصم'           => $order->discount_amount,
                    'تكلفة التوصيل'   => $order->shipping_cost,
                    'صافي المبيعات'   => $order->total_amount - $order->shipping_cost,
                    'تاريخ الطلب'     => $order->created_at->format('Y-m-d'),
                ];
            });
    }

    public function headings(): array
    {
        return [
            'ID الطلب',
            'اسم العميل',
            'المبلغ الكلي',
            'الخصم',
            'تكلفة التوصيل',
            'صافي المبيعات',
            'تاريخ الطلب'
        ];
    }
}
