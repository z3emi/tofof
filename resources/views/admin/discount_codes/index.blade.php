@extends('admin.layout')

@section('title', 'إدارة أكواد الخصم')

@push('styles')
<style>
    .form-card { border-radius: 0 !important; border: none !important; box-shadow: none !important; background: #fff; width: 100% !important; margin: 0 !important; }
    .form-card-header { background: linear-gradient(135deg, var(--primary-dark) 0%, var(--primary-medium) 100%); padding: 2.5rem 3rem; color: white; border-radius: 0 !important; }
    .table-container { border-radius: 15px; border: 1px solid #f1f5f9; overflow: hidden; background: #fff; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05); }
    .search-input { border-radius: 12px; border: 1px solid #e2e8f0; padding: 0.8rem 1.2rem; background: #fafbff; }
    code { background: rgba(109, 14, 22, 0.05); color: var(--primary-dark); padding: 0.3rem 0.6rem; border-radius: 8px; font-weight: 700; border: 1px dashed rgba(109, 14, 22, 0.2); }
</style>
@endpush

@section('content')
<div class="form-card">
    <div class="form-card-header d-flex justify-content-between align-items-center flex-wrap gap-3">
        <div>
            <h2 class="mb-2 fw-bold text-white"><i class="bi bi-percent me-2"></i> إدارة كوبونات الخصم</h2>
            <p class="mb-0 opacity-75 fs-6 text-white small">إنشاء وإدارة حملات الخصم والعروض الترويجية في المتجر.</p>
        </div>
        <div class="d-flex gap-2">
            <div class="col-toggle-place"></div>
            @can('view-discount-codes')
                <a href="{{ route('admin.discount-codes.export', request()->all()) }}" class="btn btn-outline-info p-2 d-inline-flex align-items-center justify-content-center" style="width:40px; height:40px; border-radius:10px" title="تصدير إكسل"><i class="bi bi-file-earmark-excel"></i></a>
                <a href="{{ route('admin.discount-codes.trash') }}" class="btn btn-outline-danger p-2 d-inline-flex align-items-center justify-content-center" style="width:40px; height:40px; border-radius:10px" title="المهملات"><i class="bi bi-trash"></i></a>
            @endcan
            @can('create-discount-codes')
                <a href="{{ route('admin.discount-codes.create') }}" class="btn btn-light px-4 fw-bold text-brand d-inline-flex align-items-center"><i class="bi bi-plus-circle me-1"></i> إنشاء كود</a>
            @endcan
        </div>
    </div>
    
    <div class="p-4 p-lg-5">
        <form method="GET" action="{{ route('admin.discount-codes.index') }}" class="row g-3 mb-4 p-4 bg-light rounded-4 border align-items-end">
            <div class="col-md-7">
                <label class="small fw-bold text-muted mb-2">بحث سريع في الأكواد</label>
                <input type="text" name="search" class="form-control search-input" placeholder="أدخل رمز الكوبون..." value="{{ request('search') }}">
            </div>
            <div class="col-md-5 d-flex gap-2">
                <button type="submit" class="btn text-white px-4 py-3 fw-bold flex-grow-1" style="background:var(--primary-dark); border-radius:12px">بحث</button>
                <button type="button" class="btn btn-outline-dark d-flex align-items-center justify-content-center" style="width:58px; height:58px; border-radius:12px" data-bs-toggle="modal" data-bs-target="#filtersModal" title="فلاتر إضافية">
                    <i class="bi bi-funnel fs-4"></i>
                </button>
                @if(request()->anyFilled(['search','type','status','date_from','date_to']))
                    <a href="{{ route('admin.discount-codes.index') }}" class="btn btn-outline-secondary d-flex align-items-center justify-content-center" style="width:58px; height:58px; border-radius:12px" title="تصفير">
                        <i class="bi bi-arrow-counterclockwise fs-4"></i>
                    </a>
                @endif
            </div>
        </form>

        <div class="table-container shadow-sm border overflow-hidden">
            <table class="table mb-0 align-middle text-center" id="coupons_table">
                <thead class="bg-light border-bottom">
                    <tr class="text-muted small fw-bold">
                        <th class="py-3" width="50" data-column-id="seq">{!! \App\Support\Sort::link('id', '#') !!}</th>
                        <th class="py-3" width="70" data-hide="true" data-column-id="id">ID</th>
                        <th class="py-3" data-column-id="code">رمز الكوبون</th>
                        <th class="py-3" data-column-id="type">نوع الخصم</th>
                        <th class="py-3" data-column-id="value">القيمة</th>
                        <th class="py-3" data-column-id="usage">الاستخدام</th>
                        <th class="py-3" data-column-id="status">الحالة</th>
                        <th class="py-3" width="120" data-column-id="actions">الإجراءات</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($discountCodes as $code)
                        @php $used = (int)($code->usages_count ?: 0); $max = (int)($code->max_uses ?: 0); @endphp
                        <tr>
                            <td class="small text-muted">{{ $loop->iteration + ($discountCodes->perPage() * ($discountCodes->currentPage() - 1)) }}</td>
                            <td class="fw-bold">#{{ $code->id }}</td>
                            <td><code>{{ $code->code }}</code></td>
                            <td>
                                @if($code->type == 'fixed') <span class="badge bg-soft-brand text-brand border border-brand border-opacity-10 px-3 py-2 rounded-pill">مبلغ ثابت</span>
                                @else <span class="badge bg-soft-success text-success border border-success border-opacity-10 px-3 py-2 rounded-pill">نسبة مئوية</span> @endif
                            </td>
                            <td>
                                <div class="fw-bold fs-6">
                                    @if($code->type == 'fixed') {{ number_format($code->value, 0) }} د.ع
                                    @else {{ $code->value }}% @endif
                                </div>
                            </td>
                            <td>
                                <div class="small fw-bold mb-1">{{ $used }} / {{ $max ?: '∞' }}</div>
                                @if($max > 0)
                                    <div class="progress mx-auto" style="height:6px; width:70px; border-radius:10px; background:#f1f5f9">
                                        <div class="progress-bar bg-brand" role="progressbar" style="width: {{ ($used/$max)*100 }}%; border-radius:10px"></div>
                                    </div>
                                @endif
                            </td>
                            <td>
                                @if(!$code->is_active) <span class="badge bg-secondary text-white rounded-pill px-3 py-2">موقف</span>
                                @elseif($code->expires_at && $code->expires_at->isPast()) <span class="badge bg-danger text-white rounded-pill px-3 py-2">منتهي</span>
                                @else <span class="badge bg-success text-white rounded-pill px-3 py-2">نشط</span> @endif
                            </td>
                            <td>
                                <div class="btn-group">
                                    @can('edit-discount-codes')
                                        <a href="{{ route('admin.discount-codes.edit', $code->id) }}" class="btn btn-sm btn-outline-info rounded-3 px-2 me-1 text-dark" title="تعديل"><i class="bi bi-pencil"></i></a>
                                        <form action="{{ route('admin.discount-codes.toggleStatus', $code->id) }}" method="POST">@csrf<button type="submit" class="btn btn-sm {{ $code->is_active ? 'btn-outline-warning' : 'btn-outline-success' }} rounded-3 px-2 me-1" title="{{ $code->is_active ? 'إيقاف' : 'تفعيل' }}"><i class="bi {{ $code->is_active ? 'bi-pause' : 'bi-play' }}"></i></button></form>
                                    @endcan
                                    @can('delete-discount-codes')
                                        <form action="{{ route('admin.discount-codes.destroy', $code->id) }}" method="POST" onsubmit="return confirm('هل أنت متأكد من حذف هذا الكوبون؟')">@csrf @method('DELETE')<button type="submit" class="btn btn-sm btn-outline-danger rounded-3 px-2" title="حذف"><i class="bi bi-trash"></i></button></form>
                                    @endcan
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="8" class="py-5 text-muted">لم يتم العثور على أي أكواد خصم.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-4 d-flex justify-content-between align-items-center flex-wrap gap-3">
            <form method="GET" action="{{ route('admin.discount-codes.index') }}" class="d-flex align-items-center bg-light p-2 rounded-3 border">
                @foreach(request()->except(['per_page','page']) as $k=>$v) <input type="hidden" name="{{ $k }}" value="{{ $v }}"> @endforeach
                <span class="small text-muted me-2 ms-2">إظهار:</span>
                <select name="per_page" class="form-select form-select-sm border-0 bg-transparent fw-bold" onchange="this.form.submit()" style="width:70px">
                    @foreach([10,25,50,100] as $s) <option value="{{$s}}" @selected(request('per_page',10)==$s)>{{$s}}</option> @endforeach
                </select>
            </form>
            <div>{{ $discountCodes->withQueryString()->links() }}</div>
        </div>
    </div>
</div>

{{-- Filters Modal --}}
<div class="modal fade" id="filtersModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg" style="border-radius:20px">
            <div class="modal-header border-0 p-4 pb-0">
                <h5 class="fw-bold mb-0"><i class="bi bi-funnel-fill me-2 text-brand"></i> فلاتر البحث المتقدمة</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <form method="GET" action="{{ route('admin.discount-codes.index') }}">
                    <input type="hidden" name="search" value="{{ request('search') }}">
                    <div class="row g-4">
                        <div class="col-md-6">
                            <label class="form-label small fw-bold text-muted">نوع الخصم</label>
                            <select name="type" class="form-select search-input">
                                <option value="">الكل</option>
                                <option value="percentage" @selected(request('type')=='percentage')>نسبة مئوية</option>
                                <option value="fixed" @selected(request('type')=='fixed')>مبلغ ثابت</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold text-muted">الحالة</label>
                            <select name="status" class="form-select search-input">
                                <option value="">كل الحالات</option>
                                <option value="active" @selected(request('status')=='active')>نشط</option>
                                <option value="inactive" @selected(request('status')=='inactive')>موقف</option>
                                <option value="expired" @selected(request('status')=='expired')>منتهي</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold text-muted">تاريخ الانتهاء (من)</label>
                            <input type="date" name="date_from" class="form-control" style="border-radius:10px" value="{{ request('date_from') }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold text-muted">تاريخ الانتهاء (إلى)</label>
                            <input type="date" name="date_to" class="form-control" style="border-radius:10px" value="{{ request('date_to') }}">
                        </div>
                        <div class="col-12 mt-4">
                            <button type="submit" class="btn text-white w-100 py-3 fw-bold" style="background:var(--primary-dark); border-radius:12px">تطبيق الفلتر</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
