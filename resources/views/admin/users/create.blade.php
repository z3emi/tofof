@extends('admin.layout')

@section('title', 'إضافة مستخدم جديد')

@section('content')
@push('styles')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<style>
    .form-card { border-radius: 0 !important; border: none !important; box-shadow: none !important; background: #fff; width: 100% !important; margin: 0 !important; }
    .form-card-header { background: linear-gradient(135deg, var(--primary-dark) 0%, var(--primary-medium) 100%); padding: 2.5rem 3rem; color: white; border-radius: 0 !important; }
    .form-section-title { font-weight: 700; color: var(--primary-dark); border-right: 4px solid var(--accent-gold); padding-right: 15px; margin-bottom: 2rem; }
    .avatar-upload-placeholder { width: 120px; height: 120px; border-radius: 20px; border: 2px dashed #cbd5e1; display: flex; align-items: center; justify-content: center; background: #f8fafc; cursor: pointer; transition: all 0.3s ease; position: relative; overflow: hidden; }
    .avatar-upload-placeholder:hover { border-color: var(--primary-dark); background: #f1f5f9; }
    #map-container { height: 350px; border-radius: 15px; border: 1px solid #e2e8f0; overflow: hidden; }
    .form-control, .form-select { border-radius: 10px; padding: 0.8rem 1.2rem; border: 1px solid #e2e8f0; }
    .submit-btn { background: var(--primary-dark); padding: 1rem 3rem; border-radius: 10px; font-weight: 700; color: white; border: none; }
</style>
@endpush

<div class="form-card">
    <div class="form-card-header">
        <h2 class="mb-2 fw-bold text-white"><i class="bi bi-person-plus-fill me-2"></i> إضافة مستخدم جديد</h2>
        <p class="mb-0 opacity-75 fs-6 text-white small">قم بإنشاء حساب جديد للعملاء أو المستخدمين العاديين.</p>
    </div>
    
    <div class="p-4 p-lg-5">
        @if ($errors->any())
            <div class="alert alert-danger border-0 shadow-sm rounded-4 mb-5">
                <ul class="mb-0">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('admin.users.store') }}" method="POST" enctype="multipart/form-data">
            @csrf

            <div class="text-center mb-5">
                <div class="d-inline-block">
                    <label for="avatar" class="avatar-upload-placeholder mx-auto mb-2">
                        <img id="preview" src="" style="display:none; width:100%; height:100%; object-fit:cover;">
                        <div id="upload-icon" class="text-center">
                            <i class="bi bi-camera fs-2 text-muted"></i>
                            <div class="small text-muted">الصورة الشخصية</div>
                        </div>
                    </label>
                    <input type="file" name="avatar" id="avatar" class="d-none" onchange="previewImage(this)">
                </div>
            </div>

            <div class="mb-5">
                <h5 class="form-section-title">المعلومات الشخصية</h5>
                <div class="row g-4">
                    <div class="col-md-6">
                        <label for="name" class="form-label fw-bold">الاسم الكامل <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="name" name="name" value="{{ old('name') }}" placeholder="أدخل اسم المستخدم" required>
                    </div>
                    <div class="col-md-6">
                        <label for="phone_number" class="form-label fw-bold">رقم الهاتف <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="phone_number" name="phone_number" value="{{ old('phone_number') }}" placeholder="07XXXXXXXX" required>
                    </div>
                    <div class="col-md-6">
                        <label for="email" class="form-label fw-bold">البريد الإلكتروني (اختياري)</label>
                        <input type="email" class="form-control" id="email" name="email" value="{{ old('email') }}" placeholder="user@example.com">
                    </div>
                    <div class="col-md-6">
                        <label for="customer_tier_id" class="form-label fw-bold">فئة السعر (تحديد السعر الافتراضي)</label>
                        <select name="customer_tier_id" id="customer_tier_id" class="form-select">
                            <option value="">فئة العميل العادية</option>
                            @foreach(($customerTiers ?? collect()) as $tier)
                                <option value="{{ $tier->id }}" {{ old('customer_tier_id') == $tier->id ? 'selected' : '' }}>
                                    {{ $tier->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>

            <div class="mb-5">
                <h5 class="form-section-title">الأمان</h5>
                <div class="row g-4">
                    <div class="col-md-6">
                        <label for="password" class="form-label fw-bold">كلمة المرور <span class="text-danger">*</span></label>
                        <input type="password" class="form-control" id="password" name="password" placeholder="••••••••" required>
                    </div>
                    <div class="col-md-6">
                        <label for="password_confirmation" class="form-label fw-bold">تأكيد كلمة المرور <span class="text-danger">*</span></label>
                        <input type="password" class="form-control" id="password_confirmation" name="password_confirmation" placeholder="••••••••" required>
                    </div>
                </div>
            </div>

            <div class="mb-5">
                <h5 class="form-section-title">العنوان والموقع الجغرافي</h5>
                <div class="row g-4">
                    <div class="col-md-6">
                        <label for="address" class="form-label fw-bold">العنوان النصي</label>
                        <textarea class="form-control" id="address" name="address" rows="3" placeholder="العراق، بغداد، ...">{{ old('address') }}</textarea>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-bold">تحديد الموقع على الخارطة (اختياري)</label>
                        <div id="map-container"></div>
                        <input type="hidden" name="latitude" id="latitude" value="{{ old('latitude') }}">
                        <input type="hidden" name="longitude" id="longitude" value="{{ old('longitude') }}">
                    </div>
                </div>
            </div>

            <div class="d-flex justify-content-end gap-3 pt-5 border-top">
                <a href="{{ route('admin.users.index') }}" class="btn btn-light px-5 py-3 rounded-3 fw-bold">إلغاء</a>
                <button type="submit" class="submit-btn shadow-sm py-3 px-5">حفظ المستخدم</button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
    function previewImage(input) {
        if (input.files && input.files[0]) {
            var reader = new FileReader();
            reader.onload = function(e) {
                document.getElementById('preview').src = e.target.result;
                document.getElementById('preview').style.display = 'block';
                document.getElementById('upload-icon').style.display = 'none';
            }
            reader.readAsDataURL(input.files[0]);
        }
    }

    document.addEventListener('DOMContentLoaded', function () {
        const defaultLat = 33.3152;
        const defaultLng = 44.3661;
        
        const map = L.map('map-container').setView([defaultLat, defaultLng], 12);
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png').addTo(map);

        let marker;
        @if(old('latitude') && old('longitude'))
            marker = L.marker([{{ old('latitude') }}, {{ old('longitude') }}]).addTo(map);
            map.setView([{{ old('latitude') }}, {{ old('longitude') }}], 15);
        @endif

        map.on('click', function (e) {
            if (marker) map.removeLayer(marker);
            marker = L.marker(e.latlng).addTo(map);
            document.getElementById('latitude').value = e.latlng.lat;
            document.getElementById('longitude').value = e.latlng.lng;
        });
    });
</script>
@endpush
@endsection
