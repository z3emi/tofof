@extends('admin.layout')
@section('title', 'إعدادات النسخ الاحتياطي')

@section('content')
<form action="{{ route('admin.backups.settings.store') }}" method="POST">
    @csrf
    <div class="card shadow-sm">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h4 class="mb-0">إعدادات النسخ الاحتياطي</h4>
            <a href="{{ route('admin.backups.index') }}" class="btn btn-outline-secondary btn-sm"><i class="bi bi-arrow-right"></i> العودة</a>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-8">
                    {{-- Daily Backups --}}
                    <div class="mb-4">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" role="switch" id="backup_daily_enabled" name="backup_daily_enabled" @checked(old('backup_daily_enabled', $settings['backup_daily_enabled'] ?? 'off') == 'on')>
                            <label class="form-check-label" for="backup_daily_enabled">تفعيل النسخ الاحتياطي اليومي التلقائي</label>
                        </div>
                        <small class="form-text text-muted">يعمل تلقائياً من داخل الموقع بدون Cron. يتم التنفيذ عند أول زيارة بعد الوقت المحدد يومياً.</small>
                    </div>

                    {{-- Daily Backup Time --}}
                    <div class="mb-4">
                        <label for="backup_daily_time" class="form-label">وقت النسخ الاحتياطي اليومي</label>
                        <input type="time" class="form-control" id="backup_daily_time" name="backup_daily_time" value="{{ old('backup_daily_time', $settings['backup_daily_time'] ?? '02:00') }}">
                    </div>

                    <hr class="my-4">

                    {{-- Google Drive Upload --}}
                    <div class="mb-4">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" role="switch" id="backup_google_drive_enabled" name="backup_google_drive_enabled" @checked(old('backup_google_drive_enabled', $settings['backup_google_drive_enabled'] ?? 'off') == 'on')>
                            <label class="form-check-label" for="backup_google_drive_enabled">الرفع التلقائي إلى Google Drive</label>
                        </div>
                        <small class="form-text text-muted">يتطلب تثبيت حزمة إضافية وإعدادات API خاصة.</small>
                    </div>

                    {{-- Auto Delete --}}
                    <div class="mb-4">
                        <label for="backup_auto_delete_after_days" class="form-label">حذف النسخ الاحتياطية القديمة تلقائيًا بعد</label>
                        <select name="backup_auto_delete_after_days" id="backup_auto_delete_after_days" class="form-select">
                            @php $days = [7 => 'أسبوع واحد', 30 => 'شهر واحد', 90 => '3 أشهر', 365 => 'سنة واحدة']; @endphp
                            @foreach($days as $dayCount => $label)
                                  <option value="{{ $dayCount }}" @selected(old('backup_auto_delete_after_days', $settings['backup_auto_delete_after_days'] ?? 30) == $dayCount)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>

                    <hr class="my-4">

                    {{-- Automated Backup (Web Cron) --}}
                    <div class="mb-4">
                        <h5 class="fw-bold"><i class="bi bi-clock-history me-2"></i>رابط تشغيل خارجي (اختياري)</h5>
                        <p class="text-muted small">لا تحتاج هذا الخيار للتشغيل التلقائي الداخلي. استخدمه فقط إذا أردت إجبار تشغيل الجدولة من خدمة خارجية.</p>
                        
                        <label for="cron_token" class="form-label">رمز الأمان للمزامنة (Cron Token)</label>
                        <div class="input-group mb-3">
                            <input type="text" class="form-control" id="cron_token" name="cron_token" value="{{ old('cron_token', $settings['cron_token'] ?? Str::random(32)) }}">
                            <button class="btn btn-outline-secondary" type="button" onclick="document.getElementById('cron_token').value = Math.random().toString(36).substring(2, 15) + Math.random().toString(36).substring(2, 15);">توليد جديد</button>
                        </div>

                        @if(isset($settings['cron_token']) && $settings['cron_token'] != 'off')
                            <div class="alert alert-info">
                                <strong>رابط التشغيل:</strong><br>
                                <code id="cron_url">{{ url('/cron/run?token=' . $settings['cron_token']) }}</code>
                                <button type="button" class="btn btn-sm btn-link p-0 ms-2" onclick="copyToClipboard('cron_url')">نسخ الرابط</button>
                            </div>
                        @else
                            <div class="alert alert-warning">يرجى حفظ الرمز أولاً ليظهر الرابط.</div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
        <div class="card-footer text-end">
            <button type="submit" class="btn btn-primary">حفظ الإعدادات</button>
        </div>
    </div>
</form>
@endsection

@push('scripts')
<script>
function copyToClipboard(elementId) {
    const text = document.getElementById(elementId).innerText;
    navigator.clipboard.writeText(text).then(() => {
        alert('تم نسخ الرابط بنجاح');
    });
}
</script>
@endpush
