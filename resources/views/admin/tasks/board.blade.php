@extends('admin.layout')

@section('title', 'لوحة إدارة المهام')

@push('styles')
<style>
    .kanban-wrapper {
        display: grid;
        gap: 1.1rem;
        grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
        align-items: start;
    }
    .kanban-column {
        background: rgba(255,255,255,0.85);
        border-radius: 1rem;
        box-shadow: 0 12px 24px rgba(74, 74, 74, 0.18);
        border: 1px solid rgba(74, 74, 74, 0.25);
        display: flex;
        flex-direction: column;
        max-height: 78vh;
    }
    .kanban-header {
        padding: 1rem 1.25rem;
        border-bottom: 1px solid rgba(74, 74, 74, 0.25);
        display: flex;
        justify-content: space-between;
        align-items: center;
        background: linear-gradient(135deg, rgba(74, 74, 74, 0.12), rgba(74, 74, 74, 0.12));
        border-top-left-radius: 1rem;
        border-top-right-radius: 1rem;
    }
    .kanban-header h6 {
        margin: 0;
        font-weight: 700;
        font-size: 1rem;
    }
    .kanban-tasks {
        padding: 0.85rem;
        overflow-y: auto;
        flex: 1;
        display: flex;
        flex-direction: column;
        gap: 0.75rem;
    }
    .kanban-card {
        background: #fff;
        border-radius: 0.9rem;
        border: 1px solid rgba(74, 74, 74, 0.2);
        padding: 0.8rem;
        box-shadow: 0 10px 20px rgba(74, 74, 74, 0.14);
        cursor: grab;
        transition: transform 0.2s ease, box-shadow 0.2s ease;
    }
    .kanban-card:active {
        cursor: grabbing;
    }
    .kanban-card.disabled-drag {
        cursor: default;
    }
    .kanban-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 16px 30px rgba(74, 74, 74, 0.18);
    }
    .kanban-badge {
        font-size: 0.7rem;
        border-radius: 999px;
        padding: 0.2rem 0.55rem;
    }
    .avatar-stack {
        display: flex;
        align-items: center;
    }
    .avatar-stack img {
        width: 30px;
        height: 30px;
        border-radius: 50%;
        border: 2px solid #fff;
        object-fit: cover;
        margin-left: -8px;
        transition: transform 0.2s ease;
    }
    .avatar-stack img:first-child {
        margin-left: 0;
    }
    .avatar-stack img:hover {
        transform: scale(1.08);
        z-index: 2;
    }
    .kanban-progress {
        height: 6px;
        border-radius: 999px;
        background: rgba(74, 74, 74, 0.25);
        overflow: hidden;
    }
    .kanban-progress span {
        display: block;
        height: 100%;
        border-radius: inherit;
    }
    .filter-pill {
        border-radius: 999px;
        background: rgba(74, 74, 74, 0.12);
        border: 1px solid rgba(74, 74, 74, 0.25);
        color: #3b4f9c;
        font-weight: 600;
    }
</style>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css">
@endpush

@section('content')
@php
    $statusOrder = \App\Models\Task::STATUSES;
@endphp
<div class="page-wrapper container-fluid px-0">
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
        <div>
            <h2 class="fw-bold mb-1">لوحة إدارة المهام</h2>
            <p class="text-muted mb-0">تابع مهام فريقك، حدّث حالاتها بالسحب والإفلات، وتواصل من خلال التعليقات.</p>
        </div>
        <div class="d-flex flex-wrap align-items-center gap-2">
            <a href="{{ route('admin.tasks.board') }}" class="btn btn-outline-secondary btn-sm">
                <i class="bi bi-arrow-repeat me-1"></i> تحديث اللوحة
            </a>
            @if($currentManager->can('create-tasks'))
                <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#createTaskModal">
                    <i class="bi bi-plus-circle me-1"></i> مهمة جديدة
                </button>
            @endif
        </div>
    </div>

    <form method="GET" class="card shadow-sm border-0 mb-4">
        <div class="card-body row g-3 align-items-end">
            @if(count($scopeOptions) > 1)
                <div class="col-12 col-md-3">
                    <label class="form-label">نطاق العرض</label>
                    <select name="scope" class="form-select" onchange="this.form.submit()">
                        @foreach($scopeOptions as $key => $label)
                            <option value="{{ $key }}" {{ $activeScope === $key ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
            @endif
            <div class="col-12 col-md-3">
                <label class="form-label">الأولوية</label>
                <select name="priority" class="form-select" onchange="this.form.submit()">
                    <option value="">الكل</option>
                    @foreach($priorityLabels as $value => $label)
                        <option value="{{ $value }}" {{ request('priority') === $value ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-12 col-md-3">
                <label class="form-label">المسؤول</label>
                <select name="assignee_id" class="form-select" onchange="this.form.submit()">
                    <option value="">الجميع</option>
                    @foreach($assigneeOptions as $assignee)
                        <option value="{{ $assignee->id }}" {{ (int) request('assignee_id') === $assignee->id ? 'selected' : '' }}>{{ $assignee->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-12 col-md-3">
                <label class="form-label">بحث</label>
                <div class="input-group">
                    <input type="text" name="q" class="form-control" value="{{ request('q') }}" placeholder="عنوان أو وصف المهمة">
                    <button class="btn btn-outline-secondary" type="submit"><i class="bi bi-search"></i></button>
                </div>
            </div>
        </div>
    </form>

    <div class="kanban-wrapper">
        @foreach($statusOrder as $status)
            @php
                $tasks = $tasksByStatus[$status] ?? collect();
                $statusLabel = $statusLabels[$status] ?? $status;
            @endphp
            <div class="kanban-column" data-status="{{ $status }}">
                <div class="kanban-header">
                    <h6>{{ $statusLabel }}</h6>
                    <span class="badge bg-primary-subtle text-primary">{{ $tasks->count() }}</span>
                </div>
                <div class="kanban-tasks" data-status="{{ $status }}">
                    @forelse($tasks as $task)
                        @include('admin.tasks.partials.card', [
                            'task' => $task,
                            'statusLabels' => $statusLabels,
                            'priorityLabels' => $priorityLabels,
                            'assigneeOptions' => $assigneeOptions,
                            'canManageStatus' => $canManageStatus,
                            'canEditTasks' => $canEditTasks,
                            'canDeleteTasks' => $canDeleteTasks,
                            'canAssignTasks' => $canAssignTasks,
                            'canEditCreator' => $canEditCreator,
                            'relatedTypeOptions' => $relatedTypeOptions,
                            'relatedLookupRoutes' => $relatedLookupRoutes,
                            'currentManager' => $currentManager,
                            'canViewDetails' => $canViewDetails,
                            'canCommentOnTasks' => $canCommentOnTasks,
                        ])
                    @empty
                        <div class="text-center text-muted small">
                            لا توجد مهام في هذا العمود حالياً.
                        </div>
                    @endforelse
                </div>
            </div>
        @endforeach
    </div>
</div>

@if($currentManager->can('create-tasks'))
@include('admin.tasks.partials.create-modal', [
    'assigneeOptions' => $assigneeOptions,
    'priorityLabels' => $priorityLabels,
    'statusLabels' => $statusLabels,
    'relatedTypeOptions' => $relatedTypeOptions,
    'relatedLookupRoutes' => $relatedLookupRoutes,
    'currentManager' => $currentManager,
    'canAssignTasks' => $canAssignTasks,
    'canEditCreator' => $canEditCreator,
])
@endif
@endsection

@push('scripts')
<script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const canManageStatus = {{ $canManageStatus ? 'true' : 'false' }};
        const canReopenTasks = {{ $canReopenTasks ? 'true' : 'false' }};
        const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

        if (canManageStatus) {
            document.querySelectorAll('.kanban-tasks').forEach(column => {
                new Sortable(column, {
                    group: 'tasks-board',
                    animation: 160,
                    ghostClass: 'opacity-50',
                    onEnd: function (evt) {
                        const taskId = evt.item.dataset.taskId;
                        const newStatus = evt.to.dataset.status;
                        if (!taskId || !newStatus) {
                            return;
                        }
                        const previousStatus = evt.from.dataset.status;
                        if (previousStatus === '{{ \App\Models\Task::STATUS_DONE }}' && newStatus !== '{{ \App\Models\Task::STATUS_DONE }}' && !canReopenTasks) {
                            const children = Array.from(evt.from.children);
                            const referenceNode = children[evt.oldIndex] ?? null;
                            evt.from.insertBefore(evt.item, referenceNode);
                            evt.item.classList.add('border-danger');
                            setTimeout(() => evt.item.classList.remove('border-danger'), 2000);
                            return;
                        }

                        updateTaskStatus(taskId, newStatus, evt.item);
                    }
                });
            });
        } else {
            document.querySelectorAll('.kanban-card').forEach(card => card.classList.add('disabled-drag'));
        }

        function updateTaskStatus(taskId, status, element) {
            const url = '{{ route('admin.tasks.updateStatus', ':id') }}'.replace(':id', taskId);
            const formData = new URLSearchParams({ status });

            fetch(url, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json',
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: formData.toString(),
            }).then(response => {
                if (!response.ok) {
                    throw new Error('فشل تحديث الحالة');
                }
                return response.json();
            }).then(() => {
                element.classList.add('border-success');
                setTimeout(() => element.classList.remove('border-success'), 2000);
            }).catch(() => {
                element.classList.add('border-danger');
                setTimeout(() => element.classList.remove('border-danger'), 2000);
            });
        }

        const focusTaskId = {{ $focusTaskId ? (int) $focusTaskId : 'null' }};
        if (focusTaskId) {
            const card = document.querySelector(`[data-task-id="${focusTaskId}"]`);
            if (card) {
                card.classList.add('border-primary', 'border-2');
                card.scrollIntoView({ behavior: 'smooth', block: 'center' });
                setTimeout(() => card.classList.remove('border-primary', 'border-2'), 3500);
            }
        }

        document.querySelectorAll('.task-modal').forEach(modal => {
            modal.addEventListener('shown.bs.modal', () => {
                initialiseRelatedSelectors(modal);
            }, { once: true });
        });

        const createModal = document.getElementById('createTaskModal');
        if (createModal) {
            createModal.addEventListener('shown.bs.modal', () => {
                initialiseRelatedSelectors(createModal);
            }, { once: true });
        }
    });

    function initialiseRelatedSelectors(modalElement) {
        const $modal = $(modalElement);
        $modal.find('.task-related-fields').each(function () {
            const $container = $(this);
            const $typeSelect = $container.find('.task-related-type');
            const $hiddenInput = $container.find('.task-related-id');
            const selects = {};

            $container.find('.task-related-select').each(function () {
                const $select = $(this);
                const typeKey = String($select.data('type'));
                const endpoint = $select.data('endpoint');

                if (!$select.hasClass('select2-hidden-accessible')) {
                    $select.select2({
                        dropdownParent: $modal,
                        placeholder: $select.data('placeholder'),
                        allowClear: true,
                        width: '100%',
                        dir: 'rtl',
                        ajax: endpoint ? {
                            url: endpoint,
                            dataType: 'json',
                            delay: 250,
                            data: params => ({ q: params.term || '' }),
                            processResults: data => ({ results: data }),
                        } : undefined,
                    });
                }

                $select.on('select2:select', function (event) {
                    $hiddenInput.val(event.params.data.id);
                });

                $select.on('select2:clear', function () {
                    $hiddenInput.val('');
                });

                selects[typeKey] = $select;
            });

            function syncVisibility() {
                const activeType = String($typeSelect.val() || '');
                let matched = false;

                Object.entries(selects).forEach(([key, $select]) => {
                    const matches = key === activeType;
                    $select.closest('.task-related-select-wrapper').toggleClass('d-none', !matches);
                    if (!matches) {
                        $select.val(null).trigger('change.select2');
                    } else {
                        matched = true;
                        const currentVal = $select.val();
                        $hiddenInput.val(currentVal ? currentVal.toString() : '');
                    }
                });

                if (!matched) {
                    $hiddenInput.val('');
                }
            }

            $typeSelect.on('change', syncVisibility);
            syncVisibility();
        });
    }
</script>
@endpush
