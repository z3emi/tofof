@extends('admin.layout')
@section('title', 'سلة محذوفات أكواد الخصم')

@push('styles')
<style>
  :root{ --brand:#cd8985; --line:#eadbcd; }
  .card-header{ background:#fff; border-bottom:1px solid var(--line); }
</style>
@endpush

@section('content')
<div class="card shadow-sm">
  <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
    <h4 class="mb-0 text-danger"><i class="bi bi-trash"></i> سلة محذوفات أكواد الخصم</h4>
    <a href="{{ route('admin.discount-codes.index') }}" class="btn btn-outline-secondary btn-sm">
      <i class="bi bi-arrow-right me-1"></i> العودة لأكواد الخصم
    </a>
  </div>

  <div class="card-body">
    <div class="table-responsive">
      <table class="table table-bordered text-center align-middle">
        <thead class="table-light">
          <tr>
            <th>الكود</th>
            <th>النوع</th>
            <th>القيمة</th>
            <th>تاريخ الحذف</th>
            <th>العمليات</th>
          </tr>
        </thead>
        <tbody>
          @forelse ($discountCodes as $code)
            <tr>
              <td><code>{{ $code->code }}</code></td>
              <td>
                @switch($code->type)
                    @case('fixed') مبلغ ثابت @break
                    @case('percentage') نسبة مئوية @break
                    @case('free_shipping') شحن مجاني @break
                    @default — 
                @endswitch
              </td>
              <td>
                @if($code->type === 'fixed')
                    {{ number_format($code->value, 0) }} د.ع
                @elseif($code->type === 'percentage')
                    {{ $code->value }}%
                @else
                    — 
                @endif
              </td>
              <td>{{ $code->deleted_at->format('Y-m-d H:i') }}</td>
              <td>
                @can('edit-discount-codes')
                <form action="{{ route('admin.discount-codes.restore', $code->id) }}" method="POST" class="d-inline-block">
                  @csrf
                  <button type="submit" class="btn btn-sm btn-success m-1" title="استرجاع">
                    <i class="bi bi-arrow-counterclockwise"></i> استرجاع
                  </button>
                </form>
                @endcan

                @can('edit-discount-codes')
                <form action="{{ route('admin.discount-codes.forceDelete', $code->id) }}" method="POST" class="d-inline-block" onsubmit="return confirm('تنبيه: سيتم مسح الكود نهائياً ولا يمكن التراجع. هل أنت متأكد؟')">
                  @csrf @method('DELETE')
                  <button type="submit" class="btn btn-sm btn-danger m-1" title="حذف نهائي">
                    <i class="bi bi-trash-fill"></i> حذف نهائي
                  </button>
                </form>
                @endcan
              </td>
            </tr>
          @empty
            <tr>
              <td colspan="5" class="py-4 text-muted">سلة المحذوفات فارغة.</td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </div>

    <div class="mt-3">
      {{ $discountCodes->links() }}
    </div>
  </div>
</div>
@endsection
