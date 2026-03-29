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
function log_sort($col,$title){
    $by=request('sort_by','id'); $dir=request('sort_dir','desc'); $nd=($by==$col&&$dir=='asc')?'desc':'asc';
    $ic=$by==$col?($dir=='asc'?'↑':'↓'):'';
    return '<a href="'.route('admin.activity-log.index',array_merge(request()->all(),['sort_by'=>$col,'sort_dir'=>$nd])).'" class="text-dark fw-bold text-decoration-none">'.$title.' '.$ic.'</a>';
}
$acts=['created'=>'إنشاء','updated'=>'تحديث','deleted'=>'حذف','login'=>'دخول','logout'=>'خروج'];
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
            <div class="col-md-2">
                <label class="small fw-bold text-muted mb-2">نوع الإجراء</label>
                <select name="action" class="form-select" style="border-radius:12px; padding:0.8rem" onchange="this.form.submit()">
                    <option value="">كل العمليات</option>
                    @foreach($acts as $k=>$v) <option value="{{$k}}" @selected(request('action')==$k)>{{$v}}</option> @endforeach
                </select>
            </div>
            <div class="col-md-4 d-flex gap-2">
                <button type="submit" class="btn text-white px-4 py-3 fw-bold flex-grow-1" style="background:var(--primary-dark); border-radius:12px">بحث</button>
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
                        @php $d = _diff($log->before, $log->after); @endphp
                        <tr class="log-item log-{{ $log->action }}">
                            <td class="small text-muted">#{{ $log->id }}</td>
                            <td class="text-start">
                                <div class="fw-bold text-dark">{{ $log->user->name ?? 'نظام تلقائي' }}</div>
                                <div class="small text-muted">{{ $log->user->phone_number ?? '' }}</div>
                            </td>
                            <td>
                                @switch($log->action)
                                    @case('created') <span class="badge bg-success rounded-pill px-3 py-2">إضافة <i class="bi bi-plus-lg"></i></span> @break
                                    @case('updated') <span class="badge bg-warning text-dark rounded-pill px-3 py-2">تعديل <i class="bi bi-pencil-fill"></i></span> @break
                                    @case('deleted') <span class="badge bg-danger rounded-pill px-3 py-2">حذف <i class="bi bi-trash-fill"></i></span> @break
                                    @default <span class="badge bg-secondary rounded-pill px-3 py-2">{{ $acts[$log->action] ?? $log->action }}</span>
                                @endswitch
                            </td>
                            <td class="small fw-bold">{{ class_basename($log->loggable_type) }}</td>
                            <td class="text-start">
                                <span class="small text-dark">{{ $log->loggable->name ?? $log->loggable->name_ar ?? '#'.$log->loggable_id }}</span>
                                @if(!$log->loggable) <span class="badge bg-light text-muted fw-normal" style="font-size:0.6rem">محذوف نهائياً</span> @endif
                            </td>
                            <td class="small text-muted">{{ $log->ip_address }}</td>
                            <td class="small">{{ $log->created_at->setTimezone($timezone)->format('Y-m-d H:i') }}</td>
                            <td>
                                <button class="btn btn-sm btn-outline-dark rounded-pill px-3" data-bs-toggle="collapse" data-bs-target="#det-{{ $log->id }}">
                                    عرض <i class="bi bi-chevron-down ms-1"></i>
                                </button>
                            </td>
                        </tr>
                        <tr class="collapse" id="det-{{ $log->id }}">
                            <td colspan="8" class="bg-white p-4">
                                @if(!empty($d['ch']))
                                    <div class="diff-table table-responsive">
                                        <table class="table table-sm mb-0">
                                            <thead class="table-light"><tr><th width="20%">الحقل</th><th class="text-danger">من</th><th class="text-success">إلى</th></tr></thead>
                                            <tbody>
                                                @foreach($d['ch'] as $f => $p)
                                                    <tr><td class="fw-bold small">{{$f}}</td><td><code class="text-danger small">{{_prt($p['o'])}}</code></td><td><code class="text-success small fw-bold">{{_prt($p['n'])}}</code></td></tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                @else
                                    <div class="text-center py-3 text-muted small">لا توجد فروقات بيانات مسجلة لهذه العملية.</div>
                                @endif
                                <div class="mt-3 small text-muted border-top pt-3">
                                    <i class="bi bi-cpu me-1"></i> المتصفح: {{ $log->user_agent }}
                                </div>
                            </td>
                        </tr>
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
