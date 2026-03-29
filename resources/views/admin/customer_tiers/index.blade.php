@extends('admin.layout')

@section('title', 'إعدادات فئات العملاء')

@push('styles')
<style>
    .form-card { border-radius: 0 !important; border: none !important; box-shadow: none !important; background: #fff; width: 100% !important; margin: 0 !important; }
    .form-card-header { background: linear-gradient(135deg, var(--primary-dark) 0%, var(--primary-medium) 100%); padding: 2.5rem 3rem; color: white; border-radius: 0 !important; }
    .form-section-title { font-size: 1.1rem; font-weight: 700; color: var(--primary-dark); margin-bottom: 1.5rem; padding-bottom: 0.5rem; border-bottom: 2px solid var(--accent-gold); display: inline-block; }
    .tier-input-group { background: #f8fafc; padding: 2rem; border-radius: 16px; border: 1px solid #e2e8f0; }
</style>
@endpush

@section('content')
<div class="form-card">
    <div class="form-card-header d-flex justify-content-between align-items-center flex-wrap gap-3">
        <div>
            <h2 class="mb-2 fw-bold text-white"><i class="bi bi-star-fill me-2"></i> مستويات عضوية العملاء</h2>
            <p class="mb-0 opacity-75 fs-6 text-white small">تحديد قواعد الترقية التلقائية للعملاء بناءً على عدد الطلبات المكتملة.</p>
        </div>
    </div>
    
    <div class="p-4 p-lg-5">
        <form action="{{ route('admin.customer-tiers.update') }}" method="POST">
            @csrf
            <div class="row justify-content-center">
                <div class="col-xl-8">
                    <div class="tier-input-group shadow-sm">
                        <h5 class="form-section-title">قواعد الترقية الأوتوماتيكية</h5>
                        <p class="text-muted small mb-4">أدخل عدد الطلبات المكتملة (تم التوصيل) المطلوبة لكل مستوى للترقية التلقائية.</p>
                        
                        <div class="row g-4">
                            <div class="col-md-4">
                                <label class="form-label fw-bold mb-2">🥉 الفئة البرونزية</label>
                                <div class="input-group">
                                    <input type="number" name="tier_bronze_orders" class="form-control form-control-lg" value="{{ $settings['tier_bronze_orders'] ?? 5 }}" required>
                                    <span class="input-group-text bg-white">طلب</span>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-bold mb-2">🥈 الفئة الفضية</label>
                                <div class="input-group">
                                    <input type="number" name="tier_silver_orders" class="form-control form-control-lg" value="{{ $settings['tier_silver_orders'] ?? 8 }}" required>
                                    <span class="input-group-text bg-white">طلب</span>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-bold mb-2">🥇 الفئة الذهبية</label>
                                <div class="input-group">
                                    <input type="number" name="tier_gold_orders" class="form-control form-control-lg" value="{{ $settings['tier_gold_orders'] ?? 10 }}" required>
                                    <span class="input-group-text bg-white">طلب</span>
                                </div>
                            </div>
                        </div>

                        <div class="mt-5 border-top pt-4 text-start">
                            <button type="submit" class="btn text-white px-5 py-3 fw-bold" style="background:var(--primary-dark); border-radius:12px; font-size:1.1rem">
                                <i class="bi bi-check2-circle me-1"></i> حفظ وتطبيق الإعدادات
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection
