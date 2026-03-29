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
                        <th class="py-3" width="120" data-column-id="actions">الإجراءات</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($flatItems as $node)
                        <tr class="text-center">
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
                                <div class="d-flex justify-content-center gap-1">
                                    @can('edit-primary-categories') <a href="{{ route('admin.primary-categories.edit', $node->id) }}" class="btn btn-sm btn-outline-primary rounded-3 px-2 py-1"><i class="bi bi-pencil"></i></a> @endcan
                                    @can('delete-primary-categories') <form action="{{ route('admin.primary-categories.destroy', $node->id) }}" method="POST" onsubmit="return confirm('حذف؟')">@csrf @method('DELETE')<button type="submit" class="btn btn-sm btn-outline-danger rounded-3 px-2 py-1"><i class="bi bi-trash"></i></button></form> @endcan
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
