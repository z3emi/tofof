@php
function _to_arr($v){ return is_array($v) ? $v : (is_object($v) ? json_decode(json_encode($v),true) : []); }
function _dot_f($arr, $p=''){ $o=[]; foreach($arr as $k=>$v){ $key=$p!==''?$p.'.'.$k:(string)$k; if(is_array($v)||is_object($v)) $o+=_dot_f((array)$v,$key); else $o[$key]=$v; } return $o; }
function _prt($v){ if(is_null($v)) return '—'; if(is_bool($v)) return $v?'true':'false'; if(is_array($v)||is_object($v)) return json_encode($v,256|64); return (string)$v; }
function _diff($b,$a){ 
    $bf=_dot_f(_to_arr($b)); $af=_dot_f(_to_arr($a));
    $ks=array_unique(array_merge(array_keys($bf),array_keys($af))); $ch=[]; $ad=[]; $rm=[];
    foreach($ks as $k){ $ov=$bf[$k]??null; $nv=$af[$k]??null;
        if(!array_key_exists($k,$bf)&&array_key_exists($k,$af)) $ad[$k]=$nv;
        elseif(array_key_exists($k,$bf)&&!array_key_exists($k,$af)) $rm[$k]=$ov;
        elseif(json_encode($ov)!==json_encode($nv)) $ch[$k]=['o'=>$ov,'n'=>$nv];
    } return compact('ch','ad','rm');
}
function _is_technical_field($field){
    return in_array($field, ['id','created_at','updated_at','deleted_at','remember_token','password','email_verified_at'], true);
}
function _field_label($field){
    $map = [
        'name' => 'الاسم',
        'name_ar' => 'الاسم العربي',
        'name_en' => 'الاسم الانكليزي',
        'title' => 'العنوان',
        'slug' => 'الرابط المختصر',
        'price' => 'السعر',
        'sale_price' => 'سعر التخفيض',
        'qty' => 'الكمية',
        'stock' => 'المخزون',
        'status' => 'الحالة',
        'is_active' => 'نشط',
        'phone' => 'الهاتف',
        'phone_number' => 'رقم الهاتف',
        'email' => 'البريد الإلكتروني',
        'description' => 'الوصف',
        'category_id' => 'التصنيف',
        'parent_id' => 'القسم الأب',
        'image' => 'الصورة',
    ];

    return $map[$field] ?? str_replace('_', ' ', $field);
}
function _present($v){
    if (is_null($v) || $v === '') return '—';
    if (is_bool($v)) return $v ? 'نعم' : 'لا';
    if (is_array($v) || is_object($v)) return json_encode($v, JSON_UNESCAPED_UNICODE);
    return (string) $v;
}
function _is_true_like($v){
    if (is_bool($v)) return $v;
    if (is_numeric($v)) return (int) $v === 1;
    $s = strtolower(trim((string) $v));
    return in_array($s, ['1','true','yes','on'], true);
}
function _human_changes($before, $after){
    $rows = [];
    $a = _to_arr($after);

    // الحالة المفضلة: after مخزن بصيغة field => {old,new}
    foreach ($a as $field => $value) {
        if (_is_technical_field((string) $field)) continue;

        if (is_array($value) && array_key_exists('old', $value) && array_key_exists('new', $value)) {
            if (json_encode($value['old']) !== json_encode($value['new'])) {
                $rows[] = ['field' => (string) $field, 'old' => $value['old'], 'new' => $value['new']];
            }
        }
    }

    if (!empty($rows)) {
        return $rows;
    }

    // fallback لأي صيغة قديمة
    $d = _diff($before, $after);
    foreach (($d['ch'] ?? []) as $field => $pair) {
        if (str_ends_with((string) $field, '.old') || str_ends_with((string) $field, '.new')) continue;
        if (_is_technical_field((string) $field)) continue;

        $rows[] = ['field' => (string) $field, 'old' => $pair['o'] ?? null, 'new' => $pair['n'] ?? null];
    }

    return $rows;
}
function _semantic_action(string $action, $before, $after): string {
    $action = strtolower($action);

    if ($action === 'created') return 'create';
    if ($action === 'deleted') return 'delete';
    if ($action === 'login') return 'login';
    if ($action === 'logout') return 'logout';
    if ($action === 'failed_login') return 'failed_login';

    if (in_array($action, ['updated', 'admin_update'], true)) {
        $changes = _human_changes($before, $after);

        foreach ($changes as $row) {
            $field = strtolower((string) ($row['field'] ?? ''));
            $new = $row['new'] ?? null;

            if ($field === 'banned_at') {
                return is_null($new) || $new === '' ? 'unban' : 'ban';
            }

            if ($field === 'is_active') {
                return _is_true_like($new) ? 'activate' : 'deactivate';
            }

            if ($field === 'status') {
                $status = strtolower(trim((string) $new));
                if (in_array($status, ['banned', 'blocked', 'inactive', 'disabled'], true)) return 'deactivate';
                if (in_array($status, ['active', 'enabled'], true)) return 'activate';
            }
        }

        return 'update';
    }

    return $action;
}
function _action_meta(string $semantic, string $fallback): array {
    $map = [
        'create' => ['label' => 'إضافة', 'class' => 'bg-success', 'icon' => 'bi-plus-lg'],
        'update' => ['label' => 'تعديل', 'class' => 'bg-warning text-dark', 'icon' => 'bi-pencil-fill'],
        'delete' => ['label' => 'حذف', 'class' => 'bg-danger', 'icon' => 'bi-trash-fill'],
        'ban' => ['label' => 'حظر', 'class' => 'bg-danger', 'icon' => 'bi-slash-circle-fill'],
        'unban' => ['label' => 'إلغاء الحظر', 'class' => 'bg-success', 'icon' => 'bi-check-circle-fill'],
        'activate' => ['label' => 'تفعيل', 'class' => 'bg-success', 'icon' => 'bi-toggle-on'],
        'deactivate' => ['label' => 'تعطيل', 'class' => 'bg-secondary', 'icon' => 'bi-toggle-off'],
        'login' => ['label' => 'دخول', 'class' => 'bg-info text-dark', 'icon' => 'bi-box-arrow-in-right'],
        'logout' => ['label' => 'خروج', 'class' => 'bg-secondary', 'icon' => 'bi-box-arrow-right'],
        'failed_login' => ['label' => 'فشل دخول', 'class' => 'bg-danger', 'icon' => 'bi-exclamation-triangle-fill'],
    ];

    return $map[$semantic] ?? ['label' => $fallback, 'class' => 'bg-secondary', 'icon' => 'bi-gear-fill'];
}
function log_sort($col,$title){
    $by=request('sort_by','id'); $dir=request('sort_dir','desc'); $nd=($by==$col&&$dir=='asc')?'desc':'asc';
    $ic=$by==$col?($dir=='asc'?'↑':'↓'):'';
    return '<a href="'.route('admin.activity-log.index',array_merge(request()->all(),['sort_by'=>$col,'sort_dir'=>$nd])).'" class="text-dark fw-bold text-decoration-none">'.$title.' '.$ic.'</a>';
}
$acts=['created'=>'إنشاء','updated'=>'تحديث','deleted'=>'حذف','login'=>'دخول','logout'=>'خروج','failed_login'=>'فشل دخول'];
$actionFilterOptions = [
    'create' => 'إضافة',
    'update' => 'تعديل',
    'delete' => 'حذف',
    'ban' => 'حظر',
    'unban' => 'إلغاء الحظر',
    'activate' => 'تفعيل',
    'deactivate' => 'تعطيل',
    'login' => 'دخول',
    'logout' => 'خروج',
    'failed_login' => 'فشل دخول',
];
@endphp

@extends('admin.layout')
@section('title', 'سجل الأنشطة')

@push('styles')
<style>
    .form-card { border-radius: 0 !important; border: none !important; box-shadow: none !important; background: #fff; width: 100% !important; margin: 0 !important; }
    .form-card-header { background: linear-gradient(135deg, var(--primary-dark) 0%, var(--primary-medium) 100%); padding: 2.5rem 3rem; color: white; border-radius: 0 !important; }
    .log-item { transition: all 0.2s; border-right: 4px solid transparent; }
    .log-created { border-right-color: #198754; background: #fdfefe; }
    .log-updated { border-right-color: #ffc107; background: #fffdf8; }
    .log-deleted { border-right-color: #dc3545; background: #fff9f9; }
    .diff-table { border-radius: 10px; overflow: hidden; border: 1px solid #f1f5f9; }
</style>
@endpush

@section('content')
<div class="form-card">
    <div class="form-card-header d-flex justify-content-between align-items-center">
        <div>
            <h2 class="mb-2 fw-bold text-white"><i class="bi bi-clock-history me-2"></i> سجل الأنشطة والرقابة</h2>
            <p class="mb-0 opacity-75 fs-6 text-white small">مراقبة كافة حركات المستخدمين والمدراء وتغييرات البيانات في النظام.</p>
        </div>
    </div>
    
    <div class="p-4 p-lg-5">
        <form method="GET" class="row g-3 mb-4 p-4 bg-light rounded-4 border align-items-end">
            <div class="col-md-6">
                <label class="small fw-bold text-muted mb-2">بحث ذكي</label>
                <input type="text" name="q" class="form-control" style="border-radius:12px; padding:0.8rem" placeholder="اسم، هاتف، IP، أو نوع العملية..." value="{{ request('q') }}">
            </div>
            <div class="col-md-6 d-flex gap-2">
                <button type="submit" class="btn text-white px-3 py-2 fw-bold flex-grow-1" style="background:var(--primary-dark); border-radius:12px">بحث</button>
                <button type="button" class="btn btn-outline-dark d-flex align-items-center justify-content-center" style="width:58px; height:58px; border-radius:12px" data-bs-toggle="modal" data-bs-target="#filtersModal" title="تصفية متقدمة">
                    <i class="bi bi-funnel fs-4"></i>
                </button>
                @if(request()->anyFilled(['q','action','date_from','date_to']))
                    <a href="{{ route('admin.activity-log.index') }}" class="btn btn-outline-secondary d-flex align-items-center justify-content-center" style="width:58px; height:58px; border-radius:12px" title="تصفير">
                        <i class="bi bi-arrow-counterclockwise fs-4"></i>
                    </a>
                @endif
            </div>
        </form>

        <div class="table-responsive border rounded-4 shadow-sm overflow-hidden">
            <table class="table mb-0 align-middle text-center">
                <thead class="bg-light border-bottom">
                    <tr class="text-muted small fw-bold">
                        <th class="py-3">{!! log_sort('id','#') !!}</th>
                        <th class="py-3 text-start">{!! log_sort('user_name','المسؤول') !!}</th>
                        <th class="py-3">الإجراء</th>
                        <th class="py-3">النوع</th>
                        <th class="py-3 text-start">العنصر المتأثر</th>
                        <th class="py-3">عنوان IP</th>
                        <th class="py-3">{!! log_sort('created_at','الوقت والتاريخ') !!}</th>
                        <th class="py-3" width="100">تفاصيل</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($logs as $log)
                        @php
                            $semanticAction = _semantic_action((string) $log->action, $log->before, $log->after);
                            $actionMeta = _action_meta($semanticAction, $acts[$log->action] ?? $log->action);
                        @endphp
                        <tr class="log-item log-{{ $log->action }}">
                            <td class="small text-muted">#{{ $log->id }}</td>
                            <td class="text-start">
                                <div class="fw-bold text-dark">{{ $log->user->name ?? 'نظام تلقائي' }}</div>
                                <div class="small text-muted">{{ $log->user->phone_number ?? '' }}</div>
                            </td>
                            <td>
                                <span class="badge {{ $actionMeta['class'] }} rounded-pill px-3 py-2">
                                    {{ $actionMeta['label'] }} <i class="bi {{ $actionMeta['icon'] }}"></i>
                                </span>
                            </td>
                            <td class="small fw-bold">{{ class_basename($log->loggable_type) }}</td>
                            <td class="text-start">
                                <span class="small text-dark">{{ $log->loggable->name ?? $log->loggable->name_ar ?? '#'.$log->loggable_id }}</span>
                                @if(!$log->loggable) <span class="badge bg-light text-muted fw-normal" style="font-size:0.6rem">محذوف نهائياً</span> @endif
                            </td>
                            <td class="small text-muted">{{ $log->ip_address }}</td>
                            <td class="small">{{ $log->created_at->setTimezone($timezone)->format('Y-m-d H:i') }}</td>
                            <td>
                                <button class="btn btn-sm btn-outline-dark rounded-pill px-3" type="button" data-bs-toggle="modal" data-bs-target="#detModal-{{ $log->id }}">
                                    عرض <i class="bi bi-chevron-down ms-1"></i>
                                </button>
                            </td>
                        </tr>

                        @push('log_modals')
                        <div class="modal fade" id="detModal-{{ $log->id }}" tabindex="-1" aria-hidden="true">
                            <div class="modal-dialog modal-dialog-centered modal-xl">
                                <div class="modal-content border-0 shadow-lg" style="border-radius:18px; overflow:hidden;">
                                    <div class="modal-header bg-light border-0">
                                        <h5 class="modal-title fw-bold">تفاصيل السجل #{{ $log->id }}</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                    </div>
                                    <div class="modal-body p-4">
                                        @php $changes = _human_changes($log->before, $log->after); @endphp
                                        <div class="row g-3 mb-4 small">
                                            <div class="col-md-3"><span class="text-muted d-block">المسؤول</span><strong>{{ $log->user->name ?? 'نظام تلقائي' }}</strong></div>
                                            <div class="col-md-3"><span class="text-muted d-block">الإجراء</span><strong>{{ $actionMeta['label'] }}</strong></div>
                                            <div class="col-md-3"><span class="text-muted d-block">النوع</span><strong>{{ class_basename($log->loggable_type) }}</strong></div>
                                            <div class="col-md-3"><span class="text-muted d-block">الوقت</span><strong>{{ $log->created_at->setTimezone($timezone)->format('Y-m-d H:i') }}</strong></div>
                                        </div>

                                        @if(in_array($log->action, ['updated','admin_update'], true))
                                            <h6 class="fw-bold mb-2">تم تعديل الحقول التالية</h6>
                                            <div class="diff-table table-responsive mb-4">
                                                <table class="table table-sm mb-0">
                                                    <thead class="table-light"><tr><th width="20%">الحقل</th><th class="text-danger">من</th><th class="text-success">إلى</th></tr></thead>
                                                    <tbody>
                                                        @forelse($changes as $row)
                                                            <tr>
                                                                <td class="fw-bold small">{{ _field_label($row['field']) }}</td>
                                                                <td><code class="text-danger small">{{ _present($row['old']) }}</code></td>
                                                                <td><code class="text-success small fw-bold">{{ _present($row['new']) }}</code></td>
                                                            </tr>
                                                        @empty
                                                            <tr><td colspan="3" class="text-center text-muted small py-3">لا توجد تفاصيل تعديل واضحة لهذه العملية.</td></tr>
                                                        @endforelse
                                                    </tbody>
                                                </table>
                                            </div>
                                        @elseif(in_array($log->action, ['created','admin_create'], true))
                                            <div class="alert alert-success mb-0">
                                                تم إنشاء {{ class_basename($log->loggable_type) }} جديد بواسطة {{ $log->user->name ?? 'النظام' }}.
                                            </div>
                                        @elseif(in_array($log->action, ['deleted','admin_delete'], true))
                                            <div class="alert alert-danger mb-0">
                                                تم حذف {{ class_basename($log->loggable_type) }} بواسطة {{ $log->user->name ?? 'النظام' }}.
                                            </div>
                                        @else
                                            <div class="alert alert-secondary mb-0">
                                                تم تنفيذ إجراء {{ $acts[$log->action] ?? $log->action }}.
                                            </div>
                                        @endif

                                        <div class="mt-3 small text-muted border-top pt-3">
                                            <i class="bi bi-cpu me-1"></i> المتصفح: {{ $log->user_agent }}
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endpush
                    @empty
                        <tr><td colspan="8" class="py-5 text-muted">لا توجد سجلات مطابقة للبحث.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="mt-4 d-flex justify-content-between align-items-center">
            <div class="small text-muted">إجمالي السجلات: {{ number_format($logs->total()) }}</div>
            <div>{{ $logs->withQueryString()->links() }}</div>
        </div>
    </div>
</div>

@stack('log_modals')

{{-- Filters Modal --}}
<div class="modal fade" id="filtersModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content shadow-lg border-0" style="border-radius:20px">
            <div class="modal-header border-0 p-4 pb-0">
                <h5 class="fw-bold">فلاتر تصفية السجل</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                <form method="GET" class="row g-3">
                    <div class="col-12">
                        <label class="small fw-bold mb-2">نوع الإجراء</label>
                        <select name="action" class="form-select" style="border-radius:10px">
                            <option value="">كل العمليات</option>
                            @foreach($actionFilterOptions as $k=>$v)
                                <option value="{{$k}}" @selected(request('action')==$k)>{{$v}}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-6"><label class="small fw-bold mb-2">من تاريخ</label><input type="date" name="date_from" class="form-control" style="border-radius:10px" value="{{request('date_from')}}"></div>
                    <div class="col-md-6"><label class="small fw-bold mb-2">إلى تاريخ</label><input type="date" name="date_to" class="form-control" style="border-radius:10px" value="{{request('date_to')}}"></div>
                    <div class="col-12"><label class="small fw-bold mb-2">المستخدم المتحكم</label>
                        <select name="user_id" class="form-select" style="border-radius:10px">
                            <option value="">الكل</option>
                            @foreach(\App\Models\Manager::orderBy('name')->get() as $u)
                                <option value="{{$u->id}}" @selected(request('user_id')==$u->id)>{{$u->name}}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-12 mt-4">
                        <button type="submit" class="btn text-white w-100 py-3 fw-bold" style="background:var(--primary-dark); border-radius:12px">تطبيق الفلاتر</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
