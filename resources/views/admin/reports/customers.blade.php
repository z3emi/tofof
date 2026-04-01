@extends('admin.layout')
@section('title', 'تقارير العملاء')

@push('styles')
<style>
    .form-card { border-radius: 0 !important; border: none !important; box-shadow: none !important; background: #fff; width: 100% !important; margin: 0 !important; }
    .form-card-header { background: linear-gradient(135deg, var(--primary-dark) 0%, var(--primary-medium) 100%); padding: 2.5rem 3rem; color: white; border-radius: 0 !important; }
    .search-input { border-radius: 12px; border: 1px solid #e2e8f0; padding: 0.8rem 1.2rem; background: #fafbff; }
    
    .stat-card-customers {
        background: linear-gradient(135deg, #f8f9fa 0%, #eef1f4 100%);
        border: 1px solid #e2e8f0;
        border-radius: 15px;
        transition: all 0.3s ease-in-out;
        box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05);
    }

    .stat-card-customers:hover {
        background: linear-gradient(135deg, #eef1f4, #f8f9fa);
        transform: translateY(-8px);
        box-shadow: 0 12px 25px -5px rgba(0,0,0,0.1);
        border-bottom: 4px solid var(--primary-dark);
    }

    .stat-card-customers .card-header {
        background-color: transparent !important;
        color: var(--primary-dark);
        border-bottom: 2px solid #e2e8f0 !important;
        font-weight: 700;
    }

    .stat-card-customers .card-body .badge {
        background-color: var(--primary-dark);
        color: #fff;
        font-weight: 500;
        font-size: 0.9rem;
    }

    .filter-form {
        background-color: transparent;
        border: none;
        border-radius: 8px;
        padding: 0;
    }

    .filter-form button {
        background-color: transparent;
        color: white;
        border: 1px solid rgba(255,255,255,0.3);
        border-radius: 12px;
        padding: 0.6rem 1.2rem;
    }

    .filter-form button:hover {
        background-color: rgba(255,255,255,0.1);
        border-color: rgba(255,255,255,0.5);
    }

    .table-hover tbody tr:hover {
        background-color: rgba(0, 0, 0, 0.02) !important;
    }

    .table thead {
        background-color: #f8f9fa;
    }

    .table thead th {
        font-weight: 700;
        color: var(--primary-dark);
        border-bottom: 2px solid #e2e8f0;
    }
</style>
@endpush

@section('content')
<div class="form-card">
    <div class="form-card-header d-flex justify-content-between align-items-center flex-wrap gap-3">
        <div>
            <h2 class="mb-2 fw-bold text-white"><i class="bi bi-people-fill me-2"></i> تقارير العملاء والعلاقات</h2>
            <p class="mb-0 opacity-75 fs-6 text-white small">تحليل سلوك العملاء، معدلات الشراء، والعملاء الأكثر قيمة.</p>
        </div>
        <form method="GET" action="{{ route('admin.reports.customers') }}" class="d-flex gap-2 align-items-center filter-form">
            <select name="month" class="form-select form-select-sm search-input" style="width: 150px;">
                @for ($m = 1; $m <= 12; $m++)
                    <option value="{{ $m }}" {{ ($month ?? now()->month) == $m ? 'selected' : '' }}>
                        {{ date('F', mktime(0, 0, 0, $m, 10)) }}
                    </option>
                @endfor
            </select>
            <select name="year" class="form-select form-select-sm search-input" style="width: 120px;">
                @for ($y = date('Y'); $y >= date('Y') - 5; $y--)
                    <option value="{{ $y }}" {{ ($year ?? now()->year) == $y ? 'selected' : '' }}>
                        {{ $y }}
                    </option>
                @endfor
            </select>
            <button type="submit" class="btn" style="background-color: transparent; color: white; border: 1px solid rgba(255,255,255,0.4); border-radius: 12px; padding: 0.6rem 1.2rem;">
                <i class="bi bi-search me-1"></i> تطبيق
            </button>
            <a href="{{ route('admin.reports.customers.export', ['month' => ($month ?? now()->month), 'year' => ($year ?? now()->year)]) }}" class="btn btn-outline-light p-2 d-inline-flex align-items-center justify-content-center" style="width:40px; height:40px; border-radius:10px" title="تصدير إكسل"><i class="bi bi-file-earmark-excel"></i></a>
        </form>
    </div>

    <div class="p-4 p-lg-5">
        <div class="row g-4">
        <div class="col-lg-6">
            <div class="card stat-card-customers shadow-sm">
                <div class="card-header">
                    <h5 class="mb-0"><i class="bi bi-gem"></i> أفضل العملاء (قيمة المشتريات)</h5>
                </div>
                <div class="card-body">
                    @if($topSpenders->isEmpty())
                        <div class="text-center text-muted py-4">
                            <i class="bi bi-exclamation-circle fs-3 mb-2"></i>
                            <p>لا توجد بيانات كافية.</p>
                        </div>
                    @else
                        <ul class="list-group list-group-flush">
                            @foreach($topSpenders as $customer)
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    <div class="d-flex align-items-center">
                                        <img src="https://picsum.photos/seed/{{ $customer->id }}/40/40.jpg" class="rounded-circle me-2" alt="Customer">
                                        <a href="{{ route('admin.users.show', $customer->user_id ?? $customer->id) }}" class="text-decoration-none text-dark">{{ $customer->name }}</a>
                                    </div>
                                    <span class="badge">{{ number_format($customer->orders_sum_total_amount, 0) }} د.ع</span>
                                </li>
                            @endforeach
                        </ul>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-lg-6">
            <div class="card stat-card-customers shadow-sm">
                <div class="card-header">
                    <h5 class="mb-0"><i class="bi bi-trophy-fill"></i> أفضل العملاء (عدد الطلبات)</h5>
                </div>
                <div class="card-body">
                    @if($mostFrequentBuyers->isEmpty())
                        <div class="text-center text-muted py-4">
                            <i class="bi bi-exclamation-circle fs-3 mb-2"></i>
                            <p>لا توجد بيانات كافية.</p>
                        </div>
                    @else
                        <ul class="list-group list-group-flush">
                            @foreach($mostFrequentBuyers as $customer)
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    <div class="d-flex align-items-center">
                                        <img src="https://picsum.photos/seed/{{ $customer->id }}/40/40.jpg" class="rounded-circle me-2" alt="Customer">
                                        <a href="{{ route('admin.users.show', $customer->user_id ?? $customer->id) }}" class="text-decoration-none text-dark">{{ $customer->name }}</a>
                                    </div>
                                    <span class="badge">{{ $customer->orders_count }} طلب</span>
                                </li>
                            @endforeach
                        </ul>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-12">
            <div class="card stat-card-customers shadow-sm">
                <div class="card-header">
                    <h5 class="mb-0"><i class="bi bi-moon-stars-fill"></i> عملاء غير نشطين (آخر 90 يومًا)</h5>
                </div>
                <div class="card-body">
                    @if($inactiveCustomers->isEmpty())
                        <div class="text-center text-muted py-4">
                            <i class="bi bi-check-circle fs-1 mb-2"></i>
                            <p class="fs-5">لا يوجد عملاء غير نشطين حاليًا.</p>
                        </div>
                    @else
                        <div class="table-responsive">
                            <table class="table table-hover align-middle">
                                <thead class="table-light">
                                    <tr>
                                        <th>العميل</th>
                                        <th>رقم الهاتف</th>
                                        <th class="text-center">تاريخ آخر طلب</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($inactiveCustomers as $customer)
                                        <tr>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <img src="https://picsum.photos/seed/{{ $customer->id }}/40/40.jpg" class="rounded-circle me-2" alt="Customer">
                                                    <a href="{{ route('admin.users.show', $customer->user_id ?? $customer->id) }}" class="text-decoration-none text-dark">{{ $customer->name }}</a>
                                                </div>
                                            </td>
                                            <td>{{ $customer->phone_number }}</td>
                                            <td class="text-center">
                                                <span class="badge bg-secondary">{{ optional($customer->orders->max('created_at'))->format('Y-m-d') ?? 'لا يوجد' }}</span>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
    </div>
</div>
@endsection