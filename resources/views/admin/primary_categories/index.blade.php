@php
if (!function_exists('flatten_pri_cats')) {
    function flatten_pri_cats($items, $level = 0) {
        $flat = collect();
        foreach ($items as $item) {
            $item->level = $level;
            $flat->push($item);
            if ($item->children && $item->children->isNotEmpty()) {
                $flat = $flat->merge(flatten_pri_cats($item->children->sortBy('sort_order'), $level + 1));
            }
        }
        return $flat;
    }
}
$roots = ($items ?? null) ?: (($primaryCategories ?? null) ?: ($categories ?? collect()));
$flatItems = flatten_pri_cats($roots->sortBy('sort_order'));
@endphp

@extends('admin.layout')
@section('title', 'إدارة فئات المنتجات')

@push('styles')
<style>
    .form-card { border-radius: 0 !important; border: none !important; box-shadow: none !important; background: #fff; width: 100% !important; margin: 0 !important; }
    .form-card-header { background: linear-gradient(135deg, var(--primary-dark) 0%, var(--primary-medium) 100%); padding: 2.5rem 3rem; color: white; border-radius: 0 !important; }
    .table-container { border-radius: 15px; border: 1px solid #f1f5f9; overflow: hidden; background: #fff; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05); }
    .search-input { border-radius: 12px; border: 1px solid #e2e8f0; padding: 0.8rem 1.2rem; background: #fafbff; }
    .cat-img { width:42px; height:42px; border-radius:10px; object-fit:cover; border:1px solid #eee; background:#fff; margin-right: 12px; }
    #primary_categories_table tbody tr { transition: background-color .18s ease; }
    #primary_categories_table tbody tr.row-moved { animation: slideInUp .4s ease-out; }
    @keyframes slideInUp {
        from {
            opacity: 0;
            transform: translateY(20px);
            background-color: #fff8e1;
        }
        to {
            opacity: 1;
            transform: translateY(0);
            background-color: transparent;
        }
    }
</style>
@endpush

@section('content')
<div class="form-card">
    <div class="form-card-header d-flex justify-content-between align-items-center flex-wrap gap-3">
        <div>
            <h2 class="mb-2 fw-bold text-white"><i class="bi bi-grid-fill me-2"></i> إدارة شجرة الفئات</h2>
            <p class="mb-0 opacity-75 fs-6 text-white small">تصنيف المنتجات حسب النوع (مثل: العناية، العطور، المستلزمات) مع دعم الأقسام الفرعية.</p>
        </div>
        <div class="d-flex gap-2">
            <div class="col-toggle-place"></div>
            <a href="{{ route('admin.primary-categories.export', request()->all()) }}" class="btn btn-outline-info p-2 d-inline-flex align-items-center justify-content-center" style="width:40px; height:40px; border-radius:10px" title="تصدير إكسل"><i class="bi bi-file-earmark-excel"></i></a>
            <a href="{{ route('admin.primary-categories.trash') }}" class="btn btn-outline-danger p-2 d-inline-flex align-items-center justify-content-center" style="width:40px; height:40px; border-radius:10px" title="المهملات"><i class="bi bi-trash"></i></a>
            @can('create-primary-categories')
                <a href="{{ route('admin.primary-categories.create') }}" class="btn btn-light px-4 fw-bold text-brand d-inline-flex align-items-center"><i class="bi bi-plus-circle me-1"></i> إضافة فئة</a>
            @endcan
        </div>
    </div>
    
    <div class="p-4 p-lg-5">
        <form method="GET" action="{{ route('admin.primary-categories.index') }}" class="row g-3 mb-4 p-4 bg-light rounded-4 border align-items-end">
            <div class="col-md-8">
                <label class="small fw-bold text-muted mb-2">بحث سريع في الأقسام</label>
                <input type="text" name="search" class="form-control search-input" placeholder="أدخل اسم الفئة للبحث..." value="{{ request('search') }}">
            </div>
            <div class="col-md-4 d-flex gap-2">
                <button type="submit" class="btn text-white px-4 py-3 fw-bold flex-grow-1" style="background:var(--primary-dark); border-radius:12px">بحث</button>
                <button type="button" class="btn btn-outline-dark d-flex align-items-center justify-content-center" style="width:58px; height:58px; border-radius:12px" data-bs-toggle="modal" data-bs-target="#filtersModal" title="فلاتر إضافية">
                    <i class="bi bi-funnel fs-4"></i>
                </button>
                @if(request()->anyFilled(['search','level','date_from','date_to']))
                    <a href="{{ route('admin.primary-categories.index') }}" class="btn btn-outline-secondary d-flex align-items-center justify-content-center" style="width:58px; height:58px; border-radius:12px" title="تصفير">
                        <i class="bi bi-arrow-counterclockwise fs-4"></i>
                    </a>
                @endif
            </div>
        </form>

        <div class="table-container shadow-sm border overflow-hidden">
            <table class="table mb-0 align-middle" id="primary_categories_table">
                <thead class="bg-light border-bottom">
                    <tr class="text-muted small fw-bold text-center">
                        <th class="py-3" width="50" data-column-id="seq">{!! \App\Support\Sort::link('id', '#') !!}</th>
                        <th class="py-3" width="70" data-hide="true" data-column-id="id">ID</th>
                        <th class="py-3 text-start" data-column-id="name">{!! \App\Support\Sort::link('name_ar', 'الفئة') !!}</th>
                        <th class="py-3" data-column-id="products">المنتجات</th>
                        <th class="py-3" data-column-id="sort">{!! \App\Support\Sort::link('sort_order', 'الترتيب') !!}</th>
                        <th class="py-3" data-column-id="level">المستوى</th>
                        <th class="py-3" data-column-id="status">الحالة</th>
                        <th class="py-3" width="120" data-column-id="actions">الإجراءات</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($flatItems as $node)
                        <tr class="text-center" data-item-id="{{ $node->id }}" data-level="{{ $node->level }}" data-parent-id="{{ $node->parent_id ?? '' }}">
                            <td class="small text-muted">{{ $loop->iteration }}</td>
                            <td class="small text-muted">#{{ $node->id }}</td>
                            <td class="text-start">
                                <div class="d-flex align-items-center">
                                    @for($i=0; $i < $node->level; $i++) <span class="ms-3 opacity-25">|—</span> @endfor
                                    @php $img = $node->image ? asset('storage/' . $node->image) : null; @endphp
                                    <img src="{{ $img ?: 'https://placehold.co/42?text=N/A' }}" class="cat-img">
                                    <div>
                                        <div class="fw-bold text-dark">{{ $node->name_ar }}</div>
                                        <div class="small text-muted">{{ $node->name_en }}</div>
                                    </div>
                                </div>
                            </td>
                            <td><span class="badge bg-light text-dark border px-2 py-1">{{ $node->products_count ?? 0 }} منتج</span></td>
                            <td><span class="small text-muted">{{ $node->sort_order ?? 0 }}</span></td>
                            <td>
                                @if($node->level==0) <span class="badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-25 px-2">رئيسي</span>
                                @else <span class="badge bg-secondary bg-opacity-10 text-secondary border border-secondary border-opacity-25 px-2">فرعي</span> @endif
                            </td>
                            <td>
                                @if($node->is_active) <span class="status-badge bg-active shadow-sm" style="font-size: 0.75rem; padding: 0.25rem 0.5rem; border-radius: 6px; background: #eefdf3; color: #1e7e34; border: 1px solid #c3e6cb;">فعال</span>
                                @else <span class="status-badge bg-inactive shadow-sm" style="font-size: 0.75rem; padding: 0.25rem 0.5rem; border-radius: 6px; background: #fffcf0; color: #856404; border: 1px solid #ffeeba;">غير فعال</span> @endif
                            </td>
                            <td>
                                <div class="d-flex justify-content-center gap-1">
                                    @can('edit-primary-categories') 
                                        <form action="{{ route('admin.primary-categories.move', [$node->id, 'up']) }}" method="POST" class="js-move-form" data-direction="up">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-outline-secondary rounded-3 px-2 py-1" title="تصعيد">
                                                <i class="bi bi-arrow-up"></i>
                                            </button>
                                        </form>
                                        <form action="{{ route('admin.primary-categories.move', [$node->id, 'down']) }}" method="POST" class="js-move-form" data-direction="down">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-outline-secondary rounded-3 px-2 py-1" title="تنزيل">
                                                <i class="bi bi-arrow-down"></i>
                                            </button>
                                        </form>
                                        <a href="{{ route('admin.primary-categories.edit', $node->id) }}" class="btn btn-sm btn-outline-primary rounded-3 px-2 py-1" title="تعديل"><i class="bi bi-pencil"></i></a> 
                                    
                                        @if($node->is_active)
                                            <form action="{{ route('admin.primary-categories.toggleStatus', $node->id) }}" method="POST">
                                                @csrf
                                                <input type="hidden" name="status" value="0">
                                                <button type="submit" class="btn btn-sm btn-outline-warning rounded-3 px-2 py-1" title="إيقاف التفعيل">
                                                    <i class="bi bi-pause"></i>
                                                </button>
                                            </form>
                                        @else
                                            <form action="{{ route('admin.primary-categories.toggleStatus', $node->id) }}" method="POST">
                                                @csrf
                                                <input type="hidden" name="status" value="1">
                                                <button type="submit" class="btn btn-sm btn-outline-success rounded-3 px-2 py-1" title="تفعيل">
                                                    <i class="bi bi-play"></i>
                                                </button>
                                            </form>
                                        @endif
                                    @endcan
                                    
                                    @can('delete-primary-categories') 
                                        <form action="{{ route('admin.primary-categories.destroy', $node->id) }}" method="POST" onsubmit="return confirm('حذف؟')">
                                            @csrf 
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-outline-danger rounded-3 px-2 py-1" title="حذف">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </form> 
                                    @endcan
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="py-5 text-center text-muted">لا توجد فئات لعرضها.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($roots instanceof \Illuminate\Pagination\AbstractPaginator)
            <div class="mt-4 d-flex justify-content-between align-items-center flex-wrap gap-3">
                <form method="GET" class="d-flex align-items-center bg-light p-2 rounded-3 border">
                    @foreach(request()->except(['per_page','page']) as $k=>$v) <input type="hidden" name="{{ $k }}" value="{{ $v }}"> @endforeach
                    <span class="small text-muted me-2 ms-2">إظهار:</span>
                    <select name="per_page" class="form-select form-select-sm border-0 bg-transparent fw-bold" onchange="this.form.submit()" style="width:70px">
                        @foreach([10,25,50,100] as $s) <option value="{{$s}}" @selected(request('per_page',10)==$s)>{{$s}}</option> @endforeach
                    </select>
                </form>
                <div>{{ $roots->withQueryString()->links() }}</div>
            </div>
        @endif
    </div>
</div>

{{-- Filters Modal --}}
<div class="modal fade" id="filtersModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg" style="border-radius:20px">
            <div class="modal-header border-0 p-4 pb-0">
                <h5 class="fw-bold mb-0"><i class="bi bi-funnel me-2"></i>تصفية شجرة الفئات المتقدمة</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <form method="GET" action="{{ route('admin.primary-categories.index') }}">
                    <input type="hidden" name="search" value="{{ request('search') }}">
                    <div class="row g-4">
                        <div class="col-md-6">
                            <label class="small fw-bold text-muted mb-2">مستوى الفئة</label>
                            <select name="level" class="form-select search-input">
                                <option value="">الكل</option>
                                <option value="main" @selected(request('level')=='main')>رئيسي</option>
                                <option value="sub" @selected(request('level')=='sub')>فرعي</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="small fw-bold text-muted mb-2">تاريخ الإضافة (من)</label>
                            <input type="date" name="date_from" class="form-control search-input" value="{{ request('date_from') }}">
                        </div>
                        <div class="col-md-6">
                            <label class="small fw-bold text-muted mb-2">تاريخ الإضافة (إلى)</label>
                            <input type="date" name="date_to" class="form-control search-input" value="{{ request('date_to') }}">
                        </div>
                    </div>
                    <div class="mt-5 d-flex gap-2">
                        <button type="submit" class="btn text-white w-100 py-3 fw-bold" style="background:var(--primary-dark); border-radius:12px">تطبيق الفلاتر</button>
                        <a href="{{ route('admin.primary-categories.index') }}" class="btn btn-outline-secondary px-4 py-3" style="border-radius:12px">تصفير الكل</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const table = document.getElementById('primary_categories_table');
    if (!table) return;
    const tbody = table.querySelector('tbody');
    const globalLoader = document.getElementById('global-loader');

    const refreshRowNumbers = () => {
        const rows = table.querySelectorAll('tbody tr');
        rows.forEach((row, index) => {
            const seqCell = row.querySelector('td:first-child');
            if (seqCell) seqCell.textContent = String(index + 1);
        });
    };

    const swapSortCellText = (rowA, rowB) => {
        const sortCellA = rowA.querySelector('td:nth-child(5) .small');
        const sortCellB = rowB.querySelector('td:nth-child(5) .small');
        if (!sortCellA || !sortCellB) return;
        const tmp = sortCellA.textContent;
        sortCellA.textContent = sortCellB.textContent;
        sortCellB.textContent = tmp;
    };

    const getLevel = (row) => Number(row.dataset.level || 0);
    const getParentId = (row) => row.dataset.parentId || '';

    const getRowBlock = (row) => {
        const level = getLevel(row);
        const block = [row];
        let cursor = row.nextElementSibling;

        while (cursor && getLevel(cursor) > level) {
            block.push(cursor);
            cursor = cursor.nextElementSibling;
        }

        return block;
    };

    const moveBlockUp = (row, siblingRow) => {
        const block = getRowBlock(row);
        block.forEach((node) => {
            tbody.insertBefore(node, siblingRow);
        });
    };

    const moveBlockDown = (row, siblingRow) => {
        const block = getRowBlock(row);
        const siblingBlock = getRowBlock(siblingRow);
        const afterSiblingBlock = siblingBlock[siblingBlock.length - 1].nextSibling;

        block.forEach((node) => {
            tbody.insertBefore(node, afterSiblingBlock);
        });
    };

    const getSiblingRows = (row) => {
        const rows = Array.from(tbody.querySelectorAll('tr'));
        const level = getLevel(row);
        const parentId = getParentId(row);

        return rows.filter((item) => getLevel(item) === level && getParentId(item) === parentId);
    };

    const sendMoveRequest = async (row, direction) => {
        const form = row.querySelector(`form.js-move-form[data-direction="${direction}"]`);
        if (!form) return false;

        const formData = new FormData(form);
        const response = await fetch(form.action, {
            method: 'POST',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            },
            body: formData
        });

        const payload = await response.json().catch(() => ({}));
        if (!response.ok || payload.ok === false) {
            throw new Error(payload.message || 'فشل تحديث الترتيب');
        }

        return true;
    };

    const moveOneStep = async (row, direction) => {
        const siblings = getSiblingRows(row);
        const index = siblings.indexOf(row);
        if (index === -1) return false;

        const swapIndex = direction === 'up' ? index - 1 : index + 1;
        const sibling = siblings[swapIndex];
        if (!sibling) return false;

        await sendMoveRequest(row, direction);

        if (direction === 'up') {
            moveBlockUp(row, sibling);
        } else {
            moveBlockDown(row, sibling);
        }

        swapSortCellText(row, sibling);
        return true;
    };

    const pulseMovedRows = (...rows) => {
        rows.forEach((row) => {
            row.classList.remove('row-moved');
            void row.offsetWidth;
            row.classList.add('row-moved');
            setTimeout(() => row.classList.remove('row-moved'), 430);
        });
    };

    table.addEventListener('submit', async function (event) {
        const form = event.target.closest('form.js-move-form');
        if (!form) return;

        event.preventDefault();

        const row = form.closest('tr');
        if (!row) return;

        const direction = form.dataset.direction;

        const submitButton = form.querySelector('button[type="submit"]');
        if (submitButton) submitButton.disabled = true;

        try {
            await moveOneStep(row, direction);
            refreshRowNumbers();
            const sibling = direction === 'up' ? row.nextElementSibling : row.previousElementSibling;
            if (sibling) pulseMovedRows(row, sibling);
        } catch (error) {
            if (window.showToast) {
                window.showToast(error.message || 'فشل تحديث الترتيب', 'danger');
            } else {
                alert(error.message || 'فشل تحديث الترتيب');
            }
        } finally {
            if (globalLoader) globalLoader.style.display = 'none';
            if (submitButton) submitButton.disabled = false;
        }
    });

});
</script>
@endpush
