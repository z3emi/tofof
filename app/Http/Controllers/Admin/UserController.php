<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Carbon\Carbon;
use App\Models\Order;
use App\Models\Customer;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class UserController extends Controller
{
    // The __construct middleware has been removed. Protection is now handled in web.php.

    /**
     * عرض جميع المستخدمين.
     */
    public function index(Request $request)
    {
        $perPage = (int) $request->input('per_page', 10);
        $allowedPageSizes = [5, 10, 25, 50, 100, 250, 500];
        if (! in_array($perPage, $allowedPageSizes, true)) {
            $perPage = 10;
        }

        $query = User::with('roles', 'permissions')
            ->withCount(['orders' => function ($query) {
                // نعد الطلبات المكتملة (مُسلّمة) فقط لغرض الفئات
                $query->where('status', 'delivered');
            }]);

        $allowedSorts = ['id', 'name', 'phone_number', 'orders_count', 'created_at', 'wallet_balance'];
        [$sortBy, $sortDir] = \App\Support\Sort::resolve($request, $allowedSorts, 'id');

        if ($sortBy === 'wallet_balance') {
            $query->orderByRaw('CAST(wallet_balance AS DECIMAL(15,2)) '.$sortDir);
        } else {
            $query->orderBy($sortBy, $sortDir);
        }

        if ($request->filled('search')) {
            $searchTerm = $request->search;
            $query->where(function ($q) use ($searchTerm) {
                $q->where('name', 'like', "%{$searchTerm}%")
                  ->orWhere('phone_number', 'like', "%{$searchTerm}%")
                  ->orWhere('email', 'like', "%{$searchTerm}%");
            });
        }

        if ($request->filled('status')) {
            if ($request->status === 'banned') {
                $query->whereNotNull('banned_at');
            } elseif ($request->status === 'active') {
                $query->whereNull('banned_at')->whereNotNull('phone_verified_at');
            } elseif ($request->status === 'inactive') {
                $query->whereNull('phone_verified_at');
            }
        }

        $users = $query->paginate($perPage)->withQueryString();

        return view('admin.users.index', compact('users', 'sortBy', 'sortDir', 'perPage', 'allowedSorts'));
    }

    public function show(Request $request, User $user)
    {
        $user->load('addresses');
        $query = $user->orders()->latest();

        if ($request->filled('search')) {
            $searchTerm = $request->search;
            $query->where(function ($q) use ($searchTerm) {
                $q->where('id', 'like', "%{$searchTerm}%")
                  ->orWhere('status', 'like', "%{$searchTerm}%");
            });
        }

        $perPage = $request->input('per_page', 10);
        $orders = $query->paginate($perPage)->withQueryString();

        $totalOrders = $user->orders()->count();
        $orderCounts = $user->orders()
            ->select('status', DB::raw('count(*) as count'))
            ->groupBy('status')
            ->pluck('count', 'status');
        $deliveredAmount = $user->orders()
            ->where('status', 'delivered')
            ->sum('total_amount');

        // ===== إعداد كشف المحفظة (للصفحة العادية) =====
        $walletView   = $request->input('wallet_view', 'compact'); // compact | detailed | page
        $walletFrom   = $request->input('wallet_from');
        $walletTo     = $request->input('wallet_to');
        $walletType   = $request->input('wallet_type'); // credit|debit|null
        $walletSearch = $request->input('wallet_q');
        $walletPer    = (int) $request->input('wallet_per_page', $walletView === 'detailed' ? 15 : 10);

        // (جديد) فرز المحفظة
        $walletSortBy  = $request->input('wallet_sort_by', 'created_at'); // id|created_at|amount
        $walletSortDir = strtolower($request->input('wallet_sort_dir', 'desc')) === 'asc' ? 'asc' : 'desc';
        $allowedSorts  = ['id', 'created_at', 'amount'];
        if (!in_array($walletSortBy, $allowedSorts, true)) {
            $walletSortBy = 'created_at';
        }

        $walletTotals = (object)[ 'credits' => 0.0, 'debits' => 0.0, 'net' => 0.0 ];

        $txQ = $user->walletTransactions(); 

        if ($walletFrom)   $txQ->whereDate('created_at', '>=', $walletFrom);
        if ($walletTo)     $txQ->whereDate('created_at', '<=', $walletTo);
        if ($walletType && in_array($walletType, ['credit','debit'], true)) $txQ->where('type', $walletType);
        if ($walletSearch) $txQ->where('description', 'like', '%'.$walletSearch.'%');

        // (جديد) الفرز حسب البراميترات
        if ($walletSortBy === 'amount') {
            $txQ->orderByRaw('CAST(amount AS DECIMAL(15,2)) '.$walletSortDir);
        } else {
            $txQ->orderBy($walletSortBy, $walletSortDir);
        }

        $walletTransactions = $txQ->paginate($walletPer, ['*'], 'wallet_page')->withQueryString();

        $totalsQ = $user->walletTransactions()->select('type', DB::raw('SUM(amount) as s'));
        if ($walletFrom)   $totalsQ->whereDate('created_at', '>=', $walletFrom);
        if ($walletTo)     $totalsQ->whereDate('created_at', '<=', $walletTo);
        if ($walletType && in_array($walletType, ['credit','debit'], true)) $totalsQ->where('type', $walletType);
        if ($walletSearch) $totalsQ->where('description', 'like', '%'.$walletSearch.'%');
        $totals = $totalsQ->groupBy('type')->pluck('s', 'type');

        $walletTotals->credits = (float)($totals['credit'] ?? 0);
        $walletTotals->debits  = (float)($totals['debit']  ?? 0);
        $walletTotals->net     = $walletTotals->credits - $walletTotals->debits;

        $wallet_balance = (float)($user->wallet_balance ?? 0);

        // ===== الصفحة التفصيلية (بدون روت جديد) =====
        if ($walletView === 'page') {
            return view('admin.users.wallet', compact(
                'user',
                'walletTransactions',
                'walletTotals',
                'wallet_balance',
                'walletFrom','walletTo','walletType','walletSearch','walletPer'
            ));
        }

        return view('admin.users.show', compact(
            'user',
            'orders',
            'totalOrders',
            'orderCounts',
            'deliveredAmount',
            'walletView','walletFrom','walletTo','walletType','walletSearch','walletPer',
            'walletTransactions','walletTotals','wallet_balance'
        ));
    }

    public function create()
    {
        $governorates = $this->iraqiGovernorates();
        return view('admin.users.create', compact('governorates'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'         => 'required|string|max:255',
            'phone_number' => 'required|string|unique:users,phone_number',
            'email'        => 'nullable|string|email|max:255|unique:users,email',
            'password'     => 'required|string|min:8|confirmed',
            'avatar'       => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
            'governorate'  => 'nullable|string|max:255',
            'city'         => 'nullable|string|max:255',
            'address'      => 'nullable|string|max:255',
            'latitude'     => 'nullable|numeric',
            'longitude'    => 'nullable|numeric',
        ]);

        $data = $request->only('name', 'phone_number', 'email', 'governorate', 'city', 'address', 'latitude', 'longitude');
        $data['password'] = Hash::make($request->password);

        if ($request->hasFile('avatar')) {
            $data['avatar'] = $request->file('avatar')->store('avatars', 'public');
        }

        $user = User::create($data);

        // قم بإنشاء العميل لكي يظهر للمدير في صفحة الطلبات في حال كان مفعلا أو سيتم تفعيله لاحقا.
        Customer::updateOrCreate(
            ['phone_number' => $user->phone_number],
            [
                'user_id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'governorate' => $user->governorate,
                'city' => $user->city,
                'address_details' => $user->address
            ]
        );

        return redirect()->route('admin.users.index')->with('success', 'تم إنشاء المستخدم بنجاح.');
    }

    public function edit(User $user)
    {
        $governorates = $this->iraqiGovernorates();
        return view('admin.users.edit', compact('user', 'governorates'));
    }

public function update(Request $request, User $user)
{
    $request->validate([
        'name'         => 'required|string|max:255',
        'email'        => 'nullable|string|email|max:255|unique:users,email,' . $user->id,
        'phone_number' => 'required|string|max:20|unique:users,phone_number,' . $user->id,
        'password'     => 'nullable|string|min:8|confirmed',
        'avatar'       => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
        'reset_avatar' => 'nullable|boolean',
        'governorate'  => 'nullable|string|max:255',
        'city'         => 'nullable|string|max:255',
        'address'      => 'nullable|string|max:255',
        'latitude'     => 'nullable|numeric',
        'longitude'    => 'nullable|numeric',
    ]);

    $data = $request->only('name', 'email', 'phone_number', 'governorate', 'city', 'address', 'latitude', 'longitude');

    if ($request->filled('password')) {
        $data['password'] = Hash::make($request->password);
    }

    // معالجة الصورة
    if ($request->boolean('reset_avatar')) {
        $data['avatar'] = null;
    } elseif ($request->hasFile('avatar')) {
        $path = $request->file('avatar')->store('avatars', 'public');
        $data['avatar'] = $path;
    }

    $user->update($data);

    return redirect()->route('admin.users.index')->with('success', 'تم تحديث بيانات المستخدم بنجاح.');
}
    
    public function showUserOrders(User $user)
    {
        $orders = $user->orders()->latest()->paginate(15);
        return view('admin.users.orders', compact('user', 'orders'));
    }

    public function ban(User $user)
    {
        if ($user->id === Auth::guard('web')->id()) {
            return redirect()->back()->with('error', 'لا يمكنك حظر حسابك الخاص.');
        }
        $user->update(['banned_at' => Carbon::now()]);
        DB::table('sessions')->where('user_id', $user->id)->delete();
        return redirect()->route('admin.users.index')->with('success', 'تم حظر المستخدم بنجاح.');
    }

    public function unban(User $user)
    {
        $user->update(['banned_at' => null]);
        return redirect()->route('admin.users.index')->with('success', 'تم إلغاء حظر المستخدم بنجاح.');
    }

    public function directActivate(User $user)
    {
        if (is_null($user->phone_verified_at)) {
            $user->update([
                'phone_verified_at' => Carbon::now()
            ]);

            // التأكد من أن المستخدم لديه سجل كـ Customer ليظهر في واجهة إضافة طلب جديد
            Customer::updateOrCreate(
                ['phone_number' => $user->phone_number],
                [
                    'user_id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'governorate' => $user->governorate,
                    'city' => $user->city,
                    'address_details' => $user->address
                ]
            );

            return redirect()->back()->with('success', 'تم تفعيل المستخدم بنجاح.');
        }
        return redirect()->back()->with('info', 'المستخدم مفعل مسبقاً.');
    }
    
    public function destroy(User $user)
    {
        if ($user->id === Auth::guard('web')->id()) {
            return redirect()->back()->with('error', 'لا يمكنك حذف حسابك الخاص.');
        }
        $user->delete();
        return redirect()->route('admin.users.index')->with('success', 'تم نقل المستخدم إلى سلة المحذوفات.');
    }

    public function trash(Request $request)
    {
        $perPage = (int) $request->input('per_page', 10);
        
        $query = User::onlyTrashed();

        $allowedSorts = ['id', 'name', 'phone_number', 'deleted_at'];
        [$sortBy, $sortDir] = \App\Support\Sort::resolve($request, $allowedSorts, 'deleted_at');
        
        $query->orderBy($sortBy, $sortDir);

        $users = $query->paginate($perPage)->withQueryString();
        
        return view('admin.users.trash', compact('users', 'sortBy', 'sortDir', 'perPage'));
    }

    public function restore($id)
    {
        $user = User::onlyTrashed()->findOrFail($id);
        $user->restore();
        return redirect()->back()->with('success', 'تم استرجاع حساب المستخدم بنجاح.');
    }

    public function forceDelete($id)
    {
        $user = User::onlyTrashed()->findOrFail($id);
        
        if ($user->avatar) {
            \Illuminate\Support\Facades\Storage::disk('public')->delete($user->avatar);
        }

        $user->forceDelete();
        return redirect()->back()->with('success', 'تم حذف المستخدم نهائيًا.');
    }
    
    public function inactive(Request $request)
    {
        $perPage = (int) $request->input('per_page', 20);
        $query = User::whereNull('phone_verified_at');

        $allowedSorts = ['id', 'name', 'phone_number', 'created_at'];
        [$sortBy, $sortDir] = \App\Support\Sort::resolve($request, $allowedSorts, 'created_at');

        $query->orderBy($sortBy, $sortDir);

        $inactiveUsers = $query->paginate($perPage)->withQueryString();

        return view('admin.users.inactive', compact('inactiveUsers', 'sortBy', 'sortDir', 'perPage'));
    }

    public function forceLogout(User $user)
    {
        DB::table('sessions')->where('user_id', $user->id)->delete();
        return back()->with('success', 'تم تسجيل خروج المستخدم بنجاح.');
    }

    public function forceLogoutAll()
    {
        DB::table('sessions')->whereNotNull('user_id')->delete();
        return back()->with('success', 'تم تسجيل خروج جميع المستخدمين.');
    }

    public function impersonate(User $user)
    {
        session(['impersonator_id' => auth()->id()]);
        auth()->login($user);
        return redirect('/')->with('success', 'تم تسجيل الدخول كمستخدم آخر.');
    }

    public function stopImpersonate()
    {
        $id = session('impersonator_id');
        if ($id) {
            auth()->loginUsingId($id);
            session()->forget('impersonator_id');
        }
        return redirect()->route('admin.users.index')->with('success', 'تم إيقاف وضع الانتحال.');
    }

    protected function iraqiGovernorates(): array
    {
        return [
            'بغداد', 'نينوى', 'البصرة', 'صلاح الدين', 'دهوك', 'أربيل', 'السليمانية', 'ديالى',
            'واسط', 'ميسان', 'ذي قار', 'المثنى', 'بابل', 'كربلاء', 'النجف', 'الانبار',
            'الديوانية', 'كركوك', 'حلبجة',
        ];
    }
    public function getAddresses($id)
    {
        $user = User::with('addresses')->findOrFail($id);
        return response()->json($user->addresses);
    }
}