@extends('admin.layout')

@section('title', 'تفاصيل وصفة التصنيع')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-1">وصفة التصنيع للمنتج: {{ $bom->product?->name_ar ?? 'غير محدد' }}</h4>
        <p class="text-muted mb-0">عرض تفاصيل المواد المستخدمة.</p>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('admin.manufacturing.boms.edit', $bom) }}" class="btn btn-outline-primary">تعديل</a>
        <a href="{{ route('admin.manufacturing.boms.index') }}" class="btn btn-outline-secondary">عودة للقائمة</a>
    </div>
</div>

<div class="card shadow-sm mb-4">
    <div class="card-body">
        <dl class="row mb-0">
            <dt class="col-md-3">المنتج</dt>
            <dd class="col-md-9">{{ $bom->product?->name_ar ?? 'منتج محذوف' }}</dd>

            <dt class="col-md-3">المتغير</dt>
            <dd class="col-md-9">{{ $bom->variant_name ?? 'بدون متغير' }}</dd>

            <dt class="col-md-3">آخر تحديث</dt>
            <dd class="col-md-9">{{ $bom->updated_at?->format('Y-m-d H:i') }}</dd>

            <dt class="col-md-3">ملاحظات</dt>
            <dd class="col-md-9">{{ $bom->notes ?: '—' }}</dd>
        </dl>
    </div>
</div>

<div class="card shadow-sm">
    <div class="card-header bg-light">المواد الداخلة في التصنيع</div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-striped mb-0">
                <thead class="table-light">
                    <tr>
                        <th>المادة الخام</th>
                        <th>الوحدة</th>
                        <th class="text-end">الكمية المطلوبة</th>
                        <th class="text-end">تكلفة الوحدة</th>
                        <th class="text-end">التكلفة التقديرية</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($bom->items as $item)
                        <tr>
                            <td>{{ $item->material?->name ?? 'مادة محذوفة' }}</td>
                            <td>{{ $item->material?->unit ?? '—' }}</td>
                            <td class="text-end">{{ number_format((float) $item->quantity, 3) }}</td>
                            <td class="text-end">{{ \App\Support\Currency::format($item->material?->cost_per_unit ?? 0) }}</td>
                            <td class="text-end">{{ \App\Support\Currency::format(($item->material?->cost_per_unit ?? 0) * (float) $item->quantity) }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="text-center py-4 text-muted">لم تُحدد مواد لهذه الوصفة بعد.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
