@extends('admin.layout')

@section('title', 'المستخدمون غير المفعلين')

@push('styles')
<style>
    .form-card { border-radius: 0 !important; border: none !important; box-shadow: none !important; background: #fff; width: 100% !important; margin: 0 !important; }
    .form-card-header { background: linear-gradient(135deg, var(--primary-dark) 0%, var(--primary-medium) 100%); padding: 2.5rem 3rem; color: white; border-radius: 0 !important; }
    .table-container { border-radius: 15px; border: 1px solid #f1f5f9; overflow: hidden; background: #fff; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05); }
    .search-input { border-radius: 12px; border: 1px solid #e2e8f0; padding: 0.8rem 1.2rem; background: #fafbff; }
    .otp-code { letter-spacing: 1px; font-size: 1rem; }
    .inactive-row { background: #fffaf0; }
    .inactive-row:hover > td { box-shadow: inset 0 0 0 9999px rgba(255, 193, 7, 0.08) !important; }
</style>
@endpush

@section('content')
<div class="form-card">
    <div class="form-card-header d-flex justify-content-between align-items-center flex-wrap gap-3">
        <div>
            <h2 class="mb-2 fw-bold text-white"><i class="bi bi-clock-history me-2"></i> المستخدمون بانتظار التفعيل</h2>
            <p class="mb-0 opacity-75 fs-6 text-white small">تفعيل المستخدمين مباشرة بدون رمز واتساب عند الحاجة.</p>
        </div>
        <div class="d-flex gap-2">
            <div class="col-toggle-place"></div>
            <a href="{{ route('admin.users.index') }}" class="btn btn-light px-4 fw-bold text-brand d-inline-flex align-items-center">
                <i class="bi bi-arrow-right-circle me-1"></i> العودة للمستخدمين
            </a>
        </div>
    </div>

    <div class="p-4 p-lg-5">
        <form method="GET" action="{{ route('admin.users.inactive') }}" class="row g-3 mb-4 p-4 bg-light rounded-4 border align-items-end">
            <div class="col-md-8">
                <label class="small fw-bold text-muted mb-2">بحث سريع</label>
                <input type="text" name="search" class="form-control search-input" placeholder="الاسم، الهاتف، البريد..." value="{{ request('search') }}">
            </div>
            <div class="col-md-4 d-flex gap-2">
                <button type="submit" class="btn text-white px-4 py-3 fw-bold flex-grow-1" style="background:var(--primary-dark); border-radius:12px">بحث</button>
                @if(request()->filled('search'))
                    <a href="{{ route('admin.users.inactive') }}" class="btn btn-outline-secondary d-flex align-items-center justify-content-center" style="width:58px; height:58px; border-radius:12px" title="تصفير">
                        <i class="bi bi-arrow-counterclockwise fs-4"></i>
                    </a>
                @endif
            </div>
        </form>

        <div class="table-container shadow-sm border overflow-hidden">
            <table class="table mb-0 align-middle text-center" id="inactive_users_table">
                <thead class="bg-light border-bottom">
                    <tr>
                        <th class="py-3" width="50">{!! \App\Support\Sort::link('id', '#') !!}</th>
                        <th class="py-3">{!! \App\Support\Sort::link('name', 'الاسم') !!}</th>
                        <th class="py-3">{!! \App\Support\Sort::link('phone_number', 'رقم الهاتف') !!}</th>
                        <th class="py-3">البريد</th>
                        <th class="py-3">رمز التحقق (OTP)</th>
                        <th class="py-3">صلاحية الرمز</th>
                        <th class="py-3">{!! \App\Support\Sort::link('created_at', 'تاريخ التسجيل') !!}</th>
                        <th class="py-3" width="180">الإجراء</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($inactiveUsers as $user)
                        <tr class="inactive-row">
                            <td class="fw-bold">{{ $user->id }}</td>
                            <td>
                                <div class="fw-bold text-dark">{{ $user->name }}</div>
                            </td>
                            <td><span class="small fw-medium">{{ $user->phone_number }}</span></td>
                            <td><span class="text-muted small">{{ $user->email ?? '-' }}</span></td>
                            <td><span class="badge bg-primary-subtle text-primary border otp-code">{{ $user->whatsapp_otp ?? '---' }}</span></td>
                            <td><span class="badge bg-warning text-dark">{{ $user->whatsapp_otp_expires_at ? $user->whatsapp_otp_expires_at->diffForHumans() : 'N/A' }}</span></td>
                            <td><span class="text-muted small">{{ $user->created_at->format('Y-m-d H:i') }}</span></td>
                            <td>
                                <form action="{{ route('admin.users.directActivate', $user->id) }}" method="POST" onsubmit="return confirm('تفعيل المستخدم مباشرة بدون رمز تحقق؟')">
                                    @csrf
                                    <button type="submit" class="btn btn-sm btn-success rounded-3 px-3">
                                        <i class="bi bi-play-fill me-1"></i> تفعيل
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center py-5 text-muted">لا يوجد مستخدمون في انتظار التفعيل حالياً.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-4 d-flex justify-content-between align-items-center flex-wrap gap-3">
            <form method="GET" action="{{ route('admin.users.inactive') }}" class="d-flex align-items-center bg-light p-2 rounded-3 border">
                @foreach(request()->except(['per_page','page']) as $k=>$v)
                    <input type="hidden" name="{{ $k }}" value="{{ $v }}">
                @endforeach
                <span class="small text-muted me-2 ms-2">إظهار:</span>
                <select name="per_page" class="form-select form-select-sm border-0 bg-transparent fw-bold" onchange="this.form.submit()" style="width:70px">
                    @foreach([10,25,50,100] as $s)
                        <option value="{{ $s }}" @selected((int) request('per_page', 10) === $s)>{{ $s }}</option>
                    @endforeach
                </select>
            </form>
            <div>{{ $inactiveUsers->withQueryString()->links() }}</div>
        </div>
    </div>
</div>
@endsection
