<?php

namespace App\Exports;

use App\Models\Customer;
use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;

class CustomersReportExport implements FromArray, WithHeadings
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
        $topSpenders = Customer::whereHas('orders', function ($query) {
            $query->where('status', 'delivered');
        })
        ->withSum(['orders' => function ($query) {
            $query->where('status', 'delivered');
        }], 'total_amount')
        ->orderByDesc('orders_sum_total_amount')
        ->take(10)
        ->get();

        $mostFrequentBuyers = Customer::whereHas('orders', function ($query) {
            $query->where('status', 'delivered');
        })
        ->withCount(['orders' => function ($query) {
            $query->where('status', 'delivered');
        }])
        ->orderByDesc('orders_count')
        ->take(10)
        ->get();

        $inactiveCustomers = Customer::whereHas('orders')
            ->whereDoesntHave('orders', function ($query) {
                $query->where('created_at', '>=', Carbon::now()->subDays(90));
            })
            ->with('orders')
            ->take(10)
            ->get();

        $rows = [];

        foreach ($topSpenders as $customer) {
            $rows[] = [
                'القسم' => 'أفضل العملاء حسب قيمة المشتريات',
                'اسم العميل' => $customer->name,
                'رقم الهاتف' => $customer->phone_number,
                'إجمالي المشتريات' => (float) ($customer->orders_sum_total_amount ?? 0),
                'عدد الطلبات' => '',
                'تاريخ آخر طلب' => '',
            ];
        }

        foreach ($mostFrequentBuyers as $customer) {
            $rows[] = [
                'القسم' => 'أفضل العملاء حسب عدد الطلبات',
                'اسم العميل' => $customer->name,
                'رقم الهاتف' => $customer->phone_number,
                'إجمالي المشتريات' => '',
                'عدد الطلبات' => (int) ($customer->orders_count ?? 0),
                'تاريخ آخر طلب' => '',
            ];
        }

        foreach ($inactiveCustomers as $customer) {
            $rows[] = [
                'القسم' => 'عملاء غير نشطين',
                'اسم العميل' => $customer->name,
                'رقم الهاتف' => $customer->phone_number,
                'إجمالي المشتريات' => '',
                'عدد الطلبات' => '',
                'تاريخ آخر طلب' => optional($customer->orders->max('created_at'))->format('Y-m-d') ?? '',
            ];
        }

        return $rows;
    }

    public function headings(): array
    {
        return [
            'القسم',
            'اسم العميل',
            'رقم الهاتف',
            'إجمالي المشتريات',
            'عدد الطلبات',
            'تاريخ آخر طلب',
        ];
    }
}
