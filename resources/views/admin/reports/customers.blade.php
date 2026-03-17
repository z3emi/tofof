@extends('admin.layout')
@section('title', 'تقارير العملاء')

@push('styles')
<style>
    :root {
        --bg-light: #f9f5f1;
        --color-primary: #cd8985;
        --color-secondary: #dcaca9;
        --color-accent: #be6661;
        --color-white: #ffffff;
        --color-beige: #eadbcd;
    }

    .stat-card-customers {
        background: linear-gradient(135deg, var(--bg-light), var(--color-beige));
        border: 1px solid var(--color-beige);
        transition: 0.3s;
    }

    .stat-card-customers:hover {
        background: linear-gradient(135deg, var(--color-beige), var(--bg-light));
        transform: translateY(-4px);
        box-shadow: 0 6px 20px rgba(0, 0, 0, 0.05);
    }

    .stat-card-customers .card-header {
        background-color: var(--color-white);
        color: var(--color-primary);
        border-bottom: 1px solid var(--color-beige);
    }

    .stat-card-customers .card-body .badge {
        background-color: var(--color-secondary);
        color: var(--color-white);
        font-weight: 500;
        font-size: 0.9rem;
    }

    .filter-form {
        background-color: var(--color-white);
        border: 1px solid var(--color-secondary);
        border-radius: 8px;
        padding: 0.5rem;
    }

    .filter-form button {
        background-color: var(--color-primary);
        color: var(--color-white);
        border: none;
    }

    .table-hover tbody tr:hover {
        background-color: rgba(205, 137, 133, 0.05);
    }
</style>
@endpush

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0 fw-bold">تقارير العملاء</h1>
        <form method="GET" action="{{ route('admin.reports.customers') }}" class="d-flex gap-2 align-items-center filter-form">
            <select name="month" class="form-select form-select-sm">
                @for ($m = 1; $m <= 12; $m++)
                    <option value="{{ $m }}" {{ ($month ?? now()->month) == $m ? 'selected' : '' }}>
                        {{ date('F', mktime(0, 0, 0, $m, 10)) }}
                    </option>
                @endfor
            </select>
            <select name="year" class="form-select form-select-sm">
                @for ($y = date('Y'); $y >= date('Y') - 5; $y--)
                    <option value="{{ $y }}" {{ ($year ?? now()->year) == $y ? 'selected' : '' }}>
                        {{ $y }}
                    </option>
                @endfor
            </select>
            <button type="submit" class="btn btn-sm">
                <i class="bi bi-search me-1"></i> تطبيق
            </button>
            <a href="{{ route('admin.reports.customers.export', ['month' => ($month ?? now()->month), 'year' => ($year ?? now()->year)]) }}" class="btn btn-sm btn-success" title="تصدير Excel" aria-label="تصدير Excel">
                <i class="bi bi-file-earmark-excel"></i>
            </a>
        </form>
    </div>

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
@endsection