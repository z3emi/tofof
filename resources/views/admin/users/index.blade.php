@extends('admin.layout')
@section('title', 'إدارة المستخدمين')

@push('styles')
<style>
    .form-card { border-radius: 0 !important; border: none !important; box-shadow: none !important; background: #fff; width: 100% !important; margin: 0 !important; }
    .form-card-header { background: linear-gradient(135deg, var(--primary-dark) 0%, var(--primary-medium) 100%); padding: 2.5rem 3rem; color: white; border-radius: 0 !important; }
    .table-container { border-radius: 15px; border: 1px solid #f1f5f9; overflow: hidden; background: #fff; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05); }
    .search-input { border-radius: 12px; border: 1px solid #e2e8f0; padding: 0.8rem 1.2rem; background: #fafbff; }
    
    .tier-gold { background: #fff8e1; }
    .tier-silver { background: #f8f9fa; }
    .tier-bronze { background: #fef5ed; }
    
    .selectable-row { cursor: pointer; transition: all 0.2s; }
    .selectable-row:hover { background-color: rgba(0,0,0,0.02) !important; }
    .selectable-row.selected { background-color: #fcecea !important; outline: 2px solid var(--primary-dark); outline-offset: -2px; }
</style>
@endpush

@section('content')
<div class="form-card">
    <div class="form-card-header d-flex justify-content-between align-items-center flex-wrap gap-3">
        <div>
            <h2 class="mb-2 fw-bold text-white"><i class="bi bi-people-fill me-2"></i> إدارة قاعدة المستخدمين</h2>
            <p class="mb-0 opacity-75 fs-6 text-white small">إدارة العملاء، الأرصدة، مستويات العضوية وحالات الحساب.</p>
        </div>
        <div class="d-flex gap-2 flex-wrap">
            <div class="col-toggle-place"></div>
            <a href="{{ route('admin.users.trash') }}" class="btn btn-outline-danger p-2 d-inline-flex align-items-center justify-content-center" style="width:40px; height:40px; border-radius:10px" title="سلة المحذوفات"><i class="bi bi-trash"></i></a>
            <a href="{{ route('admin.users.inactive') }}" class="btn btn-outline-info p-2 d-inline-flex align-items-center justify-content-center" style="width:40px; height:40px; border-radius:10px" title="بانتظار التفعيل"><i class="bi bi-clock-history"></i></a>
            <a href="{{ route('admin.users.create') }}" class="btn btn-light px-4 fw-bold text-brand d-inline-flex align-items-center"><i class="bi bi-person-plus-fill me-1"></i> إضافة مستخدم</a>
            <form action="{{ route('admin.users.forceLogoutAll') }}" method="POST" onsubmit="return confirm('تسجيل خروج جميع المستخدمين؟');">
                @csrf <button type="submit" class="btn btn-danger p-2 d-inline-flex align-items-center justify-content-center" style="width:40px; height:40px; border-radius:10px" title="خروج الكل"><i class="bi bi-box-arrow-right"></i></button>
            </form>
        </div>
    </div>
    
    <div class="p-4 p-lg-5">
        <form method="GET" action="{{ route('admin.users.index') }}" class="row g-3 mb-4 p-4 bg-light rounded-4 border align-items-end">
            <div class="col-md-8">
                <label class="small fw-bold text-muted mb-2">بحث ذكي (الاسم، الهاتف، البريد)</label>
                <input type="text" name="search" class="form-control search-input" placeholder="أدخل اسم المستخدم أو رقم الهاتف..." value="{{ request('search') }}">
            </div>
            <div class="col-md-4 d-flex gap-2">
                <button type="submit" class="btn text-white px-4 py-3 fw-bold flex-grow-1" style="background:var(--primary-dark); border-radius:12px">بحث</button>
                <button type="button" class="btn btn-outline-dark d-flex align-items-center justify-content-center" style="width:58px; height:58px; border-radius:12px" data-bs-toggle="modal" data-bs-target="#filtersModal" title="فلاتر إضافية">
                    <i class="bi bi-funnel fs-4"></i>
                </button>
                @if(request()->anyFilled(['search','status','tier','min_wallet','max_wallet','min_orders','max_orders','date_from','date_to']))
                    <a href="{{ route('admin.users.index') }}" class="btn btn-outline-secondary d-flex align-items-center justify-content-center" style="width:58px; height:58px; border-radius:12px" title="تصفير">
                        <i class="bi bi-arrow-counterclockwise fs-4"></i>
                    </a>
                @endif
            </div>
        </form>

        <div class="table-container shadow-sm border overflow-hidden">
            <table class="table mb-0 align-middle text-center" id="users_table">
                <thead class="bg-light border-bottom">
                    <tr class="text-muted small fw-bold">
                        <th class="py-3" width="50" data-column-id="seq">{!! \App\Support\Sort::link('id', '#') !!}</th>
                        <th class="py-3" width="70" data-hide="true" data-column-id="id">ID</th>
                        <th class="py-3" data-column-id="avatar">الصورة</th>
                        <th class="py-3 text-start" data-column-id="name">{!! \App\Support\Sort::link('name', 'الاسم') !!}</th>
                        <th class="py-3" data-column-id="phone">{!! \App\Support\Sort::link('phone_number', 'الهاتف') !!}</th>
                        <th class="py-3" data-column-id="wallet">{!! \App\Support\Sort::link('wallet_balance', 'الرصيد') !!}</th>
                        <th class="py-3" data-column-id="tier">الفئة</th>
                        <th class="py-3" data-column-id="orders">{!! \App\Support\Sort::link('orders_count', 'الطلبات') !!}</th>
                        <th class="py-3" data-column-id="status">الحالة</th>
                        <th class="py-3" width="180" data-column-id="actions">الإجراءات</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($users as $user)
                        <tr class="selectable-row @if($user->banned_at) table-danger @elseif($user->tier=='Gold') tier-gold @elseif($user->tier=='Silver') tier-silver @elseif($user->tier=='Bronze') tier-bronze @endif" data-user-id="{{ $user->id }}" data-user-name="{{ $user->name }}" data-user-wallet="{{ (float) $user->wallet_balance }}">
                            <td class="small text-muted">{{ $loop->iteration + ($users->perPage() * ($users->currentPage() - 1)) }}</td>
                            <td class="small text-muted">#{{ $user->id }}</td>
                            <td><img src="{{ $user->avatar_url }}" class="rounded-circle border" width="38" height="38" style="object-fit:cover" onerror="this.src='{{ asset('storage/avatars/default.jpg') }}'"></td>
                            <td class="text-start">
                                <div class="fw-bold text-dark">{{ $user->name }}</div>
                                <div class="small text-muted">{{ $user->created_at->format('Y-m-d') }}</div>
                            </td>
                            <td><span class="small fw-medium">{{ $user->phone_number }}</span></td>
                            <td><span class="badge bg-dark fw-bold px-2 py-1">{{ number_format($user->wallet_balance, 0) }} د.ع</span></td>
                            <td>
                                @if($user->tier=='Gold') <span class="badge bg-warning text-dark border-warning">🥇 ذهبية</span>
                                @elseif($user->tier=='Silver') <span class="badge bg-secondary text-white">🥈 فضية</span>
                                @elseif($user->tier=='Bronze') <span class="badge bg-opacity-25" style="background:#cd7f32; color:#a0522d">🥉 برونزية</span>
                                @else - @endif
                            </td>
                            <td><span class="badge bg-info bg-opacity-10 text-info border border-info border-opacity-25">{{ $user->orders_count ?? 0 }}</span></td>
                            <td>
                                @if($user->banned_at) <span class="badge bg-danger">محظور</span>
                                @elseif(is_null($user->phone_verified_at)) <span class="badge bg-warning text-dark">غير مفعل</span>
                                @else <span class="badge bg-success">نشط</span> @endif
                            </td>
                            <td>
                                <div class="d-flex justify-content-center gap-1">
                                    <a href="{{ route('admin.users.show', $user->id) }}" class="btn btn-sm btn-outline-primary rounded-3 px-2 py-1"><i class="bi bi-eye"></i></a>
                                    <a href="{{ route('admin.users.edit', $user->id) }}" class="btn btn-sm btn-outline-info rounded-3 px-2 py-1 text-dark"><i class="bi bi-pencil"></i></a>
                                    @if(is_null($user->phone_verified_at))
                                        <form action="{{ route('admin.users.directActivate', $user->id) }}" method="POST" onsubmit="return confirm('تفعيل المستخدم مباشرة بدون رمز تحقق؟')">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-outline-success rounded-3 px-2 py-1" title="تفعيل مباشر">
                                                <i class="bi bi-play-fill"></i>
                                            </button>
                                        </form>
                                    @endif
                                    @if($user->banned_at)
                                        <form action="{{ route('admin.users.unban', $user->id) }}" method="POST">@csrf<button type="submit" class="btn btn-sm btn-outline-success rounded-3 px-2 py-1" title="إلغاء الحظر"><i class="bi bi-unlock"></i></button></form>
                                    @else
                                        <form action="{{ route('admin.users.ban', $user->id) }}" method="POST">@csrf<button type="submit" class="btn btn-sm btn-outline-danger rounded-3 px-2 py-1" title="حظر"><i class="bi bi-slash-circle"></i></button></form>
                                    @endif

                                    {{-- إجراءات المحفظة للكلك الأيمن (تبقى مخفية من شريط الإجراءات) --}}
                                    <button type="button"
                                            class="context-menu-action d-none text-success"
                                            title="إضافة رصيد"
                                            data-wallet-action="deposit"
                                            data-user-id="{{ $user->id }}"
                                            data-user-name="{{ $user->name }}"
                                            data-user-wallet="{{ (float) $user->wallet_balance }}"
                                            data-wallet-url="{{ route('admin.wallet.deposit', $user->id) }}">
                                        <i class="bi bi-plus-circle"></i>
                                    </button>
                                    <button type="button"
                                            class="context-menu-action d-none text-warning"
                                            title="سحب رصيد"
                                            data-wallet-action="withdraw"
                                            data-user-id="{{ $user->id }}"
                                            data-user-name="{{ $user->name }}"
                                            data-user-wallet="{{ (float) $user->wallet_balance }}"
                                            data-wallet-url="{{ route('admin.wallet.withdraw', $user->id) }}">
                                        <i class="bi bi-dash-circle"></i>
                                    </button>

                                    <form action="{{ route('admin.users.destroy', $user->id) }}" method="POST" onsubmit="return confirm('حذف نهائي؟')">@csrf @method('DELETE')<button type="submit" class="btn btn-sm btn-outline-danger rounded-3 px-2 py-1"><i class="bi bi-trash"></i></button></form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="9" class="py-5 text-muted">لا يوجد مستخدمين لعرضهم.</td></tr>
                    @endforelse
                </tbody>
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
            <div>{{ $users->withQueryString()->links() }}</div>
        </div>
    </div>
</div>

{{-- Filters Modal --}}
<div class="modal fade" id="filtersModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg" style="border-radius:20px">
            <div class="modal-header border-0 p-4 pb-0">
                <h5 class="fw-bold mb-0"><i class="bi bi-funnel me-2"></i>تصفية المستخدمين المتقدمة</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <form method="GET" action="{{ route('admin.users.index') }}">
                    <input type="hidden" name="search" value="{{ request('search') }}">
                    <div class="row g-4">
                        <div class="col-md-6">
                            <label class="small fw-bold text-muted mb-2">حالة الحساب</label>
                            <select name="status" class="form-select search-input">
                                <option value="">كل الحالات</option>
                                <option value="active" @selected(request('status')=='active')>نشط</option>
                                <option value="banned" @selected(request('status')=='banned')>محظور</option>
                                <option value="inactive" @selected(request('status')=='inactive')>غير مفعل</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="small fw-bold text-muted mb-2">فئة المستخدم</label>
                            <select name="tier" class="form-select search-input">
                                <option value="">كل الفئات</option>
                                <option value="Gold" @selected(request('tier')=='Gold')>ذهبية</option>
                                <option value="Silver" @selected(request('tier')=='Silver')>فضية</option>
                                <option value="Bronze" @selected(request('tier')=='Bronze')>برونزية</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="small fw-bold text-muted mb-2">الرصيد بالمحفظة (من - إلى)</label>
                            <div class="input-group">
                                <input type="number" name="min_wallet" class="form-control search-input" placeholder="الأدنى" value="{{ request('min_wallet') }}">
                                <input type="number" name="max_wallet" class="form-control search-input" placeholder="الأعلى" value="{{ request('max_wallet') }}">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="small fw-bold text-muted mb-2">عدد الطلبات (من - إلى)</label>
                            <div class="input-group">
                                <input type="number" name="min_orders" class="form-control search-input" placeholder="الأدنى" value="{{ request('min_orders') }}">
                                <input type="number" name="max_orders" class="form-control search-input" placeholder="الأعلى" value="{{ request('max_orders') }}">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="small fw-bold text-muted mb-2">تاريخ الانضمام (من)</label>
                            <input type="date" name="date_from" class="form-control search-input" value="{{ request('date_from') }}">
                        </div>
                        <div class="col-md-6">
                            <label class="small fw-bold text-muted mb-2">تاريخ الانضمام (إلى)</label>
                            <input type="date" name="date_to" class="form-control search-input" value="{{ request('date_to') }}">
                        </div>
                    </div>
                    <div class="mt-5 d-flex gap-2">
                        <button type="submit" class="btn text-white w-100 py-3 fw-bold" style="background:var(--primary-dark); border-radius:12px">تطبيق الفلاتر</button>
                        <a href="{{ route('admin.users.index') }}" class="btn btn-outline-secondary px-4 py-3" style="border-radius:12px">تصفير الكل</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

{{-- Wallet Action Modal --}}
<div class="modal fade" id="walletActionModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg" style="border-radius:18px">
            <form id="walletActionForm" method="POST" data-no-loader="true">
                @csrf
                <div class="modal-header border-0 pb-0">
                    <h5 class="fw-bold mb-0" id="walletActionModalTitle">إجراء على الرصيد</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body pt-3">
                    <div class="small text-muted mb-2">المستخدم</div>
                    <div class="fw-bold mb-3" id="walletActionUserName">-</div>

                    <div class="small text-muted mb-2">الرصيد الحالي</div>
                    <div class="badge bg-dark mb-3" id="walletActionCurrentBalance">0 د.ع</div>

                    <div class="mb-3">
                        <label for="walletAmount" class="form-label fw-bold">المبلغ</label>
                        <input type="number" step="0.01" min="0.01" class="form-control search-input" id="walletAmount" name="amount" required>
                    </div>

                    <div class="mb-0">
                        <label for="walletNote" class="form-label fw-bold">الوصف</label>
                        <textarea class="form-control search-input" id="walletNote" name="note" rows="3" placeholder="اكتب وصف العملية (اختياري)"></textarea>
                    </div>
                </div>
                <div class="modal-footer border-0 pt-0">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">إلغاء</button>
                    <button type="submit" class="btn text-white" id="walletActionSubmitBtn" style="background:var(--primary-dark)">حفظ</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        document.querySelectorAll('.selectable-row').forEach(row => {
            row.addEventListener('click', function(e) {
                if(e.target.closest('a') || e.target.closest('button')) return;
                document.querySelectorAll('.selectable-row').forEach(r => r.classList.remove('selected'));
                this.classList.add('selected');
            });
        });

        const walletModalEl = document.getElementById('walletActionModal');
        const walletForm = document.getElementById('walletActionForm');
        const walletTitle = document.getElementById('walletActionModalTitle');
        const walletUserName = document.getElementById('walletActionUserName');
        const walletCurrentBalance = document.getElementById('walletActionCurrentBalance');
        const walletSubmitBtn = document.getElementById('walletActionSubmitBtn');
        const walletAmount = document.getElementById('walletAmount');
        const walletNote = document.getElementById('walletNote');

        if (!walletModalEl || !walletForm) {
            return;
        }

        const walletModal = new bootstrap.Modal(walletModalEl);

        document.addEventListener('click', function (event) {
            const trigger = event.target.closest('.context-menu-action[data-wallet-action]');
            if (!trigger) {
                return;
            }

            event.preventDefault();

            const actionType = trigger.dataset.walletAction;
            const actionUrl = trigger.dataset.walletUrl;
            const userName = trigger.dataset.userName || '-';
            const currentBalance = Number(trigger.dataset.userWallet || 0);

            walletForm.action = actionUrl;
            walletAmount.value = '';
            walletNote.value = '';

            walletUserName.textContent = userName;
            walletCurrentBalance.textContent = `${Math.round(currentBalance).toLocaleString('en-US')} د.ع`;

            if (actionType === 'withdraw') {
                walletTitle.textContent = 'سحب رصيد من محفظة المستخدم';
                walletSubmitBtn.textContent = 'تأكيد السحب';
                walletSubmitBtn.classList.remove('btn-success');
                walletSubmitBtn.classList.add('btn-warning');
                walletSubmitBtn.style.color = '#111';
            } else {
                walletTitle.textContent = 'إضافة رصيد إلى محفظة المستخدم';
                walletSubmitBtn.textContent = 'تأكيد الإضافة';
                walletSubmitBtn.classList.remove('btn-warning');
                walletSubmitBtn.classList.add('btn-success');
                walletSubmitBtn.style.color = '#fff';
            }

            walletModal.show();
        });

        walletModalEl.addEventListener('shown.bs.modal', function () {
            walletAmount.focus();
        });
    });
</script>
@endsection
