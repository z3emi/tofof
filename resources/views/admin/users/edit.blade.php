@extends('admin.layout')

@section('title', 'تعديل المستخدم: ' . $user->name)

@section('content')
@push('styles')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<style>
    .form-card { border-radius: 0 !important; border: none !important; box-shadow: none !important; background: #fff; width: 100% !important; margin: 0 !important; }
    .form-card-header { background: linear-gradient(135deg, var(--primary-dark) 0%, var(--primary-medium) 100%); padding: 2.5rem 3rem; color: white; border-radius: 0 !important; }
    .form-section-title { font-weight: 700; color: var(--primary-dark); border-right: 4px solid var(--accent-gold); padding-right: 15px; margin-bottom: 2rem; }
    .avatar-upload-container { position: relative; width: 140px; height: 140px; margin: 0 auto 1.5rem; }
    .avatar-preview { width: 100%; height: 100%; border-radius: 20px; object-fit: cover; border: 4px solid #fff; box-shadow: 0 10px 20px rgba(0,0,0,0.1); }
    .avatar-edit-btn { position: absolute; bottom: -5px; left: -5px; width: 40px; height: 40px; background: var(--accent-gold); color: white; border-radius: 12px; display: flex; align-items: center; justify-content: center; cursor: pointer; box-shadow: 0 5px 15px rgba(0,0,0,0.2); }
    #map-container { height: 350px; border-radius: 15px; border: 1px solid #e2e8f0; overflow: hidden; }
    .submit-btn { background: var(--primary-dark); padding: 1rem 3rem; border-radius: 10px; font-weight: 700; color: white; border: none; }
</style>
@endpush

<div class="form-card">
    <div class="form-card-header">
        <h2 class="mb-2 fw-bold text-white"><i class="bi bi-person-gear me-2"></i> تعديل بيانات المستخدم: {{ $user->name }}</h2>
        <p class="mb-0 opacity-75 fs-6 text-white small">قم بتحديث معلومات الحساب أو العناوين الجغرافية.</p>
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

        <form action="{{ route('admin.users.update', $user->id) }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')

            <div class="text-center mb-5">
                <div class="avatar-upload-container">
                    <img src="{{ $user->avatar_url }}" id="previewImg" class="avatar-preview" onerror="this.src='{{ asset('storage/avatars/default.jpg') }}'">
                    <label for="avatar" class="avatar-edit-btn"><i class="bi bi-camera-fill"></i></label>
                    <input type="file" id="avatar" name="avatar" class="d-none" accept="image/*" onchange="previewUpdate(this)">
                </div>
                <div class="form-check d-inline-block">
                    <input class="form-check-input" type="checkbox" name="reset_avatar" id="reset_avatar" value="1">
                    <label class="form-check-label small" for="reset_avatar">استعادة الصورة الافتراضية</label>
                </div>
            </div>

            <div class="mb-5">
                <h5 class="form-section-title">المعلومات الشخصية</h5>
                <div class="row g-4">
                    <div class="col-md-6">
                        <label for="name" class="form-label fw-bold">الاسم الكامل <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" style="border-radius:12px; padding:0.8rem" id="name" name="name" value="{{ old('name', $user->name) }}" required>
                    </div>
                    <div class="col-md-6">
                        <label for="phone_number" class="form-label fw-bold">رقم الهاتف <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" style="border-radius:12px; padding:0.8rem" id="phone_number" name="phone_number" value="{{ old('phone_number', $user->phone_number) }}" required>
                    </div>
                    <div class="col-md-6">
                        <label for="email" class="form-label fw-bold">البريد الإلكتروني (اختياري)</label>
                        <input type="email" class="form-control" style="border-radius:12px; padding:0.8rem" id="email" name="email" value="{{ old('email', $user->email) }}">
                    </div>
                    <div class="col-md-6">
                        <h6 class="fw-bold mb-3 small" style="color: var(--primary-dark);">كلمة المرور والأمان</h6>
                        <div class="row g-2">
                            <div class="col-6"><input type="password" class="form-control" style="border-radius:12px; padding:0.8rem" id="password" name="password" placeholder="كلمة مرور جديدة"></div>
                            <div class="col-6"><input type="password" class="form-control" style="border-radius:12px; padding:0.8rem" id="password_confirmation" name="password_confirmation" placeholder="تأكيد الكلمة"></div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="mb-5">
                <h5 class="form-section-title">العناوين والموقع الجغرافي</h5>
                <div class="row g-4">
                    <div class="col-md-6">
                        <label for="address" class="form-label fw-bold">العنوان النصي الحالي</label>
                        <textarea class="form-control" style="border-radius:12px; padding:0.8rem" id="address" name="address" rows="3">{{ old('address', $user->address) }}</textarea>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-bold small">تحديد الموقع بدقة على الخارطة</label>
                        <div id="map-container"></div>
                        <input type="hidden" name="latitude" id="latitude" value="{{ old('latitude', $user->latitude) }}">
                        <input type="hidden" name="longitude" id="longitude" value="{{ old('longitude', $user->longitude) }}">
                    </div>
                </div>
            </div>

            <div class="d-flex justify-content-end gap-3 pt-5 border-top">
                <a href="{{ route('admin.users.index') }}" class="btn btn-light px-5 py-3 rounded-3 fw-bold">إلغاء</a>
                <button type="submit" class="submit-btn shadow-sm py-3 px-5">حفظ التغييرات</button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
    function previewUpdate(input) {
        if (input.files && input.files[0]) {
            var reader = new FileReader();
            reader.onload = function(e) { $('#previewImg').attr('src', e.target.result); }
            reader.readAsDataURL(input.files[0]);
        }
    }

    document.addEventListener('DOMContentLoaded', function () {
        const userLat = {{ $user->latitude ?? 33.3152 }};
        const userLng = {{ $user->longitude ?? 44.3661 }};
        const map = L.map('map-container').setView([userLat, userLng], {{ $user->latitude ? 15 : 12 }});
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png').addTo(map);

        let marker = {{ $user->latitude ? 'L.marker([userLat, userLng]).addTo(map)' : 'null' }};
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
