@extends('admin.layout')

@section('title', 'معاينة البيانات قبل الاستيراد')

@section('content')
<div class="container py-4">
    <h3 class="mb-4">📥 معاينة ملف الاستيراد (قسم: {{ $section }})</h3>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    @if(session('duplicates') && count(session('duplicates')) > 0)
        <div class="alert alert-warning">
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

        <div class="table-responsive">
            <table class="table table-bordered table-striped text-center align-middle">
                <thead class="table-light">
                    <tr>
                        @foreach($headers as $index => $header)
                            <th>
                                <select name="map[{{ $index }}]" class="form-select form-select-sm">
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
                                        <option value="category_name">اسم القسم</option>
                                    @elseif($section === 'categories')
                                        <option value="name_ar">اسم القسم</option>
                                        <option value="parent_name">اسم القسم الأب</option>
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

        <div class="mt-4 text-end">
            <button type="submit" class="btn btn-success"><i class="bi bi-upload me-1"></i> استيراد البيانات</button>
            <a href="{{ route('admin.imports.index') }}" class="btn btn-secondary">عودة</a>
        </div>
    </form>
</div>
@endsection