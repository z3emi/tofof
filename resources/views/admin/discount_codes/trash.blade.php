@extends('admin.layout')
@section('title', 'سلة محذوفات أكواد الخصم')

@push('styles')
<style>
    .form-card { border-radius: 0 !important; border: none !important; box-shadow: none !important; background: #fff; width: 100% !important; margin: 0 !important; }
    .form-card-header { background: linear-gradient(135deg, var(--primary-dark) 0%, var(--primary-medium) 100%); padding: 2.5rem 3rem; color: white; border-radius: 0 !important; }
    .table-container { border-radius: 15px; border: 1px solid #f1f5f9; overflow: hidden; background: #fff; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05); }
</style>
@endpush

@section('content')
<div class="form-card">
    <div class="form-card-header d-flex justify-content-between align-items-center">
        <div>
            <h2 class="mb-2 fw-bold text-white"><i class="bi bi-trash-fill me-2"></i> سلة محذوفات الأكواد</h2>
            <p class="mb-0 opacity-75 fs-6 text-white small">إدارة أكواد الخصم المحذوفة مؤقتاً.</p>
        </div>
        <a href="{{ route('admin.discount-codes.index') }}" class="btn btn-outline-light px-4 fw-bold rounded-3"><i class="bi bi-arrow-right me-1"></i> العودة للأكواد</a>
    </div>

    <div class="p-4 p-lg-5">
        <div class="table-container shadow-sm border overflow-hidden">
            <table class="table mb-0 align-middle text-center">
                <thead class="bg-light border-bottom">
                    <tr class="text-muted small fw-bold">
                        <th class="py-3">الكود</th>
                        <th class="py-3">النوع</th>
                        <th class="py-3">القيمة</th>
                        <th class="py-3">تاريخ الحذف</th>
                        <th class="py-3" width="220">الإجراءات</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($discountCodes as $code)
                        <tr>
                            <td class="fw-bold"><code class="bg-light text-brand px-2 py-1 rounded">{{ $code->code }}</code></td>
                            <td>
                                @switch($code->type)
                                    @case('fixed') <span class="badge bg-secondary bg-opacity-10 text-secondary border border-secondary border-opacity-25 px-2">مبلغ ثابت</span> @break
                                    @case('percentage') <span class="badge bg-info bg-opacity-10 text-info border border-info border-opacity-25 px-2">نسبة مئوية</span> @break
                                    @case('free_shipping') <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 px-2">شحن مجاني</span> @break
                                    @default — 
                                @endswitch
                            </td>
                            <td class="fw-bold">
                                @if($code->type === 'fixed')
                                    {{ number_format($code->value, 0) }} د.ع
                                @elseif($code->type === 'percentage')
                                    {{ $code->value }}%
                                @else
                                    — 
                                @endif
                            </td>
                            <td class="small text-muted">{{ $code->deleted_at->format('Y-m-d H:i') }}</td>
                            <td>
                                <div class="d-flex justify-content-center gap-2">
                                    @can('edit-discount-codes')
                                        <form action="{{ route('admin.discount-codes.restore', $code->id) }}" method="POST">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-outline-success rounded-3 px-3 fw-bold">
                                                <i class="bi bi-arrow-counterclockwise me-1"></i> استرجاع
                                            </button>
                                        </form>
                                    @endcan
                                    @can('edit-discount-codes')
                                        <form action="{{ route('admin.discount-codes.forceDelete', $code->id) }}" method="POST" onsubmit="return confirm('حذف نهائي؟')">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-outline-danger rounded-3 px-3 fw-bold">
                                                <i class="bi bi-trash-fill me-1"></i> حذف نهائي
                                            </button>
                                        </form>
                                    @endcan
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="py-5 text-center text-muted">سلة المحذوفات فارغة حالياً.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($discountCodes->hasPages())
            <div class="mt-4 d-flex justify-content-center">
                {{ $discountCodes->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
