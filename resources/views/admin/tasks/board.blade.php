@extends('admin.layout')

@section('title', 'لوحة إدارة المهام')

@push('styles')
<style>
    .form-card { border-radius: 0 !important; border: none !important; box-shadow: none !important; background: #fff; width: 100% !important; margin: 0 !important; }
    .form-card-header { background: linear-gradient(135deg, var(--primary-dark) 0%, var(--primary-medium) 100%); padding: 2.5rem 3rem; color: white; border-radius: 0 !important; }
    .search-input { border-radius: 12px; border: 1px solid #e2e8f0; padding: 0.8rem 1.2rem; background: #fafbff; }
    
    .kanban-column { background: #f8fafc; border-radius: 16px; min-width: 300px; padding: 1.25rem; border: 1px solid #e2e8f0; height: fit-content; }
    .kanban-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.25rem; padding-bottom: 0.75rem; border-bottom: 2px solid #e2e8f0; }
    .kanban-tasks { min-height: 100px; display: flex; flex-direction: column; gap: 1rem; }
    .card-task { background: #fff; border-radius: 12px; padding: 1rem; border: 1px solid #e2e8f0; box-shadow: 0 1px 3px rgba(0,0,0,0.05); transition: all 0.2s; cursor: grab; }
    .card-task:hover { box-shadow: 0 10px 15px -3px rgba(0,0,0,0.1); transform: translateY(-2px); }
</style>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css">
@endpush

@section('content')
<div class="form-card">
    <div class="form-card-header d-flex justify-content-between align-items-center flex-wrap gap-3">
        <div>
            <h2 class="mb-2 fw-bold text-white"><i class="bi bi-kanban me-2"></i> لوحة إدارة مهام الفريق</h2>
            <p class="mb-0 opacity-75 fs-6 text-white small">تنظيم العمل، توزيع المهام، ومتابعة الإنجاز بنظام السحب والإفلات.</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('admin.tasks.board') }}" class="btn btn-outline-light px-4 fw-bold"><i class="bi bi-arrow-repeat me-1"></i> تحديث</a>
            @if($currentManager->can('create-tasks'))
                <button class="btn btn-light px-4 fw-bold text-brand" data-bs-toggle="modal" data-bs-target="#createTaskModal"><i class="bi bi-plus-circle me-1"></i> مهمة جديدة</button>
            @endif
        </div>
    </div>
    
    <div class="p-4 p-lg-5">
        <form method="GET" class="row g-3 mb-5 p-4 bg-light rounded-4 border align-items-end">
            <div class="col-md-3">
                <label class="small fw-bold text-muted mb-2">الأولوية</label>
                <select name="priority" class="form-select search-input" onchange="this.form.submit()">
                    <option value="">كل الأولويات</option>
                    @foreach($priorityLabels as $v => $l) <option value="{{$v}}" @selected(request('priority')==$v)>{{$l}}</option> @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <label class="small fw-bold text-muted mb-2">المسؤول</label>
                <select name="assignee_id" class="form-select search-input" onchange="this.form.submit()">
                    <option value="">كل الموظفين</option>
                    @foreach($assigneeOptions as $a) <option value="{{$a->id}}" @selected(request('assignee_id')==$a->id)>{{$a->name}}</option> @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <label class="small fw-bold text-muted mb-2">بحث ذكي</label>
                <input type="text" name="q" class="form-control search-input" placeholder="عنوان المهمة..." value="{{ request('q') }}">
            </div>
            <div class="col-md-3">
                <button type="submit" class="btn text-white w-100 py-3 fw-bold" style="background:var(--primary-dark); border-radius:12px">تطبيق الفلترة</button>
            </div>
        </form>

        <div class="d-flex gap-4 overflow-auto pb-4" style="min-height: 60vh;">
            @foreach(\App\Models\Task::STATUSES as $status)
                @php $tasks = $tasksByStatus[$status] ?? collect(); @endphp
                <div class="kanban-column" data-status="{{ $status }}">
                    <div class="kanban-header">
                        <h6 class="fw-bold mb-0 text-dark">{{ $statusLabels[$status] }}</h6>
                        <span class="badge bg-white text-dark border px-2">{{ $tasks->count() }}</span>
                    </div>
                    <div class="kanban-tasks" data-status="{{ $status }}">
                        @foreach($tasks as $task)
                            @include('admin.tasks.partials.card', ['task' => $task])
                        @endforeach
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</div>

@if($currentManager->can('create-tasks'))
    @include('admin.tasks.partials.create-modal')
@endif
@endsection

@push('scripts')
<script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const canManage = {{ $canManageStatus ? 'true' : 'false' }};
        if (canManage) {
            document.querySelectorAll('.kanban-tasks').forEach(col => {
                new Sortable(col, {
                    group: 'tasks',
                    animation: 200,
                    onEnd: function (evt) {
                        const id = evt.item.dataset.taskId;
                        const status = evt.to.dataset.status;
                        fetch('{{ route('admin.tasks.updateStatus', ':id') }}'.replace(':id', id), {
                            method: 'POST',
                            headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json', 'Content-Type': 'application/x-www-form-urlencoded' },
                            body: new URLSearchParams({ status })
                        });
                    }
                });
            });
        }
    });
</script>
@endpush
