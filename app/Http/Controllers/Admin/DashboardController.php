<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\User;
use Illuminate\Http\Request;
use App\Models\ProductReview as Review;

use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        /* ====== نطاق التاريخ للفلاتر (افتراضي آخر 30 يوم) ====== */
        $start = $request->date_start ? Carbon::parse($request->date_start)->startOfDay() : Carbon::today()->subDays(29)->startOfDay();
        $end   = $request->date_end   ? Carbon::parse($request->date_end)->endOfDay()   : now();

        /* ====== إحصائيات أساسية عامة (خارج الفلتر للحفاظ على سلوكك القديم) ====== */
        $totalOrders     = Order::count();
        $pendingOrders   = Order::where('status', 'pending')->count();
        $completedOrders = Order::where('status', 'delivered')->count();
        $returnedOrders  = Order::where('status', 'returned')->count();

        $activeCustomers = User::whereHas('orders')->count();
        $todayOrders     = Order::whereDate('created_at', Carbon::today())->count();

        $totalSales = Order::where('status', 'delivered')
            ->sum(DB::raw('total_amount - shipping_cost'));

        $latestOrders = Order::with('customer')->latest()->take(5)->get();

        $topProducts = Product::withCount('orders')
            ->orderByDesc('orders_count')->take(5)->get();

        $topCustomers = User::withCount('orders')
            ->orderByDesc('orders_count')->take(5)->get();

        $topProfitableProducts = OrderItem::with('product')
            ->selectRaw('product_id, SUM(quantity * price) as total_revenue, SUM(cost) as total_cost')
            ->groupBy('product_id')->get()
            ->map(function ($item) {
                $item->profit = (float)$item->total_revenue - (float)$item->total_cost;
                return $item;
            })->sortByDesc('profit')->take(5);

        /* ====== رسوم 30 يوم (قديمك) ====== */
        $chartLabels = [];
        $chartTotalOrdersData = [];
        $chartPendingOrdersData = [];
        $chartCompletedOrdersData = [];
        $chartReturnedOrdersData = [];
        for ($i = 29; $i >= 0; $i--) {
            $date = Carbon::today()->subDays($i)->format('Y-m-d');
            $chartLabels[]               = $date;
            $chartTotalOrdersData[]      = Order::whereDate('created_at', $date)->count();
            $chartPendingOrdersData[]    = Order::whereDate('created_at', $date)->where('status', 'pending')->count();
            $chartCompletedOrdersData[]  = Order::whereDate('created_at', $date)->where('status', 'delivered')->count();
            $chartReturnedOrdersData[]   = Order::whereDate('created_at', $date)->where('status', 'returned')->count();
        }

        /* =====================================================================
         *                 مراجعات المنتجات (كما هي مع تحسين طفيف)
         * ===================================================================== */
        $approved = Review::query();
        if (Schema::hasColumn((new Review)->getTable(), 'status')) {
            $approved->where('status', 'approved');
        }

        $totalReviews  = (clone $approved)->count();
        $averageRating = $totalReviews ? round((clone $approved)->avg('rating'), 2) : 0.0;

        $rawBreakdown = (clone $approved)
            ->select('rating', DB::raw('COUNT(*) as c'))
            ->groupBy('rating')
            ->pluck('c', 'rating')
            ->toArray();

        $ratingsBreakdown = [];
        for ($r = 1; $r <= 5; $r++) {
            $ratingsBreakdown[$r] = $rawBreakdown[$r] ?? 0;
        }

        $from = Carbon::today()->subDays(29)->startOfDay();
        $to   = now();

        $trendRows = (clone $approved)
            ->whereBetween('created_at', [$from, $to])
            ->selectRaw('DATE(created_at) as d, ROUND(AVG(rating),2) as avg_rating')
            ->groupBy('d')
            ->pluck('avg_rating', 'd')
            ->toArray();

        $ratingTrendLabels = [];
        $ratingTrendData   = [];
        for ($i = 29; $i >= 0; $i--) {
            $d = Carbon::today()->subDays($i)->format('Y-m-d');
            $ratingTrendLabels[] = $d;
            $ratingTrendData[]   = $trendRows[$d] ?? null;
        }

        $table = (new Review)->getTable();
        $reviewsBySource = ['Website' => $totalReviews ? 100 : 0];

        $hasRepliedAt = Schema::hasColumn($table, 'replied_at');
        $hasReplyText = Schema::hasColumn($table, 'reply') || Schema::hasColumn($table, 'admin_reply');

        $repliedCount = 0;
        $avgResponseTime = '—';

        if ($hasRepliedAt) {
            $repliedCount = (clone $approved)->whereNotNull('replied_at')->count();
            $avgMinutes = (clone $approved)
                ->whereNotNull('replied_at')
                ->selectRaw('AVG(TIMESTAMPDIFF(MINUTE, created_at, replied_at)) as avg_mins')
                ->value('avg_mins');
            if ($avgMinutes) {
                $avgResponseTime = \Carbon\CarbonInterval::minutes((int)round($avgMinutes))
                    ->cascade()->forHumans(['short' => true, 'parts' => 2]);
            }
        } elseif ($hasReplyText) {
            $col = Schema::hasColumn($table, 'reply') ? 'reply' : 'admin_reply';
            $repliedCount = (clone $approved)->whereNotNull($col)->where($col, '!=', '')->count();
        }

        $notReplied = max(0, $totalReviews - $repliedCount);
        $repliesStats = [
            'replied'          => $repliedCount,
            'not_replied'      => $notReplied,
            'replied_pct'      => $totalReviews ? round(($repliedCount / $totalReviews) * 100) : 0,
            'not_replied_pct'  => $totalReviews ? round(($notReplied / $totalReviews) * 100) : 0,
        ];

        /* =====================================================================
         *                   بيانات متقدمة ضمن الفترة (الفلاتر)
         * ===================================================================== */

        // الطلبات اليومية ضمن الفترة
        $ordersPerDay = Order::whereBetween('created_at', [$start, $end])
            ->selectRaw('DATE(created_at) d, COUNT(*) c')
            ->groupBy('d')
            ->pluck('c','d')
            ->toArray();

        // الإيراد الصافي اليومي ضمن الفترة (delivered فقط)
        $revenuePerDay = Order::whereBetween('created_at', [$start, $end])
            ->where('status','delivered')
            ->selectRaw('DATE(created_at) d, SUM(total_amount - shipping_cost) r')
            ->groupBy('d')
            ->pluck('r','d')
            ->toArray();

        // إنشاء المحور الزمني Labels + Arrays متطابقة الطول
        $rangeLabels = [];
        $rangeOrders = [];
        $rangeRevenue= [];
        $cursor = $start->copy();
        while ($cursor->lte($end)) {
            $d = $cursor->format('Y-m-d');
            $rangeLabels[] = $d;
            $rangeOrders[] = (int)($ordersPerDay[$d] ?? 0);
            $rangeRevenue[]= (float)($revenuePerDay[$d] ?? 0);
            $cursor->addDay();
        }

        // KPIs ضمن الفترة
        $ordersInRange = array_sum($rangeOrders);
        $revenueInRange= array_sum($rangeRevenue);
        $deliveredInRange = Order::whereBetween('created_at', [$start,$end])->where('status','delivered')->count();
        $completionRate = $ordersInRange ? round(($deliveredInRange / $ordersInRange) * 100) : 0;
        $aov = $ordersInRange ? ($revenueInRange / $ordersInRange) : 0;

        $kpi = [
            'orders_total'   => $ordersInRange,
            'revenue_net'    => (float)$revenueInRange,
            'aov'            => (float)$aov,
            'completion_rate'=> $completionRate,
        ];

        // Sparklines (نفس مدة الفترة)
        $spark = [
            'orders'     => $rangeOrders,
            'revenue'    => $rangeRevenue,
            'aov'        => array_map(function($r,$o){ return $o ? (float)$r / (int)$o : 0; }, $rangeRevenue, $rangeOrders),
            'completion' => (function() use ($start,$end){
                // نبني سلسلة تقريبية لنسبة الإكمال اليومية
                $out = [];
                $c = $start->copy();
                while($c->lte($end)){
                    $d = $c->format('Y-m-d');
                    $total = Order::whereDate('created_at',$d)->count();
                    $done  = Order::whereDate('created_at',$d)->where('status','delivered')->count();
                    $out[] = $total ? round(($done/$total)*100) : 0;
                    $c->addDay();
                }
                return $out;
            })(),
        ];

        // توزيع الحالات ضمن الفترة (doughnut)
        $statusDistribution = Order::whereBetween('created_at', [$start, $end])
            ->selectRaw("status, COUNT(*) c")
            ->groupBy('status')
            ->pluck('c','status')
            ->toArray();
        // تأكيد المفاتيح الثلاثة:
        $statusDistribution = [
            'delivered' => (int)($statusDistribution['delivered'] ?? 0),
            'pending'   => (int)($statusDistribution['pending'] ?? 0),
            'returned'  => (int)($statusDistribution['returned'] ?? 0),
        ];

        // الأكثر ربحية ضمن الفترة (انطلاقاً من order_items مع ربط order لتقييد التاريخ)
        $topProfitableProductsRange = OrderItem::with('product')
            ->join('orders','orders.id','=','order_items.order_id')
            ->whereBetween('orders.created_at', [$start,$end])
            ->selectRaw('order_items.product_id, SUM(order_items.quantity * order_items.price) as total_revenue, SUM(order_items.cost) as total_cost')
            ->groupBy('order_items.product_id')
            ->get()
            ->map(function ($item) {
                $item->profit = (float)$item->total_revenue - (float)$item->total_cost;
                return $item;
            })->sortByDesc('profit')->take(5);

        return view('admin.dashboard', compact(
            // القديم
            'totalOrders','pendingOrders','completedOrders','returnedOrders',
            'activeCustomers','todayOrders','totalSales','latestOrders',
            'topProducts','topCustomers','topProfitableProducts',
            'chartLabels','chartTotalOrdersData','chartPendingOrdersData',
            'chartCompletedOrdersData','chartReturnedOrdersData',
            'averageRating','totalReviews','reviewsBySource','repliesStats',
            'avgResponseTime','ratingsBreakdown','ratingTrendLabels','ratingTrendData',
            // الجديد
            'kpi','spark','statusDistribution','rangeLabels','rangeOrders','rangeRevenue',
            'topProfitableProductsRange'
        ));
    }

    public function notifications()
    {
        $user = Auth::user();
        $notifications = $user->notifications()->latest()->take(15)->get();
        $unreadCount = $user->unreadNotifications()->count();

        return response()->json([
            'notifications' => $notifications,
            'unread_count' => $unreadCount,
        ]);
    }

    public function markNotificationAsRead(Request $request)
    {
        $user = Auth::user();
        $notificationId = $request->input('id');

        if ($notificationId) {
            $notification = $user->notifications()->find($notificationId);
            if ($notification) {
                $notification->markAsRead();
                return response()->json(['success' => true]);
            }
        }
        return response()->json(['success' => false], 404);
    }

    public function markAllNotificationsAsRead()
    {
        Auth::user()->unreadNotifications->markAsRead();
        return response()->json(['success' => true]);
    }
}
