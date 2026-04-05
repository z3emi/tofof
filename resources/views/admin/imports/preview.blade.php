@extends('admin.layout')

@section('title', 'معاينة البيانات قبل الاستيراد')

@push('styles')
<style>
    .form-card { border-radius: 0 !important; border: none !important; box-shadow: none !important; background: #fff; width: 100% !important; margin: 0 !important; }
    .form-card-header { background: linear-gradient(135deg, var(--primary-dark) 0%, var(--primary-medium) 100%); padding: 2.5rem 3rem; color: white; border-radius: 0 !important; }
    .form-card-body { padding: 1.5rem 1.25rem; }
    .import-panel {
        max-width: 1240px;
        margin: 0 auto;
        border: 1px solid #e7edf4;
        border-radius: 16px;
        overflow: hidden;
        box-shadow: 0 8px 26px rgba(15, 23, 42, .06);
        background: #fff;
    }
    .table-container { border-radius: 15px; border: 1px solid #f1f5f9; overflow: hidden; background: #fff; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05); }
    .section-title { font-size: 1.1rem; font-weight: 700; color: var(--primary-dark); margin-bottom: 1rem; display: flex; align-items: center; gap: 10px; }
    .section-title::after { content: ''; flex-grow: 1; height: 2px; background: #f1f5f9; }

    .import-alert { border-radius: .75rem; border: 1px solid #e2e8f0; }
    .table thead th { white-space: nowrap; }
    .table th, .table td { border-color: #e7edf4; }
    .map-select {
        width: 100%;
        min-width: 0;
        border-radius: .5rem;
        font-size: .8rem;
        border-color: #d7dfea;
    }
    .map-select:focus {
        border-color: var(--primary-medium);
        box-shadow: 0 0 0 .22rem rgba(109, 14, 22, .12);
    }
    .import-actions {
        margin-top: 1rem;
        display: flex;
        justify-content: center;
        gap: .5rem;
        flex-wrap: wrap;
    }
    .import-btn {
        min-height: 42px;
        border-radius: .7rem;
        padding: .5rem 1rem;
        font-weight: 700;
    }
    .import-btn-primary {
        border: 0;
        color: #fff;
        background: linear-gradient(135deg, var(--primary-medium) 0%, var(--primary-dark) 100%);
        box-shadow: 0 8px 18px rgba(109, 14, 22, .22);
    }
    .import-btn-primary:hover { filter: brightness(.97); color: #fff; }
    .import-btn-secondary {
        border: 1px solid #d5dce7;
        background: #fff;
        color: #475569;
    }
    .import-btn-secondary:hover { background: #f8fafc; color: #334155; }

    @media (max-width: 991.98px) {
        .form-card-header { padding: 1.4rem 1rem; }
        .form-card-header h2 { font-size: 1.15rem; }
        .form-card-body { padding: 1rem .8rem; }
        .table { font-size: .8rem; }
        .map-select { font-size: .74rem; }
        .import-btn { width: 100%; }
    }
</style>
@endpush

@section('content')
<div class="form-card">
    <div class="form-card-header d-flex justify-content-between align-items-center flex-wrap gap-3">
        <div>
            <h2 class="mb-2 fw-bold text-white"><i class="bi bi-table me-2"></i> معاينة ملف الاستيراد</h2>
            <p class="mb-0 opacity-75 fs-6 text-white small">القسم الحالي: {{ $sectionLabel ?? $section }} - قم بمطابقة الأعمدة قبل تنفيذ الاستيراد.</p>
        </div>
    </div>

    <div class="form-card-body">
    <div class="import-panel p-3 p-md-4">

    @if(session('success'))
        <div class="alert alert-success import-alert">{{ session('success') }}</div>
    @endif

    @if(session('duplicates') && count(session('duplicates')) > 0)
        <div class="alert alert-warning import-alert">
            <strong>⚠️ السجلات التالية تم تجاهلها لأنها مكررة:</strong>
            <ul class="mb-0 mt-2">
                @foreach(session('duplicates') as $item)
                    <li>{{ $item }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('admin.imports.import') }}">
        @csrf

        <input type="hidden" name="path" value="{{ $path }}">
        <input type="hidden" name="section" value="{{ $section }}">

        <div class="form-check form-switch mb-3">
            <input class="form-check-input" type="checkbox" role="switch" id="ignoreHeader" name="ignore_header" checked>
            <label class="form-check-label" for="ignoreHeader">تجاهل أول صف (Header)</label>
        </div>

        <div class="section-title text-muted mb-4">
            <span class="badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-25 px-3 py-2">مخطط الأعمدة</span>
            <span class="small fw-bold">{{ count($headers) }} عمود</span>
        </div>

        <div class="table-container shadow-sm border overflow-hidden">
        <div class="table-responsive">
            <table class="table table-bordered table-striped text-center align-middle">
                <thead class="table-light">
                    <tr>
                        @foreach($headers as $index => $header)
                            <th>
                                <select name="map[{{ $index }}]" class="form-select form-select-sm map-select">
                                    <option value="ignore">-- تجاهل --</option>

                                    {{-- الحقول المتاحة حسب القسم --}}
                                    @if($section === 'products')
                                        <option value="name_ar">الاسم (عربي)</option>
                                        <option value="name_en">الاسم (إنجليزي)</option>
                                        <option value="name_ku">الاسم (كردي)</option>
                                        <option value="sku">SKU</option>
                                        <option value="price">السعر</option>
                                        <option value="description">الوصف</option>
                                        <option value="description_ar">الوصف (عربي)</option>
                                        <option value="description_en">الوصف (إنجليزي)</option>
                                        <option value="description_ku">الوصف (كردي)</option>
                                        <option value="stock_quantity">الكمية</option>
                                        <option value="category_name">اسم البراند</option>
                                        <option value="brand_name">اسم الفئة</option>
                                        <option value="brand_names">أسماء الفئات (متعددة)</option>
                                    @elseif($section === 'categories')
                                        <option value="name_ar">اسم البراند</option>
                                        <option value="name_en">اسم البراند (إنجليزي)</option>
                                        <option value="parent_name">اسم البراند الأب</option>
                                    @elseif($section === 'brands')
                                        <option value="name_ar">اسم الفئة (عربي)</option>
                                        <option value="name_en">اسم الفئة (إنجليزي)</option>
                                        <option value="slug">Slug</option>
                                        <option value="parent_name">اسم الفئة الأم</option>
                                    @elseif($section === 'users')
                                        <option value="name">الاسم</option>
                                        <option value="email">البريد الإلكتروني</option>
                                        <option value="password">كلمة المرور</option>
                                    @elseif($section === 'clients')
                                        <option value="name">الاسم</option>
                                        <option value="phone">الهاتف</option>
                                        <option value="address">العنوان</option>
                                    @elseif($section === 'discounts')
                                        <option value="code">الكود</option>
                                        <option value="amount">القيمة</option>
                                        <option value="type">النوع</option>
                                        <option value="expires_at">تاريخ الانتهاء</option>
                                    @endif
                                </select>
                                <div class="mt-1 small text-muted">{{ $header }}</div>
                            </th>
                        @endforeach
                    </tr>
                </thead>
                <tbody>
                    @foreach($rows as $row)
                        <tr>
                            @foreach($row as $cell)
                                <td>{{ $cell }}</td>
                            @endforeach
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        </div>

        <div class="import-actions">
            <button type="submit" class="btn import-btn import-btn-primary"><i class="bi bi-upload me-1"></i> استيراد البيانات</button>
            <a href="{{ route('admin.imports.index') }}" class="btn import-btn import-btn-secondary">عودة</a>
        </div>
    </form>
</div>
    </div>
</div>
@endsection