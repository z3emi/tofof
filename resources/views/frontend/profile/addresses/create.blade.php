@extends('frontend.profile.layout')

@section('title', 'إضافة عنوان جديد')

@push('styles')
    {{-- Leaflet CSS --}}
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />

    <style>
      :root{
        --brand:#6d0e16;
        --brand-dark:#500a10;
        --brand-bg:#fdfaf9;
        --hair:#f0e8e8;
        --soft:#f5eeee;
        --text:#1a1a1a;
      }
      html.dark{
        --brand-bg:#0b0f14;
        --hair:#1f2937;
        --soft:#374151;
        --text:#e5e7eb;
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
        box-shadow: 0 0 0 3px rgba(109,14,22,.12);
      }
      .hint{ color:#9ca3af; font-size:.85rem; margin-top:.35rem; }

      html.dark .control{
        background:#111827 !important; color:#e5e7eb !important; border-color:#374151 !important;
      }
      html.dark .control::placeholder{ color:#94a3af; }
      html.dark .control:focus{
        border-color:#500a10 !important;
        box-shadow: 0 0 0 3px rgba(109,14,22,.25);
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

      /* بطاقة الخريطة - هوية برغندية */
      .map-card{
        margin: 0 0 1rem;
        border: 0;
        border-radius: 0;
        background: transparent;
        padding: 0;
      }
      html.dark .map-card{
        background: transparent;
        border-color: transparent;
      }

      .map-head{
        display:flex;
        align-items:center;
        justify-content:space-between;
        gap:.75rem;
        flex-wrap:wrap;
        margin-bottom:.65rem;
      }
      .map-title-wrap{ display:flex; flex-direction:column; gap:.15rem; }
      .map-title{ color:var(--brand); font-weight:900; font-size:1rem; line-height:1.2; }
      .map-sub{ color:#7a6e6e; font-size:.84rem; }
      html.dark .map-sub{ color:#9ca3af; }

      .map-shell{
        padding: 0;
        border-radius: 22px;
        background: transparent;
        border: 0;
        position: relative;
      }
      html.dark .map-shell{
        background: transparent;
      }

      /* خريطة Leaflet */
      #map{
        height: 320px; width: 100%;
        background: var(--brand-bg);
        border: 2px solid rgba(109,14,22,.28);
        border-radius: 22px;
        margin: 0;
        z-index: 1;
        box-shadow: 0 14px 30px rgba(109,14,22,.14);
      }
      .leaflet-container{ position:relative !important; border-radius:22px; }
      html.dark #map{ border-color: rgba(176,85,96,.45); }

      /* زر تحديد موقعي داخل الخريطة */
      .map-locate-btn{
        position:absolute;
        top:12px;
        left:12px;
        z-index: 600;
      }

      /* مؤشر التحديد الثابت في المنتصف */
      .map-center-pin{
        position:absolute;
        top:50%;
        left:0;
        right:0;
        margin-left:auto;
        margin-right:auto;
        transform: translateY(-100%) rotate(-45deg);
        width: 30px;
        height: 30px;
        border-radius: 999px 999px 999px 0;
        background: linear-gradient(145deg, #8c1c26 0%, #6d0e16 65%);
        border: 2px solid #ffffff;
        box-shadow: 0 8px 18px rgba(109,14,22,.35);
        z-index: 550;
        pointer-events: none;
      }
      .map-center-pin::before{
        content:'';
        position:absolute;
        width: 10px;
        height: 10px;
        border-radius: 999px;
        background:#ffffff;
        top:50%;
        left:50%;
        transform: translate(-50%, -50%);
      }
      .map-center-shadow{
        position:absolute;
        top:50%;
        left:0;
        right:0;
        margin-left:auto;
        margin-right:auto;
        transform: translateY(4px);
        width: 18px;
        height: 8px;
        border-radius: 999px;
        background: rgba(0,0,0,.22);
        filter: blur(1px);
        z-index: 540;
        pointer-events: none;
      }
      html.dark .map-center-shadow{ background: rgba(0,0,0,.4); }
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
        background:#0b0f14; color:#b05560; box-shadow: inset 0 0 0 1px #b05560;
      }
      html.dark .btn-ghost:hover{ background:#6d0e16; color:#fff; box-shadow:none; }

      @media (max-width:640px){ #map{ height: 250px; } }

      /* أخطاء */
      .error{ color:#b91c1c; font-size:.85rem; margin-top:.3rem; font-weight:700; }
      html.dark .error{ color:#f87171; }
    </style>
@endpush

@section('profile-content')
<div>
    <h2 class="text-xl md:text-2xl page-title mb-1">إضافة عنوان شحن جديد</h2>
    <p class="page-sub text-sm md:text-base mb-4 md:mb-6">أدخل بيانات العنوان بدقة لتحسين سرعة التوصيل</p>

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

        {{-- الخريطة أعلى النموذج --}}
        <div class="map-card">
          <div class="map-head">
            <div class="map-title-wrap">
              <div class="map-title">حدد الموقع على الخريطة</div>
            </div>

            <div class="map-toolbar"></div>
          </div>

          <div class="map-shell">
            <button type="button" id="get_location_btn" class="btn-ghost map-locate-btn">
              <i class="bi bi-crosshair2"></i> تحديد موقعي
            </button>
            <div id="map"></div>
            <div class="map-center-pin" aria-hidden="true"></div>
            <div class="map-center-shadow" aria-hidden="true"></div>
          </div>
        </div>

        {{-- قيم الإحداثيات المخفية --}}
        <input type="hidden" name="latitude" id="latitude" value="{{ old('latitude') }}">
        <input type="hidden" name="longitude" id="longitude" value="{{ old('longitude') }}">

        <div class="grid grid-cols-1 gap-3 md:grid-cols-2 md:gap-4">
            {{-- المحافظة --}}
            <div class="field">
                <label for="governorate" class="label">المحافظة</label>
                <select id="governorate" name="governorate" required class="control">
                    <option value="">اختر المحافظة</option>
                  <option value="غير محدد" {{ old('governorate') == 'غير محدد' ? 'selected' : '' }}>عدم التحديد</option>
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
      // التحميل المسبق لأسماء المحافظات للمطابقة
      const governorateAliases = @json(config('locations.iraqi_governorate_aliases'));

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

        const map = L.map('map', { scrollWheelZoom: true, zoomControl: false }).setView([initialLat, initialLng], initialZoom);

        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
          attribution: '&copy; OpenStreetMap',
          subdomains: ['a','b','c']
        }).addTo(map);

        function updateHidden(lat, lng, triggerGeocode = true){
          document.getElementById('latitude').value  = Number(lat).toFixed(6);
          document.getElementById('longitude').value = Number(lng).toFixed(6);
          
          if (triggerGeocode) {
            reverseGeocode(lat, lng);
          }
        }

        // التحديد يتم من مركز الخريطة: المستخدم يحرّك الخريطة والمؤشر ثابت
        map.on('moveend', ()=>{
          const center = map.getCenter();
          updateHidden(center.lat, center.lng);
        });

        // تعبئة أولية للإحداثيات عند فتح الصفحة
        updateHidden(initialLat, initialLng, hasOld);

        // وظيفة جلب البيانات من الإحداثيات
        async function reverseGeocode(lat, lng) {
          try {
            const response = await fetch(`https://nominatim.openstreetmap.org/reverse?format=json&lat=${lat}&lon=${lng}&zoom=18&addressdetails=1&accept-language=ar`);
            const data = await response.json();
            
            if (data && data.address) {
              const addr = data.address;
              
              // 1. تحديد المحافظة
              let state = addr.state || addr.province || addr.city || '';
              state = state.replace('محافظة ', '').trim();
              
              let matchedGov = '';
              for (const [alias, realName] of Object.entries(governorateAliases)) {
                if (state.includes(alias) || alias.includes(state)) {
                  matchedGov = realName;
                  break;
                }
              }

              if (matchedGov) {
                const govSelect = document.getElementById('governorate');
                govSelect.value = matchedGov;
                govSelect.dispatchEvent(new Event('change'));
              }

              // 2. تحديث المنطقة/المدينة (مع تجنب أرقام المحلات "Mahalla")
              const labels = [
                addr.suburb, 
                addr.city_district, 
                addr.town, 
                addr.neighbourhood, 
                addr.quarter, 
                addr.village, 
                addr.city
              ].filter(Boolean);

              let areaLabel = '';
              for (const label of labels) {
                // إذا كان النص يحتوي على حروف عربية وليس مجرد رقم (مثل رقم المحلة 906)
                if (label && !/^\d+$/.test(label.trim())) {
                  areaLabel = label.trim();
                  break;
                }
              }

              // إذا لم نجد اسماً غير رقمي، نستخدم أول خيار متاح
              if (!areaLabel && labels.length > 0) {
                 areaLabel = labels[0];
              }

              if (areaLabel) {
                 document.getElementById('city').value = areaLabel;
              }

              // 3. تفاصيل العنوان (الحي أو الشارع)
              const road = addr.road || '';
              const neighbourhood = addr.neighbourhood || addr.suburb || '';
              if (road || neighbourhood) {
                 const details = [road, neighbourhood].filter(Boolean).join('، ');
                 // نملأها فقط إذا كانت فارغة أو إذا كانت قصيرة جداً
                 const detailsInput = document.getElementById('address_details');
                 if (!detailsInput.value || detailsInput.value.length < 5) {
                    detailsInput.value = details;
                 }
              }
            }
          } catch (error) {
            console.error('Error in reverse geocoding:', error);
          }
        }

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

    (function(){
      const css = document.createElement('style');
      css.textContent = '@keyframes spin{to{transform:rotate(360deg)}}';
      document.head.appendChild(css);
    })();
  </script>
@endpush
