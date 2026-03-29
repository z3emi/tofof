@php
// === نفس منطق الطلبات: روابط فرز تصاعدي/تنازلي مع آيكون ===
function wallet_sortable($column, $title, $currentSortBy, $currentSortDir, $userId) {
    $sortBy   = request('wallet_sort_by', $currentSortBy);
    $sortDir  = request('wallet_sort_dir', $currentSortDir);
    $newDir   = ($sortBy == $column && $sortDir == 'asc') ? 'desc' : 'asc';
    $icon     = '';
    if ($sortBy == $column) {
        $icon = $sortDir == 'asc' ? '<i class="bi bi-sort-up ms-1"></i>' : '<i class="bi bi-sort-down ms-1"></i>';
    }
    $queryParams = request()->except(['wallet_sort_by', 'wallet_sort_dir', 'page']);
    $queryParams['wallet_sort_by'] = $column;
    $queryParams['wallet_sort_dir'] = $newDir;
    $queryParams['wallet_view'] = 'page';

    return '<a href="' . route('admin.users.show', array_merge(['user'=>$userId], $queryParams)) . '" class="text-decoration-none text-dark">'
         . e($title) . $icon . '</a>';
}

$sortBy  = request('wallet_sort_by', 'created_at');
$sortDir = request('wallet_sort_dir', 'desc');
@endphp

@extends('admin.layout')

@section('title', 'كشف حساب المحفظة - ' . $user->name)

@push('styles')
<style>
    /* ألوان الصفوف مثل صفحة الطلبات: أخضر للإيداع، أحمر للسحب */
    .wallet-row-credit { background-color: #e9f7ef !important; }
    .wallet-row-debit  { background-color: #fdecea !important; }

    /* نفس تنسيق الباجينيشن المعتمد */
    .pagination { justify-content: center !important; gap: .4rem; margin-top: 1rem; }
    .pagination .page-item .page-link {
        background-color: #f9f5f1 !important; color: #be6661 !important; border-color: #be6661 !important;
        font-weight: 600; border-radius: .375rem; transition: background-color .3s, color .3s; box-shadow: none;
    }
    .pagination .page-item .page-link:hover { background-color: #dcaca9 !important; color: #fff !important; border-color: #dcaca9 !important; }
    .pagination .page-item.active .page-link { background-color: #be6661 !important; border-color: #be6661 !important; color: #fff !important; font-weight: 700; }

    /* كروت إحصائيات بسيطة */
    .stat-card { border:0; border-radius:.75rem; }
    /* لوّن الصف بالكامل (كل الخلايا) حسب نوع العملية */
.table tbody tr.wallet-row-credit > th,
.table tbody tr.wallet-row-credit > td {
  background-color: #e9f7ef !important;   /* أخضر فاتح للإيداع */
  border-color: #cbe6d5 !important;
}

.table tbody tr.wallet-row-debit > th,
.table tbody tr.wallet-row-debit > td {
  background-color: #fdecea !important;   /* أحمر فاتح للسحب */
  border-color: #f3c9c5 !important;
}

/* ثبّت اللون عند المرور بالماوس */
.table-hover tbody tr.wallet-row-credit:hover > * {
  background-color: #dff2e8 !important;
}
.table-hover tbody tr.wallet-row-debit:hover > * {
  background-color: #f9dcd8 !important;
}

/* لو عندك تباين نص داخل الصفوف الملونة */
.table tbody tr.wallet-row-credit > td .badge,
.table tbody tr.wallet-row-debit > td .badge {
  filter: none; /* تأكد تبقى الألوان واضحة */
}

</style>
@endpush

@section('content')
<div class="card shadow-sm">
    <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
        <h4 class="mb-0"><i class="bi bi-wallet2 me-2"></i>كشف حساب المحفظة — {{ $user->name }}</h4>
        <div class="d-flex gap-2">
            <a href="{{ route('admin.users.show', $user->id) }}" class="btn btn-sm btn-outline-secondary">
                <i class="bi bi-arrow-right"></i> الرجوع
            </a>
        </div>
    </div>

    <div class="card-body">
        {{-- أعلى الصفحة: رصيد حالي + إجماليات ضمن الفلاتر --}}
        @isset($wallet_balance, $walletTotals)
        <div class="row g-3 mb-3">
            <div class="col-md-4">
                <div class="card stat-card" style="background:#fff3cd;">
                    <div class="card-body">
                        <div class="text-muted small">الرصيد الحالي</div>
                        <div class="h5 m-0 fw-bold">{{ number_format($wallet_balance, 2) }} د.ع</div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card stat-card" style="background:#e9f7ef;">
                    <div class="card-body">
                        <div class="text-muted small">إجمالي الإيداعات (ضمن الفلاتر)</div>
                        <div class="h5 m-0 fw-bold">{{ number_format($walletTotals->credits ?? 0, 2) }} د.ع</div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card stat-card" style="background:#fdecea;">
                    <div class="card-body">
                        <div class="text-muted small">إجمالي السحوبات (ضمن الفلاتر)</div>
                        <div class="h5 m-0 fw-bold">{{ number_format($walletTotals->debits ?? 0, 2) }} د.ع</div>
                    </div>
                </div>
            </div>
        </div>
        @endisset

        {{-- مربع البحث السريع + زر المودال --}}
        <form method="GET" action="{{ route('admin.users.show', $user->id) }}" class="row g-2 mb-4" id="toolbarForm">
            <input type="hidden" name="wallet_view" value="page">
            @foreach(request()->except(['wallet_q','page','wallet_view']) as $k => $v)
                <input type="hidden" name="{{ $k }}" value="{{ $v }}">
            @endforeach

            <div class="col-md-8">
                <input type="text" name="wallet_q" class="form-control" style="border-radius:12px; height:58px" placeholder="ابحث بالملاحظة أو الوصف..." value="{{ request('wallet_q') }}">
            </div>

            <div class="col-md-4 d-flex gap-2">
                <button type="submit" class="btn text-white px-4 fw-bold flex-grow-1" style="background-color:var(--primary-dark); border-radius:12px; height:58px">بحث</button>
                <button type="button" class="btn btn-outline-dark d-flex align-items-center justify-content-center" style="width:58px; height:58px; border-radius:12px" data-bs-toggle="modal" data-bs-target="#filtersModal" title="تصفية الحركات">
                    <i class="bi bi-funnel fs-4"></i>
                </button>
                @if(request()->anyFilled(['wallet_from','wallet_to','wallet_min','wallet_max','wallet_type','wallet_q']))
                    <a href="{{ route('admin.users.show', ['user' => $user->id, 'wallet_view' => 'page']) }}" class="btn btn-outline-secondary d-flex align-items-center justify-content-center" style="width:58px; height:58px; border-radius:12px" title="إعادة تعيين">
                        <i class="bi bi-arrow-counterclockwise fs-4"></i>
                    </a>
                @endif
            </div>
        </form>

        {{-- جدول الحركات --}}
        <div class="table-responsive">
            @php
                // تراكمي لحساب before/after بشكل صحيح (الأحدث ثم الأقدم)
                $runningAfter = $wallet_balance ?? (float)($user->wallet_balance ?? 0);
            @endphp
            <table class="table table-bordered table-hover text-center align-middle" style="min-width:900px;">
                <thead class="table-light">
                    <tr>
                        <th>{!! wallet_sortable('id', '#', $sortBy, $sortDir, $user->id) !!}</th>
                        <th>{!! wallet_sortable('created_at', 'التاريخ', $sortBy, $sortDir, $user->id) !!}</th>
                        <th>النوع</th>
                        <th>{!! wallet_sortable('amount', 'المبلغ', $sortBy, $sortDir, $user->id) !!}</th>
                        <th>الرصيد قبل</th>
                        <th>الرصيد بعد</th>
                        <th>الوصف</th>
                        <th>تمّت بواسطة</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($walletTransactions as $t)
                        @php
                            // استخدم balance_after إن متوفر؛ وإلا خذه من التراكمي
                            $after = is_null($t->balance_after) ? $runningAfter : (float)$t->balance_after;

                            // حساب before حسب النوع
                            if ($t->type === 'credit') {
                                $before = $after - (float)$t->amount;
                            } else {
                                $before = $after + (float)$t->amount;
                            }

                            $byId   = $t->performed_by ?? ($t->user?->id ?? null);
                            $byName = $t->performed_by_name ?? ($t->user?->name ?? null);
                        @endphp
                        <tr class="{{ $t->type === 'credit' ? 'wallet-row-credit' : 'wallet-row-debit' }}">
                            <td>{{ $t->id }}</td>
                            <td>{{ \Carbon\Carbon::parse($t->created_at)->format('Y/m/d H:i:s') }}</td>
                            <td>
                                @if($t->type === 'credit')
                                    <span class="badge bg-success">إيداع</span>
                                @else
                                    <span class="badge bg-danger">سحب</span>
                                @endif
                            </td>
                            <td>{{ number_format($t->amount, 2) }} د.ع</td>
                            <td>{{ number_format($before, 2) }} د.ع</td>
                            <td>{{ number_format($after, 2) }} د.ع</td>
                            <td class="text-start">{{ $t->description ?? '-' }}</td>
                            <td>
                                @if($byId)
                                    ID: {{ $byId }} @if($byName)<div class="small text-muted">{{ $byName }}</div>@endif
                                @else
                                    -
                                @endif
                            </td>
                        </tr>
                        @php
                            // نحدّث التراكمي للحركة الأقدم التالية
                            $runningAfter = $before;
                        @endphp
                    @empty
                        <tr><td colspan="8" class="p-4">لا توجد حركات مطابقة.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- باجينيشن + اختيار عدد السجلات (أسفل يمين) --}}
        <div class="d-flex justify-content-between align-items-center mt-3 flex-wrap gap-2">
            <form method="GET" action="{{ route('admin.users.show', $user->id) }}" class="d-flex align-items-center">
                <input type="hidden" name="wallet_view" value="page">
                @foreach(request()->except(['wallet_per_page','page','wallet_view']) as $key => $value)
                    <input type="hidden" name="{{ $key }}" value="{{ $value }}">
                @endforeach
                <label for="wallet_per_page" class="me-2">عدد السجلات:</label>
                <select name="wallet_per_page" id="wallet_per_page" class="form-select form-select-sm" onchange="this.form.submit()">
                    @foreach([5,10,25,50,100] as $size)
                        <option value="{{ $size }}" {{ request('wallet_per_page', 10) == $size ? 'selected' : '' }}>
                            {{ $size }}
                        </option>
                    @endforeach
                </select>
            </form>
            <div>{{ $walletTransactions->withQueryString()->links() }}</div>
        </div>
    </div>
</div>

{{-- Modal: فلاتر منبثقة (نفس ستايل الطلبات) --}}
<div class="modal fade" id="filtersModal" tabindex="-1" aria-labelledby="filtersModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header" style="background-color:#cd8985;color:#fff;">
        <h5 class="modal-title" id="filtersModalLabel">تصفية الحركات</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <form method="GET" action="{{ route('admin.users.show', $user->id) }}" class="row g-3" id="modalFiltersForm">
            <input type="hidden" name="wallet_view" value="page">
            {{-- احتفظ بكل البراميترات الحالية عدا ما سنعدله هنا --}}
            @foreach(request()->except(['wallet_from','wallet_to','wallet_min','wallet_max','wallet_type','wallet_q','wallet_sort_by','wallet_sort_dir','page','wallet_view']) as $k => $v)
                <input type="hidden" name="{{ $k }}" value="{{ $v }}">
            @endforeach

            <div class="col-md-6">
                <label class="form-label">من تاريخ</label>
                <input type="date" name="wallet_from" class="form-control" value="{{ request('wallet_from') }}">
            </div>
            <div class="col-md-6">
                <label class="form-label">إلى تاريخ</label>
                <input type="date" name="wallet_to" class="form-control" value="{{ request('wallet_to') }}">
            </div>

            <div class="col-md-6">
                <label class="form-label">المبلغ الأدنى</label>
                <input type="number" step="any" name="wallet_min" class="form-control" placeholder="مثال: 1000" value="{{ request('wallet_min') }}">
            </div>
            <div class="col-md-6">
                <label class="form-label">المبلغ الأعلى</label>
                <input type="number" step="any" name="wallet_max" class="form-control" placeholder="مثال: 50000" value="{{ request('wallet_max') }}">
            </div>

            <div class="col-md-6">
                <label class="form-label">النوع</label>
                <select name="wallet_type" class="form-select">
                    <option value="">الكل</option>
                    <option value="credit" {{ request('wallet_type')=='credit' ? 'selected' : '' }}>إيداع</option>
                    <option value="debit"  {{ request('wallet_type')=='debit'  ? 'selected' : '' }}>سحب</option>
                </select>
            </div>

            <div class="col-md-6">
                <label class="form-label">بحث في الوصف</label>
                <input type="text" name="wallet_q" class="form-control" placeholder="مثال: طلب #123" value="{{ request('wallet_q') }}">
            </div>

            <div class="col-md-6">
                <label class="form-label">فرز حسب</label>
                <select name="wallet_sort_by" class="form-select">
                    <option value="created_at" {{ $sortBy=='created_at' ? 'selected':'' }}>التاريخ</option>
                    <option value="amount"     {{ $sortBy=='amount'     ? 'selected':'' }}>المبلغ</option>
                    <option value="id"         {{ $sortBy=='id'         ? 'selected':'' }}>رقم الحركة</option>
                </select>
            </div>
            <div class="col-md-6">
                <label class="form-label">الاتجاه</label>
                <select name="wallet_sort_dir" class="form-select">
                    <option value="desc" {{ $sortDir=='desc' ? 'selected':'' }}>تنازلي</option>
                    <option value="asc"  {{ $sortDir=='asc'  ? 'selected':'' }}>تصاعدي</option>
                </select>
            </div>

            <div class="col-12 mt-2">
                <button type="submit" class="btn w-100" style="background-color:#cd8985;color:#fff;border-color:#cd8985;">
                    <i class="bi bi-funnel" style="color:#000;"></i> تطبيق الفلاتر
                </button>
            </div>
            <div class="col-12">
                <a href="{{ route('admin.users.show', ['user' => $user->id, 'wallet_view' => 'page']) }}" class="btn btn-outline-secondary w-100">إعادة تعيين</a>
            </div>
        </form>
      </div>
    </div>
  </div>
</div>
@endsection
