@extends('admin.layout')
@section('title', 'إعدادات فئات العملاء')

@section('content')
<form action="{{ route('admin.customer-tiers.update') }}" method="POST">
    @csrf
    <div class="card shadow-sm">
        <div class="card-header">
            <h4 class="mb-0">إعدادات فئات العملاء</h4>
        </div>
        <div class="card-body">
            <p class="text-muted">حدد عدد الطلبات المكتملة (تم التوصيل) المطلوبة لكل فئة.</p>
            <div class="mb-3">
                <label for="tier_bronze_orders" class="form-label">🥉 الفئة البرونزية</label>
                <input type="number" name="tier_bronze_orders" id="tier_bronze_orders" class="form-control" value="{{ $settings['tier_bronze_orders'] ?? 5 }}" required>
            </div>
            <div class="mb-3">
                <label for="tier_silver_orders" class="form-label">🥈 الفئة الفضية</label>
                <input type="number" name="tier_silver_orders" id="tier_silver_orders" class="form-control" value="{{ $settings['tier_silver_orders'] ?? 8 }}" required>
            </div>
            <div class="mb-3">
                <label for="tier_gold_orders" class="form-label">🥇 الفئة الذهبية</label>
                <input type="number" name="tier_gold_orders" id="tier_gold_orders" class="form-control" value="{{ $settings['tier_gold_orders'] ?? 10 }}" required>
            </div>
        </div>
        <div class="card-footer text-end">
            <button type="submit" class="btn btn-primary">حفظ الإعدادات</button>
        </div>
    </div>
</form>
@endsection
