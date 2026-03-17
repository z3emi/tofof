<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\CustomersReportExport;
use App\Exports\FinancialReportExport;
use App\Exports\StockReportExport;

class ReportController extends Controller
{
    public function __construct()
    {
        $this->middleware(\Spatie\Permission\Middleware\PermissionMiddleware::class . ':view-reports');
    }

    public function financial(Request $request)
    {
        $year  = $request->input('year',  Carbon::now()->year);
        $month = $request->input('month', Carbon::now()->month);

        $startDate = Carbon::createFromDate($year, $month, 1)->startOfMonth();
        $endDate   = Carbon::createFromDate($year, $month, 1)->endOfMonth();

        // نجلب الطلبات المُسلمة فقط ضمن الفترة، مع العناصر
        $ordersQuery = Order::with(['items.product', 'customer'])
            ->where('status', 'delivered')
            ->whereBetween('created_at', [$startDate, $endDate]);

        $orders = $ordersQuery->get();
        // جلب قائمة الطلبات مع الترقيم لعرضها في الجدول
        $ordersList = $ordersQuery->orderBy('created_at', 'desc')->paginate(20)->withQueryString();

        $totalSalesNet     = 0; // صافي المبيعات
        $totalOrders       = $orders->count();

        // بيانات الرسم اليومي
        $byDay = []; // date => ['net'=>...]

        foreach ($orders as $o) {
            $itemsTotal = $o->items->sum(fn($i) => (float)$i->price * (int)$i->quantity);
            $discount   = (float)($o->discount_amount ?? 0);

            $net        = max(0, $itemsTotal - $discount);     // بعد الخصم

            $totalSalesNet    += $net;

            $d = $o->created_at->toDateString();
            if (!isset($byDay[$d])) {
                $byDay[$d] = ['net' => 0];
            }
            $byDay[$d]['net']     += $net;
        }

        // تجهيز بيانات الرسم اليومي عبر المرور على كل أيام الشهر
        $chartLabels = [];
        $salesData   = [];

        for ($date = $startDate->copy(); $date->lte($endDate); $date->addDay()) {
            $key = $date->format('Y-m-d');
            $chartLabels[] = $key;

            if (isset($byDay[$key])) {
                $salesData[]  = $byDay[$key]['net'];
            } else {
                $salesData[]  = 0;
            }
        }

        // المنتجات الأكثر مبيعًا
        $topSellingProducts = OrderItem::whereHas('order', function ($query) use ($startDate, $endDate) {
            $query->where('status', 'delivered')
                  ->whereBetween('created_at', [$startDate, $endDate]);
        })
        ->with('product')
        ->selectRaw('product_id, SUM(quantity) as total_quantity_sold')
        ->groupBy('product_id')
        ->orderByDesc('total_quantity_sold')
        ->take(10)
        ->get();

        return view('admin.reports.financial', compact(
            'totalSalesNet', 'totalOrders', 'ordersList',
            'chartLabels', 'salesData', 'month', 'year',
            'topSellingProducts'
        ));
    }

    public function exportExcel(Request $request)
    {
        $month = $request->input('month', date('m'));
        $year  = $request->input('year',  date('Y'));

        return Excel::download(new FinancialReportExport($month, $year), 'financial_report_'.$month.'_'.$year.'.xlsx');
    }

    public function exportStockExcel(Request $request)
    {
        $month = (int) $request->input('month', date('m'));
        $year  = (int) $request->input('year', date('Y'));

        return Excel::download(new StockReportExport($month, $year), 'stock_report_'.$month.'_'.$year.'.xlsx');
    }

    public function exportCustomersExcel(Request $request)
    {
        $month = (int) $request->input('month', date('m'));
        $year  = (int) $request->input('year', date('Y'));

        return Excel::download(new CustomersReportExport($month, $year), 'customers_report_'.$month.'_'.$year.'.xlsx');
    }

    public function stockReport(Request $request)
    {
        $month = $request->input('month', date('m'));
        $year  = $request->input('year',  date('Y'));

        $products = Product::all();

        $lowStockProducts = $products->filter(function ($product) {
            return $product->stock_quantity > 0 && $product->stock_quantity <= 10;
        })->sortBy('stock_quantity');

        $outOfStockProducts = $products->filter(function ($product) {
            return $product->stock_quantity <= 0;
        });

        $topSellingProducts = Product::withCount(['orderItems' => function($q) use($month, $year) {
            $q->whereYear('created_at', $year)
              ->whereMonth('created_at', $month);
        }])->orderByDesc('order_items_count')->take(10)->get();

        return view('admin.reports.stock', compact(
            'month', 'year',
            'lowStockProducts', 'outOfStockProducts', 'topSellingProducts'
        ));
    }

    public function index()
    {
        return view('admin.reports.index');
    }

    public function inventory(Request $request)
    {
        $month = $request->input('month', date('m'));
        $year  = $request->input('year',  date('Y'));

        $products = Product::all();

        $lowStockProducts = $products->filter(function ($product) {
            return $product->stock_quantity > 0 && $product->stock_quantity < 10;
        })->sortBy('stock_quantity');

        $outOfStockProducts = $products->filter(function ($product) {
            return $product->stock_quantity <= 0;
        });

        $topSellingProducts = Product::whereHas('orderItems', function ($query) use ($month, $year) {
            $query->whereYear('created_at', $year)
                  ->whereMonth('created_at', $month);
        })
        ->withCount(['orderItems' => function ($query) use ($month, $year) {
            $query->whereYear('created_at', $year)
                  ->whereMonth('created_at', $month);
        }])
        ->orderBy('order_items_count', 'desc')
        ->take(10)
        ->get();

        return view('admin.reports.stock', compact(
            'month', 'year',
            'lowStockProducts', 'outOfStockProducts', 'topSellingProducts'
        ));
    }

    public function customers(Request $request)
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

        return view('admin.reports.customers', compact(
            'topSpenders', 'mostFrequentBuyers', 'inactiveCustomers'
        ));
    }
}
