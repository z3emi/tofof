@php
function users_sortable_link($column, $title, $currentSortBy, $currentSortDir) {
    $sortBy      = request('sort_by', $currentSortBy);
    $sortDir     = request('sort_dir', $currentSortDir);
    $newDir      = ($sortBy == $column && $sortDir == 'asc') ? 'desc' : 'asc';
    $icon        = '';
    if ($sortBy == $column) {
        $icon = $sortDir == 'asc' ? '<i class="bi bi-sort-up ms-1"></i>' : '<i class="bi bi-sort-down ms-1"></i>';
    }
    $queryParams = request()->except(['sort_by', 'sort_dir', 'page']);
    $queryParams['sort_by']  = $column;
    $queryParams['sort_dir'] = $newDir;

    return '<a href="' . route('admin.users.index', $queryParams) . '" class="text-decoration-none text-dark">'
         . e($title) . $icon . '</a>';
}
@endphp

@extends('admin.layout')

@section('title', 'إدارة المستخدمين')

@push('styles')
<style>
    /* ================= START: التعديل المطلوب هنا ================= */
    .table-row-gold td { background-color: #fff8e1 !important; }
    .table-row-silver td { background-color: #e9ecef !important; }
    .table-row-bronze td { background-color: #fcece0 !important; }
    /* ================== END: التعديل المطلوب هنا ================== */

    /* تمييز المستخدم المحظور باللون الأحمر */
    .table-danger, .table-danger > th, .table-danger > td {
        background-color: #fbe9e7 !important;
        text-decoration: line-through;
        opacity: 0.7;
    }
    /* تمييز المستخدم غير المفعل باللون الرمادي */
    .table-inactive, .table-inactive > th, .table-inactive > td {
        background-color: #f1f3f5 !important;
        opacity: 0.8;
    }

    /* تخصيص شكل روابط التنقل ليتناسب مع التصميم */
    .pagination {
        justify-content: center !important;
        gap: 0.4rem;
        margin-top: 1rem;
    }
    .pagination .page-item .page-link {
        background-color: #f9f5f1 !important;
        color: #cd8985 !important;
        border-color: #cd8985 !important;
        font-weight: 600;
        border-radius: 0.375rem;
        transition: background-color 0.3s, color 0.3s;
        box-shadow: none;
    }
    .pagination .page-item .page-link:hover {
        background-color: #dcaca9 !important;
        color: #fff !important;
        border-color: #dcaca9 !important;
    }
    .pagination .page-item.active .page-link {
        background-color: #cd8985 !important;
        border-color: #cd8985 !important;
        color: #fff !important;
    }

    /* ===== تحديد صف المستخدم (يُلوّن الصف كله) ===== */
    .selectable-row { cursor: pointer; transition: background-color .12s ease; }
    .selectable-row.selected td {
        background-color: #f3e5e3 !important;
        --bs-table-accent-bg: #f3e5e3 !important;
    }
    .selectable-row.selected {
        outline: 2px solid #cd8985;
        outline-offset: -2px;
    }

    /* مودال */
    .modal { backdrop-filter: blur(2px); }
    .modal-content { border: none; border-radius: 0.5rem; box-shadow: 0 0.5rem 1rem rgba(0,0,0,.15); }
    .modal-header { background-color: #f8f9fa; border-bottom: 1px solid #dee2e6; }
    .modal-footer { border-top: 1px solid #dee2e6; }
</style>
@endpush

@section('content')
<div class="card shadow-sm">
    <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
        <h4 class="mb-0">جميع المستخدمين</h4>
        <div class="d-flex align-items-center flex-wrap gap-2">
            @can('view-users')
            <a href="{{ route('admin.users.trash') }}" class="btn btn-outline-danger btn-sm">
                <i class="bi bi-trash me-1"></i> سلة المحذوفات
            </a>
            @endcan
            <a href="{{ route('admin.users.inactive') }}" class="btn btn-warning btn-sm">
                <i class="bi bi-clock-history me-1"></i> المستخدمون قيد التفعيل
            </a>
            <a href="{{ route('admin.users.create') }}" class="btn btn-primary btn-sm" style="background-color: #cd8985; border-color: #cd8985;">
                <i class="bi bi-plus-circle me-1"></i> إضافة مستخدم جديد
            </a>
            <form action="{{ route('admin.users.forceLogoutAll') }}" method="POST" class="d-inline" onsubmit="return confirm('تسجيل خروج جميع المستخدمين؟');">
                @csrf
                <button type="submit" class="btn btn-danger btn-sm">
                    <i class="bi bi-box-arrow-right me-1"></i> تسجيل خروج الكل
                </button>
            </form>
        </div>
    </div>
    <div class="card-body">
        {{-- فورم البحث --}}
        <form method="GET" action="{{ route('admin.users.index') }}" class="row g-2 mb-4">
            <div class="col">
                <input type="text" name="search" class="form-control" placeholder="ابحث بالاسم أو رقم الهاتف..." value="{{ request('search') }}">
            </div>
            <div class="col-auto">
                <select name="status" class="form-select">
                    <option value="">كل الحالات</option>
                    <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>نشط</option>
                    <option value="banned" {{ request('status') == 'banned' ? 'selected' : '' }}>محظور</option>
                    <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>غير مفعل</option>
                </select>
            </div>
            <div class="col-auto">
                <button type="submit" class="btn btn-primary" style="background-color: #cd8985; border-color: #cd8985;">
                    <i class="bi bi-search me-1"></i> بحث
                </button>
            </div>
        </form>

        <div class="table-responsive">
            <table class="table table-bordered text-center align-middle" style="min-width: 1100px;">
                <thead class="table-light">
                    <tr>
                        <th>#</th>
                        <th>الصورة</th>
                        <th>{!! users_sortable_link('name', 'الاسم', $sortBy ?? 'id', $sortDir ?? 'desc') !!}</th>
                        <th>{!! users_sortable_link('phone_number', 'رقم الهاتف', $sortBy ?? 'id', $sortDir ?? 'desc') !!}</th>
                        <th>{!! users_sortable_link('wallet_balance', 'الرصيد', $sortBy ?? 'id', $sortDir ?? 'desc') !!}</th>
                        <th>الفئة</th>
                        <th>{!! users_sortable_link('orders_count', 'الطلبات المكتملة', $sortBy ?? 'id', $sortDir ?? 'desc') !!}</th>
                        <th>النوع</th>
                        <th>الحالة</th>
                        <th>تاريخ التسجيل</th>
                        <th>العمليات</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($users as $user)
                        @php
                            $balance = number_format((float)($user->wallet_balance ?? 0), 2);
                        @endphp
                        <tr @class([
                            'selectable-row',
                            'table-danger' => $user->banned_at,
                            'table-inactive' => is_null($user->phone_verified_at) && is_null($user->banned_at),
                            'table-row-gold' => !$user->banned_at && $user->tier === 'Gold',
                            'table-row-silver' => !$user->banned_at && $user->tier === 'Silver',
                            'table-row-bronze' => !$user->banned_at && $user->tier === 'Bronze',
                        ]) data-user-id="{{ $user->id }}">
                            <td>{{ $user->id }}</td>
                            <td>
                                @php
                                    $src = $user->avatar_url ?? asset('storage/avatars/default.jpg');
                                @endphp
                                <img src="{{ $src }}" alt="{{ $user->name }}" class="rounded-circle mx-auto" width="40" height="40" style="object-fit: cover;">
                            </td>
                            <td class="text-nowrap">{{ $user->name }}</td>
                            <td class="text-nowrap">{{ $user->phone_number }}</td>
                            <td><span class="badge bg-dark">{{ $balance }} د.ع</span></td>
                            <td>
                                @if($user->tier === 'Gold')
                                    <span class="badge bg-warning text-dark">🥇 ذهبية</span>
                                @elseif($user->tier === 'Silver')
                                    <span class="badge bg-secondary">🥈 فضية</span>
                                @elseif($user->tier === 'Bronze')
                                    <span class="badge" style="background-color:#cd7f32;color:#fff;">🥉 برونزية</span>
                                @else
                                    -
                                @endif
                            </td>
                            <td><span class="badge bg-info">{{ $user->orders_count ?? 0 }}</span></td>
                            <td>
                                @if ($user->roles->isNotEmpty())
                                    @foreach($user->roles as $role)
                                        @if($role->name == 'Super-Admin')
                                            <span class="badge bg-danger">{{ $role->name }}</span>
                                        @else
                                            <span class="badge bg-primary">{{ $role->name }}</span>
                                        @endif
                                    @endforeach
                                @elseif ($user->permissions->isNotEmpty())
                                    <span class="badge bg-info text-dark">خاص</span>
                                @else
                                    <span class="badge bg-secondary">مستخدم</span>
                                @endif
                            </td>
                            <td>
                                @if($user->banned_at)
                                    <span class="badge bg-danger">محظور</span>
                                @elseif(is_null($user->phone_verified_at))
                                    <span class="badge bg-secondary">غير مفعل</span>
                                @else
                                    <span class="badge bg-success">نشط</span>
                                @endif
                            </td>
                            <td>{{ $user->created_at->format('Y-m-d') }}</td>
                            <td>
                                <a href="{{ route('admin.users.show', $user->id) }}" class="btn btn-sm btn-outline-primary m-1 px-2" title="عرض التفاصيل">
                                    <i class="bi bi-eye"></i>
                                </a>
                                <a href="{{ route('admin.users.edit', $user->id) }}" class="btn btn-sm btn-outline-info m-1 px-2" title="تعديل">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                @if($user->banned_at)
                                    <form action="{{ route('admin.users.unban', $user->id) }}" method="POST" class="d-inline">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-outline-success m-1 px-2" title="إلغاء الحظر">
                                            <i class="bi bi-unlock"></i>
                                        </button>
                                    </form>
                                @elseif(is_null($user->phone_verified_at))
                                    <form action="{{ route('admin.users.directActivate', $user->id) }}" method="POST" class="d-inline" onsubmit="return confirm('تفعيل هذا المستخدم مباشرة؟');">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-outline-success m-1 px-2" title="تفعيل سريع">
                                            <i class="bi bi-check2-circle"></i>
                                        </button>
                                    </form>
                                    <form action="{{ route('admin.users.ban', $user->id) }}" method="POST" class="d-inline">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-outline-danger m-1 px-2" title="حظر">
                                            <i class="bi bi-slash-circle"></i>
                                        </button>
                                    </form>
                                @else
                                    <form action="{{ route('admin.users.ban', $user->id) }}" method="POST" class="d-inline">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-outline-danger m-1 px-2" title="حظر">
                                            <i class="bi bi-slash-circle"></i>
                                        </button>
                                    </form>
                                @endif
                                <form action="{{ route('admin.users.forceLogout', $user->id) }}" method="POST" class="d-inline" onsubmit="return confirm('تسجيل خروج هذا المستخدم؟');">
                                    @csrf
                                    <button type="submit" class="btn btn-sm btn-outline-warning m-1 px-2" title="تسجيل خروج">
                                        <i class="bi bi-box-arrow-right"></i>
                                    </button>
                                </form>
                                <form action="{{ route('admin.users.impersonate', $user->id) }}" method="POST" class="d-inline">
                                    @csrf
                                    <button type="submit" class="btn btn-sm btn-outline-primary m-1 px-2" title="تسجيل الدخول كمستخدم">
                                        <i class="bi bi-person-check"></i>
                                    </button>
                                </form>
                                <form action="{{ route('admin.users.destroy', $user->id) }}" method="POST" class="d-inline" onsubmit="return confirm('هل أنت متأكد من حذف هذا المستخدم نهائيًا؟');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger m-1 px-2" title="حذف المستخدم">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                                <span class="d-none">
                                    <button class="context-menu-action text-success" title="إضافة رصيد" onclick="window.openWalletModal('deposit', {{ $user->id }})">
                                        <i class="bi bi-plus-circle"></i>
                                    </button>
                                    <button class="context-menu-action text-danger" title="سحب رصيد" onclick="window.openWalletModal('withdraw', {{ $user->id }})">
                                        <i class="bi bi-dash-circle"></i>
                                    </button>
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="11">لا يوجد مستخدمين لعرضهم.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        {{-- إضافة اختيار عدد المستخدمين بالصفحة + عرض التصفح --}}
        <div class="d-flex justify-content-between align-items-center mt-3 flex-wrap gap-2">
            <form method="GET" action="{{ route('admin.users.index') }}" class="d-flex align-items-center">
                @foreach(request()->except(['per_page', 'page']) as $key => $value)
                    <input type="hidden" name="{{ $key }}" value="{{ $value }}">
                @endforeach
                <label for="per_page" class="me-2">عدد المستخدمين:</label>
                <select name="per_page" id="per_page" class="form-select form-select-sm" onchange="this.form.submit()">
                    @foreach([5, 10, 25, 50, 100] as $size)
                        <option value="{{ $size }}" {{ request('per_page',5) == $size ? 'selected' : '' }}>
                            {{ $size }}
                        </option>
                    @endforeach
                </select>
            </form>

            <div>
                {{ $users->withQueryString()->links() }}
            </div>
        </div>
    </div>
</div>

{{-- مودال موحد للمحفظة --}}
<div class="modal fade" id="walletActionModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" id="walletActionForm" 
                  data-deposit-template="{{ route('admin.wallet.deposit', ['user' => ':id']) }}"
                  data-withdraw-template="{{ route('admin.wallet.withdraw', ['user' => ':id']) }}">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title" id="walletModalTitle">إدارة الرصيد</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="amount" id="walletFormAmount">
                    <div class="mb-3">
                        <label for="walletAmountDisplay" class="form-label">المبلغ</label>
                        <input type="number" step="0.01" min="0.01" id="walletAmountDisplay" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label for="walletNote" class="form-label">ملاحظة (اختياري)</label>
                        <input type="text" name="note" id="walletNote" class="form-control" maxlength="255">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إلغاء</button>
                    <button type="submit" class="btn btn-primary" id="walletSubmitButton">تنفيذ</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    let selectedRow = null;
    const rows = document.querySelectorAll('.selectable-row');

    // Handle row selection
    rows.forEach(row => {
        row.addEventListener('click', function(e) {
            // تجاهل النقر إذا كان على زر أو رابط
            if(e.target.closest('a') || e.target.closest('button') || e.target.closest('form')) {
                return;
            }
            if (selectedRow) {
                selectedRow.classList.remove('selected');
            }
            this.classList.add('selected');
            selectedRow = this;
        });
    });

    // Setup wallet modal
    const modalEl = document.getElementById('walletActionModal');
    if (!modalEl) return;

    const modalInstance = new bootstrap.Modal(modalEl);
    const form = document.getElementById('walletActionForm');
    const modalTitle = document.getElementById('walletModalTitle');
    const submitButton = document.getElementById('walletSubmitButton');
    const amountDisplayInput = document.getElementById('walletAmountDisplay');
    const amountHiddenInput = document.getElementById('walletFormAmount');

    // Make the function global so we can call it from the injected context menu HTML
    window.openWalletModal = function(actionType, userId) {
        if (!userId) {
            alert('خطة بيانات المستخدم غير متوفرة.');
            return;
        }

        const actionUrlTemplate = (actionType === 'deposit')
            ? form.dataset.depositTemplate
            : form.dataset.withdrawTemplate;

        form.action = actionUrlTemplate.replace(':id', userId);

        // Configure modal for the specific action
        if (actionType === 'deposit') {
            modalTitle.textContent = `إضافة رصيد - المستخدم #${userId}`;
            submitButton.textContent = 'إيداع';
            submitButton.className = 'btn btn-success';
        } else {
            modalTitle.textContent = `سحب رصيد - المستخدم #${userId}`;
            submitButton.textContent = 'سحب';
            submitButton.className = 'btn btn-danger';
        }

        // Reset form and show modal
        form.reset();
        amountDisplayInput.value = '';
        
        modalInstance.show();
    };

    // Handle form submission
    form.addEventListener('submit', function(e) {
        const amountValue = parseFloat(amountDisplayInput.value);
        if (!amountValue || amountValue <= 0) {
            e.preventDefault();
            alert('يرجى إدخال مبلغ صحيح أكبر من صفر.');
            return;
        }
        // Copy the display amount to the hidden input that gets submitted
        amountHiddenInput.value = amountValue;
    });
});
</script>
@endpush
