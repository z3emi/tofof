@extends('frontend.profile.layout')

@section('title', 'إضافة عنوان جديد')

@push('styles')
    {{-- Leaflet CSS --}}
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />

    <style>
      :root{
        --brand:#cd8985;
        --brand-dark:#be6661;
        --brand-bg:#f9f5f1;
        --hair:#f3ece5;
        --soft:#efe4da;
        --text:#4a3f3f;
      }
      html.dark{
        --brand-bg:#0b0f14;
        --hair:#1f2937;
        --soft:#374151;
        --text:#e5e7eb;
      }

      /* سطح موحّد */
      .surface{
        background:#fff;
        border-radius:16px;
        box-shadow: 0 12px 30px rgba(205,137,133,.10), 0 6px 14px rgba(0,0,0,.06);
        padding:1rem;
        border:1px solid var(--hair);
      }
      @media (min-width:768px){ .surface{ padding:1.25rem 1.5rem; } }
      html.dark .surface{
        background:#0f172a !important;
        box-shadow: 0 12px 30px rgba(0,0,0,.35), inset 0 0 0 1px var(--hair) !important;
      }

      .page-title{ color:var(--text); font-weight:800; }
      .page-sub{ color:#7a6e6e; }
      html.dark .page-sub{ color:#9ca3af; }

      /* الحقول */
      .field{ margin-bottom: .9rem; }
      .label{ display:block; color:var(--text); font-weight:700; margin-bottom:.35rem; }
      .control{
        width:100%; border:1px solid var(--hair); border-radius:12px;
        padding:.625rem .75rem; background:#fff; color:var(--text);
        transition:.16s ease; box-shadow: inset 0 0 0 0 rgba(0,0,0,0);
      }
      .control:focus{
        outline: none;
        border-color: var(--brand);
        box-shadow: 0 0 0 3px rgba(205,137,133,.18);
      }
      .hint{ color:#9ca3af; font-size:.85rem; margin-top:.35rem; }

      html.dark .control{
        background:#111827 !important; color:#e5e7eb !important; border-color:#374151 !important;
      }
      html.dark .control::placeholder{ color:#94a3af; }
      html.dark .control:focus{
        border-color:#be6661 !important;
        box-shadow: 0 0 0 3px rgba(190,102,97,.28);
      }

      /* أزرار */
      .btn-brand{
        display:inline-flex; align-items:center; gap:.5rem;
        background:var(--brand); color:#fff; font-weight:800;
        padding:.6rem 1rem; border-radius:12px; border:0; text-decoration:none;
        transition:.18s ease;
      }
      .btn-brand:hover{ background:var(--brand-dark); color:#fff; }

      .btn-secondary{
        display:inline-flex; align-items:center; gap:.5rem;
        background:#f3f4f6; color:#374151; font-weight:800;
        padding:.6rem 1rem; border-radius:12px; border:1px solid #e5e7eb; text-decoration:none;
        transition:.18s ease;
      }
      .btn-secondary:hover{ background:#e5e7eb; }
      html.dark .btn-secondary{
        background:#0b0f14; color:#e5e7eb; border-color:#374151;
      }
      html.dark .btn-secondary:hover{ background:#111827; }

      /* خريطة Leaflet */
      #map{
        height: 300px; width: 100%;
        background: var(--brand-bg);
        border: 1px solid var(--hair);
        border-radius: 12px;
        margin: .75rem 0 0.75rem;
        z-index: 1;
        box-shadow: inset 0 0 0 1px var(--hair);
      }
      .leaflet-container{ position:relative !important; border-radius:12px; }
      .map-toolbar{
        display:flex; gap:.5rem; justify-content:flex-end; flex-wrap:wrap;
      }
      .btn-ghost{
        display:inline-flex; align-items:center; gap:.4rem;
        font-weight:800; font-size:.9rem; color:var(--brand);
        background:#fff; border-radius:10px; padding:.45rem .75rem;
        box-shadow: inset 0 0 0 1px var(--brand);
        transition:.18s ease; border:0;
      }
      .btn-ghost:hover{ background:var(--brand); color:#fff; box-shadow:none; }
      html.dark .btn-ghost{
        background:#0b0f14; color:#f0b0ad; box-shadow: inset 0 0 0 1px #f0b0ad;
      }
      html.dark .btn-ghost:hover{ background:#cd8985; color:#0b0f14; box-shadow:none; }

      @media (max-width:640px){ #map{ height: 260px; } }

      /* أخطاء */
      .error{ color:#b91c1c; font-size:.85rem; margin-top:.3rem; font-weight:700; }
      html.dark .error{ color:#f87171; }
    </style>
@endpush

@section('profile-content')
<div class="surface">
    <h2 class="text-xl md:text-2xl page-title mb-1">إضافة عنوان شحن جديد</h2>
    <p class="page-sub text-sm md:text-base mb-4 md:mb-6">أدخلي بيانات العنوان بدقة لتحسين سرعة التوصيل</p>

    {{-- رسائل الأخطاء العامة --}}
    @if ($errors->any())
      <div style="background:#fff5f5; border:1px solid #fecaca; color:#7f1d1d; border-radius:12px; padding:.75rem .9rem; margin-bottom:1rem;">
        يرجى التحقق من الحقول المدخلة أدناه.
      </div>
    @endif

    <form action="{{ route('profile.addresses.store') }}" method="POST">
        @csrf

        {{-- مهم: عنوان الرجوع بعد الحفظ (صفحة المصدر) --}}
        <input type="hidden" name="return_to" value="{{ request('return_to', url()->previous()) }}">

        <div class="grid grid-cols-1 gap-3 md:grid-cols-2 md:gap-4">
            {{-- المحافظة --}}
            <div class="field">
                <label for="governorate" class="label">المحافظة</label>
                <select id="governorate" name="governorate" required class="control">
                    <option value="">اختر المحافظة</option>
                    @php
                        $governorates = config('locations.iraqi_governorates');
                    @endphp
                    @foreach ($governorates as $gov)
                        <option value="{{ $gov }}" {{ old('governorate') == $gov ? 'selected' : '' }}>{{ $gov }}</option>
                    @endforeach
                </select>
                @error('governorate') <div class="error">{{ $message }}</div> @enderror
            </div>

            {{-- المدينة --}}
            <div class="field">
                <label for="city" class="label">المدينة / القضاء</label>
                <input type="text" id="city" name="city" value="{{ old('city') }}" class="control" required>
                @error('city') <div class="error">{{ $message }}</div> @enderror
            </div>
        </div>

        {{-- تفاصيل العنوان --}}
        <div class="field">
            <label for="address_details" class="label">تفاصيل العنوان</label>
            <input type="text" id="address_details" name="address_details" value="{{ old('address_details') }}" class="control" placeholder="اسم الشارع، رقم الزقاق، رقم الدار" required>
            @error('address_details') <div class="error">{{ $message }}</div> @enderror
        </div>

        {{-- أقرب نقطة دالة --}}
        <div class="field">
            <label for="nearest_landmark" class="label">أقرب نقطة دالة (اختياري)</label>
            <input type="text" id="nearest_landmark" name="nearest_landmark" value="{{ old('nearest_landmark') }}" class="control" placeholder="مثال: قرب جامع الحبوبي">
            @error('nearest_landmark') <div class="error">{{ $message }}</div> @enderror
        </div>

        <div style="height:1px; background:var(--hair); margin:1rem 0;"></div>

        {{-- أدوات الخريطة --}}
        <div class="map-toolbar">
          <button type="button" id="get_location_btn" class="btn-ghost">
            <i class="bi bi-geo-alt-fill"></i> تحديد موقعي الحالي
          </button>
        </div>

        {{-- الخريطة --}}
        <div id="map"></div>
        <p class="hint">تحديد الموقع على الخريطة اختياري، لكنه يساعدنا في الوصول بشكل أسرع.</p>

        {{-- قيم الإحداثيات المخفية --}}
        <input type="hidden" name="latitude" id="latitude" value="{{ old('latitude') }}">
        <input type="hidden" name="longitude" id="longitude" value="{{ old('longitude') }}">

        {{-- أزرار الإرسال --}}
        <div class="flex flex-col sm:flex-row justify-start gap-3 pt-3 md:pt-4">
            <button type="submit" class="btn-brand">
                <i class="bi bi-check2-circle"></i> حفظ العنوان
            </button>
            <a href="{{ route('profile.addresses.index') }}" class="btn-secondary">
                <i class="bi bi-arrow-right"></i> إلغاء
            </a>
        </div>
    </form>
</div>
@endsection

@push('scripts')
  {{-- Leaflet JS --}}
  <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

  <script>
    document.addEventListener('DOMContentLoaded', function() {
      // تأخير بسيط لضمان اكتمال الرسم
      setTimeout(initMap, 80);

      function initMap(){
        const mapEl = document.getElementById('map');
        if(!mapEl) return;

        const oldLat = parseFloat(document.getElementById('latitude').value || '');
        const oldLng = parseFloat(document.getElementById('longitude').value || '');
        const hasOld = !isNaN(oldLat) && !isNaN(oldLng);

        const initialLat = hasOld ? oldLat : 33.3152;  // بغداد
        const initialLng = hasOld ? oldLng : 44.3661;
        const initialZoom = hasOld ? 15 : 12;

        const map = L.map('map', { scrollWheelZoom: true }).setView([initialLat, initialLng], initialZoom);

        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
          attribution: '&copy; OpenStreetMap',
          subdomains: ['a','b','c']
        }).addTo(map);

        // الماركر القابل للسحب
        const marker = L.marker([initialLat, initialLng], { draggable:true, autoPan:true })
                        .addTo(map)
                        .bindPopup('اسحبني لتحديد الموقع الدقيق')
                        .openPopup();

        function updateHidden(lat, lng){
          document.getElementById('latitude').value  = Number(lat).toFixed(6);
          document.getElementById('longitude').value = Number(lng).toFixed(6);
        }
        if(!hasOld){ updateHidden(initialLat, initialLng); }

        marker.on('dragend', e=>{
          const {lat, lng} = e.target.getLatLng();
          updateHidden(lat, lng);
        });

        map.on('click', e=>{
          marker.setLatLng(e.latlng);
          updateHidden(e.latlng.lat, e.latlng.lng);
        });

        // زر "تحديد موقعي"
        const locateBtn = document.getElementById('get_location_btn');
        locateBtn.addEventListener('click', function(){
          if(!navigator.geolocation){
            alert('المتصفح لا يدعم خدمة تحديد الموقع');
            return;
          }
          const originalHTML = this.innerHTML;
          this.innerHTML = '<i class="bi bi-arrow-repeat" style="animation:spin 1s linear infinite;"></i> جاري التحديد...';

          navigator.geolocation.getCurrentPosition(pos=>{
              const { latitude, longitude } = pos.coords;
              map.setView([latitude, longitude], 15);
              marker.setLatLng([latitude, longitude]);
              updateHidden(latitude, longitude);
              this.innerHTML = originalHTML;
            }, err=>{
              alert('تعذّر تحديد الموقع: ' + err.message);
              this.innerHTML = originalHTML;
            }, { enableHighAccuracy:true, timeout:10000 }
          );
        });

        // إصلاح الحجم بعد التحميل
        setTimeout(()=> map.invalidateSize(), 200);
      }
    });

    // دوران أيقونة البتن (في حال ماكو Tailwind animate-spin)
    (function(){
      const css = document.createElement('style');
      css.textContent = '@keyframes spin{to{transform:rotate(360deg)}}';
      document.head.appendChild(css);
    })();
  </script>
@endpush
