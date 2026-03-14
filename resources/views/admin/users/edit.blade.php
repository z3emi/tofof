@extends('admin.layout')

@section('title', 'تعديل بيانات المستخدم')

@push('styles')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<style>
    .avatar-upload {
        position: relative;
        max-width: 120px;
        margin: 0 auto 1rem;
    }
    .avatar-edit {
        position: absolute;
        right: 0;
        z-index: 1;
        top: 0;
    }
    .avatar-edit input {
        display: none;
    }
    .avatar-edit label {
        display: inline-block;
        width: 34px;
        height: 34px;
        margin-bottom: 0;
        border-radius: 100%;
        background: #FFFFFF;
        border: 1px solid transparent;
        box-shadow: 0px 2px 4px 0px rgba(0, 0, 0, 0.12);
        cursor: pointer;
        font-weight: normal;
        transition: all 0.2s ease-in-out;
    }
    .avatar-edit label:hover {
        background: #f1f1f1;
        border-color: #d6d6d6;
    }
    .avatar-edit label i {
        color: #757575;
        position: absolute;
        top: 8px;
        left: 0;
        right: 0;
        text-align: center;
        margin: auto;
    }
    .avatar-preview {
        width: 120px;
        height: 120px;
        position: relative;
        border-radius: 100%;
        border: 6px solid #F8F8F8;
        box-shadow: 0px 2px 4px 0px rgba(0, 0, 0, 0.1);
    }
    .avatar-preview > div {
        width: 100%;
        height: 100%;
        border-radius: 100%;
        background-size: cover;
        background-repeat: no-repeat;
        background-position: center;
    }
    #map { height: 300px; border-radius: 0.5rem; border: 1px solid #dee2e6; }
</style>
@endpush

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-lg-8 mx-auto">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                    <h5 class="mb-0 fw-bold"><i class="bi bi-person-gear me-2"></i>تعديل بيانات: {{ $user->name }}</h5>
                    <span class="badge bg-light text-dark border">ID: #{{ $user->id }}</span>
                </div>
                <div class="card-body p-4">
                    <form action="{{ route('admin.users.update', $user->id) }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')

                        @if ($errors->any())
                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                <ul class="mb-0">
                                    @foreach ($errors->all() as $error) <li>{{ $error }}</li> @endforeach
                                </ul>
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                        @endif

                        <div class="row">
                            {{-- الصورة الشخصية --}}
                            <div class="col-12 text-center mb-4">
                                <div class="avatar-upload">
                                    <div class="avatar-edit">
                                        <input type='file' id="imageUpload" name="avatar" accept=".png, .jpg, .jpeg, .webp" />
                                        <label for="imageUpload"><i class="bi bi-camera-fill"></i></label>
                                    </div>
                                    <div class="avatar-preview">
                                        <div id="imagePreview" style="background-image: url('{{ $user->avatar_url }}');">
                                        </div>
                                    </div>
                                </div>
                                <div class="form-check d-inline-block mt-2">
                                    <input class="form-check-input" type="checkbox" name="reset_avatar" value="1" id="resetAvatar">
                                    <label class="form-check-label small text-muted" for="resetAvatar">إعادة تعيين الصورة الافتراضية</label>
                                </div>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="name" class="form-label fw-semibold">الاسم الكامل <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light"><i class="bi bi-person"></i></span>
                                    <input type="text" class="form-control" id="name" name="name" value="{{ old('name', $user->name) }}" required>
                                </div>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="phone_number" class="form-label fw-semibold">رقم الهاتف <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light"><i class="bi bi-phone"></i></span>
                                    <input type="text" class="form-control" id="phone_number" name="phone_number" value="{{ old('phone_number', $user->phone_number) }}" required>
                                </div>
                            </div>

                            <div class="col-md-12 mb-3">
                                <label for="email" class="form-label fw-semibold">البريد الإلكتروني (اختياري)</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light"><i class="bi bi-envelope"></i></span>
                                    <input type="email" class="form-control" id="email" name="email" value="{{ old('email', $user->email) }}">
                                </div>
                            </div>

                            <div class="col-12 mt-3 p-3 bg-light rounded-3 border mb-4">
                                <div class="row">
                                    <div class="col-md-6 mb-3 mb-md-0">
                                        <label for="password" class="form-label fw-semibold text-muted small">كلمة المرور الجديدة</label>
                                        <div class="input-group">
                                            <span class="input-group-text bg-white"><i class="bi bi-lock"></i></span>
                                            <input type="password" class="form-control border-start-0" id="password" name="password" placeholder="اتركه فارغاً للحفاظ على القديمة">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="password_confirmation" class="form-label fw-semibold text-muted small">تأكيد كلمة المرور</label>
                                        <div class="input-group">
                                            <span class="input-group-text bg-white"><i class="bi bi-lock-check"></i></span>
                                            <input type="password" class="form-control border-start-0" id="password_confirmation" name="password_confirmation" placeholder="أعد كتابة كلمة المرور">
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="col-12 mt-2 pt-2 border-top">
                                <h6 class="fw-bold mb-3 mt-2 text-primary"><i class="bi bi-geo-alt me-2"></i>معلومات الموقع</h6>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="governorate" class="form-label fw-semibold">المحافظة</label>
                                <select name="governorate" id="governorate" class="form-select">
                                    <option value="">اختر المحافظة...</option>
                                    @foreach($governorates as $gov)
                                        <option value="{{ $gov }}" {{ old('governorate', $user->governorate) == $gov ? 'selected' : '' }}>{{ $gov }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="city" class="form-label fw-semibold">المدينة / المنطقة</label>
                                <input type="text" class="form-control" id="city" name="city" value="{{ old('city', $user->city) }}" placeholder="اسم المدينة أو المنطقة">
                            </div>

                            <div class="col-12 mb-3">
                                <label for="address" class="form-label fw-semibold">العنوان التفصيلي</label>
                                <textarea class="form-control" id="address" name="address" rows="2" placeholder="الشارع، رقم المنزل، نقطة دالة...">{{ old('address', $user->address) }}</textarea>
                            </div>

                            <div class="col-12 mb-3">
                                <label class="form-label fw-semibold">تحديد الموقع على الخريطة</label>
                                <div id="map"></div>
                                <input type="hidden" name="latitude" id="latitude" value="{{ old('latitude', $user->latitude) }}">
                                <input type="hidden" name="longitude" id="longitude" value="{{ old('longitude', $user->longitude) }}">
                                <small class="text-muted mt-2 d-block">قم بتحريك العلامة لتحديد الموقع بدقة</small>
                            </div>
                        </div>

                        <div class="d-flex justify-content-between mt-4">
                            <a href="{{ route('admin.users.index') }}" class="btn btn-light px-4">إلغاء</a>
                            <button type="submit" class="btn btn-primary px-5" style="background-color: #cd8985; border-color: #cd8985;">حفظ التغييرات</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
    function readURL(input) {
        if (input.files && input.files[0]) {
            var reader = new FileReader();
            reader.onload = function(e) {
                document.getElementById('imagePreview').style.backgroundImage = 'url(' + e.target.result + ')';
            }
            reader.readAsDataURL(input.files[0]);
        }
    }

    document.getElementById("imageUpload").addEventListener("change", function() {
        readURL(this);
    });

    // Map logic
    document.addEventListener('DOMContentLoaded', function() {
        var defaultLat = 33.3152;
        var defaultLng = 44.3661;
        
        var lat = document.getElementById('latitude').value || defaultLat;
        var lng = document.getElementById('longitude').value || defaultLng;

        var map = L.map('map').setView([lat, lng], 13);
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '&copy; OpenStreetMap contributors'
        }).addTo(map);

        var marker = L.marker([lat, lng], {
            draggable: true
        }).addTo(map);

        marker.on('dragend', function(event) {
            var position = marker.getLatLng();
            document.getElementById('latitude').value = position.lat;
            document.getElementById('longitude').value = position.lng;
        });

        map.on('click', function(e) {
            marker.setLatLng(e.latlng);
            document.getElementById('latitude').value = e.latlng.lat;
            document.getElementById('longitude').value = e.latlng.lng;
        });
    });
</script>
@endpush
