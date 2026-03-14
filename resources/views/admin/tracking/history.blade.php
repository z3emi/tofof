@extends('admin.layout')

@section('title', 'سجل تتبع الموظفين')

@section('content')
    <div class="row g-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    @if(($employeesTableMissing ?? false) || ($trackingTableMissing ?? false))
                        <div class="alert alert-warning d-flex align-items-center gap-2" role="alert">
                            <i class="bi bi-exclamation-triangle-fill fs-4"></i>
                            <div>
                                يتعذر تحميل بيانات الموظفين أو سجلات الحركة حالياً. يرجى التأكد من تشغيل ترحيلات قاعدة البيانات
                                الخاصة بجداول <code>{{ $employeeTableName ?? 'employees' }}</code> و
                                <code>{{ $trackingTableName ?? 'employee_tracking_logs' }}</code> قبل عرض السجل التاريخي.
                            </div>
                        </div>
                    @endif

                    <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-4">
                        <div>
                            <h1 class="h4 mb-1">سجل الحركة اليومية</h1>
                            <p class="text-muted mb-0">اختر الموظف والتاريخ لعرض المسار الزمني الكامل.</p>
                        </div>
                        <div class="d-flex gap-3 flex-wrap">
                            <div>
                                <label for="history-employee" class="form-label mb-1">الموظف</label>
                                <select id="history-employee" class="form-select" {{ ($employeesTableMissing ?? false) ? 'disabled' : '' }}>
                                    <option value="">— اختر الموظف —</option>
                                    @foreach($employees as $employee)
                                        <option value="{{ $employee->id }}" data-department="{{ $employee->department ?? '' }}">
                                            {{ $employee->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label for="history-date" class="form-label mb-1">التاريخ</label>
                                <input type="date" id="history-date" class="form-control" value="{{ now()->toDateString() }}" {{ ($trackingTableMissing ?? false) ? 'disabled' : '' }}>
                            </div>
                            <div class="align-self-end">
                                <button type="button" class="btn btn-primary" id="history-refresh-btn" {{ (($employeesTableMissing ?? false) || ($trackingTableMissing ?? false)) ? 'disabled' : '' }}>
                                    <i class="bi bi-arrow-repeat"></i>
                                    تحديث السجل
                                </button>
                            </div>
                        </div>
                    </div>

                    <div id="history-map" class="rounded-4 border" style="height: 520px;"></div>
                </div>
            </div>
        </div>

        <div class="col-12 col-xl-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <h2 class="h5 mb-3">معلومات الموظف</h2>
                    <dl class="row mb-0" id="history-employee-summary">
                        <dt class="col-4 text-muted">الاسم</dt>
                        <dd class="col-8">—</dd>
                        <dt class="col-4 text-muted">القسم</dt>
                        <dd class="col-8">—</dd>
                        <dt class="col-4 text-muted">التاريخ</dt>
                        <dd class="col-8">—</dd>
                        <dt class="col-4 text-muted">عدد النقاط</dt>
                        <dd class="col-8">—</dd>
                    </dl>
                </div>
            </div>
        </div>

        <div class="col-12 col-xl-8">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <h2 class="h5 mb-3">الجدول الزمني للحركة</h2>
                    <ul class="list-group list-group-flush" id="history-timeline">
                        <li class="list-group-item text-center text-muted">اختر موظفاً لعرض السجل.</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('styles')
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-o9N1j7kSa3HY0zZFwhfS0G2LF/0Vi0Fxv1ZtmE6F1kE=" crossorigin="" />
    <style>
        #history-map {
            width: 100%;
            min-height: 520px;
            direction: ltr;
            position: relative;
            background: #f5f5f5;
        }

        #history-map .leaflet-popup-content {
            font-size: 0.9rem;
        }
    </style>
@endpush

@push('scripts')
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-SmSRNclGhnzLzGTT1zPaVhP4ZpX6w1Bs8FkPzYtGv6Y=" crossorigin=""></script>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const employeesTableMissing = @json($employeesTableMissing ?? false);
            const trackingTableMissing = @json($trackingTableMissing ?? false);
            const requirementsReady = !(employeesTableMissing || trackingTableMissing);

            const map = L.map('history-map').setView([33.3128, 44.3615], 12);
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '&copy; OpenStreetMap contributors'
            }).addTo(map);

            requestAnimationFrame(() => map.invalidateSize());
            window.addEventListener('resize', () => map.invalidateSize());

            const markers = [];
            let routeLine = null;

            const employeeSelect = document.getElementById('history-employee');
            const dateInput = document.getElementById('history-date');
            const refreshBtn = document.getElementById('history-refresh-btn');
            const timeline = document.getElementById('history-timeline');
            const summary = document.getElementById('history-employee-summary');
            const summaryRows = summary.querySelectorAll('dd');

            const historyEndpointTemplate = '{{ route('admin.hr.tracking.history-data', ['employee' => '__EMPLOYEE__']) }}';

            const actionLabels = {
                checkin: 'تسجيل دخول',
                checkout: 'تسجيل خروج',
                move: 'حركة'
            };

            function clearMap() {
                markers.forEach(marker => map.removeLayer(marker));
                markers.length = 0;

                if (routeLine) {
                    map.removeLayer(routeLine);
                    routeLine = null;
                }
            }

            function clearTimeline(message = 'اختر موظفاً لعرض السجل.') {
                timeline.innerHTML = `<li class="list-group-item text-center text-muted">${message}</li>`;
            }

            function updateSummary(data = null) {
                if (!data) {
                    summaryRows[0].textContent = '—';
                    summaryRows[1].textContent = '—';
                    summaryRows[2].textContent = '—';
                    summaryRows[3].textContent = '—';
                    return;
                }

                summaryRows[0].textContent = data.employee.name ?? '—';
                summaryRows[1].textContent = data.employee.department ?? '—';
                summaryRows[2].textContent = data.date ?? '—';
                summaryRows[3].textContent = data.logs.length;
            }

            function buildEndpoint(employeeId) {
                return historyEndpointTemplate.replace('__EMPLOYEE__', employeeId);
            }

            function renderTimeline(logs) {
                if (!logs.length) {
                    clearTimeline('لا توجد تحركات في هذا اليوم.');
                    return;
                }

                timeline.innerHTML = '';

                logs.forEach(log => {
                    const item = document.createElement('li');
                    item.className = 'list-group-item d-flex justify-content-between align-items-start';
                    item.innerHTML = `
                        <div>
                            <div class="fw-semibold">${log.recorded_at ?? '—'}</div>
                            <div class="text-muted small">${actionLabels[log.action] ?? log.action ?? '—'}</div>
                            ${log.address ? `<div class="small">${log.address}</div>` : ''}
                        </div>
                        <div class="text-end small text-muted">
                            <div>خط العرض: ${log.gps_lat ?? '—'}</div>
                            <div>خط الطول: ${log.gps_long ?? '—'}</div>
                        </div>
                    `;
                    timeline.appendChild(item);
                });
            }

            function renderMap(logs) {
                clearMap();

                map.invalidateSize();

                if (!logs.length) {
                    return;
                }

                const coordinates = logs
                    .filter(log => log.gps_lat && log.gps_long)
                    .map(log => [log.gps_lat, log.gps_long]);

                if (!coordinates.length) {
                    return;
                }

                routeLine = L.polyline(coordinates, {color: '#0d6efd', weight: 4}).addTo(map);
                map.invalidateSize();
                map.fitBounds(routeLine.getBounds().pad(0.2));

                logs.forEach(log => {
                    if (!log.gps_lat || !log.gps_long) {
                        return;
                    }

                    const marker = L.circleMarker([log.gps_lat, log.gps_long], {
                        radius: 7,
                        color: '#0d6efd',
                        fillColor: '#0d6efd',
                        fillOpacity: 0.8
                    }).addTo(map);

                    marker.bindTooltip(`${log.recorded_at ?? '—'}`, {permanent: false});
                    marker.bindPopup(`
                        <div class="text-start">
                            <div class="fw-semibold mb-1">${actionLabels[log.action] ?? log.action ?? '—'}</div>
                            <div class="small">${log.recorded_at ?? '—'}</div>
                            ${log.address ? `<div class="small text-muted mt-1">${log.address}</div>` : ''}
                        </div>
                    `);

                    markers.push(marker);
                });
            }

            async function loadHistory() {
                if (!requirementsReady) {
                    clearTimeline('بانتظار تهيئة جداول الموظفين والتتبع.');
                    updateSummary();
                    clearMap();
                    return;
                }

                const employeeId = employeeSelect.value;
                if (!employeeId) {
                    clearTimeline();
                    updateSummary();
                    clearMap();
                    return;
                }

                const params = new URLSearchParams();
                if (dateInput.value) {
                    params.append('date', dateInput.value);
                }

                try {
                    refreshBtn.disabled = true;
                    refreshBtn.classList.add('disabled');

                    const query = params.toString();
                    const endpoint = query ? `${buildEndpoint(employeeId)}?${query}` : buildEndpoint(employeeId);

                    const response = await fetch(endpoint, {
                        headers: {
                            'Accept': 'application/json'
                        }
                    });

                    if (!response.ok) {
                        if (response.status === 503) {
                            clearTimeline('بانتظار تهيئة الجداول المطلوبة.');
                            updateSummary();
                            clearMap();
                            return;
                        }

                        throw new Error('تعذر تحميل السجل.');
                    }

                    const payload = await response.json();
                    updateSummary(payload);
                    renderTimeline(payload.logs ?? []);
                    renderMap(payload.logs ?? []);
                } catch (error) {
                    console.error(error);
                    clearTimeline('حدث خطأ أثناء تحميل السجل.');
                    updateSummary();
                    clearMap();
                } finally {
                    refreshBtn.disabled = false;
                    refreshBtn.classList.remove('disabled');
                }
            }

            if (requirementsReady) {
                employeeSelect.addEventListener('change', loadHistory);
                dateInput.addEventListener('change', loadHistory);
                refreshBtn.addEventListener('click', loadHistory);
            } else {
                clearTimeline('بانتظار تهيئة جداول الموظفين والتتبع.');
            }
        });
    </script>
@endpush
