@extends('admin.layout')

@section('title', 'إدارة المنتجات')

@push('styles')
<style>
    .form-card { border-radius: 0 !important; border: none !important; box-shadow: none !important; background: #fff; width: 100% !important; margin: 0 !important; }
    .form-card-header { background: linear-gradient(135deg, var(--primary-dark) 0%, var(--primary-medium) 100%); padding: 2.5rem 3rem; color: white; border-radius: 0 !important; }
    .status-badge { border-radius: 8px; padding: 0.4rem 0.8rem; font-weight: 700; font-size: 0.8rem; color: #fff; }
    .bg-active { background: #198754; }
    .bg-inactive { background: #6c757d; }
    .table-container { border-radius: 15px; border: 1px solid #f1f5f9; overflow-x: auto; background: #fff; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05); -webkit-overflow-scrolling: touch; }
    .search-input { border-radius: 12px; border: 1px solid #e2e8f0; padding: 0.8rem 1.2rem; background: #fafbff; }
    .prod-thumb { width:48px; height:48px; border-radius:10px; object-fit:cover; border:1px solid #eee; }
    .qty-badge { font-weight:700; padding:0.35rem 0.7rem; border-radius:8px; font-size:0.85rem; }
    .qty-high { background:#e8f5e9; color:#1b5e20; }
    .qty-mid { background:#fff3cd; color:#664d03; }
    .qty-low { background:#fde2e1; color:#a4161a; }
</style>
@endpush

@section('content')
<div class="form-card">
    <div class="form-card-header d-flex justify-content-between align-items-center flex-wrap gap-3">
        <div>
            <h2 class="mb-2 fw-bold text-white"><i class="bi bi-box-seam-fill me-2"></i> إدارة قائمة المنتجات</h2>
            <p class="mb-0 opacity-75 fs-6 text-white small">تحكم في المخزون، الأسعار، وحالات العرض في المتجر.</p>
        </div>
        <div class="d-flex gap-2">
            @can('view-products')
                <div class="col-toggle-place"></div>
                <a href="{{ route('admin.products.export', request()->all()) }}" class="btn btn-outline-info p-2 d-inline-flex align-items-center justify-content-center" style="width:40px; height:40px; border-radius:10px" title="تصدير إكسل"><i class="bi bi-file-earmark-excel"></i></a>
                <a href="{{ route('admin.products.trash') }}" class="btn btn-outline-danger p-2 d-inline-flex align-items-center justify-content-center" style="width:40px; height:40px; border-radius:10px" title="المهملات"><i class="bi bi-trash"></i></a>
            @endcan
            @can('create-products')
                <a href="{{ route('admin.products.create') }}" class="btn btn-light px-4 fw-bold text-brand"><i class="bi bi-plus-circle me-1"></i> إضافة منتج</a>
            @endcan
        </div>
    </div>
    <div class="p-4 p-lg-5">
        <form method="GET" action="{{ route('admin.products.index') }}" class="row g-3 mb-4 p-4 bg-light rounded-4 border align-items-end">
            <div class="col-md-5">
                <label class="small fw-bold text-muted mb-2">بحث سريع (الاسم أو SKU)</label>
                <input type="text" name="search" class="form-control search-input" placeholder="أدخل اسم المنتج..." value="{{ request('search') }}">
            </div>
            <div class="col-md-3">
                <label class="small fw-bold text-muted mb-2">الفئة / البراند</label>
                <select name="category_id" class="form-select search-input" onchange="this.form.submit()">
                    <option value="">كل الفئات</option>
                    @foreach(\App\Models\Category::orderBy('name_ar')->get() as $cat)
                        <option value="{{ $cat->id }}" @selected(request('category_id')==$cat->id)>{{ $cat->name_ar }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-4 d-flex gap-2">
                <button type="submit" class="btn text-white px-4 py-3 fw-bold flex-grow-1" style="background:var(--primary-dark); border-radius:12px">بحث</button>
                <button type="button" class="btn btn-outline-dark d-flex align-items-center justify-content-center" style="width:58px; height:58px; border-radius:12px" data-bs-toggle="modal" data-bs-target="#filtersModal" title="فلاتر إضافية">
                    <i class="bi bi-funnel fs-4"></i>
                </button>
                @if(request()->anyFilled(['search','category_id','status','min_price','max_price','min_stock','max_stock','date_from','date_to']))
                    <a href="{{ route('admin.products.index') }}" class="btn btn-outline-secondary d-flex align-items-center justify-content-center" style="width:58px; height:58px; border-radius:12px" title="تصفير">
                        <i class="bi bi-arrow-counterclockwise fs-4"></i>
                    </a>
                @endif
            </div>
        </form>

        <div class="table-container shadow-sm border">
            <table class="table mb-0 align-middle text-center" id="products_table">
                <thead class="bg-light border-bottom">
                    <tr class="text-muted small fw-bold">
                        <th class="py-3" width="50" data-column-id="seq">{!! \App\Support\Sort::link('id', '#') !!}</th>
                        <th class="py-3" width="70" data-hide="true" data-column-id="id">ID</th>
                        <th class="py-3 text-start" data-column-id="name">{!! \App\Support\Sort::link('name_ar', 'المنتج') !!}</th>
                        <th class="py-3" data-column-id="price">{!! \App\Support\Sort::link('price', 'سعر البيع') !!}</th>
                        <th class="py-3" data-column-id="sale_price">{!! \App\Support\Sort::link('sale_price', 'سعر الخصم') !!}</th>
                        <th class="py-3" data-column-id="qty">{!! \App\Support\Sort::link('available_quantity', 'الكمية') !!}</th>
                        <th class="py-3" data-column-id="status">{!! \App\Support\Sort::link('is_active', 'الحالة') !!}</th>
                        <th class="py-3" width="150" data-column-id="actions">الإجراءات</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($products as $product)
                        @php $qty = (int)($product->available_quantity ?? 0); @endphp
                        <tr>
                            <td class="small text-muted">{{ $loop->iteration + ($products->perPage() * ($products->currentPage() - 1)) }}</td>
                            <td class="small text-muted">#{{ $product->id }}</td>
                            <td class="text-start">
                                <div class="d-flex align-items-center gap-3">
                                    <img src="{{ asset('storage/' . optional($product->firstImage)->image_path) }}" class="prod-thumb" onerror="this.src='https://placehold.co/48?text=N/A'">
                                    <div>
                                        <div class="fw-bold text-dark">{{ $product->name_ar ?? $product->name }}</div>
                                        <div class="small text-muted">SKU: {{ $product->sku ?? 'N/A' }}</div>
                                    </div>
                                </div>
                            </td>
                            <td><div class="fw-bold">{{ number_format($product->price, 0) }} د.ع</div></td>
                            <td>
                                @if($product->sale_price) <span class="text-success fw-bold">{{ number_format($product->sale_price, 0) }} د.ع</span>
                                @else <span class="text-muted small">لا يوجد</span> @endif
                            </td>
                            <td>
                                <div class="d-flex align-items-center justify-content-center gap-2">
                                    <span class="qty-badge @if($qty > 10) qty-high @elseif($qty > 0) qty-mid @else qty-low @endif">
                                        {{ $qty }}
                                    </span>
                                    @can('edit-products')
                                    <button type="button" class="btn btn-sm btn-outline-secondary border-0 p-1 edit-qty-trigger" 
                                            data-id="{{ $product->id }}" 
                                            data-name="{{ $product->name_ar }}" 
                                            data-qty="{{ $qty }}"
                                            data-no-context="true"
                                            title="تعديل الكمية">
                                        <i class="bi bi-pencil-square"></i>
                                    </button>
                                    @endcan
                                </div>
                            </td>
                            <td>
                                @if($product->is_active) <span class="status-badge bg-active shadow-sm">فعال</span>
                                @else <span class="status-badge bg-inactive shadow-sm">غير فعال</span> @endif
                            </td>
                            <td>
                                <div class="d-flex justify-content-center gap-1">
                                    @can('edit-products')
                                    <div class="d-flex justify-content-center gap-1">
                                        <button type="button" class="btn btn-sm btn-outline-info rounded-3 px-2 py-1 edit-qty-trigger" 
                                                data-id="{{ $product->id }}" 
                                                data-name="{{ $product->name_ar }}" 
                                                data-qty="{{ $qty }}"
                                                title="تعديل الكمية">
                                            <i class="bi bi-plus-slash-minus"></i>
                                        </button>
                                        <a href="{{ route('admin.products.edit', $product->id) }}" class="btn btn-sm btn-outline-primary rounded-3 px-2 py-1" title="تعديل المنتج"><i class="bi bi-pencil"></i></a>
                                        
                                        {{-- Toggle Button (Shows relevant action only) --}}
                                        @if($product->is_active)
                                            <form action="{{ route('admin.products.toggleStatus', $product->id) }}" method="POST">
                                                @csrf
                                                <input type="hidden" name="status" value="0">
                                                <button type="submit" class="btn btn-sm btn-outline-warning rounded-3 px-2 py-1" title="إيقاف التفعيل">
                                                    <i class="bi bi-pause"></i>
                                                </button>
                                            </form>
                                        @else
                                            <form action="{{ route('admin.products.toggleStatus', $product->id) }}" method="POST">
                                                @csrf
                                                <input type="hidden" name="status" value="1">
                                                <button type="submit" class="btn btn-sm btn-outline-success rounded-3 px-2 py-1" title="تفعيل">
                                                    <i class="bi bi-play"></i>
                                                </button>
                                            </form>
                                        @endif

                                        @can('delete-products')
                                        <form action="{{ route('admin.products.destroy', $product->id) }}" method="POST" onsubmit="return confirm('حذف؟')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-outline-danger rounded-3 px-2 py-1" title="حذف">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </form>
                                        @endcan
                                    </div>
                                    @endcan
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="py-5 text-muted">لا يوجد منتجات لعرضها.</td></tr>
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
            <div>{{ $products->withQueryString()->links() }}</div>
        </div>
    </div>
</div>

{{-- Filters Modal --}}
<div class="modal fade" id="filtersModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg" style="border-radius:20px">
            <div class="modal-header border-0 p-4 pb-0">
                <h5 class="fw-bold mb-0"><i class="bi bi-funnel me-2"></i>تصفية المنتجات المتقدمة</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <form method="GET" action="{{ route('admin.products.index') }}" id="advancedFilterForm">
                    <input type="hidden" name="search" value="{{ request('search') }}">
                    <div class="row g-4">
                        <div class="col-md-6">
                            <label class="small fw-bold text-muted mb-2">البراند (القسم الرئيسي)</label>
                            <select name="category_id" class="form-select search-input">
                                <option value="">كل البراندات</option>
                                @foreach(\App\Models\Category::orderBy('name_ar')->get() as $cat)
                                    <option value="{{ $cat->id }}" @selected(request('category_id') == $cat->id)>{{ $cat->name_ar }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="small fw-bold text-muted mb-2">حالة العرض</label>
                            <select name="status" class="form-select search-input">
                                <option value="">كل الحالات</option>
                                <option value="active" @selected(request('status')=='active')>فعال</option>
                                <option value="inactive" @selected(request('status')=='inactive')>غير فعال</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="small fw-bold text-muted mb-2">السعر (من - إلى)</label>
                            <div class="input-group">
                                <input type="number" name="min_price" class="form-control search-input" placeholder="الأدنى" value="{{ request('min_price') }}">
                                <input type="number" name="max_price" class="form-control search-input" placeholder="الأعلى" value="{{ request('max_price') }}">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="small fw-bold text-muted mb-2">المخزون (من - إلى)</label>
                            <div class="input-group">
                                <input type="number" name="min_stock" class="form-control search-input" placeholder="الأدنى" value="{{ request('min_stock') }}">
                                <input type="number" name="max_stock" class="form-control search-input" placeholder="الأعلى" value="{{ request('max_stock') }}">
                            </div>
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
                        <a href="{{ route('admin.products.index') }}" class="btn btn-outline-secondary px-4 py-3" style="border-radius:12px">تصفير الكل</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

{{-- Edit Quantity Modal --}}
<div class="modal fade" id="editQtyModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" style="max-width: 400px;">
        <div class="modal-content border-0 shadow-lg" style="border-radius:20px">
            <div class="modal-header border-0 p-4 pb-0">
                <h5 class="fw-bold mb-0"><i class="bi bi-pencil-square me-2 text-brand"></i>تعديل الكمية</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="editQtyForm" method="POST">
                @csrf
                <div class="modal-body p-4">
                    <p class="text-muted small mb-3">المنتج: <span id="modalProductName" class="fw-bold text-dark"></span></p>
                    <div class="form-group">
                        <label class="small fw-bold text-muted mb-2">الكمية المتاحة حالياً</label>
                        <input type="number" name="stock_quantity" id="modalStockQty" class="form-control search-input" min="0" required>
                    </div>
                </div>
                <div class="modal-footer border-0 p-4 pt-0">
                    <button type="submit" class="btn text-white w-100 py-3 fw-bold" style="background:var(--primary-dark); border-radius:12px">حفظ التغييرات</button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const editQtyModalEl = document.getElementById('editQtyModal');
    if (!editQtyModalEl) return;
    
    const editQtyModal = new bootstrap.Modal(editQtyModalEl);
    const editQtyForm = document.getElementById('editQtyForm');
    const modalProductName = document.getElementById('modalProductName');
    const modalStockQty = document.getElementById('modalStockQty');

    document.addEventListener('click', function(e) {
        const trigger = e.target.closest('.edit-qty-trigger');
        if (trigger) {
            e.preventDefault();
            e.stopPropagation();
            
            const id = trigger.dataset.id;
            const name = trigger.dataset.name;
            const qty = trigger.dataset.qty;
            
            modalProductName.textContent = name;
            modalStockQty.value = qty;
            editQtyForm.action = `{{ url('admin/products') }}/${id}/update-stock`;
            
            editQtyModal.show();
            
            const contextMenu = document.getElementById('context-menu');
            if (contextMenu) contextMenu.style.display = 'none';
        }
    });
});
</script>
@endpush
@endsection