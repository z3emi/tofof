@extends('frontend.profile.layout')
@section('title', 'أكواد الخصم')

@push('styles')
<style>
  .discount-card {
    border: 1px solid #ece7e7;
    border-radius: 14px;
    background: #fff;
    padding: 1rem;
  }
  .discount-code {
    font-weight: 800;
    letter-spacing: 0.5px;
    background: #f8f3f3;
    border: 1px dashed #6d0e16;
    color: #6d0e16;
    border-radius: 10px;
    padding: 0.5rem 0.8rem;
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
  }
  .status-pill {
    border-radius: 999px;
    padding: 0.2rem 0.7rem;
    font-size: 0.75rem;
    font-weight: 700;
  }
</style>
@endpush

@section('profile-content')
<div class="space-y-4">
  <div>
    <h2 class="text-xl md:text-2xl font-black text-slate-800">أكواد الخصم الخاصة بي</h2>
    <p class="text-sm text-slate-500 mt-1">هذه الأكواد مخصصة لك ويمكنك نسخها واستخدامها مباشرة أثناء الدفع.</p>
  </div>

  @if($codes->isEmpty())
    <div class="bg-white border border-slate-100 rounded-2xl p-8 text-center text-slate-500">
      لا توجد أكواد خصم متاحة لحسابك حالياً.
    </div>
  @else
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
      @foreach($codes as $code)
        @php
          $isExpired = $code->expires_at && $code->expires_at->isPast();
          $isInactive = ! $code->is_active;
        @endphp
        <div class="discount-card">
          <div class="flex items-center justify-between gap-2 mb-3">
            <span class="status-pill {{ $isInactive ? 'bg-gray-100 text-gray-700' : ($isExpired ? 'bg-red-100 text-red-700' : 'bg-emerald-100 text-emerald-700') }}">
              {{ $isInactive ? 'غير مفعل' : ($isExpired ? 'منتهي' : 'متاح') }}
            </span>
            <small class="text-slate-500">
              {{ $code->expires_at ? 'ينتهي: ' . $code->expires_at->format('Y-m-d H:i') : 'بدون انتهاء' }}
            </small>
          </div>

          <div class="flex items-center justify-between gap-3 mb-3">
            <div class="discount-code" id="discount-code-{{ $code->id }}">{{ $code->code }}</div>
            <button type="button" class="px-3 py-2 rounded-xl bg-[#6d0e16] text-white text-sm font-bold" onclick="copyDiscountCode('discount-code-{{ $code->id }}', this)">نسخ</button>
          </div>

          <div class="text-sm text-slate-700">
            @if($code->type === 'percentage')
              خصم {{ rtrim(rtrim(number_format((float) $code->value, 2, '.', ''), '0'), '.') }}%
            @elseif($code->type === 'fixed')
              خصم {{ number_format((float) $code->value, 0) }} د.ع
            @else
              شحن مجاني
            @endif
          </div>
        </div>
      @endforeach
    </div>
  @endif
</div>

<script>
function copyDiscountCode(elementId, button) {
  const text = document.getElementById(elementId)?.innerText?.trim();
  if (!text) return;

  navigator.clipboard.writeText(text).then(() => {
    const prev = button.innerText;
    button.innerText = 'تم النسخ';
    setTimeout(() => button.innerText = prev, 1200);
  });
}
</script>
@endsection
