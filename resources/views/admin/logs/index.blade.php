@php
// --- Helpers لعرض فروقات مفهومة ---
function _to_array($v){ return is_array($v) ? $v : (is_object($v) ? json_decode(json_encode($v), true) : []); }

function _is_assoc($a){ return is_array($a) && array_keys($a)!==range(0,count($a)-1); }

function _dot_flat($arr, $prefix=''){
    $out=[];
    foreach($arr as $k=>$v){
        $key = $prefix!=='' ? $prefix.'.'.$k : (string)$k;
        if(is_array($v) || is_object($v)) $out += _dot_flat((array)$v,$key);
        else $out[$key] = $v;
    }
    return $out;
}

function _pretty($v){
    if (is_null($v)) return '—';
    if (is_bool($v)) return $v ? 'true' : 'false';
    if (is_array($v) || is_object($v)) return json_encode($v, JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES);
    return (string)$v;
}

function _build_diff($before,$after){
    $b=_dot_flat(_to_array($before));
    $a=_dot_flat(_to_array($after));

    // لو كان after عبارة عن قائمة تغييرات {key, old, new}
    if(!_is_assoc($a) && isset($a[0]) && is_array($a[0]) && array_key_exists('old',$a[0]) && array_key_exists('new',$a[0])){
        $b=[]; $a2=[];
        foreach($after as $row){
            $key = $row['key'] ?? ($row['attribute'] ?? null);
            if($key===null) continue;
            $b[$key] = $row['old'] ?? null;
            $a2[$key] = $row['new'] ?? null;
        }
        $a = $a2;
    }

    $keys = array_unique(array_merge(array_keys($b), array_keys($a)));
    $changed=[]; $added=[]; $removed=[];
    foreach($keys as $k){
        $old = $b[$k] ?? null;
        $new = $a[$k] ?? null;
        if(!array_key_exists($k,$b) && array_key_exists($k,$a)) $added[$k]=$new;
        elseif(array_key_exists($k,$b) && !array_key_exists($k,$a)) $removed[$k]=$old;
        elseif(json_encode($old) !== json_encode($new)) $changed[$k]=['old'=>$old,'new'=>$new];
    }
    return compact('changed','added','removed');
}
@endphp

@php
// === Helper: رابط ترتيب الأعمدة ===
function al_sort_link($column, $title, $routeName = 'admin.activity-log.index') {
    $currentBy  = request('sort_by', 'id');
    $currentDir = request('sort_dir', 'desc');
    $newDir     = ($currentBy === $column && $currentDir === 'asc') ? 'desc' : 'asc';

    $icon = '';
    if ($currentBy === $column) {
        $icon = $currentDir === 'asc'
            ? '<i class="bi bi-sort-up ms-1"></i>'
            : '<i class="bi bi-sort-down ms-1"></i>';
    }

    $query = request()->except(['sort_by','sort_dir','page']);
    $query['sort_by']  = $column;
    $query['sort_dir'] = $newDir;

    return '<a href="'.route($routeName, $query).'" class="text-decoration-none text-dark">'.$title.$icon.'</a>';
}

// ألوان الصفوف بحسب الإجراء
$actionRowClass = [
    'created' => 'table-success',
    'updated' => 'table-warning',
    'deleted' => 'table-danger',
    'login'   => 'table-primary',
    'logout'  => 'table-secondary',
];

// بادجز عربية
$actionLabel = [
    'created' => 'إنشاء',
    'updated' => 'تحديث',
    'deleted' => 'حذف',
    'login'   => 'تسجيل دخول',
    'logout'  => 'تسجيل خروج',
];
@endphp

@extends('admin.layout')
@section('title', 'سجل الأنشطة')

@push('styles')
<style>
.pagination{ justify-content:center !important; gap:.4rem; margin-top:1rem; }
.pagination .page-item .page-link{
  background-color:#f9f5f1 !important; color:#be6661 !important; border-color:#be6661 !important;
  font-weight:600; border-radius:.375rem; transition:background-color .3s, color .3s; box-shadow:none;
}
.pagination .page-item .page-link:hover{ background-color:#dcaca9 !important; color:#fff !important; border-color:#dcaca9 !important; }
.pagination .page-item.active .page-link{ background-color:#be6661 !important; border-color:#be6661 !important; color:#fff !important; font-weight:700; pointer-events:none; }

.log-details pre{
  margin:0; white-space:pre-wrap; word-break:break-word; background:#f8f9fa; border:1px solid #eee; border-radius:.5rem; padding:.75rem;
}
.log-details .badge-label{ min-width:88px; }
</style>
@endpush

@section('content')
<div class="card shadow-sm">
    <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
        <h4 class="mb-0">سجل الأنشطة</h4>
        <div>
            <button type="button" class="btn btn-outline-primary btn-sm"
                    data-bs-toggle="modal" data-bs-target="#filtersModal"
                    style="background-color:#cd8985;color:#fff;border-color:#cd8985;">
                <i class="bi bi-funnel"></i> فلاتر
            </button>
        </div>
    </div>

    <div class="card-body">
        {{-- بحث سريع --}}
        <form method="GET" action="{{ route('admin.activity-log.index') }}" class="row g-2 mb-4" id="filterForm">
            <div class="col-md-4">
                <input type="text" name="q" class="form-control" placeholder="ابحث بالاسم/الهاتف/النوع/IP/الإجراء..."
                       value="{{ $filters['q'] ?? request('q') }}">
            </div>
            <div class="col-md-3">
                <select name="action" class="form-select" onchange="this.form.submit()">
                    <option value="">كل الإجراءات</option>
                    @foreach (['created'=>'إنشاء','updated'=>'تحديث','deleted'=>'حذف','login'=>'تسجيل دخول','logout'=>'تسجيل خروج'] as $k=>$v)
                        <option value="{{ $k }}" @selected(($filters['action'] ?? request('action'))===$k)>{{ $v }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <button class="btn btn-primary w-100">بحث</button>
            </div>
        </form>

        <div class="table-responsive">
            <table class="table table-bordered text-center align-middle" style="min-width:1000px;">
                <thead class="table-light">
                    <tr>
                        <th>{!! al_sort_link('id', '#') !!}</th>
                        <th>{!! al_sort_link('user_name', 'المستخدم') !!}</th>
                        <th>الهاتف</th>
                        <th>{!! al_sort_link('action', 'الإجراء') !!}</th>
                        <th>{!! al_sort_link('loggable_type', 'النوع') !!}</th>
                        <th>{!! al_sort_link('loggable_id', 'العنصر') !!}</th>
                        <th>{!! al_sort_link('ip_address', 'IP') !!}</th>
                        <th>{!! al_sort_link('created_at', 'التاريخ') !!}</th>
                        <th>تفاصيل</th>
                    </tr>
                </thead>
                <tbody>
@forelse ($logs as $log)
@php
    $rowClass = $actionRowClass[$log->action] ?? '';
    $user     = $log->user;
    $phone    = $user->phone_number ?? '—';
    $tz       = $timezone ?? config('app.timezone','Asia/Baghdad');
    $when     = optional($log->created_at)->timezone($tz)->format('Y-m-d H:i:s');
@endphp
                    <tr class="{{ $rowClass }}">
                        <td>#{{ $log->id }}</td>
                        <td class="text-start">
                            <div class="fw-semibold">{{ $user->name ?? 'نظام' }}</div>
                            @if($user)
                                <small class="text-muted">ID: {{ $user->id }}</small>
                            @endif
                        </td>
                        <td>{{ $phone }}</td>
                        <td>
                            @php $lbl = $actionLabel[$log->action] ?? $log->action; @endphp
                            @switch($log->action)
                                @case('created') <span class="badge bg-success">{{ $lbl }}</span> @break
                                @case('updated') <span class="badge bg-warning text-dark">{{ $lbl }}</span> @break
                                @case('deleted') <span class="badge bg-danger">{{ $lbl }}</span> @break
                                @case('login')   <span class="badge bg-primary">{{ $lbl }}</span> @break
                                @case('logout')  <span class="badge bg-secondary">{{ $lbl }}</span> @break
                                @default         <span class="badge bg-light text-dark">{{ $lbl }}</span>
                            @endswitch
                        </td>
                        <td>{{ class_basename($log->loggable_type) }}</td>
                        <td class="text-start">
                            @if($log->loggable)
                                {{ $log->loggable->name ?? $log->loggable->name_ar ?? '#'.$log->loggable_id }}
                            @else
                                #{{ $log->loggable_id }} (محذوف)
                            @endif
                        </td>
                        <td><span class="small">{{ $log->ip_address ?? '—' }}</span></td>
                        <td>{{ $when }}</td>
                        <td>
                            <button class="btn btn-sm btn-outline-dark"
                                    data-bs-toggle="collapse" data-bs-target="#log-{{ $log->id }}">
                                <i class="bi bi-file-text"></i> تفاصيل
                            </button>
                        </td>
                    </tr>

                    {{-- تفاصيل مفهومة --}}
                    <tr class="collapse" id="log-{{ $log->id }}">
                        <td colspan="9" class="bg-white text-start p-3">
                            @php $diff = _build_diff($log->before, $log->after); @endphp
                            <div class="row g-3">
                                <div class="col-12">
                                    <div class="d-flex flex-wrap gap-2 small">
                                        <span class="badge bg-light text-dark">الموديل: <strong>{{ class_basename($log->loggable_type) ?: '—' }}</strong></span>
                                        <span class="badge bg-light text-dark">العنصر: <strong>#{{ $log->loggable_id }}</strong></span>
                                        <span class="badge bg-light text-dark">IP: <strong>{{ $log->ip_address ?? '—' }}</strong></span>
                                        <span class="badge bg-light text-dark">المتصفح: <strong>{{ Str::limit($log->user_agent, 50) ?: '—' }}</strong></span>
                                    </div>
                                </div>

                                <div class="col-12">
                                    <h6 class="mb-2"><i class="bi bi-arrow-left-right"></i> الحقول التي تغيّرت</h6>
                                    @if(empty($diff['changed']))
                                        <div class="alert alert-light border small mb-0">لا توجد فروقات مسجّلة.</div>
                                    @else
                                        <div class="table-responsive">
                                            <table class="table table-sm align-middle mb-0">
                                                <thead class="table-light">
                                                    <tr>
                                                        <th style="width:35%;">الحقل</th>
                                                        <th class="text-danger">قبل</th>
                                                        <th class="text-success">بعد</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @foreach($diff['changed'] as $field => $pair)
                                                        <tr>
                                                            <td class="fw-semibold">{{ $field }}</td>
                                                            <td><code class="text-danger">{{ _pretty($pair['old']) }}</code></td>
                                                            <td><code class="text-success">{{ _pretty($pair['new']) }}</code></td>
                                                        </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                    @endif
                                </div>

                                @if(!empty($diff['added']))
                                <div class="col-md-6">
                                    <h6 class="mb-2"><i class="bi bi-plus-circle"></i> حقول أُضيفت</h6>
                                    <ul class="list-group small">
                                        @foreach($diff['added'] as $field => $val)
                                            <li class="list-group-item d-flex justify-content-between align-items-start">
                                                <span class="fw-semibold">{{ $field }}</span>
                                                <code>{{ _pretty($val) }}</code>
                                            </li>
                                        @endforeach
                                    </ul>
                                </div>
                                @endif

                                @if(!empty($diff['removed']))
                                <div class="col-md-6">
                                    <h6 class="mb-2"><i class="bi bi-dash-circle"></i> حقول حُذفت</h6>
                                    <ul class="list-group small">
                                        @foreach($diff['removed'] as $field => $val)
                                            <li class="list-group-item d-flex justify-content-between align-items-start">
                                                <span class="fw-semibold">{{ $field }}</span>
                                                <code>{{ _pretty($val) }}</code>
                                            </li>
                                        @endforeach
                                    </ul>
                                </div>
                                @endif
                            </div>
                        </td>
                    </tr>
@empty
                    <tr><td colspan="9" class="p-4">لا توجد سجلات لعرضها.</td></tr>
@endforelse
                </tbody>
            </table>
        </div>

        {{-- أسفل الجدول --}}
        <div class="d-flex justify-content-between align-items-center mt-3 flex-wrap gap-2">
            <form method="GET" action="{{ route('admin.activity-log.index') }}" class="d-flex align-items-center">
                @foreach(request()->except(['per_page','page']) as $k => $v)
                    <input type="hidden" name="{{ $k }}" value="{{ $v }}">
                @endforeach
                <label for="per_page" class="me-2">عدد السجلات:</label>
                <select name="per_page" id="per_page" class="form-select form-select-sm" onchange="this.form.submit()">
                    @foreach([5,10,25,50,100] as $size)
                        <option value="{{ $size }}" {{ request('per_page', 5) == $size ? 'selected' : '' }}>{{ $size }}</option>
                    @endforeach
                </select>
            </form>

            <div>
                {{ $logs->withQueryString()->links() }}
            </div>
        </div>
    </div>
</div>

{{-- Modal الفلاتر --}}
@php
    $usersList = \App\Models\User::select('id','name','phone_number')->orderBy('name')->get();
@endphp
<div class="modal fade" id="filtersModal" tabindex="-1" aria-labelledby="filtersModalLabel" aria-hidden="true">
  <div class="modal-dialog"><div class="modal-content">
      <div class="modal-header" style="background-color:#cd8985;color:white;">
        <h5 class="modal-title" id="filtersModalLabel">تصفية السجل</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <form method="GET" action="{{ route('admin.activity-log.index') }}" class="row g-3" id="modalFiltersForm">
          <div class="modal-body">
              <div class="col-12">
                  <label class="form-label">بحث عام</label>
                  <input type="text" name="q" class="form-control" placeholder="اسم/هاتف/نوع/إجراء/IP..." value="{{ request('q') }}">
              </div>
              <div class="col-md-6">
                  <label class="form-label">من تاريخ</label>
                  <input type="date" name="date_from" class="form-control" value="{{ request('date_from') }}">
              </div>
              <div class="col-md-6">
                  <label class="form-label">إلى تاريخ</label>
                  <input type="date" name="date_to" class="form-control" value="{{ request('date_to') }}">
              </div>
              <div class="col-md-6">
                  <label class="form-label">الإجراء</label>
                  <select name="action" class="form-select">
                      <option value="">الكل</option>
                      @foreach (['created'=>'إنشاء','updated'=>'تحديث','deleted'=>'حذف','login'=>'تسجيل دخول','logout'=>'تسجيل خروج'] as $k=>$v)
                          <option value="{{ $k }}" @selected(request('action')===$k)>{{ $v }}</option>
                      @endforeach
                  </select>
              </div>
              <div class="col-md-6">
                  <label class="form-label">المستخدم</label>
                  <select name="user_id" class="form-select">
                      <option value="">الكل</option>
                      @foreach($usersList as $u)
                          <option value="{{ $u->id }}" @selected(request('user_id')==$u->id)>{{ $u->name }} @if($u->phone_number) ({{ $u->phone_number }}) @endif</option>
                      @endforeach
                  </select>
              </div>
              <div class="col-md-6">
                  <label class="form-label">النوع (Model)</label>
                  <input type="text" name="model" class="form-control" placeholder="مثال: Order أو Product" value="{{ request('model') }}">
              </div>
              <div class="col-md-6">
                  <label class="form-label">عنوان IP</label>
                  <input type="text" name="ip" class="form-control" placeholder="مثال: 192.168.1.10" value="{{ request('ip') }}">
              </div>
          </div>
          <div class="modal-footer">
              <a href="{{ route('admin.activity-log.index') }}" class="btn btn-outline-secondary">إعادة تعيين</a>
              <button type="submit" class="btn" style="background-color:#cd8985;color:white;border-color:#cd8985;">تصفية</button>
          </div>
      </form>
  </div></div>
</div>
@endsection
