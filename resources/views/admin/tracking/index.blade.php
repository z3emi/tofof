@extends('admin.layout')

@section('title', 'تتبع الموظفين - الموقع المباشر')

@section('content')
    <div class="row g-4">
        <div class="col-12">
            <div class="card shadow-sm border-0">
                <div class="card-body">
                    @if(($employeesTableMissing ?? false) || ($trackingTableMissing ?? false))
                        <div class="alert alert-warning d-flex align-items-center gap-2" role="alert">
                            <i class="bi bi-exclamation-triangle-fill fs-4"></i>
                            <div>
                                لم يتم العثور على جداول <code>{{ $employeeTableName ?? 'employees' }}</code> أو
                                <code>{{ $trackingTableName ?? 'employee_tracking_logs' }}</code> بعد. يرجى التأكد من تشغيل ترحيلات
                                قاعدة البيانات ذات الصلة قبل استخدام لوحة التتبع.
                            </div>
                        </div>
                    @endif

                    <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-3">
                        <div>
                            <h1 class="h4 mb-1">خريطة المواقع المباشرة</h1>
                            <p class="text-muted mb-0">تحديث تلقائي كل 60 ثانية لعرض أحدث مواقع الموظفين.</p>
                        </div>
                        <div class="text-end">
                            <span class="badge bg-light text-dark" id="tracking-last-update">آخر تحديث: —</span>
                        </div>
                    </div>

                    <div id="live-tracking-map" class="rounded-4 border" style="height: 520px;"></div>
                </div>
            </div>
        </div>

        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <h2 class="h5 mb-3">آخر التحركات المسجلة</h2>
                    <div class="table-responsive">
                        <table class="table align-middle" id="tracking-latest-table">
                            <thead class="table-light">
                            <tr>
                                <th>الموظف</th>
                                <th>القسم</th>
                                <th>الإجراء</th>
                                <th>السرعة (كم/س)</th>
                                <th>البطارية (%)</th>
                                <th>الجهاز</th>
                                <th>وقت التسجيل</th>
                            </tr>
                            </thead>
                            <tbody>
                            <tr id="tracking-empty-row">
                                <td colspan="7" class="text-center text-muted py-4">لا توجد تحركات مسجلة حالياً.</td>
                            </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('styles')
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-o9N1j7kSa3HY0zZFwhfS0G2LF/0Vi0Fxv1ZtmE6F1kE=" crossorigin="" />
    <style>
        #live-tracking-map {
            width: 100%;
            min-height: 520px;
            direction: ltr;
            position: relative;
            background: #f5f5f5;
        }

        #live-tracking-map .leaflet-popup-content {
            font-size: 0.9rem;
            line-height: 1.4;
        }
    </style>
@endpush

@push('scripts')
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-SmSRNclGhnzLzGTT1zPaVhP4ZpX6w1Bs8FkPzYtGv6Y=" crossorigin=""></script>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const trackingTablesReady = @json($trackingTablesReady ?? true);
            const map = L.map('live-tracking-map', {
                zoomControl: true,
                scrollWheelZoom: true
            }).setView([33.3128, 44.3615], 12);

            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '&copy; OpenStreetMap contributors'
            }).addTo(map);

            requestAnimationFrame(() => map.invalidateSize());
            window.addEventListener('resize', () => map.invalidateSize());

            const markers = new Map();
            const tableBody = document.querySelector('#tracking-latest-table tbody');
            const emptyRow = document.getElementById('tracking-empty-row');
            const lastUpdate = document.getElementById('tracking-last-update');

            const actionLabels = {
                checkin: 'تسجيل دخول',
                checkout: 'تسجيل خروج',
                move: 'حركة'
            };

            const actionBadges = {
                checkin: 'bg-success',
                checkout: 'bg-danger',
                move: 'bg-info'
            };

            function formatValue(value, suffix = '') {
                if (value === null || value === undefined || value === '') {
                    return '—';
                }
                return `${value}${suffix}`;
            }

            function updateTable(data) {
                tableBody.innerHTML = '';

                if (!data.length) {
                    tableBody.appendChild(emptyRow);
                    emptyRow.classList.remove('d-none');
                    return;
                }

                emptyRow.classList.add('d-none');

                data.forEach(item => {
                    const row = document.createElement('tr');
                    row.innerHTML = `
                        <td class="fw-semibold">${item.name ?? '—'}</td>
                        <td>${item.department ?? '—'}</td>
                        <td><span class="badge ${actionBadges[item.action] ?? 'bg-secondary'}">${actionLabels[item.action] ?? item.action ?? '—'}</span></td>
                        <td>${formatValue(item.speed)}</td>
                        <td>${formatValue(item.battery_level)}</td>
                        <td>${item.device_id ?? '—'}</td>
                        <td>${item.recorded_at ?? '—'}</td>
                    `;
                    tableBody.appendChild(row);
                });
            }

            function updateMarkers(data) {
                const seen = new Set();

                data.forEach(item => {
                    if (!item.employee_id || !item.gps_lat || !item.gps_long) {
                        return;
                    }

                    const id = item.employee_id;
                    const position = [item.gps_lat, item.gps_long];
                    const popupContent = `
                        <div class="text-start">
                            <div class="fw-semibold">${item.name ?? '—'}</div>
                            <div class="text-muted small">${item.department ?? ''}</div>
                            <div class="mt-2 small">الإجراء: ${actionLabels[item.action] ?? item.action ?? '—'}</div>
                            <div class="small">الوقت: ${item.recorded_at ?? '—'}</div>
                            <div class="small">الجهاز: ${item.device_id ?? '—'}</div>
                        </div>`;

                    if (!markers.has(id)) {
                        const marker = L.marker(position).addTo(map);
                        marker.bindPopup(popupContent);
                        marker.bindTooltip(item.name ?? `موظف ${id}`, {permanent: false});
                        markers.set(id, marker);
                    } else {
                        const marker = markers.get(id);
                        marker.setLatLng(position);
                        marker.setPopupContent(popupContent);
                    }

                    seen.add(id);
                });

                markers.forEach((marker, id) => {
                    if (!seen.has(id)) {
                        map.removeLayer(marker);
                        markers.delete(id);
                    }
                });

                map.invalidateSize();

                if (data.length) {
                    const coordinates = data
                        .filter(item => item.gps_lat && item.gps_long)
                        .map(item => [item.gps_lat, item.gps_long]);

                    if (coordinates.length) {
                        const bounds = L.latLngBounds(coordinates);
                        if (bounds.isValid()) {
                            map.fitBounds(bounds.pad(0.2));
                        }
                    }
                }
            }

            async function refreshTracking() {
                if (!trackingTablesReady) {
                    lastUpdate.textContent = 'آخر تحديث: — (بانتظار تهيئة الجداول)';
                    return;
                }

                try {
                    const response = await fetch('{{ route('admin.hr.tracking.live-data') }}', {
                        headers: {
                            'Accept': 'application/json'
                        }
                    });

                    if (!response.ok) {
                        throw new Error('فشل تحميل بيانات التتبع');
                    }

                    const payload = await response.json();
                    updateMarkers(payload);
                    updateTable(payload);
                    lastUpdate.textContent = `آخر تحديث: ${new Date().toLocaleTimeString('ar-IQ')}`;
                } catch (error) {
                    console.error(error);
                }
            }

            refreshTracking();
            setInterval(refreshTracking, 60000);
        });
    </script>
@endpush
