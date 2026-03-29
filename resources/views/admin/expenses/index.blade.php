@extends('admin.layout')

@section('title', 'إدارة المصاريف')

@push('styles')
<style>
    .form-card { border-radius: 0 !important; border: none !important; box-shadow: none !important; background: #fff; width: 100% !important; margin: 0 !important; }
    .form-card-header { background: linear-gradient(135deg, var(--primary-dark) 0%, var(--primary-medium) 100%); padding: 2.5rem 3rem; color: white; border-radius: 0 !important; }
    .table-container { border-radius: 15px; border: 1px solid #f1f5f9; overflow: hidden; background: #fff; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05); }
    .search-input { border-radius: 12px; border: 1px solid #e2e8f0; padding: 0.8rem 1.2rem; background: #fafbff; }
</style>
@endpush

@section('content')
<div class="form-card">
    <div class="form-card-header d-flex justify-content-between align-items-center flex-wrap gap-3">
        <div>
            <h2 class="mb-2 fw-bold text-white"><i class="bi bi-wallet2 me-2"></i> سجل المصاريف التشغيلية</h2>
            <p class="mb-0 opacity-75 fs-6 text-white small">متابعة النفقات، الإيجارات، وتكاليف التشغيل الشهرية والسنوية.</p>
        </div>
        <div>
            @can('create-expenses')
                <a href="{{ route('admin.expenses.create') }}" class="btn btn-light px-4 fw-bold text-brand"><i class="bi bi-plus-circle me-1"></i> تسجيل مصروف</a>
            @endcan
        </div>
    </div>
    
    <div class="p-4 p-lg-5">
        <form method="GET" action="{{ route('admin.expenses.index') }}" class="row g-3 mb-4 p-4 bg-light rounded-4 border align-items-end">
            <div class="col-md-8">
                <label class="small fw-bold text-muted mb-2">بحث سريع في المصاريف</label>
                <input type="text" name="search" class="form-control search-input" placeholder="بحث بالبيان أو الوصف..." value="{{ request('search') }}">
            </div>
            <div class="col-md-4 d-flex gap-2">
                <button type="submit" class="btn text-white px-4 py-3 fw-bold flex-grow-1" style="background:var(--primary-dark); border-radius:12px">بحث</button>
                <button type="button" class="btn btn-outline-dark d-flex align-items-center justify-content-center" style="width:58px; height:58px; border-radius:12px" data-bs-toggle="modal" data-bs-target="#filtersModal" title="تصفية متقدمة">
                    <i class="bi bi-funnel fs-4"></i>
                </button>
                @if(request()->anyFilled(['search','month','year','min_amount','max_amount','date_from','date_to']))
                    <a href="{{ route('admin.expenses.index') }}" class="btn btn-outline-secondary d-flex align-items-center justify-content-center" style="width:58px; height:58px; border-radius:12px" title="تصفير">
                        <i class="bi bi-arrow-counterclockwise fs-4"></i>
                    </a>
                @endif
            </div>
        </form>

        <div class="table-container shadow-sm border overflow-hidden">
            <table class="table mb-0 align-middle text-center">
                <thead class="bg-light border-bottom">
                    <tr class="text-muted small fw-bold">
                        <th class="py-3" width="120">التاريخ</th>
                        <th class="py-3 text-start">البيان / الوصف</th>
                        <th class="py-3">المبلغ (د.ع)</th>
                        <th class="py-3">بواسطة</th>
                        <th class="py-3" width="120">الإجراءات</th>
                    </tr>
                </thead>
                <tbody>
                    @php $sum = 0; @endphp
                    @forelse($expenses as $expense)
                        @php $sum += $expense->amount; @endphp
                        <tr>
                            <td class="small text-muted">{{ $expense->expense_date->format('Y-m-d') }}</td>
                            <td class="text-start fw-bold text-dark">{{ $expense->title ?? $expense->description }}</td>
                            <td><span class="badge bg-danger bg-opacity-10 text-danger border border-danger border-opacity-25 px-3 py-2 fw-bold">{{ number_format($expense->amount, 0) }} د.ع</span></td>
                            <td class="small">{{ $expense->manager->name ?? '---' }}</td>
                            <td>
                                <div class="d-flex justify-content-center gap-1">
                                    <a href="{{ route('admin.expenses.edit', $expense->id) }}" class="btn btn-sm btn-outline-primary rounded-3 px-2 py-1"><i class="bi bi-pencil"></i></a>
                                    @can('delete-expenses')
                                        <form action="{{ route('admin.expenses.destroy', $expense->id) }}" method="POST" onsubmit="return confirm('حذف المصروف؟')">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-outline-danger rounded-3 px-2 py-1"><i class="bi bi-trash"></i></button>
                                        </form>
                                    @endcan
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="py-5 text-muted">لا يوجد مصاريف مسجلة لهذه الفترة.</td></tr>
                    @endforelse
                </tbody>
                @if($expenses->isNotEmpty())
                    <tfoot class="bg-light border-top">
                        <tr class="fw-bold">
                            <td colspan="2" class="text-end py-3 ps-4">إجمالي المصاريف:</td>
                            <td colspan="3" class="text-start text-danger py-3 fs-5">{{ number_format($sum, 0) }} د.ع</td>
                        </tr>
                    </tfoot>
                @endif
            </table>
        </div>

        <div class="mt-4 d-flex justify-content-between align-items-center flex-wrap gap-3">
            <form method="GET" class="d-flex align-items-center bg-light p-2 rounded-3 border">
                @foreach(request()->except(['per_page','page']) as $k=>$v) <input type="hidden" name="{{ $k }}" value="{{ $v }}"> @endforeach
                <span class="small text-muted me-2 ms-2">إظهار:</span>
                <select name="per_page" class="form-select form-select-sm border-0 bg-transparent fw-bold" onchange="this.form.submit()" style="width:70px">
                    @foreach([10,25,50,100] as $s) <option value="{{$s}}" @selected(request('per_page',10)==$s)>{{$s}}</option> @endforeach
                </select>
            </form>
            <div>{{ $expenses->withQueryString()->links() }}</div>
        </div>
    </div>
</div>

{{-- Filters Modal --}}
<div class="modal fade" id="filtersModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg" style="border-radius:20px">
            <div class="modal-header border-0 p-4 pb-0">
                <h5 class="fw-bold mb-0"><i class="bi bi-funnel me-2"></i>تصفية المصاريف المتقدمة</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <form method="GET" action="{{ route('admin.expenses.index') }}">
                    <input type="hidden" name="search" value="{{ request('search') }}">
                    <div class="row g-4">
                        <div class="col-md-6">
                            <label class="small fw-bold text-muted mb-2">الشهر</label>
                            <select name="month" class="form-select search-input">
                                <option value="">اختر الشهر</option>
                                @for ($m = 1; $m <= 12; $m++)
                                    <option value="{{ $m }}" @selected(($month ?? date('m')) == $m)>{{ date('F', mktime(0, 0, 0, $m, 10)) }}</option>
                                @endfor
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="small fw-bold text-muted mb-2">السنة</label>
                            <select name="year" class="form-select search-input">
                                @for ($y = date('Y'); $y >= date('Y') - 5; $y--)
                                    <option value="{{ $y }}" @selected(($year ?? date('Y')) == $y)>{{ $y }}</option>
                                @endfor
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="small fw-bold text-muted mb-2">المبلغ (من - إلى)</label>
                            <div class="input-group">
                                <input type="number" name="min_amount" class="form-control search-input" placeholder="الأدنى" value="{{ request('min_amount') }}">
                                <input type="number" name="max_amount" class="form-control search-input" placeholder="الأعلى" value="{{ request('max_amount') }}">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="small fw-bold text-muted mb-2">تاريخ مخصص (من)</label>
                            <input type="date" name="date_from" class="form-control search-input" value="{{ request('date_from') }}">
                        </div>
                        <div class="col-md-6">
                            <label class="small fw-bold text-muted mb-2">تاريخ مخصص (إلى)</label>
                            <input type="date" name="date_to" class="form-control search-input" value="{{ request('date_to') }}">
                        </div>
                    </div>
                    <div class="mt-5 d-flex gap-2">
                        <button type="submit" class="btn text-white w-100 py-3 fw-bold" style="background:var(--primary-dark); border-radius:12px">تطبيق الفلاتر</button>
                        <a href="{{ route('admin.expenses.index') }}" class="btn btn-outline-secondary px-4 py-3" style="border-radius:12px">تصفير الكل</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
